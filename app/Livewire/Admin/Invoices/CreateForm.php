<?php

namespace App\Livewire\Admin\Invoices;

use App\Models\Catalog\Product;
use App\Models\Customer\Customer;
use App\Models\Invoice\Invoice;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class CreateForm extends Component
{
    public ?int $customer_id = null;
    public string $currency  = 'UZS';
    public string $due_date  = '';
    public float  $tax_rate  = 0;
    public string $notes     = '';
    public array  $items     = [];

    // Customer search
    public string $customerQuery        = '';
    public string $selectedCustomerName = '';
    public array  $customerResults      = [];

    public function mount(): void
    {
        $this->authorize('create', Invoice::class);
        $this->due_date = now()->addDays(14)->toDateString();
    }

    protected function rules(): array
    {
        return [
            'customer_id'        => 'required|exists:customers,id',
            'currency'           => 'required|in:UZS,USD',
            'due_date'           => 'required|date',
            'tax_rate'           => 'numeric|min:0|max:100',
            'notes'              => 'nullable|string|max:5000',
            'items'              => 'required|array|min:1',
            'items.*.name'       => 'required|string|max:255',
            'items.*.quantity'   => 'required|numeric|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
        ];
    }

    protected function messages(): array
    {
        return [
            'customer_id.required'       => 'Выберите клиента.',
            'items.min'                  => 'Добавьте хотя бы одну позицию.',
            'items.*.name.required'      => 'Укажите название позиции.',
            'items.*.quantity.required'  => 'Укажите количество.',
            'items.*.unit_price.required'=> 'Укажите цену.',
        ];
    }

    // ── Customer typeahead ───────────────────────────────────────────────────

    public function updatedCustomerQuery(): void
    {
        if (strlen($this->customerQuery) < 1) {
            $this->customerResults = [];
            return;
        }
        $q = mb_strtolower($this->customerQuery);
        $this->customerResults = Customer::active()
            ->where(fn ($query) => $query
                ->whereRaw('LOWER(name) LIKE ?', ["%{$q}%"])
                ->orWhereRaw('LOWER(inn) LIKE ?', ["%{$q}%"])
            )
            ->orderBy('name')
            ->limit(10)
            ->get(['id', 'name', 'inn'])
            ->map(fn ($c) => ['id' => $c->id, 'name' => $c->name, 'inn' => $c->inn ?? ''])
            ->toArray();
    }

    public function selectCustomer(int $id, string $name): void
    {
        $this->customer_id          = $id;
        $this->selectedCustomerName = $name;
        $this->customerQuery        = '';
        $this->customerResults      = [];
    }

    public function clearCustomer(): void
    {
        $this->customer_id          = null;
        $this->selectedCustomerName = '';
        $this->customerQuery        = '';
        $this->customerResults      = [];
    }

    // ── Items ────────────────────────────────────────────────────────────────

    public function addProduct(int $productId): void
    {
        foreach ($this->items as $i => $item) {
            if ((int) ($item['product_id'] ?? 0) === $productId) {
                $this->items[$i]['quantity'] = (float) $this->items[$i]['quantity'] + 1;
                return;
            }
        }

        $product = Product::with(['prices' => fn ($q) => $q->where('is_active', true)])->find($productId);
        if (! $product) return;

        $price = $product->prices
            ->where('type', 'retail')
            ->where('currency', $this->currency)
            ->first()
            ?? $product->prices->where('type', 'retail')->first();

        $this->items[] = [
            'product_id' => $product->id,
            'name'       => $product->name,
            'sku'        => $product->sku ?? '',
            'quantity'   => 1,
            'unit_price' => $price ? (float) $price->amount : 0,
        ];
    }

    public function removeItem(int $index): void
    {
        array_splice($this->items, $index, 1);
        $this->items = array_values($this->items);
    }

    public function getSubtotalProperty(): float
    {
        return collect($this->items)->sum(fn ($i) =>
            (float) ($i['quantity'] ?? 0) * (float) ($i['unit_price'] ?? 0)
        );
    }

    public function getTaxAmountProperty(): float
    {
        return round($this->subtotal * $this->tax_rate / 100, 2);
    }

    public function getTotalProperty(): float
    {
        return $this->subtotal + $this->taxAmount;
    }

    public function save(): void
    {
        $this->authorize('create', Invoice::class);
        $data = $this->validate();

        DB::transaction(function () use ($data) {
            $subtotal  = collect($data['items'])->sum(fn ($i) => (float) $i['quantity'] * (float) $i['unit_price']);
            $taxAmount = round($subtotal * $data['tax_rate'] / 100, 2);
            $total     = $subtotal + $taxAmount;

            $year   = now()->year;
            $count  = Invoice::whereYear('created_at', $year)->count() + 1;
            $number = 'ИНВ-' . $year . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);

            $invoice = Invoice::create([
                'number'      => $number,
                'customer_id' => $data['customer_id'],
                'manager_id'  => auth()->id(),
                'currency'    => $data['currency'],
                'status'      => 'draft',
                'due_date'    => $data['due_date'],
                'subtotal'    => $subtotal,
                'tax_rate'    => $data['tax_rate'],
                'tax_amount'  => $taxAmount,
                'total'       => $total,
                'paid_amount' => 0,
                'notes'       => $data['notes'] ?? null,
            ]);

            foreach ($data['items'] as $i => $item) {
                $lineTotal = (float) $item['quantity'] * (float) $item['unit_price'];
                $invoice->items()->create([
                    'product_id' => $item['product_id'] ?? null,
                    'name'       => $item['name'],
                    'sku'        => $item['sku'] ?? '',
                    'quantity'   => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'tax_rate'   => $data['tax_rate'],
                    'total'      => $lineTotal,
                    'sort_order' => $i,
                ]);
            }
        });

        session()->flash('success', 'Инвойс создан.');
        $this->dispatch('invoice-saved');
        $this->reset(['customer_id', 'notes', 'items',
                      'customerQuery', 'selectedCustomerName', 'customerResults']);
    }

    public function render()
    {
        $productsList = Product::where('is_active', true)
            ->with(['prices' => fn ($q) => $q->where('is_active', true), 'category.group'])
            ->orderBy('name_ru')
            ->get()
            ->map(fn ($p) => [
                'id'          => $p->id,
                'name'        => $p->name_ru ?? $p->name_uz ?? '',
                'sku'         => $p->sku ?? '',
                'group_name'  => $p->category?->group?->name_ru ?? '',
                'group_color' => $p->category?->group?->color ?? 'gray',
                'price_uzs'   => (float) ($p->prices->where('type', 'retail')->where('currency', 'UZS')->first()?->amount ?? 0),
                'price_usd'   => (float) ($p->prices->where('type', 'retail')->where('currency', 'USD')->first()?->amount ?? 0),
            ]);

        return view('livewire.admin.invoices.create-form', [
            'productsList' => $productsList,
            'subtotal'     => $this->subtotal,
            'taxAmount'    => $this->taxAmount,
            'total'        => $this->total,
        ]);
    }
}
