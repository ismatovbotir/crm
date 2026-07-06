<div>
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-bold text-gray-900">Коммерческие предложения</h1>
            <p class="text-sm text-gray-500 mt-0.5">Ваши КП от менеджеров RSG</p>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-4 px-4 py-3 bg-success-50 border border-success-200 rounded-lg text-sm text-success-700">
            {{ session('success') }}
        </div>
    @endif

    {{-- Filter bar --}}
    <div class="mb-4">
        <select wire:model.live="statusFilter"
                class="text-sm border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500 bg-white">
            <option value="">Все статусы</option>
            <option value="sent">Отправлено</option>
            <option value="viewed">Просмотрено</option>
            <option value="accepted">Принято</option>
            <option value="rejected">Отклонено</option>
            <option value="expired">Истекло</option>
        </select>
    </div>

    <x-card :padding="false">
        <table class="w-full text-sm">
            <thead class="border-b border-gray-100 bg-gray-50">
                <tr>
                    <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wide">Номер</th>
                    <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wide">Дата</th>
                    <th class="px-5 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wide">Сумма</th>
                    <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wide hidden md:table-cell">Действ. до</th>
                    <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wide">Статус</th>
                    <th class="px-5 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($quotes as $q)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-5 py-3 font-medium text-gray-900">{{ $q->number }}</td>
                        <td class="px-5 py-3 text-gray-500">{{ $q->created_at->format('d.m.Y') }}</td>
                        <td class="px-5 py-3 text-right font-medium text-gray-900">
                            {{ number_format($q->total, 0, '.', ' ') }}
                            <span class="text-xs text-gray-400 font-normal">{{ $q->currency }}</span>
                        </td>
                        <td class="px-5 py-3 text-gray-500 hidden md:table-cell">
                            {{ $q->valid_until?->format('d.m.Y') ?? '—' }}
                        </td>
                        <td class="px-5 py-3"><x-quote-status-badge :status="$q->status" /></td>
                        <td class="px-5 py-3 text-right">
                            <a href="/portal/quotes/{{ $q->id }}"
                               class="text-primary-600 hover:text-primary-800 text-xs font-medium">
                                Открыть →
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="py-12 text-center text-gray-400 text-sm">
                            Коммерческих предложений нет
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        @if($quotes->hasPages())
            <div class="px-5 py-3 border-t border-gray-100">
                {{ $quotes->links() }}
            </div>
        @endif
    </x-card>
</div>
