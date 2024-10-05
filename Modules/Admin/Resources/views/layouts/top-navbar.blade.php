@extends('admin::layouts.layout')

@section('main-content')
  @include('admin::navbars.navbar-top-menu')
  <div class="content">
      @yield('admin::dashboard')
      @include('admin::partials.footer')
  </div>
@endsection
