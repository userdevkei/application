@extends('clerk::layouts.default')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/2.1.5/css/dataTables.dataTables.css">
@section('clerk::dashboard')
    <div class="card">
        <div class="card-header">
            <div class="row flex-between-center">
                <div class="col-6 col-sm-auto d-flex align-items-center pe-0">
                    <h5 class="fs-9 mb-0 text-nowrap py-0 py-xl-0">Add Blend Sheet </h5>
                </div>
            </div>
        </div>
        <div class="card-body overflow-hidden p-lg-3">
            <div class="row align-items-center">
                <div class="tab-pane preview-tab-pane active" role="tabpanel" aria-labelledby="tab-dom-c3976e0e-38db-410e-861a-36d04a3a7494" id="dom-c3976e0e-38db-410e-861a-36d04a3a7494">
                    <form method="POST" action="{{ route('admin.addBlendSheet') }}">
                        @csrf
                        <div class="row g-2 needs-validation" novalidate="">
                            <div class="col-md-4 mb-3">
                                <label for="organizerSingle">CLIENTS</label>
                                <select class="form-select js-choice" name="client" data-options='{"removeItemButton":true,"placeholder":true}'>
                                    <option selected disabled value="">Select Client...</option>
                                    @foreach($clients as $client)
                                        <option value="{{ $client->client_id }}">{{ $client->client_name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="floatingInputValid">GARDEN NAME</label>
                                <input class="form-control form-control-lg"  type="text" name="gardenName" placeholder="provide garden name" />
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="floatingInputValid">GRADE NAME</label>
                                <input class="form-control form-control-lg"  type="text" name="blendGrade" placeholder="provide grade name" />
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="organizerSingle">PREPARED AT</label>
                                <select class="form-select js-choice" name="station" data-options='{"removeItemButton":true,"placeholder":true}'>
                                    <option disabled selected value="">Select PHML Warehouse...</option>
                                    @foreach($stations as $station)
                                        <option value="{{ $station->station_id }}">{{ $station->station_name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="organizerSingle">DESTINATION PORT</label>
                                <select class="form-select js-choice" id="selectedWarehouse" size="1" name="destination" data-options='{"removeItemButton":true,"placeholder":true}'>
                                    <option disabled selected value="">Select Port ...</option>
                                    @foreach($ports as $port)
                                        <option value="{{ $port->destination_id }}">{{ $port->port_name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="organizerSingle">CONTAINER SIZE</label>
                                <select class="form-select js-choice" name="containerSize" data-options='{"removeItemButton":true,"placeholder":true}'>
                                    <option disabled selected value="">Select Tea Type...</option>
                                    <option value="1">20 FT</option>
                                    <option value="2">40 FT TEAS</option>
                                    <option value="3">40 FTHC</option>
                                </select>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="organizerSingle">LOADING TYPE</label>
                                <select class="form-select js-choice" name="packagingType" data-options='{"removeItemButton":true,"placeholder":true}'>
                                    <option disabled selected value="">Select Tea Type...</option>
                                    <option value="1"> PALLETIZED CARDBOARD </option>
                                    <option value="2"> PALLETIZED SLIPSHEET </option>
                                    <option value="3"> PALLETIZED WOODEN </option>
                                    <option value="4"> LOOSE LOADING </option>
                                </select>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="floatingInputValid">BLEND NUMBER</label>
                                <input class="form-control form-control-lg"  type="text" name="shipmentNumber" placeholder="provide blend number" />
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="floatingInputValid">CONTRACT NUMBER</label>
                                <input class="form-control form-control-lg"  type="text" name="contract" placeholder="provide contract number" />
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="floatingInputValid">SHIPPING MARK</label>
                                <input class="form-control form-control-lg" id="totalWeight" type="text" name="mark" placeholder="provide shipping mark" />
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="floatingInputValid">CONSIGNEE</label>
                                <input class="form-control form-control-lg" id="totalWeight" type="text" name="consignee" placeholder="provide consignee" />
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="floatingInputValid">VESSEL NAME</label>
                                <input class="form-control form-control-lg" type="text" name="vessel" placeholder="provide vessel name" />
                            </div>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label for="floatingInputValid">STANDARD DETAILS</label>
                            <textarea type="text" class="form-control form-control-lg" name="shippingInstruction" placeholder="shipping instruction" required> </textarea>
                        </div>
                        <div class="d-flex justify-content-center">
                            <button type="submit" class="btn btn-md btn-success col-6">SAVE SHIPPING INSTRUCTION</button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>

    {{--    <script src="https://code.jquery.com/jquery-3.7.1.js"></script>--}}
    {{--    <script src="https://cdn.datatables.net/2.1.5/js/dataTables.js"></script>--}}
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
