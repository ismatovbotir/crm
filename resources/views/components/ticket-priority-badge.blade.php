@props(['priority'])
@php
$map = ['low'=>['gray','Низкий'],'medium'=>['blue','Средний'],'high'=>['yellow','Высокий'],'critical'=>['red','Критичный']];
$s = $map[$priority] ?? ['gray', $priority];
@endphp
<x-badge :color="$s[0]">{{ $s[1] }}</x-badge>
