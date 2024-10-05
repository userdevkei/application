@extends('admin::layouts.default')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/2.1.5/css/dataTables.dataTables.css">
@section('admin::dashboard')
    <div class="card">
        <div class="card-header">
            <div class="row flex-between-center">
                <div class="col-6 col-sm-auto d-flex align-items-center pe-0">
                    <h5 class="fs-9 mb-0 text-nowrap py-0 py-xl-0">Add Delivery Order </h5>
                </div>
            </div>
        </div>
        <div class="card-body overflow-hidden p-lg-3">
            <div class="row align-items-center">
                <div class="tab-pane preview-tab-pane active" role="tabpanel" aria-labelledby="tab-dom-c3976e0e-38db-410e-861a-36d04a3a7494" id="dom-c3976e0e-38db-410e-861a-36d04a3a7494">
                    <form id="doForm" method="POST" action="{{ route('admin.registerDeliveryOrder') }}">
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
                                <select class="form-select js-choice" size="1" name="packet" data-options='{"removeItemButton":true,"placeholder":true}'>
                                    <option disabled selected value="">Select Packages...</option>
                                    <?php $number = 1000; ?>
                                    @for ($i = 1; $i <= $number; $i++)
                                        <option value="{{ $i }}"> {{ $i . ' pkgs' }}</option>
                                    @endfor
                                </select>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="floatingInputValid">TOTAL NET WEIGHT</label>
                                <input class="form-control form-control-lg" type="number" step="0.01" name="weight" placeholder="provide total net weight" />
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="organizerSingle">BROKER</label>
                                <select class="form-select js-choice" size="1" name="broker_id" data-options='{"removeItemButton":true,"placeholder":true}'>
                                    <option disabled selected value="">Select Packages...</option>
                                    @foreach($brokers as $broker)
                                        <option value="{{ $broker->broker_id }}">{{ $broker->broker_name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="floatingInputValid">LOT NUMBER</label>
                                <input class="form-control form-control-lg" type="text" name="lot_number" placeholder="provide total net weight" />
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="floatingInputValid">SALE NUMBER</label>
                                <input class="form-control form-control-lg" type="text" name="sale_number" placeholder="provide total net weight" />
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="floatingInputValid">SALE DATE</label>
                                <input class="form-control form-control-lg" type="date" name="sale_date" placeholder="provide total net weight" />
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="floatingInputValid">PROMPT DATE</label>
                                <input class="form-control form-control-lg" id="totalWeight" type="date" name="prompt_date" placeholder="provide total net weight" />
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="organizerSingle">PRODUCER WAREHOUSE</label>
                                <select class="form-select js-choice" id="warehouse" size="1" name="warehouse_id" data-options='{"removeItemButton":true,"placeholder":true}'>
                                    <option disabled selected value="">Select Producer Warehouse...</option>
                                    @foreach($warehouses as $warehouse)
                                        <option value="{{ $warehouse->warehouse_id }}">{{ $warehouse->warehouse_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="organizerSingle">SUB WAREHOUSE </label>
                                <select class="form-select form-control-lg" id="branch" size="1" name="branch" data-options='{"removeItemButton":true,"placeholder":true}'>
                                    <option disabled selected value="">Select Warehouse locality...</option>
                                </select>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="organizerSingle">SUB WAREHOUSE LOCALITY</label>
                                <select class="form-select js-choice" id="warehouseBay" size="1" name="locality" data-options='{"removeItemButton":true,"placeholder":true}'>
                                    <option disabled selected value="">Select Warehouse Bay...</option>
                                    <option value="1">ISLAND</option>
                                    <option value="2">CHANGAMWE</option>
                                    <option value="3">JOMVU</option>
                                    <option value="4">BONJE</option>
                                    <option value="5">MIRITINI</option>
                                </select>
                            </div>

                        </div>
                        <div class="d-flex justify-content-center">
                            <button id="submitButton" type="submit" class="btn btn-md btn-success col-6">SAVE DELIVERY ORDER</button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>

    {{--    <script src="https://code.jquery.com/jquery-3.7.1.js"></script>--}}
    {{--    <script src="https://cdn.datatables.net/2.1.5/js/dataTables.js"></script>--}}
    <script>
        $(document).ready(function() {
            $('#warehouse').change(function () {
                var warehouseId = $('#warehouse').val();
                $.ajax({
                    type: 'GET',
                    url: '{{ route('clerk.filterWarehouseBranch') }}',
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

            $('#doForm').on('submit', function(event) {
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
        });
    </script>

@endsection
