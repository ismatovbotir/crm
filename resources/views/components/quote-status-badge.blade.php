@props(['status'])
@php
$map = ['draft'=>['gray','Черновик'],'sent'=>['blue','Отправлен'],'viewed'=>['purple','Просмотрен'],'accepted'=>['green','Принят'],'rejected'=>['red','Отклонён'],'expired'=>['yellow','Истёк']];
$s = $map[$status] ?? ['gray', $status];
@endphp
<x-badge :color="$s[0]">{{ $s[1] }}</x-badge>
