@extends('admin::layouts.layout')

@section('main-content')
  @include('admin::navbars.navbar-vertical')
  <div class="content">
    @include('admin::navbars.navbar-combo')
    @yield('admin::dashboard')
    @include('admin::partials.footer')
  </div>
@endsection
