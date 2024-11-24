@extends('account::layouts.default')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/2.1.5/css/dataTables.dataTables.css">
@section('account::dashboard')
    <div class="card">
        <div class="card-header">
            <div class="row flex-between-center">
                <div class="col-6 col-sm-auto d-flex align-items-center pe-0">
                    <h5 class="fs-9 mb-0 text-nowrap py-0 py-xl-0"> {{ $client->client_account_name }} Ledger Report  {{ Carbon\Carbon::parse($fy->year_starting)->format('Y') == Carbon\Carbon::parse($fy->year_ending)->format('Y') ? Carbon\Carbon::parse($fy->year_starting)->format('Y') : Carbon\Carbon::parse($fy->year_starting)->format('Y').'/'.Carbon\Carbon::parse($fy->year_ending)->format('y') }} FY </h5>
                </div>
                <div class="col-6 col-sm-auto ms-auto text-end ps-0">
                    <div id="table-simple-pagination-replace-element">
                        <a class="btn btn-falcon-default btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#staticBackdrop1"><span class="fas fa-file-download" data-fa-transform="shrink-3 down-2"></span><span class="d-none d-sm-inline-block ms-1">Report</span></a>
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
                                    <form method="POST" action="{{ route('accounts.generateExpenseLedgerStatement') }}">
                                        <div class="row row-cols-sm-2 g-2">
                                            @csrf
                                            <input type="hidden" value="{{ base64_encode($client->client_account_id.':'.$fy->financial_year_id) }}" name="clientId">
                                            <div class="mb-4">
                                                <label class="fw-bold">DATE FROM</label>
                                                <input type="date" class="form-control form-control-lg" name="dateFrom">
                                            </div>

                                            <div class="mb-4">
                                                <label class="fw-bold">DATE TO</label>
                                                <input type="date" class="form-control form-control-lg" name="dateTo">
                                            </div>
                                        </div>
                                        <div class="d-flex justify-content-center mt-2">
                                            <button type="submit" class="btn btn-success">DOWNLOAD REPORT</button>
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
                            <th>DATE INVOICED</th>
                            <th>TRANSACTION TYPE</th>
                            <th>INVOICE NUMBER</th>
                            <th>CLIENT NAME</th>
                            <th>DESCRIPTION</th>
                            <th>TOTAL INVOICE</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php $totalInvoice = 0; $amount = 0;?>
                        @foreach($statements as $statement)
                                <?php
                                $amount = $statement->type == 2 ? $statement->amount_due*-1 : $statement->amount_due;
                                $totalInvoice += $amount;
                                ?>
                            <tr>
                                <td> {{ $loop->iteration }} </td>
                                <td> {{ Carbon\Carbon::createFromTimestamp($statement->date_invoiced)->format('D, d/m/y') }} </td>
                                <td> {{ $statement->type == 1 ? 'INVOICE' : 'CREDIT NOTE' }} </td>
                                <td> {{ $statement->invoice_number }} </td>
                                <td> {{ $statement->client_account_name }} </td>
                                <td> {{ $statement->customer_message }} </td>
                                <td> {{ $currency->currency_symbol }} {{ number_format($amount, 2, '.', ',') }} </td>
                            </tr>
                        @endforeach
                        </tbody>
                        <tr class="text-center">
                            <td colspan="6" class="fw-bold text-center">TOTAL INVOICE</td>
                            <td colspan="1"
                                class="fw-bold"> {{ $currency->currency_symbol }} {{ number_format($totalInvoice, 2, '.', ',') }}</td>
                        </tr>
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
