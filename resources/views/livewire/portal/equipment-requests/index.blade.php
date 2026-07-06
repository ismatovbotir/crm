<div>
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-bold text-gray-900">Заявки на оборудование</h1>
            <p class="text-sm text-gray-500 mt-0.5">История ваших заявок на подбор оборудования</p>
        </div>
        <a href="{{ route('portal.equipment-requests.create') }}">
            <x-button>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Новая заявка
            </x-button>
        </a>
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
            <option value="submitted">Новая</option>
            <option value="under_review">На рассмотрении</option>
            <option value="quoted">КП отправлено</option>
            <option value="closed">Закрыта</option>
        </select>
    </div>

    <x-card :padding="false">
        <table class="w-full text-sm">
            <thead class="border-b border-gray-100 bg-gray-50">
                <tr>
                    <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wide">Тема</th>
                    <th class="px-5 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wide hidden md:table-cell">Бюджет</th>
                    <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wide hidden md:table-cell">Нужно к</th>
                    <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wide">Статус</th>
                    <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wide hidden lg:table-cell">Создана</th>
                    <th class="px-5 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
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
                @forelse($requests as $req)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-5 py-3 font-medium text-gray-900 max-w-xs">
                            <a href="{{ route('portal.equipment-requests.show', $req) }}" class="hover:text-primary-600 line-clamp-2">
                                {{ $req->subject }}
                            </a>
                        </td>
                        <td class="px-5 py-3 text-right text-gray-600 hidden md:table-cell">
                            {{ $req->budget ? number_format($req->budget, 0, '.', ' ') : '—' }}
                        </td>
                        <td class="px-5 py-3 text-gray-500 hidden md:table-cell">
                            {{ $req->needed_by?->format('d.m.Y') ?? '—' }}
                        </td>
                        <td class="px-5 py-3">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $statusColors[$req->status] ?? 'bg-gray-100 text-gray-600' }}">
                                {{ $statusLabels[$req->status] ?? $req->status }}
                            </span>
                        </td>
                        <td class="px-5 py-3 text-gray-500 hidden lg:table-cell">
                            {{ $req->created_at->format('d.m.Y') }}
                        </td>
                        <td class="px-5 py-3 text-right">
                            <a href="{{ route('portal.equipment-requests.show', $req) }}"
                               class="text-primary-600 hover:text-primary-800 text-xs font-medium">
                                Открыть →
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="py-12 text-center text-gray-400 text-sm">
                            Заявок нет.
                            <a href="{{ route('portal.equipment-requests.create') }}" class="text-primary-600 hover:underline">Создать первую</a>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        @if($requests->hasPages())
            <div class="px-5 py-3 border-t border-gray-100">
                {{ $requests->links() }}
            </div>
        @endif
    </x-card>
</div>
