@extends('layouts.admin')

@section('title', 'Тикет #' . $ticket->id)

@section('content')
    <livewire:admin.tickets.show :ticket="$ticket" />
@endsection
