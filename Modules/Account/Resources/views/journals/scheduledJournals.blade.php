@extends('account::layouts.default')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/2.1.5/css/dataTables.dataTables.css">
@section('account::dashboard')
    <div class="card">
        <div class="card-header">
            <div class="row flex-between-center">
                <div class="col-6 col-sm-auto d-flex align-items-center pe-0">
                    <h5 class="fs-9 mb-0 text-nowrap py-0 py-xl-0">Scheduled System Journals </h5>
                </div>
                <div class="col-6 col-sm-auto ms-auto text-end ps-0">
                    <div id="table-simple-pagination-replace-element">
{{--                        @if(auth()->user()->role_id == 7)--}}
                        <a class="btn btn-falcon-default btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#staticBackdrop"><span class="fas fa-plus" data-fa-transform="shrink-3 down-2"></span><span class="d-none d-sm-inline-block ms-1">New Schedule</span></a>
{{--                        @endif--}}
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
                                    <h5 class="mb-1" id="staticBackdropLabel">SCHEDULE NEW JOURNAL</h5>
                                </div>
                                <div class="p-4">
                                    <div class="row">
                                        <form method="POST" action="{{ route('accounts.storeScheduledSystemJournals') }}">
                                            @csrf
                                            <div class="mb-4">
                                                <label>VOUCHER NAME</label>
                                                <select class="form-select js-choice" id="chartId" name="purchaseId">
                                                    <option value="" selected disabled>-- select voucher number --</option>
                                                    @foreach($purchases as $purchase)
                                                        <option value="{{ $purchase->purchase_id }}">{{ $purchase->voucher_number }} - {{ $purchase->clientName }} ({{ $purchase->currency_symbol }}. {{ number_format($purchase->amount_due, 2) }})</option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div class="mb-4">
                                                <label>ACCOUNT NAME</label>
                                                <input type="text" class="form-control" id="accountName" value="" readonly>
                                                <input type="hidden" class="form-control" size="1" id="accountId" value="">
                                            </div>

                                            <div class="mb-4">
                                                <label>PURCHASE AMOUNT</label>
                                                <input type="text" class="form-control" size="2" id="amountDue" name="amountDue" value="" readonly>
                                            </div>

                                            <div class="mb-4">
                                                <label> JOURNAL TYPE</label>
                                                <select class="form-select js-choice" name="journalId" required>
                                                    <option  selected disabled value="">--select journal type --</option>
                                                    @foreach($journals as $journal)
                                                        <option value="{{ $journal->journal_id }}">{{ $journal->journal_name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div class="mb-4">
                                                <label> PERIOD IN MONTHS</label>
                                                <?php $months = 120; ?>
                                                <select class="form-select js-choice" name="duration" id="durationId">
                                                    <option value="" selected disabled>-- select duration --</option>
                                                    @for($i=1; $i<=$months; $i++)
                                                        <option value="{{ $i }}">{{ $i }} Months</option>
                                                    @endfor
                                                </select>
                                            </div>

                                            <div class="mb-4">
                                                <label>AMOUNT PER MONTH</label>
                                                <input type="number" class="form-control" name="installment" readonly required id="installmentsId" placeholder="monthly payments">
                                            </div>

                                            <div class="mb-4">
                                                <label> SCHEDULED JOURNAL STATUS</label>
                                                <select class="form-select js-choice" name="status">
                                                    <option value="" selected disabled>-- select status --</option>
                                                    <option value="1">ACTIVE</option>
                                                    <option value="2">INACTIVE</option>
                                                </select>
                                            </div>

                                            <div class="d-flex justify-content-center mt-2">
                                                <button type="submit" class="btn btn-md col-md-7 btn-success">SAVE JOURNAL</button>
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
                            <th>VOUCHER NUMBER</th>
                            <th>CLIENT NAME</th>
                            <th>JOURNAL NAME</th>
                            <th>AMOUNT DUE</th>
                            <th>DURATION</th>
                            <th>MONTHLY PAY</th>
                            <th>CURRENT VALUE</th>
                            <th>STATUS</th>
                            <th>ACTION</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($schedules as $schedule)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $schedule->voucher_number }}</td>
                                <td>{{ $schedule->clientName }}</td>
                                <td>{{ $schedule->journal_name }} </td>
                                <td>{{ $schedule->currency_symbol }}. {{ number_format($schedule->amount_due, 2) }}</td>
                                <td>{{ $schedule->duration }} Months</td>
                                <td>{{ number_format($schedule->monthly_due, 2) }}</td>
                                <td>{{ number_format($schedule->current_value, 2) }}</td>
                                <td>{!! $schedule->status == 1 ? '<span class="badge bg-info">ACTIVE</span>' : ( $schedule->status == 2 ? '<span class="badge bg-danger">INACTIVE</span>' :  '<span class="badge bg-success">PAID FULLY</span>') !!}</td>
                                <td>
                                    @if($schedule->status !== 3)
                                        <a class="link text-info" data-bs-toggle="modal" title="Edit Journal" href="#" data-bs-target="#staticBackdropEditAccount-{{ $schedule->journal_schedule_id }}"><span class="fa-regular fa-pen-to-square"></span></a>
                                        <div class="modal fade" id="staticBackdropEditAccount-{{ $schedule->journal_schedule_id }}" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                                            <div class="modal-dialog modal-lg modal-dialog-centered">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h6 class="modal-title" id="staticBackdropLabel">UPDATE {{ $schedule->voucher_number }} - {{ $schedule->clientName }} ({{ $schedule->currency_symbol }}. {{ number_format($schedule->amount_due, 2) }}) MONTHLY JOURNAL</h6>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <form method="POST" action="{{ route('accounts.updateScheduledSystemJournals', $schedule->journal_schedule_id) }}">
                                                            @csrf
                                                            <div class="mb-4">
                                                                <label> JOURNAL TYPE</label>
                                                                <select class="form-select js-choice" name="journalId" required>
                                                                    <option  selected disabled value="">--select journal type --</option>
                                                                    @foreach($journals as $journal)
                                                                        <option @if($journal->journal_id == $schedule->journal_id) selected @endif value="{{ $journal->journal_id }}"> {{ $journal->journal_name }} </option>
                                                                    @endforeach
                                                                </select>
                                                            </div>

                                                            <div class="mb-4">
                                                                <label> PERIOD IN MONTHS</label>
                                                                    <?php $months = 120; ?>
                                                                <select class="form-select js-choice duration-select" name="duration" id="durationId-{{ $schedule->journal_schedule_id }}" data-row-id="{{ $schedule->journal_schedule_id }}">
                                                                    @for($i=1; $i<=$months; $i++)
                                                                        <option @if($schedule->duration == $i) selected @endif  value="{{ $i }}">{{ $i }} Months</option>
                                                                    @endfor
                                                                </select>
                                                            </div>
                                                            @php( $bill = floatval($schedule->amount_due  - $schedule->total_settled))
                                                            <input type="hidden" id="amountDue-{{ $schedule->journal_schedule_id }}" value="{{ $bill }}">

                                                            <div class="mb-4">
                                                                <label>AMOUNT PER MONTH</label>
                                                                <input type="number" class="form-control" name="installment" readonly required id="installmentsId-{{ $schedule->journal_schedule_id }}" placeholder="monthly payments" value="{{ $schedule->monthly_due }}">
                                                            </div>

                                                            <div class="mb-4">
                                                                <label> SCHEDULED JOURNAL STATUS</label>
                                                                <select class="form-select js-choice" name="status">
                                                                    <option @if($schedule->status == 1) selected @endif value="1">ACTIVE</option>
                                                                    <option @if($schedule->status == 2) selected @endif value="2">INACTIVE</option>
                                                                </select>
                                                            </div>

                                                            <div class="d-flex justify-content-center mt-2">
                                                                <button type="submit" class="btn btn-md col-md-7 btn-success">UPDATE JOURNAL</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @else
                                        <span class="fa-solid fa-check-double text-success"></span>
                                    @endif

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

        $('#chartId').on('change', function () {
           var purchaseId = $(this).val();

           $.ajax({
               type: 'get',
               url: '{{ route('accounts.fetchLedgerToScheduleJournal') }}',
               data: {purchaseId},
               success: function (response) {
                   console.log(response);
                   if (response) {
                       // Clear previous options except for the placeholder
                       $('#accountId').val('');
                       $('#accountName').val('');
                       $('#amountDue').val('');

                       $('#accountId').val(response.purchase_id);
                       $('#accountName').val(response.clientName);
                       $('#amountDue').val(response.amount_due);
                   }
               }
           });
        });

        $('#durationId').change(function () {
           var duration = $(this).val();
           var amountDue = $('#amountDue').val();
           var installment = (amountDue / duration).toFixed(2)

            $('#installmentsId').val(installment)

        });


// Attach change event handler to dynamically generated duration dropdowns
        $(document).on('change', '.duration-select', function () {
            let rowId = $(this).data('row-id'); // Get the unique row ID
            let duration = $(this).val();
            let amountDue = $(`#amountDue-${rowId}`).val();
            let installment = (amountDue / duration).toFixed(2);

            console.log(rowId, duration, amountDue, installment)

            // Update the installment field in the specific row
            $(`#installmentsId-${rowId}`).val(installment);
        });

    });
</script>
