@extends('layouts.portal')

@section('title', 'Тикет #' . $ticket->id)

@section('content')
    <livewire:portal.tickets.show :ticket="$ticket" />
@endsection
