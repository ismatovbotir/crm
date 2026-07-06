<?php

namespace App\Livewire\Admin\Documents;

use App\Models\Catalog\BusinessTypeRecommendation;
use App\Models\Catalog\Product;
use App\Models\Customer\Contact;
use App\Models\Customer\Customer;
use App\Models\Invoice\Invoice;
use App\Models\Quote\Quote;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Component;

class CreateForm extends Component
{
    // ── Type switch ──────────────────────────────────────────────────────────

    public string $type = 'quote'; // 'quote' | 'invoice'

    // ── Shared fields ────────────────────────────────────────────────────────

    public ?int   $customer_id     = null;
    public ?int   $contact_id      = null;
    public string $currency        = 'UZS';
    public string $exchange_rate   = '';
    public string $issue_date      = '';
    public string $notes           = '';
    public string $global_discount_type  = 'percent'; // 'percent' | 'sum'
    public float  $global_discount_value = 0;
    public float  $vat_percent           = 0;

    // ── Quote-only fields ────────────────────────────────────────────────────

    public string $valid_until = '';
    public string $terms       = '';

    // ── Invoice-only fields ──────────────────────────────────────────────────

    public string $due_date = '';

    // ── Customer search ──────────────────────────────────────────────────────

    public string $customerQuery        = '';
    public string $selectedCustomerName = '';
    public array  $customerResults      = [];

    // ── Line items ───────────────────────────────────────────────────────────

    // Each item: product_id, name, sku, description, quantity, unit_price,
    //            discount_value, discount_type, final_price, total
    public array $items = [];

    // ── Recommendations (quote only) ─────────────────────────────────────────

    public array $recommendations = [];

    // ── Lifecycle ────────────────────────────────────────────────────────────

    public function mount(string $type = 'quote', ?int $customerId = null): void
    {
        $this->authorize(
            'create',
            $type === 'quote' ? Quote::class : Invoice::class
        );

        $this->type        = $type;
        $this->issue_date  = today()->toDateString();
        $this->valid_until = now()->addDays(20)->toDateString();
        $this->due_date    = now()->addDays(14)->toDateString();

        if ($customerId) {
            $customer = Customer::find($customerId);
            if ($customer) {
                $this->customer_id          = $customer->id;
                $this->selectedCustomerName = $customer->name;
                if ($type === 'quote') {
                    $this->loadRecommendations();
                }
            }
        }
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
        $this->loadRecommendations();
    }

    public function clearCustomer(): void
    {
        $this->customer_id          = null;
        $this->contact_id           = null;
        $this->selectedCustomerName = '';
        $this->customerQuery        = '';
        $this->customerResults      = [];
        $this->recommendations      = [];
    }

    // ── Customer change: load business-type recommendations ──────────────────

    public function updatedCustomerId(): void
    {
        $this->loadRecommendations();
    }

    protected function loadRecommendations(): void
    {
        if ($this->type !== 'quote') {
            $this->recommendations = [];
            return;
        }

        $this->recommendations = [];

        if (! $this->customer_id) {
            return;
        }

        $customer = Customer::find($this->customer_id);

        if (! $customer?->business_type_id) {
            return;
        }

        $this->recommendations = BusinessTypeRecommendation::where('business_type_id', $customer->business_type_id)
            ->with(['product' => fn ($q) => $q->with([
                'category.group',
                'prices' => fn ($pq) => $pq->where('is_active', true),
            ])])
            ->orderByRaw("CASE priority WHEN 'required' THEN 1 WHEN 'recommended' THEN 2 ELSE 3 END")
            ->orderBy('sort_order')
            ->get()
            ->filter(fn ($r) => $r->product !== null)
            ->map(fn ($r) => [
                'product_id' => $r->product_id,
                'name'       => $r->product->name_ru,
                'sku'        => $r->product->sku ?? '',
                'priority'   => $r->priority,
                'group_name' => $r->product->category?->group?->name_ru ?? '',
                'price_uzs'  => (float) ($r->product->prices->where('type', 'retail')->where('currency', 'UZS')->first()?->amount ?? 0),
                'price_usd'  => (float) ($r->product->prices->where('type', 'retail')->where('currency', 'USD')->first()?->amount ?? 0),
            ])
            ->values()
            ->toArray();
    }

    // ── Reset discount value when type changes ───────────────────────────────

    public function updatedGlobalDiscountType(): void
    {
        $this->global_discount_value = 0;
    }

    // ── Recalculate on any item field change ─────────────────────────────────

    public function updated(string $name): void
    {
        if (! str_starts_with($name, 'items.')) {
            return;
        }

        $parts = explode('.', $name);
        if (count($parts) !== 3) {
            return;
        }

        [, $idx, $field] = $parts;
        $index = (int) $idx;

        if (in_array($field, ['quantity', 'unit_price', 'discount_value'])) {
            $this->recalculateTotal($index);
        } elseif ($field === 'final_price') {
            $this->recalculateFromFinalPrice($index);
        } elseif ($field === 'total') {
            $this->recalculateDiscount($index);
        } elseif ($field === 'discount_type') {
            $this->items[$index]['discount_value'] = 0;
            $this->recalculateTotal($index);
        }
    }

    private function recalculateTotal(int $i): void
    {
        $qty   = max(0, (float) ($this->items[$i]['quantity']       ?? 0));
        $price = max(0, (float) ($this->items[$i]['unit_price']     ?? 0));
        $disc  = max(0, (float) ($this->items[$i]['discount_value'] ?? 0));
        $type  = $this->items[$i]['discount_type'] ?? 'percent';

        $finalPrice = $type === 'percent'
            ? $price * (1 - min($disc, 100) / 100)
            : max(0, $price - $disc);

        $this->items[$i]['final_price'] = round($finalPrice, 2);
        $this->items[$i]['total']       = round($qty * $finalPrice, 2);
    }

    private function recalculateFromFinalPrice(int $i): void
    {
        $qty        = max(1, (float) ($this->items[$i]['quantity']    ?? 1));
        $price      = max(0, (float) ($this->items[$i]['unit_price']  ?? 0));
        $finalPrice = max(0, (float) ($this->items[$i]['final_price'] ?? 0));
        $type       = $this->items[$i]['discount_type'] ?? 'percent';

        $this->items[$i]['total'] = round($qty * $finalPrice, 2);

        if ($price > 0) {
            $this->items[$i]['discount_value'] = $type === 'percent'
                ? round(max(0, min(100, (1 - $finalPrice / $price) * 100)), 2)
                : round(max(0, $price - $finalPrice), 2);
        }
    }

    private function recalculateDiscount(int $i): void
    {
        $qty   = max(1, (float) ($this->items[$i]['quantity']   ?? 1));
        $price = max(0, (float) ($this->items[$i]['unit_price'] ?? 0));
        $total = max(0, (float) ($this->items[$i]['total']      ?? 0));
        $type  = $this->items[$i]['discount_type'] ?? 'percent';

        $finalPrice = $qty > 0 ? $total / $qty : 0;
        $this->items[$i]['final_price'] = round($finalPrice, 2);

        if ($price > 0) {
            $this->items[$i]['discount_value'] = $type === 'percent'
                ? round(max(0, min(100, (1 - $finalPrice / $price) * 100)), 2)
                : round(max(0, $price - $finalPrice), 2);
        }
    }

    // ── Item management ──────────────────────────────────────────────────────

    public function addProduct(int $productId): void
    {
        // If already in list — just increment qty
        foreach ($this->items as $i => $item) {
            if ((int) ($item['product_id'] ?? 0) === $productId) {
                $this->items[$i]['quantity'] = (int) $this->items[$i]['quantity'] + 1;
                $this->recalculateTotal($i);
                return;
            }
        }

        $product = Product::with('prices')->find($productId);
        if (! $product) {
            return;
        }

        $price = $product->prices
            ->where('type', 'retail')
            ->where('currency', $this->currency)
            ->first();

        $unitPrice = $price ? (float) $price->amount : 0;

        $this->items[] = [
            'product_id'     => $product->id,
            'name'           => $product->name_ru,
            'sku'            => $product->sku ?? '',
            'description'    => '',
            'quantity'       => 1,
            'unit_price'     => $unitPrice,
            'discount_value' => 0,
            'discount_type'  => 'percent',
            'final_price'    => $unitPrice,
            'total'          => $unitPrice,
        ];
    }

    public function removeItem(int $index): void
    {
        array_splice($this->items, $index, 1);
        $this->items = array_values($this->items);
    }

    // ── Computed totals ──────────────────────────────────────────────────────

    #[Computed]
    public function grossSubtotal(): float
    {
        return collect($this->items)->sum(fn ($item) =>
            (float) ($item['quantity'] ?? 0) * (float) ($item['unit_price'] ?? 0)
        );
    }

    #[Computed]
    public function itemsDiscount(): float
    {
        return max(0, $this->grossSubtotal - $this->subtotal);
    }

    #[Computed]
    public function subtotal(): float
    {
        return collect($this->items)->sum(fn ($item) => (float) ($item['total'] ?? 0));
    }

    #[Computed]
    public function grandTotal(): float
    {
        $sub  = $this->subtotal;
        $disc = $this->global_discount_type === 'percent'
            ? $sub * (min((float) $this->global_discount_value, 100) / 100)
            : min((float) $this->global_discount_value, $sub);

        return $sub - $disc;
    }

    protected function globalDiscountAmount(): float
    {
        $sub = $this->subtotal;

        return $this->global_discount_type === 'percent'
            ? $sub * (min((float) $this->global_discount_value, 100) / 100)
            : min((float) $this->global_discount_value, $sub);
    }

    // ── Validation ───────────────────────────────────────────────────────────

    protected function rules(): array
    {
        $rules = [
            'customer_id'              => 'required|exists:customers,id',
            'contact_id'               => 'nullable|exists:contacts,id',
            'currency'                 => 'required|in:UZS,USD',
            'exchange_rate'            => 'nullable|numeric|min:0',
            'issue_date'               => 'required|date',
            'notes'                    => 'nullable|string|max:5000',
            'global_discount_type'     => 'in:percent,sum',
            'global_discount_value'    => 'numeric|min:0',
            'items'                    => 'required|array|min:1',
            'items.*.product_id'       => 'nullable|exists:products,id',
            'items.*.name'             => 'required|string|max:255',
            'items.*.sku'              => 'nullable|string|max:100',
            'items.*.quantity'         => 'required|integer|min:1',
            'items.*.unit_price'       => 'required|numeric|min:0',
            'items.*.discount_value'   => 'numeric|min:0',
            'items.*.discount_type'    => 'in:percent,sum',
            'items.*.final_price'      => 'numeric|min:0',
            'items.*.total'            => 'numeric|min:0',
        ];

        if ($this->type === 'quote') {
            $rules['valid_until'] = 'required|date|after:today';
            $rules['terms']       = 'nullable|string|max:5000';
        } else {
            $rules['due_date'] = 'required|date';
        }

        return $rules;
    }

    protected function messages(): array
    {
        return [
            'customer_id.required'        => 'Выберите клиента.',
            'valid_until.required'         => 'Укажите срок действия КП.',
            'valid_until.after'            => 'Срок действия должен быть в будущем.',
            'due_date.required'            => 'Укажите срок оплаты.',
            'items.required'               => 'Добавьте хотя бы одну позицию.',
            'items.min'                    => 'Добавьте хотя бы одну позицию.',
            'items.*.name.required'        => 'Укажите название позиции.',
            'items.*.quantity.required'    => 'Укажите количество.',
            'items.*.unit_price.required'  => 'Укажите цену.',
        ];
    }

    // ── Save (dispatcher) ────────────────────────────────────────────────────

    public function save(): void
    {
        $this->type === 'quote' ? $this->saveQuote() : $this->saveInvoice();
    }

    // ── Save Quote ───────────────────────────────────────────────────────────

    private function saveQuote(): void
    {
        $this->authorize('create', Quote::class);
        $data = $this->validate();

        DB::transaction(function () use ($data) {
            $subtotal       = collect($data['items'])->sum(fn ($i) => (float) ($i['total'] ?? 0));
            $discountAmount = $data['global_discount_type'] === 'percent'
                ? $subtotal * (min((float) $data['global_discount_value'], 100) / 100)
                : min((float) $data['global_discount_value'], $subtotal);
            $afterDiscount  = $subtotal - $discountAmount;
            $total          = $afterDiscount;

            $year   = now()->year;
            $count  = Quote::whereYear('created_at', $year)->withTrashed()->count() + 1;
            $number = 'КП-' . $year . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);

            $quote = Quote::create([
                'number'           => $number,
                'customer_id'      => $data['customer_id'],
                'contact_id'       => $data['contact_id'] ?? null,
                'manager_id'       => auth()->id(),
                'status'           => 'draft',
                'currency'         => $data['currency'],
                'exchange_rate'    => $data['exchange_rate'] ?: 1,
                'issue_date'       => $data['issue_date'],
                'valid_until'      => $data['valid_until'],
                'subtotal'         => $subtotal,
                'discount_percent' => $subtotal > 0 ? round($discountAmount / $subtotal * 100, 4) : 0,
                'discount_total'   => $discountAmount,
                'vat_percent'      => 0,
                'vat_amount'       => 0,
                'total'            => $total,
                'terms'            => $data['terms'] ?? null,
                'notes'            => $data['notes'] ?? null,
                'version'          => 1,
            ]);

            foreach ($data['items'] as $idx => $item) {
                $discVal  = (float) ($item['discount_value'] ?? 0);
                $discType = $item['discount_type'] ?? 'percent';
                $base     = (float) $item['quantity'] * (float) $item['unit_price'];
                $discPct  = $discType === 'percent'
                    ? $discVal
                    : ($base > 0 ? round($discVal / $base * 100, 4) : 0);

                $quote->items()->create([
                    'product_id'       => $item['product_id'] ?? null,
                    'name'             => $item['name'],
                    'sku'              => $item['sku'] ?? null,
                    'description'      => $item['description'] ?? null,
                    'quantity'         => $item['quantity'],
                    'unit_price'       => $item['unit_price'],
                    'discount_percent' => $discPct,
                    'final_price'      => (float) ($item['final_price'] ?? $item['unit_price']),
                    'total'            => (float) ($item['total'] ?? 0),
                    'sort_order'       => $idx,
                ]);
            }

            $quote->load('items');
            $quote->versions()->create([
                'version'        => 1,
                'items_snapshot' => $quote->items->toArray(),
                'total'          => $total,
                'created_by'     => auth()->id(),
            ]);
        });

        session()->flash('success', 'КП создано.');
        $this->dispatch('quote-saved');
        $this->reset([
            'customer_id', 'contact_id', 'terms', 'notes', 'items',
            'global_discount_type', 'global_discount_value', 'exchange_rate',
            'customerQuery', 'selectedCustomerName', 'customerResults', 'recommendations',
        ]);
        $this->issue_date  = today()->toDateString();
        $this->valid_until = now()->addDays(20)->toDateString();
    }

    // ── Save Invoice ─────────────────────────────────────────────────────────

    private function saveInvoice(): void
    {
        $this->authorize('create', Invoice::class);
        $data = $this->validate();

        DB::transaction(function () use ($data) {
            $subtotal       = collect($data['items'])->sum(fn ($i) => (float) ($i['total'] ?? 0));
            $discountAmount = $this->globalDiscountAmount();
            $total          = $subtotal - $discountAmount;

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
                'tax_rate'    => 0,
                'tax_amount'  => 0,
                'total'       => $total,
                'paid_amount' => 0,
                'notes'       => $data['notes'] ?? null,
            ]);

            foreach ($data['items'] as $i => $item) {
                $invoice->items()->create([
                    'product_id' => $item['product_id'] ?? null,
                    'name'       => $item['name'],
                    'sku'        => $item['sku'] ?? null,
                    'quantity'   => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'tax_rate'   => 0,
                    'total'      => (float) ($item['total'] ?? 0),
                    'sort_order' => $i,
                ]);
            }
        });

        session()->flash('success', 'Инвойс создан.');
        $this->dispatch('invoice-saved');
        $this->reset([
            'customer_id', 'contact_id', 'notes', 'items',
            'global_discount_type', 'global_discount_value', 'exchange_rate',
            'customerQuery', 'selectedCustomerName', 'customerResults', 'recommendations',
        ]);
        $this->issue_date = today()->toDateString();
        $this->due_date   = now()->addDays(14)->toDateString();
    }

    // ── Render ───────────────────────────────────────────────────────────────

    public function render()
    {
        $contacts = $this->customer_id
            ? Contact::where('customer_id', $this->customer_id)->get()
            : collect();

        $productsList = Product::where('is_active', true)
            ->with([
                'prices'         => fn ($q) => $q->where('type', 'retail')->where('is_active', true),
                'category.group',
            ])
            ->orderBy('name_ru')
            ->get()
            ->map(fn ($p) => [
                'id'          => $p->id,
                'name'        => $p->name_ru,
                'sku'         => $p->sku ?? '',
                'group_name'  => $p->category?->group?->name_ru ?? '',
                'group_color' => $p->category?->group?->color ?? 'gray',
                'price_uzs'   => (float) ($p->prices->where('currency', 'UZS')->first()?->amount ?? 0),
                'price_usd'   => (float) ($p->prices->where('currency', 'USD')->first()?->amount ?? 0),
            ]);

        return view('livewire.admin.documents.create-form', [
            'contacts'             => $contacts,
            'productsList'         => $productsList,
            'grossSubtotal'        => $this->grossSubtotal(),
            'itemsDiscount'        => $this->itemsDiscount(),
            'subtotal'             => $this->subtotal(),
            'grandTotal'           => $this->grandTotal(),
            'globalDiscountAmount' => $this->globalDiscountAmount(),
        ]);
    }
}
