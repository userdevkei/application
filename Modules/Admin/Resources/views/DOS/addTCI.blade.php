@extends('clerk::layouts.default')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/2.1.5/css/dataTables.dataTables.css">
@section('clerk::dashboard')
    <div class="card">
        <div class="card-header">
            <div class="row flex-between-center">
                <div class="col-6 col-sm-auto d-flex align-items-center pe-0">
                    <h5 class="fs-9 mb-0 text-nowrap py-0 py-xl-0">Teas Pending TCI </h5>
                </div>
                <div class="col-6 col-sm-auto ms-auto text-end ps-0">
                    <div id="table-simple-pagination-replace-element">
                        <a class="btn btn-falcon-default btn-sm" data-bs-toggle="modal" data-bs-target="#staticBackdrop"><span class="fas fa-filter" data-fa-transform="shrink-3 down-2"></span><span class="d-none d-sm-inline-block ms-1">New TCI</span></a>
                    </div>
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
                                <h5 class="mb-1" id="staticBackdropLabel">BUILD A TCI</h5>
                            </div>
                            <div class="p-4">
                                    <div class="row">
                                        <div class="col-4 mb-2">
                                            <label> WAREHOUSES </label>
                                            <select class="form-select js-choice" name="warehouse" id="warehouse">
                                                <option value="" selected>-- all warehouses --</option>
                                                @foreach($orders->groupBy('warehouse_name') as $warehouseName => $warehouse)
                                                    <option value="{{ $warehouse[0]->warehouse_id }}">{{ $warehouseName }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-4 mb-2">
                                            <label> SUB WAREHOUSES </label>
                                            <select class="form-select form-control-lg" name="sub_warehouse" id="branch">
                                                <option value="" selected>-- all warehouses --</option>
                                            </select>
                                        </div>
                                        <div class="col-4 mb-2">
                                            <label> CLIENT NAME</label>
                                            <select class="form-select form-control-lg" name="client" id="client">
                                                <option value="" selected>-- all clients --</option>
                                            </select>
                                        </div>

                                    </div>
                                <form id="tciForm" method="post" action="{{ route('clerk.createLLI') }}">
                                    @csrf

                                    <div class="mb-4" id="teasPane"></div>
                                    <div class="mb-4" id="detailsPane">
                                        <div class="row">
                                            <div class="col-4 mb-2">
                                                <label class="my-1 fs-xs fw-bold" style="font-size: 85% !important;">DESTINATION</label>
                                                <select name="station" id="colorSelect" class="form-select js-choice" required>
                                                    <option selected value="" disabled>-- select station --</option>
                                                    @foreach($stations as $station)
                                                        <option value="{{ $station->station_id }}">{{ $station->station_name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div class="col-4 mb-2">
                                                <label class="my-1 fs-xs fw-bold" style="font-size: 85% !important;">TRANSPORTER</label>
                                                <select name="transporter" id="colorSelect" class="form-select js-choice">
                                                    <option selected value="" disabled>-- select transporter --</option>
                                                    @foreach($transporters as $transporter)
                                                        <option value="{{ $transporter->transporter_id }}">{{ $transporter->transporter_name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-4 mb-2">
                                                <label class="my-1 fs-xs fw-bold" style="font-size: 85% !important;">VEHICLE REGISTRATION</label><br>
                                                <input class="form-control form-control-lg" value="" name="registration" id="editableSelect" type="text" list="optionsList" placeholder="-- vehicle registration number --">
                                                <datalist id="optionsList">
                                                    @foreach($registrations as $registration => $transporter)
                                                        <option value="{{ $registration }}">{{ $registration }}</option>
                                                    @endforeach
                                                </datalist>
                                            </div>
                                            <div class="col-4 mb-2">
                                                <label class="my-1 fs-xs fw-bold" style="font-size: 85% !important;">DRIVER'S ID NUMBER</label><br>
                                                <input id="idSelect" type="text" value="" list="idList" name="idNumber" class="form-control form-control-lg idSelect" placeholder="-- driver's ID Number --" >
                                                <datalist id="idList">
                                                    @foreach($drivers as $user)
                                                        <option value="{{ $user->id_number }}">{{ $user->id_number }}</option>
                                                    @endforeach
                                                </datalist>
                                            </div>
                                            <div class="col-4 mb-2">
                                                <label class="my-1 fs-xs fw-bold" style="font-size: 85% !important;">DRIVER'S NAME</label>
                                                <input type="text" value="" name="driverName" id="driverName" class="form-control form-control-lg driverName">
                                            </div>
                                            <div class="col-4 mb-4">
                                                <label class="my-1 fs-xs fw-bold" style="font-size: 85% !important;">DRIVER'S PHONE NUMBER</label>
                                                <input type="text" value="" name="driverPhone" id="driverPhone" class="form-control form-control-lg driverPhone" >
                                            </div>
                                        </div>
                                        <div class="d-flex justify-content-center mt-4">
                                            <button type="submit" id="submitButton" class="btn col-8 btn-md btn-falcon-success">CREATE TCI</button>
                                        </div>
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
                            <th>Client Name</th>
                            <th>Inv No</th>
                            <th>Garden Name</th>
                            <th>Grade</th>
                            <th>Lot No</th>
                            <th>Producer Whs</th>
                            <th>Sub-Warehouse</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($orders as $order)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $order->client_name }}</td>
                                <td>{{ $order->invoice_number }}</td>
                                <td>{{ $order->garden_name }}</td>
                                <td>{{ $order->grade_name }}</td>
                                <td>{{ $order->lot_number }}</td>
                                <td>{{ $order->warehouse_name }}</td>
                                <td>{{ $order->sub_warehouse_name }}</td>
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

        $('#detailsPane').hide();
        $('#teasPane').hide();

        $('#warehouse').change(function () {
            var warehouseId = $('#warehouse').val();
            $.ajax({
                type: 'GET',
                url: '{{ route('clerk.filterByGarden') }}',
                data: { warehouseId },
                success: function (response) {
                    // console.log(response)

                    $('#branch').empty();

                    $('#branch').append('<option disabled selected class="text-center" value="">-- Select warehouse branch --</option>');
                    // Iterate over the response data and populate the select
                    $.each(response, function(index, branch) {
                        $('#branch').append('<option value="' + branch.sub_warehouse_id + '">' + branch.sub_warehouse_name + '</option>');
                    });
                },
                error: function (xhr, status, error) {
                    // Function to handle errors
                    console.error('Error:', error);
                }
            });

        });

        $('#branch').change(function () {
            var warehouseBranchId = $(this).val();
            var warehouseId = $('#warehouse').val();

            $.ajax({
                type: 'GET',
                url: '{{ route('clerk.filterByClient') }}',
                data: {warehouseBranchId, warehouseId},
                success: function (response) {
                    $('#client').empty(); // Clear previous options

                    $('#teasPane').hide();
                    $('#detailsPane').hide();

                    // Append the default option
                    $('#client').append('<option disabled selected class="text-center" value="">-- Select client account --</option>');

                    // Populate the select element with options from the response
                    $.each(response, function (index, client) {
                        $('#client').append('<option value="' + client.client_id + '">' + client.client_name + '</option>');
                    });
                },
                error: function (xhr) {
                    console.log("Error: ", xhr.responseText);
                }
            });
        });

        $('#client').change(function () {
            var clientId = $(this).val();
            var warehouseBranchId = $('#branch').val();
            var warehouseId = $('#warehouse').val();
            $.ajax({
                type: 'GET',
                url: '{{ route('clerk.filterBySaleNumber') }}',
                data: {warehouseBranchId, clientId, warehouseId},
                success: function (response) {
                    var tableHtml = '<table class="table table-sm table-bordered table-striped" id="datatable" ><thead><th>Select</th><th>Client</th><th>Garden</th><th>Grade</th><th>Order Number</th><th>Invoice Number</th><th>Sale Number</th><th>Pcks</th><th>Pkg</th><th>Prompt Date</th></thead><tbody>';
                    response.forEach(function (item) {
                        tableHtml += '<tr>';
                        tableHtml += '<td><input type="checkbox" name="deliveryIds[]" value="' + item.delivery_id + '"></td>';
                        tableHtml += '<td>' + item.client_name + '</td>';
                        tableHtml += '<td>' + item.garden_name + '</td>';
                        tableHtml += '<td>' + item.grade_name + '</td>';
                        tableHtml += '<td>' + item.order_number + '</td>';
                        tableHtml += '<td>' + item.invoice_number + '</td>';
                        tableHtml += '<td>' + item.sale_number + '</td>';
                        tableHtml += '<td>' + item.packet + '</td>';
                        tableHtml += '<td>' + (item.package == 1 ? 'PS' : 'PB') + '</td>';
                        tableHtml += '<td>' + item.prompt_date + '</td>';
                        tableHtml += '</tr>';
                    });
                    tableHtml += '</tbody></table>';


                    $('#teasPane').html(tableHtml);
                    $('#teasPane').show();
                    $('#detailsPane').show();
                },
                error: function (xhr, status, error) {
                    // Function to handle errors
                    console.error('Error:', error);
                }
            });
        });

        $('#tciForm').on('submit', function(event) {
            // event.preventDefault(); // Prevents the default form submission

            var form = $(this);
            var submitButton = $('#submitButton');

            // Simulate form submission process
            setTimeout(function() {
                // Assuming the form submission is successful, disable the button
                submitButton.prop('disabled', true);

                // You can also display a success message or perform other actions here
                // alert('Form submitted successfully!');
            }, 10); // Simulate a delay for the form submission process
        });

        $('.idSelect').on('change', function () {

            var idNumber = $(this).val();

            $.ajax({
                url: '{{ route('clerk.fetchIdNumber') }}',
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
