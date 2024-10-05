@extends('admin::layouts.default')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/2.1.5/css/dataTables.dataTables.css">
@section('admin::dashboard')
    <div class="card">
        <div class="card-header">
            <div class="row flex-between-center">
                <div class="col-6 col-sm-auto d-flex align-items-center pe-0">
                    <h5 class="fs-9 mb-0 text-nowrap py-0 py-xl-0">Direct Deliveriess </h5>
                </div>
                    <div class="col-6 col-sm-auto ms-auto text-end ps-0">
                        <div id="table-simple-pagination-replace-element">
                            <a class="btn btn-falcon-danger btn-sm" data-bs-toggle="modal" data-bs-target="#staticBackdrop"><span class="fas fa-upload" data-fa-transform="shrink-3 down-2"></span><span class="d-none d-sm-inline-block ms-1">Import Teas</span></a>
                            <a class="btn btn-falcon-default btn-sm" href="{{ route('admin.addDirectDelivery') }}"><span class="fas fa-plus" data-fa-transform="shrink-3 down-2"></span><span class="d-none d-sm-inline-block ms-1">New</span></a>
                        </div>
                    </div>
            </div>
            <div class="modal fade" id="staticBackdrop" data-bs-keyboard="false" data-bs-backdrop="static" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg mt-6" role="document">
                    <div class="modal-content border-0">
                        <div class="position-absolute top-0 end-0 mt-3 me-3 z-1">
                            <button class="btn-close btn btn-sm btn-circle d-flex flex-center transition-base" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body p-0">
                            <div class="rounded-top-3 bg-body-tertiary py-3 ps-4 pe-6">
                                <h5 class="mb-1" id="staticBackdropLabel">Import Direct Delivery From Excel</h5>
                            </div>
                            <div class="p-4">
                                <div class="row">
                                    <form id="doForm" method="POST" action="{{ route('admin.importStock') }}" enctype="multipart/form-data">
                                        @csrf
                                        <div class="row row-cols-sm-1 g-2">
                                            <div class="mb-2">
                                                <label class="my-1 fs-xs fw-bold" style="font-size: 85% !important;">DELIVERY NUMBER</label>
                                                <input type="text" name="delivery_number" value="{{ old('delivery_number') }}" class="form-control" placeholder="--" style="height: 64% !important;" required>
                                            </div>

                                            <div class="mb-2">
                                                <label class="my-1 fs-xs fw-bold" style="font-size: 85% !important;">EXCEL FILE</label>
                                                <input type="file" name="uploadFile" class="form-control" required placeholder="--" style="height: 64% !important;">
                                            </div>
                                        </div>
                                        <div class="d-flex justify-content-center mt-4">
                                            <button type="submit" id="submitButton" class="btn btn-success col-8">UPLOAD STOCK</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @if (session('importErrors'))
                <div class="alert alert-warning mt-2">
                    <ol>
                        @foreach (session('importErrors') as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ol>
                </div>
            @endif
        </div>
        <div class="card-body overflow-hidden p-lg-3">
            <div class="row align-items-center">
                <div class="tab-pane preview-tab-pane active" role="tabpanel" aria-labelledby="tab-dom-c3976e0e-38db-410e-861a-36d04a3a7494" id="dom-c3976e0e-38db-410e-861a-36d04a3a7494">
                    <table class="table mb-0 table-bordered table-striped" id="datatable">
                        <thead class="bg-200">
                        <tr>
                            <th>#</th>
                            <th>Delivery #</th>
                            <th>Client Name</th>
                            <th>Tea Type</th>
                            <th>Packaging</th>
                            <th>Packages </th>
                            <th>Net Weight</th>
                            <th>Producer Whs</th>
                            <th>Destination</th>
                            <th>Status</th>
                            <th nowrap=""></th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($orders as $order)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $order->order_number }}</td>
                                <td>{{ $order->client_name }}</td>
                                <td>{{ $order->tea_id == 1 ? 'AUCTION TEA' : ($order->tea_id  == 2 ? 'PRIVATE TEA' : ($order->tea_id  == 3 ? 'FACTORY TEA' : 'BLEND REMNANTS')) }}</td>
                                <td>{{ $order->packet == 1 ? 'PB' : 'PS' }}</td>
                                <td>{{ $order->total_packages }}</td>
                                <td>{{ $order->total_net_weight }}</td>
                                <td>{{ $order->warehouse_name }}</td>
                                <td>{{ $order->station_name }}</td>
                                <td>{!! $order->order_status == null ? '<span class="text-danger">Pending <span>' : '<span class="text-success">Stocked</span>' !!}</td>
                                <td nowrap="">
                                    <div class="dropdown font-sans-serif position-static" >
                                        @if($order->order_status > 0)
                                            <a onclick="return false;" class="link-success fs-sm mx-2" data-bs-toggle="tooltip" data-bs-placement="left" title="Teas received and stock updated">
                                                <span class="fas fa-check"></span>
                                            </a>
                                        @else
                                            <a onclick="return confirm('Are you sure you want to receive all teas under this delivery?')" class="link-danger d-inline-block fs-sm mx-2" data-bs-toggle="tooltip" data-bs-placement="left" title="Receive teas under this delivery" href="{{ route('admin.receiveDirectDeliveries', base64_encode($order->order_number)) }}"> <span class="fas fa-compress-alt"></span> </a>
                                        @endif
                                            <a class="link text-600 btn-sm dropdown-toggle btn-reveal" type="button" data-bs-toggle="dropdown" data-boundary="window" aria-haspopup="true" aria-expanded="false">
                                                    <span class="fas fa-ellipsis-h fs-10"></span>
                                            </a>
                                            <div class="dropdown-menu dropdown-menu-end border py-0">
                                                <div class="py-2">
                                                    <a class="dropdown-item text-info"  href="{{ route('admin.viewDirectDeliveryOrder', base64_encode($order->order_number)) }}">View Direct Delivery</a>
                                                    <a class="dropdown-item text-dark" href="{{ route('admin.downloadDirectDeliveries', base64_encode($order->order_number . ':' . '1')) }}">Download Goods Tally </a>
                                                    <a class="dropdown-item text-secondary" href="{{ route('admin.downloadDirectDeliveries', base64_encode($order->order_number . ':' . '2')) }}">Download Goods Tally (Excel) </a>
                                                </div>
                                            </div>
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

    <script src="https://code.jquery.com/jquery-3.7.1.js"></script>
    <script src="https://cdn.datatables.net/2.1.5/js/dataTables.js"></script>
    <script>
        $(document).ready(function() {
            $('#datatable').DataTable( {
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
