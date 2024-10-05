@extends('account::layouts.default')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/2.1.5/css/dataTables.dataTables.css">
@section('account::dashboard')
    <div class="card">
        <div class="card-header">
            <div class="row flex-between-center">
                <div class="col-6 col-sm-auto d-flex align-items-center pe-0">
                    <h5 class="fs-9 mb-0 text-nowrap py-0 py-xl-0"> {{ $statements->first()->tax_name }} Statement For {{ Carbon\Carbon::parse($fy->year_starting)->format('Y') == Carbon\Carbon::parse($fy->year_ending)->format('Y') ? Carbon\Carbon::parse($fy->year_starting)->format('Y') : Carbon\Carbon::parse($fy->year_starting)->format('Y').'/'.Carbon\Carbon::parse($fy->year_ending)->format('y') }}</h5>
                </div>
                <div class="col-6 col-sm-auto ms-auto text-end ps-0">
                    <div id="table-simple-pagination-replace-element">
                        <a class="btn btn-falcon-default btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#staticBackdrop"><span class="fas fa-plus" data-fa-transform="shrink-3 down-2"></span><span class="d-none d-sm-inline-block ms-1">New Voucher</span></a>
                    </div>
                </div>
                <div class="modal fade" id="staticBackdrop" data-bs-keyboard="false" data-bs-backdrop="static" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                    <div class="modal-dialog modal-xl mt-6" role="document">
                        <div class="modal-content border-0">
                            <div class="position-absolute top-0 end-0 mt-3 me-3 z-1">
                                <button class="btn-close btn btn-sm btn-circle d-flex flex-center transition-base" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body p-0">
                                <div class="rounded-top-3 bg-body-tertiary py-3 ps-4 pe-6">
                                    <h5 class="mb-1" id="staticBackdropLabel">ADD NEW PAYMENT VOUCHER</h5>
                                </div>
                                <div class="p-4">
                                    <div class="row">
                                        <form method="POST" action="{{ route('accounts.storePaymentInvoice') }}">
                                            <div class="row row-cols-sm-2 g-2">
                                                @csrf
                                                <div class="form-floating mb-4">
                                                    <select class="form-select financialYear js-choices" id="financialYear" name="financialYear" required>
                                                        <option value="">-- select financial year --</option>
                                                        @foreach($years as $fy)
                                                            <option value="{{ $fy['financial_year_id'] }}">{{ $fy['financial_year'] }}</option>
                                                        @endforeach
                                                    </select>
                                                    <label>TRANSACTION FINANCIAL YEAR Year</label>
                                                </div>

                                                <div class="form-floating mb-4">
                                                    <select class="form-select js-choices" id="clientAccount" name="clientAccount" required>
                                                        <option value="" class="text-center">-- select an account to credit --</option>
                                                        @foreach($taxAccounts as $taxAccount)
                                                            <option value="{{ $taxAccount->tax_bracket_id }}">{{ $taxAccount->tax_name }} </option>
                                                        @endforeach
                                                    </select>
                                                    <label>TAX ACCOUNT</label>
                                                </div>

                                                <div class="form-floating mb-4">
                                                    <select class="form-select js-choices" id="account" name="account" required>
                                                        <option value="">-- select account to pay to --</option>
                                                        @foreach($accounts as $account)
                                                            <option value="{{ $account->client_account_id }}">{{ $account->client_account_name }} {{ $account->currency_symbol }}</option>
                                                        @endforeach
                                                    </select>
                                                    <label>PAYMENT TO ACCOUNT</label>
                                                </div>

                                                <div class="form-floating mb-4">
                                                    <input type="number" step="0.01" name="amountReceived" class="form-control" placeholder="--">
                                                    <label> AMOUNT RECEIVED</label>
                                                </div>

                                                <div class="form-floating mb-4">
                                                    <input type="text" name="transaction" class="form-control" placeholder="--">
                                                    <label> CHEQUE/TRANSACTION NUMBER</label>
                                                </div>

                                                <div class="form-floating mb-4">
                                                    <input type="date" name="dateReceived" class="form-control" placeholder="--" required>
                                                    <label>DATE RECEIVED</label>
                                                </div>
                                            </div>

                                            <div class="form-floating mb-4">
                                                <textarea class="form-control" style="height: 70px !important;" name="description" required></textarea>
                                                <label>DESCRIPTION</label>
                                            </div>

                                            <div class="d-flex justify-content-center mt-2">
                                                <button type="submit" class="btn btn-success">SAVE PAYMENT INVOICE</button>
                                            </div>
                                        </form>
                                    </div>
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
                    <table class="table table-sm mb-0 table-bordered table-striped" id="datatable">
                        <thead class="bg-200">
                        <tr>
                            <th>#</th>
                            <th>DATE CREATED</th>
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
                                <td> {{ Carbon\Carbon::parse($statement->date_invoiced)->format('D, d/m/y') }} </td>
                                <td> {{ $statement->invoice_number }} </td>
                                <td> {{ $statement->description }} </td>
                                <td> Ksh. {{ number_format($statement->debit, 2, '.', ',') }} </td>
                                <td> Ksh. {{ number_format($statement->credit, 2, '.', ',') }} </td>
                            </tr>

                                <?php
                                $totalDebit += (float) $statement->debit;
                                $totalCredit += (float) $statement->credit;
                                $totalBalance = $totalDebit - $totalCredit;
                                ?>
                        @endforeach
                        </tbody>
                        <tr>
                            <td colspan="4" class="fw-bold text-center">TOTALS</td>
                            <td class="fw-bold">Ksh. {{ number_format($totalDebit, 2, '.', ',') }}</td>
                            <td class="fw-bold">Ksh. {{ number_format($totalCredit, 2, '.', ',') }}</td>
                        </tr>
                        <tr class="text-center">
                            <td colspan="4" class="fw-bold"> BALANCE</td>
                            <td colspan="2" class="fw-bold"> Ksh. {{ number_format($totalBalance, 2, '.', ',') }}</td>
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
