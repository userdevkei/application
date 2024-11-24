@extends('account::layouts.default')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/2.1.5/css/dataTables.dataTables.css">
@section('account::dashboard')
    <div class="card">
        <div class="card-header">
            <div class="row flex-between-center">
                <div class="col-6 col-sm-auto d-flex align-items-center pe-0">
                    <h5 class="fs-9 mb-0 text-nowrap py-0 py-xl-0">
                        {{ $account->client_account_name }} ({{ $account->currency_symbol }}) ACCOUNT. INV. # {{ $account->voucher_number }}
                        @php
                            // Retrieve and debug timestamps
                            $dueDateTimestamp = $account->due_date;
                            $dateInvTimestamp = $account->date_invoiced;

                                $dueDate = \Carbon\Carbon::createFromTimestamp($dueDateTimestamp);
                                $dateInv = \Carbon\Carbon::createFromTimestamp($dateInvTimestamp);
                                $today = \Carbon\Carbon::today();

                                // Calculate the difference in days
                                $dateDiff = $dateInv->diffInDays($dueDate, false);
                                $daysToGo = $today->diffInDays($dueDate, false);

                                $percentage = round(($daysToGo/ abs($dateDiff)) * 100, 2);
                                if ($dueDate->lt($today)) {
                                    $daysToGo = -$daysToGo;
                                }
                        @endphp

                        {!! $account->status == 1 ? '<span class="badge bg-success">Paid </span>' : ($percentage > 75 ? '<span class="badge bg-success">'. $daysToGo. ' Days To Payment'. '</span>': ($percentage >= 50 ? '<span class="badge bg-info">'. $daysToGo. ' Days To Payment'. '</span>': ($percentage >= 25 ? '<span class="badge bg-warning">'. $daysToGo. ' Days To Payment'. '</span>': ($percentage >= 0 ? '<span class="badge bg-dark">'. $daysToGo. ' Days To Payment'. '</span>': '<span class="badge bg-danger"> Late By '. $daysToGo. ' Days'. '</span>')))) !!}
                    </h5>
                </div>
                <div class="col-6 col-sm-auto ms-auto text-end ps-0">
                    <div id="table-simple-pagination-replace-element">
                        <a class="btn btn-falcon-default btn-sm"href="{{ route('accounts.downloadPurchaseVoucher', $account->purchase_id) }}" target="_blank"><span class="fas fa-file-download" data-fa-transform="shrink-3 down-2"></span><span class="d-none d-sm-inline-block ms-1">Download</span></a>

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
                            <th>INVOICE ITEM</th>
                            <th>Description</th>
                            <th>VATABLE</th>
                            <th>QUANTITY</th>
                            <th>UNIT PRICE</th>
                            <th>DEBIT</th>
                            <th>CREDIT</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php $subTotal = 0; $totalTax = 0; $totalDue = 0; $wht = 0;?>
                        @foreach($invoices as $invoice)
                            <tr>
                                <td> {{ $loop->iteration }} </td>
                                <td> {{ $invoice->account_name }} </td>
                                <td> {{ $invoice->description }} </td>
                                <td> {{ $invoice->tax_name }} {{ $invoice->tax_rate == null ? 0 : $invoice->tax_rate }}%</td>
                                <td> {{ $invoice->quantity }}</td>
                                <td> {{ number_format($invoice->unit_price, 2) }}</td>
                                <td class="text-end"> {{ number_format($invoice->unit_price * $invoice->quantity, 2) }}</td>
                                <td class="text-end">0.00</td>
                            </tr>
                                <?php
                                $totalTax += $invoice->unit_price * $invoice->quantity * ($invoice->tax_rate/100);
                                $subTotal += $invoice->unit_price * $invoice->quantity;
                                $wht += $invoice->tax_rate == null ? 0 : (($invoice->unit_price * $invoice->quantity) * $invoice->taxRate/100);
                                ?>
                        @endforeach
                        <?php $totalDue = $subTotal; $withHolding = $wht; $totalAmountDue = $totalDue + $totalTax - $wht; ?>
                        </tbody>
                        <tr>
                            <td colspan="6" class="text-end fw-bold">SUBTOTAL </td>
                            <td class="fw-bold text-end">{{ number_format($subTotal, 2) }}</td>
                            <td class="text-end">0.00</td>
                        </tr>
                        <tr>
                            <td colspan="6" class="text-end fw-bold"> TOTAL VAT</td>
                            <td class="fw-bold text-end"> {{ number_format($totalTax, 2) }}</td>
                            <td class="text-end">0.00</td>
                        </tr>

                        <tr>
                            <td colspan="6" class="text-end fw-bold"> TOTAL WHT {{ $invoices[0]['taxRate'] }}</td>
                            <td class="text-end">0.00</td>
                            <td class="fw-bold text-end"> {{ number_format($withHolding, 2) }}</td>
                        </tr>

                        <tr>
                            <td colspan="6" class="text-end fw-bold"> AMOUNT DUE</td>
                            <td colspan="2" class="fw-bold text-center">{{ $account->currency_symbol }} {{ number_format($totalAmountDue, 2) }}</td>
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

