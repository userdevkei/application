@extends('admin::layouts.default')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/2.1.5/css/dataTables.dataTables.css">
@section('admin::dashboard')
    <div class="card">
        <div class="card-header">
            <div class="row flex-between-center">
                <div class="col-6 col-sm-auto d-flex align-items-center pe-0">
                    <h5 class="fs-9 mb-0 text-nowrap py-0 py-xl-0">PackMac Staff </h5>
                </div>
                <div class="col-6 col-sm-auto ms-auto text-end ps-0">
                    <div id="table-simple-pagination-replace-element">
                        <a class="btn btn-falcon-default btn-sm" data-bs-toggle="modal" data-bs-target="#staticBackdrop"><span class="fas fa-plus" data-fa-transform="shrink-3 down-2"></span><span class="d-none d-sm-inline-block ms-1">New Staff</span></a>
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
                                    <h5 class="mb-1" id="staticBackdropLabel">CREATE USER ACCOUNT</h5>
                                </div>
                                <div class="p-4">
                                    <form id="userForm" method="POST" action="{{ route('admin.registerUser') }}">
                                        @csrf
                                        <div class="row row-cols-sm-3 g-2">
                                            <div class="mb-4">
                                                <label class="fw-bold fs-6" style="font-size: small !important;">FIRST NAME</label>
                                                <input type="text" name="first_name" class="form-control form-control-lg" placeholder="--">
                                            </div>
                                            <div class="mb-4">
                                                <label class="fw-bold fs-6" style="font-size: small !important;">SURNAME </label>
                                                <input type="text" name="surname" class="form-control form-control-lg" placeholder="--">
                                            </div>
                                            <div class="mb-4">
                                                <label class="fw-bold fs-6" style="font-size: small !important;">OTHER NAMES</label>
                                                <input type="text" name="other" class="form-control form-control-lg" placeholder="optional">
                                            </div>

                                            <div class="mb-4">
                                                <label class="fw-bold fs-6" style="font-size: small !important;">PRIORITY LEVEL</label>
                                                <select name="gender" class="form-select js-choice" required>
                                                    <option disabled selected>-- select gender --</option>
                                                    <option value="M">MALE </option>
                                                    <option value="F">FEMALE</option>
                                                </select>
                                            </div>

                                            <div class="mb-4">
                                                <label class="fw-bold fs-6" style="font-size: small !important;">PHONE NUMBER</label>
                                                <input type="text" name="phone" max="13" min="9" class="form-control form-control-lg" placeholder="--">
                                            </div>

                                            <div class="mb-4">
                                                <label class="fw-bold fs-6" style="font-size: small !important;">EMAIL ADDRESS</label>
                                                <input type="email" name="email" class="form-control form-control-lg" placeholder="--">
                                            </div>

                                            <div class="mb-4">
                                                <label class="fw-bold fs-6" style="font-size: small !important;">ID NUMBER</label>
                                                <input type="number" name="id_number" class="form-control form-control-lg" placeholder="--">
                                            </div>

                                            <div class="mb-4">
                                                <label class="fw-bold fs-6" style="font-size: small !important;">USER ROLE</label>
                                                <select name="role" class="form-select js-choice" style="height: 61% !important;">
                                                    <option selected disabled value="">-- select client account --</option>
                                                    @foreach($roles as $role)
                                                        <option value="{{ $role->id }}">{{ $role->role_name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div class="mb-4">
                                                <label class="fw-bold fs-6" style="font-size: small !important;">STATION</label>
                                                <select name="station" class="form-select js-choice" style="height: 61% !important;">
                                                    <option selected disabled value="">-- select client account --</option>
                                                    @foreach($stations as $station)
                                                        <option value="{{ $station->station_id }}">{{ $station->station_name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>

                                        </div>

                                        <div class="d-flex justify-content-center mt-1">
                                            <button type="submit" id="submitButton" class="btn btn-success col-8">CREATE USER </button>
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
                            <th>Staff Number</th>
                            <th>Staff Name </th>
                            <th>User Role</th>
                            <th>Location </th>
                            <th>Station</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($users as $user)
                            <tr>
                                <td> {{ $loop->iteration }} </td>
                                <td> {{ $user->staff_number }} </td>
                                <td> {{ $user->staff_name }} </td>
                                <td> {{ $user->role_name }} </td>
                                <td> {{ $user->location_name }} </td>
                                <td> {{ $user->station_name }} </td>
                                <td> {!! $user->status == 1 ? '<span class="badge bg-success"> Active </span>' : '<span class="badge bg-danger"> Inactive </span>' !!} </td>
                                <td nowrap="">
                                    <a class="text-info" data-bs-toggle="modal" data-bs-target="#staticBackdrop_{{ $user->user_id }}"><span class="fa-regular fa-pen-to-square"></span><span class="d-none d-sm-inline-block ms-1"></span></a>

                                    <a class="text-danger mx-1" data-bs-toggle="tooltip" data-bs-placement="left" title="Disable User Account" onclick="return confirm('Are you sure you want to disabled this user account?')" href="{{ route('admin.disableStaff', $user->user_id) }}"> <span class="fa fa-trash-alt"></span> </a>

                                    <div class="modal fade" id="staticBackdrop_{{ $user->user_id }}" data-bs-keyboard="false" data-bs-backdrop="static" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                                        <div class="modal-dialog modal-xl mt-6" role="document">
                                            <div class="modal-content border-0">
                                                <div class="position-absolute top-0 end-0 mt-3 me-3 z-1">
                                                    <button class="btn-close btn btn-sm btn-circle d-flex flex-center transition-base" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body p-0">
                                                    <div class="rounded-top-3 bg-body-tertiary py-3 ps-4 pe-6">
                                                        <h5 class="mb-1" id="staticBackdropLabel">UPDATE USER ACCOUNT</h5>
                                                    </div>
                                                    <div class="p-4">
                                                        <form id="userForm" method="POST" action="{{ route('admin.updateUser', $user->user_id) }}">
                                                            @csrf
                                                            <div class="row row-cols-sm-3 g-2">
                                                                <div class="mb-4">
                                                                    <label class="fw-bold fs-6" style="font-size: small !important;">FIRST NAME</label>
                                                                    <input type="text" name="first_name" class="form-control form-control-lg" value="{{ $user->first_name }}" placeholder="--">
                                                                </div>
                                                                <div class="mb-4">
                                                                    <label class="fw-bold fs-6" style="font-size: small !important;">SURNAME </label>
                                                                    <input type="text" name="surname" class="form-control form-control-lg" value="{{ $user->surname }}" placeholder="--">
                                                                </div>
                                                                <div class="mb-4">
                                                                    <label class="fw-bold fs-6" style="font-size: small !important;">OTHER NAMES</label>
                                                                    <input type="text" name="other" class="form-control form-control-lg" placeholder="optional" value="{{ $user->middle_name }}">
                                                                </div>

                                                                <div class="mb-4">
                                                                    <label class="fw-bold fs-6" style="font-size: small !important;">PRIORITY LEVEL</label>
                                                                    <select name="gender" class="form-select js-choice" required>
                                                                        <option @if($user->gender == 'M') selected @endif value="M">MALE </option>
                                                                        <option @if($user->gender == 'F') selected @endif value="F">FEMALE</option>
                                                                    </select>
                                                                </div>

                                                                <div class="mb-4">
                                                                    <label class="fw-bold fs-6" style="font-size: small !important;">PHONE NUMBER</label>
                                                                    <input type="text" name="phone" max="13" min="9" class="form-control form-control-lg" value="{{ $user->phone_number }}" placeholder="--">
                                                                </div>

                                                                <div class="mb-4">
                                                                    <label class="fw-bold fs-6" style="font-size: small !important;">EMAIL ADDRESS</label>
                                                                    <input type="email" name="email" class="form-control form-control-lg" placeholder="--" value="{{ $user->email_address }}">
                                                                </div>

                                                                <div class="mb-4">
                                                                    <label class="fw-bold fs-6" style="font-size: small !important;">ID NUMBER</label>
                                                                    <input type="number" name="id_number" class="form-control form-control-lg" placeholder="--" value="{{ $user->id_number }}">
                                                                </div>

                                                                <div class="mb-4">
                                                                    <label class="fw-bold fs-6" style="font-size: small !important;">USER ROLE</label>
                                                                    <select name="role" class="form-select js-choice" style="height: 61% !important;">
                                                                        @foreach($roles as $role)
                                                                            <option @if($user->role_id == $role->id) selected @endif value="{{ $role->id }}">{{ $role->role_name }}</option>
                                                                        @endforeach
                                                                    </select>
                                                                </div>

                                                                <div class="mb-4">
                                                                    <label class="fw-bold fs-6" style="font-size: small !important;">STATION</label>
                                                                    <select name="station" class="form-select js-choice" style="height: 61% !important;">
                                                                        <option selected disabled value="">-- select client account --</option>
                                                                        @foreach($stations as $station)
                                                                            <option  @if($user->station_id == $station->station_id) selected @endif value="{{ $station->station_id }}">{{ $station->station_name }}</option>
                                                                        @endforeach
                                                                    </select>
                                                                </div>

                                                            </div>

                                                            <div class="d-flex justify-content-center mt-1">
                                                                <button type="submit" id="submitButton" class="btn btn-success col-8">UPDATE USER ACCOUNT</button>
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
</script>
