@props(['status'])
@php
$map = ['none'=>['gray','Не отгружен'],'partial'=>['yellow','Частично'],'complete'=>['green','Отгружен']];
$s = $map[$status] ?? ['gray', $status];
@endphp
<x-badge :color="$s[0]">
    <svg class="w-3 h-3 inline -mt-0.5 mr-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8l1.5 9.5A2 2 0 008.5 19h7a2 2 0 001.985-1.5L19 8"/></svg>
    {{ $s[1] }}
</x-badge>
