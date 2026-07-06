<div>
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-bold text-gray-900">Заявки на оборудование</h1>
            <p class="text-sm text-gray-500 mt-0.5">Запросы от клиентов на подбор оборудования</p>
        </div>
    </div>

    {{-- Filters --}}
    <x-card class="mb-4" :padding="false">
        <div class="flex flex-wrap gap-3 p-4">
            <div class="flex-1 min-w-48">
                <input wire:model.live.debounce.300ms="search"
                       placeholder="Поиск по теме или клиенту..."
                       class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
            </div>
            <select wire:model.live="statusFilter"
                    class="rounded-lg border border-gray-300 px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-primary-500">
                <option value="">Все статусы</option>
                <option value="submitted">Новая</option>
                <option value="under_review">На рассмотрении</option>
                <option value="quoted">КП отправлено</option>
                <option value="closed">Закрыта</option>
            </select>
            <select wire:model.live="managerFilter"
                    class="rounded-lg border border-gray-300 px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-primary-500">
                <option value="">Все менеджеры</option>
                @foreach($this->managers as $mgr)
                    <option value="{{ $mgr->id }}">{{ $mgr->name }}</option>
                @endforeach
            </select>
        </div>
    </x-card>

    {{-- Table --}}
    <x-card :padding="false">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b bg-gray-50/50">
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Тема</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide hidden md:table-cell">Клиент</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide hidden lg:table-cell">Менеджер</th>
                    <th class="text-right px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide hidden lg:table-cell">Бюджет</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Статус</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide hidden md:table-cell">Дата</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($this->requests as $req)
                    @php
                        $statusColors = [
                            'submitted'    => 'bg-primary-100 text-primary-700',
                            'under_review' => 'bg-warning-100 text-warning-700',
                            'quoted'       => 'bg-blue-100 text-blue-700',
                            'closed'       => 'bg-gray-100 text-gray-500',
                        ];
                        $statusLabels = [
                            'submitted'    => 'Новая',
                            'under_review' => 'На рассмотрении',
                            'quoted'       => 'КП отправлено',
                            'closed'       => 'Закрыта',
                        ];
                    @endphp
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-4 py-3">
                            <a href="{{ route('admin.equipment-requests.show', $req) }}"
                               class="font-medium text-gray-900 hover:text-primary-600">
                                {{ Str::limit($req->subject, 60) }}
                            </a>
                            @if($req->needed_by)
                                <p class="text-xs text-gray-400 mt-0.5">Нужно к: {{ $req->needed_by->format('d.m.Y') }}</p>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-gray-600 hidden md:table-cell">{{ $req->customer?->name ?? '—' }}</td>
                        <td class="px-4 py-3 text-gray-500 text-xs hidden lg:table-cell">{{ $req->manager?->name ?? '—' }}</td>
                        <td class="px-4 py-3 text-right text-gray-700 hidden lg:table-cell">
                            {{ $req->budget ? number_format($req->budget, 0, '.', ' ') : '—' }}
                        </td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $statusColors[$req->status] ?? 'bg-gray-100 text-gray-600' }}">
                                {{ $statusLabels[$req->status] ?? $req->status }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-xs text-gray-400 hidden md:table-cell">{{ $req->created_at->format('d.m.Y') }}</td>
                        <td class="px-4 py-3">
                            <a href="{{ route('admin.equipment-requests.show', $req) }}"
                               class="p-1.5 rounded text-gray-400 hover:text-primary-600 hover:bg-primary-50 inline-flex transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-12 text-center text-gray-400">Заявок нет</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        @if($this->requests->hasPages())
            <div class="px-4 py-3 border-t border-gray-100">{{ $this->requests->links() }}</div>
        @endif
    </x-card>
</div>
