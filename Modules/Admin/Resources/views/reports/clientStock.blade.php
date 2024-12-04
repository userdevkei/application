@extends('admin::layouts.default')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/2.1.5/css/dataTables.dataTables.css">
@section('admin::dashboard')
    <div class="card">
        <div class="card-header">
            <div class="row flex-between-center">
                <div class="col-6 col-sm-auto d-flex align-items-center pe-0">
                    <h5 class="fs-9 mb-0 text-nowrap py-0 py-xl-0"> {{ $stocks[0]->client_name }} Stocks Aging Analysis</h5>
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

                                    <form method="POST" action="{{ route('admin.downloadClientStockAgingReport', $stocks[0]->client_id) }}">
                                        @csrf
                                        <div class="row row-cols-sm-1 g-2">
                                            <div class="mb-4">
                                                <label class="fw-bold fs-6" style="font-size: small !important;">REPORT PERIOD</label>
                                                <select name="period" class="form-select  js-choice">
                                                    <option value="" selected>-- select report period --</option>
                                                    <option value="1"> <30 Days </option>
                                                    <option value="2">31-90 Days</option>
                                                    <option value="3">91-180 Days</option>
                                                    <option value="4">181-365 Days</option>
                                                    <option value="5">365+ Days </option>
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
                                            <button type="submit" id="submitBtn" class="btn btn-success col-8">DOWNLOAD REPORT</button>
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
                            <th>Invoice Number</th>
                            <th>Delivery Type</th>
                            <th>Order Number</th>
                            <th>Garden Name </th>
                            <th>Grade Name</th>
                            <th>Packages</th>
                            <th>Net Weight</th>
                            <th>Received On</th>
                            <th>Aging Period</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($stocks as $stock)
                            <tr>
                                <td> {{ $loop->iteration }} </td>
                                <td> {{ $stock->invoice_number }} </td>
                                <td> {{ $stock->delivery }} </td>
                                <td> {{ $stock->order_number }} </td>
                                <td> {{ $stock->garden_name }} </td>
                                <td> {{ $stock->grade_name }} </td>
                                <td> {{ $stock->current_stock }} </td>
                                <td> {{ $stock->current_weight }} </td>
                                <td> {{ $stock->min_date_received }} </td>
                                <td> {{ $stock->aging_period }} </td>
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
