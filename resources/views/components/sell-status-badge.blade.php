@props(['status'])
@php
$map = ['draft'=>['gray','Черновик'],'confirmed'=>['blue','Подтверждён'],'shipped'=>['yellow','Отгружен'],'delivered'=>['green','Доставлен'],'cancelled'=>['gray','Отменён']];
$s = $map[$status] ?? ['gray', $status];
@endphp
<x-badge :color="$s[0]">{{ $s[1] }}</x-badge>
