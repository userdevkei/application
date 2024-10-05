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
                    <div class="col-auto navbar-vertical-label">Sales
                    </div>
                    <div class="col ps-0">
                      <hr class="mb-0 navbar-vertical-divider" />
                    </div>
                  </div>
                  <!-- parent pages--><a class="nav-link" href="{{ route('accounts.viewInvoices') }}" role="button" data-bs-toggle="" aria-expanded="false">
                    <div class="d-flex align-items-center"><span class="nav-link-icon"><span class="fa-solid fa-bag-shopping"></span></span><span class="nav-link-text ps-1">Sales Invoices</span>
                    </div>
                  </a>
                  <!-- parent pages--><a class="nav-link" href="{{ route('accounts.viewAllTransactions') }}" role="button" data-bs-toggle="" aria-expanded="false">
                    <div class="d-flex align-items-center"><span class="nav-link-icon"><span class="fa-solid fa-receipt"></span></span><span class="nav-link-text ps-1">Accounts Receivable </span>
                    </div>
                  </a>

                    <!-- parent pages--><a class="nav-link" href="{{ route('accounts.salesFYTaxes') }}" role="button" data-bs-toggle="" aria-expanded="false">
                        <div class="d-flex align-items-center"><span class="nav-link-icon"><span class="fa-solid fa-list"></span></span><span class="nav-link-text ps-1">VAT Statements </span>
                        </div>
                    </a>
                </li>
                <li class="nav-item">
                  <!-- label-->
                  <div class="row navbar-vertical-label-wrapper mt-3 mb-2">
                    <div class="col-auto navbar-vertical-label">Purchases
                    </div>
                    <div class="col ps-0">
                      <hr class="mb-0 navbar-vertical-divider" />
                    </div>
                  </div>
                  <!-- parent pages--><a class="nav-link" href="{{ route('accounts.viewPurchases') }}" role="button" data-bs-toggle="" aria-expanded="false">
                    <div class="d-flex align-items-center"><span class="nav-link-icon"><span class="fa-solid fa-cart-shopping"></span></span><span class="nav-link-text ps-1">Purchase Invoices</span>
                    </div>
                  </a>
                 {{-- <!-- parent pages--><a class="nav-link" href="--}}{{--{{ route('clerk.viewBlendBalances') }}--}}{{--" role="button" data-bs-toggle="" aria-expanded="false">
                    <div class="d-flex align-items-center"><span class="nav-link-icon"><span class="fa-solid fa-money-check"></span></span><span class="nav-link-text ps-1">Account Payable</span>
                    </div>
                  </a>--}}
                    <!-- parent pages--><a class="nav-link" href="{{ route('accounts.purchaseFYTaxes') }}" role="button" data-bs-toggle="" aria-expanded="false">
                    <div class="d-flex align-items-center"><span class="nav-link-icon"><span class="fa-solid fa-list-ul"></span></span><span class="nav-link-text ps-1">WHT Statement</span>
                    </div>
                  </a>
                </li>
                <li class="nav-item">
                  <!-- label-->
                  <div class="row navbar-vertical-label-wrapper mt-3 mb-2">
                    <div class="col-auto navbar-vertical-label">Chart of Accounts
                    </div>
                    <div class="col ps-0">
                      <hr class="mb-0 navbar-vertical-divider" />
                    </div>
                  </div>
                    <!-- parent pages--><a class="nav-link" href="{{ route('accounts.viewAccounts') }}" role="button" data-bs-toggle="" aria-expanded="false">
                        <div class="d-flex align-items-center"><span class="nav-link-icon"><span class="fa-solid fa-users-line"></span></span><span class="nav-link-text ps-1">Accounts </span>
                        </div>
                    </a>
                    <!-- parent pages--><a class="nav-link" href="{{ route('accounts.accountSubCategories') }}" role="button" data-bs-toggle="" aria-expanded="false">
                        <div class="d-flex align-items-center"><span class="nav-link-icon"><span class="fa-solid fa-users-between-lines"></span></span><span class="nav-link-text ps-1">Grouped Ledgers </span>
                        </div>
                    </a>
                    <!-- parent pages--><a class="nav-link" href="{{ route('accounts.viewChartAccounts') }}" role="button" data-bs-toggle="" aria-expanded="false">
                        <div class="d-flex align-items-center"><span class="nav-link-icon"><span class="fa-solid fa-user"></span></span><span class="nav-link-text ps-1">Ledgers </span>
                        </div>
                    </a>
                    <!-- parent pages--><a class="nav-link" href="{{ route('accounts.viewClientAccounts') }}" role="button" data-bs-toggle="" aria-expanded="false">
                        <div class="d-flex align-items-center"><span class="nav-link-icon"><span class="fa-solid fa-money-bills"></span></span><span class="nav-link-text ps-1">Incomes/Expenses </span>
                        </div>
                    </a>
                </li>

                  <li class="nav-item">
                      <!-- label-->
                      <div class="row navbar-vertical-label-wrapper mt-3 mb-2">
                          <div class="col-auto navbar-vertical-label">System Currencies
                          </div>
                          <div class="col ps-0">
                              <hr class="mb-0 navbar-vertical-divider" />
                          </div>
                      </div>
                      <!-- parent pages--><a class="nav-link" href="{{ route('accounts.exchangeRates') }}" role="button" data-bs-toggle="" aria-expanded="false">
                          <div class="d-flex align-items-center"><span class="nav-link-icon"><span class="fa-solid fa-money-bill-trend-up"></span></span><span class="nav-link-text ps-1">Exchange Rates </span>
                          </div>
                      </a>
                      <!-- parent pages--><a class="nav-link" href="{{ route('accounts.viewCurrencies') }}" role="button" data-bs-toggle="" aria-expanded="false">
                          <div class="d-flex align-items-center"><span class="nav-link-icon"><span class="fa-solid fa-money-bill"></span></span><span class="nav-link-text ps-1">Currencies </span>
                          </div>
                      </a>
                  </li>

                <li class="nav-item">
                  <!-- label-->
                  <div class="row navbar-vertical-label-wrapper mt-3 mb-2">
                    <div class="col-auto navbar-vertical-label">Financial Years
                    </div>
                    <div class="col ps-0">
                      <hr class="mb-0 navbar-vertical-divider" />
                    </div>
                  </div>
                  <!-- parent pages--><a class="nav-link" href="{{ route('accounts.viewFinancialYears') }}" role="button" data-bs-toggle="" aria-expanded="false">
                    <div class="d-flex align-items-center"><span class="nav-link-icon"><span class="fa-solid fa-calendar-days"></span></span><span class="nav-link-text ps-1">View Financial Years </span>
                    </div>
                  </a>
                </li>

                  <li class="nav-item">
                      <!-- label-->
                      <div class="row navbar-vertical-label-wrapper mt-3 mb-2">
                          <div class="col-auto navbar-vertical-label">Taxes
                          </div>
                          <div class="col ps-0">
                              <hr class="mb-0 navbar-vertical-divider" />
                          </div>
                      </div>
                      <!-- parent pages--><a class="nav-link" href="{{ route('accounts.viewTaxBrackets') }}" role="button" data-bs-toggle="" aria-expanded="false">
                          <div class="d-flex align-items-center"><span class="nav-link-icon"><span class="fa-solid fa-hand-holding-dollar"></span></span><span class="nav-link-text ps-1">Tax Brackets</span>
                          </div>
                      </a>
                      <!-- parent pages--><a class="nav-link" href="{{ route('accounts.viewTaxes') }}" role="button" data-bs-toggle="" aria-expanded="false">
                          <div class="d-flex align-items-center"><span class="nav-link-icon"><span class="fa-solid fa-sack-xmark"></span></span><span class="nav-link-text ps-1">Taxes</span>
                          </div>
                      </a>
                  </li>

                  <li class="nav-item mb-0">
                      <!-- label-->
                      <div class="row navbar-vertical-label-wrapper mt-3 mb-2">
                          <div class="col-auto navbar-vertical-label">Verified Reports
                          </div>
                          <div class="col ps-0">
                              <hr class="mb-0 navbar-vertical-divider" />
                          </div>
                      </div>
                      <!-- parent pages--><a class="nav-link" href="{{ route('accounts.getSalesFinancialYears') }}" role="button" data-bs-toggle="" aria-expanded="false">
                          <div class="d-flex align-items-center"><span class="nav-link-icon"><span class="fa-solid fa-list-check"></span></span><span class="nav-link-text ps-1">Sales Statements</span>
                          </div>
                      </a>
                      <!-- parent pages--><a class="nav-link" href="{{ route('accounts.getPurchasesFinancialYears') }}" role="button" data-bs-toggle="" aria-expanded="false">
                          <div class="d-flex align-items-center"><span class="nav-link-icon"><span class="fa-solid fa-list-ol"></span></span><span class="nav-link-text ps-1">Purchases Statements</span>
                          </div>
                      </a>
                      <!-- parent pages--><a class="nav-link" href="{{ route('accounts.getAccountStatementFinancialYears') }}" role="button" data-bs-toggle="" aria-expanded="false">
                          <div class="d-flex align-items-center"><span class="nav-link-icon"><span class="fa-solid fa-filter-circle-dollar"></span></span><span class="nav-link-text ps-1">Trial Balance</span>
                          </div>
                      </a>
{{--                      <!-- parent pages--><a class="nav-link" href="#" role="button" data-bs-toggle="" aria-expanded="false">--}}
{{--                          <div class="d-flex align-items-center"><span class="nav-link-icon"><span class="fa fa-file"></span></span><span class="nav-link-text ps-1">Journal Ledger</span>--}}
{{--                          </div>--}}
{{--                      </a>--}}
                  </li>
              </ul>
            </div>
          </div>
        </nav>
        <!-- ----- navbar-vertical end -------------- -->
