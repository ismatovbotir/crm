@extends('layouts.admin')

@section('title', $customer->name)

@section('content')
    <livewire:admin.customers.show :customer="$customer" />
@endsection
