@props(['status'])

@php
$map = [
    'new'            => ['label' => 'Новый',          'color' => 'blue'],
    'qualified'      => ['label' => 'Квалифицирован', 'color' => 'purple'],
    'contacted'      => ['label' => 'Контакт',        'color' => 'yellow'],
    'in_negotiation' => ['label' => 'Переговоры',     'color' => 'yellow'],
    'won'            => ['label' => 'Успех',          'color' => 'green'],
    'lost'           => ['label' => 'Проигран',       'color' => 'red'],
    'client'         => ['label' => 'Клиент',         'color' => 'green'],
];
$s = $map[$status] ?? ['label' => $status, 'color' => 'gray'];
@endphp

<x-badge :color="$s['color']">{{ $s['label'] }}</x-badge>
