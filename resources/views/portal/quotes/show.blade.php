@extends('layouts.portal')

@section('title', 'КП #' . $quote->number)

@section('content')
    <livewire:portal.quotes.show :quote="$quote" />
@endsection
