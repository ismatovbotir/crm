@extends('layouts.portal')

@section('title', 'Инвойс #' . $invoice->number)

@section('content')
    <livewire:portal.invoices.show :invoice="$invoice" />
@endsection
