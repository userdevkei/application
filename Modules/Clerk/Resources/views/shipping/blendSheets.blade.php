@extends('clerk::layouts.default')
@section('clerk::dashboard')
    <div class="card">
        <div class="card-header">
            <div class="row flex-between-center">
                <div class="col-6 col-sm-auto d-flex align-items-center pe-0">
                    <h5 class="fs-9 mb-0 text-nowrap py-0 py-xl-0">Blend Shipping </h5>
                </div>
                <div class="col-6 col-sm-auto ms-auto text-end ps-0">
                    <div id="table-simple-pagination-replace-element">
                        @if(auth()->user()->role_id == 3)
                            <a class="btn btn-falcon-default btn-sm" href="{{ route('clerk.createBlendSheet') }}"><span class="fas fa-plus" data-fa-transform="shrink-3 down-2"></span><span class="d-none d-sm-inline-block ms-1">Create Blend</span></a>
                        @endif
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
                                <td>{{ $transfer->client_name }}</td>
                                <td>{{ $transfer->blend_number }}</td>
                                <td>{{ $transfer->vessel_name }}</td>
                                <td>{{ $transfer->port_name }}</td>
                                <td>{{ $transfer->station_name }}</td>
                                <td>
                                    {!! $transfer->status == 0 ? '<span class="badge bg-warning"> Blend Created </span>' : ($transfer->status == 1 ? '<span class="badge bg-info"> Teas Updated </span>' : ($transfer->status == 2 ? '<span class="badge bg-secondary"> Blend Updated </span>' : ($transfer->status == 3 ? '<span class="badge bg-dark"> Pend. Approval </span>' : '<span class="badge bg-success"> Shipped </span>'))) !!}
                                </td>
                                <td nowrap="">
                                    <div class="d-flex align-items-center">
                                        @if($transfer->status == 0)
                                            @if($transfer->location_id == auth()->user()->station->location->location_id && auth()->user()->role_id == 3)
                                                <a class="link text-warning"  onclick="return confirm('Are you sure you want to initiate Blend?')" data-bs-toggle="tooltip" data-bs-placement="left" title="Click to initiate Blend" href="{{ route('clerk.updateBlendSheet', $transfer->blend_id) }}"><span class="fa-solid fa-share-from-square" ></span></a>
                                            @else
                                                <a class="link text-info" data-bs-toggle="tooltip" data-bs-placement="left" title="Blend pending submission for approval"><span class="fa-solid fa-spinner"></span></a>
                                            @endif
                                        @elseif($transfer->status == 1)
                                            @if($transfer->location_id == auth()->user()->station->location->location_id && auth()->user()->role_id == 3)
                                                <a class="link text-info" data-bs-placement="left" title="Click to update OutTurn Report" data-bs-toggle="tooltip" href="{{ route('clerk.updateOutTurnReport', $transfer->blend_id) }}"><span class="fa-solid fa-pen-to-square"></span></a>
                                            @else
                                                <a class="link text-danger" data-bs-toggle="tooltip" data-bs-placement="left" title="Blend Updated, pending details update"><span class="fa-solid fa-spinner"></span></a>
                                            @endif
                                        @elseif($transfer->status == 2)
                                            @if($transfer->location_id == auth()->user()->station->location->location_id && auth()->user()->role_id == 3)
                                                <a class="link text-danger" data-bs-toggle="tooltip" data-bs-placement="left" title="Send Blend for approval" onclick="return confirm('Are you sure you want to send this Blend for approval?')" href="{{ route('clerk.markBlendTeaAsShipped', $transfer->blend_id) }}"><span class="fa-regular fa-paper-plane"></span></a>
                                            @else
                                                <a class="link text-danger" data-bs-toggle="tooltip" data-bs-placement="left" title="Blend updated, pending submission for approval"><span class="fa-solid fa-spinner"></span></a>
                                            @endif

                                        @elseif($transfer->status == 3)
                                            @if(auth()->user()->role_id == 2)
                                                <a class="link text-danger" data-bs-toggle="tooltip" data-bs-placement="left" title="Blend pending confirmation and shipping" onclick="return confirm('Are you sure you want to approve this Blend? This will mark Blend as shipped')" href="{{ route('clerk.updateBlendSheet', $transfer->blend_id) }}"><span class="fa-regular fa-thumbs-up"></span></a>
                                            @else
                                                <a class="link dark__text-warning" data-bs-toggle="tooltip" data-bs-placement="left" title="Blend pending confirmation and shipping"><span class="fa-regular fa-hourglass-half"></span></a>
                                            @endif

                                        @else
                                            <a class="link text-success" data-bs-toggle="tooltip" data-bs-placement="left" title="Blend shipped, stock updated"><span class="fa-solid fa-check-double"></span></a>
                                        @endif
                                        <div class="dropdown font-sans-serif position-static" >
                                            <a class="link text-600 btn-sm dropdown-toggle btn-reveal" type="button" data-bs-toggle="dropdown" data-boundary="window" aria-haspopup="true" aria-expanded="false">
                                                <span class="fas fa-ellipsis-h fs-10"></span>
                                            </a>
                                            <div class="dropdown-menu dropdown-menu-end border py-0">
                                                <div class="py-2">
                                                    <a class="dropdown-item text-info" href="{{ route('clerk.addBlendTeas', $transfer->blend_id) }}">View Blend Sheet</a>
                                                    <a class="dropdown-item text-primary" href="{{ route('clerk.downloadBlendSheet', $transfer->blend_id) }}">Download Blend Sheet</a>
                                                    @if($transfer->status >= 3)
                                                        <a class="dropdown-item text-dark" href="{{ route('clerk.downloadOutturReport', $transfer->blend_id) }}"> Download Outturn Report</a>
                                                    @endif
                                                    <a class="dropdown-item text-secondary" href="{{ route('clerk.downloadBlendDriverClearance', $transfer->blend_id) }}"> Download Driver Clearance</a>
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
<script>
    $(document).ready(function() {
        $('#datatable').DataTable({
            order: [0, 'asc'],
            pageLength: 50
        });
    });
</script>
