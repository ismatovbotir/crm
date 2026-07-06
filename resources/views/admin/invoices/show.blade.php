@extends('layouts.admin')

@section('title', 'Инвойс #' . $invoice->number)

@section('content')
    <livewire:admin.invoices.show :invoice="$invoice" />
@endsection
