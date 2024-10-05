@extends('clerk::layouts.default')
@section('clerk::dashboard')
    <div class="card">
        <div class="card-header">
            <div class="row flex-between-center">
                <div class="col-6 col-sm-auto d-flex align-items-center pe-0">
                    <h5 class="fs-9 mb-0 text-nowrap py-0 py-xl-0">Verified Report Requests </h5>
                </div>
                <div class="col-6 col-sm-auto ms-auto text-end ps-0">
                    <div id="table-simple-pagination-replace-element">
                            <a class="btn btn-falcon-default btn-sm" data-bs-toggle="modal" data-bs-target="#staticBackdrop"><span class="fas fa-plus" data-fa-transform="shrink-3 down-2"></span><span class="d-none d-sm-inline-block ms-1">New Report</span></a>
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
                                    <h5 class="mb-1" id="staticBackdropLabel">CREATE A CUSTOM REPORT</h5>
                                </div>
                                <div class="p-4">

                                    <form method="POST" action="{{ route('clerk.storeReport') }}">
                                        @csrf
                                        <div class="row row-cols-sm-3 g-2">
                                            <div class="mb-4">
                                                <label class="fw-bold fs-6" style="font-size: small !important;">REQUEST TYPE</label>
                                                <select name="request_type" class="form-select  js-choice" id="reportType">
                                                    <option disabled selected>-- select report type --</option>
                                                    <option value="1">STOCK POSITION REPORT</option>
                                                    <option value="2">BLEND BALANCE REPORT</option>
                                                    <option value="3">STRAIGHT LINE REPORT</option>
                                                    <option value="4">BLEND PROCESSING REPORT</option>
                                                    <option value="5">TEA TRANSFERS REPORT</option>
                                                    <option value="6">TEA COLLECTION REPORT</option>
                                                </select>
                                            </div>
                                            <div class="mb-4">
                                                <label class="fw-bold fs-6" style="font-size: small !important;">CLIENT NAME</label>
                                                <select name="client_id" id="clientId" class="form-select js-choice" style="height: 61% !important;">
                                                    <option selected disabled value="">-- select client account --</option>
                                                    @foreach($clients as $client)
                                                        <option value="{{ $client->client_id }}">{{ $client->client_name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="mb-4">
                                                <label class="fw-bold fs-6" style="font-size: small !important;">SI/BLEND/DELIVERY NUMBER (optional)</label>
                                                <select name="request_number" id="requestNumber" class="form-select form-control-lg" style="height: 64% !important;">
                                                    <option selected disabled value="">-- select an option --</option>
                                                </select>
                                            </div>

                                            <div class="mb-4">
                                                <label class="fw-bold fs-6" style="font-size: small !important;">DATE FROM(optional)</label>
                                                <input type="date" class="form-control form-control-lg" name="date_from" style="height: 64% !important;">
                                            </div>

                                            <div class="mb-4">
                                                <label class="fw-bold fs-6" style="font-size: small !important;">DATE TO (optional)</label>
                                                <input type="date" class="form-control form-control-lg" name="date_to" style="height: 64% !important;">
                                            </div>

                                            <div class="mb-4">
                                                <label class="fw-bold fs-6" style="font-size: small !important;">PRIORITY LEVEL</label>
                                                <select name="priority" class="form-select js-choice" required>
                                                    <option disabled selected>-- select priority type --</option>
                                                    <option value="1">VERY URGENT</option>
                                                    <option value="2">URGENT</option>
                                                    <option value="3">MEDIUM PRIORITY</option>
                                                </select>
                                            </div>

                                        </div>

                                        <div class="d-flex justify-content-center mt-1">
                                            <button type="submit" id="submitBtn" class="btn btn-success col-8">CREATE REPORT REQUEST </button>
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
                            <th>Request Number</th>
                            <th>Request Type </th>
                            <th>Client Name</th>
                            <th>Report Filter</th>
                            <th>Date Ranges</th>
                            <th>Priority</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($requests as $request)
                            <tr>
                                <td> {{ $loop->iteration }} </td>
                                <td> {{ $request->service_number }} </td>
                                <td> {{ $request->request_type == 1 ? 'STOCK POSITION REPORT' : ($request->request_type == 2 ? 'BLEND BALANCE REPORT' : ($request->request_type == 3 ? 'STRAIGHT LINE REPORT' : ($request->request_type == 4 ? 'BLEND PROCESSING REPORT' : ($request->request_type == 5 ? 'TEA TRANSFERS REPORT' : 'TEA COLLECTION REPORT')))) }} </td>
                                <td> {{ $request->client_name }} </td>
                                <td> {{ $request->request_number == null ? 'NO FILTER' : $request->request_number }} </td>
                                <td> {{ $request->date_from == null ? 'FULL REPORT' : $request->date_from.' - '.$request->date_to }} </td>
                                <td> {!! $request->priority == 1 ? '<span class="badge bg-danger"> Very Urgent </span>' : ($request->priority == 2 ? '<span class="badge bg-warning"> Urgent </span>' : '<span class="badge bg-info"> Medium Priority </span>') !!} </td>
                                <td> {!! $request->status == 0 ? '<span class="badge bg-warning"> Pending Approval </span>' : '<span class="badge bg-success"> Approved </span>' !!} </td>
                                <td nowrap="">
                                    @if(auth()->user()->role_id == 2)
                                        @if($request->status == 0)
                                            <a class="text-primary" data-bs-toggle="tooltip" data-bs-placement="left" title="Approve Report Request" onclick="return confirm('Are you sure you want to approve this report request?')" href="{{ route('clerk.approveReportRequest', $request->request_id) }}">
                                         <span class="fa-regular fa-thumbs-up"></span>
                                            </a>
                                        @else
                                            <a class="text-success" data-bs-toggle="tooltip" data-bs-placement="left" title="Report Request Approved"> <span class="fa fa-check-double"> </span> </a>
                                        @endif

                                        <a class="text-secondary" data-bs-toggle="tooltip" data-bs-placement="left" title="Download Report Request" href="{{ route('clerk.downloadReportRequest', $request->request_id) }}"> <span class="fa fa-cloud-download-alt"></span> </a>

                                    @else
                                        @if($request->status == 1)
                                            <a class="text-info" data-bs-toggle="tooltip" data-bs-placement="left" title="Download Report Request" href="{{ route('clerk.downloadReportRequest', $request->request_id) }}"> <span class="fa fa-cloud-download-alt"></span> </a>
                                        @else
                                            <a class="text-warning" data-bs-toggle="tooltip" data-bs-placement="left" title="Report pending approval" > <span class="fas fa-spinner"></span>
                                            </a>
                                        @endif
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
<script>
    $(document).ready(function() {
        $('#datatable').DataTable({
            order: [0, 'asc'],
            pageLength: 50
        });


            $('#reportType, #clientId').change(function () {
                var typeReport = $('#reportType').val();
                var idClient = $('#clientId').val();
                $('#requestNumber').empty();
                $.ajax({
                    type: 'GET',
                    url: '{{ route('clerk.filterReports') }}',
                    data: {typeReport, idClient},
                    success: function (response) {

                        console.log(typeReport, idClient, response)

                        // Clear all options from the select element
                        $('#requestNumber').empty();

                        // Append the default option
                        $('#requestNumber').append('<option disabled selected class="text-center" value="">-- select number --</option>');

                        // Populate the select element with options from the response
                        $.each(response, function (requestNumber, stocks) {
                            $('#requestNumber').append('<option value="' + requestNumber + '">' + requestNumber + '</option>');
                        });

                    },
                    error: function (xhr, status, error) {
                        // Function to handle errors
                        console.error('Error:', error);
                    }
                });
            });

    });
</script>
