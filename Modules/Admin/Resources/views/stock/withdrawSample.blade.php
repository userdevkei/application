@extends('admin::layouts.default')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/2.1.5/css/dataTables.dataTables.css">
@section('admin::dashboard')
    <div class="card">
        <div class="card-header">
            <div class="row flex-between-center">
                <div class="col-6 col-sm-auto d-flex align-items-center pe-0">
                    <h5 class="fs-9 mb-0 text-nowrap py-0 py-xl-0">Tea Global Search </h5>
                </div>
            </div>
        </div>
        <div class="card-body overflow-hidden p-lg-3">
{{--            @foreach($teas as $data)--}}
                    <?php //$stocks = $data->toArray() ?><!---->
                <div class="table-responsive">
                    <table class="table table-striped table-bordered table-sm fs-sm mb-4">
                        <thead>
                        <th colspan="6">TEA DETAILS</th>
                        </thead>
                        <thead>
                        <th colspan="2">Delivery Order</th>
                        <th>DO Created By</th>
                        <th>{{ \App\Models\UserInfo::where('user_id', $data->created_by)->first()->surname.' '.\App\Models\UserInfo::where('user_id', $data->created_by)->first()->first_name }}</th>
                        <td>DO status</td>
                        <td>{{ $data->status == null || $data->status == 1 ? 'Under Collection' : 'Collected' }}</td>
                        </thead>
                        <tbody>
                        <tr>
                            <td>Client Name</td>
                            <td>{{ $data->client_name  }}</td>
                            <td>Delivery Type</td>
                            <td>{{ $data->delivery_type == 1 ? 'DO Entry' : 'Direct Delivery'  }}</td>
                            <td>Tea Type</td>
                            <td>{{ $data->tea_id == 1 ? 'Auction Tea' : ($data->tea_id == 2 ? 'Blend Balance' : ($data->tea_id == 3 ? 'Factory Tea' : 'Private Tea')) }}</td>
                        </tr>

                        <tr>
                            <td>Order Number</td>
                            <td>{{ $data->order_number }}</td>
                            <td>Garden Name</td>
                            <td>{{ $data->garden_name }}</td>
                            <td>Garden Name</td>
                            <td>{{ $data->grade_name }}</td>
                        </tr>

                        <tr>
                            <td>Number of packages</td>
                            <td>{{ $data->packet }}</td>
                            <td>Net Weight</td>
                            <td>{{ $data->weight }}</td>
                            <td>Package</td>
                            <td>{{ $data->package == 1 ? 'Poly Bag' : 'Paper Sack' }}</td>
                        </tr>

                        <tr>
                            <td>Producer Warehouse</td>
                            <td>{{ $data->warehouse_name }}</td>
                            <td>Sub-warehouse</td>
                            <td>{{ $data->sub_warehouse_name }}</td>
                            <td>Sub-warehouse Locality</td>
                            <td>{{ $data->locality == 1 ? 'ISLAND' : ( $data->locality ==  2 ? 'CHANGAMWE' : ($data->locality == 3 ? 'JOMVU' : ($data->locality == 4 ? 'BONJE' : ($data->locality == 5 ? 'MIRITINI' : '')))) }} </td>
                        </tr>
                        <tr>
                            <td>Broker</td>
                            <td>{{ $data->broker_name }}</td>
                            <td>Sale Number</td>
                            <td>{{ $data->sale_number }}</td>
                            <td>Invoice Number</td>
                            <td>{{ $data->invoice_number }}</td>
                        </tr>

                        <tr>
                            <td>Lot Number </td>
                            <td>{{ $data->lot_number }}</td>
                            <td>Sale Date</td>
                            <td>{{ $data->sale_date }}</td>
                            <td>Prompt Date</td>
                            <td>{{ $data->prompt_date }}</td>
                        </tr>

                        </tbody>
                    </table>
                    <hr class="mb-4 mt-4">
                    <table class="table table-sm table-striped table-bordered">
                        <thead>
                        <th colspan="3">TEA BALANCE</th>
                        </thead>
                        <tbody>
                            <tr>
                                <th>TOTAL PACKAGES</th>
                                <th>TOTAL WEIGHT</th>
                                <th>CURRENT LOCATION</th>
                            </tr>
                            <tr>
                                <td>{{ $data->current_stock }}</td>
                                <td>{{ $data->current_weight }}</td>
                                <td>{{ $data->stocked_at }}</td>
                            </tr>
                        </tbody>
                    </table>
                    <hr class="mb-4 mt-4 m-2">
                    <form id="sampleForm" method="post" action="{{ route('admin.storeSampleRequest', $data->stock_id) }}">
                        @csrf
                        <div class="col-3 mb-2">
                            <label>Sample Weight Requested</label>
                            <input type="number" step="0.01" class="form-control form-control-lg" name="sample_weight" placeholder="--" id="sampleWeight" required>
                        </div>
                        <div class="col-6 mb-4">
                            <label>Balances</label>
                            <label>Packages : {{ $data->current_stock }}</label>
                            <label id="newWeight">Weight : {{ $data->current_weight }}</label>
                        </div>

                        <button type="submit" id="submitButton" class="btn btn-danger" onclick="return confirm('Are you sure you want to deduct sample from selected tea?')">Deduct Sample</button>
                    </form>
                </div>
        </div>
    </div>
@endsection
<script src="https://code.jquery.com/jquery-3.7.1.js"></script>
<script src="https://cdn.datatables.net/2.1.5/js/dataTables.js"></script>
<script>
    $(document).ready(function () {

        $('#sampleWeight').change(function () {
            var sample = $(this).val();
            var weight = @json($data->weight);
            var balance = 0;
            balance = weight - sample;

            $('#newWeight').text('Weight : ' + balance)
        });

        $('#sampleForm').on('submit', function(event) {
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
