<div class="max-w-4xl mx-auto">
    {{-- Header --}}
    <div class="mb-6">
        <nav class="text-sm text-gray-500 mb-1">
            <a href="{{ route('admin.returns.index') }}" class="hover:text-primary-600">Возвраты</a>
            <span class="mx-1">/</span>
            <span class="text-gray-900">Новый возврат</span>
        </nav>
        <h1 class="text-xl font-bold text-gray-900">Новый возврат</h1>
    </div>

    {{-- Flash --}}
    @if(session('error'))
    <div class="mb-4 px-4 py-3 bg-danger-50 border border-danger-200 rounded-lg text-sm text-danger-700">{{ session('error') }}</div>
    @endif

    {{-- Section 1: Customer + Sell --}}
    <x-card title="Клиент и отгрузка" class="mb-4">
        <div class="grid grid-cols-2 gap-4">
            {{-- Customer select --}}
            <x-select label="Клиент" wire:model.live="customer_id" :error="$errors->first('customer_id')">
                <option value="">— выберите клиента —</option>
                @foreach($customers as $c)
                <option value="{{ $c->id }}">{{ $c->name }}</option>
                @endforeach
            </x-select>

            {{-- Sell select (shows after customer chosen) --}}
            @if($customer_id)
            <x-select label="Отгрузка (Sell)" wire:model.live="selected_sell" :error="$errors->first('selected_sell')">
                <option value="">— выберите отгрузку —</option>
                @foreach($availableSells as $s)
                <option value="{{ $s['id'] }}">{{ $s['number'] }} — {{ \Carbon\Carbon::parse($s['sold_at'])->format('d.m.Y') }}</option>
                @endforeach
            </x-select>
            @else
            <div class="flex items-end pb-0.5">
                <p class="text-sm text-gray-400 italic">Сначала выберите клиента</p>
            </div>
            @endif
        </div>
    </x-card>

    {{-- Section 2: Return lines --}}
    @if(!empty($lines))
    <x-card title="Позиции к возврату" :padding="false" class="mb-4">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b bg-gray-50/60">
                    <th class="px-4 py-2.5 w-8"></th>
                    <th class="text-left px-4 py-2.5 text-xs font-semibold text-gray-500 uppercase tracking-wide">Товар</th>
                    <th class="text-right px-4 py-2.5 text-xs font-semibold text-gray-500 uppercase tracking-wide">Кол.</th>
                    <th class="text-right px-4 py-2.5 text-xs font-semibold text-gray-500 uppercase tracking-wide">Цена</th>
                    <th class="text-left px-4 py-2.5 text-xs font-semibold text-gray-500 uppercase tracking-wide">Серийный №</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach($lines as $idx => $line)
                <tr class="{{ empty($line['checked']) ? 'opacity-50' : '' }}">
                    <td class="px-4 py-2.5">
                        <input type="checkbox" wire:model.live="lines.{{ $idx }}.checked"
                               class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                    </td>
                    <td class="px-4 py-2.5">
                        <p class="font-medium text-gray-900">{{ $line['name'] }}</p>
                        @if($line['sku'])<p class="text-xs text-gray-400 font-mono">{{ $line['sku'] }}</p>@endif
                    </td>
                    <td class="px-4 py-2.5 text-right">
                        @if($line['is_serial'])
                            <span class="text-sm text-gray-500">1 шт</span>
                        @else
                            <input type="number"
                                   wire:model.live="lines.{{ $idx }}.quantity"
                                   min="0.001" max="{{ $line['max_qty'] }}" step="0.001"
                                   class="w-20 rounded border border-gray-300 px-2 py-1 text-sm text-right focus:outline-none focus:ring-2 focus:ring-primary-500"
                                   {{ empty($line['checked']) ? 'disabled' : '' }}>
                        @endif
                    </td>
                    <td class="px-4 py-2.5 text-right text-sm text-gray-700">
                        {{ number_format($line['unit_price'], 0, '.', ' ') }}
                    </td>
                    <td class="px-4 py-2.5">
                        @if($line['is_serial'])
                            <input type="text"
                                   wire:model="lines.{{ $idx }}.serial_number"
                                   placeholder="Введите серийный номер"
                                   class="w-full rounded border border-gray-300 px-2 py-1.5 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-primary-500 {{ empty($line['checked']) ? 'opacity-50' : '' }}"
                                   {{ empty($line['checked']) ? 'disabled' : '' }}>
                            @error("lines.{$idx}.serial_number")
                            <p class="text-xs text-danger-600 mt-1">{{ $message }}</p>
                            @enderror
                        @else
                            <span class="text-gray-300">—</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @error('lines')<p class="px-4 py-2 text-sm text-danger-600">{{ $message }}</p>@enderror
    </x-card>
    @endif

    {{-- Section 3: Return details --}}
    <x-card title="Детали возврата" class="mb-4">
        <div class="grid grid-cols-2 gap-4">
            <x-select label="Причина возврата" wire:model="reason" :error="$errors->first('reason')">
                <option value="">— выберите причину —</option>
                <option value="warranty">Гарантия</option>
                <option value="defect">Брак</option>
                <option value="changed_mind">Передумал</option>
                <option value="other">Другое</option>
            </x-select>
            <div class="flex gap-2">
                <div class="flex-1">
                    <x-input label="Сумма к возврату" type="number" wire:model="refund_amount" :error="$errors->first('refund_amount')" />
                </div>
                <x-select label="Валюта" wire:model="currency" class="w-28">
                    <option value="UZS">UZS</option>
                    <option value="USD">USD</option>
                </x-select>
            </div>
        </div>
        <div class="mt-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Примечание</label>
            <textarea wire:model="notes" rows="3"
                      class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500"
                      placeholder="Причины возврата, состояние товара..."></textarea>
        </div>
    </x-card>

    {{-- Footer --}}
    <div class="flex justify-end gap-3">
        <a href="{{ route('admin.returns.index') }}"
           class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
            Отмена
        </a>
        <x-button wire:click="save" wire:loading.attr="disabled">
            <span wire:loading.remove>Создать возврат</span>
            <span wire:loading>Сохранение...</span>
        </x-button>
    </div>
</div>
