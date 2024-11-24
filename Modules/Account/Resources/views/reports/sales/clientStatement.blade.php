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
                        <a class="btn btn-falcon-primary btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#staticBackdrop"><span class="fas fa-plus" data-fa-transform="shrink-3 down-2"></span><span class="d-none d-sm-inline-block ms-1">Opening Bal</span></a>
                        <a class="btn btn-falcon-default btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#staticBackdrop1"><span class="fas fa-file-download" data-fa-transform="shrink-3 down-2"></span><span class="d-none d-sm-inline-block ms-1">Report</span></a>
                    </div>
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
                                    <h5 class="mb-1" id="staticBackdropLabel">ADD OPENING BALANCE</h5>
                                </div>
                                <div class="p-4">
                                    <div class="row">
                                    @if($opBal !== null)
                                        <form method="POST" action="{{ route('accounts.updateOpeningBalance') }}">
                                            <div class="row row-cols-sm-1 g-2">
                                                @csrf
                                                <input type="hidden" value="{{ base64_encode($client->client_account_id.':'.$fy->financial_year_id) }}" name="clientId">
                                                <input type="hidden" value="{{ $opBal->client_account_id }}" name="opBal">

                                                <div class="mb-1">
                                                    <label class="fw-bold">INVOICE TYPE</label>
                                                    <input type="radio" name="type" value="1" id="debitInvoice"> <span>DEBIT </span>
                                                    <input type="radio" name="type" value="2" id="creditInvoice"> <span>CREDIT </span>
                                                </div>

                                                <!-- Credit Section -->
                                                <div class="mt-2" id="credit" style="display: none;">
                                                    <div class="form-floating mb-4">
                                                        <select class="form-select" id="account" name="account">
                                                            <option value="">-- select account to pay to --</option>
                                                            @foreach($payments as $payment)
                                                                <option value="{{ $payment->client_account_id }}">{{ $payment->client_account_name }}</option>
                                                            @endforeach
                                                        </select>
                                                        <label> PAYMENT METHOD</label>
                                                    </div>

                                                    <div class="form-floating mb-4">
                                                        <input type="number" step="0.01" name="amountReceived" class="form-control" placeholder="--">
                                                        <label> AMOUNT RECEIVED</label>
                                                    </div>
                                                </div>

                                                <!-- Debit Section -->
                                                <div class="mt-2" id="debit" style="display: none;">
                                                    <div class="form-floating mb-4">
                                                        <input type="number" step="0.01" name="amountInvoice" class="form-control" placeholder="--">
                                                        <label> INVOICE AMOUNT</label>
                                                    </div>
                                                </div>

                                            </div>

                                            <div class="mt-2 d-flex justify-content-center" id="submitBtn" style="display: none !important;">
                                                <button type="submit" class="btn btn-md btn-falcon-danger col-md-7">INVOICE CLIENT</button>
                                            </div>

                                        </form>
                                        @endif

                                    </div>
                                </div>
                            </div>
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
                                        <form method="POST" action="{{ route('accounts.generateClientStatement') }}">
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

        // Get references to the radio buttons and sections
        const debitInvoice = document.getElementById('debitInvoice');
        const creditInvoice = document.getElementById('creditInvoice');
        const debitSection = document.getElementById('debit');
        const creditSection = document.getElementById('credit');
        const accountField = document.getElementById('account');
        const submitBtn = document.getElementById('submitBtn');

        function toggleInvoiceType() {
            if (debitInvoice.checked) {
                debitSection.style.display = 'block';
                creditSection.style.display = 'none';
                submitBtn.style.display = 'block';
                submitBtn.querySelector('button').textContent = 'UPDATE INVOICE';
                accountField.removeAttribute('required'); // Remove 'required' attribute for Debit
            } else if (creditInvoice.checked) {
                creditSection.style.display = 'block';
                debitSection.style.display = 'none';
                submitBtn.style.display = 'block';
                submitBtn.querySelector('button').textContent = 'UPDATE PAYMENT';
                accountField.setAttribute('required', 'required'); // Set 'required' attribute for Credit
            }
        }

        // Attach event listeners to the radio buttons
        debitInvoice.addEventListener('change', toggleInvoiceType);
        creditInvoice.addEventListener('change', toggleInvoiceType);
    });
</script>
