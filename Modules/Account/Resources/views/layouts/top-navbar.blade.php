@extends('account::layouts.layout')

@section('main-content')
  @include('account::navbars.navbar-top-menu')
  <div class="content">
      @yield('account::dashboard')
      @include('account::partials.footer')
  </div>
@endsection
