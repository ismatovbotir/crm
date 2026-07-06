@props(['status'])
@php
$map = ['open'=>['blue','Открыт'],'in_progress'=>['yellow','В работе'],'pending_customer'=>['purple','Ждёт клиента'],'resolved'=>['green','Решён'],'closed'=>['gray','Закрыт']];
$s = $map[$status] ?? ['gray', $status];
@endphp
<x-badge :color="$s[0]">{{ $s[1] }}</x-badge>
