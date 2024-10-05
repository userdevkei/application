@extends('admin::layouts.default')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/2.1.5/css/dataTables.dataTables.css">
@section('admin::dashboard')
    <div class="card">
        <div class="card-header">
            <div class="row flex-between-center">
                <div class="col-6 col-sm-auto d-flex align-items-center pe-0">
                    <h5 class="fs-9 mb-0 text-nowrap py-0 py-xl-0">Teas In Stock </h5>
                </div>
                <div class="col-6 col-sm-auto ms-auto text-end ps-0">
                        <div id="table-simple-pagination-replace-element">
                        </div>
                </div>
            </div>
        </div>
        <div class="card-body overflow-hidden p-sm-3">
            <div class="row align-items-center">
                <div class="tab-pane preview-tab-pane active" role="tabpanel" aria-labelledby="tab-dom-c3976e0e-38db-410e-861a-36d04a3a7494" id="dom-c3976e0e-38db-410e-861a-36d04a3a7494">
                    <table class="table mb-0 table-bordered table-striped" id="datatable">
                        <thead class="bg-200">
                        <tr>
                            <th>#</th>
                            <th>DEL. TYPE</th>
                            <th>Client Name</th>
                            <th>Order #</th>
                            <th>Inv #</th>
                            <th>Lot #</th>
                            <th>Garden Name</th>
                            <th>Grade</th>
                            <th>Packages</th>
                            <th>Weight</th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody>
                            @foreach($stocks as $stock)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $stock->delivery_type == 1 ? 'DO ENTRY' : 'DIRECT DEL' }}</td>
                                    <td>{{ $stock->client_name }}</td>
                                    <td>{{ $stock->order_number }}</td>
                                    <td>{{ $stock->invoice_number }}</td>
                                    <td>{{ $stock->lot_number }}</td>
                                    <td>{{ $stock->garden_name }}</td>
                                    <td>{{ $stock->grade_name }}</td>
                                    <td>{{ $stock->total_pallets }}</td>
                                    <td>{{ $stock->total_weight }}</td>
                                    <td nowrap="">
                                        <a class="link text-danger mx-1" onclick="return confirm('Are you sure you want to restore Invoice Number {{ $stock->invoice_number }}?')" href="{{ route('admin.restoreArchivedTea', $stock->stock_id) }}" data-bs-toggle="tooltip" data-bs-placement="left" title="Restore archived tea"><span class="fa-solid fa-trash-can-arrow-up"></span></a>
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

    function clearDate() {
        document.getElementById('todayDate').value = ''; // Clear the date input value
    }

    function clearDate() {
        document.getElementById('monthAgo').value = ''; // Clear the date input value
    }

    $(document).ready(function() {
        $('#datatable').DataTable( {
            order: [ 0, 'asc' ],
            pageLength: 50
        } );

        var currentDate = new Date(); // Get the current date and time

// Adjust the current date and time for a timezone offset of +3 hours
        currentDate.setHours(currentDate.getHours() + 3);

// Format the adjusted date and time string for input type datetime-local
        var formattedDateTime = currentDate.toISOString().slice(0, -8); // Removes the milliseconds and timezone offset

// Set the value of the datetime-local input element
        document.getElementById('todayDate').value = formattedDateTime;



        var today = new Date();

// Subtract one month from today's date
        var oneMonthAgo = new Date(today);
        oneMonthAgo.setMonth(today.getMonth() - 1);

// Format the date as YYYY-MM-DD
        var year = oneMonthAgo.getFullYear();
        var month = (oneMonthAgo.getMonth() + 1).toString().padStart(2, '0');
        var day = oneMonthAgo.getDate().toString().padStart(2, '0');
        var hours = oneMonthAgo.getHours().toString().padStart(2, '0');
        var minutes = oneMonthAgo.getMinutes().toString().padStart(2, '0');
        var seconds = oneMonthAgo.getSeconds().toString().padStart(2, '0');

        var formattedDateTime = `${year}-${month}-${day}T${hours}:${minutes}`;

// Set the value of the input field to the date one month ago
        document.getElementById("monthAgo").value = formattedDateTime;

    } );
</script>
