@extends('layouts.admin')

@section('title', $sell->number)

@section('content')
    <livewire:admin.sells.show :sell="$sell" />
@endsection
