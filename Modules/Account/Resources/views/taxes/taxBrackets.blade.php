@extends('account::layouts.default')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/2.1.5/css/dataTables.dataTables.css">
@section('account::dashboard')
    <div class="card">
        <div class="card-header">
            <div class="row flex-between-center">
                <div class="col-6 col-sm-auto d-flex align-items-center pe-0">
                    <h5 class="fs-9 mb-0 text-nowrap py-0 py-xl-0">Tax Brackets </h5>
                </div>
                <div class="col-6 col-sm-auto ms-auto text-end ps-0">
                    <div id="table-simple-pagination-replace-element">
                        @if(auth()->user()->role_id == 7)
                            <a class="btn btn-falcon-default btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#staticBackdrop"><span class="fas fa-plus" data-fa-transform="shrink-3 down-2"></span><span class="d-none d-sm-inline-block ms-1">New Tax Bracket</span></a>
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
                                    <h5 class="mb-1" id="staticBackdropLabel">ADD NEW TAX BRACKET</h5>
                                </div>
                                <div class="p-4">
                                    <div class="row">
                                        <form method="POST" action="{{ route('accounts.storeTaxBracket') }}">
                                            @csrf

                                            <div class="form-floating mb-4">
                                                <select class="form-select" name="tax_id">
                                                    <option value="" selected disabled>-- select tax type --</option>
                                                    @foreach($taxes as $tax)
                                                        <option value="{{ $tax->tax_id }}">{{ $tax->tax_name }}</option>
                                                    @endforeach
                                                </select>
                                                <label> TYPE TAX </label>
                                            </div>

                                            <div class="form-floating mb-4">
                                                <input type="number" step="0.01" name="tax" min="0" class="form-control" placeholder="--" required>
                                                <label>VAT TAX RATE</label>
                                            </div>

                                            <div class="form-floating mb-4">
                                                <select class="form-select" name="status">
                                                    <option value="" selected disabled>-- select status --</option>
                                                    <option value="1">ACTIVE</option>
                                                    <option value="2">INACTIVE</option>
                                                </select>
                                                <label> TAX RATE STATUS</label>
                                            </div>

                                            <div class="d-flex justify-content-center mt-2">
                                                <button type="submit" class="btn btn-success">SAVE VAT TAX RATE</button>
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
                            <th>TAX NAME</th>
                            <th>TAX RATE</th>
                            <th>STATUS</th>
                            @if(auth()->user()->role_id == 7)
                                <th>ACTION</th>
                            @endif
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($brackets as $tax)
                            <tr>
                                <td> {{ $loop->iteration }} </td>
                                <td> {{ $tax->tax_name }} </td>
                                <td> {{ $tax->tax_rate }}% </td>
                                <td> {!! $tax->status == 1 ? '<span class="badge text-bg-success"> ACTIVE </span>' : '<span class="badge text-bg-danger"> INACTIVE </span>' !!} </td>
                                @if(auth()->user()->role_id == 7)
                                    <td>
                                    <a class="link text-info" data-bs-toggle="modal" title="Edit Tax Bracket Information" href="#" data-bs-target="#staticBackdropEditAccount-{{ $tax->tax_bracket_id }}"><span class="fa-regular fa-pen-to-square"></span></a>
                                    <div class="modal fade" id="staticBackdropEditAccount-{{ $tax->tax_bracket_id }}" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h6 class="modal-title" id="staticBackdropLabel">UPDATE TAX BRACKET</h6>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <form method="POST" action="{{ route('accounts.updateTaxBracket', $tax->tax_bracket_id) }}">
                                                        @csrf
                                                        <div class="form-floating mb-4">
                                                            <select class="form-select" name="tax_id">
                                                                <option value="{{ $tax->tax_id }}" selected>{{ $tax->tax_name }}</option>
                                                                @foreach($taxes as $tx)
                                                                    <option @if($tax->tax_id == $tx->tax_id) selected @endif value="{{ $tx->tax_id }}">{{ $tx->tax_name }}</option>
                                                                @endforeach
                                                            </select>
                                                            <label> TYPE TAX </label>
                                                        </div>

                                                        <div class="form-floating mb-4">
                                                            <input type="number" name="tax" step="0.01" min="0" class="form-control" placeholder="--" value="{{ $tax->tax_rate }}" required>
                                                            <label>TAX BRACKET RATE</label>
                                                        </div>

                                                        <div class="form-floating mb-4" >
                                                            <select name="status" class="form-select" required>
                                                                <option disabled selected>-- select status --</option>
                                                                <option @if($tax->status == 1) selected @endif value="1">ACTIVATE TAX BRACKET</option>
                                                                <option @if($tax->status == 2) selected @endif value="2">DEACTIVATE TAX BRACKET</option>
                                                            </select>
                                                            <label>TAX BRACKET STATUS</label>
                                                        </div>

                                                        <div class="d-flex justify-content-center mt-2">
                                                            <button type="submit" class="btn btn-success">UPDATE TAX BRACKET</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <a class="link text-danger" data-bs-toggle="tooltip" data-bs-placement="left" title="Delete Tax Rate" onclick="return confirm('Are you sure you want to delete this tax bracket?')" href="{{ route('accounts.deleteTaxBracket', $tax->tax_bracket_id) }}"><span class="fa-regular fa-trash-can"></span></a>
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
