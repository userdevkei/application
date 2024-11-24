@extends('account::layouts.default')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/css/bootstrap.min.css">
{{--<link rel="stylesheet" href="https://cdn.datatables.net/2.1.5/css/dataTables.dataTables.css">--}}
@section('account::dashboard')
    <div class="card">
        <div class="card-header">
            <div class="row flex-between-center">
                <div class="col-6 col-sm-auto d-flex align-items-center pe-0">
                    <h5 class="fs-9 mb-0 text-nowrap py-0 py-xl-0">Transport Details </h5>
                </div>
                <div class="col-6 col-sm-auto ms-auto text-end ps-0">
                    <div id="table-simple-pagination-replace-element">
                        <button class="btn btn-falcon-danger btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#transporter"><span class="fas fa-file-download" data-fa-transform="shrink-3 down-2"></span><span class="d-none d-sm-inline-block ms-1">Transport Report</span></button>
                    </div>
                </div>
                <div class="modal fade" id="transporter" data-bs-keyboard="false" data-bs-backdrop="static" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                    <div class="modal-dialog modal-xl mt-6" role="document">
                        <div class="modal-content border-0">
                            <div class="position-absolute top-0 end-0 mt-3 me-3 z-1">
                                <button class="btn-close btn btn-sm btn-circle d-flex flex-center transition-base" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body p-0">
                                <div class="rounded-top-3 bg-body-tertiary py-3 ps-4 pe-6">
                                    <h5 class="mb-1" id="staticBackdropLabel">GENERATE TRANSPORTER REPORT</h5>
                                </div>
                                <div class="p-4">
                                    <form method="POST" action="{{ route('accounts.exportTransportReport') }}">
                                        @csrf
                                        <div class="row row-cols-sm-2 g-1">
                                            <div class="col-6 mb-2">
                                                <label> CLIENT NAME</label>
                                                <select class="form-select js-choice" name="transporter" data-options='{"removeItemButton":true,"placeholder":true}'>
                                                    <option value="" selected>-- all transporters --</option>
                                                    @foreach($invoices->groupBy('transporter_name') as $transporterName => $transporter)
                                                        <option value="{{ $transporter[0]->transporter_id }}">{{ $transporterName }}</option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div class="col-6 mb-2">
                                                <label> DELIVERY TYPE</label>
                                                <select class="form-select js-choice" name="report" data-options='{"removeItemButton":true,"placeholder":true}'>
                                                    <option value="" selected>-- all deliveries --</option>
                                                    <option value="1">COLLECTIONS</option>
                                                    <option value="2">TRANSFERS</option>
                                                </select>
                                            </div>
                                            <div class="mb-2 date-input-container">
                                                <label class="my-1 fs-xs fw-bold" style="font-size: 85% !important;">DATE FROM</label>
                                                <input type="date" id="monthAgo" value="" name="from" class="form-control date-input" style="height: 62% !important;">
                                            </div>

                                            <div class="mb-2 date-input-container">
                                                <label class="my-1 fs-xs fw-bold" style="font-size: 85% !important;">DATE TO</label>
                                                <input type="date"  id="todayDate" name="to" class="form-control date-input" style="height: 62% !important;">
                                            </div>
                                        </div>

                                        <div class="mt-4 d-flex justify-content-center">
                                            <button type="submit" class="btn btn-success col-7">DOWNLOAD REPORT</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
        <div class="card-body overflow-hidden p-lg-3">
            <div class="row align-items-center">
                <div class="tab-pane preview-tab-pane active" role="tabpanel" aria-labelledby="tab-dom-c3976e0e-38db-410e-861a-36d04a3a7494" id="dom-c3976e0e-38db-410e-861a-36d04a3a7494">
                    <table class="table mb-0 table-bordered table-striped" id="datatable">
                        <thead class="bg-200">
                        <tr>
                            <th>#</th>
                            <th>DEL. TYPE</th>
                            <th>TRANSPORTER</th>
                            <th>DRIVER NAME</th>
                            <th>VEHICLE REG.</th>
                            <th>CLIENT NAME</th>
                            <th>INV. NUMBER</th>
                            <th>PCKGS</th>
                            <th>WEIGHT</th>
                            <th>FROM </th>
                            <th>DESTINATION</th>
                            <th>TCI/DEL NUMBER</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($invoices as $invoice)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $invoice->delivery_type }}</td>
                                <td>{{ $invoice->transporter_name }}</td>
                                <td>{{ $invoice->driver_name }}</td>
                                <td>{{ $invoice->registration }}</td>
                                <td>{{ $invoice->client_name }}</td>
                                <td>{{ $invoice->invoice_number }}</td>
                                <td>{{ $invoice->total_pallets }}</td>
                                <td>{{ number_format($invoice->total_weight, 2) }}</td>
                                <td>{{ $invoice->warehouse_name }}</td>
                                <td>{{ $invoice->station_name }}</td>
                                <td>{{ $invoice->loading_number }}</td>
                            </tr>
                          @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
<script src="https://code.jquery.com/jquery-3.7.1.js"></script>
{{--<script src="https://cdn.datatables.net/2.1.5/js/dataTables.js"></script>--}}
<script>
    $(document).ready(function() {
        $('#datatable').DataTable({
            order: [0, 'asc'],
            pageLength: 50
        });
    });
</script>
