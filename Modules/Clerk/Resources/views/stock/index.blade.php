@extends('clerk::layouts.default')
@section('clerk::dashboard')
    <div class="card">
        <div class="card-header">
            <div class="row flex-between-center">
                <div class="col-6 col-sm-auto d-flex align-items-center pe-0">
                    <h5 class="fs-9 mb-0 text-nowrap py-0 py-xl-0">Teas In Stock </h5>
                </div>
                <div class="col-6 col-sm-auto ms-auto text-end ps-0">
                        <div id="table-simple-pagination-replace-element">
                            @if(auth()->user()->role_id == 3)
                            <a class="btn btn-falcon-default btn-sm" data-bs-toggle="modal" data-bs-target="#staticBackdrop"><span class="fas fa-plus" data-fa-transform="shrink-3 down-2"></span><span class="d-none d-sm-inline-block ms-1">Receive Teas</span></a>
                            @endif
                            <button class="btn btn-falcon-default btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#stockReport"><span class="fas fa-file-download" data-fa-transform="shrink-3 down-2"></span><span class="d-none d-sm-inline-block ms-1">Download</span></button>
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
                                    <form method="post" action="{{ route('clerk.StockReport') }}">
                                        @csrf
                                        <div class="row">
                                                <div class="col-6 mb-2">
                                                    <label> CLIENT NAME</label>
                                                    <select class="form-select js-choice" name="client">
                                                        <option value="" selected>-- all clients --</option>
                                                        @foreach($stocks->groupBy('client_id') as $clientName => $client)
                                                            <option value="{{ $clientName }}">{{ $client[0]->client_name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>

                                                <div class="col-6 mb-2">
                                                    <label> WAREHOUSES </label>
                                                    <select class="form-select js-choice" name="station">
                                                        <option value="" selected>-- all warehouses --</option>
                                                        @foreach($stocks->groupBy('station_id') as $warehouseName => $warehouse)
                                                            <option value="{{ $warehouseName }}">{{ $warehouse[0]->stocked_at }}</option>
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
                                        </div>

                                                @if(auth()->user()->role_id == 2)
                                                <div class="mt-2 fs-sm d-flex justify-content-center">
                                                        {{-- <input class="mx-2" type="radio" name="report" value=""> <span class="text-info fw-bolder">ALL STL</span> --}}
                                                        <input class="mx-2" type="radio" name="report" value="1"> <span class="text-primary fw-bolder">PDF</span>
                                                        <input class="mx-2" type="radio" name="report" value="2"> <span class="text-secondary fw-bolder">EXCEL </span>
                                                    </div>
                                                @endif

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
                                        <form method="post" action="{{ route('clerk.getDoNumber') }}">
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
        <div class="card-body overflow-hidden p-lg-3">
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
                            <th nowrap="">Date Rcvd</th>
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
                                        <a class="link text-info mx-1" href="{{ route('clerk.traceTea', $stock->delivery_id) }}" data-bs-toggle="tooltip" data-bs-placement="left" title="Trace Tea"><span class="fa fa-info"></span> </a>
                                        <a class="link text-primary mx-1" data-bs-toggle="tooltip" data-bs-placement="left" title="Withdraw Sample" href="{{ route('clerk.withdrawSample', $stock->stock_id) }}"><span class="fa fa-edit"></span> </a>
{{--                                        <a class="link text-danger mx-1" data-bs-toggle="tooltip" data-bs-placement="left" title="Delete Tea" onclick="return confirm('Are you sure you want to delete te inv {{ $stock->invoice_number }}?')"><span class="fa fa-trash"></span> </a>--}}
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
