@extends('admin::layouts.default')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/2.1.5/css/dataTables.dataTables.css">
<style>

</style>
@section('admin::dashboard')
    <div class="card">
        <div class="card-header">
            <div class="row flex-between-center">
                <div class="col-6 col-sm-auto d-flex align-items-center pe-0">
                    <h5 class="fs-9 mb-0 text-nowrap py-0 py-xl-0">Receive {{ $order->invoice_number }} ({{ $order->client_name }}) </h5>
                </div>
            </div>
        </div>
        <div class="card-body overflow-hidden p-lg-3">
            <div class="row align-items-center">
                <form id="tciForm" method="post" action="{{ route('admin.updateStock', $order->stock_id) }}">
                    @csrf
                    <div class="tab-pane preview-tab-pane active" role="tabpanel" aria-labelledby="tab-dom-c3976e0e-38db-410e-861a-36d04a3a7494" id="dom-c3976e0e-38db-410e-861a-36d04a3a7494">
                        <fieldset class="border p-2">
                            <legend class="float-none w-auto fw-bold">Tea Details</legend>
                            <div class="row row-cols-sm-3 g-2">
                                <input type="hidden" value="{{ $order->stock_id }}" name="delivery_id">
                                <div class="mb-2">
                                    <label class="my-1 fs-xs fw-bold" style="font-size: 85% !important;">PACKAGES RECEIVED</label>
                                    <input type="number" name="numberPackages" id="numberPackages" class="form-control form-control-lg numberPackages" value="{{ $order->total_pallets }}" placeholder="--" required/>
                                </div>

                                <div class="mb-2">
                                    <label class="my-1 fs-xs fw-bold" style="font-size: 85% !important;">TEA NET WEIGHT (DO WEIGHT)</label>
                                    <input type="number" name="total_weight" id="totalWeight" value="{{ $order->net_weight }}" class="form-control form-control-lg totalWeight" placeholder="--" required/>
                                </div>
                                <div class="mb-2">
                                    <label class="my-1 fs-xs fw-bold" style="font-size: 85% !important;">PACKAGE TARE</label>
                                    <select name="tare" id="packageTare" class="form-select js-choice form-select-lg packageTare" required data-options='{"removeItemButton":true,"placeholder":true}'>
                                        <?php $tare = 1; ?>
                                        <option selected disabled value="">-- select package tare --</option>
                                        @for($i = 0.0; $i<= $tare; $i += 0.1)
                                            <option @if($order->package_tare == $i) selected @endif value="{{ $i }}"> {{ $i.' kgs' }}</option>
                                        @endfor
                                    </select>
                                </div>
                                <div class="mb-2">
                                    <label class="my-1 fs-xs fw-bold" style="font-size: 85% !important;">PALLET WEIGHT</label>
                                    <select name="pallet_weight" id="palletWeight'" class="form-select js-choice palletWeight" required data-options='{"removeItemButton":true,"placeholder":true}'>
                                        <?php $palettes = 200; ?>
                                        @for($i = 0; $i <= $palettes; $i++ )
                                            <option @if($order->pallet_weight == $i ) selected
                                                    @endif  value="{{ $i }}"> {{ $i }} KGS
                                            </option>
                                        @endfor
                                    </select>
                                </div>

                                <div class="mb-2">
                                    <label class="my-1 fs-xs fw-bold" style="font-size: 85% !important;">TEA GROSS WEIGHT</label>
                                    <input type="text" readonly required min="0" name="netWeight" value="{{ $order->total_weight == null ? ' ': $order->total_weight }}" id="netWeight" placeholder="--" class="form-control form-control-lg netWeight">
                                </div>

                                    <div class="mb-2">
                                        <label class="my-1 fs-xs fw-bold" style="font-size: 85% !important;">DELIVERY NUMBER</label>
                                        <input type="text" class="form-control form-control-lg" name="delivery_number" placeholder="---" value="{{ $order->delivery_number }}">
                                    </div>
                                    <div class="col-4">
                                        <label class="my-1 fs-xs fw-bold" style="font-size: 85% !important;">PHML WAREHOUSES</label>
                                        <select name="station" class="form-control form-control-lg js-choice" id="recStation" required data-options='{"removeItemButton":true,"placeholder":true}'>
                                            <option value="" disabled selected>-- select warehouse --</option>
                                            @foreach($stations as $station)
                                                <option @if($order->station_id == $station->station_id) selected @endif value="{{ $station->station_id }}"> {{ $station->station_name }} </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="mb-2">
                                        <label class="my-1 fs-xs fw-bold" style="font-size: 85% !important;">WAREHOUSE BAY</label>
                                        <select name="bay" class="form-control form-control-lg" id="selectedBay" required>
                                            <option value="{{ $order->bay_id }}" selected>{{ $order->bay_name }}</option>
                                        </select>
                                    </div>
                                </div>
                            </fieldset>

                        <div class="mt-4">
                            <fieldset class="border p-2">
                                <legend class="float-none w-auto fw-bold">Transport Details</legend>
                                <div class="row">
                                    <div class="col-4 mb-2">
                                        <label class="my-1 fs-xs fw-bold" style="font-size: 85% !important;">TRANSPORTER</label>
                                        <select name="transporter" id="colorSelect" class="form-select js-choice" data-options='{"removeItemButton":true,"placeholder":true}'>
                                            <option selected value="" disabled>-- select transporter --</option>
                                            @foreach($transporters as $transporter)
                                                <option @if($order->transporter_id == $transporter->transporter_id) selected @endif value="{{ $transporter->transporter_id }}">{{ $transporter->transporter_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-4 mb-2">
                                        <label class="my-1 fs-xs fw-bold" style="font-size: 85% !important;">VEHICLE REGISTRATION</label><br>
                                        <input class="form-control form-control-lg" value="{{ $order->registration }}" name="registration" id="editableSelect" type="text" list="optionsList" placeholder="-- vehicle registration number --" >
                                        <datalist id="optionsList">
                                            @foreach($registrations as $registration => $transporter)
                                                <option value="{{ $registration }}">{{ $registration }}</option>
                                            @endforeach
                                        </datalist>
                                    </div>
                                    <div class="col-4 mb-2">
                                        <label class="my-1 fs-xs fw-bold" style="font-size: 85% !important;">DRIVER'S ID NUMBER</label><br>
                                        <input id="idSelect" type="text" value="{{ $order->id_number }}" list="idList" name="idNumber" class="form-control form-control-lg idSelect" placeholder="-- driver's ID Number --" >
                                        <datalist id="idList">
                                            @foreach($drivers as $user)
                                                <option value="{{ $user->id_number }}">{{ $user->id_number }}</option>
                                            @endforeach
                                        </datalist>
                                    </div>
                                    <div class="col-4 mb-2">
                                        <label class="my-1 fs-xs fw-bold" style="font-size: 85% !important;">DRIVER'S NAME</label>
                                        <input type="text" value="{{ $order->driver_name }}" name="driverName" id="driverName" class="form-control form-control-lg driverName">
                                    </div>
                                    <div class="col-4 mb-4">
                                        <label class="my-1 fs-xs fw-bold" style="font-size: 85% !important;">DRIVER'S PHONE NUMBER</label>
                                        <input type="text" value="{{ $order->phone }}" name="driverPhone" id="driverPhone" class="form-control form-control-lg driverPhone" >
                                    </div>
                                    <div class="col-4 mb-4">
                                        <label class="my-1 fs-xs fw-bold" style="font-size: 85% !important;">DATE RECEIVED</label>
                                        <input type="date" class="form-control form-control-lg" name="date_received" value="{{ Carbon\Carbon::parse($order->stock_date)->format('Y-m-d') }}">
                                    </div>
                                </div>
                            </fieldset>
                            <div class="d-flex justify-content-center mt-5">
                                <button type="submit" id="submitButton" class="btn btn-success col-7">UPDATE STOCK</button>
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
                url: '{{ route('admin.filterWarehouseBay') }}',
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

        // Attach change event handler to all input fields
        $('input[name^="numberPackages"], input[name^="total_weight"], select[name^="tare"], select[name^="pallet_weight"]').on('change', function() {
            var numberPackages = $('#numberPackages').val();
            var totalWeight = $('#totalWeight').val();
            var packageTare = $('#packageTare').val();
            var palletWeight = $('.palletWeight').val();

            console.log(numberPackages, totalWeight, packageTare, palletWeight)

            var netWeight = parseFloat(totalWeight) + parseFloat(numberPackages) * parseFloat(packageTare) + parseFloat(palletWeight);

            $('#netWeight').val(netWeight);
        });

    } );

    $(document).on('change', '#idSelect', function () {
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
</script>
