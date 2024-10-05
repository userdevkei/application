@extends('admin::layouts.default')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/2.1.5/css/dataTables.dataTables.css">
@section('admin::dashboard')
    <div class="card">
        <div class="card-header">
            <div class="row flex-between-center">
                <div class="col-6 col-sm-auto d-flex align-items-center pe-0">
                    <h5 class="fs-9 mb-0 text-nowrap py-0 py-xl-0">PHML Warehouses </h5>
                </div>
                <div class="col-6 col-sm-auto ms-auto text-end ps-0">
                    <div id="table-simple-pagination-replace-element">
                        <a class="btn btn-falcon-default btn-sm" data-bs-toggle="modal" data-bs-target="#staticBackdrop"><span class="fas fa-plus" data-fa-transform="shrink-3 down-2"></span><span class="d-none d-sm-inline-block ms-1">New Station</span></a>
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
                                    <h5 class="mb-1" id="staticBackdropLabel">CREATE NEW STATION</h5>
                                </div>
                                <div class="p-4">
                                    <form id="userForm" method="POST" action="{{ route('admin.registerStation') }}">
                                        @csrf
                                        <div class="row row-cols-sm-1 g-2">
                                            <div class="mb-1">
                                                <label class="fw-bold fs-6" style="font-size: small !important;">STATION NAME</label>
                                                <input type="text" name="station_name" class="form-control form-control-lg" placeholder="--" required>
                                            </div>

                                            <div class="mb-1">
                                                <label class="fw-bold fs-6" style="font-size: small !important;">STATION CAPACITY</label>
                                                <input type="number" name="capacity" class="form-control form-control-lg" placeholder="in packages" required>
                                            </div>

                                            <div class="mb-1">
                                                <label class="fw-bold fs-6" style="font-size: small !important;">WAREHOUSE LOCATIONS</label>
                                                <select name="location" class="form-select js-choice" required>
                                                    <option disabled selected>-- select warehouse location --</option>
                                                    @foreach($locations as $location)
                                                        <option value="{{ $location->location_id }}">{{ $location->location_name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div class="mb-1">
                                                <label class="fw-bold fs-6" style="font-size: small !important;">PHYSICAL ADDRESS</label>
                                                <input type="text" name="address" class="form-control form-control-lg" placeholder="--">
                                            </div>
                                            <div class="mb-1">
                                                <label class="fw-bold fs-6" style="font-size: small !important;">STATUS</label>
                                                <select name="status" class="form-select js-choice" required>
                                                    <option disabled selected>-- select status --</option>
                                                    <option value="1">ACTIVE</option>
                                                    <option value="2">CLOSED</option>
                                                </select>
                                            </div>

                                        </div>
                                        <div class="d-flex justify-content-center mt-1">
                                            <button type="submit" id="submitButton" class="btn btn-success col-8">CREATE PHML WAREHOUSE </button>
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
                            <th>Station Name</th>
                            <th>Station Location </th>
                            <th>Station Capacity</th>
                            <th>Physical Address</th>
                            <th>Station Bays </th>
                            <th>Status </th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($stations as $user)
                            <tr>
                                <td> {{ $loop->iteration }} </td>
                                <td> {{ $user->station_name }} </td>
                                <td> {{ $user->location_name }} </td>
                                <td> {{ $user->capacity }} </td>
                                <td> {{ $user->address }} </td>
                                <td>
                                    <a class="link text-dark flex-end" data-bs-toggle="modal" title="Update warehouses" href="#" data-bs-target="#staticBackdropW-{{ $user->station_id }}"> add/view bays </a>

                                    <div class="modal fade" id="staticBackdropW-{{ $user->station_id }}" data-bs-keyboard="false" data-bs-backdrop="static" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                                        <div class="modal-dialog modal-lg modal-dialog-centered mt-6" role="document">
                                            <div class="modal-content border-0">
                                                <div class="position-absolute top-0 end-0 mt-3 me-3 z-1">
                                                    <button class="btn-close btn btn-sm btn-circle d-flex flex-center transition-base" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body p-0">
                                                    <div class="rounded-top-3 bg-body-tertiary py-3 ps-4 pe-6">
                                                        <h5 class="modal-title" id="staticBackdropLabel">VIEW/ADD/UPDATE {{ $user->station_name }} BAYS </h5>
                                                    </div>

                                                    <div class="p-4">
                                                        <table class="table mb-0 table-bordered table-striped" id="datatable1">
                                                            <thead class="bg-200">
                                                            <tr>
                                                                <th>#</th>
                                                                <th>WAREHOUSE BAY</th>
                                                                <th>Action</th>
                                                            </tr>
                                                            </thead>
                                                            <tbody>
                                                            @foreach($user->bays as $bay)
                                                                <tr>
                                                                    <td>{{ $loop->iteration }}</td>
                                                                    <td>{{ $bay->bay_name }}</td>
                                                                    <td>
                                                                        <form method="POST" action="{{ route('admin.updateSubwarehouseName', $bay->bay_id) }}">
                                                                            @csrf
                                                                            <div class="row m-0 p-0 py-0">
                                                                                <div class="col-7">
                                                                                    <input type="text" class="form-control" name="newBay" >
                                                                                </div>
                                                                                <div class="col-5">
                                                                                    <button type="submit" class="btn btn-sm btn-success">update</button>
                                                                                </div>
                                                                            </div>
                                                                        </form>
                                                                    </td>
                                                                </tr>
                                                            @endforeach
                                                            </tbody>
                                                        </table>

                                                    <form method="POST" action="{{ route('admin.updateWarehouseBays', $user->station_id) }}">
                                                        @csrf
                                                        <div class="mt-3" id="inputFields-{{ $user->station_id }}">
                                                            <!-- Initial input field -->
                                                            <div class="form-floating mb-4 d-flex align-items-center">
                                                                <input type="text" name="warehouseBay[]" class="form-control" placeholder="--" required>
                                                                <label>WAREHOUSE BAY</label>
                                                                <div class="ms-2">
                                                                    <a class="link-primary" onclick="addInputField('{{ $user->station_id }}')"><i class="fa fa-plus"></i></a>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="d-flex justify-content-center mt-2">
                                                            <button type="submit" class="btn btn-success">UPDATE WAREHOUSE LOCATIONS</button>
                                                        </div>
                                                    </form>
                                                </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td> {{ $user->status == 1 ? 'ACTIVE' : 'CLOSED' }} </td>
                                <td nowrap="">
                                    <a class="text-info" data-bs-toggle="modal" data-bs-target="#staticBackdrop_{{ $user->station_id }}"><span class="fa-regular fa-pen-to-square"></span><span class="d-none d-sm-inline-block ms-1"></span></a>

                                    <a class="text-danger mx-1" data-bs-toggle="tooltip" data-bs-placement="left" title="Disable User Account" onclick="return confirm('Are you sure you want to disabled this user account?')" href="#"> <span class="fa fa-trash-alt"></span> </a>

                                    <div class="modal fade" id="staticBackdrop_{{ $user->station_id }}" data-bs-keyboard="false" data-bs-backdrop="static" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                                        <div class="modal-dialog modal-lg mt-6" role="document">
                                            <div class="modal-content border-0">
                                                <div class="position-absolute top-0 end-0 mt-3 me-3 z-1">
                                                    <button class="btn-close btn btn-sm btn-circle d-flex flex-center transition-base" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body p-0">
                                                    <div class="rounded-top-3 bg-body-tertiary py-3 ps-4 pe-6">
                                                        <h5 class="mb-1" id="staticBackdropLabel">UPDATE CLIENT ACCOUNT</h5>
                                                    </div>
                                                    <div class="p-4">
                                                        <form id="userForm" method="POST" action="{{ route('admin.updateStation', $user->station_id) }}">
                                                            @csrf
                                                            <div class="row row-cols-sm-1 g-2">
                                                                <div class="mb-1">
                                                                    <label class="fw-bold fs-6" style="font-size: small !important;">STATION NAME</label>
                                                                    <input type="text" name="station_name" class="form-control form-control-lg" placeholder="--" required value="{{ $user->station_name }}">
                                                                </div>

                                                                <div class="mb-1">
                                                                    <label class="fw-bold fs-6" style="font-size: small !important;">STATION CAPACITY </label>
                                                                    <input type="number" name="capacity" class="form-control form-control-lg" placeholder="--" value="{{ $user->capacity }}">
                                                                </div>

                                                                <div class="mb-1">
                                                                    <label class="fw-bold fs-6" style="font-size: small !important;">WAREHOUSE LOCATIONS</label>
                                                                    <select name="location" class="form-select js-choice" required>
                                                                        <option disabled selected>-- select warehouse location --</option>
                                                                        @foreach($locations as $location)
                                                                            <option @if($location->location_id == $user->location_id) @endif value="{{ $location->location_id }}">{{ $location->location_name }}</option>
                                                                        @endforeach
                                                                    </select>
                                                                </div>

                                                                <div class="mb-1">
                                                                    <label class="fw-bold fs-6" style="font-size: small !important;">PHYSICAL ADDRESS</label>
                                                                    <input type="text" name="address" class="form-control form-control-lg" placeholder="--" value="{{ $user->address }}">
                                                                </div>

                                                                <div class="mb-1">
                                                                    <label class="fw-bold fs-6" style="font-size: small !important;">STATUS</label>
                                                                    <select name="status" class="form-select js-choice" required>
                                                                        <option @if($user->status == 1) selected @endif value="1">ACTIVE </option>
                                                                        <option @if($user->status == 2) selected @endif value="2">CLOSED</option>
                                                                    </select>
                                                                </div>
                                                            </div>
                                                            <div class="d-flex justify-content-center mt-1">
                                                                <button type="submit" id="submitButton" class="btn btn-success col-8">UPDATE STATION DETAILS</button>
                                                            </div>
                                                        </form>
                                                    </div>
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

        $('#datatable1').DataTable({
            order: [0, 'asc'],
            pageLength: 50
        });

        $('#userForm').on('submit', function(event) {
            // event.preventDefault(); // Prevents the default form submission

            var form = $(this);
            var submitButton = $('#submitButton');

            // Simulate form submission process
            setTimeout(function() {
                // Assuming the form submission is successful, disable the button
                submitButton.prop('disabled', true);

                // You can also display a success message or perform other actions here
                // alert('Form submitted successfully!');
            }, 10); // Simulate a delay for the form submission process
        });
    });

    function addInputField(warehouseId) {
        var inputContainer = document.getElementById('inputFields-' + warehouseId);
        var newInputField = document.createElement('div');
        newInputField.className = 'form-floating mb-4 d-flex align-items-center'; // Add class for alignment

        var input = document.createElement('input');
        input.type = 'text';
        input.name = 'warehouseBay[]';
        input.className = 'form-control';
        input.placeholder = '--';
        input.required = true;

        var label = document.createElement('label');
        label.textContent = 'WAREHOUSE BAY';

        var buttonContainer = document.createElement('div');
        buttonContainer.className = 'ms-2'; // Add class for margin

        var removeButton = document.createElement('button');
        removeButton.type = 'button';
        removeButton.className = 'btn btn-sm btn-close';
        removeButton.onclick = function() {
            removeInputField(newInputField);
        };

        buttonContainer.appendChild(removeButton);

        newInputField.appendChild(input);
        newInputField.appendChild(label);
        newInputField.appendChild(buttonContainer);

        inputContainer.appendChild(newInputField);
    }

    function removeInputField(field) {
        field.parentNode.removeChild(field);
    }

</script>
