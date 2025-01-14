@extends('admin::layouts.default')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/2.1.5/css/dataTables.dataTables.css">
@section('admin::dashboard')
    <div class="card">
        <div class="card-header">
            <div class="row flex-between-center">
                <div class="col-6 col-sm-auto d-flex align-items-center pe-0">
                    <h5 class="fs-9 mb-0 text-nowrap py-0 py-xl-0">External Tea Transfers </h5>
                </div>
                <div class="col-6 col-sm-auto ms-auto text-end ps-0">
                    <div id="table-simple-pagination-replace-element">
                        <a class="btn btn-falcon-default btn-sm" data-bs-toggle="modal" data-bs-target="#staticBackdrop"><span class="fas fa-plus" data-fa-transform="shrink-3 down-2"></span><span class="d-none d-sm-inline-block ms-1">New Request</span></a>
                    </div>
                </div>

                <div class="modal fade" id="staticBackdrop" data-bs-keyboard="false" data-bs-backdrop="static" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                    <div class="modal-dialog modal-xl mt-6" role="document">
                        <div class="modal-content border-0">
                            <div class="position-absolute top-0 end-0 mt-3 me-3 z-1">
                                <button class="btn-close btn btn-sm btn-circle d-flex flex-center transition-base" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body p-0">
                                <div class="rounded-top-3 bg-body-tertiary py-3 ps-4 pe-6">
                                    <h5 class="mb-1" id="staticBackdropLabel">FILTER TEAS FOR TRANSFER</h5>
                                </div>
                                <div class="p-4">
                                    <form method="POST" id="myForm" action="{{ route('admin.prepareExternalTransfer') }}">
                                        @csrf
                                        <div class="row row-cols-sm-3 g-2">
                                            @php
                                                $stations = \App\Models\Station::where('status', 1)->get();
                                            @endphp
                                            <div class=" mb-4">
                                                <label class="fs-sm fw-bold my-2" style="font-size: 85% !important;"> RECEIVING WAREHOUSE </label>
                                                <select name="location" class="form-select js-choice form-select-lg" id="selectWarehouse">
                                                    <option disabled selected>-- select station --</option>
                                                    @foreach($stations as $station)
                                                        <option value="{{ $station->station_id }}">{{ $station->station_name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class=" mb-4">
                                                <label class="fs-sm fw-bold my-2" style="font-size: 85% !important;"> CLIENT NAME</label>
                                                <select name="client" class="form-select form-select-lg" id="selectClients">
                                                    <option disabled selected>-- select client --</option>
                                                </select>
                                            </div>

                                            <div class=" mb-4">
                                                <label class="fs-sm fw-bold my-2" style="font-size: 85% !important;"> DESTINATION WAREHOUSE</label>
                                                <select name="warehouse" class="form-select form-select-lg js-choice" id="selectStation">
                                                    <option disabled selected>-- select destination --</option>
                                                    @foreach($warehouses as $warehouse)
                                                        <option value="{{ $warehouse->warehouse_id }}">{{ $warehouse->warehouse_name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>

                                        <div class="d-flex justify-content-center mt-1">
                                            <button id="submitButton" type="submit" class="btn btn-success col-8">PREPARE TRANSFER REQUEST </button>
                                        </div>
                                    </form>
                                </div>
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
                            <th>Date Initiated </th>
                            <th>Delivery Number </th>
                            <th>Client Name</th>
                            <th>Packages</th>
                            <th>Net Weight</th>
                            <th>Transfer From</th>
                            <th>Destination</th>
                            <th nowrap="">Status</th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($transfers as $transfer)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ \Carbon\Carbon::parse($transfer->created_at)->format('d/m/y') }}</td>
                                <td>{{ $transfer->delivery_number }}</td>
                                <td nowrap="">{{ $transfer->client_name }}</td>
                                <td>{{ $transfer->total_palettes }}</td>
                                <td>{{ $transfer->total_weight }}</td>
                                <td nowrap="">{{ $transfer->station_name }}</td>
                                <td nowrap="">{{ $transfer->warehouse_name }}</td>
                                <td>
                                    {!! $transfer->status === 0 ? '<span class="badge bg-warning"> Created </span>' : ($transfer->status == 1 ?  '<span class="badge bg-danger"> Pending Approval <span>' :($transfer->status == 2 ? '<span class="badge bg-info"> Approved <span>' : '<span class="badge bg-success"> Released <span>')) !!}
                                </td>
                                <td nowrap="">
                                    <div class="d-flex align-items-center">
                                        <!-- Trace Tea Icon -->
                                            @if($transfer->status === 0 || $transfer->status == 1 || $transfer->status == 2)

                                                <a class="link text-danger" data-bs-toggle="tooltip" data-bs-placement="left" title="Transfer pending release" onclick="return confirm('Are you sure you want to release this transfer request?')" href="{{ route('admin.releaseExternalTransfer', base64_encode($transfer->delivery_number)) }}"> <span class="fa-solid fa-truck-arrow-right"> </span></a>
                                            @else
                                                <a class="link text-success" data-bs-toggle="tooltip" data-bs-placement="left" title="Transfer released, and stock updated"> <span class="fa-solid fa-check-double"></span> </a>
                                            @endif
                                        <!-- Dropdown Icon -->
                                        <div class="dropdown font-sans-serif position-static" >
                                            <a class="link text-600 btn-sm dropdown-toggle btn-reveal" type="button" data-bs-toggle="dropdown" data-boundary="window" aria-haspopup="true" aria-expanded="false">
                                                <span class="fas fa-ellipsis-h fs-10"></span>
                                            </a>
                                            <div class="dropdown-menu dropdown-menu-end border py-0">
                                                <div class="py-2">
                                                    <a class="dropdown-item text-info" href="{{ route('admin.viewExternalTransferDetails', base64_encode($transfer->delivery_number)) }}">View Transfer</a>
                                                    <a class="dropdown-item text-primary" href="{{ route('admin.downloadExtraDelNote', base64_encode($transfer->delivery_number)) }}">Download Transfer</a>
                                                </div>
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
@endsection
<script src="https://code.jquery.com/jquery-3.7.1.js"></script>
<script src="https://cdn.datatables.net/2.1.5/js/dataTables.js"></script>
<script>
    $(document).ready(function() {
        $('#datatable').DataTable({
            order: [0, 'asc'],
            pageLength: 50
        });

        $('#selectWarehouse').change(function () {
            var warehouseId = $(this).val();
            $.ajax({
                type: 'GET',
                url: '{{ route('admin.selectClients') }}',
                data: { warehouseId },
                success:function (response) {
                    console.log(response)

                    $('#selectClients').empty();

                    $('#selectClients').append('<option value="" selected disabled class="text-center"> -- select client --');

                    $.each(response, function (clientName, clients) {
                        $('#selectClients').append('<option value="'+ clients[0]. client_id +'">'+ clientName +'</option>');
                    })
                }
            })

        });
    });
</script>
