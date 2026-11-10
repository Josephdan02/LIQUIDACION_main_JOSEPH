@extends('layouts.app')

@section('title', 'Editar anexo | SANTRIX ANEXO LOCAL')
@section('page-title', 'Editar anexo')

@section('content')
    <form action="{{ route('anexos.update', $anexo) }}" method="POST">
        @csrf
        @method('PUT')
        @include('anexos._form')
    </form>
@endsection
