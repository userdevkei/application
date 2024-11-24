@extends('clerk::layouts.default')
@section('clerk::dashboard')
    <div class="card">
        <div class="card-header">
            <div class="row flex-between-center">
                <div class="col-6 col-sm-auto d-flex align-items-center pe-0">
                    <h5 class="fs-9 mb-0 text-nowrap py-0 py-xl-0">Delivery Orders </h5>
                </div>
                    <div class="col-6 col-sm-auto ms-auto text-end ps-0">
                        <div id="table-simple-pagination-replace-element">
                        @if(auth()->user()->role_id == 5)
                            <a class="btn btn-falcon-default btn-sm" href="{{ route('clerk.addDeliveryOrders') }}"><span class="fas fa-plus" data-fa-transform="shrink-3 down-2"></span><span class="d-none d-sm-inline-block ms-1">New</span></a>
                        @endif
                        @if(auth()->user()->role_id == 2)
                            <a class="btn btn-falcon-danger btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#staticBackdrop1"><span class="fa-solid fa-arrow-up-from-bracket" data-fa-transform="shrink-3 down-2"></span><span class="d-none d-sm-inline-block ms-1">Import DOS</span></a>
                        @endif
                        </div>
                    </div>
            </div>
             <div class="modal fade" id="staticBackdrop1" data-bs-keyboard="false" data-bs-backdrop="static" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                    <div class="modal-dialog modal-xl mt-6" role="document">
                        <div class="modal-content border-0">
                            <div class="position-absolute top-0 end-0 mt-3 me-3 z-1">
                                <button class="btn-close btn btn-sm btn-circle d-flex flex-center transition-base" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body p-0">
                                <div class="rounded-top-3 bg-body-tertiary py-3 ps-4 pe-6">
                                    <h5 class="mb-1" id="staticBackdropLabel">FILTER BY DATE</h5>
                                </div>
                                <div class="p-4">
                                    <div class="row">
                                        <form method="POST" action="{{ route('clerk.ImportDOS') }}" enctype="multipart/form-data">
                                            <div class="row row-cols-sm-1 g-2">
                                                @csrf
                                                <div class="mb-1">
                                                    <label class="fw-bold">CLIENT NAME</label>
                                                    <select class="form-select js-choice" required name="clientId" size="1" data-options='{"removeItemButton":true,"placeholder":true}'>
                                                        <option selected disabled value="">Select Client...</option>
                                                        @foreach($clients as $client)
                                                            <option value="{{ $client->client_id }}">{{ $client->client_name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>

                                                <div class="mb-4">
                                                    <label class="fw-bold">SELECT EXCEL</label>
                                                    <input type="file" class="form-control" name="uploadFile">
                                                </div>
                                            </div>
                                            <div class="d-flex justify-content-center mt-2">
                                                <button type="submit" class="btn btn-success">UPLOAD DOS</button>
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
                    <table class="table mb-0 table-bordered table-striped table-responsive-sm fs-sm" id="datatable">
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
                            <th>TCI #</th>
                            <th></th>
                        </tr>
                        </thead>
                    <tbody>
                    @foreach($orders as $order)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td nowrap="">{{ $order->client_name }}</td>
                            <td>{{ $order->invoice_number }}</td>
                            <td>{{ $order->garden_name }}</td>
                            <td nowrap="">{{ $order->grade_name }}</td>
                            <td>{{ $order->lot_number }}</td>
                            <td>{{ $order->warehouse_name }}</td>
                            <td>{{ $order->sub_warehouse_name }}</td>
                            <td>{{ $order->loading_number }}</td>
                            <td>
                                <a class="link-info d-block fs-sm" href="{{ route('clerk.traceTea', $order->delivery_id) }}" data-bs-toggle="tooltip" data-bs-placement="left" title="Trace Tea"><span class="fa fa-info"></span> </a>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            </div>
        </div>

    </div>
<script>
    $(document).ready(function() {
        $('#datatable').DataTable( {
            order: [ 0, 'asc' ],
            pageLength: 50
        } );
    } );
</script>

@endsection
