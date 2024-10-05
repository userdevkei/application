@extends('account::layouts.default')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/2.1.5/css/dataTables.dataTables.css">
@section('account::dashboard')
    <div class="card">
        <div class="card-header">
            <div class="row flex-between-center">
                <div class="col-6 col-sm-auto d-flex align-items-center pe-0">
                    <h5 class="fs-9 mb-0 text-nowrap py-0 py-xl-0"> {{ $client->client_account_name }} LEDGER REPORT FOR {{ Carbon\Carbon::parse($fy->year_starting)->format('Y') == Carbon\Carbon::parse($fy->year_ending)->format('Y') ? Carbon\Carbon::parse($fy->year_starting)->format('Y') : Carbon\Carbon::parse($fy->year_starting)->format('Y').'/'.Carbon\Carbon::parse($fy->year_ending)->format('y') }} FINANCIAL YEAR </h5>
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
                            <th>DATE CREATED</th>
                            <th>TRANSACTION TYPE</th>
                            <th>INVOICE NUMBER</th>
                            <th>DESCRIPTION</th>
                            <th>DEBIT</th>
                            <th>CREDIT</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                        $totalDebit = 0;
                        $totalCredit = 0;
                        $totalBalance = 0;
                        ?>
                        @foreach($statements as $statement)
                            <tr>
                                <td> {{ $loop->iteration }} </td>
                                <td> {{ Carbon\Carbon::createFromTimestamp($statement->date_invoiced)->format('D, d/m/y') }} </td>
                                <td> {{ $statement->type }} </td>
                                <td> {{ $statement->invoice_number }} </td>
                                <td> {{ $statement->description }} </td>
                                <td> {{ $currency->currency_symbol }} {{ number_format($statement->debit, 2, '.', ',') }} </td>
                                <td> {{ $currency->currency_symbol }} {{ number_format($statement->credit, 2, '.', ',') }} </td>
                            </tr>

                                <?php
                                $totalDebit += (float)$statement->debit;
                                $totalCredit += (float)$statement->credit;
                                $totalBalance = $totalDebit - $totalCredit;
                                ?>
                        @endforeach
                        </tbody>
                        <tr>
                            <td colspan="5" class="fw-bold text-center">TOTALS</td>
                            <td class="fw-bold">{{ $currency->currency_symbol }} {{ number_format($totalDebit, 2, '.', ',') }}</td>
                            <td class="fw-bold">{{ $currency->currency_symbol }} {{ number_format($totalCredit, 2, '.', ',') }}</td>
                        </tr>
                        <tr class="text-center">
                            <td colspan="5" class="fw-bold">CLOSING BALANCE</td>
                            <td colspan="2"
                                class="fw-bold"> {{ $currency->currency_symbol }} {{ number_format($totalBalance, 2, '.', ',') }}</td>
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
