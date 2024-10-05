@extends('admin::layouts.default')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/2.1.5/css/dataTables.dataTables.css">
<style>

</style>
@section('admin::dashboard')
    <div class="card">
        <div class="card-header">
            <div class="row flex-between-center mx-4">
                <div class="col-6 col-sm-auto d-flex align-items-center pe-0">
                    <h5 class="fs-9 mb-0 text-nowrap py-0 py-xl-0">Receive {{ $orders->first()->loading_number }} ({{ $orders->first()->client_name }}) </h5>
                </div>
            </div>
        </div>
        <div class="card-body overflow-hidden p-lg-6">
            <div class="row align-items-center">
                <form id="tciForm" method="post" action="{{ route('admin.receiveDelivery') }}">
                    @csrf
                    <div class="tab-pane preview-tab-pane active" role="tabpanel" aria-labelledby="tab-dom-c3976e0e-38db-410e-861a-36d04a3a7494" id="dom-c3976e0e-38db-410e-861a-36d04a3a7494">
                        <table class="table mb-0 table-bordered table-striped " id="datatable">
                            <thead class="bg-200">
                            <tr>
                                <th>#</th>
                                <th>Inv No</th>
                                <th>Garden Name</th>
                                <th>Grade</th>
                                <th>Lot Number</th>
                                <th>Sale Number</th>
                                <th>Packages</th>
                                <th>Weight</th>
                                <th>Tare Weight</th>
                                <th>Pallete Weight</th>
                                <th>Gross Weight</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($orders as $index => $order)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $order->invoice_number }}</td>
                                    <td>{{ $order->garden_name }}</td>
                                    <td>{{ $order->grade_name }}</td>
                                    <td>{{ $order->lot_number }}</td>
                                    <td>{{ $order->sale_number }}</td>
                                    <td> <input type="number" name="orders[{{ $index }}]['numberPackages]" class="form-control numberPackages" min="0" max="{{ $order->maxPallets }}" value="{{ $order->maxPallets }}"> </td>
                                    <td> <input type="number" name="orders[{{ $index }}]['netWeight']" class="form-control netWeight" min="0" max="{{ $order->maxWeight }}" value="{{ $order->maxWeight }}"> </td>
                                    <td>
                                            <?php $pTare = 1; $pWeight = 500; ?>
                                        <select class="form-select form-control-sm packageTare" name="orders[{{ $index }}]['packageTare']">
                                            <option selected disabled value="">-- package tare --</option>
                                            @for ($i = 0; $i <= $pTare; $i += 0.1)
                                                <option value="{{ $i }}"> {{ $i }} KGS</option>
                                            @endfor
                                        </select>
                                    </td>
                                    <td>
                                        <select class="form-select form-control-sm paletteTare" name="orders[{{ $index }}]['paletteTare']">
                                            <option selected disabled value="">-- pallete weight--</option>
                                            @for ($i = 0; $i <= $pWeight; $i++)
                                                <option value="{{ $i }}"> {{ $i }} KGS</option>
                                            @endfor
                                        </select>
                                    </td>
                                    <td>
                                        <input type="number" name="orders[{{ $index }}]['grossWeight']" class="form-control grossWeight" readonly value="{{ $order->weight }}">
                                        <input type="hidden" name="orders[{{ $index }}]['deliveryId']" value="{{ $order->delivery_id }}">
                                        <input type="hidden" name="orders[{{ $index }}]['invNumber']" value="{{ $order->invoice_number }}">
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                        <div class="mt-4">
                            <fieldset class="border p-2">
                                <legend class="float-none w-auto fs-sm fw-bold">Tea Details</legend>
                                <div class="row">
                                    <div class="col-4">
                                        <label class="my-1 fs-xs fw-bold" style="font-size: 85% !important;">DELIVERY NUMBER</label>
                                        <input type="text" class="form-control form-control-lg" name="delivery_number" placeholder="---">
                                    </div>
                                    <div class="col-4">
                                        <label class="my-1 fs-xs fw-bold" style="font-size: 85% !important;">PHML WAREHOUSES</label>
                                        <select name="station" class="form-control form-control-lg js-choice" id="recStation" required>>
                                            <option value="" disabled selected>-- select warehouse --</option>
                                            @foreach($stations as $station)
                                                <option value="{{ $station->station_id }}">{{ $station->station_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-4">
                                        <label class="my-1 fs-xs fw-bold" style="font-size: 85% !important;">WAREHOUSE BAY</label>
                                        <select name="bay" class="form-control form-control-lg" id="selectedBay" required>
                                            <option value="" disabled selected>-- select warehouse --</option>
                                        </select>
                                    </div>
                                </div>
                            </fieldset>
                        </div>

                        <div class="mt-4">
                            <fieldset class="border p-2">
                                <legend class="float-none w-auto fs-sm fw-bold">Transport Details</legend>
                                <div class="row">
                                    <div class="col-4 mb-2">
                                        <label class="my-1 fs-xs fw-bold" style="font-size: 85% !important;">TRANSPORTER</label>
                                        <select name="transporter" id="colorSelect" class="form-select js-choice" required>
                                            <option selected value="" disabled>-- select transporter --</option>
                                            @foreach($transporters as $transporter)
                                                <option value="{{ $transporter->transporter_id }}">{{ $transporter->transporter_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-4 mb-2">
                                        <label class="my-1 fs-xs fw-bold" style="font-size: 85% !important;">VEHICLE REGISTRATION</label><br>
                                        <input class="form-control form-control-lg" value="" name="registration" id="editableSelect" type="text" list="optionsList" placeholder="-- vehicle registration number --" required>
                                        <datalist id="optionsList">
                                            @foreach($registrations as $registration => $transporter)
                                                <option value="{{ $registration }}">{{ $registration }}</option>
                                            @endforeach
                                        </datalist>
                                    </div>
                                    <div class="col-4 mb-2">
                                        <label class="my-1 fs-xs fw-bold" style="font-size: 85% !important;">DRIVER'S ID NUMBER</label><br>
                                        <input id="idSelect" type="text" value="" list="idList" name="idNumber" class="form-control form-control-lg idSelect" placeholder="-- driver's ID Number --" required>
                                        <datalist id="idList">
                                            @foreach($drivers as $user)
                                                <option value="{{ $user->id_number }}">{{ $user->id_number }}</option>
                                            @endforeach
                                        </datalist>
                                    </div>
                                    <div class="col-4 mb-2">
                                        <label class="my-1 fs-xs fw-bold" style="font-size: 85% !important;">DRIVER'S NAME</label>
                                        <input type="text" value="" name="driverName" id="driverName" class="form-control form-control-lg driverName" required">
                                    </div>
                                    <div class="col-4 mb-4">
                                        <label class="my-1 fs-xs fw-bold" style="font-size: 85% !important;">DRIVER'S PHONE NUMBER</label>
                                        <input type="text" value="" name="driverPhone" id="driverPhone" class="form-control form-control-lg driverPhone" required>
                                    </div>
                                    <div class="col-4 mb-4">
                                        <label class="my-1 fs-xs fw-bold" style="font-size: 85% !important;">DATE RECEIVED</label>
                                        <input type="date" class="form-control form-control-lg" name="date_received" value="">
                                    </div>
                                </div>
                            </fieldset>
                            <div class="d-flex justify-content-center mt-5">
                                <button type="submit" id="submitButton" class="btn btn-success col-7">RECEIVE DELIVERY</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
<script src="https://code.jquery.com/jquery-3.7.1.js"></script>
<script src="https://cdn.datatables.net/2.1.5/js/dataTables.js"></script>
<script>
    $(document).ready(function() {
        $('#datatable').DataTable( {
            order: [ 0, 'asc' ],
            pageLength: 50
        } );

        $('#recStation').change( function () {
            var selectedStation = $(this).val();
            console.log(selectedStation)
            $.ajax({
                type: 'GET',
                url: '{{ route('clerk.filterWarehouseBay') }}',
                data: { selectedStation },
                success:function (response) {
                    console.log(response)

                    $('#selectedBay').empty();

                    // Append the default option
                    $('#selectedBay').append('<option disabled selected class="text-center" value="">-- Select warehouse bay --</option>');

                    // Populate the select element with options from the response
                    $.each(response, function(i, bay) {
                        $('#selectedBay').append('<option value="' + bay.bay_id + '">' + bay.bay_name + '</option>');
                    });
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

    } );

    // Attach change event handler to all input fields
    $(document).on('change', '.numberPackages, .netWeight, .packageTare, .paletteTare', function() {
        // Get the closest row
        let row = $(this).closest('tr');

        // Get input values
        let numberPackages = parseFloat(row.find('.numberPackages').val()) || 0; // Default to 0 if empty
        let netWeight = parseFloat(row.find('.netWeight').val()) || 0;
        let packageTare = parseFloat(row.find('.packageTare').val()) || 0;
        let paletteTare = parseFloat(row.find('.paletteTare').val()) || 0;

        // Calculate tare weight
        let tareWeight = (numberPackages * packageTare) + paletteTare;

        // Calculate gross weight
        let grossWeight = netWeight + tareWeight;

        // Update the gross weight input field
        row.find('.grossWeight').val(isNaN(grossWeight) ? '' : grossWeight.toFixed(2));
    });

    $(document).on('change', '#idSelect', function () {
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
</script>
