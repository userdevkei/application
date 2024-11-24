@extends('admin::layouts.default')
@section('admin::dashboard')
    <div class="card">
        <div class="card-header">
            <div class="row flex-between-center">
                <div class="col-6 col-sm-auto d-flex align-items-center pe-0">
                    <h5 class="fs-9 mb-0 text-nowrap py-0 py-xl-0">@if($id == 7) Straight Line Shipping @else Blend Process @endif </h5>
                </div>
                <div class="col-6 col-sm-auto ms-auto text-end ps-0">
                    <div id="table-simple-pagination-replace-element">
                        <a class="btn btn-falcon-default btn-sm" href="{{ route('admin.transferReport', $id) }}"><span class="fas fa-file-download" data-fa-transform="shrink-3 down-2"></span><span class="d-none d-sm-inline-block ms-1">Download</span></a>
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
                            <th>Client Name</th>
                            <th>Shipping Number </th>
                            <th>Vessel Name</th>
                            <th>Destination</th>
                            <th>Warehouse</th>
                            <th nowrap="">Status</th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($orders as $transfer)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ \Carbon\Carbon::parse($transfer->created_at)->format('d/m/y') }}</td>
                                <td>{{ $transfer->client_name }}</td>
                                <td>{{ $transfer->shipping_number }}</td>
                                <td>{{ $transfer->vessel_name }}</td>
                                <td>{{ $transfer->port_name }}</td>
                                <td>{{ $transfer->station_name }}</td>
                                <td>
                                    {!! $transfer->status == 0 ? '<span class="badge bg-warning"> SI Created </span>' : ($transfer->status == 1 ? '<span class="badge bg-info"> Teas Updated </span>' : ($transfer->status == 2 ? '<span class="badge bg-secondary"> SI Updated </span>' : ($transfer->status == 3 ? '<span class="badge bg-dark"> Pend. Approval </span>' : '<span class="badge bg-success"> Shipped </span>'))) !!}
                                </td>
                                <td nowrap="">
                                    <div class="d-flex align-items-center">
                                        @if($id == 7)
                                            @if($transfer->status == 0)
                                                <a class="link text-warning"  onclick="return confirm('Are you sure you want to initiate SI?')" data-bs-toggle="tooltip" data-bs-placement="left" title="Click to initiate SI" href="{{ route('admin.initateSI', $transfer->shipping_id) }}"><span class="fa-solid fa-share-from-square" ></span></a>
                                            @elseif($transfer->status == 1)
                                                <a class="link text-info" data-bs-placement="left" title="Click to update SI" data-bs-toggle="modal" data-bs-target="#staticBackdrop-{{ $transfer->shipping_id }}"><span class="fa-solid fa-file-pen"></span></a>

                                                <div class="modal fade" id="staticBackdrop-{{ $transfer->shipping_id }}" data-bs-keyboard="false" data-bs-backdrop="static" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                                                    <div class="modal-dialog modal-xl modal-dialog-centered mt-6" role="document">
                                                        <div class="modal-content border-0">
                                                            <div class="position-absolute top-0 end-0 mt-3 me-3 z-1">
                                                                <button class="btn-close btn btn-sm btn-circle d-flex flex-center transition-base" data-bs-dismiss="modal" aria-label="Close"></button>
                                                            </div>
                                                            <div class="modal-body p-0">
                                                                <div class="rounded-top-3 bg-body-tertiary py-3 ps-4 pe-6">
                                                                    <h5 class="mb-1" id="staticBackdropLabel">UPDATE SHIPPING INSTRUCTION SI NO: {{ $transfer->shipping_number }}</h5>
                                                                </div>
                                                                <div class="p-4">
                                                                    <form class="form" method="POST" action="{{ route('admin.updateShippingInstructionDetails', $transfer->shipping_id) }}">
                                                                        @csrf
                                                                        <div class="row row-cols-sm-3 g-2">
                                                                            <div class="mb-2">
                                                                                <label class="my-1 fs-xs fw-bold" style="font-size: 85% !important;">CONTAINER NUMBER</label>
                                                                                <input type="text" name="containerNumber" class="form-control form-control-lg" placeholder="--" style="height: 62% !important;" required>
                                                                            </div>
                                                                            <div class="mb-2">
                                                                                <label class="my-1 fs-xs fw-bold" style="font-size: 85% !important;">CONTAINER TARE</label>
                                                                                <input type="number" step="0.01" name="tare" class="form-control form-control-lg" placeholder="--" style="height: 62% !important;" required>
                                                                            </div>
                                                                            <div class="mb-2">
                                                                                <label class="my-1 fs-xs fw-bold" style="font-size: 85% !important;">CLEARING AGENT</label>
                                                                                <select name="agent" class="form-select js-choice" required data-options='{"removeItemButton":true,"placeholder":true}'>
                                                                                    <option selected disabled value="">-- select clearing agent -- </option>
                                                                                    @foreach($agents as $agent)
                                                                                        <option value="{{ $agent->agent_id }}">{{ $agent->agent_name }}</option>
                                                                                    @endforeach
                                                                                </select>
                                                                            </div>

                                                                            <div class="mb-2">
                                                                                <label class="my-1 fs-xs fw-bold" style="font-size: 85% !important;">SEAL NUMBER</label>
                                                                                <input type="text" name="seal" class="form-control form-control-lg" placeholder="--" style="height: 62% !important;" required>
                                                                            </div>

                                                                            <div class="mb-2">
                                                                                <label class="my-1 fs-xs fw-bold" style="font-size: 85% !important;">CARGO ESCORTED?</label>
                                                                                <select name="escort" class="form-select js-choice" required data-options='{"removeItemButton":true,"placeholder":true}'>
                                                                                    <option selected disabled value="">-- select option -- </option>
                                                                                    <option value="1">YES</option>
                                                                                    <option value="2">NO</option>
                                                                                </select>
                                                                            </div>

                                                                            <div class="mb-2">
                                                                                <label class="my-1 fs-xs fw-bold" style="font-size: 85% !important;">TRANSPORTER</label>
                                                                                <select name="transporter" class="form-select js-choice" required data-options='{"removeItemButton":true,"placeholder":true}'>
                                                                                    <option selected disabled value="">-- select transporter -- </option>
                                                                                    @foreach($transporters as $transporter)
                                                                                        <option value="{{ $transporter->transporter_id }}">{{ $transporter->transporter_name }}</option>
                                                                                    @endforeach
                                                                                </select>
                                                                            </div>

                                                                            <div class="mb-2">
                                                                                <label class="my-1 fs-xs fw-bold" style="font-size: 85% !important;">VEHICLE REGISTRATION</label><br>
                                                                                <input class="form-control form-control-lg" name="registration" id="editableSelect" type="text" list="optionsList" placeholder="-- plate number --" required>
                                                                                <datalist id="optionsList">
                                                                                    @foreach($registrations as $registration => $transporter)
                                                                                        <option value="{{ $registration }}">{{ $registration }} </option>
                                                                                    @endforeach
                                                                                </datalist>
                                                                            </div>

                                                                            <div class="mb-2">
                                                                                <label class="my-1 fs-xs fw-bold" style="font-size: 85% !important;">DRIVER'S ID NUMBER</label> <br>
                                                                                <input id="idSelect" type="text" list="idList" name="idNumber" class="form-control form-control-lg idSelect" placeholder="-- driver's ID Number --" required>
                                                                                <datalist id="idList">
                                                                                    @foreach($users as $user)
                                                                                        <option value="{{ $user->id_number }}">{{ $user->id_number }}</option>
                                                                                    @endforeach
                                                                                </datalist>
                                                                            </div>

                                                                            <div class="mb-2">
                                                                                <label class="my-1 fs-xs fw-bold" style="font-size: 85% !important;">DRIVER'S NAME</label>
                                                                                <input type="text" name="driverName" id="driverName" class="form-control form-control-lg driverName" required>
                                                                            </div>

                                                                            <div class="mb-4">
                                                                                <label class="my-1 fs-xs fw-bold" style="font-size: 85% !important;">DRIVER'S PHONE NUMBER</label>
                                                                                <input type="text" name="driverPhone" id="driverPhone" class="form-control form-control-lg driverPhone" required>
                                                                            </div>
                                                                        </div>
                                                                        <div class="d-flex justify-content-center mt-2">
                                                                            <button type="submit" class="btn btn-success" onclick="return confirm('Once submitted you can not change shipping instruction. Are you sure you want to proceed?')">UPDATE SHIPPING INSTRUCTION</button>
                                                                        </div>
                                                                    </form>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                {{--@elseif($transfer->status == 2)
                                                    @if($transfer->location_id == auth()->user()->station->location->location_id && auth()->user()->role_id == 3)
                                                        <a class="link text-danger" data-bs-toggle="tooltip" data-bs-placement="left" title="Send SI for approval" onclick="return confirm('Are you sure you want to send this SI for approval?')" href="{{ route('admin.updateShippingInstruction', $transfer->shipping_id) }}"><span class="fa-regular fa-paper-plane"></span></a>
                                                    @else
                                                        <a class="link text-danger" data-bs-toggle="tooltip" data-bs-placement="left" title="SI updated, pending submission for approval"><span class="fa-solid fa-spinner"></span></a>
                                                    @endif--}}

                                            @elseif($transfer->status == 2 || $transfer->status == 3)
                                                <a class="link text-danger" data-bs-toggle="tooltip" data-bs-placement="left" title="SI pending confirmation and shipping" onclick="return confirm('Are you sure you want to approve this SI? This will mark SI as shipped')" href="{{ route('admin.markAsShipped', $transfer->shipping_id) }}"><span class="fa-regular fa-thumbs-up"></span></a>
                                            @elseif($transfer->status == 4)
                                                <a class="link text-success" data-bs-toggle="tooltip" data-bs-placement="left" title="SI shipped, stock updated"><span class="fa-solid fa-check-double"></span></a>
                                            @endif

                                        <div class="dropdown font-sans-serif position-static" >
                                            <a class="link text-600 btn-sm dropdown-toggle btn-reveal" type="button" data-bs-toggle="dropdown" data-boundary="window" aria-haspopup="true" aria-expanded="false">
                                                <span class="fas fa-ellipsis-h fs-10"></span>
                                            </a>
                                            <div class="dropdown-menu dropdown-menu-end border py-0">
                                                <div class="py-2">
                                                    <a class="dropdown-item text-info" href="{{ route('admin.addShipmentTeas', $transfer->shipping_id) }}">View SI</a>
                                                    <a class="dropdown-item text-primary" href="{{ route('admin.downloadSIDocument', $transfer->shipping_id) }}">Download SI</a>
                                                    <a class="dropdown-item text-dark" href="{{ route('admin.downloadDriverClearance', $transfer->shipping_id) }}"> Driver Clearance</a>
                                                    @if($transfer->status < 4)
                                                        <a class="dropdown-item text-danger" onclick="return confirm('Are you sure you want to delete SI Number {{ $transfer->shipping_number }}?')" href="{{ route('admin.deleteShippingInstruction', $transfer->shipping_id) }}"> Delete SI</a>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                        @else
                                            @if($transfer->status == 0 || $transfer->status == 1)
                                                <a class="link text-info" data-bs-placement="left" title="Click to update OutTurn Report" data-bs-toggle="tooltip" href="{{ route('admin.updateOutTurnReport', $transfer->shipping_id) }}"><span class="fa-solid fa-pen-to-square"></span></a>
                                            @elseif($transfer->status == 2 || $transfer->status == 3)
                                                <a class="link text-danger" data-bs-toggle="tooltip" data-bs-placement="left" title="Blend pending confirmation and shipping" onclick="return confirm('Are you sure you want to approve this Blend? This will mark Blend as shipped')" href="{{ route('admin.markBlendTeaAsShipped', $transfer->shipping_id) }}"><span class="fa-regular fa-thumbs-up"></span></a>
                                            @else
                                                <a class="link text-success" data-bs-toggle="tooltip" data-bs-placement="left" title="Blend shipped, stock updated"><span class="fa-solid fa-check-double"></span></a>
                                            @endif

                                            <div class="dropdown font-sans-serif position-static" >
                                                <a class="link text-600 btn-sm dropdown-toggle btn-reveal" type="button" data-bs-toggle="dropdown" data-boundary="window" aria-haspopup="true" aria-expanded="false">
                                                    <span class="fas fa-ellipsis-h fs-10"></span>
                                                </a>
                                                <div class="dropdown-menu dropdown-menu-end border py-0">
                                                    <div class="py-2">
                                                        <a class="dropdown-item text-info" href="{{ route('admin.addBlendTeas', $transfer->shipping_id) }}">View Blend Sheet</a>
                                                        <a class="dropdown-item text-primary" href="{{ route('admin.downloadBlendSheet', $transfer->shipping_id) }}">Download Blend Sheet</a>
                                                        <a class="dropdown-item text-dark" href="{{ route('admin.downloadOutturReport', $transfer->shipping_id) }}"> Download Outturn Report</a>
                                                        <a class="dropdown-item text-secondary" href="{{ route('admin.downloadBlendDriverClearance', $transfer->shipping_id) }}"> Download Driver Clearance</a>
                                                        @if($transfer->status < 4)
                                                            <a class="dropdown-item text-danger" onclick="return confirm('Are you sure you want to delete SI Number {{ $transfer->shipping_number }}?')" href="{{ route('admin.deleteBlendSheet', $transfer->shipping_id) }}"> Delete Blend Sheet</a>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
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
<script>
    $(document).ready(function() {
        $('#datatable').DataTable({
            order: [0, 'asc'],
            pageLength: 50
        });

    });

        document.addEventListener("DOMContentLoaded", function () {
            var input = document.getElementById("editableSelect");
            new Awesomplete(input, {
                list: "#optionsList",
                autoFirst: false, // Automatically select the first option
                minChars: 2,
                filter: function (text, input) {
                    return Awesomplete.FILTER_CONTAINS(text, input.match(/[^,]*$/)[0]);
                },
                replace: function (text) {
                    this.input.value = text; // Replace entire input value with selected option
                }
            });
        });

        document.addEventListener("DOMContentLoaded", function () {
            var input = document.getElementById("idSelect");
            new Awesomplete(input, {
                list: "#idList",
                autoFirst: true, // Automatically select the first option
                minChars: 3,
                filter: function (text, input) {
                    return Awesomplete.FILTER_CONTAINS(text, input.match(/[^,]*$/)[0]);
                },
                replace: function (text) {
                    this.input.value = text; // Replace entire input value with selected option
                }
            });
        });

        document.addEventListener("DOMContentLoaded", function () {
            var input = document.getElementById("editableSelected");
            new Awesomplete(input, {
                list: "#optionsListed",
                autoFirst: false, // Automatically select the first option
                minChars: 2,
                filter: function (text, input) {
                    return Awesomplete.FILTER_CONTAINS(text, input.match(/[^,]*$/)[0]);
                },
                replace: function (text) {
                    this.input.value = text; // Replace entire input value with selected option
                }
            });
        });

        document.addEventListener("DOMContentLoaded", function () {
            var input = document.getElementById("idSelected");
            new Awesomplete(input, {
                list: "#idListed",
                autoFirst: true, // Automatically select the first option
                minChars: 3,
                filter: function (text, input) {
                    return Awesomplete.FILTER_CONTAINS(text, input.match(/[^,]*$/)[0]);
                },
                replace: function (text) {
                    this.input.value = text; // Replace entire input value with selected option
                }
            });
        });

        $(document).ready(function () {
            $('.idSelect').on('change', function () {

                var idNumber = $(this).val();

                $.ajax({
                    url: '{{ route('admin.fetchIdNumber') }}',
                    method: 'GET',
                    data: {idNumber},
                    dataType: 'json',
                    success: function (response) {
                        console.log('Success:', response.driver_name);

                        $('.driverName').val(response.driver_name)
                        $('.driverPhone').val(response.driver_phone)
                    },
                    error: function (xhr, status, error) {
                        // Function to handle errors
                        console.error('Error:', error);
                        $('#driverName').val('')
                        $('#driverPhone').val('')
                    }
                });
            });
        });
</script>
