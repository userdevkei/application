@extends('account::layouts.default')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/2.1.5/css/dataTables.dataTables.css">
@section('account::dashboard')
    <div class="card">
        <div class="card-header">
            <div class="row flex-between-center">
                <div class="col-6 col-sm-auto d-flex align-items-center pe-0">
                    <h5 class="fs-9 mb-0 text-nowrap py-0 py-xl-0">Ledgers </h5>
                </div>
                <div class="col-6 col-sm-auto ms-auto text-end ps-0">
                    <div id="table-simple-pagination-replace-element">
                        <a class="btn btn-falcon-default btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#staticBackdrop"><span class="fas fa-plus" data-fa-transform="shrink-3 down-2"></span><span class="d-none d-sm-inline-block ms-1">New Ledger</span></a>
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
                                    <h5 class="mb-1" id="staticBackdropLabel">ADD NEW LEDGER</h5>
                                </div>
                                <div class="p-4">
                                    <div class="row">
                                        <form method="POST" action="{{ route('accounts.addChartAccount') }}">
                                            @csrf
                                            <div class="form-floating mb-4">
                                                <input type="text" class="form-control" name="account_name" placeholder="--">
                                                <label>ACCOUNT NAME</label>
                                            </div>

                                            <div class="form-floating mb-4" >
                                                <select name="account_category" id="category" class="form-select js-choice" required>
                                                    <option disabled selected>-- select account category --</option>
                                                    @foreach($categories as $category)
                                                        <option value="{{ $category->sub_account_id }}">{{ $category->sub_account_name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div class="mb-4">
                                                <textarea type="text" name="description" class="form-control" rows="3" placeholder="ACCOUNT DESCRIPTION"></textarea>
                                                <label> </label>
                                            </div>

                                            <div class="d-flex justify-content-center mt-2">
                                                <button type="submit" class="btn btn-success">SAVE LEDGER</button>
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
                            <th>ACCOUNT CODE</th>
                            <th>ACCOUNT NAME</th>
                            <th>ACCOUNT CATEGORY</th>
                            <th>SUBCATEGORY</th>
                            <th>DESCRIPTION</th>
                            <th>STATUS</th>
                            <th>ACTION</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($accounts as $account)
                            <tr>
                                <td> {{ $loop->iteration }} </td>
                                <td> {{ $account->chart_number }} </td>
                                <td> {{ $account->chart_name }} </td>
                                <td> {{ $account->account_name }} </td>
                                <td> {{ $account->sub_account_name }} </td>
                                <td> {{ $account->description }} </td>
                                <td> {!! $account->status == 1 ? '<span class="badge text-bg-success"> ACTIVE </span>' : '<span class="badge text-bg-danger"> DISABLED </span>' !!} </td>
                                <td>
                                    <a class="text-info" data-bs-toggle="modal" title="Edit Account Information" href="#" data-bs-target="#staticBackdropEditAccount-{{ $account->chart_id }}"><span class="fa-regular fa-pen-to-square"></span></a>
                                    <div class="modal fade" id="staticBackdropEditAccount-{{ $account->chart_id }}" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h6 class="modal-title" id="staticBackdropLabel">UPDATE {{ $account->chart_number }} {{ $account->chart_name }} DETAILS</h6>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <form method="POST" action="{{ route('accounts.updateChartAccount', $account->chart_id) }}">
                                                        @csrf
                                                        <div class="form-floating mb-4">
                                                            <input type="text" name="account_name" class="form-control" value="{{ $account->chart_name }}" placeholder="--" required>
                                                            <label>ACCOUNT NAME</label>
                                                        </div>

                                                        <div class="form-floating mb-4" >
                                                            <select name="account_category" id="category_{{ $account->chart_id }}" class="form-select choices" required>
                                                                <option disabled selected>-- select account category --</option>
                                                                @foreach($categories as $category)
                                                                    <option @if($account->sub_account_id == $category->sub_account_id) selected @endif value="{{ $category->sub_account_id }}">{{ $category->sub_account_name }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>

                                                        <div class="form-floating mb-4" >
                                                            <select name="status" class="form-select choices" required>
                                                                <option disabled selected>-- select status --</option>
                                                                <option @if($account->status == 1) selected @endif value="1">ACTIVATE ACCOUNT</option>
                                                                <option @if($account->status == 2) selected @endif value="2">DISABLE ACCOUNT</option>
                                                            </select>
                                                        </div>

                                                        <div class="mb-4">
                                                            <textarea type="text" name="description" class="form-control" rows="3" placeholder="ACCOUNT DESCRIPTION">{{ $account->description }}</textarea>
                                                            <label> </label>
                                                        </div>

                                                        <div class="d-flex justify-content-center mt-2">
                                                            <button type="submit" class="btn btn-success">UPDATE CHART OF ACCOUNT</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <a class="link text-danger" data-bs-toggle="tooltip" data-bs-placement="left" title="Delete Ledger Account" onclick="return confirm('Are you sure you want to delete this account?')" href="{{ route('accounts.deleteChartAccount', $account->chart_id) }}"><span class="fa-regular fa-trash-can"></span></a>
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

        $('#category_{{ $account->chart_id }}').on('change', function () {

            var accountCat = $(this).val();

            $.ajax({
                'type': 'GET',
                'url' : '{{ route('accounts.getAccountType') }}',
                'data' : { accountCat},
                success: function (data) {
                    $('#account_type_{{ $account->chart_id }}').empty();

                    // Append new options based on account_type
                    if (data.account_type == 1) {
                        $('#account_type_{{ $account->chart_id }}').append('<option value="" disabled selected>-- select account type --</option>');
                        $('#account_type_{{ $account->chart_id }}').append('<option value="1">CREDIT ACCOUNT</option>');
                    } else if (data.account_type == 2) {
                        $('#account_type_{{ $account->chart_id }}').append('<option value="" disabled selected>-- select account type --</option>');
                        $('#account_type_{{ $account->chart_id }}').append('<option value="2">DEBIT ACCOUNT</option>');
                    } else {
                        $('#account_type_{{ $account->chart_id }}').append('<option value="" disabled selected>-- select account type --</option>');
                        $('#account_type_{{ $account->chart_id }}').append('<option value="1">CREDIT ACCOUNT</option>');
                        $('#account_type_{{ $account->chart_id }}').append('<option value="2">DEBIT ACCOUNT</option>');
                    }
                }
            });

        });

    });
</script>

