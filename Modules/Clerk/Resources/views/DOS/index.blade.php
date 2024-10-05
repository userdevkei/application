@extends('clerk::layouts.default')
@section('clerk::dashboard')
    <div class="card">
        <div class="card-header">
            <div class="row flex-between-center">
                <div class="col-6 col-sm-auto d-flex align-items-center pe-0">
                    <h5 class="fs-9 mb-0 text-nowrap py-0 py-xl-0">Delivery Orders </h5>
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
                    <table class="table mb-0 table-bordered table-striped table-responsive-sm fs-sm" id="datatable">
                        <thead class="bg-200">
                        <tr>
                            <th>#</th>
                            <th>Client Name</th>
                            <th>Inv No</th>
                            <th>Garden Name</th>
                            <th>Grade</th>
                            <th>Lot No</th>
                            <th>Producer Whs</th>
                            <th>Sub-Warehouse</th>
                            <th>TCI #</th>
                            <th></th>
                        </tr>
                        </thead>
                    <tbody>
                    @foreach($orders as $order)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td nowrap="">{{ $order->client_name }}</td>
                            <td>{{ $order->invoice_number }}</td>
                            <td>{{ $order->garden_name }}</td>
                            <td nowrap="">{{ $order->grade_name }}</td>
                            <td>{{ $order->lot_number }}</td>
                            <td>{{ $order->warehouse_name }}</td>
                            <td>{{ $order->sub_warehouse_name }}</td>
                            <td>{{ $order->loading_number }}</td>
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
