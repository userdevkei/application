@extends('clerk::layouts.layout')

@section('main-content')
  @include('clerk::navbars.navbar-top-menu')
  <div class="content">
      @yield('clerk::dashboard')
      @include('clerk::partials.footer')
  </div>
@endsection
