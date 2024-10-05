@extends('clerk::layouts.default')
@section('clerk::dashboard')
    <div class="card">
        <div class="card-header mb-0">
            <div class="row flex-between-center">
                <div class="col-6 col-sm-auto d-flex align-items-center pe-0">
                    <h5 class="fs-9 mb-0 text-nowrap py-0 py-xl-0">Tea Collection </h5>
                </div>
                @if(auth()->user()->role_id == 5)
                    <div class="col-6 col-sm-auto ms-auto text-end ps-0">
                        <div id="table-simple-pagination-replace-element">
                            <a class="btn btn-falcon-default btn-sm" href="{{ route('clerk.addTCI') }}" type="button"><span class="fas fa-plus" data-fa-transform="shrink-3 down-2"></span><span class="d-none d-sm-inline-block ms-1">New</span></a>
                        </div>
                    </div>
                @endif
            </div>
        </div>
        <div class="card-body overflow-hidden p-lg-3">
            <div class="row align-items-center">
                <div class="tab-pane preview-tab-pane active" role="tabpanel" aria-labelledby="tab-dom-c3976e0e-38db-410e-861a-36d04a3a7494" id="dom-c3976e0e-38db-410e-861a-36d04a3a7494">
                    <table class="table mb-0 fs-sm table-bordered table-striped" id="datatable">
                        <thead class="bg-200">
                        <tr>
                            <th>#</th>
                            <th>TCI #</th>
                            <th>Client Name</th>
                            <th>Lot Number</th>
                            <th>Producer Warehouse</th>
                            <th>SubWarehouse</th>
                            <th nowrap="">Whs Location</th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($instructions as $tci => $order)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $tci }}</td>
                                <td>{{ $order[0]['client_name'] }}</td>
                                <td>{{ $order[0]['lot_number'] }}</td>
                                <td>{{ $order[0]['warehouse_name'] }}</td>
                                <td>{{ $order[0]['sub_warehouse_name'] }}</td>
                                <td>{{ $order[0]['locality'] == 1 ? 'ISLAND' : ($order[0]['locality'] == 2 ? 'CHANGAMWE' : ($order[0]['locality'] == 3 ? 'JOMVU' : ($order[0]['locality'] == 4 ? 'BONJE' : 'MIRITINI'))) }}</td>
                                <td nowrap="">
                                    <a class="link-info mx-1 fs-sm" data-bs-toggle="tooltip" data-bs-placement="left" title="View TCI Details" href="{{ route('clerk.viewTciDetails', base64_encode($tci)) }}"><span class="fa fa-info"></span> </a>
                                    <a class="text-secondary mx-1" data-bs-toggle="tooltip" data-bs-placement="left" title="Download TCI" target="_blank" href="{{ route('clerk.downloadLLI', base64_encode($tci.':'.'1')) }}">
                                        <span class="fas fa-print"></span>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
    <script>
        $(document).ready(function() {
            $('#datatable').DataTable( {
                order: [ 0, 'asc' ],
                pageLength: 50
            } );
        } );
    </script>

@endsection
