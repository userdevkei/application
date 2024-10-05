@extends('admin::layouts.layout')
@section('main-content')
  @include('admin::navbars.navbar-vertical')
  <div class="content">
    @include('admin::navbars.navber-top-default')
      @yield('admin::dashboard')
      @include('admin::partials.footer')
  </div>
@endsection
