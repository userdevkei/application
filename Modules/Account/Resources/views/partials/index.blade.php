<div class="card">
    <div class="card-body overflow-hidden p-lg-6">
        <div class="row align-items-center">
{{--            <div class="row ps-lg-4 my-5 text-center text-lg-start">--}}
                <div class="row g-3 mb-3">
                    <div class="col-xxl-6 col-lg-12">
                        <div class="card h-100">
                            <div class="bg-holder bg-card" style="background-image:url({{ url('assets/img/icons/spot-illustrations/corner-3.png') }});">
                            </div>
                            <!--/.bg-holder-->

                            <div class="card-header z-1">
                                <h5 class="text-primary">Welcome {{ auth()->user()->user->surname }}! </h5>
                                <h6 class="text-600">Here are some quick links for you to start </h6>
                            </div>
                            <div class="card-body z-1">
                                <div class="row g-2 h-100 align-items-end">
                                    <div class="col-sm-6 col-md-5">
                                        <div class="d-flex position-relative">
                                            <div class="icon-item icon-item-sm border rounded-3 shadow-none me-2"><span class="fa fa-user text-primary"></span></div>
                                            <div class="flex-1"><a class="stretched-link text-800" href="#!">
                                                    <h6 class="mb-0">Role</h6>
                                                </a>
                                                <p class="mb-0 fs-11 text-500 fs-sm text-lowercase">{{ auth()->user()->role->role_name }}</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-md-5">
                                        <div class="d-flex position-relative">
                                            <div class="icon-item icon-item-sm border rounded-3 shadow-none me-2"><span class="fas fa-calendar text-warning"></span></div>
                                            <div class="flex-1"><a class="stretched-link text-800" href="#!">
                                                    <h6 class="mb-0">Active Since</h6>
                                                </a>
                                                <p class="mb-0 fs-11 text-500">{{ Carbon\Carbon::parse(auth()->user()->created_at)->format('D, d/m/y H:i') }} </p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-md-5">
                                        <div class="d-flex position-relative">
                                            <div class="icon-item icon-item-sm border rounded-3 shadow-none me-2"><span class="fa-solid fa-location-pin-lock text-success"></span></div>
                                            <div class="flex-1"><a class="stretched-link text-800" href="#!">
                                                    <h6 class="mb-0">Location</h6>
                                                </a>
                                                <p class="mb-0 fs-11 text-500">{{ auth()->user()->station->location->location_name }}</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-md-5">
                                        <div class="d-flex position-relative">
                                            <div class="icon-item icon-item-sm border rounded-3 shadow-none me-2"><span class="fa-solid fa-warehouse text-danger"></span></div>
                                            <div class="flex-1"><a class="stretched-link text-800" href="#!">
                                                    <h6 class="mb-0">Warehouse</h6>
                                                </a>
                                                <p class="mb-0 fs-11 text-500">{{ auth()->user()->station->station_name }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xxl-3 col-md-6">
                        <div class="card h-100">
                            <div class="card-header d-flex flex-between-center">
                                <h5 class="mb-0">Income</h5><a class="btn btn-link btn-sm px-0" href="#!">Report<span class="fas fa-chevron-right ms-1 fs-11"> </span></a>
                            </div>
                            <div class="card-body">
                                <p class="fs-10 text-600">Invoices</p>
                                <div class="progress mb-3 rounded-pill" style="height: 6px;" role="progressbar" aria-valuenow="43.72" aria-valuemin="0" aria-valuemax="100">
                                    <div class="progress-bar bg-progress-gradient rounded-pill" style="width: 75%"></div>
                                </div>
                                <p class="mb-0 text-primary">Raised Today</p>
                                <p class="mb-0 fs-11 text-500">Jan 1st to 30th</p>
                            </div>
                        </div>
                    </div>

                    <div class="col-xxl-3 col-md-6">
                        <div class="card h-100">
                            <div class="card-header d-flex flex-between-center">
                                <h5 class="mb-0">Expenses</h5><a class="btn btn-link btn-sm px-0" href="#!">Report<span class="fas fa-chevron-right ms-1 fs-11"> </span></a>
                            </div>
                            <div class="card-body">
                                <p class="fs-10 text-600">Purchases</p>
                                <div class="progress mb-3 rounded-pill" style="height: 6px;" role="progressbar" aria-valuenow="43.72" aria-valuemin="0" aria-valuemax="100">
                                    <div class="progress-bar bg-progress-gradient rounded-pill" style="width: 75%"></div>
                                </div>
                                <p class="mb-0 text-primary">Payments Today</p>
                                <p class="mb-0 fs-11 text-500">Jan 1st to 30th</p>
                            </div>
                        </div>
                    </div>

                </div>
            <div class="row g-3 mb-3">
                <div class="col-xxl-6 col-lg-12">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="row g-3 mb-3">
                                <div class="col-sm-6">
                                    <div class="card overflow-hidden" style="min-width: 12rem">
                                        <div class="bg-holder bg-card" style="background-image:url({{ url('assets/img/icons/spot-illustrations/corner-1.png') }});">
                                        </div>
                                        <!--/.bg-holder-->

                                        <div class="card-body position-relative">
                                            <h6>Accounts<span class="badge badge-subtle-warning rounded-pill ms-2">-0.23%</span></h6>
                                            <div class="display-4 fs-5 mb-2 fw-normal font-sans-serif text-warning" data-countup='{"endValue":{{ $accounts->count() }},"decimalPlaces":2}'>0</div><a class="fw-semi-bold fs-10 text-nowrap" href="#">All Accounts<span class="fas fa-angle-right ms-1" data-fa-transform="down-1"></span></a>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="card overflow-hidden" style="min-width: 12rem">
                                        <div class="bg-holder bg-card" style="background-image:url({{ url('assets/img/icons/spot-illustrations/corner-2.png') }});">
                                        </div>
                                        <!--/.bg-holder-->

                                        <div class="card-body position-relative">
                                            <h6>Group Ledgers<span class="badge badge-subtle-info rounded-pill ms-2">0.0%</span></h6>
                                            <div class="display-4 fs-5 mb-2 fw-normal font-sans-serif text-info" data-countup='{"endValue":{{ $group->count() }},"decimalPlaces":2}'>0</div><a class="fw-semi-bold fs-10 text-nowrap" href="#">All Grouped Ledgers<span class="fas fa-angle-right ms-1" data-fa-transform="down-1"></span></a>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-sm-6">
                                    <div class="card overflow-hidden" style="min-width: 12rem">
                                        <div class="bg-holder bg-card" style="background-image:url({{ url('assets/img/icons/spot-illustrations/corner-1.png') }});">
                                        </div>
                                        <!--/.bg-holder-->
                                        <div class="card-body position-relative">
                                            <h6>Income Streams<span class="badge badge-subtle-warning rounded-pill ms-2">-0.23%</span></h6>
                                            <div class="display-4 fs-5 mb-2 fw-normal font-sans-serif text-warning" data-countup='{"endValue":{{ $incomes->count() }},"decimalPlaces":2}'>0</div><a class="fw-semi-bold fs-10 text-nowrap" href="#">All Income Streams<span class="fas fa-angle-right ms-1" data-fa-transform="down-1"></span></a>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="card overflow-hidden" style="min-width: 12rem">
                                        <div class="bg-holder bg-card" style="background-image:url({{ url('assets/img/icons/spot-illustrations/corner-2.png') }});">
                                        </div>
                                        <!--/.bg-holder-->

                                        <div class="card-body position-relative">
                                            <h6>Expenses<span class="badge badge-subtle-info rounded-pill ms-2">0.0%</span></h6>
                                            <div class="display-4 fs-5 mb-2 fw-normal font-sans-serif text-info" data-countup='{"endValue":{{ $expenses->count() }},"decimalPlaces":2}'>0</div><a class="fw-semi-bold fs-10 text-nowrap" href="#">All Expenses<span class="fas fa-angle-right ms-1" data-fa-transform="down-1"></span></a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-12">
                            <div class="card">
                                <div class="card-header pb-0">
                                    <div class="row flex-between-center">
                                        <div class="col-auto">
                                            <h6 class="mb-0">Report for this week</h6>
                                        </div>
                                        <div class="col-auto d-flex">
                                            <div class="btn btn-sm d-flex align-items-center p-0 me-3 shadow-none" id="echart-bar-report-for-this-week-option-1"><span class="fas fa-circle text-primary fs-11 me-1"></span><span class="text">Income </span></div>
                                            <div class="btn btn-sm d-flex align-items-center p-0 shadow-none" id="echart-bar-report-for-this-week-option-2"><span class="fas fa-circle text-300 fs-11 me-1"></span><span class="text">Expenses</span></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body py-0">
                                    <!-- Find the JS file for the following chart at: src/js/charts/echarts/report-for-this-week.js-->
                                    <div class="echart-bar-report-for-this-week" data-echart-responsive="true" data-chart="{&quot;option1&quot;:&quot;echart-bar-report-for-this-week-option-1&quot;,&quot;option2&quot;:&quot;echart-bar-report-for-this-week-option-2&quot;}"> </div>
                                </div>
{{--                                <div class="card-footer bg-body-tertiary p-0"><a class="btn btn-sm btn-link d-block w-100 py-2" href="#!">See all projects<span class="fas fa-chevron-right ms-1 fs-11"></span></a></div>--}}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xxl-6 col-lg-6">
                    <div class="card h-100">
{{--                        <div class="card-header d-flex flex-between-center">--}}
{{--                            <h6 class="mb-0">Project Statistics</h6>--}}
{{--                            <div class="dropdown font-sans-serif btn-reveal-trigger">--}}
{{--                                <button class="btn btn-link text-600 btn-sm dropdown-toggle dropdown-caret-none btn-reveal" type="button" id="dropdown-project-statistics" data-bs-toggle="dropdown" data-boundary="viewport" aria-haspopup="true" aria-expanded="false"><span class="fas fa-ellipsis-h fs-11"></span></button>--}}
{{--                                <div class="dropdown-menu dropdown-menu-end border py-2" aria-labelledby="dropdown-project-statistics"><a class="dropdown-item" href="#!">View</a><a class="dropdown-item" href="#!">Export</a>--}}
{{--                                    <div class="dropdown-divider"></div><a class="dropdown-item text-danger" href="#!">Remove</a>--}}
{{--                                </div>--}}
{{--                            </div>--}}
{{--                        </div>--}}
                        <div class="card-body pt-0">
                            <div class="row mb-2">
{{--                                <div class="col-6 border-end border-200">--}}
{{--                                    <h4 class="mb-0">{{ $clients->count() }} </h4>--}}
{{--                                    <p class="fs-10 text-600 mb-0">All Clients</p>--}}
{{--                                </div>--}}
{{--                                <div class="col-3 border-end text-center border-200">--}}
{{--                                    <h4 class="fs-9 mb-0">{{ $stocks->count() }}</h4>--}}
{{--                                    <p class="fs-10 text-600 mb-0">Clients (stocked) </p>--}}
{{--                                </div>--}}
{{--                                <div class="col-3 text-center">--}}
{{--                                    <h4 class="fs-9 mb-0">{{ $clients->count() - $stocks->count() }} </h4>--}}
{{--                                    <p class="fs-10 text-600 mb-0">Clients (out of stock)</p>--}}
{{--                                </div>--}}
                            </div>
{{--                            <p class="fs-10 mb-2 mt-3">Top Clients By Stock</p>--}}

{{--                            <div class="d-flex justify-content-between">--}}
{{--                                <h6 class="col-6">Client Name</h6>--}}
{{--                                <h6 class="col-3">Packages </h6>--}}
{{--                                <h6 class="col-3">Net Weight</h6>--}}
{{--                            </div>--}}

{{--                            <?php $tops = $stocks->take(6); ?>--}}
{{--                        @foreach($tops as $index => $stock)--}}
{{--                                <div class="d-flex flex-between-center rounded-3 bg-body-tertiary p-3 mb-2">--}}
{{--                                        <h6 class="mb-0 col-6">--}}
{{--                                            @if($index == 0)--}}
{{--                                                <span class="fas fa-circle fs-10 me-3 text-primary"></span>--}}
{{--                                            @elseif($index == 1)--}}
{{--                                                <span class="fas fa-circle fs-10 me-3 text-warning"> </span>--}}
{{--                                            @elseif($index == 2)--}}
{{--                                                <span class="fas fa-circle fs-10 me-3 text-secondary"></span>--}}
{{--                                            @elseif($index == 3)--}}
{{--                                                <span class="fas fa-circle fs-10 me-3 text-info"></span>--}}
{{--                                            @elseif($index == 4)--}}
{{--                                                <span class="fas fa-circle fs-10 me-3 text-dark"></span>--}}
{{--                                            @elseif($index == 5)--}}
{{--                                                <span class="fas fa-circle fs-10 me-3 text-danger"></span>--}}
{{--                                            @elseif($index == 6)--}}
{{--                                                <span class="fas fa-circle fs-10 me-3 text-success"></span>--}}
{{--                                            @endif--}}
{{--                                            {{ $stock->client_name }}</h6>--}}
{{--                                        <a class="fs-11 text-600 mb-0 col-3" href="#!">{{ $stock->packages }}</a>--}}
{{--                                        <a class="fs-11 text-600 mb-0 col-3" href="#!">{{ number_format($stock->net_weight, 2, '.', ',') }}</a>--}}
{{--                                </div>--}}
{{--                            @endforeach--}}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
