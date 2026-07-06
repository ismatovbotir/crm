@props(['status'])

@php
$map = [
    'active'   => ['label' => 'Активный',  'color' => 'green'],
    'vip'      => ['label' => 'VIP',       'color' => 'purple'],
    'inactive' => ['label' => 'Неактивен', 'color' => 'gray'],
    'blocked'  => ['label' => 'Заблокирован', 'color' => 'red'],
];
$s = $map[$status] ?? ['label' => $status, 'color' => 'gray'];
@endphp

<x-badge :color="$s['color']">{{ $s['label'] }}</x-badge>
