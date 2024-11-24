@extends('account::layouts.default')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/2.1.5/css/dataTables.dataTables.css">
@section('account::dashboard')
    <div class="card">
        <div class="card-header">
            <div class="row flex-between-center">
                <div class="col-6 col-sm-auto d-flex align-items-center pe-0">
                    <h5 class="fs-9 mb-0 text-nowrap py-0 py-xl-0">Aging Analysis {{ base64_decode($id) == 1 ? '(SALES)' : '(PURCHASES)' }} </h5>
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
                            <th>CLIENT NAME</th>
                            <th>ACCOUNT CURRENCY</th>
                            <th>AMT DUE (0-30 Days)</th>
                            <th>AMT DUE (31-60 Days)</th>
                            <th>AMT DUE (61-90 Days)</th>
                            <th>AMT DUE (90+ Days)</th>
                            <th>ACTION</th>
                        </tr>
                        </thead>
                        <tbody>
                            @foreach($data as $invoice)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $invoice->client_name }}</td>
                                    <td>{{ $invoice->currency_symbol }}</td>
                                    <td>{{ number_format($invoice->amount_due_30_days, 2) }}</td>
                                    <td>{{ number_format($invoice->amount_due_60_days, 2) }}</td>
                                    <td>{{ number_format($invoice->amount_due_90_days, 2) }}</td>
                                    <td>{{ number_format($invoice->amount_due_90_plus, 2) }}</td>
                                    <td>
                                        <a class="link text-secondary m-2" data-bs-toggle="tooltip" data-bs-placement="left" title="View Payment Distribution" href="{{ route('accounts.viewAgingInvoices', base64_encode($invoice->client_id. ':'. base64_decode($id))) }}"> <span class="fas fa-folder-open"> </span> </a>
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
