@extends('layouts.admin')

@section('title', $product->name)

@section('content')
    <livewire:admin.catalog.products.show :product="$product" />
@endsection
