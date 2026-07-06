@extends('layouts.admin')

@section('title', $lead->name)

@section('content')
    <livewire:admin.leads.show :lead="$lead" />
@endsection
