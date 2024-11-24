@extends('admin::layouts.default')
{{--<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/css/bootstrap.min.css">--}}
{{--<link rel="stylesheet" href="https://cdn.datatables.net/2.1.5/css/dataTables.dataTables.css">--}}
@section('admin::dashboard')
    <div class="card">
        <div class="card-header">
            <div class="row flex-between-center">
                <div class="col-6 col-sm-auto d-flex align-items-center pe-0">
                    <h5 class="fs-9 mb-0 text-nowrap py-0 py-xl-0">Teas In Stock </h5>
                </div>
                <div class="col-6 col-sm-auto ms-auto text-end ps-0">
                        <div id="table-simple-pagination-replace-element">
                            <a class="btn btn-falcon-default btn-sm" data-bs-toggle="modal" data-bs-target="#staticBackdrop"><span class="fas fa-plus" data-fa-transform="shrink-3 down-2"></span><span class="d-none d-sm-inline-block ms-1">Receive Teas</span></a>

                            <button class="btn btn-falcon-default btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#stockReport"><span class="fas fa-file-download" data-fa-transform="shrink-3 down-2"></span><span class="d-none d-sm-inline-block ms-1">Stock Report</span></button>

                            <button class="btn btn-falcon-danger btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#transporter"><span class="fas fa-file-download" data-fa-transform="shrink-3 down-2"></span><span class="d-none d-sm-inline-block ms-1">Transport Report</span></button>

                        </div>
                    </div>
                <div class="modal fade" id="transporter" data-bs-keyboard="false" data-bs-backdrop="static" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                    <div class="modal-dialog modal-xl mt-6" role="document">
                        <div class="modal-content border-0">
                            <div class="position-absolute top-0 end-0 mt-3 me-3 z-1">
                                <button class="btn-close btn btn-sm btn-circle d-flex flex-center transition-base" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body p-0">
                                <div class="rounded-top-3 bg-body-tertiary py-3 ps-4 pe-6">
                                    <h5 class="mb-1" id="staticBackdropLabel">GENERATE TRANSPORTER REPORT</h5>
                                </div>
                                <div class="p-4">
                                    <form method="POST" action="{{ route('admin.exportTransportReport') }}">
                                        @csrf
                                        <div class="row row-cols-sm-2 g-1">
                                            <div class="col-6 mb-2">
                                                <label> CLIENT NAME</label>
                                                <select class="form-select js-choice" name="transporter" data-options='{"removeItemButton":true,"placeholder":true}'>
                                                    <option value="" selected>-- all transporters --</option>
                                                    @foreach($transporters as $transporter)
                                                        <option value="{{ $transporter->transporter_id }}">{{ $transporter->transporter_name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div class="col-6 mb-2">
                                                <label> DELIVERY TYPE</label>
                                                <select class="form-select js-choice" name="report" data-options='{"removeItemButton":true,"placeholder":true}'>
                                                    <option value="" selected>-- all deliveries --</option>
                                                    <option value="1">COLLECTIONS</option>
                                                    <option value="2">TRANSFERS</option>
                                                </select>
                                            </div>
                                            <div class="mb-2 date-input-container">
                                                <label class="my-1 fs-xs fw-bold" style="font-size: 85% !important;">DATE FROM</label>
                                                <input type="date" id="monthAgo" value="" name="from" class="form-control date-input" style="height: 62% !important;">
                                            </div>

                                            <div class="mb-2 date-input-container">
                                                <label class="my-1 fs-xs fw-bold" style="font-size: 85% !important;">DATE TO</label>
                                                <input type="date"  id="todayDate" name="to" class="form-control date-input" style="height: 62% !important;">
                                            </div>
                                        </div>

                                        <div class="mt-4 d-flex justify-content-center">
                                            <button type="submit" class="btn btn-success col-7">DOWNLOAD REPORT</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                 <div class="modal fade" id="stockReport" data-bs-keyboard="false" data-bs-backdrop="static" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                    <div class="modal-dialog modal-xl mt-6" role="document">
                        <div class="modal-content border-0">
                            <div class="position-absolute top-0 end-0 mt-3 me-3 z-1">
                                <button class="btn-close btn btn-sm btn-circle d-flex flex-center transition-base" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body p-0">
                                <div class="rounded-top-3 bg-body-tertiary py-3 ps-4 pe-6">
                                    <h5 class="mb-1" id="staticBackdropLabel">GENERATE CUSTOM REPORT</h5>
                                </div>
                                <div class="p-4">
                                    <form method="post" action="{{ route('admin.StockReport') }}">
                                        @csrf
                                        <div class="row">
                                                <div class="col-6 mb-2">
                                                    <label> CLIENT NAME</label>
                                                    <select class="form-select js-choice" name="client" data-options='{"removeItemButton":true,"placeholder":true}'>
                                                        <option value="" selected>-- all clients --</option>
                                                        @foreach($stocks->groupBy('client_name') as $clientName => $client)
                                                            <option value="{{ $client[0]->client_id }}">{{ $clientName }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>

                                                <div class="col-6 mb-2">
                                                    <label> WAREHOUSES </label>
                                                    <select class="form-select js-choice" name="station" data-options='{"removeItemButton":true,"placeholder":true}'>
                                                        <option value="" selected>-- all warehouses --</option>
                                                        @foreach($stocks->groupBy('stocked_at') as $warehouseName => $warehouse)
                                                            <option value="{{ $warehouse[0]->station_id }}">{{ $warehouseName }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>

                                                <div class="col-6 mb-2 date-input-container">
                                                    <label> DATE FROM </label>
                                                    <input type="date" class="form-control form-control-lg" value="{{ Carbon\Carbon::today()->subDays(30)->format('Y-m-d') }}" name="from" placeholder="--">
                                                </div>

                                                <div class="col-6 mb-2">
                                                    <label> DATE TO</label>
                                                    <input type="date" value="{{ Carbon\Carbon::today()->format('Y-m-d') }}" class="form-control form-control-lg" name="to" placeholder="--">
                                                </div>

                                                <div class="mt-2 fs-sm d-flex justify-content-center">
                                                    {{-- <input class="mx-2" type="radio" name="report" value=""> <span class="text-info fw-bolder">ALL STL</span> --}}
                                                    <input class="mx-2" type="radio" name="report" value="1"> <span class="text-primary fw-bolder">PDF</span>
                                                    <input class="mx-2" type="radio" name="report" value="2"> <span class="text-secondary fw-bolder">EXCEL </span>
                                                </div>
                                            </div>

                                            <div class="d-flex justify-content-center mt-4">
                                                <button type="submit" class="btn col-8 btn-md btn-falcon-success">DOWNLOAD REPORT</button>
                                            </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                    <div class="modal fade" id="staticBackdrop" data-bs-keyboard="false" data-bs-backdrop="static" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                        <div class="modal-dialog modal-lg mt-6" role="document">
                            <div class="modal-content border-0">
                                <div class="position-absolute top-0 end-0 mt-3 me-3 z-1">
                                    <button class="btn-close btn btn-sm btn-circle d-flex flex-center transition-base" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body p-0">
                                    <div class="rounded-top-3 bg-body-tertiary py-3 ps-4 pe-6">
                                        <h5 class="mb-1" id="staticBackdropLabel">Provide DO Number/TCI Number To Receive Teas</h5>
                                    </div>
                                    <div class="p-4">
                                        <form method="post" action="{{ route('admin.getDoNumber') }}">
                                            @csrf
                                            <div class="row">
                                                <div class="col-lg-12 d-flex justify-content-center">
                                                    <div class="flex-1 form-floating">
                                                        <input type="text" class="form-control form-control-lg" name="doNumber" required placeholder="--">
                                                        <label> DO/TCI Number</label>
                                                    </div>

                                                </div>
                                                <div class="d-flex justify-content-center mt-4">
                                                    <button type="submit" class="btn col-8 btn-md btn-falcon-success">PROCEED</button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
            </div>
        </div>
        <div class="card-body overflow-hidden p-sm-3">
            <div class="row align-items-center">
                <div class="tab-pane preview-tab-pane active" role="tabpanel" aria-labelledby="tab-dom-c3976e0e-38db-410e-861a-36d04a3a7494" id="dom-c3976e0e-38db-410e-861a-36d04a3a7494">
                    <table class="table mb-0 table-bordered table-striped" id="datatable">
                        <thead class="bg-200">
                        <tr>
                            <th>#</th>
                            <th>Client Name</th>
                            <th>Order #</th>
                            <th>Inv #</th>
                            <th>Lot #</th>
                            <th>Garden Name</th>
                            <th>Grade</th>
                            <th>Packages</th>
                            <th>Weight</th>
                            <th nowrap="">Date Rc'd</th>
                            <th>Stocked at</th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody>
                            @foreach($stocks as $stock)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $stock->client_name }}</td>
                                    <td>{{ $stock->order_number }}</td>
                                    <td>{{ $stock->invoice_number }}</td>
                                    <td>{{ $stock->lot_number }}</td>
                                    <td>{{ $stock->garden_name }}</td>
                                    <td>{{ $stock->grade_name }}</td>
                                    <td>{{ $stock->current_stock }}</td>
                                    <td>{{ $stock->current_weight }}</td>
                                    <td nowrap="">{{ \Carbon\Carbon::createFromTimestamp($stock->date_received)->format('d/m/Y') }}</td>
                                    <td>{{ $stock->stocked_at }} - {{ $stock->bay_name }}</td>
                                    <td nowrap="">
                                        <div class="dropdown font-sans-serif position-static" >
                                            <a class="link text-info mx-1" href="{{ route('admin.traceTea', $stock->delivery_id) }}" data-bs-toggle="tooltip" data-bs-placement="left" title="Trace Tea"><span class="fa fa-info"></span> </a>

                                            <a class="link text-600 btn-sm dropdown-toggle btn-reveal" type="button" data-bs-toggle="dropdown" data-boundary="window" aria-haspopup="true" aria-expanded="false">
                                                <span class="fas fa-ellipsis-h fs-10"></span>
                                            </a>
                                            <div class="dropdown-menu dropdown-menu-end border py-0">
                                                <div class="py-2">
                                                    <a class="dropdown-item text-info" data-bs-toggle="tooltip" data-bs-placement="left" title="Update Tea" href="{{ route('admin.editStock', $stock->stock_id) }}">Edit Tea</a>
                                                    <a class="dropdown-item text-success" data-bs-toggle="tooltip" data-bs-placement="left" title="Obtain Sample" href="{{ route('admin.withdrawSample', $stock->stock_id) }}">Obtain Sample</a>
                                                    @if($stock->used == 0)
                                                        <a class="dropdown-item text-danger" data-bs-toggle="tooltip" data-bs-placement="left" title="Remove tea from stock" onclick="return confirm('Are you sure you want archive Invoice Number {{ $stock->invoice_number }}')" href="{{ route('admin.deleteTea', $stock->stock_id) }}">Archive Tea</a>
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
@endsection
<script src="https://code.jquery.com/jquery-3.7.1.js"></script>
{{--<script src="https://cdn.datatables.net/2.1.5/js/dataTables.js"></script>--}}
<script>

    function clearDate() {
        document.getElementById('todayDate').value = ''; // Clear the date input value
    }

    function clearDate() {
        document.getElementById('monthAgo').value = ''; // Clear the date input value
    }

    $(document).ready(function() {
        $('#datatable').DataTable( {
            order: [ 0, 'asc' ],
            pageLength: 50
        } );

        var currentDate = new Date(); // Get the current date and time

// Adjust the current date and time for a timezone offset of +3 hours
        currentDate.setHours(currentDate.getHours() + 3);

// Format the adjusted date and time string for input type datetime-local
        var formattedDateTime = currentDate.toISOString().slice(0, -8); // Removes the milliseconds and timezone offset

// Set the value of the datetime-local input element
        document.getElementById('todayDate').value = formattedDateTime;



        var today = new Date();

// Subtract one month from today's date
        var oneMonthAgo = new Date(today);
        oneMonthAgo.setMonth(today.getMonth() - 1);

// Format the date as YYYY-MM-DD
        var year = oneMonthAgo.getFullYear();
        var month = (oneMonthAgo.getMonth() + 1).toString().padStart(2, '0');
        var day = oneMonthAgo.getDate().toString().padStart(2, '0');
        var hours = oneMonthAgo.getHours().toString().padStart(2, '0');
        var minutes = oneMonthAgo.getMinutes().toString().padStart(2, '0');
        var seconds = oneMonthAgo.getSeconds().toString().padStart(2, '0');

        var formattedDateTime = `${year}-${month}-${day}T${hours}:${minutes}`;

// Set the value of the input field to the date one month ago
        document.getElementById("monthAgo").value = formattedDateTime;

    } );
</script>
