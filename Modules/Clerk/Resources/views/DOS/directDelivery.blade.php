@extends('clerk::layouts.default')
@section('clerk::dashboard')
    <div class="card">
        <div class="card-header">
            <div class="row flex-between-center">
                <div class="col-6 col-sm-auto d-flex align-items-center pe-0">
                    <h5 class="fs-9 mb-0 text-nowrap py-0 py-xl-0">Direct Deliveries </h5>
                </div>
                @if (auth()->user()->role_id == 3)
                    <div class="col-6 col-sm-auto ms-auto text-end ps-0">
                        <div id="table-simple-pagination-replace-element">
                            <a class="btn btn-falcon-default btn-sm" href="{{ route('clerk.addDirectDelivery') }}"><span class="fas fa-plus" data-fa-transform="shrink-3 down-2"></span><span class="d-none d-sm-inline-block ms-1">New</span></a>
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
                            <th>Delivery #</th>
                            <th>Client Name</th>
                            <th>Tea Type</th>
                            <th>Packaging</th>
                            <th>Packages </th>
                            <th>Net Weight</th>
                            <th>Producer Whs</th>
                            <th>Destination</th>
                            <th>Status</th>
                            <th nowrap=""></th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($orders as $order)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $order->order_number }}</td>
                                <td>{{ $order->client_name }}</td>
                                <td>{{ $order->tea_id == 1 ? 'AUCTION TEA' : ($order->tea_id  == 2 ? 'PRIVATE TEA' : ($order->tea_id  == 3 ? 'FACTORY TEA' : 'BLEND REMNANTS')) }}</td>
                                <td>{{ $order->packet == 1 ? 'PB' : 'PS' }}</td>
                                <td>{{ $order->total_packages }}</td>
                                <td>{{ $order->total_net_weight }}</td>
                                <td>{{ $order->warehouse_name }}</td>
                                <td>{{ $order->station_name }}</td>
                                <td>{!! $order->order_status == null ? '<span class="text-danger">Pending <span>' : '<span class="text-success">Stocked</span>' !!}</td>
                                <td nowrap="">
                                    @if($order->order_status > 0)
                                        <a onclick="return false;" class="link-success fs-sm mx-2" data-bs-toggle="tooltip" data-bs-placement="left" title="Teas received and stock updated">
                                            <span class="fas fa-check"></span>
                                        </a>
                                    @else
                                        <a onclick="return confirm('Are you sure you want to receive all teas under this delivery?')" class="link-danger d-inline-block fs-sm mx-2" data-bs-toggle="tooltip" data-bs-placement="left" title="Receive teas under this delivery" href="{{ route('clerk.receiveDirectDeliveries', base64_encode($order->order_number)) }}"> <span class="fas fa-compress-alt"></span> </a>
                                    @endif
                                        <a class="link-info fs-sm" href="{{ route('clerk.viewDirectDeliveryOrder', base64_encode($order->order_number)) }}" data-bs-toggle="tooltip" data-bs-placement="left" title="View Direct Del Details" ><span class="fa fa-info"></span> </a>
                                    <a class="text-secondary  mx-2" data-bs-toggle="tooltip" data-bs-placement="left" title="Tally of goods received" href="{{ route('clerk.downloadDirectDeliveries', base64_encode($order->order_number . ':' . '1')) }}">
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
