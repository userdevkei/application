@extends('clerk::layouts.default')
@section('clerk::dashboard')
    <div class="card">
        <div class="card-header">
            <div class="row flex-between-center">
                <div class="col-6 col-sm-auto d-flex align-items-center pe-0">
                    <h5 class="fs-9 mb-0 text-nowrap py-0 py-xl-0">@if($id == 5) Internal Tea Transfers @else External Tea Transfers @endif </h5>
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
                            <th>#</th>
                            <th>Date Initiated </th>
                            <th>Delivery Number </th>
                            <th>Client Name</th>
                            <th>Packages</th>
                            <th>Net Weight</th>
                            <th>Transfer From</th>
                            <th>Destination</th>
                            <th nowrap="">Status</th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($orders as $transfer)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ \Carbon\Carbon::parse($transfer->created_at)->format('d/m/y') }}</td>
                                <td>{{ $transfer->delivery_number }}</td>
                                <td nowrap="">{{ $transfer->client_name }}</td>
                                <td>{{ $transfer->total_palettes }}</td>
                                <td>{{ number_format($transfer->total_weight, 2) }}</td>
                                <td>{{ $transfer->station_name }}</td>
                                <td nowrap="">{{ $transfer->destination_name }}</td>
                                <td>
                                    {!! $transfer->status === null ? '<span class="badge bg-warning"> Created </span>' : ($transfer->status == 0 ? '<span class="badge bg-dark"> Initiated <span>' : ($transfer->status == 1 && $transfer->origin == auth()->user()->station->location->location_id ? '<span class="badge bg-info"> Service Req. <span>' : ($transfer->status == 1 ? '<span class="badge bg-info"> Approved <span>' : ($transfer->status == 2 ? '<span class="badge bg-danger"> Released <span>' : '<span class="badge bg-success"> Received <span>')))) !!}
                                </td>
                                <td nowrap="">
                                    <div class="d-flex align-items-center">
                                        <!-- Trace Tea Icon -->
                                        @if($id == 5)
                                        @if(auth()->user()->role_id == 3)
                                            @if($transfer->status === null)
                                                <a class="link text-danger" data-bs-toggle="tooltip" data-bs-placement="left" title="Transfer pending initiation"> <span class="fa-solid fa-spinner"></span></a>
                                            @elseif($transfer->status == 0)
                                                <a class="link text-success" data-bs-toggle="tooltip" data-bs-placement="left" title="Transfer initiated, pending approval"> <span class="fa-regular fa-hourglass-half"></span> </a>
                                            @elseif($transfer->status == 1 && $transfer->origin === auth()->user()->station->location->location_id)
                                                <a class="link text-success" data-bs-toggle="tooltip" data-bs-placement="left" title="Transfer approved, release transfer"  onclick="return confirm('Are you sure you want to release teas for this request?')" href="{{ route('clerk.serviceRequest', base64_encode($transfer->delivery_number)) }}"> <span class="fa-solid fa-retweet"></span> </a>
                                            @elseif($transfer->status == 1)
                                                <a class="link text-danger" data-bs-toggle="tooltip" data-bs-placement="left" title="Transfer approved, pending release"> <span class="fa-solid fa-check"> </span> </a>
                                            @elseif($transfer->status == 2)
                                                <a class="link text-secondary" data-bs-toggle="tooltip" data-bs-placement="left" title="Transfer released, pending receiving"> <span class="fa-solid fa-truck-arrow-right"> </span></a>
                                            @else
                                                <a class="link text-success" data-bs-toggle="tooltip" data-bs-placement="left" title="Transfer received, and stock updated"> <span class="fa-solid fa-check-double"></span> </a>
                                            @endif
                                        @elseif(auth()->user()->role_id == 2)
                                            @if($transfer->status == null || $transfer->status == 0)
                                                <a class="link text-warning" data-bs-toggle="tooltip" data-bs-placement="left" title="Click to approve this transfer" onclick="return confirm('Are you sure you want to approve this transfer request?')" href="{{ route('clerk.initiateTransfer', base64_encode($transfer->delivery_number)) }}" ><span class="fa-regular fa-thumbs-up"></span></a>
                                            @elseif($transfer->status == 1)
                                                <a class="link text-success" data-bs-toggle="tooltip" data-bs-placement="left" title="Transfer approved, pending release"> <span class="fa-solid fa-check"></span> </a>
                                            @elseif($transfer->status == 2)
                                                <a class="link text-success" data-bs-toggle="tooltip" data-bs-placement="left" title="Transfer released"> <span class="fa-solid fa-truck-arrow-right"></span> </a>
                                            @else
                                                <a class="link text-success" data-bs-toggle="tooltip" data-bs-placement="left" title="Transfer released"> <span class="fa-solid fa-check-double"></span> </a>
                                            @endif
                                        @else
                                            @if($transfer->status === null)
                                                <a class="link text-danger" data-bs-toggle="tooltip" data-bs-placement="left" title="Transfer pending initiation"> <span class="fa-solid fa-spinner"></span></a>
                                            @elseif($transfer->status == 0)
                                                <a class="link text-info" data-bs-toggle="tooltip" data-bs-placement="left" title="Transfer Pending Approval" href="#" ><span class="fa-solid fa-spinner"></span></a>
                                            @elseif($transfer->status == 1)
                                                <a class="link text-success" data-bs-toggle="tooltip" data-bs-placement="left" title="Transfer approved, pending release"> <span class="fa-solid fa-check"></span> </a>
                                            @elseif($transfer->status == 2)
                                                <a class="link text-success" data-bs-toggle="tooltip" data-bs-placement="left" title="Transfer released"> <span class="fa-solid fa-truck-arrow-right"></span> </a>
                                            @else
                                                <a class="link text-success" data-bs-toggle="tooltip" data-bs-placement="left" title="Transfer released"> <span class="fa-solid fa-check-double"></span> </a>

                                            @endif
                                        @endif
                                        @elseif($id == 6)
                                            @if(auth()->user()->role_id == 3)
                                                @if($transfer->status === 0 )
                                                    <a class="link text-danger" data-bs-toggle="tooltip" data-bs-placement="left" title="Transfer pending initiation"> <span class="fa-solid fa-spinner"></span></a>
                                                @elseif($transfer->status == 1)
                                                    <a class="link text-danger" data-bs-toggle="tooltip" data-bs-placement="left" title="Transfer initiated, pending approval"> <span class="fa-solid fa-check"> </span> </a>
                                                @elseif($transfer->status == 2 && $transfer->location_id == auth()->user()->station->location->location_id)
                                                    <a class="link text-danger" data-bs-toggle="tooltip" data-bs-placement="left" title="Transfer approved, pending release" onclick="return confirm('Are you sure you want to release this transfer request?')" href="{{ route('clerk.releaseExternalTransfer', base64_encode($transfer->delivery_number)) }}"> <span class="fa-solid fa-truck-arrow-right"> </span></a>
                                                @elseif($transfer->status == 2)
                                                    <a class="link text-secondary" data-bs-toggle="tooltip" data-bs-placement="left" title="Transfer approved, pending release"> <span class="fa-solid fa-truck-arrow-right"> </span></a>
                                                @else
                                                    <a class="link text-success" data-bs-toggle="tooltip" data-bs-placement="left" title="Transfer released, and stock updated"> <span class="fa-solid fa-check-double"></span> </a>
                                                @endif
                                            @elseif(auth()->user()->role_id == 2)
                                                @if($transfer->status === 0 || $transfer->status == 1)
                                                    <a class="link text-warning" data-bs-toggle="tooltip" data-bs-placement="left" title="Click to approve this transfer" onclick="return confirm('Are you sure you want to approve this transfer request?')" href="{{ route('clerk.approveExternalTransfer', base64_encode($transfer->delivery_number)) }}" ><span class="fa-regular fa-thumbs-up"></span></a>
                                                @elseif($transfer->status == 2)
                                                    <a class="link text-success" data-bs-toggle="tooltip" data-bs-placement="left" title="Transfer approved, pending release"> <span class="fa-solid fa-truck-arrow-right"></span> </a>
                                                @else
                                                    <a class="link text-success" data-bs-toggle="tooltip" data-bs-placement="left" title="Transfer released, stock updated"> <span class="fa-solid fa-check-double"></span> </a>
                                                @endif
                                            @else
                                                @if($transfer->status === null)
                                                    <a class="link text-danger" data-bs-toggle="tooltip" data-bs-placement="left" title="Transfer pending initiation"> <span class="fa-solid fa-spinner"></span></a>
                                                @elseif($transfer->status == 0)
                                                    <a class="link text-info" data-bs-toggle="tooltip" data-bs-placement="left" title="Transfer Pending Approval" href="#" ><span class="fa-solid fa-spinner"></span></a>
                                                @elseif($transfer->status == 1)
                                                    <a class="link text-success" data-bs-toggle="tooltip" data-bs-placement="left" title="Transfer approved, pending release"> <span class="fa-solid fa-check"></span> </a>
                                                @elseif($transfer->status == 2)
                                                    <a class="link text-success" data-bs-toggle="tooltip" data-bs-placement="left" title="Transfer released"> <span class="fa-solid fa-truck-arrow-right"></span> </a>
                                                @else
                                                    <a class="link text-success" data-bs-toggle="tooltip" data-bs-placement="left" title="Transfer released"> <span class="fa-solid fa-check-double"></span> </a>

                                                @endif
                                            @endif
                                        @endif
                                        <!-- Dropdown Icon -->
                                        <div class="dropdown font-sans-serif position-static" >
                                            <a class="link text-600 btn-sm dropdown-toggle btn-reveal" type="button" data-bs-toggle="dropdown" data-boundary="window" aria-haspopup="true" aria-expanded="false">
                                                <span class="fas fa-ellipsis-h fs-10"></span>
                                            </a>
                                            <div class="dropdown-menu dropdown-menu-end border py-0">
                                                <div class="py-2">
                                                    @if($id == 5)
                                                        <a class="dropdown-item text-info" href="{{ route('clerk.viewInternalTransferDetails', base64_encode($transfer->delivery_number)) }}">View Transfer</a>
                                                        <a class="dropdown-item text-primary" href="{{ route('clerk.downloadInterDelNote', base64_encode($transfer->delivery_number)) }}">Download Transfer</a>
                                                        @if($transfer->location_id == auth()->user()->station->location->location_id && $transfer->status == 2)
                                                            <a class="dropdown-item text-danger" href="{{ route('clerk.prepareToReceiveTransfer', base64_encode($transfer->delivery_number)) }}">Receive Transfer</a>
                                                        @endif
                                                    @else
                                                        <div class="py-2">
                                                            <a class="dropdown-item text-info" href="{{ route('clerk.viewExternalTransferDetails', base64_encode($transfer->delivery_number)) }}">View Transfer</a>
                                                            <a class="dropdown-item text-primary" href="{{ route('clerk.downloadExtraDelNote', base64_encode($transfer->delivery_number)) }}">Download Transfer</a>
                                                        </div>
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
<script>
    $(document).ready(function() {
        $('#datatable').DataTable({
            order: [0, 'asc'],
            pageLength: 50
        });

        $('#selectWarehouse').change(function () {
            var stationId = $('#selectWarehouse').val();

            $.ajax({
                type: 'GET',
                url: '{{ route('clerk.selectStation') }}',
                data: { stationId },
                success:function (response) {
                    console.log(response)
                    $('#selectStation').empty();

                    $('#selectStation').append('<option value="" selected disabled class="text-center"> -- select client --');

                    response.forEach(function (client) {
                        $('#selectStation').append('<option value="'+ client.station_id +'">'+ client.station_name +'</option>');
                    })
                }
            })

        });

        $('#selectStation').change(function () {
            var warehouseId = $(this).val();
            $.ajax({
                type: 'GET',
                url: '{{ route('clerk.selectClients') }}',
                data: { warehouseId },
                success:function (response) {
                    console.log(response)
                    $('#selectClients').empty();

                    $('#selectClients').append('<option value="" selected disabled class="text-center"> -- select client --');

                    $.each(response, function (clientName, clients) {
                        $('#selectClients').append('<option value="'+ clients[0]. client_id +'">'+ clientName +'</option>');
                    })
                }
            })

        });
    });
</script>
