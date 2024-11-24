@extends('account::layouts.default')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/2.1.5/css/dataTables.dataTables.css">
@section('account::dashboard')
    <div class="card">
        <div class="card-header">
            <div class="row flex-between-center">
                <div class="col-6 col-sm-auto d-flex align-items-center pe-0">
                    <h5 class="fs-9 mb-0 text-nowrap py-0 py-xl-0">Payment Vouchers </h5>
                </div>
                <div class="col-6 col-sm-auto ms-auto text-end ps-0">
                    <div id="table-simple-pagination-replace-element">
                        @if(auth()->user()->role_id == 9)
                            <a class="btn btn-falcon-default btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#staticBackdrop"><span class="fas fa-plus" data-fa-transform="shrink-3 down-2"></span><span class="d-none d-sm-inline-block ms-1">New Voucher</span></a>
                        @endif
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
                                        <form method="POST" action="{{ route('accounts.storePurchasePaymentInvoice') }}">
                                            <div class="row row-cols-sm-2 g-2">
                                                @csrf
                                                <div class="mb-4">
                                                    <select class="form-select js-choice" id="financialYear" size="1" name="financialYear" data-options='{"removeItemButton":true,"placeholder":true}'>
                                                    {{--                                                    <select class="form-select financialYear" id="financialYear" name="financialYear" required>--}}
                                                        <option value="">-- select financial year --</option>
                                                        @foreach($years as $fy)
                                                            <option value="{{ $fy['financial_year_id'] }}">{{ $fy['financial_year'] }}</option>
                                                        @endforeach
                                                    </select>
{{--                                                    <label>TRANSACTION FINANCIAL YEAR</label>--}}
                                                </div>

                                                <div class="form-floating mb-4">
                                                    <select class="form-select js-choice" id="clientAccount" size="2" name="clientAccount" data-options='{"removeItemButton":true,"placeholder":true}' style="height: 125% !important;">
                                                    {{--                                                    <select class="form-select choices" id="clientAccount" name="clientAccount" required>--}}
                                                        <option selected disabled value="" class="text-center">-- select an account to credit --</option>
                                                        @foreach($accounts as $account)
                                                            <option value="{{ $account->client_account_id }}">{{ $account->client_account_name }} {{ $account->currency_symbol }}</option>
                                                        @endforeach
                                                    </select>
{{--                                                    <label>PAYMENT FOR ACCOUNT</label>--}}
                                                </div>

                                                <div class="form-floating mb-4">
                                                    <select class="form-select" id="account" name="account" required >
                                                        <option value="">-- select account to pay to --</option>
                                                    </select>
                                                    <label> PAYMENT METHOD</label>
                                                </div>

                                                <div class="form-floating mb-4">
                                                    <input type="number" step="0.01" name="amountReceived" class="form-control" placeholder="--" >
                                                    <label> AMOUNT RECEIVED</label>
                                                </div>

                                                <div class="form-floating mb-4">
                                                    <input type="text" name="transaction" class="form-control" placeholder="--" >
                                                    <label> CHEQUE/TRANSACTION NUMBER</label>
                                                </div>

                                                <div class="form-floating mb-4">
                                                    <input type="date" name="dateReceived" class="form-control" placeholder="--" required >
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
                    <table class="table mb-0 table-bordered table-striped" id="datatable">
                        <thead class="bg-200">
                        <tr>
                            <th>#</th>
                            <th>INVOICE NUMBER</th>
                            <th>FINANCIAL YEAR</th>
                            <th>SUPPLIER ACCOUNT NAME</th>
                            <th>ACCOUNT PAID</th>
                            <th>TRANSACTION/CHEQUE #</th>
                            <th>AMOUNT RECEIVED</th>
                            <th>INVOICE RECEIVED</th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($transactions as $invoice)
                            <tr>
                                <td> {{ $loop->iteration }} </td>
                                <td> {{ $invoice->invoice_number }} </td>
                                <td> {{ Carbon\Carbon::parse($invoice->year_starting)->format('Y') == Carbon\Carbon::parse($invoice->year_ending)->format('Y') ? Carbon\Carbon::parse($invoice->year_starting)->format('Y') : Carbon\Carbon::parse($invoice->year_starting)->format('Y').'/'.Carbon\Carbon::parse($invoice->year_ending)->format('y') }} </td>
                                <td> {{ $invoice->client_account_name }} </td>
                                <td> {{ $invoice->account }} </td>
                                <td> {{ $invoice->transaction_code }} </td>
                                <td> {{ $invoice->currency_symbol }} {{ number_format($invoice->amount_received, 2) }} </td>
                                <td> {{ \Carbon\Carbon::createFromTimestamp($invoice->date_received)->format('D, d/m/y') }} </td>
                                <td>
                                    <a class="link text-success" data-bs-toggle="tooltip" data-bs-placement="left" title="Download Payment Voucher" href="{{ route('accounts.downloadPaymentReceipt', $invoice->payment_id) }}"> <span class="fa-solid fa-file-download"></span> </a>
                                    <a class="link text-secondary m-2" data-bs-toggle="tooltip" data-bs-placement="left" title="View Payment Distribution" href="{{ route('accounts.purchaseVoucherDistribution', $invoice->payment_id) }}"> <span class="fas fa-folder-open"> </span> </a>
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
        $('#datatable').DataTable( {
            order: [ 0, 'asc' ],
            pageLength: 50
        } );

    $('#clientAccount').on('change', function () {
        var clientAccount = $(this).val();

        $.ajax({
            type: 'GET',
            url: '{{ route('accounts.getPaymentMethods') }}',
            data: { clientAccount },
            success: function (data) {
                console.log(data)
                var $select = $('#account'); // Replace with the actual ID of your <select> element

                // Clear existing options
                $select.empty();

                // Add a default placeholder option
                $select.append('<option value="">Select a payment method</option>');

                // Populate new options
                $.each(data, function(index, paymentMethod) {
                    $select.append('<option value="' + paymentMethod.client_account_id + '">' + paymentMethod.client_account_name +' - '+ paymentMethod.currency_symbol + '</option>');
                });
            }
        });
    });
    });
</script>
