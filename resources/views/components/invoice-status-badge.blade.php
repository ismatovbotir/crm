@props(['status'])
@php
$map = ['draft'=>['gray','Черновик'],'sent'=>['blue','Отправлен'],'partially_paid'=>['yellow','Частично'],'paid'=>['green','Оплачен'],'overdue'=>['red','Просрочен'],'cancelled'=>['gray','Отменён']];
$s = $map[$status] ?? ['gray', $status];
@endphp
<x-badge :color="$s[0]">{{ $s[1] }}</x-badge>
