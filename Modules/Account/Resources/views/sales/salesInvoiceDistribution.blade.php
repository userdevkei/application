@extends('account::layouts.default')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/css/bootstrap.min.css">
{{--<link rel="stylesheet" href="https://cdn.datatables.net/2.1.5/css/dataTables.dataTables.css">--}}
@section('account::dashboard')
    <div class="card">
        <div class="card-header">
            <div class="row flex-between-center">
                <div class="col-6 col-sm-auto d-flex align-items-center pe-0">
                    <h5 class="fs-9 mb-0 text-nowrap py-0 py-xl-0">
                        {{ $invoices[0]->payment_number }} - {{ $invoices[0]->client_account_name }}
                    </h5>
                </div>
                <div class="col-6 col-sm-auto ms-auto text-end ps-0">
                    <div id="table-simple-pagination-replace-element">
                        @if($invoices[0]->invoice_id !== null)
                            <a class="btn btn-falcon-default btn-sm" href="{{ route('accounts.downloadInvoice', $invoices[0]->invoice_id) }}" target="_blank"><span class="fas fa-cloud-download-alt" ></span><span class="d-none d-sm-inline-block ms-1">New Invoice</span></a>
                        @endif
                    </div>
                </div>

            </div>
        </div>
        <div class="card-body overflow-hidden p-lg-3">
            <div class="row align-items-center">
                <div class="tab-pane preview-tab-pane active" role="tabpanel" aria-labelledby="tab-dom-c3976e0e-38db-410e-861a-36d04a3a7494" id="dom-c3976e0e-38db-410e-861a-36d04a3a7494">
                    <table class="table mb-0 table-sm table-bordered table-striped" id="datatable">
                        <thead class="bg-200">
                        <tr>
                            <th>#</th>
                            <th>FINANCIAL YEAR</th>
                            <th>INVOICE NUMBER</th>
                            <th>BILLING IN</th>
                            <th>AMOUNT DUE</th>
                            <th>AMOUNT SETTLED</th>
                            <th>STATUS</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php $totalAmountDue = 0; $totalAmountSettled = 0; ?>
                        @foreach($invoices as $invoice)
                            <tr>
                                <td> {{ $loop->iteration }} </td>
                                <td> {{ \Carbon\Carbon::parse($invoice->year_starting)->format('Y') ==  \Carbon\Carbon::parse($invoice->year_ending)->format('Y') ? \Carbon\Carbon::parse($invoice->year_starting)->format('Y') : \Carbon\Carbon::parse($invoice->year_starting)->format('Y').'/'.\Carbon\Carbon::parse($invoice->year_ending)->format('y') }} </td>
                                <td> {{ $invoice->invoice_number }}</td>
                                <td> {{ $invoice->currency_symbol }}</td>
                                <td> {{ number_format($invoice->amount_due, 2) }}</td>
                                <td> {{ number_format($invoice->amount_settled, 2) }}</td>
                                <td> {!! $invoice->amount_due == $invoice->amount_settled ? '<span class="badge bg-success"> Fully Settled </span>' : '<span class="badge bg-info"> Partially Settled </span>' !!} </td>
                            </tr>
                                <?php
                                $totalAmountDue += $invoice->amount_due;
                                $totalAmountSettled += $invoice->amount_settled;
                                ?>
                        @endforeach
                        </tbody>
                        <tr>
                            <td colspan="4" class="fw-bold">SUBTOTAL </td>
                            <td class="fw-bold">{{  $invoices[0]->currency_symbol }} {{ number_format($totalAmountDue, 2) }}</td>
                            <td class="fw-bold">{{  $invoices[0]->currency_symbol }} {{ number_format($totalAmountSettled, 2) }}</td>
                            <td> {!! $totalAmountSettled == $totalAmountDue ? '<span class="badge bg-success"> Fully Settled </span>' : '<span class="badge bg-info"> Partially Settled </span>' !!} </td>
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
            pageLength: 100
        });
    });
</script>
