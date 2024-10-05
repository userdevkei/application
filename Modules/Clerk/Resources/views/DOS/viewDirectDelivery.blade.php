@extends('clerk::layouts.default')
@section('clerk::dashboard')
    <div class="card">
        <div class="card-header">
            <div class="row flex-between-center">
                <div class="col-6 col-sm-auto d-flex align-items-center pe-0">
                    <h5 class="fs-9 mb-0 text-nowrap py-0 py-xl-0">{{ $delivery }} Direct Delivery Details </h5>
                </div>
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
                            <th>Tea Type</th>
                            <th>Inv No</th>
                            <th>Garden Name</th>
                            <th>Grade</th>
                            <th>Package</th>
                            <th>Pkgs</th>
                            <th>Weight</th>
                            <th>Producer Whs</th>
                            <th>Destination</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($orders as $order)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $order->client_name }}</td>
                                <td>{{ $order->tea_id == 1 ? 'AUCTION TEA' : ($order->tea_id == 2 ? 'PRIVATE TEA' : ($order->tea_id == 3 ? 'FACTORY TEA' : 'BLEND REMNANTS')) }}</td>
                                <td>{{ $order->invoice_number }}</td>
                                <td>{{ $order->garden_name }}</td>
                                <td>{{ $order->grade_name }}</td>
                                <td>{{ $order->package == 1 ? 'PAPER SACK' : 'PAPER BAG' }}</td>
                                <td>{{ $order->packet }}</td>
                                <td>{{ $order->weight }}</td>
                                <td>{{ $order->warehouse_name }}</td>
                                <td>{{ $order->station_name }}</td>
                                <td>{!! $order->status == 1 ? '<span class="text-info">Under Collection </span>' : '<span class="text-success">Tea Received</span>' !!}</td>
                                <td>
                                    <a class="link-info d-block fs-sm" href="{{ route('clerk.traceTea', $order->delivery_id) }}" data-bs-toggle="tooltip" data-bs-placement="left" title="Trace Tea"><span class="fa fa-info"></span> </a>
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
