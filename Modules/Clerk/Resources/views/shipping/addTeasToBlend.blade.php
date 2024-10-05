@extends('clerk::layouts.default')
@section('clerk::dashboard')
    <div class="card">
        <div class="card-header">
            <div class="row flex-between-center">
                <div class="col-6 col-sm-auto d-flex align-items-center pe-0">
                    <h5 class="fs-9 mb-0 text-nowrap py-0 py-xl-0">SI NUMBER {{ $bs->blend_number }} <span class="text-danger"></span> <span class="text-success"> </span></h5>
                </div>
                <div class="col-6 col-sm-auto ms-auto text-end ps-0">
                    <div id="table-simple-pagination-replace-element">
                        @if($bs->status == 0 && $bs->location_id ==  auth()->user()->station->location->location_id)
                            <a class="btn btn-falcon-default btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#staticBackdrop">Select Teas</a>
                            <a class="btn btn-falcon-default btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#staticBackdrop1">Use Blend Bal</a>
                        @endif
                    </div>
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
                            <h5 class="mb-1" id="staticBackdropLabel">Select teas to add to blend</h5>
                        </div>
                        <div class="p-4">
                            <form id="shippingForm" method="POST" action="{{ route('clerk.storeBlendTeas', $bs->blend_id) }}">
                                @csrf
                                <table id="datatable1" class="table table-striped table-sm table-bordered fs-xs">
                                    <thead>
                                    <tr>
                                        <th>&#10003;</th>
                                        <th>#</th>
                                        <th>GARDEN NAME</th>
                                        <th>GRADE</th>
                                        <th>INVOICE NO.</th>
                                        <th>LOT NO.</th>
                                        <th>PKGS</th>
                                        <th>WEIGHT</th>
                                        <th>TCI NO.</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($clientTeas as $cTea)
                                            <?php $unit = $cTea->current_weight/$cTea->current_stock; ?>
                                        <tr data-weight-per-stock="{{ $unit }}">
                                            <td><input type="checkbox" name="stock_id[]" value="{{ $cTea->stock_id }}" onchange="updateFormData(this)"></td>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $cTea->garden_name }}</td>
                                            <td>{{ $cTea->grade_name }}</td>
                                            <td>{{ $cTea->invoice_number }}</td>
                                            <td>{{ $cTea->lot_number }}</td>
                                            <td><input type="number" min="1" max="{{ $cTea->current_stock }}" class="form-control form-control-sm" name="current_stock[]" value="{{ $cTea->current_stock }}" onchange="recalculateWeight(this)"></td>
                                            <td><span id="current_weight_{{ $cTea->stock_id }}">{{ $cTea->current_weight }}</span></td>
                                            <td>{{ $cTea->loading_number }}</td>
                                        </tr>
                                    @endforeach

                                    </tbody>
                                </table>

                                <input type="hidden" name="form_data" id="form_data">

                                <div class="text-center">
                                    <span id="alert" class="text-danger text-center" style="display: none !important;"> Select at least one tea to add to the shipping instruction</span>
                                </div>
                                @if($clientTeas->count() > 0)
                                    <div class="d-flex justify-content-center mt-4">
                                        <button type="submit" class="btn btn-success">ADD TEAS TO SHIPPING INSTRUCTION</button>
                                    </div>
                                @endif
                            </form>
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
                            <h5 class="mb-1" id="staticBackdropLabel">Select blend balances to add to blend</h5>
                        </div>
                        <div class="p-4">
                            <form id="shippingForm" method="POST" action="{{ route('clerk.addBlendBalanceTeas', $bs->blend_id) }}">
                                @csrf
                                <table id="datatable2" class="table table-striped table-sm table-bordered fs-xs">
                                    <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>LINE TYPE</th>
                                        <th>GARDEN NAME</th>
                                        <th>GRADE</th>
                                        <th>BLEND NO.</th>
                                        <th>PKGS</th>
                                        <th>WEIGHT</th>
                                        <th>PKGS USED</th>
                                        <th>WGHT USED</th>
                                        <th>&#10003;</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($blendBalances as $tTea)
                                        <tr data-weight-per-stock="">
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $tTea->type }}</td>
                                            <td>{{ $tTea->garden }}</td>
                                            <td>{{ $tTea->grade }}</td>
                                            <td>{{ $tTea->blend_number }}</td>
                                            <td>{{ $tTea->current_packages }}</td>
                                            <td>{{ number_format($tTea->current_weight) }}</td>
                                            <td> <input type="number" step="0.1" class="form-control form-control-sm" name="blends[{{ $tTea->blend_balance_id }}][packages]" min="0" max="{{ $tTea->ex_packages }}"> </td>
                                            <td> <input type="number" step="0.01" class="form-control form-control-sm" name="blends[{{ $tTea->blend_balance_id }}][weights]" min="0" max="{{ $tTea->net_weight }}"> </td>
                                            <td><input type="checkbox" name="stock_id[]" value="{{ $tTea->blend_balance_id }}" onchange="updateFormData(this)"></td>
                                            <input type="hidden" value="{{ $bs->station_id }}" name="station_id">
                                        </tr>
                                    @endforeach

                                    </tbody>
                                </table>

                                <input type="hidden" name="form_data" id="form_data">

                                <div class="text-center">
                                    <span id="alert" class="text-danger text-center" style="display: none !important;"> Select at least one tea to add to the shipping instruction</span>
                                </div>
                                @if($clientTeas->count() > 0)
                                    <div class="d-flex justify-content-center mt-4">
                                        <button type="submit" class="btn btn-success">ADD TEAS TO SHIPPING INSTRUCTION</button>
                                    </div>
                                @endif
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
            <script>
                function validateForm() {
                    var checkboxes = document.querySelectorAll('input[name="stock_id[]"]');
                    var checked = false;

                    checkboxes.forEach(function(checkbox) {
                        if (checkbox.checked) {
                            checked = true;
                        }
                    });

                    if (!checked) {
                        $('#alert').show();
                        return false;
                    }

                    return true;
                }

                document.getElementById('shippingForm').addEventListener('submit', function(event) {
                    if (!validateForm()) {
                        event.preventDefault();
                    }
                });

                function recalculateWeight(input) {
                    var currentStock = parseFloat(input.value);
                    var row = input.closest('tr');
                    var currentWeightSpan = row.querySelector('[id^="current_weight_"]');
                    var stockId = currentWeightSpan.id.replace('current_weight_', '');
                    var weightPerStock = parseFloat(row.dataset.weightPerStock);
                    var newCurrentWeight = currentStock * weightPerStock;
                    currentWeightSpan.textContent = newCurrentWeight.toFixed(2);

                    // Update the form_data after recalculating the weight
                    updateFormData(row.querySelector('input[name="stock_id[]"]'));
                }

                function updateFormData(checkbox) {
                    var row = checkbox.closest('tr');
                    var stockId = checkbox.value;
                    var currentStockInput = row.querySelector('input[name="current_stock[]"]');
                    var currentWeightSpan = row.querySelector('[id^="current_weight_"]');
                    var currentStock = parseFloat(currentStockInput.value);
                    var weightPerStock = parseFloat(row.dataset.weightPerStock);
                    var newCurrentWeight = currentStock * weightPerStock;

                    var dataObject = {
                        stock_id: stockId,
                        stock: currentStock,
                        weight: newCurrentWeight
                    };

                    var formDataInput = document.getElementById('form_data');
                    var existingData = formDataInput.value ? JSON.parse(formDataInput.value) : [];

                    // Update the form data when the checkbox is checked or unchecked
                    if (checkbox.checked) {
                        // Remove any previous entry for the same stock_id and push the new data
                        existingData = existingData.filter(item => item.stock_id !== stockId);
                        existingData.push(dataObject);
                    } else {
                        // Remove the unchecked stock from the form_data
                        existingData = existingData.filter(item => item.stock_id !== stockId);
                    }

                    formDataInput.value = JSON.stringify(existingData);
                }
            </script>

        </div>
        <div class="card-body overflow-hidden p-lg-3">
            <div class="row align-items-center">
                <div class="tab-pane preview-tab-pane active" role="tabpanel" aria-labelledby="tab-dom-c3976e0e-38db-410e-861a-36d04a3a7494" id="dom-c3976e0e-38db-410e-861a-36d04a3a7494">
                        <table class="table mb-0 table-bordered table-striped" id="datatable">
                            <thead class="bg-200">
                            <tr>
                                <th>#</th>
                                <th>GARDEN NAME</th>
                                <th>GRADE</th>
                                <th>INVOICE NO.</th>
                                <th>PKGS</th>
                                <th>WEIGHT</th>
                                <th>SALE</th>
                                <th>PROMPT DATE</th>
                                <th>TCI NO.</th>
                                <th>Action</th>
                            </tr>
                            </thead>
                            <?php $inputPacks = 0; $inputWeight = 0; ?>

                            <tbody>
                            @foreach($teas as $shipping)
                                    <?php
                                    $inputPacks += $shipping->blended_packages;
                                    $inputWeight += $shipping->blended_weight;
                                    ?>
                                <tr>
                                    <td> {{ $loop->iteration }} </td>
                                    <td> {{ $shipping->garden_name == null ? $shipping->garden : $shipping->garden_name  }} </td>
                                    <td> {{ $shipping->grade_name == null ? $shipping->grade : $shipping->grade_name }} </td>
                                    <td> {{ $shipping->invoice_number == null ? $shipping->blend_number : $shipping->invoice_number }} </td>
                                    <td> {{ $shipping->blended_packages }} </td>
                                    <td> {{ $shipping->blended_weight }} </td>
                                    <td> {{ $shipping->sale_number == null ? 'B/RM' : $shipping->sale_number }} </td>
                                    <td> {{ $shipping->prompt_date == null ? $shipping->blend_date : $shipping->prompt_date }} </td>
                                    <td> {{ $shipping->loading_number == null ? 'NO TCI' : $shipping->loading_number }} </td>
                                    <td>
                                    </td>
                                </tr>

                            @endforeach
                            </tbody>
                        </table>
                </div>
            </div>
            <h5 class="mt-3">SHIPMENT DETAILS</h5>
            <div class="row g-3 font-sans-serif mt-1">
                <div class="col-sm-4">
                    <div class="rounded-3 border p-3 h-100">
                        <div class="d-flex align-items-center mb-4"><span class="dot bg-info bg-opacity-25"></span>
                            <h6 class="mb-0 fw-bold">Client Details</h6>
                        </div>
                        <ul class="list-unstyled mb-0">
                            <li class="d-flex align-items-center fs-11 fw-medium pt-1 mb-3"><span class="dot bg-info bg-opacity-100"></span>
                                <p class="lh-sm mb-0 text-700">Client Name :<span class="text-900 ps-2">{{ $bs->client_name }}</span></p>
                            </li>
                            <li class="d-flex align-items-center fs-11 fw-medium pt-1 mb-3"><span class="dot bg-info bg-opacity-75"></span>
                                <p class="lh-sm mb-0 text-700">Client Email :<span class="text-900 ps-2">{{ $bs->email == null ? 'Not updated' : $bs->email }}</span></p>
                            </li>
                            <li class="d-flex align-items-center fs-11 fw-medium pt-1 mb-3"><span class="dot bg-info bg-opacity-50"></span>
                                <p class="lh-sm mb-0 text-700">Client Phone :<span class="text-900 ps-2">{{ $bs->client_phone == null ? 'Not updated' : $bs->client_phone }}</span></p>
                            </li>
                            <li class="d-flex align-items-center fs-11 fw-medium pt-1 mb-3"><span class="dot bg-info bg-opacity-25"></span>
                                <p class="lh-sm mb-0 text-700">Client Address :<span class="text-900 ps-2">{{ $bs->address == null ? 'Not updated' : $bs->address }}</span></p>
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="col-sm-4">
                    <div class="rounded-3 border p-3 h-100">
                        <div class="d-flex align-items-center mb-4"><span class="dot bg-primary"></span>
                            <h6 class="mb-0 fw-bold">Shipment Details</h6>
                        </div>
                        <ul class="list-unstyled mb-0">
                            <li class="d-flex align-items-center fs-11 fw-medium pt-1 mb-3"><span class="dot bg-primary bg-opacity-75"></span>
                                <p class="lh-sm mb-0 text-700">SI Number :<span class="text-900 ps-2">{{ $bs->blend_number }}</span></p>
                            </li>
                            <li class="d-flex align-items-center fs-11 fw-medium pt-1 mb-3"><span class="dot bg-primary bg-opacity-50"></span>
                                <p class="lh-sm mb-0 text-700">Load Type :<span class="text-900 ps-2">{{ $bs->load_type == 1 ? 'LOOSE LOADING' : 'PALLETIZED LOADING'}}</span></p>
                            </li>
                            <li class="d-flex align-items-center fs-11 fw-medium pt-1 mb-3"><span class="dot bg-primary bg-opacity-50"></span>
                                <p class="lh-sm mb-0 text-700">Vessel Name :<span class="text-900 ps-2">{{ $bs->vessel_name }}</span></p>
                            </li>
                            <li class="d-flex align-items-center fs-11 fw-medium pt-1 mb-3"><span class="dot bg-primary bg-opacity-50"></span>
                                <p class="lh-sm mb-0 text-700">Destination :<span class="text-900 ps-2">{{ $bs->port_name }}</span></p>
                            </li>
                            <li class="d-flex align-items-center fs-11 fw-medium pt-1 mb-3"><span class="dot bg-primary bg-opacity-50"></span>
                                <p class="lh-sm mb-0 text-700">Shipping Mark :<span class="text-900 ps-2">{{ $bs->shipping_mark }}</span></p>
                            </li>
                            <li class="d-flex align-items-center fs-11 fw-medium pt-1 mb-3"><span class="dot bg-primary bg-opacity-50"></span>
                                <p class="lh-sm mb-0 text-700">Shipping Instruction :<span class="text-900 ps-2">{{ $bs->standard_details }}</span></p>
                            </li>
                            <li class="d-flex align-items-center fs-11 fw-medium pt-1 mb-3"><span class="dot bg-primary bg-opacity-50"></span>
                                <p class="lh-sm mb-0 text-700">Input Packages :<span class="text-900 ps-2">{{ number_format($inputPacks, 2) }}</span></p>
                            </li>
                            <li class="d-flex align-items-center fs-11 fw-medium pt-1 mb-3"><span class="dot bg-primary bg-opacity-50"></span>
                                <p class="lh-sm mb-0 text-700">Input Weight :<span class="text-900 ps-2">{{ number_format($inputWeight, 2) }}</span></p>
                            </li>
                            <li class="d-flex align-items-center fs-11 fw-medium pt-1 mb-3"><span class="dot bg-primary bg-opacity-50"></span>
                                <p class="lh-sm mb-0 text-700">Output Packages :<span class="text-900 ps-2">{{ $bs->outputPackages }}</span></p>
                            </li>
                            <li class="d-flex align-items-center fs-11 fw-medium pt-1 mb-3"><span class="dot bg-primary bg-opacity-50"></span>
                                <p class="lh-sm mb-0 text-700">Output Weight :<span class="text-900 ps-2">{{ $bs->outputWeight }}</span></p>
                            </li>
                            <li class="d-flex align-items-center fs-11 fw-medium pt-1 mb-3"><span class="dot bg-primary bg-opacity-50"></span>
                                <p class="lh-sm mb-0 text-700">Status :<span class="text-900 ps-2">
                                        {!! $bs->status == 0 ? '<span class="badge bg-warning"> Blend Created </span>' : ($bs->status == 1 ? '<span class="badge bg-info"> Teas Updated </span>' : ($bs->status == 2 ? '<span class="badge bg-secondary"> Blend Updated </span>' : ($bs->status == 3 ? '<span class="badge bg-dark"> Pend. Approval </span>' : '<span class="badge bg-success"> Shipped on'. \Carbon\Carbon::createFromTimestamp($bs->ship_date)->format('D, d M Y H:i') .'</span>'))) !!}
                                    </span>
                                </p>
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="col-sm-4">
                    <div class="rounded-3 border p-3 h-100">
                        <div class="d-flex align-items-center mb-4"><span class="dot bg-primary"></span>
                            <h6 class="mb-0 fw-bold">Logistics</h6>
                        </div>
                        <ul class="list-unstyled mb-0">
                            <li class="d-flex align-items-center fs-11 fw-medium pt-1 mb-3"><span class="dot bg-primary bg-opacity-75"></span>
                                <p class="lh-sm mb-0 text-700">Transporter :<span class="text-900 ps-2">{{ $bs->transporter_name }}</span></p>
                            </li>
                            <li class="d-flex align-items-center fs-11 fw-medium pt-1 mb-3"><span class="dot bg-primary bg-opacity-50"></span>
                                <p class="lh-sm mb-0 text-700">Clearing Agent :<span class="text-900 ps-2">{{ $bs->agent_name }}</span></p>
                            </li>
                            <li class="d-flex align-items-center fs-11 fw-medium pt-1 mb-3"><span class="dot bg-primary bg-opacity-50"></span>
                                <p class="lh-sm mb-0 text-700">Driver Name :<span class="text-900 ps-2">{{ $bs->driver_name }}</span></p>
                            </li>
                            <li class="d-flex align-items-center fs-11 fw-medium pt-1 mb-3"><span class="dot bg-primary bg-opacity-75"></span>
                                <p class="lh-sm mb-0 text-700">Driver Phone :<span class="text-900 ps-2">{{ $bs->phone }}</span></p>
                            </li>
                            <li class="d-flex align-items-center fs-11 fw-medium pt-1 mb-3"><span class="dot bg-primary bg-opacity-50"></span>
                                <p class="lh-sm mb-0 text-700">Vehicle Reg :<span class="text-900 ps-2">{{ $bs->registration }}</span></p>
                            </li>
                            <li class="d-flex align-items-center fs-11 fw-medium pt-1 mb-3"><span class="dot bg-primary bg-opacity-50"></span>
                                <p class="lh-sm mb-0 text-700">Consignee :<span class="text-900 ps-2">{{ $bs->consignee }}</span></p>
                            </li>
                            <li class="d-flex align-items-center fs-11 fw-medium pt-1 mb-3"><span class="dot bg-primary bg-opacity-50"></span>
                                <p class="lh-sm mb-0 text-700">Container Size :<span class="text-900 ps-2">{{ $bs->container_size == 1 ? '20 FT' :($bs->conatiner_size == 2 ? '40 FT' : '40 FTHC') }}</span></p>
                            </li>
                            <li class="d-flex align-items-center fs-11 fw-medium pt-1 mb-3"><span class="dot bg-primary bg-opacity-75"></span>
                                <p class="lh-sm mb-0 text-700">Container No :<span class="text-900 ps-2">{{ \App\Models\ShipmentContainer::where('blend_id', $bs->blend_id)->count() }} CONTAINER(S) </span></p>
                            </li>
                            <li class="d-flex align-items-center fs-11 fw-medium pt-1 mb-3"><span class="dot bg-primary bg-opacity-50"></span>
                                <p class="lh-sm mb-0 text-700">Container Tare :<span class="text-900 ps-2">{{ $bs->container_tare }}</span></p>
                            </li>
                            <li class="d-flex align-items-center fs-11 fw-medium pt-1 mb-3"><span class="dot bg-primary bg-opacity-75"></span>
                                <p class="lh-sm mb-0 text-700">Seal Number : <span class="text-900 ps-2">{{ $bs->seal_number }}</span></p>
                            </li>
                            <li class="d-flex align-items-center fs-11 fw-medium pt-1 mb-3"><span class="dot bg-primary bg-opacity-50"></span>
                                <p class="lh-sm mb-0 text-700">Cargo Escorted :<span class="text-900 ps-2">{{ $bs->escort == 1 ? 'Yes' : ($bs->escort == 2 ? 'No' : null) }}</span></p>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection
<script src="https://code.jquery.com/jquery-3.7.1.js"></script>
<script>
    $(document).ready(function() {
        $('#datatable1').DataTable({
            order: [0, 'asc'],
            pageLength: 50
        });

        $('#datatable2').DataTable({
            order: [0, 'asc'],
            pageLength: 50
        });
        $('#datatable').DataTable({
            order: [0, 'asc'],
            pageLength: 50
        });
    });

</script>

