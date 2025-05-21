@extends('layouts.admin')

@section('header', 'Nouvelle file d\'attente')

@section('content')
@include('admin.queues.form')
@endsection
