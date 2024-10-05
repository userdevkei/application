<script>
    @if(count($errors) > 0)
    @foreach($errors->all() as $error)
    toastr.error("{{ $error }}");
    @endforeach
        @endif

        @if(Session::has('error'))
        toastr.options = {
        "closeButton": true,
        "progressBar": true,
        "closeDuration": 10000
    }
    toastr.error("{{ session('error') }}");
    @endif
        @if(Session::has('success'))
        toastr.options = {
        "closeButton": true,
        "progressBar": true,
        "closeDuration": 10000
    }
    toastr.success("{{ session('success') }}");
    @endif

        @if(Session::has('info'))
        toastr.options = {
        "closeButton": true,
        "progressBar": true,
        "closeDuration": 10000
    }
    toastr.info("{{ session('info') }}");
    @endif

        @if(Session::has('warning'))
        toastr.options = {
        "closeButton": true,
        "progressBar": true,
        "closeDuration": 10000
    }
    toastr.warning("{{ session('warning') }}");
    @endif
</script>
