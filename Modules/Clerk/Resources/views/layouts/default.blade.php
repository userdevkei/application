@extends('clerk::layouts.layout')
@section('main-content')
  @include('clerk::navbars.navbar-vertical')
  <div class="content">
    @include('clerk::navbars.navber-top-default')
      @yield('clerk::dashboard')
      @include('clerk::partials.footer')
  </div>
@endsection
