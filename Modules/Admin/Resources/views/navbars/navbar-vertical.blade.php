<!-- ---- navbar-vertical starts------------ -->
        <nav class="navbar navbar-light navbar-vertical navbar-expand-xl">
          <script>
            var navbarStyle = localStorage.getItem("navbarStyle");
            if (navbarStyle && navbarStyle !== 'transparent') {
              document.querySelector('.navbar-vertical').classList.add(`navbar-${navbarStyle}`);
            }
          </script>
          <div class="d-flex align-items-center">
            <div class="toggle-icon-wrapper">
              <button class="btn navbar-toggler-humburger-icon navbar-vertical-toggle" data-bs-toggle="tooltip" data-bs-placement="left" title="Toggle Navigation"><span class="navbar-toggle-icon"><span class="toggle-line"></span></span></button>

            </div><a class="navbar-brand" href="{{ route('dashboard') }}">
              <div class="d-flex align-items-center py-3"><img class="me-2" src="{{ url('assets/img/favicons/icon.png') }}" alt="" width="40" /><span class="font-sans-serif fs-sm"></span>
              </div>
            </a>
          </div>
          <div class="collapse navbar-collapse" id="navbarVerticalCollapse">
            <div class="navbar-vertical-content scrollbar">
              <ul class="navbar-nav flex-column mb-3" id="navbarVerticalNav">
                <li class="nav-item">
                  <!-- label-->
                  <div class="row navbar-vertical-label-wrapper mt-3 mb-2">
                    <div class="col-auto navbar-vertical-label">Tea Deliveries
                    </div>
                    <div class="col ps-0">
                      <hr class="mb-0 navbar-vertical-divider" />
                    </div>
                  </div>
                  <!-- parent pages--><a class="nav-link" href="{{ route('admin.viewDeliveryOrders') }}" role="button" data-bs-toggle="" aria-expanded="false">
                    <div class="d-flex align-items-center"><span class="nav-link-icon"><span class="fas fa-shipping-fast"></span></span><span class="nav-link-text ps-1">Delivery Orders</span>
                    </div>
                  </a>
                  <!-- parent pages--><a class="nav-link" href="{{ route('admin.viewLLIs') }}" role="button" data-bs-toggle="" aria-expanded="false">
                    <div class="d-flex align-items-center"><span class="nav-link-icon"><span class="fas fa-dolly"></span></span><span class="nav-link-text ps-1">Tea  Collections </span>
                    </div>
                  </a>

                    <!-- parent pages--><a class="nav-link" href="{{ route('admin.viewDirectDeliveries') }}" role="button" data-bs-toggle="" aria-expanded="false">
                        <div class="d-flex align-items-center"><span class="nav-link-icon"><span class="fas fa-truck"></span></span><span class="nav-link-text ps-1">Direct Deliveries </span>
                        </div>
                    </a>
                </li>
                <li class="nav-item">
                  <!-- label-->
                  <div class="row navbar-vertical-label-wrapper mt-3 mb-2">
                    <div class="col-auto navbar-vertical-label">Stock Position
                    </div>
                    <div class="col ps-0">
                      <hr class="mb-0 navbar-vertical-divider" />
                    </div>
                  </div>
                  <!-- parent pages--><a class="nav-link" href="{{ route('admin.viewDeliveries') }}" role="button" data-bs-toggle="" aria-expanded="false">
                    <div class="d-flex align-items-center"><span class="nav-link-icon"><span class="fas fa-chart-line"></span></span><span class="nav-link-text ps-1">Teas in Stock</span>
                    </div>
                  </a>
                  <!-- parent pages--><a class="nav-link" href="{{ route('admin.viewBlendBalances') }}" role="button" data-bs-toggle="" aria-expanded="false">
                    <div class="d-flex align-items-center"><span class="nav-link-icon"><span class="fa fa-balance-scale"></span></span><span class="nav-link-text ps-1">Blend Balances</span>
                    </div>
                  </a>
                    <!-- parent pages--><a class="nav-link" href="{{ route('admin.teaSamplesRequest') }}" role="button" data-bs-toggle="" aria-expanded="false">
                    <div class="d-flex align-items-center"><span class="nav-link-icon"><span class="fa-brands fa-creative-commons-sampling"></span></span><span class="nav-link-text ps-1">Tea Samples</span>
                    </div>
                  </a>
                    <!-- parent pages--><a class="nav-link" href="{{ route('admin.allArchivedTeas') }}" role="button" data-bs-toggle="" aria-expanded="false">
                    <div class="d-flex align-items-center"><span class="nav-link-icon"><span class="fa-regular fa-folder-open"></span></span><span class="nav-link-text ps-1">Archived Teas</span>
                    </div>
                  </a>
                </li>
                <li class="nav-item">
                  <!-- label-->
                  <div class="row navbar-vertical-label-wrapper mt-3 mb-2">
                    <div class="col-auto navbar-vertical-label">Tea Transfers
                    </div>
                    <div class="col ps-0">
                      <hr class="mb-0 navbar-vertical-divider" />
                    </div>
                  </div>
                    <!-- parent pages--><a class="nav-link" href="{{ route('admin.viewInternalTransfers') }}" role="button" data-bs-toggle="" aria-expanded="false">
                        <div class="d-flex align-items-center"><span class="nav-link-icon"><span class="fa-solid fa-arrow-down-up-across-line"></span></span><span class="nav-link-text ps-1">Internal </span>
                        </div>
                    </a>
                    <!-- parent pages--><a class="nav-link" href="{{ route('admin.viewExternalTransfers') }}" role="button" data-bs-toggle="" aria-expanded="false">
                        <div class="d-flex align-items-center"><span class="nav-link-icon"><span class="fa fa-exchange-alt" aria-hidden="true"></span></span><span class="nav-link-text ps-1">External </span>
                        </div>
                    </a>
                </li>
                <li class="nav-item">
                  <!-- label-->
                  <div class="row navbar-vertical-label-wrapper mt-3 mb-2">
                    <div class="col-auto navbar-vertical-label">Tea Shipment
                    </div>
                    <div class="col ps-0">
                      <hr class="mb-0 navbar-vertical-divider" />
                    </div>
                  </div>
                  <!-- parent pages--><a class="nav-link" href="{{ route('admin.viewShippingInstructions') }}" role="button" data-bs-toggle="" aria-expanded="false">
                    <div class="d-flex align-items-center"><span class="nav-link-icon"><span class="fas fa-rocket"></span></span><span class="nav-link-text ps-1">Straight Line </span>
                    </div>
                  </a>

                    <!-- parent pages--><a class="nav-link" href="{{ route('admin.viewBlendProcessing') }}" role="button" data-bs-toggle="" aria-expanded="false">
                        <div class="d-flex align-items-center"><span class="nav-link-icon"><span class="fa fa-cogs" aria-hidden="true"></span></span><span class="nav-link-text ps-1">Blended Process </span>
                        </div>
                    </a>
                </li>

                  <li class="nav-item">
                      <!-- label-->
                      <div class="row navbar-vertical-label-wrapper mt-3 mb-2">
                          <div class="col-auto navbar-vertical-label">Verified Reports
                          </div>
                          <div class="col ps-0">
                              <hr class="mb-0 navbar-vertical-divider" />
                          </div>
                      </div>
                      <!-- parent pages--><a class="nav-link" href="{{ route('admin.viewReportRequest') }}" role="button" data-bs-toggle="" aria-expanded="false">
                          <div class="d-flex align-items-center"><span class="nav-link-icon"><span class="fa fa-file"></span></span><span class="nav-link-text ps-1">View Reports</span>
                          </div>
                      </a>
                  </li>

                  <li class="nav-item">
                      <!-- label-->
                      <div class="row navbar-vertical-label-wrapper mt-3 mb-2">
                          <div class="col-auto navbar-vertical-label">System & Settings
                          </div>
                          <div class="col ps-0">
                              <hr class="mb-0 navbar-vertical-divider" />
                          </div>
                      </div>
                      <!-- parent pages--><a class="nav-link" href="{{ route('admin.users') }}" role="button" data-bs-toggle="" aria-expanded="false">
                          <div class="d-flex align-items-center"><span class="nav-link-icon"><span class="fa-solid fa-users-gear"></span></span><span class="nav-link-text ps-1">Users</span>
                          </div>
                      </a>
                      <!-- parent pages--><a class="nav-link" href="{{ route('admin.viewClients') }}" role="button" data-bs-toggle="" aria-expanded="false">
                          <div class="d-flex align-items-center"><span class="nav-link-icon"><span class="fa-solid fa-users-between-lines"></span></span><span class="nav-link-text ps-1">Clients</span>
                          </div>
                      </a>
                      <!-- parent pages--><a class="nav-link" href="{{ route('admin.viewClearingAgents') }}" role="button" data-bs-toggle="" aria-expanded="false">
                          <div class="d-flex align-items-center"><span class="nav-link-icon"><span class="fa-solid fa-user-tie"></span></span><span class="nav-link-text ps-1">Clearing Agents</span>
                          </div>
                      </a>
                      <!-- parent pages--><a class="nav-link" href="{{ route('admin.viewBrokers') }}" role="button" data-bs-toggle="" aria-expanded="false">
                          <div class="d-flex align-items-center"><span class="nav-link-icon"><span class="fa-solid fa-handshake-slash"></span></span><span class="nav-link-text ps-1">Brokers</span>
                          </div>
                      </a>
                      <!-- parent pages--><a class="nav-link" href="{{ route('admin.viewGardens') }}" role="button" data-bs-toggle="" aria-expanded="false">
                          <div class="d-flex align-items-center"><span class="nav-link-icon"><span class="fa-solid fa-wheat-awn"></span></span><span class="nav-link-text ps-1">Tea Gardens</span>
                          </div>
                      </a>
                      <!-- parent pages--><a class="nav-link" href="{{ route('admin.viewTeaGrade') }}" role="button" data-bs-toggle="" aria-expanded="false">
                          <div class="d-flex align-items-center"><span class="nav-link-icon"><span class="fa-solid fa-trademark"></span></span><span class="nav-link-text ps-1">Tea Grades</span>
                          </div>
                      </a>
                      <!-- parent pages--><a class="nav-link" href="{{ route('admin.viewWarehouses') }}" role="button" data-bs-toggle="" aria-expanded="false">
                          <div class="d-flex align-items-center"><span class="nav-link-icon"><span class="fa-solid fa-warehouse"></span></span><span class="nav-link-text ps-1">Producer Warehouses</span>
                          </div>
                      </a>
                      <!-- parent pages--><a class="nav-link" href="{{ route('admin.viewTransporters') }}" role="button" data-bs-toggle="" aria-expanded="false">
                          <div class="d-flex align-items-center"><span class="nav-link-icon"><span class="fa-solid fa-truck-arrow-right"></span></span><span class="nav-link-text ps-1">Transporters</span>
                          </div>
                      </a>
                      <!-- parent pages--><a class="nav-link" href="{{ route('admin.viewShippingDestinations') }}" role="button" data-bs-toggle="" aria-expanded="false">
                          <div class="d-flex align-items-center"><span class="nav-link-icon"><span class="fa-solid fa-anchor"></span></span><span class="nav-link-text ps-1">Destinations</span>
                          </div>
                      </a>
                      <!-- parent pages--><a class="nav-link" href="{{ route('admin.viewStations') }}" role="button" data-bs-toggle="" aria-expanded="false">
                          <div class="d-flex align-items-center"><span class="nav-link-icon"><span class="fa-solid fa-boxes-stacked"></span></span><span class="nav-link-text ps-1">PMHL Warehouses</span>
                          </div>
                      </a>
                      <!-- parent pages--><a class="nav-link" href="{{ route('admin.viewOurLocations') }}" role="button" data-bs-toggle="" aria-expanded="false">
                          <div class="d-flex align-items-center"><span class="nav-link-icon"><span class="fa-solid fa-map-location-dot"></span></span><span class="nav-link-text ps-1">PMHL Whs Localities</span>
                          </div>
                      </a>
                      <!-- parent pages--><a class="nav-link mb-4" href="{{ route('admin.viewRoles') }}" role="button" data-bs-toggle="" aria-expanded="false">
                          <div class="d-flex align-items-center"><span class="nav-link-icon"><span class="fa-solid fa-screwdriver-wrench"></span></span><span class="nav-link-text ps-1">User Roles</span>
                          </div>
                      </a>
                  </li>
              </ul>
            </div>
          </div>
        </nav>
        <!-- ----- navbar-vertical end -------------- -->
