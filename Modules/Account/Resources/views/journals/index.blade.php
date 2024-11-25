@extends('account::layouts.default')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/2.1.5/css/dataTables.dataTables.css">
@section('account::dashboard')
    <div class="card">
        <div class="card-header">
            <div class="row flex-between-center">
                <div class="col-6 col-sm-auto d-flex align-items-center pe-0">
                    <h5 class="fs-9 mb-0 text-nowrap py-0 py-xl-0">System Journals </h5>
                </div>
                <div class="col-6 col-sm-auto ms-auto text-end ps-0">
                    <div id="table-simple-pagination-replace-element">
{{--                        @if(auth()->user()->role_id == 7)--}}
                        <a class="btn btn-falcon-default btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#staticBackdrop"><span class="fas fa-plus" data-fa-transform="shrink-3 down-2"></span><span class="d-none d-sm-inline-block ms-1">New Journal</span></a>
{{--                        @endif--}}
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
                                    <h5 class="mb-1" id="staticBackdropLabel">ADD NEW JOURNAL</h5>
                                </div>
                                <div class="p-4">
                                    <div class="row">
                                        <form method="POST" action="{{ route('accounts.storeSystemJournals') }}">
                                            @csrf
                                            <div class="form-floating mb-4">
                                                <input type="text" step="0.01" name="journal" min="0" class="form-control" placeholder="--" required>
                                                <label>JOURNAL NAME</label>
                                            </div>

                                            <div class="form-floating mb-4">
                                                <select class="form-select" name="effect" required>
                                                    <option value="" selected disabled>-- select effect --</option>
                                                    <option value="1">APPRECIATES LEDGER</option>
                                                    <option value="2">DEPRECIATES LEDGER</option>
                                                </select>
                                                <label> EFFECT ON LEDGER</label>
                                            </div>

                                            <div class="form-floating mb-4">
                                                <select class="form-select" name="status">
                                                    <option value="" selected disabled>-- select status --</option>
                                                    <option value="1">ACTIVE</option>
                                                    <option value="2">INACTIVE</option>
                                                </select>
                                                <label> JOURNAL STATUS</label>
                                            </div>

                                            <div class="d-flex justify-content-center mt-2">
                                                <button type="submit" class="btn btn-md col-md-7 btn-success">SAVE JOURNAL</button>
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
                            <th>JOURNAL NAME</th>
                            <th>EFFECT ON LEDGER</th>
                            <th>JOURNAL STATUS</th>
                            <th>ACTION</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($journals as $journal)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $journal->journal_name }}</td>
                                <td>{{ $journal->effect == 2 ? 'DECREASES' : 'INCREASES' }}</td>
                                <td>{!! $journal->status == 1 ? '<span class="badge bg-success">ACTIVE</span>' : '<span class="badge bg-danger">INACTIVE</span>' !!}</td>
                                <td>
                                    <a class="link text-info" data-bs-toggle="modal" title="Edit Journal" href="#" data-bs-target="#staticBackdropEditAccount-{{ $journal->journal_id }}"><span class="fa-regular fa-pen-to-square"></span></a>
                                    <div class="modal fade" id="staticBackdropEditAccount-{{ $journal->journal_id }}" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h6 class="modal-title" id="staticBackdropLabel">UPDATE {{ $journal->journal_name }}</h6>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <form method="POST" action="{{ route('accounts.updateSystemJournals', $journal->journal_id) }}">
                                                        @csrf
                                                        <div class="form-floating mb-4">
                                                            <input type="text" step="0.01" name="journal" value="{{ $journal->journal_name }}" class="form-control" placeholder="--" required>
                                                            <label>JOURNAL NAME</label>
                                                        </div>

                                                        <div class="form-floating mb-4">
                                                            <select class="form-select" name="effect" required>
                                                                <option @if($journal->effect == 1) selected @endif value="1">APPRECIATES LEDGER</option>
                                                                <option @if($journal->effect == 2) selected @endif value="2">DEPRECIATES LEDGER</option>
                                                            </select>
                                                            <label> EFFECT ON LEDGER</label>
                                                        </div>

                                                        <div class="form-floating mb-4">
                                                            <select class="form-select" name="status">
                                                                <option @if($journal->status == 1) selected @endif value="1">ACTIVE</option>
                                                                <option @if($journal->status == 2) selected @endif value="2">INACTIVE</option>
                                                            </select>
                                                            <label> JOURNAL STATUS</label>
                                                        </div>

                                                        <div class="d-flex justify-content-center mt-2">
                                                            <button type="submit" class="btn btn-md col-md-7 btn-success">UPDATE JOURNAL</button>
                                                        </div>
                                                    </form>
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
