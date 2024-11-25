<!DOCTYPE html>
<html lang="en-US" dir="ltr">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">


    <!-- ===============================================-->
    <!--    Document Title-->
    <!-- ===============================================-->
    <title> {{ config('app.name') }} | ADMIN Dashboard </title>


    <!-- ===============================================-->
    <!--    Favicons-->
    <!-- ===============================================-->
    <link rel="apple-touch-icon" sizes="180x180" href="{{ url('/assets/img/favicons/icon.png') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ url('/assets/img/favicons/logo-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ url('/assets/img/favicons/logo-16x16.png') }}">
    <link rel="shortcut icon" type="image/x-icon" href="{{ url('/assets/img/favicons/logo.png') }}">
    <link rel="manifest" href="{{ url('/assets/img/favicons/manifest.json') }}">
    <meta name="msapplication-TileImage" content="{{ url('/assets/img/favicons/150x150.png') }}">
    <meta name="theme-color" content="#ffffff">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/css/bootstrap.min.css">
    <link href="{{ url('/vendors/datatables.net-bs5/dataTables.bootstrap5.min.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css"/>
    <link href="{{ url('vendors/choices/choices.min.css') }}" rel="stylesheet" />
    <script src="{{ asset('vendors/jquery/jquery.min.js') }}"></script>
    <script src="{{ url('/assets/js/config.js') }}"></script>
    <script src="{{ url('/vendors/overlayscrollbars/OverlayScrollbars.min.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>


    <!-- ===============================================-->
    <!--    Stylesheets-->
    <!-- ===============================================-->
    <link rel="preconnect" href="https://fonts.gstatic.com">
{{--    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,500,600,700%7cPoppins:300,400,500,600,700,800,900&amp;display=swap" rel="stylesheet">--}}
    <link href="{{ url('/vendors/overlayscrollbars/OverlayScrollbars.min.css') }}" rel="stylesheet">
    <link href="{{ url('/assets/css/theme-rtl.css') }}" rel="stylesheet" id="style-rtl">
    <link href="{{ url('/assets/css/theme.css') }}" rel="stylesheet" id="style-default">
    <link href="{{ url('/assets/css/user-rtl.css') }}" rel="stylesheet" id="user-style-rtl">
    <link href="{{ url('/assets/css/user.css') }}" rel="stylesheet" id="user-style-default">
    <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">

    <script>
      var isRTL = JSON.parse(localStorage.getItem('isRTL'));
      if (isRTL) {
        var linkDefault = document.getElementById('style-default');
        var userLinkDefault = document.getElementById('user-style-default');
        linkDefault.setAttribute('disabled', true);
        userLinkDefault.setAttribute('disabled', true);
        document.querySelector('html').setAttribute('dir', 'rtl');
      } else {
        var linkRTL = document.getElementById('style-rtl');
        var userLinkRTL = document.getElementById('user-style-rtl');
        linkRTL.setAttribute('disabled', true);
        userLinkRTL.setAttribute('disabled', true);
      }
    </script>
  </head>

  <body>

    <!-- ===============================================-->
    <!--    Main Content-->
    <!-- ===============================================-->
    <main class="main" id="top">
      <div class="container-fluid" data-layout="container-fluid">
        <script>
          var isFluid = JSON.parse(localStorage.getItem('isFluid'));
          if (isFluid) {
            var container = document.querySelector('[data-layout]');
            container.classList.remove('container');
            container.classList.add('container-fluid');
          }
        </script>
        @yield('main-content')
          @include('partials.messages')

      </div>
    </main>
    <!-- ===============================================-->
    <!--    End of Main Content-->
    <!-- ===============================================-->

<<<<<<< Updated upstream
{{--    @include('admin::partials.setting-panel')--}}
=======
    {{-- @include('admin::partials.setting-panel') --}}
>>>>>>> Stashed changes

    <!-- ===============================================-->
    <!--    JavaScripts-->
    <!-- ===============================================-->
    <script src="{{ url('/vendors/popper/popper.min.js') }}"></script>
    <script src="{{ url('/vendors/bootstrap/bootstrap.min.js') }}"></script>
    <script src="{{ url('/vendors/anchorjs/anchor.min.js') }}"></script>
    <script src="{{ url('/vendors/is/is.min.js') }}"></script>
    <script src="{{ url('/vendors/echarts/echarts.min.js') }}"></script>
    <script src="{{ url('/vendors/fontawesome/all.min.js') }}"></script>
    <script src="{{ url('/vendors/lodash/lodash.min.js') }}"></script>
    <script src="{{ url('/vendors/countup/countUp.umd.js') }}"></script>
{{--    <script src="https://cdnjs.cloudflare.com/ajax/libs/js-polyfills/0.1.43/polyfill.min.js"></script>--}}
{{--    <script src="https://polyfill.io/v3/polyfill.min.js?features=window.scroll"></script>--}}
    <script src="{{ url('/vendors/list.js/list.min.js') }}"></script>
    <script src="{{ url('/assets/js/theme.js') }}"></script>
    <script src="{{ url('vendors/choices/choices.min.js') }}"></script>
    <script src="{{ asset('vendors/datatables.net/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('vendors/datatables.net-bs5/dataTables.bootstrap5.min.js') }}"> </script>
    <script src="{{ asset('vendors/datatables.net-fixedcolumns/dataTables.fixedColumns.min.js') }}"> </script>


  </body>

</html>
