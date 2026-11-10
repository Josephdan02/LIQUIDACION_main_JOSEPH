@extends('layouts.app')

@section('title', 'Nuevo registro | SANTRIX ANEXO LOCAL')
@section('page-title', 'Nuevo registro')

@section('content')
    <form action="{{ route('registros.store') }}" method="POST">
        @csrf
        @include('registros._form')
    </form>
@endsection
