<div class="max-w-6xl mx-auto">
    {{-- Flash messages --}}
    @if(session('success'))<div class="mb-4 px-4 py-3 bg-success-50 border border-success-200 rounded-lg text-sm text-success-700">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="mb-4 px-4 py-3 bg-danger-50 border border-danger-200 rounded-lg text-sm text-danger-700">{{ session('error') }}</div>@endif

    {{-- Header --}}
    <div class="flex items-start justify-between mb-6">
        <div>
            <nav class="text-sm text-gray-500 mb-1">
                <a href="{{ route('admin.returns.index') }}" class="hover:text-primary-600">Возвраты</a>
                <span class="mx-1">/</span>
                <span class="text-gray-900">{{ $return->number }}</span>
            </nav>
            <h1 class="text-xl font-bold text-gray-900">{{ $return->number }}</h1>
            <p class="text-sm text-gray-500">{{ $return->customer?->name }}</p>
        </div>
        <div class="flex items-center gap-3">
            @php
            $statusColor = match($return->status) {
                'draft'      => 'gray',
                'approved'   => 'blue',
                'refunded'   => 'green',
                'cancelled'  => 'red',
                default      => 'gray',
            };
            $statusLabel = match($return->status) {
                'draft'      => 'Черновик',
                'approved'   => 'Подтверждён',
                'refunded'   => 'Возврат выполнен',
                'cancelled'  => 'Отменён',
                default      => $return->status,
            };
            @endphp
            <x-badge :color="$statusColor">{{ $statusLabel }}</x-badge>

            {{-- Draft actions --}}
            @if($return->status === 'draft')
            <button type="button" wire:click="approve"
                    wire:confirm="Подтвердить возврат {{ $return->number }}?"
                    class="inline-flex items-center gap-1.5 px-4 py-1.5 text-sm font-medium rounded-lg bg-primary-600 text-white hover:bg-primary-700 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                Подтвердить
            </button>
            <button type="button" wire:click="cancel"
                    wire:confirm="Отменить возврат {{ $return->number }}?"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium text-gray-600 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                Отменить
            </button>
            @endif

            {{-- Approved actions --}}
            @if($return->status === 'approved')
            <button type="button" wire:click="markRefunded"
                    wire:confirm="Отметить возврат как выполненный? Это действие нельзя отменить."
                    class="inline-flex items-center gap-1.5 px-4 py-1.5 text-sm font-medium rounded-lg bg-success-600 text-white hover:bg-success-700 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/>
                </svg>
                Выполнить возврат
            </button>
            <button type="button" wire:click="cancel"
                    wire:confirm="Отменить возврат {{ $return->number }}?"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium text-gray-600 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                Отменить
            </button>
            @endif
        </div>
    </div>

    {{-- Two-column layout --}}
    <div class="grid grid-cols-3 gap-6">

        {{-- Left: items --}}
        <div class="col-span-2 space-y-4">
            <x-card title="Позиции возврата" :padding="false">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b bg-gray-50/50">
                            <th class="text-left px-4 py-2.5 text-xs text-gray-500 font-semibold uppercase tracking-wide w-8">#</th>
                            <th class="text-left px-4 py-2.5 text-xs text-gray-500 font-semibold uppercase tracking-wide">Товар</th>
                            <th class="text-left px-4 py-2.5 text-xs text-gray-500 font-semibold uppercase tracking-wide">Артикул</th>
                            <th class="text-left px-4 py-2.5 text-xs text-gray-500 font-semibold uppercase tracking-wide">Серийный №</th>
                            <th class="text-right px-4 py-2.5 text-xs text-gray-500 font-semibold uppercase tracking-wide">Кол.</th>
                            <th class="text-right px-4 py-2.5 text-xs text-gray-500 font-semibold uppercase tracking-wide">Цена</th>
                            <th class="text-right px-4 py-2.5 text-xs text-gray-500 font-semibold uppercase tracking-wide">Итого</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($return->items as $i => $item)
                        <tr>
                            <td class="px-4 py-2.5 text-gray-400 text-xs">{{ $i + 1 }}</td>
                            <td class="px-4 py-2.5">
                                <p class="font-medium text-gray-900">{{ $item->name }}</p>
                            </td>
                            <td class="px-4 py-2.5">
                                @if($item->sku)
                                <span class="text-xs text-gray-400 font-mono">{{ $item->sku }}</span>
                                @else
                                <span class="text-gray-300">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-2.5">
                                @if($item->serial_number)
                                <span class="inline-block font-mono text-xs bg-gray-100 text-gray-700 px-2 py-0.5 rounded">{{ $item->serial_number }}</span>
                                @else
                                <span class="text-gray-300">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-2.5 text-right text-gray-700">{{ $item->quantity }}</td>
                            <td class="px-4 py-2.5 text-right text-gray-700">{{ number_format($item->unit_price, 0, '.', ' ') }}</td>
                            <td class="px-4 py-2.5 text-right font-semibold text-gray-900">{{ number_format($item->quantity * $item->unit_price, 0, '.', ' ') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-sm text-gray-400">Позиции не добавлены</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </x-card>
        </div>

        {{-- Right sidebar --}}
        <div class="space-y-4">

            {{-- Return info --}}
            <x-card title="Информация о возврате">
                <dl class="space-y-2.5 text-sm">
                    <div>
                        <dt class="text-xs text-gray-400 uppercase font-medium">Клиент</dt>
                        <dd class="text-gray-900 font-medium mt-0.5">{{ $return->customer?->name ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-gray-400 uppercase font-medium">Причина</dt>
                        <dd class="text-gray-700 mt-0.5">{{ match($return->reason) {
                            'warranty'     => 'Гарантия',
                            'defect'       => 'Брак',
                            'changed_mind' => 'Передумал',
                            'other'        => 'Другое',
                            default        => $return->reason,
                        } }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-gray-400 uppercase font-medium">Сумма к возврату</dt>
                        <dd class="mt-0.5">
                            <span class="text-lg font-bold text-success-700">{{ number_format($return->refund_amount, 0, '.', ' ') }}</span>
                            <span class="text-sm text-gray-500 ml-1">{{ $return->currency }}</span>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-xs text-gray-400 uppercase font-medium">Создан</dt>
                        <dd class="text-gray-700 mt-0.5">{{ $return->created_at->format('d.m.Y H:i') }}</dd>
                    </div>
                    @if($return->refunded_at)
                    <div>
                        <dt class="text-xs text-gray-400 uppercase font-medium">Возврат выполнен</dt>
                        <dd class="text-success-700 font-medium mt-0.5">{{ $return->refunded_at->format('d.m.Y H:i') }}</dd>
                    </div>
                    @endif
                    @if($return->notes)
                    <div>
                        <dt class="text-xs text-gray-400 uppercase font-medium">Примечание</dt>
                        <dd class="text-gray-700 mt-0.5 text-xs leading-relaxed whitespace-pre-line">{{ $return->notes }}</dd>
                    </div>
                    @endif
                </dl>
            </x-card>

            {{-- Links card --}}
            @if($return->sell || $return->invoice || $return->ticket)
            <x-card title="Связанные документы">
                <dl class="space-y-2.5 text-sm">
                    @if($return->sell)
                    <div>
                        <dt class="text-xs text-gray-400 uppercase font-medium">Отгрузка</dt>
                        <dd class="mt-0.5">
                            <a href="{{ route('admin.sells.show', $return->sell) }}"
                               class="inline-flex items-center gap-1 font-mono text-sm text-primary-600 hover:text-primary-700 hover:underline">
                                {{ $return->sell->number }}
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </a>
                        </dd>
                    </div>
                    @endif
                    @if($return->invoice)
                    <div>
                        <dt class="text-xs text-gray-400 uppercase font-medium">Инвойс</dt>
                        <dd class="mt-0.5">
                            <a href="{{ route('admin.invoices.show', $return->invoice) }}"
                               class="inline-flex items-center gap-1 font-mono text-sm text-primary-600 hover:text-primary-700 hover:underline">
                                {{ $return->invoice->number }}
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </a>
                        </dd>
                    </div>
                    @endif
                    @if($return->ticket)
                    <div>
                        <dt class="text-xs text-gray-400 uppercase font-medium">Тикет</dt>
                        <dd class="mt-0.5">
                            <a href="{{ route('admin.tickets.show', $return->ticket) }}"
                               class="inline-flex items-center gap-1 font-mono text-sm text-primary-600 hover:text-primary-700 hover:underline">
                                {{ $return->ticket->number }}
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </a>
                        </dd>
                    </div>
                    @endif
                </dl>
            </x-card>
            @endif

        </div>
    </div>
</div>
