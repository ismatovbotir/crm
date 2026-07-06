@extends('layouts.admin')

@section('title', 'Редактировать ' . $quote->number)

@section('content')
<div class="max-w-7xl mx-auto">

    {{-- Sticky page header ──────────────────────────────────────────────── --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <nav class="text-sm text-gray-500 mb-1">
                <a href="{{ route('admin.quotes.index') }}" class="hover:text-primary-600">КП</a>
                <span class="mx-1">/</span>
                <a href="{{ route('admin.quotes.show', $quote) }}" class="hover:text-primary-600">{{ $quote->number }}</a>
                <span class="mx-1">/</span>
                <span class="text-gray-900">Редактировать</span>
            </nav>
            <div class="flex items-center gap-3">
                <h1 class="text-xl font-bold text-gray-900">{{ $quote->number }}</h1>
                <x-quote-status-badge :status="$quote->status" />
                <span class="text-xs text-gray-400 bg-gray-100 px-2 py-0.5 rounded-full">v{{ $quote->version }}</span>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.quotes.show', $quote) }}"
               class="inline-flex items-center px-4 py-2 text-sm font-medium rounded-lg border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 transition-colors">
                Отмена
            </a>
            <button type="submit" form="quote-edit-form"
                    wire:loading.attr="disabled"
                    class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-medium rounded-lg bg-primary-600 text-white hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-1 transition-colors disabled:opacity-50">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                Сохранить КП
            </button>
        </div>
    </div>

    {{-- Edit form card ──────────────────────────────────────────────────── --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6"
         style="height: calc(100vh - 9rem)">
        <livewire:admin.quotes.edit-form :quote="$quote" />
    </div>

</div>
@endsection
