@extends('admin::layouts.default')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/2.1.5/css/dataTables.dataTables.css">
@section('admin::dashboard')
    <div class="card">
        <div class="card-header">
            <div class="row flex-between-center">
                <div class="col-6 col-sm-auto d-flex align-items-center pe-0">
                    <h5 class="fs-9 mb-0 text-nowrap py-0 py-xl-0">Blend Shipping </h5>
                </div>
                <div class="col-6 col-sm-auto ms-auto text-end ps-0">
                    <div id="table-simple-pagination-replace-element">
                        <a class="btn btn-falcon-default btn-sm" href="{{ route('admin.createBlendSheet') }}"><span class="fas fa-plus" data-fa-transform="shrink-3 down-2"></span><span class="d-none d-sm-inline-block ms-1">Create Blend</span></a>
                        <a class="btn btn-falcon-danger btn-sm" data-bs-toggle="modal" data-bs-target="#staticBackdrop"><span class="fas fa-cloud-download-alt" data-fa-transform=""></span><span class="d-none d-sm-inline-block ms-1">Report</span></a>
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
                                    <h5 class="mb-1" id="staticBackdropLabel">DOWNLOAD BLEND SHEET REPORT</h5>
                                </div>
                                <div class="p-4">
                                    <form method="GET" action="{{ route('admin.exportBlendsReport') }}">
                                        @csrf
                                        <div class="row row-cols-sm-2 g-2">
        
                                            <div class="mb-2">
                                                <label class="my-1 fs-xs fw-bold" style="font-size: 85% !important;">CLIENT NAME</label>
                                                <select name="client" id="clientInput" class="form-select js-choice">
                                                    <option selected disabled value="">-- select client --</option>
                                                    @foreach($clients as $clientName => $client)
                                                        <option value="{{ $client[0]->client_id }}">{{ $clientName }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
        
                                            <div class="mb-2">
                                                <label class="my-1 fs-xs fw-bold" style="font-size: 85% !important;">STATION NAME</label>
                                                <select name="station" id="stationInput" class="form-select js-choice">
                                                    <option selected disabled value="">-- select station --</option>
                                                    @foreach($stations as $stationName => $station)
                                                        <option value="{{ $station[0]->station_id }}">{{ $stationName }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
        
        
                                            <div class="mb-2 date-input-container">
                                                <label class="my-1 fs-xs fw-bold" style="font-size: 85% !important;">DATE FROM</label>
                                                <input type="date" id="monthAgo" value="" name="from" class="form-control form-control-lg" style="height: 62% !important;">
                                            </div>
        
                                            <div class="mb-2 date-input-container">
                                                <label class="my-1 fs-xs fw-bold" style="font-size: 85% !important;">DATE TO</label>
                                                <input type="date"  id="todayDate" name="to" class="form-control form-control-lg" style="height: 62% !important;">
                                            </div>
                                        </div>
        
                                        <div class="mt-2 fs-sm d-flex justify-content-center">
                                            <input class="mx-2" type="radio" name="report" value=""> <span class="text-info fw-bolder">ALL BLENDS</span>
                                            <input class="mx-2" type="radio" name="report" value="1"> <span class="text-dark fw-bolder">BLENDS PENDING</span>
                                            <input class="mx-2" type="radio" name="report" value="2"> <span class="text-success fw-bolder">BLENDS SHIPPED </span>
                                        </div>
                                        <div class="mt-2 d-flex justify-content-center">
                                            <button type="submit" class="btn btn-secondary col-7">DOWNLOAD REPORT</button>
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
                            <th>Date Initiated </th>
                            <th>Client Name</th>
                            <th>Shipping Number </th>
                            <th>Vessel Name</th>
                            <th>Destination</th>
                            <th>Warehouse</th>
                            <th nowrap="">Status</th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($sheets as $transfer)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ \Carbon\Carbon::parse($transfer->created_at)->format('d/m/y') }}</td>
                                <td nowrap="">{{ $transfer->client_name }}</td>
                                <td>{{ $transfer->blend_number }}</td>
                                <td>{{ $transfer->vessel_name }}</td>
                                <td nowrap="">{{ $transfer->port_name }}</td>
                                <td>{{ $transfer->station_name }}</td>
                                <td>
                                    {!! $transfer->status == 0 ? '<span class="badge bg-warning"> Blend Created </span>' : ($transfer->status == 1 ? '<span class="badge bg-info"> Teas Updated </span>' : ($transfer->status == 2 ? '<span class="badge bg-secondary"> Blend Updated </span>' : ($transfer->status == 3 ? '<span class="badge bg-dark"> Pend. Approval </span>' : '<span class="badge bg-success"> Shipped </span>'))) !!}
                                </td>
                                <td nowrap="">
                                    <div class="d-flex align-items-center">
                                        @if($transfer->status == 0 || $transfer->status == 1)
                                            <a class="link text-info" data-bs-placement="left" title="Click to update OutTurn Report" data-bs-toggle="tooltip" href="{{ route('admin.updateOutTurnReport', $transfer->blend_id) }}"><span class="fa-solid fa-pen-to-square"></span></a>
                                        @elseif($transfer->status == 2 || $transfer->status == 3)
                                            <a class="link text-danger" data-bs-toggle="tooltip" data-bs-placement="left" title="Blend pending confirmation and shipping" onclick="return confirm('Are you sure you want to approve this Blend? This will mark Blend as shipped')" href="{{ route('admin.markBlendTeaAsShipped', $transfer->blend_id) }}"><span class="fa-regular fa-thumbs-up"></span></a>
                                        @else
                                            <a class="link text-success" data-bs-toggle="tooltip" data-bs-placement="left" title="Blend shipped, stock updated"><span class="fa-solid fa-check-double"></span></a>
                                        @endif
                                        <div class="dropdown font-sans-serif position-static" >
                                            <a class="link text-600 btn-sm dropdown-toggle btn-reveal" type="button" data-bs-toggle="dropdown" data-boundary="window" aria-haspopup="true" aria-expanded="false">
                                                <span class="fas fa-ellipsis-h fs-10"></span>
                                            </a>
                                            <div class="dropdown-menu dropdown-menu-end border py-0">
                                                <div class="py-2">
                                                    <a class="dropdown-item text-info" href="{{ route('admin.addBlendTeas', $transfer->blend_id) }}">View Blend Sheet</a>
                                                    <a class="dropdown-item text-primary" href="{{ route('admin.downloadBlendSheet', $transfer->blend_id) }}">Download Blend Sheet</a>
                                                    <a class="dropdown-item text-dark" href="{{ route('admin.downloadOutturReport', $transfer->blend_id) }}"> Download Outturn Report</a>
                                                    <a class="dropdown-item text-secondary" href="{{ route('admin.downloadBlendDriverClearance', $transfer->blend_id) }}"> Download Driver Clearance</a>
                                                    @if($transfer->status < 4)
                                                        <a class="dropdown-item text-danger" onclick="return confirm('Are you sure you want to delete SI Number {{ $transfer->blend_number }}?')" href="{{ route('admin.deleteBlendSheet', $transfer->blend_id) }}"> Delete Blend Sheet</a>
                                                    @endif
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
<script src="https://cdn.datatables.net/2.1.5/js/dataTables.js"></script>
<script>
    $(document).ready(function() {
        $('#datatable').DataTable({
            order: [0, 'asc'],
            pageLength: 50
        });
    });
</script>
