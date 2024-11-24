@extends('account::layouts.default')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/2.1.5/css/dataTables.dataTables.css">
@section('account::dashboard')
    <div class="card">
        <div class="card-header">
            <div class="row flex-between-center">
                <div class="col-6 col-sm-auto d-flex align-items-center pe-0">
                    <h5 class="fs-9 mb-0 text-nowrap py-0 py-xl-0">{{ $invoices[0]->client_name }} Aging Invoices Analysis </h5>
                </div>
                <div class="col-6 col-sm-auto ms-auto text-end ps-0">
                    <div id="table-simple-pagination-replace-element">
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
                            <th>INVOICE DATE</th>
                            <th>INVOICE NUMBER</th>
                            <th>INVOICE AMOUNT</th>
                            <th>AMOUNT SETTLED</th>
                            <th>OUTSTANDING BAL</th>
                            <th>AGING DATE</th>
                        </tr>
                        </thead>
                        <tbody>
                        @php $totalInvoice = 0; $totalPayment = 0; $totalOutstanding = 0; @endphp
                            @foreach($invoices as $invoice)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ \Carbon\Carbon::parse($invoice->invoice_date)->format('Y-m-d') }}</td>
                                    <td>{{ $invoice->invoice_number }}</td>
                                    <td class="text-end">{{ number_format($invoice->amount_due, 2) }}</td>
                                    <td class="text-end">{{ number_format($invoice->total_payments, 2) }}</td>
                                    <td class="text-end">{{ number_format($invoice->outstanding_balance, 2) }}</td>
                                    <td>{{ $invoice->aging_category }}</td>
                                </tr>
                                @php $totalInvoice += $invoice->amount_due; $totalPayment += $invoice->total_payments; $totalOutstanding += $invoice->outstanding_balance; @endphp
                            @endforeach
                        </tbody>
                        <tr>
                            <td colspan="3" class="text-center fw-bold">TOTALS </td>
                            <td class="text-end fw-bold fst-italic">{{ $invoice->currency_symbol }} {{ number_format($totalInvoice, 2) }}</td>
                            <td class="text-end fw-bold fst-italic">{{ $invoice->currency_symbol }} {{ number_format($totalPayment, 2) }}</td>
                            <td class="text-end fw-bold fst-italic">{{ $invoice->currency_symbol }} {{ number_format($totalOutstanding, 2) }}</td>
                            <td></td>
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
