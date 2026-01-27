@extends('layouts.master')

@section('page-css')
    <link rel="stylesheet" href="/css/admin.css">
@endsection

@section('content')
    <div class="admin-wrapper">
        @yield('admin-content')
    </div>
@endsection
