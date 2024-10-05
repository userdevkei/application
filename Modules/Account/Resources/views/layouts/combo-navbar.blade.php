@extends('account::layouts.layout')

@section('main-content')
  @include('account::navbars.navbar-vertical')
  <div class="content">
    @include('account::navbars.navbar-combo')
    @yield('account::dashboard')
    @include('account::partials.footer')
  </div>
@endsection
