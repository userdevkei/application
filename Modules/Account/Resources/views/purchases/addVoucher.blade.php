@extends('account::layouts.default')
<script src="https://code.jquery.com/jquery-3.7.1.js"></script>
<style>
    .invoice-container {
        background-color: #fff;
        border: 1px solid #ddd;
        border-radius: 10px;
        padding: 20px;
        margin: 20px;
    }
    .invoice-header, .invoice-footer {
        /*background-color: #e9ecef;*/
        padding: 10px;
        border-radius: 5px;
    }
    .invoice-table th, .invoice-table td {
        vertical-align: middle;
    }
    .summary-section {
        border: 1px solid #ddd;
        border-radius: 10px;
        padding: 10px;
        background-color: #fff;
    }

    #invoiceNumber {
        background-color: #fff !important;
        border: none !important;
    }
</style>
@section('account::dashboard')
    <div class="card">
        <div class="card-header">
            <div class="row flex-between-center">
                <div class="col-6 col-sm-auto d-flex align-items-center pe-0">
                    <h5 class="fs-9 mb-0 text-nowrap py-0 py-xl-0">Add Payment Voucher</h5>
                </div>
                {{--                <div class="col-6 col-sm-auto ms-auto text-end ps-0">--}}
                {{--                    <div id="table-simple-pagination-replace-element">--}}
                {{--                        <a class="btn btn-falcon-default btn-sm" href="{{ route('accounts.addInvoice') }}"><span class="fas fa-plus" data-fa-transform="shrink-3 down-2"></span><span class="d-none d-sm-inline-block ms-1">New Invoice</span></a>--}}
                {{--                    </div>--}}
                {{--                </div>--}}
            </div>
        </div>
        <div class="card-body overflow-hidden p-lg-3">
            <div class="row align-items-center">
                <div class="tab-pane preview-tab-pane active" role="tabpanel" aria-labelledby="tab-dom-c3976e0e-38db-410e-861a-36d04a3a7494" id="dom-c3976e0e-38db-410e-861a-36d04a3a7494">
                    <form method="POST" action="{{ route('accounts.storePurchaseInvoice') }}">
                        @csrf
                        <div class="container-fluid invoice-container">
                            <div class="invoice-header mb-4">
                                <div class="d-flex justify-content-end mb-3">
                                    <div class="mx-2" style="width: 15vw !important;">
                                        <label>TRANSACTION FINANCIAL YEAR</label>
                                        <select class="form-select financialYear js-choice" id="financialYear" name="financialYear" required>
                                            <option disabled selected value="">-- select FY --</option>
                                            @foreach($financialYears as $fy)
                                                <option value="{{ $fy['financial_year_id'] }}">{{ $fy['financial_year'] }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="row row-cols-sm-3 g-1">
                                    <div>
                                        <label for="invoiceNumber" class="form-label fw-bold" style="font-size: 85% !important;">SUPPLIER/PRODUCER</label>
                                        <select class="form-select js-choice" name="accountId" id="accountId" required>
                                            <option disabled value="" selected>-- select account to bill --</option>
                                            @foreach($debtors as $account)
                                                <option value="{{ $account->client_account_id }}">{{ $account->client_account_name }} - {{ $account->currency_symbol }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div>
                                        <label for="invoiceDate" class="form-label fs-6 fw-bold" style="font-size: 85% !important;">INVOICE DATE </label>
                                        <input type="date" name="invoiceDate" class="form-control" id="invoiceDate" value="{{ \Carbon\Carbon::now()->format('Y-m-d') }}" style="height: 62% !important;">
                                    </div>
                                    <div>
                                        <label for="invoiceDate" class="form-label fs-6 fw-bold" style="font-size: 85% !important;">INVOICE DUE DATE</label>
                                        <input type="date" name="dueDate" class="form-control" id="dueDate" value="{{ \Carbon\Carbon::now()->addDay(30)->format('Y-m-d') }}" style="height: 62% !important;">
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex justify-content-start mb-3" style="white-space: nowrap !important;">
                                <div class="mb-3">
                                    <div class="btn-group">
                                        <select id="itemSelect" class="form-control form-select col-9">
                                            <option disabled value="" selected>-- select item to add --</option>
                                        </select>
                                        <button id="addItemButton" class="btn btn-sm btn-success col-3" style="width: 20vw !important;">Add Item</button>
                                    </div>
                                </div>
                            </div>
                            <div class="table-responsive mb-3">
                                <table class="table table-striped invoice-table table-bordered">
                                    <thead>
                                    <th>#</th>
                                    <th>Item</th>
                                    <th>Description</th>
                                    <th>Quantity</th>
                                    <th>Rate</th>
                                    <th>Tax</th>
                                    <th>Amount</th>
                                    <th>Action</th>
                                    </thead>
                                    <tbody id="invoiceItems">
                                    <!-- Rows for items will be added dynamically -->
                                    </tbody>
                                </table>
                            </div>

                            <div class="row row-cols-sm-2 g-1">
                                <div class="mb-3">
                                    <label for="invoiceNumber" class="form-label">Invoice Number</label>
                                    <input type="text" class="form-control" name="invoiceNumber" id="invoice_number">
                                    <span id="invoice_status"></span>
                                </div>
                                <div class="mb-3">
                                    <label for="customerMessage" class="form-label">Customer Message</label>
                                    <input type="text" class="form-control" id="customerMessage" name="customerMessage" required>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="summary-section mt-1" style="font-size: 85% !important;">
                                        <h6 class="my-2"><u>ACCOUNT SUMMARY</u></h6>
                                        <p id="account" style="font-style: italic !important; font-family: Cambria,cursive;">ACCOUNT CATEGORY</p>
                                        <p id="subAccount" style="font-style: italic !important; font-family: Cambria,cursive;">ACCOUNT SUBCATEGORY</p>
                                        <p id="chartAccount" style="font-style: italic !important; font-family: Cambria,cursive;">CHART ACCOUNT</p>
                                        <p id="accountName" style="font-style: italic !important; font-family: Cambria,cursive;">ACCOUNT NAME</p>
                                        <p id="accountCurrency" style="font-style: italic !important; font-family: Cambria,cursive;">ACCOUNTS CURRENCY</p>
                                        <p id="openingDate" style="font-style: italic !important; font-family: Cambria,cursive;">ACCOUNT OPENING DATE</p>
                                    </div>
                                </div>
                                <div class="col-md-6 text-end">
                                    <div class="mb-3">
                                        <select class="form-select taxBracket" id="taxBracket" name="taxBracket">
                                            @foreach($taxes->where('effect', 1) as $tax)
                                                <option selected value="{{ $tax->tax_bracket_id }}" data-rate="{{ $tax->tax_rate }}" data-name="{{ $tax->tax_name }}">{{ $tax->tax_name }} {{ $tax->tax_rate }}%</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <p>Subtotal</p>
                                        <p id="total">0.00</p>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <p>Total VAT</p>
                                        <p id="totalVat">0.00</p>
                                        <input type="hidden" id="taxTotal" value="" name="totalTax">
                                    </div>
                                    <div class="mb-3">
                                        <select class="form-select withHoldingTax" name="withHoldingTax" id="withHoldingTax">
                                            <option value="" data-rate="0"> NOT APPLICABLE</option>
                                            @foreach($taxes->where('effect', 2) as $tax)
                                                <option selected value="{{ $tax->tax_bracket_id }}" data-rate="{{ $tax->tax_rate }}" data-name="{{ $tax->tax_name }}">{{ $tax->tax_name }} {{ $tax->tax_rate }}%</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <p>Total Withholding Tax</p>
                                        <p id="totalWithholdingTax">0.00</p>
                                        <input type="hidden" id="taxWithholdingTotal" value="" name="withHoldingTaxTotal">
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <p>Amount Due</p>
                                        <p id="totalDue"> </p>
                                        <input type="hidden" id="amountDue" name="amountDue" value="">
                                    </div>
                                    <div class="d-flex justify-content-end">
                                        <button class="btn btn-danger me-2 save">Save & Close</button>
                                        <button class="btn btn-success save">Save & New</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
<script src="https://code.jquery.com/jquery-3.7.1.js"></script>
<script src="https://cdn.datatables.net/2.1.5/js/dataTables.js"></script>
<script>
    $(document).ready(function () {
        $('#accountId').on('change', function () {
            var account = $('#accountId').val();

            $.ajax({
                type: 'GET',
                url: '{{ route('accounts.fetchAccount') }}',
                data: { account },
                success: function (data) {

                    $('#account').text(data.account_number+' - '+data.account_name)
                    $('#subAccount').text(data.sub_category_number+' - '+data.sub_account_name)
                    $('#chartAccount').text(data.chart_number+' - '+data.chart_name)
                    $('#accountName').text(data.client_account_number+' - '+data.client_account_name)
                    $('#accountCurrency').text(data.currency_name +' ('+data.currency_symbol+')')

                    var openingDate = new Date(data.opening_date * 1000); // assuming opening_date is in seconds
                    var formattedDate = openingDate.getFullYear() + '-' +
                        ('0' + (openingDate.getMonth() + 1)).slice(-2) + '-' +
                        ('0' + openingDate.getDate()).slice(-2) + ' ' +
                        ('0' + openingDate.getHours()).slice(-2) + ':' +
                        ('0' + openingDate.getMinutes()).slice(-2) + ':' +
                        ('0' + openingDate.getSeconds()).slice(-2);

                    $('#openingDate').text(formattedDate)
                }
            });

            $.ajax({
                type: 'GET',
                url: '{{ route('accounts.getExpenseItems') }}',
                data: { account },
                success: function (response) {

                    $('#itemSelect').find('option:not(:first)').remove();
                    $('#invoiceItems').empty();
                    // $('#itemSelect').append(' <option disabled value="" selected>-- select item to add --</option>');
                    response.forEach(function (item) {
                        $('#itemSelect').append(
                            $('<option>', {
                                value: item.client_account_id,
                                'data-account': item.client_account_id,
                                'data-name': item.client_account_name,
                                'data-rate': item.rate,
                                text: item.client_account_name + ' - ' + item.currency_symbol
                            })
                        );
                    });

                }
            });

        });

        let itemIndex = 1;

        $('#addItemButton').on('click', function() {
            event.preventDefault();
            const selectedItem = $('#itemSelect').find('option:selected');
            const itemName = selectedItem.data('name');
            const perCost = selectedItem.data('rate');
            const clientId = selectedItem.data('account');
            const itemId = selectedItem.val();

            const itemRate = (perCost === '' || typeof perCost === 'undefined') ? 0 : perCost;

            if (!itemId) {
                alert('Please select an item to add.');
                return;
            }

            const row = `
                    <tr>
                        <td>${itemIndex}</td>
                        <input type="hidden" value="${clientId}" name="items[${itemIndex}][client_id]">
                        <td contenteditable="true">${itemName}</td>
                        <td><input type="text" class="form-control" name="items[${itemIndex}][description]" /></td>
                        <td><input type="number" min="1" step="0.1" class="form-control quantity" name="items[${itemIndex}][quantity]" value="1" required /></td>
                        <td><input type="number" min="1" step="0.1" class="form-control rate" name="items[${itemIndex}][rate]" value="${perCost}" required /></td>
                        <td><select class="form-control form-control-sm vatable" name="items[${itemIndex}][vatable]"><option value="0">Non-Vatable</option><option value="1">Vatable</option></select></td>
                        <td class="amount">${itemRate}</td>
                        <td><a class="btn-link text-danger btn-sm removeItemButton" title="remove item">
                            <span class="btn-inner">
                                 <svg class="icon-18" width="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" stroke="currentColor">
                                       <path d="M19.3248 9.46826C19.3248 9.46826 18.7818 16.2033 18.4668 19.0403C18.3168 20.3953 17.4798 21.1893 16.1088 21.2143C13.4998 21.2613 10.8878 21.2643 8.27979 21.2093C6.96079 21.1823 6.13779 20.3783 5.99079 19.0473C5.67379 16.1853 5.13379 9.46826 5.13379 9.46826"
                                                      stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                                       <path d="M20.708 6.23975H3.75" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                                       <path d="M17.4406 6.23973C16.6556 6.23973 15.9796 5.68473 15.8256 4.91573L15.5826 3.69973C15.4326 3.13873 14.9246 2.75073 14.3456 2.75073H10.1126C9.53358 2.75073 9.02558 3.13873 8.87558 3.69973L8.63258 4.91573C8.47858 5.68473 7.80258 6.23973 7.01758 6.23973" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                                 </svg>
                            </span>
                        </a> </td>
                    </tr>
                `;

            $('#invoiceItems').append(row);
            itemIndex++;
            calculateTotal();
        });

        $(document).on('click', '.removeItemButton', function() {
            $(this).closest('tr').remove();
            updateSerialNumbers();
            calculateTotal();
        });

        $(document).on('input', '.quantity, .rate, .vatable, .taxBracket, .withHoldingTax', function() {
            const row = $(this).closest('tr');
            const quantity = row.find('.quantity').val();
            const rate = row.find('.rate').val();
            const amount = quantity * rate;
            row.find('.amount').text(amount.toFixed(2));
            calculateTotal();
        });

        function updateSerialNumbers() {
            itemIndex = 1;
            $('#invoiceItems tr').each(function() {
                $(this).find('td:first').text(itemIndex);
                itemIndex++;
            });
        }

        function calculateTotal() {
            let total = 0;
            let tax = 0;
            let amountDue = 0;
            $('#invoiceItems tr').each(function() {
                const amount = parseFloat($(this).find('.amount').text());
                let taxRate = parseFloat($('#taxBracket').find('option:selected').data('rate'));
                total += amount;

                const totalTax = parseFloat($(this).find('.vatable').val());
                tax += totalTax * amount * parseFloat(taxRate)/100;


            });
            // Update total in the UI, if needed

            const withHolding = total * parseFloat($('#withHoldingTax').find('option:selected').data('rate'))/100;
            amountDue = total + tax - withHolding;

            // console.log(withHolding)

            $('#total').text(total.toFixed(2));
            $('#amountDue').val(amountDue.toFixed(2));
            $('#totalVat').text(tax.toFixed(2))
            $('#taxTotal').val(tax.toFixed(2))
            $('#taxWithholdingTotal').val(withHolding.toFixed(2));
            $('#totalWithholdingTax').text(withHolding.toFixed(2));
            $('#totalDue').text(amountDue.toFixed(2));
            $('#withHoldingTaxTotal').text(withHolding.toFixed(2));
        }

        $('#invoice_number').on('input', function() {
            let invoiceNumber = $(this).val();

            if (invoiceNumber.length > 0) {
                $.ajax({
                    url: '{{ route('accounts.fetchPurchaseInvNumber') }}',
                    method: 'GET',
                    data: {invoiceNumber},
                    success: function(response) {
                        console.log(response)
                        if (response.exists) {
                            $('#invoice_status').text('Invoice number already exists').css('color', 'red');
                            $('.save').prop('disabled', true);
                        } else {
                            $('#invoice_status').text('Invoice number is available').css('color', 'green');
                            $('.save').prop('disabled', false);
                        }
                    },
                    error: function() {
                        $('#invoice_status').text('Error checking invoice number').css('color', 'orange');
                    }
                });
            } else {
                $('#invoice_status').text('');
            }
        });

    });
</script>
