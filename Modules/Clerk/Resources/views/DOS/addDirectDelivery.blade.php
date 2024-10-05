@extends('clerk::layouts.default')
@section('clerk::dashboard')
    <div class="card">
        <div class="card-header">
            <div class="row flex-between-center">
                <div class="col-6 col-sm-auto d-flex align-items-center pe-0">
                    <h5 class="fs-9 mb-0 text-nowrap py-0 py-xl-0">Add Direct Delivery </h5>
                </div>
            </div>
        </div>
        <div class="card-body overflow-hidden p-lg-3">
            <div class="row align-items-center">
                <div class="tab-pane preview-tab-pane active" role="tabpanel" aria-labelledby="tab-dom-c3976e0e-38db-410e-861a-36d04a3a7494" id="dom-c3976e0e-38db-410e-861a-36d04a3a7494">
                    <form method="POST" action="{{ route('clerk.registerDirectDeliveryOrder') }}">
                        @csrf
                        <div class="row g-2 needs-validation" novalidate="">
                            <div class="col-md-4 mb-3">
                                <label for="organizerSingle">CLIENTS</label>
                                <select class="form-select js-choice" name="client_id" size="1" data-options='{"removeItemButton":true,"placeholder":true}'>
                                    <option selected disabled value="">Select Client...</option>
                                    @foreach($clients as $client)
                                        <option value="{{ $client->client_id }}">{{ $client->client_name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="organizerSingle">TEA TYPES</label>
                                <select class="form-select js-choice" name="tea_id" size="1" data-options='{"removeItemButton":true,"placeholder":true}'>
                                    <option disabled selected value="">Select Tea Type...</option>
                                    <option value="1">AUCTION TEAS</option>
                                    <option value="2">PRIVATE TEAS</option>
                                    <option value="3">FACTORY TEAS</option>
                                    <option value="4">BLEND REMNANTS</option>
                                </select>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="organizerSingle">TEA GARDENS</label>
                                <select class="form-select js-choice" size="1" name="garden_id" data-options='{"removeItemButton":true,"placeholder":true}'>
                                    <option disabled selected value="">Select Tea Garden...</option>
                                    @foreach($gardens as $garden)
                                        <option value="{{ $garden->garden_id }}">{{ $garden->garden_name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="organizerSingle">TEA GRADES</label>
                                <select class="form-select js-choice" size="1" name="grade_id" data-options='{"removeItemButton":true,"placeholder":true}'>
                                    <option disabled selected value="">Select Tea Grade...</option>
                                    @foreach($grades as $grade)
                                        <option value="{{ $grade->grade_id }}">{{ $grade->grade_name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="floatingInputValid">DO NUMBER</label>
                                <input class="form-control form-control-lg" type="text" name="order_number" placeholder="provide do number" />
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="floatingInputValid">INVOICE NUMBER</label>
                                <input class="form-control form-control-lg" type="text" name="invoice_number" placeholder="provide invoice number" />
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="organizerSingle">PACKAGE TYPE</label>
                                <select class="form-select js-choice" size="1" name="package" data-options='{"removeItemButton":true,"placeholder":true}'>
                                    <option disabled selected value="">Select Tea Type...</option>
                                    <option value="1">PAPER SACK (PS)</option>
                                    <option value="2">PAPER BAG (PB)</option>
                                </select>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="organizerSingle">TOTAL PACKAGES</label>
                                <select class="form-select js-choice" id="numberPackages" size="1" name="packet" data-options='{"removeItemButton":true,"placeholder":true}'>
                                    <option disabled selected value="">Select Packages...</option>
                                    <?php $number = 1000; ?>
                                    @for ($i = 1; $i <= $number; $i++)
                                        <option value="{{ $i }}"> {{ $i . ' pkgs' }}</option>
                                    @endfor
                                </select>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="floatingInputValid">TOTAL NET WEIGHT</label>
                                <input class="form-control form-control-lg" id="totalWeight" type="text" name="weight" placeholder="provide total net weight" />
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="organizerSingle">PACKAGE TARE</label>
                                <select class="form-select js-choice" id="packageTare" size="1" name="tare" data-options='{"removeItemButton":true,"placeholder":true}'>
                                    <option disabled selected value="">Select Packages...</option>
                                    <?php $number = 1; ?>
                                    @for ($i = 0; $i <= $number; $i += 0.1)
                                        <option value="{{ $i }}"> {{ $i . ' kgs' }}</option>
                                    @endfor
                                </select>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="organizerSingle">TOTAL PALLET WEIGHT</label>
                                <select class="form-select js-choice" id="palletWeight" size="1" name="pallet_weight" data-options='{"removeItemButton":true,"placeholder":true}'>
                                    <option disabled selected value="">Select Packages...</option>
                                    <?php $number = 500; ?>
                                    @for ($i = 0; $i <= $number; $i++)
                                        <option value="{{ $i }}"> {{ $i . ' kgs' }}</option>
                                    @endfor
                                </select>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="floatingInputValid">TOTAL GROSS WEIGHT</label>
                                <input type="text" class="form-control form-control-lg" id="netWeight" name="netWeight" placeholder="total gross weight" readonly>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="organizerSingle">PRODUCER WAREHOUSE</label>
                                <select class="form-select js-choice" id="organizerSingle" size="1" name="warehouse_id" data-options='{"removeItemButton":true,"placeholder":true}'>
                                    <option disabled selected value="">Select Producer Warehouse...</option>
                                    @foreach($warehouses as $warehouse)
                                        <option value="{{ $warehouse->warehouse_id }}">{{ $warehouse->warehouse_name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="organizerSingle">PHML WAREHOUSE</label>
                                <select class="form-select js-choice" id="selectedWarehouse" size="1" name="station_id" data-options='{"removeItemButton":true,"placeholder":true}'>
                                    <option disabled selected value="">Select PHML Warehouse...</option>
                                    @foreach($stations as $station)
                                        <option value="{{ $station->station_id }}">{{ $station->station_name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="organizerSingle">PHML WAREHOUSE BAYS</label>
                                <select class="form-select form-control-lg" id="warehouseBay" size="1" name="bay" data-options='{"removeItemButton":true,"placeholder":true}'>
                                    <option disabled selected value="">Select Warehouse Bay...</option>
                                </select>
                            </div>
                        </div>
                        <div class="d-flex justify-content-center mb-2 mt-3">
                            <button type="submit" class="btn btn-md btn-success col-6">SAVE DIRECT DELIVERY</button>
                        </div>
                    </form>
            </div>
        </div>

    </div>
    </div>

    <script>

        function calcNetWeight(totalWeight, numberPackages, packageTare, palletWeight) {
            var grossWeight = (parseFloat(numberPackages) * parseFloat(packageTare)) + parseFloat(palletWeight);
            var netWeight = parseFloat(totalWeight) + grossWeight;
            // console.log(netWeight)
            $('#netWeight').val(isNaN(netWeight) ? null : netWeight);
        }

        $(document).ready(function() {
            // Attach change event handler to all input fields
            $(document).on('change', '#numberPackages, #totalWeight, #packageTare, #palletWeight', function() {
                var numberPackages = $('#numberPackages')
                    .val(); // Get the value of the numberPackages input
                var totalWeight = $('#totalWeight').val(); // Get the value of the totalWeight input
                var packageTare = $('#packageTare').val(); // Get the value of the packageTare input
                var palletWeight = $('#palletWeight').val(); // Get the value of the palletWeight input

                console.log(numberPackages)
                console.log(totalWeight)
                console.log(packageTare)
                console.log(palletWeight)
                calcNetWeight(totalWeight, numberPackages, packageTare, palletWeight);
            });

            $('#selectedWarehouse').on('change', function() {
                var selectedStation = $(this).val();
                console.log(selectedStation)
                $.ajax({
                    type: 'GET',
                    url: '{{ route('clerk.filterWarehouseBay') }}',
                    data: {
                        selectedStation
                    },
                    success: function(response) {
                        console.log(response)

                        $('#warehouseBay').empty();

                        // Append the default option
                        $('#warehouseBay').append('<option disabled selected class="text-center" value="">-- Select warehouse bay --</option>' );

                        // Populate the select element with options from the response
                        $.each(response, function(index, bay) {
                            $('#warehouseBay').append('<option value="' + bay.bay_id + '">' + bay.bay_name + '</option>');
                        });
                    }

                });

            });
        });
    </script>

@endsection
