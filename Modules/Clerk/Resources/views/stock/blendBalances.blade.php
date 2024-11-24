@extends('clerk::layouts.default')
@section('clerk::dashboard')
    <div class="card">
        <div class="card-header">
            <div class="row flex-between-center">
                <div class="col-6 col-sm-auto d-flex align-items-center pe-0">
                    <h5 class="fs-9 mb-0 text-nowrap py-0 py-xl-0">Blend Balances In Stock </h5>
                </div>
                @if(auth()->user()->role_id == 5)
                    <div class="col-6 col-sm-auto ms-auto text-end ps-0">
                        <div id="table-simple-pagination-replace-element">
                            <a class="btn btn-falcon-default btn-sm" href="{{ route('clerk.addDeliveryOrders') }}"><span class="fas fa-plus" data-fa-transform="shrink-3 down-2"></span><span class="d-none d-sm-inline-block ms-1">New</span></a>
                        </div>
                    </div>
                @endif
            </div>
        </div>
        <div class="card-body overflow-hidden p-lg-3">
            <div class="row align-items-center">
                <div class="tab-pane preview-tab-pane active" role="tabpanel" aria-labelledby="tab-dom-c3976e0e-38db-410e-861a-36d04a3a7494" id="dom-c3976e0e-38db-410e-861a-36d04a3a7494">
                    <table class="table mb-0 table-bordered table-striped" id="datatable">
                        <thead class="bg-200">
                        <tr>
                            <th>#</th>
                            <th>Client Name</th>
                            <th>Line Type</th>
                            <th>Garden Name</th>
                            <th>Grade</th>
                            <th>Blend Number</th>
                            <th>Packages</th>
                            <th>Weight</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($balances as $index => $balance)
                            <tr>
                                <td> {{ $loop->iteration }} </td>
                                <td> {{ $balance->client_name }} </td>
                                <td> {{ $balance->type }} </td>
                                <td> {{ $balance->garden }} </td>
                                <td> {{ $balance->grade }} </td>
                                <td> {{ $balance->blend_number }} </td>
                                <td> {{ $balance->current_packages }} </td>
                                <td> {{ $balance->current_weight }} </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
<script src="https://code.jquery.com/jquery-3.7.1.js"></script>
<script>
    $(document).ready(function() {
        $('#datatable').DataTable( {
            order: [ 0, 'asc' ],
            pageLength: 50
        } );
    } );
</script>
