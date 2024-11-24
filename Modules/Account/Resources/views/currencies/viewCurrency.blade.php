@extends('account::layouts.default')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/2.1.5/css/dataTables.dataTables.css">
@section('account::dashboard')
    <div class="card">
        <div class="card-header">
            <div class="row flex-between-center">
                <div class="col-6 col-sm-auto d-flex align-items-center pe-0">
                    <h5 class="fs-9 mb-0 text-nowrap py-0 py-xl-0">Supported Currencies </h5>
                </div>
                <div class="col-6 col-sm-auto ms-auto text-end ps-0">
                    <div id="table-simple-pagination-replace-element">
                        @if(auth()->user()->role_id == 7)
                            <a class="btn btn-falcon-default btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#staticBackdrop"><span class="fas fa-plus" data-fa-transform="shrink-3 down-2"></span><span class="d-none d-sm-inline-block ms-1">New Currency</span></a>
                        @endif
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
                                    <h5 class="mb-1" id="staticBackdropLabel">ADD NEW CURRENCY</h5>
                                </div>
                                <div class="p-4">
                                    <div class="row">
                                        <form method="POST" action="{{ route('accounts.addCurrency') }}">
                                            @csrf
                                            <div class="form-floating mb-4">
                                                <input type="text" name="currency_name" class="form-control" placeholder="--" required>
                                                <label>CURRENCY NAME</label>
                                            </div>

                                            <div class="form-floating mb-4">
                                                <input type="text" name="currency_symbol" class="form-control" placeholder="--">
                                                <label> CURRENCY SYMBOL</label>
                                            </div>

                                            <div class="form-floating mb-4" >
                                                <select name="priority" class="form-select" required>
                                                    <option value="" selected disabled> -- select currency priority -- </option>
                                                    <option value="1">PRIMARY CURRENCY</option>
                                                    <option value="2">SECONDARY CURRENCY</option>
                                                </select>
                                                <label>CURRENCY PRIORITY</label>
                                            </div>

                                            <div class="d-flex justify-content-center mt-2">
                                                <button type="submit" class="btn btn-success">SAVE CURRENCY</button>
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
                            <th>CURRENCY NAME</th>
                            <th>CURRENCY SYMBOL</th>
                            <th>PRIORITY</th>
                            <th>STATUS</th>
                            @if(auth()->user()->role_id == 7)
                                <th>ACTION</th>
                            @endif
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($currencies as $currency)
                            <tr>
                                <td> {{ $loop->iteration }} </td>
                                <td> {{ $currency->currency_name }} </td>
                                <td> {{ $currency->currency_symbol }} </td>
                                <td> {!! $currency->priority == 1 ? 'PRIMARY CURRENCY' : 'SECONDARY CURRENCY' !!} </td>
                                <td> {!! $currency->status == 1 ? '<span class="badge text-bg-success"> ACTIVE </span>' : '<span class="badge text-bg-danger"> DISABLED </span>' !!} </td>
                                @if(auth()->user()->role_id == 7)
                                    <td>
                                    <a class="link text-info" data-bs-toggle="modal" title="Edit Account Information" href="#" data-bs-target="#staticBackdropEditAccount-{{ $currency->currency_id }}"><span class="fa-regular fa-pen-to-square"></span></a>
                                    <div class="modal fade" id="staticBackdropEditAccount-{{ $currency->currency_id }}" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h6 class="modal-title" id="staticBackdropLabel">UPDATE {{ $currency->currency_name }} CURRENCY</h6>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <form method="POST" action="{{ route('accounts.updateCurrency', $currency->currency_id) }}">
                                                        @csrf
                                                        <div class="form-floating mb-4">
                                                            <input type="text" name="currency_name" class="form-control" placeholder="--" value="{{ $currency->currency_name }}" required>
                                                            <label>ACCOUNT NAME</label>
                                                        </div>

                                                        <div class="mb-4">
                                                            <input type="text" name="currency_symbol" class="form-control" placeholder="--" value="{{ $currency->currency_symbol }}">
                                                            <label> </label>
                                                        </div>

                                                        <div class="form-floating mb-4" >
                                                            <select name="priority" class="form-select" required>
                                                                <option value="{{ $currency->priority }}" selected> {{ $currency->priority == 1 ? 'PRIMARY CURRENCY' : 'SECONDARY CURRENCY' }} </option>
                                                                <option @if($currency->priority == 1) selected @endif value="1">PRIMARY CURRENCY</option>
                                                                <option @if($currency->priority == 2) selected @endif value="2">SECONDARY CURRENCY</option>
                                                            </select>
                                                            <label>CURRENCY PRIORITY</label>
                                                        </div>

                                                        <div class="form-floating mb-4" >
                                                            <select name="status" class="form-select" required>
                                                                <option disabled selected>-- select status --</option>
                                                                <option @if($currency->status == 1) selected @endif value="1">ACTIVATE CURRENCY</option>
                                                                <option @if($currency->status == 2) selected @endif value="2">DISABLE CURRENCY</option>
                                                            </select>
                                                            <label>ACCOUNT STATUS</label>
                                                        </div>

                                                        <div class="d-flex justify-content-center mt-2">
                                                            <button type="submit" class="btn btn-success">UPDATE CURRENCY</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <a class="link text-danger" data-bs-toggle="tooltip" data-bs-placement="left" title="Delete Account" onclick="return confirm('Are you sure you want to delete this currency?')" href="{{ route('accounts.deleteCurrency', $currency->currency_id) }}"><span class="fa-regular fa-trash-can"></span></a>
                                </td>
                                @endif
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
