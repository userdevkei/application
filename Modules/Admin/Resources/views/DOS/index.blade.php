@extends('admin::layouts.default')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/2.1.5/css/dataTables.dataTables.css">
@section('admin::dashboard')
    <div class="card">
        <div class="card-header">
            <div class="row flex-between-center">
                <div class="col-6 col-sm-auto d-flex align-items-center pe-0">
                    <h5 class="fs-9 mb-0 text-nowrap py-0 py-xl-0">Delivery Orders </h5>
                </div>
{{--                @if(auth()->user()->role_id == 5)--}}
                    <div class="col-6 col-sm-auto ms-auto text-end ps-0">
                        <div id="table-simple-pagination-replace-element">
                            <a class="btn btn-falcon-default btn-sm" href="{{ route('admin.addDeliveryOrders') }}"><span class="fas fa-plus" data-fa-transform="shrink-3 down-2"></span><span class="d-none d-sm-inline-block ms-1">New</span></a>
                            <a class="btn btn-falcon-danger btn-sm" data-bs-toggle="modal" data-bs-target="#staticBackdrop"><span class="fas fa-cloud-download-alt" data-fa-transform=""></span><span class="d-none d-sm-inline-block ms-1">Report</span></a>

                        </div>
                    </div>
{{--                @endif--}}
            </div>
            <div class="modal fade" id="staticBackdrop" data-bs-keyboard="false" data-bs-backdrop="static" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                    <div class="modal-dialog modal-xl mt-6" role="document">
                        <div class="modal-content border-0">
                            <div class="position-absolute top-0 end-0 mt-3 me-3 z-1">
                                <button class="btn-close btn btn-sm btn-circle d-flex flex-center transition-base" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body p-0">
                                <div class="rounded-top-3 bg-body-tertiary py-3 ps-4 pe-6">
                                    <h5 class="mb-1" id="staticBackdropLabel">GENERATE STRAIGHTLINE REPORT</h5>
                                </div>
                                <div class="p-4">
                                    <form method="POST" action="{{ route('admin.collectionReport') }}">
                                            @csrf
                                            <div class="row row-cols-sm-3 g-1">
                                                <div class="mb-2">
                                                    <label class="my-1 fs-xs fw-bold" style="font-size: 85% !important;">CLIENT NAME</label>
                                                    <select name="client" id="clientInput" class="form-select js-choice" >
                                                        <option selected disabled value="">-- select client --</option>
                                                        @foreach($orders->groupBy('client_id') as $clientId => $clientIds)
                                                            <option value="{{ $clientId }}">{{ \App\Models\Client::where('client_id', $clientId)->first()->client_name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>

                                                <div class="mb-2">
                                                    <label class="my-1 fs-xs fw-bold" style="font-size: 85% !important;">DELIVERY TYPE</label>
                                                    <select name="delivery" id="delivery" class="form-select js-choice" >
                                                        <option selected disabled value="">-- select status --</option>
                                                        <option value="1">DO ENTRY</option>
                                                        <option value="2">DIRECT ENTRY </option>
                                                    </select>
                                                </div>

                                                <div class="mb-2">
                                                    <label class="my-1 fs-xs fw-bold" style="font-size: 85% !important;">TEA STATUS</label>
                                                    <select name="collection" id="collection" class="form-select js-choice" >
                                                        <option selected disabled value="">-- select status --</option>
                                                        <option value="1">UNDER COLLECTION </option>
                                                        <option value="2">COLLECTED </option>
                                                        <option value="3">WITHOUT TCI </option>
                                                    </select>
                                                </div>

                                                <div class="mb-2">
                                                    <label class="my-1 fs-xs fw-bold" style="font-size: 85% !important;">WAREHOUSE NAME</label>
                                                    <select name="warehouse" id="warehouseID" class="form-select js-choice" >
                                                        <option selected disabled value="">-- select warehouse --</option>
                                                        @foreach($orders->groupBy('warehouse_id') as $warehouseId => $warehouseIds)
                                                            <option value="{{ $warehouseId }}">{{ \App\Models\Warehouse::where('warehouse_id', $warehouseId)->first()->warehouse_name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>

                                                <div class="mb-2 date-input-container">
                                                    <label class="my-1 fs-xs fw-bold" style="font-size: 85% !important;">DATE FROM</label>
                                                    <input type="date" id="monthAgo" value="" name="from" class="form-control form-control-lg" style="height: 62% !important;">
                                                </div>

                                                <div class="mb-2 date-input-container">
                                                    <label class="my-1 fs-xs fw-bold" style="font-size: 85% !important;">DATE TO</label>
                                                    <input type="date"  id="todayDate" name="to" class="form-control form-control-lg" style="height: 62% !important;">
                                                </div>
                                            </div>

                                            <div class="mt-1 d-flex justify-content-center">
                                                <input class="mx-2" type="radio" name="report" value="1" required> <span class="text-info fw-bolder">PDF DOCUMENT</span>
                                                <input class="mx-2" type="radio" name="report" value="2" required> <span class="text-success fw-bolder">EXCEL DOCUMENT</span>
                                            </div>

                                            <div class="mt-4 d-flex justify-content-center">
                                                <button type="submit" class="btn btn-secondary col-7">DOWNLOAD REPORT</button>
                                            </div>

                                        </form>
                                </div>
                            </div>
                        </div>
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
                            <th>Date Created </th>
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
                            <td>{{ Carbon\Carbon::parse($order->created_at)->format('d/m/Y') }}
                            <td>{{ $order->client_name }}</td>
                            <td>{{ $order->invoice_number }}</td>
                            <td>{{ $order->garden_name }}</td>
                            <td>{{ $order->grade_name }}</td>
                            <td>{{ $order->lot_number }}</td>
                            <td>{{ $order->warehouse_name }}</td>
                            <td>{{ $order->sub_warehouse_name }}</td>
                            <td>{{ $order->loading_number }}</td>
                            <td>
                                <div class="dropdown font-sans-serif position-static" >
                                    <a class="link text-600 btn-sm dropdown-toggle btn-reveal" type="button" data-bs-toggle="dropdown" data-boundary="window" aria-haspopup="true" aria-expanded="false">
                                        <span class="fas fa-ellipsis-h fs-10"></span>
                                    </a>
                                    <div class="dropdown-menu dropdown-menu-end border py-0">
                                        <div class="py-2">
                                            <a class="dropdown-item text-info" href="{{ route('admin.traceTea', $order->delivery_id) }}">View DO</a>
                                            <a class="dropdown-item text-primary" href="{{ route('admin.editDO', $order->delivery_id) }}">Edit DO</a>
                                            @if($order->status < 2)
                                                <a class="dropdown-item text-danger" onclick="return confirm('Are you sure you want to delete DO INVOICE NO. {{ $order->invoice_number }}?')" href="{{ route('admin.deleteDeliveryOrder', $order->delivery_id) }}">Delete DO</a>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            </div>
        </div>

    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.js"></script>
    <script src="https://cdn.datatables.net/2.1.5/js/dataTables.js"></script>
<script>
    $(document).ready(function() {
        $('#datatable').DataTable( {
            order: [ 0, 'asc' ],
            pageLength: 50
        } );
    } );
</script>

@endsection
