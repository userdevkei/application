@extends('account::layouts.default')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/2.1.5/css/dataTables.dataTables.css">
@section('account::dashboard')
    <div class="card">
        <div class="card-header">
            <div class="row flex-between-center">
                <div class="col-6 col-sm-auto d-flex align-items-center pe-0">
                    <h5 class="fs-9 mb-0 text-nowrap py-0 py-xl-0">Trial Balance </h5>
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
                            <th>ACCOUNT/GROUP LEDGER</th>
                            <th>ACCOUNT NUMBER</th>
                            <th>GROUP LEDGER</th>
                            <th>TOTAL DEBIT</th>
                            <th>TOTAL CREDIT</th>
                        </tr>
                        </thead>
                        <tbody>
                        @php
                            $grandTotalDebit = 0;
                            $grandTotalCredit = 0;
                        @endphp

                        @foreach ($invoices as $accountType => $subAccounts)
                            <tr class="account-type">
                                <td colspan="5" class="fw-bold fst-italic">{{ $accountType }}</td>
                            </tr>

                            @php
                                $totalDebit = 0;
                                $totalCredit = 0;
                            @endphp

                            @foreach ($subAccounts as $subAccount)
                                @php
                                    $totalDebit += floatval($subAccount['totalDebit']);
                                    $totalCredit += floatval($subAccount['totalCredit']);
                                @endphp
                                <tr>
                                    <td class="fw-bold">{{ $subAccount['sub_account_name'] }}</td>
                                    <td>{{ $subAccount['sub_account_number'] }}</td>
                                    <td>{{ $subAccount['sub_account_name'] }}</td>
                                    <td>{{ number_format($subAccount['totalDebit'], 2) }}</td>
                                    <td>{{ number_format($subAccount['totalCredit'], 2) }}</td>
                                </tr>
                            @endforeach

                            <tr class="total-row">
                                <td colspan="3" class="text-center fw-bold">TOTALS FOR {{ $accountType }}</td>
                                <td class="fw-bold">{{ number_format($totalDebit, 2) }}</td>
                                <td class="fw-bold">{{ number_format($totalCredit, 2) }}</td>
                            </tr>

                            @php
                                $grandTotalDebit += $totalDebit;
                                $grandTotalCredit += $totalCredit;
                            @endphp
                        @endforeach

                        <tr class="total-row">
                            <td colspan="3" class="text-center fw-bold">GRAND TOTALS</td>
                            <td class="fw-bold">{{ number_format($grandTotalDebit, 2) }}</td>
                            <td class="fw-bold">{{ number_format($grandTotalCredit, 2) }}</td>
                        </tr>
                        {{--                @foreach($invoices as $accountNumber => $accounts)--}}
                        {{--                    @foreach($accounts as $currency => $invoice)--}}

                        {{--                        <tr>--}}
                        {{--                            <td> {{ ++$sn }} </td>--}}
                        {{--                            <td> {{ $accountNumber }} </td>--}}
                        {{--                            <td> {{ $invoice[0]['clientAccount'] }} </td>--}}
                        {{--                                <?php--}}
                        {{--                                $totalAmount = 0;--}}
                        {{--                                $quantity = 0;--}}
                        {{--                                foreach ($invoice as $item){--}}
                        {{--                                    $totalAmount += ($item->quantity * $item->unit_price);--}}
                        {{--                                    $quantity += $item->quantity;--}}
                        {{--                                }--}}
                        {{--                                ?>--}}
                        {{--                            <td> {{ number_format($quantity, 2) }} </td>--}}
                        {{--                            <td> {{ $invoice[0]['currency_symbol'] }}{{ number_format($totalAmount, 2) }} </td>--}}
                        {{--                            <td>--}}
                        {{--                                <a class="btn btn-sm btn-icon text-info flex-end" data-bs-toggle="tooltip" title="Download Client Statement" href="{{ route('accounts.downloadClientStatement', $invoice[0]['invoice_id']) }}" data-bs-target="#staticBackdropEditAccount-{{ $invoice[0]['invoice_id'] }}">--}}
                        {{--                                    <span class="btn-inner">--}}
                        {{--                                        <svg class="icon-20" width="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">--}}
                        {{--                                            <path d="M12.1221 15.436L12.1221 3.39502" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>--}}
                        {{--                                            <path d="M15.0381 12.5083L12.1221 15.4363L9.20609 12.5083" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>--}}
                        {{--                                            <path d="M16.7551 8.12793H17.6881C19.7231 8.12793 21.3721 9.77693 21.3721 11.8129V16.6969C21.3721 18.7269 19.7271 20.3719 17.6971 20.3719L6.55707 20.3719C4.52207 20.3719 2.87207 18.7219 2.87207 16.6869V11.8019C2.87207 9.77293 4.51807 8.12793 6.54707 8.12793L7.48907 8.12793" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>--}}
                        {{--                                        </svg>--}}
                        {{--                                    </span>--}}
                        {{--                                </a>--}}
                        {{--                                <a class="btn btn-sm btn-icon text-primary" data-bs-toggle="tooltip" title="VIew Account Statement" href="{{ route('accounts.viewAccountStatement', base64_encode($invoice[0]['client_account_id'].':'.$invoice[0]['financial_year_id'])) }}">--}}
                        {{--                                    <span class="btn-inner">--}}
                        {{--                                        <svg class="icon-20" width="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">--}}
                        {{--                                            <path d="M22.4541 11.3918C22.7819 11.7385 22.7819 12.2615 22.4541 12.6082C21.0124 14.1335 16.8768 18 12 18C7.12317 18 2.98759 14.1335 1.54586 12.6082C1.21811 12.2615 1.21811 11.7385 1.54586 11.3918C2.98759 9.86647 7.12317 6 12 6C16.8768 6 21.0124 9.86647 22.4541 11.3918Z" stroke="#130F26"></path>--}}
                        {{--                                            <circle cx="12" cy="12" r="5" stroke="#130F26"></circle>--}}
                        {{--                                            <circle cx="12" cy="12" r="3" fill="#130F26"></circle>--}}
                        {{--                                            <mask mask-type="alpha" maskUnits="userSpaceOnUse" x="9" y="9" width="6" height="6">--}}
                        {{--                                                <circle cx="12" cy="12" r="3" fill="#130F26"></circle>--}}
                        {{--                                            </mask>--}}
                        {{--                                            <circle opacity="0.89" cx="13.5" cy="10.5" r="1.5" fill="white"></circle>--}}
                        {{--                                        </svg>--}}
                        {{--                                    </span>--}}
                        {{--                                </a>--}}
                        {{--                            </td>--}}
                        {{--                        </tr>--}}
                        {{--                    @endforeach--}}
                        {{--                @endforeach--}}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
