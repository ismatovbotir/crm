<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Catalog\Category;
use App\Models\Catalog\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ImportController extends Controller
{
    public function catalogTemplate()
    {
        abort_unless(auth()->user()->can('catalog.import'), 403);

        return response()->streamDownload(function () {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($handle, ['sku', 'name', 'description', 'category_slug', 'retail_price_uzs', 'wholesale_price_uzs', 'retail_price_usd', 'stock', 'is_active'], ';');
            fputcsv($handle, ['POS-001', 'Пример товара', 'Описание', 'pos-systems', '1500000', '1200000', '150', '10', '1'], ';');
            fclose($handle);
        }, 'catalog-import-template.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function catalogImport(Request $request)
    {
        abort_unless(auth()->user()->can('catalog.import'), 403);

        $request->validate(['file' => 'required|file|mimes:csv,txt|max:5120']);

        $file = $request->file('file');
        $handle = fopen($file->getPathname(), 'r');

        // Skip BOM if present
        $bom = fread($handle, 3);
        if ($bom !== chr(0xEF).chr(0xBB).chr(0xBF)) {
            rewind($handle);
        }

        $header = fgetcsv($handle, 0, ';');
        $header = array_map('trim', $header);

        $created = 0;
        $updated = 0;
        $errors  = [];
        $row = 1;

        while (($data = fgetcsv($handle, 0, ';')) !== false) {
            $row++;
            if (count($data) < 2) {
                continue;
            }

            $record = array_combine(array_slice($header, 0, count($data)), $data);
            $sku = trim($record['sku'] ?? '');
            $name = trim($record['name'] ?? '');

            if (! $sku || ! $name) {
                $errors[] = "Строка {$row}: пропущен SKU или название";
                continue;
            }

            try {
                $categorySlug = trim($record['category_slug'] ?? '');
                $category = $categorySlug
                    ? Category::where('slug', $categorySlug)->first()
                    : null;

                $product = Product::updateOrCreate(
                    ['sku' => $sku],
                    [
                        'name_ru'          => $name,
                        'description_ru'   => $record['description'] ?? null,
                        'category_id'      => $category?->id,
                        'is_active'        => ($record['is_active'] ?? '1') === '1',
                        'is_visible_portal' => true,
                    ]
                );

                // Retail price UZS
                if (! empty($record['retail_price_uzs'])) {
                    $product->prices()->updateOrCreate(
                        ['type' => 'retail', 'currency' => 'UZS'],
                        [
                            'amount'    => (float) str_replace([' ', ','], ['', '.'], $record['retail_price_uzs']),
                            'is_active' => true,
                        ]
                    );
                }

                // Wholesale price UZS
                if (! empty($record['wholesale_price_uzs'])) {
                    $product->prices()->updateOrCreate(
                        ['type' => 'wholesale', 'currency' => 'UZS'],
                        [
                            'amount'    => (float) str_replace([' ', ','], ['', '.'], $record['wholesale_price_uzs']),
                            'is_active' => true,
                        ]
                    );
                }

                // Retail price USD
                if (! empty($record['retail_price_usd'])) {
                    $product->prices()->updateOrCreate(
                        ['type' => 'retail', 'currency' => 'USD'],
                        [
                            'amount'    => (float) str_replace([' ', ','], ['', '.'], $record['retail_price_usd']),
                            'is_active' => true,
                        ]
                    );
                }

                // Stock (main warehouse)
                if (isset($record['stock'])) {
                    $product->stock()->updateOrCreate(
                        ['warehouse' => 'main'],
                        ['quantity' => max(0, (int) $record['stock'])]
                    );
                }

                $product->wasRecentlyCreated ? $created++ : $updated++;

            } catch (\Exception $e) {
                $errors[] = "Строка {$row}: " . $e->getMessage();
            }
        }

        fclose($handle);

        $message = "Импорт завершён: создано {$created}, обновлено {$updated}";
        if ($errors) {
            $message .= ', ошибок ' . count($errors);
        }

        return redirect()->route('admin.catalog.products.index')
            ->with('success', $message)
            ->with('import_errors', $errors);
    }
}
