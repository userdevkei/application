@extends('clerk::layouts.default')
<style>
    /* Disable the up and down arrows */
    input[type="number"]::-webkit-inner-spin-button,
    input[type="number"]::-webkit-outer-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }

    input[type="number"] {
        -moz-appearance: textfield; /* Firefox */
    }

    .outputWeight, .packagesNumber, .perPackage, .packagesNumberEx, .sweepings, .noOfBlends {
        border-top: none !important;
        border-left: none !important;
        border-right: none !important;
        border-color: forestgreen !important;
    }

    .tag-container {
        display: flex;
        flex-wrap: wrap;
        gap: 5px;
    }

    .tag {
        display: flex;
        align-items: center;
        background-color: forestgreen;
        color: white;
        padding: 5px 10px;
        border-radius: 20px;
        font-size: 14px;
    }

    .tag .remove-button {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 16px;
        height: 16px;
        margin-left: 5px;
        color: yellow !important;
        background: forestgreen;
        border: none !important;
        font-size: 16px;
        /*font-style: italic;*/
        cursor: pointer;
    }

    .input-container {
        position: relative;
        width: 100%;
    }

    /* .input-container input {
        width: 32%;
        !*padding: 10px;*!
        !*border: 1px solid #ccc;*!
        !*border-radius: 5px;*!
        !*font-size: 14px;*!
        !*margin-bottom: 5px;*!
        !*height: 70% !important;*!
    } */

    .input-container .add-button {
        position: absolute;
        top: 50%;
        right: 10px;
        transform: translateY(-50%);
        background-color: #007bff;
        color: #fff;
        padding: 2px 8px;
        border: none;
        border-radius: 50%;
        cursor: pointer;
        font-size: 14px;
        outline: none;
    }

    #editableSelect, #idSelect {
        min-width: 14vw !important; /* Set width to 100% */
        min-height: 2.5vh !important;
        box-sizing: border-box; /* Include padding and border in the width calculation */
        /* Add any other styling you need */
    }
</style>
@section('clerk::dashboard')
    <div class="card">
        <div class="card-header">
            <div class="row flex-between-center">
                <div class="col-6 col-sm-auto d-flex align-items-center pe-0">
                    <h5 class="fs-9 mb-0 text-nowrap py-0 py-xl-0">Update Blend Number {{ $bs->blend_number }} ({{ $bs->client_name }}) </h5>
                </div>
            </div>
        </div>
        <div class="card-body overflow-hidden p-lg-3">
            <div class="row align-items-center">
                <div class="tab-pane preview-tab-pane active" role="tabpanel" aria-labelledby="tab-dom-c3976e0e-38db-410e-861a-36d04a3a7494" id="dom-c3976e0e-38db-410e-861a-36d04a3a7494">
                    <form method="POST" action="{{ route('clerk.updateBlendSheetDetails', $bs->blend_id) }}">
                        @csrf
                            <div class="row row-cols-sm-2 g-2">
                                <fieldset class="border py-2">
                                    <legend class="float-none fw-bolder w-auto p-2 h6">BLEND OUTTURN </legend>
                                    <div class="row mb-2 mx-2">
                                        <div class="col-md-4">
                                            <span class="h6 " style="font-size: 90% !important;">INPUT WEIGHT</span>
                                        </div>
                                        <div class="col-md-2">
                                            <span class="fs-sm"><span id="inputWeight">{{ $bs->input_weight }}</span> </span>
                                        </div>
                                        <div class="col-md-4">
                                            <span class="h6 " style="font-size: 90% !important;">INPUT PACKAGES</span>
                                        </div>
                                        <div class="col-md-2">
                                            <span class="fs-sm"><span id="inputWeight">{{ $bs->input_packages }}</span> </span>
                                        </div>
                                    </div>
                                    <div class="mb-2 mx-2 my-3">
                                        <span class="h6 fs-xs  m-2" style="font-size: 95% !important;">BLENDED TEA PACKAGING </span>
                                    </div>

                                    <div class="row mb-4 mx-2 mt-3">
                                        <div class="col-md-6">
                                            <span class="h6 fs-xs  m-2" style="font-size: 95% !important;">BLEND PHASES INVOLVED</span>
                                        </div>
                                        <div class="col-md-6">
                                            <input type="number" class="form-control noOfBlends" name="noOfBlends" id="blendNumbers">
                                        </div>
                                    </div>

                                    <div class="mb-4 mx-2 mt-1" id="appendList"></div>

                                    <div class="row mb-2 mx-2">
                                        <div class="col-md-6">
                                            <span class="h6 fs-xs " style="font-size: 95% !important;">SHIPMENT TOTAL WEIGHT</span>
                                        </div>
                                        <div class="col-md-6"><span class=" text-primary" id="shipmentTotal"></span></div>
                                    </div>

                                    <div class="row mb-2 mx-2 my-3">
                                        <div class="col-md-6">
                                            <span class="h6 fs-xs  m-2" style="font-size: 95% !important;">BLEND BALANCE PACKAGES </span>
                                        </div>

                                        <div class="col-md-6">
                                            <span class="fs-sm">
                                            <input type="number" min="0" inputmode="numeric" class="form-control packagesNumberEx" id="packagesNumberEx" step="0.1" placeholder="NO. OF PACKAGES">
                                            </span>
                                        </div>
                                    </div>

                                    <div id="inputFields-"></div>

                                    <div class="row mb-3 mx-2 my-2" id="extraBlendBalance"></div>

                                    <div class="row mb-2 mx-2">
                                        <div class="col-md-6">
                                            <span class="h6 fs-xs " style="font-size: 95% !important;">TOTAL BLEND BALANCE </span>
                                        </div>
                                        <div class="col-md-6"><span class=" text-info" id="totalBlendBalance"></span></div>
                                    </div>

                                </fieldset>

                                <fieldset class="border py-2">
                                    <legend class="float-none w-auto fw-bolder p-2 h6">BLEND OUTTURN </legend>
                                    <div class="row mb-2 mx-2 my-3">
                                        <div class="col-md-6">
                                            <span class="h6 fs-xs  m-2" style="font-size: 95% !important;">SWEEPINGS</span>
                                        </div>

                                        <div class="col-md-6">
                                            <span class="fs-sm">
                                                <input type="number" min="0" inputmode="numeric" class="form-control sweepings" id="sweepings" step="0.1" name="sweepings" placeholder="WEIGHT OF SWEEPINGS" required>
                                            </span>
                                        </div>
                                    </div>

                                    <div class="row mb-2 mx-2 my-3">
                                        <div class="col-md-6">
                                            <span class="h6 fs-xs  m-2" style="font-size: 95% !important;">CYCLONE/DUST</span>
                                        </div>

                                        <div class="col-md-6">
                                            <span class="fs-sm">
                                                <input type="number" min="0" inputmode="numeric" class="form-control sweepings" id="cDust" step="0.1" name="cDust" placeholder="WEIGHT OF CYCLONE/DUST" required>
                                            </span>
                                        </div>
                                    </div>


                                    <div class="row mb-2 mx-2 my-3">
                                        <div class="col-md-6">
                                            <span class="h6 fs-xs  m-2" style="font-size: 95% !important;">FIBRE</span>
                                        </div>

                                        <div class="col-md-6">
                                            <span class="fs-sm">
                                                <input type="number" min="0" inputmode="numeric" class="form-control sweepings" id="fibre" step="0.1" name="fibre" placeholder="WEIGHT OF FIBRE" required>
                                            </span>
                                        </div>
                                    </div>

                                    <div class="row mb-4 mx-2 my-3">
                                        <div class="col-md-6">
                                            <span class="h6 fs-xs  m-2" style="font-size: 95% !important;">SIEVED DUST</span>
                                        </div>

                                        <div class="col-md-6">
                                            <span class="fs-sm">
                                                <input type="number" min="0" inputmode="numeric" class="form-control sweepings" id="bDust" step="0.1" name="bDust" placeholder="WEIGHT OF BROWN DUST" required>
                                            </span>
                                        </div>
                                    </div>

                                    <div class="row mb-4 mx-2 my-3">
                                        <div class="col-md-6">
                                            <span class="h6 fs-xs  m-2" style="font-size: 95% !important;">TARE WEIGHT VARIANCE</span>
                                        </div>

                                        <div class="col-md-6">
                                            <span class="fs-sm">
                                            <input type="number" min="0" inputmode="numeric" class="form-control tareVariance" id="tVariance" step="0.01" name="tareVariance" value="0"  placeholder="TARE VARIANCE" required>
                                            </span>
                                        </div>
                                        <input type="hidden" id="totalPackages">
                                    </div>

                                    <div class="row mb-2 mx-2">
                                        <div class="col-md-6">
                                            <span class="h6 fs-xs " style="font-size: 95% !important;">OTHER BALANCES</span>
                                        </div>
                                        <div class="col-md-6"><span class=" text-danger" id="otherBlendBalance"></span></div>
                                    </div>

                                    <div class="row mb-2 mx-2">
                                        <div class="col-md-6">
                                            <span class="h6 " style="font-size: 90% !important;">PRODUCTION WEIGHT <sup class="text-danger">*</sup></span>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="col-md-6"><span class=" text-success" id="outputWeight"></span></div>
                                        </div>
                                    </div>

                                    <div class="row mb-2 mx-2">
                                        <div class="col-md-4"><span class="h6 fs-xs " style="font-size: 85% !important;">GAIN/LOSS</span> </div>
                                        <div class="col-md-2"><span class="text-primary" id="gainWeight"></span></div>
                                        <div class="col-md-3"><span class="h6 fs-xs " style="font-size: 85% !important;">GAIN/LOSS(%) </span> </div>
                                        <div class="col-md-3"> <span class="text-info" id="gainPercent"></span> </div>
                                    </div>
                                </fieldset>
                            </div>

                            <fieldset class="border p-2">
                                <legend class="float-none w-auto p-2 h6 fw-bolder">LOGISTICS</legend>

                                <div class="row row-cols-sm-4 g-1">
                                    <div class="mb-2">
                                        <label class="my-1 fs-xs " style="font-size: 85% !important;">PACKAGE TARE <sup class="text-danger">*</sup></label>
                                        @php $increment = 0.1; @endphp
                                        <select class="form-select js-choice" name="packageTare" required data-options='{"removeItemButton":true,"placeholder":true}'>
                                            <option disabled selected value="">-- select package tare --</option>
                                            @for ($i = 0.1; $i <= 1; $i += $increment)
                                                <option value="{{ $i }}">{{ number_format($i, 1) }} Kgs</option>
                                            @endfor
                                        </select>
                                    </div>
                                    <div class="mb-2">
                                        <label class="my-1 fs-xs " style="font-size: 85% !important;">CONTAINER TARE <sup class="text-danger">*</sup></label>
                                        <input type="number" step="0.01" name="tare" class="form-control form-control-lg" placeholder="--" required>
                                    </div>
                                    <div class="mb-2">
                                        <label class="my-1 fs-xs " style="font-size: 85% !important;">CLEARING AGENT <sup class="text-danger">*</sup></label>
                                        <select name="agentId" class="form-select form-select-lg js-choice" data-options='{"removeItemButton":true,"placeholder":true}' required>
                                            <option selected disabled value="">-- select clearing agent -- </option>
                                            @foreach($agents as $agent)
                                                <option value="{{ $agent->agent_id }}">{{ $agent->agent_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="mb-2">
                                        <label class="my-1 fs-xs " style="font-size: 85% !important;">SEAL NUMBER <sup class="text-danger">*</sup></label>
                                        <input type="text" name="seal" class="form-control form-select-lg" placeholder="--" required>
                                    </div>

                                    <div class="mb-2">
                                        <label class="my-1 fs-xs " style="font-size: 85% !important;">CARGO ESCORTED? <sup class="text-danger">*</sup></label>
                                        <select name="escortId" class="form-select form-select-lg js-choice" data-options='{"removeItemButton":true,"placeholder":true}' required>
                                            <option selected disabled value="">-- select option -- </option>
                                            <option value="1">YES</option>
                                            <option value="2">NO</option>
                                        </select>
                                    </div>

                                    <div class="mb-2">
                                        <label class="my-1 fs-xs " style="font-size: 85% !important;">BLEND DATE <sup class="text-danger">*</sup></label>
                                        <input type="date" name="blendDate" class="form-control form-control-lg" placeholder="--" required>
                                    </div>

                                    <div class="mb-2">
                                        <label class="my-1 fs-xs " style="font-size: 85% !important;">TRANSPORTER <sup class="text-danger">*</sup></label>
                                        <select name="transporter" class="form-select form-select-lg js-choice" data-options='{"removeItemButton":true,"placeholder":true}' required>
                                            <option selected disabled value=" ">-- select transporter -- </option>
                                            @foreach($transporters as $transporter)
                                                <option value="{{ $transporter->transporter_id }}">{{ $transporter->transporter_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="mb-2">
                                        <label class="my-1 fs-xs " style="font-size: 85% !important;">VEHICLE REGISTRATION <sup class="text-danger">*</sup></label><br>
                                        <input class="form-control form-control-lg" data-options='{"removeItemButton":true,"placeholder":true}' name="registration" id="editableSelect" type="text" list="optionsList" placeholder="-- plate number --" required>
                                        <datalist id="optionsList">
                                            @foreach($registrations as $registration => $transporter)
                                                <option value="{{ $registration }}">{{ $registration }} </option>
                                            @endforeach
                                        </datalist>
                                    </div>

                                    <div class="mb-2">
                                        <label class="my-1 fs-xs " style="font-size: 85% !important;">DRIVER'S ID NUMBER <sup class="text-danger">*</sup></label> <br>
                                        <input id="idSelect" type="text" list="idList" name="idNumber" class="form-control form-control-lg idSelect" placeholder="-- driver's ID Number --" required>
                                        <datalist id="idList">
                                            @foreach($users as $user)
                                                <option value="{{ $user->id_number }}">{{ $user->id_number }}</option>
                                            @endforeach
                                        </datalist>
                                    </div>
                                    <div class="mb-2">
                                        <label class="my-1 fs-xs " style="font-size: 85% !important;">DRIVER'S NAME <sup class="text-danger">*</sup> </label>
                                        <input type="text" name="driverName" id="driverName" class="form-control form-control-lg driverName" required>
                                    </div>

                                    <div class="mb-4">
                                        <label class="my-1 fs-xs " style="font-size: 85% !important;">DRIVER'S PHONE NUMBER <sup class="text-danger">*</sup></label>
                                        <input type="text" name="driverPhone" id="driverPhone" class="form-control form-control-lg driverPhone" >
                                    </div>

                                </div>
{{--                                <input type="hidden" class="hiddenTagsInput" name="containerNumbers" required>--}}
                                <label class="my-1 fs-xs " style="font-size: 85% !important;">CONTAINER NUMBER(S) <sup class="text-danger">*</sup></label><br>
                                <div class="tag-container"></div>
                                <div class="input-container mt-2 mb-4">
                                    <input type="text" class="form-control form-control-lg js-choice tag" name="containerNumbers" placeholder="Type a container number and press enter">
                                </div>
                            </fieldset>

                        <fieldset class="border py-2">
                            <legend class="float-none w-auto p-2 h6 fw-bolder">SUPERVISORS DETAILS</legend>
                            <div class="row row-cols-3 g-2 mx-1">
                                <div class="form-floating mb-2">
                                    <input type="text" name="supervisor[1][name]" class="form-control" placeholder="--" required/>
                                    <label>MACHINE OPERATOR</label>
                                </div>

                                <div class="form-floating mb-2">
                                    <input type="text" name="supervisor[2][name]" class="form-control" placeholder="--" required/>
                                    <label>BLEND SUPERVISOR</label>
                                </div>

                                <div class="form-floating mb-2">
                                    <input type="text" name="supervisor[3][name]" class="form-control" placeholder="--" required/>
                                    <label>THIRD PARTY INSPECTION CLERK</label>
                                </div>
                            </div>
                        </fieldset>

                        <fieldset class="border py-2">
                            <legend class="float-none w-auto p-2 h6 fw-bolder">NEW MATERIALS ISSUED</legend>
                            <div class="row row-cols-3 g-2 mx-1">
                                <div class="form-floating mb-2">
                                    <input type="number" name="new[1][name]" class="form-control" placeholder="--" required/>
                                    <label>PAPER SACK</label>
                                </div>

                                <div class="form-floating mb-2">
                                    <input type="number" name="new[2][name]" class="form-control" placeholder="--" required/>
                                    <label>POLY BAGS</label>
                                </div>

                                <div class="form-floating mb-2">
                                    <input type="number" name="new[3][name]" class="form-control" placeholder="--" required/>
                                    <label>SMALL POUCH</label>
                                </div>

                                <div class="form-floating mb-2">
                                    <input type="number" name="new[4][name]" class="form-control" placeholder="--" required/>
                                    <label>PALLETS</label>
                                </div>

                                <div class="form-floating mb-2">
                                    <input type="number" name="new[5][name]" class="form-control" placeholder="--" required/>
                                    <label>GUNNY BAGS</label>
                                </div>
                            </div>
                        </fieldset>

                        <fieldset class="border py-2">
                            <legend class="float-none w-auto p-2 h6 fw-bolder">USED MATERIALS RETRIVALS</legend>
                            <div class="row row-cols-4 g-2 mx-1">
                                <div class="form-floating mb-2">
                                    <input type="number" name="inUse[1][name]" class="form-control" placeholder="--" required/>
                                    <label>PAPER SACK</label>
                                </div>

                                <div class="form-floating mb-2">
                                    <input type="number" name="inUse[2][name]" class="form-control" placeholder="--" required/>
                                    <label>POLY BAGS</label>
                                </div>

                                <div class="form-floating mb-2">
                                    <input type="number" name="inUse[3][name]" class="form-control" placeholder="--" required/>
                                    <label>PALLETS</label>
                                </div>

                                <div class="form-floating mb-2">
                                    <input type="number" name="inUse[4][name]" class="form-control" placeholder="--" required/>
                                    <label>GUNNY BAGS</label>
                                </div>

                                <div class="form-floating mb-2">
                                    <input type="number" name="damaged[1][name]" class="form-control" placeholder="--" required/>
                                    <label>PAPER SACK (DAMAGED)</label>
                                </div>

                                <div class="form-floating mb-2">
                                    <input type="number" name="damaged[2][name]" class="form-control" placeholder="--" required/>
                                    <label>POLY BAGS (DAMAGED)</label>
                                </div>

                                <div class="form-floating mb-2">
                                    <input type="number" name="damaged[3][name]" class="form-control" placeholder="--" required/>
                                    <label>PALLETS (DAMAGED)</label>
                                </div>

                                <div class="form-floating mb-2">
                                    <input type="number" name="damaged[4][name]" class="form-control" placeholder="--" required/>
                                    <label>GUNNY BAGS (DAMAGED)</label>
                                </div>
                            </div>
                        </fieldset>

                        <div class="d-flex justify-content-center mt-4">
                            <button type="submit" onclick="return confirm('Make sure all required fields are filled in. Are you sure you want to continue with submission?')" class="btn btn-success">UPDATE BLEND SHEET DETAILS</button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>

    <script>

        $(document).ready(function() {

            $('[id^=blendNumbers]').change(function () {
                appendList();
                calculateGainLoss();
            });

            $('[id^=packagesNumberEx]').change(function () {
                calculateExtraBlendBalance()
                calculateGainLoss()
            })

            $('[id^=sweepings], [id^=fibre], [id^=bDust],  [id^=cDust], [id^=blendNumbers], [id^=tVariance]').change(function () {
                calculateOutputBalance()
                calculateGainLoss()
            })

            function appendList() {
                var fieldsBlendNumbers = parseFloat($('#blendNumbers').val());
                var totalBalance = 0; // Initialize total balance
                var li = ''; // Initialize an empty string to store list items
                for (var i = 1; i <= fieldsBlendNumbers; i++) {
                    // Concatenate each list item to the 'li' variable
                    li += '<div class="row row-cols-sm-2 g-2">' +
                        '<div class="form-floating mb-1 d-flex align-items-center">' +
                        '<input type="number" name="blend['+ i +'][packages]" id="packagesNumber'+ i +'" class="form-control packagesNumber" placeholder="--" required>' +
                        '<label>NO. OF PACKAGES</label>' +
                        '</div>'+
                        '<div class="form-floating mb-1 d-flex align-items-center">' +
                        '<input type="number" name="blend['+ i +'][weight]"  id="perPackage'+ i +'" class="form-control ms-2 perPackage" placeholder="--" required>' +
                        '<label>WEIGHT PER PACKAGE</label>' +
                        '</div>' +
                        '</div>';
                }
                // Set the HTML content of the inputFields container to the concatenated 'li' variable
                $('#appendList').html(li);

                // Attach event listener to number of packages and weight per package fields
                $('[id^=packagesNumber], [id^=perPackage]').on('change', function () {
                    calculateBlendBalance();
                });
            }

            // Function to calculate gain/loss weight and percentage
            function calculateGainLoss() {
                var totalShipped = parseFloat($('#shipmentTotal').text());
                var balances = parseFloat($('#totalBlendBalance').text());
                var otherBalances = parseFloat($('#otherBlendBalance').text());
                var totalOutputWeight = totalShipped + balances + otherBalances
                $('#outputWeight').text(isNaN(parseFloat(totalOutputWeight)) ? '' : parseFloat(totalOutputWeight).toFixed(2));
                var inputWeight = parseFloat($('#inputWeight').text());
                var gainWeight = totalOutputWeight - inputWeight;
                var gainPercent = (gainWeight / inputWeight) * 100;
                $('#gainWeight').empty().text(isNaN(parseFloat(gainWeight)) ? '' : gainWeight.toFixed(2) + ' kgs');
                $('#gainPercent').empty().text(isNaN(parseFloat(gainPercent)) ? '' : gainPercent.toFixed(2) + ' %');
            }

            function calculateBlendBalance() {
                var blendBalance = 0;
                var allPackages = 0;

                // Iterate through all packagesNumber elements, excluding #packagesNumberEx
                $('[id^=packagesNumber]').not('#packagesNumberEx').each(function () {
                    var numberPackages = parseFloat($(this).val()) || 0; // Fallback to 0 if NaN
                    var phaseIndex = $(this).attr('id').replace('packagesNumber', '');
                    var perPackage = parseFloat($('#perPackage' + phaseIndex).val()) || 0; // Fallback to 0 if NaN

                    // Add to blendBalance
                    blendBalance += numberPackages * perPackage;
                    allPackages += numberPackages;
                });

                // Enable/disable fields based on blendBalance
                if (blendBalance > 0) {
                    $('#bDust, #cDust, #fibre, #sweepings, #packagesNumberEx, #tVariance').prop('readonly', false);
                } else {
                    $('#bDust, #cDust, #fibre, #sweepings, #packagesNumberEx, #tVariance').prop('readonly', true);
                }

                // Update blend balance and package total in corresponding HTML elements
                $('#totalPackages').val(isNaN(allPackages) ? 0 : allPackages.toFixed(2));
                $('#shipmentTotal').text(isNaN(blendBalance) ? '' : blendBalance.toFixed(2));

                // Recalculate gain/loss after updating blend balance
                calculateGainLoss();
            }


            function calculateExtraBlendBalance() {
                var fields = parseFloat($('#packagesNumberEx').val()) || 0; // Handle NaN by defaulting to 0
                var li = ''; // Initialize an empty string to store list items
                for (var i = 1; i <= fields; i++) {
                    // Concatenate each list item to the 'li' variable
                    li +=   '<div class="row row-cols-sm-2 g-2">' +
                            '<div class="form-floating mb-1 d-flex align-items-center">' +
                            '<input type="number" name="balances['+ i +'][packages]" id="packages'+ i +'" class="form-control ms-2 packagesNumberEx" placeholder="--" required>' +
                            '<label>PACKAGES</label>' +
                            '</div>' +
                            '<div class="form-floating mb-1 d-flex align-items-center">' +
                            '<input type="number" name="balances['+ i +'][weight]" id="weight'+ i +'" class="form-control packagesNumberEx" placeholder="--" required>' +
                            '<label>WEIGHT</label>' +
                            '</div>'+
                            '</div>';
                }
                // Set the HTML content of the inputFields container to the concatenated 'li' variable
                $('#inputFields-').html(li);
                // Attach event listener to number of packages and weight fields
                $('[id^=weight], [id^=packages]').on('change', function () {
                    grandBalance(); // Call grandBalance to update total balance
                    calculateGainLoss(); // Update gain/loss accordingly
                });
            }

            function grandBalance() {
                var grandBlendBalance = 0; // Initialize total balance
                // Iterate through all weight fields
                $('[id^=weight]').each(function () {
                    var weight = parseFloat($(this).val()) || 0; // Fallback to 0 if NaN
                    var index = $(this).attr('id').replace('weight', ''); // Get the index of the current weight input
                    var packages = parseFloat($('#packages' + index).val()) || 0; // Get corresponding packages input by index
                    console.log("Weight: " + weight, "Packages: " + packages); // Debugging
                    // Multiply weight by packages and add to grand total
                    grandBlendBalance += weight * packages;
                });
                console.log("Grand Blend Balance: " + grandBlendBalance); // Debugging
                // Update blend balance in the corresponding HTML element
                $('#totalBlendBalance').text(isNaN(grandBlendBalance) ? '' : grandBlendBalance.toFixed(2));
                // Trigger gain/loss calculation after grand balance is updated
                calculateGainLoss();
            }

            function calculateOutputBalance() {
                var bDust =  $('#bDust').val();
                var cDust =  $('#cDust').val();
                var fibre =  $('#fibre').val();
                var sweepings =  $('#sweepings').val();
                var tVariance = $('#tVariance').val();
                var totalPackages = $('#totalPackages').val();
                console.log(totalPackages)
                var balance = parseFloat(bDust) + parseFloat(cDust) + parseFloat(fibre) + parseFloat(sweepings) + (parseFloat(totalPackages) * tVariance);

                // console.log(balance, tVariance, sweepings, fibre, cDust, bDust, totalPackages)
                $('#otherBlendBalance').text(isNaN(parseFloat(balance)) ? '' : parseFloat(balance).toFixed(2));
            }

            // Initial settings on document ready
            $('[id^=packagesNumber], [id^=perPackage], [id^=packagesNumberEx], [id^=perPackageEx], [id^=sweepings], [id^=fibre], [id^=bDust],  [id^=cDust], [id^=tVariance]').prop('readonly', true);

            // Trigger calculations when any of the specified inputs change
            $('[id^=outputWeight], [id^=packagesNumber], [id^=perPackage], [id^=perPackageEx], [id^=sweepings], [id^=fibre], [id^=bDust],  [id^=cDust], [id^=blendNumbers], [id^=tVariance]').change(function () {
                calculateOutputBalance()
                calculateBlendBalance()
                grandBalance()
                calculateGainLoss()

            });

        });

        $('.idSelect').on('change', function () {

            var idNumber = $(this).val();

            $.ajax({
                url: '{{ route('clerk.fetchIdNumber') }}',
                method: 'GET',
                data: {idNumber},
                dataType: 'json',
                success: function (response) {
                    $('.driverName').val(response.driver_name)
                    $('.driverPhone').val(response.driver_phone)
                },
                error: function (xhr, status, error) {
                    // Function to handle errors
                    console.error('Error:', error);
                    $('#driverName').val('')
                    $('#driverPhone').val('')
                }
            });
        });

        function addTag(form) {
            const tagInput = form.querySelector('.tagsInput');
            const tagText = tagInput.value.trim();
            const tagContainer = form.querySelector('.tag-container');
            const hiddenInput = form.querySelector('.hiddenTagsInput');

            if (tagText !== '') {
                const tag = document.createElement('div');
                tag.classList.add('tag');

                const tagTextSpan = document.createElement('span');
                tagTextSpan.textContent = tagText;

                const removeButton = document.createElement('button');
                removeButton.classList.add('remove-button');
                removeButton.textContent = 'x';
                removeButton.addEventListener('click', function() {
                    tagContainer.removeChild(tag);
                    updateHiddenInputValue(form);
                });

                tag.appendChild(tagTextSpan);
                tag.appendChild(removeButton);

                tagContainer.appendChild(tag);
                tagInput.value = '';

                updateHiddenInputValue(form);
            }

            // Prevent form submission on Enter key press
            tagInput.addEventListener('keydown', function(event) {
                if (event.key === 'Enter') {
                    event.preventDefault();
                    addTag(form);
                }
            });
        }

        function updateHiddenInputValue(form) {
            const tagContainer = form.querySelector('.tag-container');
            const tags = tagContainer.querySelectorAll('.tag');
            const hiddenInput = form.querySelector('.hiddenTagsInput');

            let tagValues = [];
            tags.forEach(tag => {
                tagValues.push(tag.querySelector('span').textContent);
            });

            hiddenInput.value = tagValues.join(',');
        }

        document.querySelectorAll('.tagForm').forEach(form => {
            /* form.addEventListener('submit', function(event) {
                 event.preventDefault();
                 addTag(form);
             });*/

            form.querySelector('.tagsInput').addEventListener('keydown', function(event) {
                if (event.key === 'Enter') {
                    event.preventDefault();
                    addTag(form);
                }
            });
        });
    </script>
@endsection
