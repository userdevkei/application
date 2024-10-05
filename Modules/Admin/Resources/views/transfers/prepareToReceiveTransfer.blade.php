@extends('admin::layouts.default')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/2.1.5/css/dataTables.dataTables.css">
@section('admin::dashboard')
    <div class="card">
        <div class="card-header">
            <div class="row flex-between-center">
                <div class="col-6 col-sm-auto d-flex align-items-center pe-0">
                    <h5 class="fs-9 mb-0 text-nowrap py-0 py-xl-0">Internal Tea Transfers From <span class="text-danger">{!! $transfers[0]->station_name !!}</span> To <span class="text-success">{!! $transfers[0]->destination_name !!} </span></h5>
                </div>
                <div class="col-6 col-sm-auto ms-auto text-end ps-0">
                    <div id="table-simple-pagination-replace-element">
                        <span class="text-info">{!! $transfers[0]->client_name !!}</span>
                    </div>
                </div>

            </div>
        </div>
        <div class="card-body overflow-hidden p-lg-3">
            <div class="row align-items-center">
                <div class="tab-pane preview-tab-pane active" role="tabpanel" aria-labelledby="tab-dom-c3976e0e-38db-410e-861a-36d04a3a7494" id="dom-c3976e0e-38db-410e-861a-36d04a3a7494">
                    <form method="POST" id="myForm" action="{{ route('admin.receiveInterTransferRequest', base64_encode($transfers[0]->delivery_number)) }}">
                        @csrf
                        <table class="table mb-0 table-bordered table-striped" id="datatable">
                            <thead class="bg-200">
                            <tr>
                                <th>#</th>
                                <th>&#10003;</th>
                                <th>Garden Name</th>
                                <th>Grade Name</th>
                                <th>Invoice Number </th>
                                <th>Lot Number</th>
                                <th>Requested</th>
                                <th>Receive Pkgs</th>
                                <th>Request Weight</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($transfers as $transfer)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>
                                        <input type="checkbox" class="select-checkbox" name="deliveries[{{ $transfer->transfer_id }}][deliveryId]" value="{{ $transfer->transfer_id }}">
                                    </td>
                                    <td>{{ $transfer->garden_name }}</td>
                                    <td>{{ $transfer->grade_name }}</td>
                                    <td>{{ $transfer->invoice_number }}</td>
                                    <td>{{ $transfer->lot_number }}</td>
                                    <td>{{ $transfer->requested_palettes }}</td>
                                    <td>
                                        <input id="currentPackages" type="hidden" value="{{ $transfer->requested_palettes }}">
                                        <input type="number" step="0.1" name="deliveries[{{ $transfer->transfer_id }}][palette]" class="form-control" max="{{ $transfer->requested_palettes }}" id="tPackages">
                                    </td>
                                    <td>
                                        <input id="currentWeight" type="hidden" value="{{ $transfer->requested_weight }}">
                                        <input type="number" step="0.01" name="deliveries[{{ $transfer->transfer_id }}][weight]" class="form-control" max="{{ $transfer->requested_weight }}" readonly id="tWeight">
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>

                        <div class="row row-cols-sm-3 g-2">

                            <div class="mb-2">
                                <label class="my-1 fs-xs fw-bold" style="font-size: 85% !important;">TRANSPORTER</label>
                                <select name="transporter" id="colorSelect" class="form-select js-choice">
                                    <option disabled value="">-- select transporter -- </option>
                                    @foreach($transporters as $transporter)
                                        <option @if($transporter->transporter_id == $transfer->transporter_id) selected @endif value="{{ $transporter->transporter_id }}">{{ $transporter->transporter_name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-2">
                                <label class="my-1 fs-xs fw-bold" style="font-size: 85% !important;">VEHICLE REGISTRATION</label><br>
                                <input class="form-control form-control-lg" value="{{ $transfer->registration }}" name="registration" id="editableSelect" type="text" list="optionsList" placeholder="-- plate number --">
                                <datalist id="optionsList">
                                    @foreach($registrations as $registration => $transporter)
                                        <option value="{{ $registration }}">{{ $registration }} </option>
                                    @endforeach
                                </datalist>

                            </div>

                            <div class="mb-2">
                                <label class="my-1 fs-xs fw-bold" style="font-size: 85% !important;">DRIVER'S ID NUMBER</label> <br>
                                <input id="idSelect" type="text" value="{{ $transfer->id_number }}" list="idList" name="idNumber" class="form-control idSelect form-control-lg" placeholder="-- driver's ID Number --">
                                <datalist id="idList">
                                    @foreach($users as $user)
                                        <option value="{{ $user->id_number }}">{{ $user->id_number }}</option>
                                    @endforeach
                                </datalist>
                            </div>

                            <div class="mb-2">
                                <label class="my-1 fs-xs fw-bold" style="font-size: 85% !important;">DRIVER'S NAME</label>
                                <input type="text" name="driverName" value="{{ $transfer->driver_name }}" id="driverName" class="form-control form-control-lg driverName">
                            </div>

                            <div class="mb-4">
                                <label class="my-1 fs-xs fw-bold" style="font-size: 85% !important;">DRIVER'S PHONE NUMBER</label>
                                <input type="text" name="driverPhone" value="{{ $transfer->phone }}" id="driverPhone" class="form-control form-control-lg driverPhone">
                            </div>

                            <div class="mb-2">
                                <label class="my-1 fs-xs fw-bold" style="font-size: 85% !important;">WAREHOUSE BAY</label>
                                <select name="bayId" id="colorSelect" class="form-select js-choice" required>
                                    <option selected disabled value="">-- select bay -- </option>
                                    @foreach($stations as $station)
                                        <option value="{{ $station->bay_id }}">{{ $station->bay_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <input type="hidden" id="allDeliveries" name="allDeliveries">

                        <div class="d-flex justify-content-center">
                            <button type="submit" id="submitButton" class="btn btn-success col-8">RECEIVE TRANSFER REQUEST </button>
                        </div>
                    </form>


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

        $(document).on('change', '#tPackages', function() {
            console.log('hey')
            var totalPackages = parseInt($(this).val(), 10);
            var currentStock = parseInt($(this).closest('tr').find('#currentPackages').val(), 10);
            var currentWeight = parseFloat($(this).closest('tr').find('#currentWeight').val());

            // Check if the entered value is valid
            if (isNaN(totalPackages) || totalPackages < 0 || totalPackages > currentStock) {
                // Handle invalid input (e.g., display error message)
                return;
            }

            // Calculate the new weight based on the number of packages
            var newWeight = (totalPackages * currentWeight) / currentStock;

            // Update the weight input field
            $(this).closest('tr').find('#tWeight').val(newWeight.toFixed(2));
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

    $(document).ready(function () {
        const selectedItems = {}; // Object to track selected items

        // Event binding for changes in the palette input and checkbox
        $('#datatable').on('change input', 'input[name*=palette], .select-checkbox', function () {
            const $row = $(this).closest('tr');
            const stockId = $row.find('.select-checkbox').val(); // Get stock ID from the checkbox
            const isChecked = $row.find('.select-checkbox').prop('checked'); // Check if the checkbox is selected

            // Retrieve palette value using the correct selector
            let paletteValue = parseFloat($row.find('input[name="deliveries[' + stockId + '][palette]"]').val()) || 0;

            // Retrieve current packages and current weight from hidden inputs
            let currentPackages = parseFloat($row.find('#currentPackages').val()) || 0;
            let currentWeight = parseFloat($row.find('#currentWeight').val()) || 0;

            // Calculate updated weight based on the palette value
            let updatedWeight = 0;
            if (paletteValue !== 0 && currentPackages !== 0) {
                updatedWeight = (currentWeight / currentPackages) * paletteValue;
            }

            // Update selectedItems based on checkbox state
            if (isChecked) {
                selectedItems[stockId] = {
                    deliveryId: stockId,
                    palette: paletteValue,
                    weight: updatedWeight.toFixed(2)
                };
            } else {
                delete selectedItems[stockId]; // Remove if unchecked
            }

            console.log('Selected items:', selectedItems); // Debug: Check selected items
        });

        // Form submission handling
        $('#myForm').submit(function (event) {
            // Prevent default submission behavior
            event.preventDefault();

            // Prepare data to submit (only selected items)
            const dataToSubmit = {
                deliveries: Object.values(selectedItems) // Include only selected items
            };

            console.log('Data to submit:', dataToSubmit); // Debug: Check data to submit

            // Update the hidden input with the selected items
            $('#allDeliveries').val(JSON.stringify(dataToSubmit)); // Set hidden input with selected items

            // Proceed with the form submission
            this.submit();
        });

        $('#myForm').on('submit', function(event) {
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

