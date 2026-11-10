@extends('layouts.app')

@section('title', 'Editar registro | SANTRIX ANEXO LOCAL')
@section('page-title', 'Editar registro')

@section('content')
    <form action="{{ route('registros.update', $registro) }}" method="POST">
        @csrf
        @method('PUT')
        @include('registros._form')
    </form>
@endsection
