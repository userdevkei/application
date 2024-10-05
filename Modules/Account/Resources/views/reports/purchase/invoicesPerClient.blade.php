@extends('account::layouts.default')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/2.1.5/css/dataTables.dataTables.css">
@section('account::dashboard')
    <div class="card">
        <div class="card-header">
            <div class="row flex-between-center">
                <div class="col-6 col-sm-auto d-flex align-items-center pe-0">
                    <h5 class="fs-9 mb-0 text-nowrap py-0 py-xl-0"> Services Purchased (Purchases) </h5>
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
                            <th>ACCOUNT NUMBER</th>
                            <th>CLIENT NAME</th>
                            <th>TOTAL INVOICED</th>
                            <th>INVOICE DATE</th>
                            <th>DUE DATE</th>
                            <th>ACTION</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php $sn = 0; ?>
                        @foreach($invoices as $accountNumber => $accounts)
                            @foreach($accounts as $currency => $invoice)
                                <tr>
                                    <td> {{ ++$sn }} </td>
                                    <td> {{ $accountNumber }} </td>
                                    <td> {{ $invoice[0]['clientAccount'] }} </td>
                                        <?php
                                        $totalAmount = 0;
                                        foreach ($invoice as $item){
                                            $totalAmount += $item->amount_due;
                                        }
                                        ?>
                                    <td> {{ $invoice[0]['currency_symbol'] }}{{ number_format($totalAmount, 2) }} </td>
                                    <td> {{ \Carbon\Carbon::createFromTimestamp($invoice[0]['date_invoiced'])->format('D, d/m/y') }} </td>
                                    <td> {{ \Carbon\Carbon::createFromTimestamp($invoice[0]['due_date'])->format('D, d/m/y') }} </td>
                                    <td>
                                        <a class="link-info mx-2" data-bs-toggle="tooltip" data-bs-placement="left" title="Download Supplier Statement" href="{{ route('accounts.downloadSupplierStatement', base64_encode($invoice[0]['client_account_id'].':'.$invoice[0]['financial_year_id'])) }}" data-bs-target="#staticBackdropEditAccount-{{ $invoice[0]['financial_year_id'] }}"><span class="fa-solid fa-file-arrow-down"></span></a>
                                        <a class="link-dark" data-bs-toggle="tooltip" data-bs-placement="left" title="VIew Supplier Statement" href="{{ route('accounts.viewSupplierStatement', base64_encode($invoice[0]['client_account_id'].':'.$invoice[0]['financial_year_id'])) }}"><span class="fas fa-folder-open"></span></a>
                                    </td>
                                </tr>
                            @endforeach
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
