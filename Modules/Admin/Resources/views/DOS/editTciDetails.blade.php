@extends('admin::layouts.default')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/2.1.5/css/dataTables.dataTables.css">
@section('admin::dashboard')
    <div class="card">
        <div class="card-header">
            <div class="row flex-between-center">
                <div class="col-6 col-sm-auto d-flex align-items-center pe-0">
                    <h5 class="fs-9 mb-0 text-nowrap py-0 py-xl-0">UPDATE {{ $tci->client_name == null }} - {{ $tci->loading_number }} DETAILS </h5>
                </div>
                <div class="col-6 col-sm-auto ms-auto text-end ps-0">
                    <div id="table-simple-pagination-replace-element">
                        <a class="btn btn-falcon-primary btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#staticBackdrop"> <span class="fa fa-plus"></span> Add </a>
                    </div>
                </div>
            </div>

        </div>
            <div class="modal fade" id="staticBackdrop" data-bs-keyboard="false" data-bs-backdrop="static" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                <div class="modal-dialog modal-xl modal-dialog-centered mt-6" role="document">
                    <div class="modal-content border-0">
                        <div class="position-absolute top-0 end-0 mt-3 me-3 z-1">
                            <button class="btn-close btn btn-sm btn-circle d-flex flex-center transition-base" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body p-0">
                            <div class="rounded-top-3 bg-body-tertiary py-3 ps-4 pe-6">
                                <h5 class="mb-1" id="staticBackdropLabel">Add Teas to TCI</h5>
                            </div>
                            <div class="p-4 fs-sm">
                                <div class="row">
                                    <form id="doForm" method="POST" action="{{ route('admin.updateLLI', base64_encode($tci->loading_number)) }}">
                                        @csrf
                                        <table class="table mb-0 table-bordered table-striped" id="datatable1">
                                            <thead class="bg-200">
                                            <tr>
                                                <th>Select</th>
                                                <th>Garden</th>
                                                <th>Grade</th>
                                                <th>Order Number</th>
                                                <th>Invoice Number</th>
                                                <th>Sale Number</th>
                                                <th>Packages</th>
                                                <th>Weight</th>
                                                <th>Packing</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            @foreach($teas as $tea)
                                                <tr>
                                                    <td><input type="checkbox" value="{{ $tea->delivery_id }}" name="delNumbers[]"></td>
                                                    <td>{{ $tea->garden_name }}</td>
                                                    <td>{{ $tea->grade_name }}</td>
                                                    <td>{{ $tea->order_number }}</td>
                                                    <td>{{ $tea->invoice_number }}</td>
                                                    <td>{{ $tea->sale_number }}</td>
                                                    <td>{{ $tea->packet }}</td>
                                                    <td>{{ $tea->weight }}</td>
                                                    <td>{{ $tea->package == 1 ? 'PB' : 'PS' }}</td>
                                                </tr>
                                            @endforeach

                                            </tbody>
                                        </table>
                                        @if($teas->count() > 0)
                                            <div class="d-flex justify-content-center mt-4">
                                                <button id="submitButton" type="submit" class="btn btn-success col-8"> UPDATE TEAS TO TCI </button>
                                            </div>
                                        @endif
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <div class="card-body overflow-hidden p-lg-3">
            <div class="row align-items-center">
                <div class="tab-pane preview-tab-pane active fs-11" role="tabpanel" aria-labelledby="tab-dom-c3976e0e-38db-410e-861a-36d04a3a7494" id="dom-c3976e0e-38db-410e-861a-36d04a3a7494">
                    <table class="table mb-0 table-bordered table-striped" id="datatable">
                        <thead class="bg-200">
                        <tr>
                            <th>#</th>
                            <th>Inv No</th>
                            <th>Garden Name</th>
                            <th>Grade</th>
                            <th>Package</th>
                            <th>Lot No</th>
                            <th>Sale #</th>
                            <th>Prompt Date</th>
                            <th>Sale Date</th>
                            <th>Pkgs</th>
                            <th>Weight</th>
                            <th>Producer Whs</th>
                            <th>Sub-Warehouse</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($orders as $order)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $order->invoice_number }}</td>
                                <td>{{ $order->garden_name }}</td>
                                <td>{{ $order->grade_name }}</td>
                                <td>{{ $order->package == 1 ? 'PAPER SACK' : 'PAPER BAG' }}</td>
                                <td>{{ $order->lot_number }}</td>
                                <td>{{ $order->sale_number }}</td>
                                <td>{{ $order->prompt_date }}</td>
                                <td>{{ $order->sale_date }}</td>
                                <td>{{ $order->packet }}</td>
                                <td>{{ $order->weight }}</td>
                                <td>{{ $order->warehouse_name }}</td>
                                <td>{{ $order->sub_warehouse_name }}</td>
                                <td>{!! $order->status == 1 ? '<span class="text-info">Under Collection </span>' : '<span class="text-success">Tea Received</span>' !!}</td>
                                <td>
                                    @if( $order->status < 2)
                                        <a class="link-danger d-block fs-sm" onclick="return confirm('Are you sure you want to remove selected tea from the TCI?')" href="{{ route('admin.removeTeaFromTCI', $order->loading_id) }}" data-bs-toggle="tooltip" data-bs-placement="left" title="Remove this tea from the TCI"><span class="fa fa-trash-alt"></span> </a>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.js"></script>
    <script src="https://cdn.datatables.net/2.1.5/js/dataTables.js"></script>
    <script>
        $(document).ready(function() {
            $('#datatable').DataTable( {
                order: [ 0, 'asc' ],
                pageLength: 50
            } );

            $('#datatable1').DataTable( {
                order: [ 0, 'asc' ],
                pageLength: 50
            } );

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

        } );
    </script>

@endsection
