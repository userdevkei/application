@extends('account::layouts.default')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/css/bootstrap.min.css">
{{--<link rel="stylesheet" href="https://cdn.datatables.net/2.1.5/css/dataTables.dataTables.css">--}}
@section('account::dashboard')
    <div class="card">
        <div class="card-header">
            <div class="row flex-between-center">
                <div class="col-6 col-sm-auto d-flex align-items-center pe-0">
                    <h5 class="fs-9 mb-0 text-nowrap py-0 py-xl-0">Income/Expenses Ledgers </h5>
                </div>
                <div class="col-6 col-sm-auto ms-auto text-end ps-0">
                    <div id="table-simple-pagination-replace-element">
                        <a class="btn btn-falcon-default btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#staticBackdrop"><span class="fas fa-plus" data-fa-transform="shrink-3 down-2"></span><span class="d-none d-sm-inline-block ms-1">New I/E Ledger</span></a>
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
                                    <h5 class="mb-1" id="staticBackdropLabel">ADD NEW LEDGER</h5>
                                </div>
                                <div class="p-4">
                                    <div class="row">
                                        <form method="POST" action="{{ route('accounts.addClientAccount') }}">
                                            @csrf
                                            <div class="mb-4">
                                                <label class="my-1 fs-xs fw-bold" style="font-size: 85% !important;">LEDGER NAME</label> <br>
                                                <input id="editableSelect" type="text" list="clients" name="account_name" class="form-control editableSelect" placeholder="-- ledger name --" required style="width: 39vw !important;">
                                                <datalist id="clients">
                                                    @foreach($clients as $client)
                                                        <option value="{{ $client }}">{{ $client }}</option>
                                                    @endforeach
                                                </datalist>
                                            </div>

                                            <div class="mb-3">
                                                <label class="my-1 fs-xs fw-bold" style="font-size: 85% !important;">CLIENT ADDRESS (optional)</label>
                                                <input type="text" name="client_address" class="form-control" >
                                            </div>

                                            <div class="mb-3">
                                                <label class="my-1 fs-xs fw-bold" style="font-size: 85% !important;">ACCOUNT KRA PIN (optional)</label>
                                                <input type="text" name="kraPin" class="form-control" >
                                            </div>

                                            <div class="mb-4" >
                                                <label class="my-1 fs-xs fw-bold" style="font-size: 85% !important;">ACCOUNT TYPE</label>
                                                <select name="account_category" id="category" class="form-select choices" required>
                                                    <option disabled selected>-- select account --</option>
                                                    @foreach($categories as $category)
                                                        <option value="{{ $category->chart_id }}">{{ $category->chart_number }} - {{ $category->chart_name }} </option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div class="mb-4" >
                                                <label class="my-1 fs-xs fw-bold" style="font-size: 85% !important;">ACCOUNT CURRENCY</label>
                                                <select name="account_currency" id="account_currency" class="form-select choices" required>
                                                    <option disabled selected>-- select currency --</option>
                                                    @foreach($currencies as $currency)
                                                        <option value="{{ $currency->currency_id }}"> {{ $currency->currency_symbol }} - {{ $currency->currency_name }} </option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div class="mb-4" >
                                                <label class="my-1 fs-xs fw-bold" style="font-size: 85% !important;">ACCOUNT LEDGER TYPE</label>
{{--                                                <select name="type" class="form-select js-choice" required>--}}
                                                <select class="form-select js-choice" name="type" size="1" data-options='{"removeItemButton":true,"placeholder":true}'>
                                                    <option disabled selected value="">-- select ledger type --</option>
                                                    <option value="1">INCOME LEDGER</option>
                                                    <option value="2">EXPENSE LEDGER</option>
                                                    <option value="3">EXPENSE/INCOME LEDGER</option>
                                                    <option value="4">PAYMENT LEDGER</option>
                                                    <option value="5">OPENING BALANCE</option>
                                                    <option value="6">CLOSING BALANCE</option>
                                                </select>
                                            </div>

                                            <div class="mb-4">
                                                <label class="my-1 fs-xs fw-bold" style="font-size: 85% !important;">ACCOUNT DESCRIPTION</label>
                                                <textarea type="text" name="description" class="form-control" rows="3" placeholder="ACCOUNT DESCRIPTION"></textarea>
                                                <label> </label>
                                            </div>

                                            <div class="d-flex justify-content-center mt-2">
                                                <button type="submit" class="btn btn-success">SAVE INCOME/EXPENSE</button>
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
                            <th>ACCOUNT #</th>
                            <th>CLIENT NAME</th>
                            <th>ACCOUNT CATEGORY</th>
                            <th>ACCOUNT TYPE</th>
                            <th>CURRENCY USED</th>
                            <th>OPENED ON</th>
                            <th>DESCRIPTION</th>
                            <th>STATUS</th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($clientsAccounts as $account)
                            <tr>
                                <td> {{ $loop->iteration }} </td>
                                <td> {{ $account->client_account_number }} </td>
                                <td> {{ $account->client_account_name }} </td>
                                <td> {{ $account->chart_name }} </td>
                                <td> {{ $account->sub_account_name }} </td>
                                <td> {{ $account->currency_name }} ({{ $account->currency_symbol }})</td>
                                <td> {{ \Carbon\Carbon::createFromTimestamp($account->opening_date)->format('D, d/m/y H:i') }} </td>
                                <td> {{ $account->description }} </td>
                                <td> {!! $account->closing_date == null ? '<span class="badge text-bg-success"> ACTIVE </span>' : '<span class="badge text-bg-danger"> ACCOUNT CLOSED </span>' !!} </td>
                                <td>
                                    <a class="link-info" data-bs-toggle="modal" title="Edit Account Information" href="#" data-bs-target="#staticBackdropEditAccount-{{ $account->client_account_id }}"><span class="fa-regular fa-pen-to-square"></span></a>
                                    @if(auth()->user()->role_id == 7)
                                        <a class="link-danger" data-bs-toggle="tooltip" data-bs-placement="left" title="Delete income/expense ledger" onclick="return confirm('Are you sure you want to delete this account?')" href="{{ route('accounts.deleteClientAccount', $account->client_account_id) }}"><span class="fa-regular fa-trash-can"></span></a>
                                    @endif

                                    <div class="modal fade" id="staticBackdropEditAccount-{{ $account->client_account_id }}" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered modal-lg">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h6 class="modal-title" id="staticBackdropLabel">UPDATE {{ $account->client_account_number }} {{ $account->client_account_name }} DETAILS</h6>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <form id="UpdateForm" method="POST" action="{{ route('accounts.updateClientAccount', $account->client_account_id) }}">
                                                        @csrf
                                                        <div class="mb-4">
                                                            <label class="my-1 fs-xs fw-bold" style="font-size: 100% !important;">CLIENT NAME</label> <br>
                                                            <input id="editableSelect" type="text" list="clients" value="{{ $account->client_account_name }}" name="account_name" class="form-control form-control-lg" placeholder="-- client name --" required style="height: 100% !important;">
                                                            <datalist id="clients">
                                                                @foreach($clients as $client)
                                                                    <option value="{{ $client }}">{{ $client }}</option>
                                                                @endforeach
                                                            </datalist>
                                                        </div>

                                                        <div class="mb-4" >
                                                            <label class="my-1 fs-xs fw-bold" style="font-size: 100% !important;">ACCOUNT TYPE</label> <br>
                                                            <select name="account_category" size="1" class="form-select js-choice readonly" required disabled>
                                                                <option disabled selected>-- select account --</option>
                                                                @foreach($categories as $category)
                                                                    <option @if($category->chart_id == $account->chart_id) selected @endif value="{{ $category->chart_id }}">{{ $category->chart_number }} - {{ $category->chart_name }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>

                                                        <div class="mb-3">
                                                            <label class="my-1 fs-xs fw-bold" style="font-size: 85% !important;">CLIENT ADDRESS (optional)</label>
                                                            <input type="text" name="client_address" value="{{ $account->client_address }}" class="form-control form-select-lg" >
                                                        </div>

                                                        <div class="mb-3">
                                                            <label class="my-1 fs-xs fw-bold" style="font-size: 85% !important;">ACCOUNT KRA PIN (optional)</label>
                                                            <input type="text" name="kraPin" class="form-control form-control-lg" value="{{ $account->kra_pin }}" style="height: 100%!important;">
                                                        </div>

                                                        <div class="mb-3" >
                                                            <label class="my-1 fs-xs fw-bold" style="font-size: 100% !important;">ACCOUNT PAID IN</label>
                                                            <select class="form-select js-choice" size="1" name="account_currency" data-options='{"removeItemButton":true,"placeholder":true}'>
                                                            {{--                                                            <select name="account_currency" class="form-select" id="account_currency">--}}
                                                                <option selected value=""> -- select currency --  {{ $account->currency_id }} </option>
                                                                @foreach($currencies as $upCurrency)
                                                                    <option @if($upCurrency->currency_id == $account->currency_id) selected @endif value="{{ $upCurrency->currency_id }}"> {{ $upCurrency->currency_symbol }} - {{ $upCurrency->currency_name }} </option>
                                                                @endforeach
                                                            </select>
                                                        </div>

                                                        <div class="mb-4" >
                                                            <label class="my-1 fs-xs fw-bold" style="font-size: 100% !important;">ACCOUNT STATUS</label>
                                                            <select class="form-select js-choice" size="1" name="account_status" data-options='{"removeItemButton":true,"placeholder":true}'>
                                                            {{--                                                            <select name="account_status" id="account_status" class="form-select" >--}}
                                                                <option @if($account->closing_date == null) selected @endif value="1" >ACTIVATE ACCOUNT</option>
                                                                <option @if($account->closing_date !== null) selected @endif value="2" >CLOSE ACCOUNT</option>
                                                            </select>
                                                        </div>

                                                        <div class="mb-4" >
                                                            <select class="form-select js-choice" size="1" name="type" data-options='{"removeItemButton":true,"placeholder":true}'>
{{--                                                            <select name="type" class="form-select " >--}}
                                                                <option @if($account->type == null) selected @endif value="null"  >-- select ledger type --</option>
                                                                <option @if($account->type == 1)  selected @endif value="1">INCOME LEDGER</option>
                                                                <option @if($account->type == 2)  selected @endif value="2">EXPENSE LEDGER</option>
                                                                <option @if($account->type == 3)  selected @endif value="3">EXPENSE/INCOME LEDGER</option>
                                                                <option @if($account->type == 4)  selected @endif value="4">PAYMENT LEDGER</option>
                                                                <option @if($account->type == 5)  selected @endif value="4">OPENING BALANCE</option>
                                                                <option @if($account->type == 6)  selected @endif value="4">CLOSING BALANCE</option>
                                                            </select>
                                                        </div>

                                                        <div class="mb-4">
                                                            <textarea type="text" name="description" class="form-control form-select-lg" rows="3" placeholder="ACCOUNT DESCRIPTION">{{ $account->description }}</textarea>
                                                            <label> </label>
                                                        </div>

                                                        <div class="d-flex justify-content-center mt-2">
                                                            <button type="submit" class="btn btn-success"> UPDATE CLIENT ACCOUNT</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
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
{{--<script src="https://cdn.datatables.net/2.1.5/js/dataTables.js"></script>--}}
<script>
    $(document).ready(function() {
        $('#datatable').DataTable({
            order: [0, 'asc'],
            pageLength: 50
        });
    });
</script>
