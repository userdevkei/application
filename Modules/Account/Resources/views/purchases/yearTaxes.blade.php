@extends('account::layouts.default')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/2.1.5/css/dataTables.dataTables.css">
@section('account::dashboard')
<div class="card">
    <div class="card-header">
        <div class="row flex-between-center">
            <div class="col-6 col-sm-auto d-flex align-items-center pe-0">
                <h5 class="fs-9 mb-0 text-nowrap py-0 py-xl-0">Year Tax Categories </h5>
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
                        <th>TAX NAME</th>
                        <th>TAX RATE</th>
                        <th>ACTION</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($taxes as $tax)
                    <tr>
                        <td> {{ $loop->iteration }} </td>
                        <td> {{ $tax[0]['tax_name'] }} </td>
                        <td> {{ $tax[0]['tax_rate'] }}% </td>
                        <td>
                            <a class="link text-dark" data-bs-toggle="tooltip" data-bs-placement="left" title="View Tax Statement" href="{{ route('accounts.purchaseTaxStatement', base64_encode($id.':'.$tax[0]['tax_bracket_id'])) }}"> <span class="fas fa-folder-open"> </span> </a>
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