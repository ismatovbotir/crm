@extends('layouts.admin')

@section('title', 'Заявка #' . $equipmentRequest->id)

@section('content')
    <livewire:admin.equipment-requests.show :equipment-request="$equipmentRequest" />
@endsection
