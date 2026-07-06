@extends('layouts.admin')

@section('title', 'КП #' . $quote->number)

@section('content')
    <livewire:admin.quotes.show :quote="$quote" />
@endsection
