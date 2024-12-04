@extends('admin::layouts.default')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/2.1.5/css/dataTables.dataTables.css">
@section('admin::dashboard')
    <div class="card">
        <div class="card-header">
            <div class="row flex-between-center">
                <div class="col-6 col-sm-auto d-flex align-items-center pe-0">
                    <h5 class="fs-9 mb-0 text-nowrap py-0 py-xl-0">Stocks Aging Report </h5>
                </div>
                <div class="col-6 col-sm-auto ms-auto text-end ps-0">
                    <div id="table-simple-pagination-replace-element">
                        <a class="btn btn-falcon-default btn-sm" data-bs-toggle="modal" data-bs-target="#staticBackdrop"><span class="fas fa-plus" data-fa-transform="shrink-3 down-2"></span><span class="d-none d-sm-inline-block ms-1">New Report</span></a>
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
                                    <h5 class="mb-1" id="staticBackdropLabel">CREATE A CUSTOM REPORT</h5>
                                </div>
                                <div class="p-4">

                                    <form method="POST" action="{{ route('admin.downloadStockAgingReport') }}">
                                        @csrf
                                        <div class="row row-cols-sm-1 g-2">
                                            <div class="mb-4">
                                                <label class="fw-bold fs-6" style="font-size: small !important;">CLIENT NAME</label>
                                                <select name="clientId" id="clientId" class="form-select js-choice" style="height: 61% !important;">
                                                    <option selected disabled value="">-- select client account --</option>
                                                    @foreach($clients as $client)
                                                        <option value="{{ $client->client_id }}">{{ $client->client_name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div class="mb-4">
                                                <label class="fw-bold fs-6" style="font-size: small !important;">REPORT FORMAT</label>
                                                <select name="reportType" class="form-select js-choice">
                                                    <option value="" selected>-- select report format --</option>
                                                    <option value="1">PDF DOCUMENT</option>
                                                    <option value="2">EXCEL DOCUMENT</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="d-flex justify-content-center mt-1">
                                            <button type="submit" class="btn btn-success col-8">DOWNLOAD REPORT </button>
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
                            <th>Client Name</th>
                            <th> <30 Days </th>
                            <th> 31-90 Days</th>
                            <th> 91-180 Days</th>
                            <th> 181-365 Days</th>
                            <th> 365+ Days</th>
                            <th> Total Weight (Packages) </th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($clients as $request)
                            <tr>
                                <td> {{ $loop->iteration }} </td>
                                <td> {{ $request->client_name }}</td>
                                <td> {{ number_format($request->weight_0_30, 2) }}</td>
                                <td> {{ number_format($request->weight_31_90, 2) }}</td>
                                <td> {{ number_format($request->weight_91_180, 2) }}</td>
                                <td> {{ number_format($request->weight_181_365, 2) }}</td>
                                <td> {{ number_format($request->weight_more_than_1yr, 2) }}</td>
                                <td> {{ number_format($request->total_weight, 2) }} ({{ number_format($request->total_stock, 0) }}) </td>
                                <td>
                                    <a class="link text-secondary m-2" data-bs-toggle="tooltip" data-bs-placement="left" title="View Clients Stock Analysis" href="{{ route('admin.clientStock',$request->client_id) }}"> <span class="fas fa-folder-open"> </span> </a>
                                </td>
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
<script src="https://cdn.datatables.net/2.1.5/js/dataTables.js"></script>
<script>
    $(document).ready(function() {
        $('#datatable').DataTable({
            order: [0, 'asc'],
            pageLength: 50
        });
    });
</script>
