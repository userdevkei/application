<!-- ---- navbar-vertical starts------------ -->
<style>
    .nav-link-icon {
        display: inline !important; /* Force display across breakpoints */
    }
</style>
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
                      <div class="col ps-0">
                          <hr class="mb-0 navbar-vertical-divider" />
                      </div>
{{--                      <li class="nav-item">--}}
                    <!-- parent pages--><a class="nav-link dropdown-indicator" href="#sales" role="button" data-bs-toggle="collapse" aria-expanded="false" aria-controls="sales">
                        <div class="d-flex align-items-center"><span class="nav-link-icon"><span class="fa-solid fa-wallet"></span></span><span class="nav-link-text ps-1">Sales</span>
                        </div>
                    </a>
                    <ul class="nav collapse" id="sales">
                        <li class="nav-item"><a class="nav-link" href="{{ route('accounts.viewInvoices') }}">
                                <div class="d-flex align-items-center"><span class="nav-link-icon"><span class="fa-solid fa-bag-shopping"></span></span> <span class="nav-link-text ps-1"> Sales Invoices</span>
                                </div>
                            </a>
                            <!-- more inner pages-->
                        </li>

                        <li class="nav-item"><a class="nav-link" href="{{ route('accounts.viewAllTransactions') }}">
                                <div class="d-flex align-items-center"><span class="nav-link-icon"><span class="fa-solid fa-receipt"></span></span><span class="nav-link-text ps-1">Accounts Receivable</span>
                                </div>
                            </a>
                            <!-- more inner pages-->
                        </li>

                        <li class="nav-item"><a class="nav-link" href="{{ route('accounts.salesFYTaxes') }}">
                                <div class="d-flex align-items-center"><span class="nav-link-icon"><span class="fa-solid fa-list"></span></span><span class="nav-link-text ps-1">VAT Statements</span>
                                </div>
                            </a>
                            <!-- more inner pages-->
                        </li>
                    </ul>
                </li>
                <li class="nav-item">
                    <div class="col ps-0">
                        <hr class="mb-0 navbar-vertical-divider" />
                    </div>
                    <!-- parent pages--><a class="nav-link dropdown-indicator" href="#purchases" role="button" data-bs-toggle="collapse" aria-expanded="false" aria-controls="purchases">
                        <div class="d-flex align-items-center"><span class="nav-link-icon"><span class="fa-solid fa-coins"></span></span><span class="nav-link-text ps-1">Purchases</span>
                        </div>
                    </a>
                  <!-- label-->
                    <ul class="nav collapse" id="purchases">
                      <!-- parent pages--><li class="nav-item"><a class="nav-link" href="{{ route('accounts.viewPurchases') }}" role="button" data-bs-toggle="" aria-expanded="false">
                        <div class="d-flex align-items-center"><span class="nav-link-icon"><span class="fa-solid fa-cart-shopping"></span></span><span class="nav-link-text ps-1">Purchase Invoices</span>
                        </div>
                            </a></li>
                      <!-- parent pages--><li class="nav-item"><a class="nav-link" href="{{ route('accounts.viewPurchasePayments') }}" role="button" data-bs-toggle="" aria-expanded="false">
                        <div class="d-flex align-items-center"><span class="nav-link-icon"><span class="fas fa-cash-register"></span></span><span class="nav-link-text ps-1">Account Payable</span>
                        </div>
                            </a></li>
                        <!-- parent pages--><li class="nav-item"><a class="nav-link" href="{{ route('accounts.purchaseFYTaxes') }}" role="button" data-bs-toggle="" aria-expanded="false">
                        <div class="d-flex align-items-center"><span class="nav-link-icon"><span class="fa-solid fa-list-ul"></span></span><span class="nav-link-text ps-1">WHT Statement</span>
                        </div>
                            </a></li>
                         <!-- parent pages--><li class="nav-item"><a class="nav-link" href="{{ route('accounts.transportDetails') }}" role="button" data-bs-toggle="" aria-expanded="false">
                        <div class="d-flex align-items-center"><span class="nav-link-icon"><span class="fa-solid fa-truck-fast"></span></span><span class="nav-link-text ps-1">Transport</span>
                        </div>
                            </a></li>
                    </ul>
                </li>
                  <li class="nav-item">
                      <!-- label-->
                      <div class="col ps-0">
                          <hr class="mb-0 navbar-vertical-divider" />
                      </div>
                      <!-- parent pages--><a class="nav-link" href="#" role="button" data-bs-toggle="" aria-expanded="false">
                          <div class="d-flex align-items-center"><span class="nav-link-icon"><span class="fa-solid fa-building-columns"></span></span><span class="nav-link-text ps-1">Bank Reconciliation </span>
                          </div>
                      </a>
                  </li>
                  <li class="nav-item">
                      <div class="col ps-0">
                          <hr class="mb-0 navbar-vertical-divider" />
                      </div>
                      <!-- parent pages--><a class="nav-link dropdown-indicator" href="#journals" role="button" data-bs-toggle="collapse" aria-expanded="false" aria-controls="journals">
                          <div class="d-flex align-items-center"><span class="nav-link-icon"><span class="fa-solid fa-book"></span></span><span class="nav-link-text ps-1">Journals</span>
                          </div>
                      </a>
                      <!-- label-->
                      <ul class="nav collapse" id="journals">
                          <!-- parent pages--><li class="nav-item"><a class="nav-link" href="#" role="button" data-bs-toggle="" aria-expanded="false">
                                  <div class="d-flex align-items-center"><span class="nav-link-icon"><span class="fa-regular fa-folder-open"></span></span><span class="nav-link-text ps-1">View Journals</span>
                                  </div>
                              </a></li>
                          <!-- parent pages--><li class="nav-item"><a class="nav-link" href="#" role="button" data-bs-toggle="" aria-expanded="false">
                                  <div class="d-flex align-items-center"><span class="nav-link-icon"><span class="fa-solid fa-clock-rotate-left"></span></span><span class="nav-link-text ps-1">Schedule Journal</span>
                                  </div>
                              </a></li>
                      </ul>
                  </li>
                <li class="nav-item">
                  <!-- label-->
                    <div class="col ps-0">
                      <hr class="mb-0 navbar-vertical-divider" />
                    </div>
                    <!-- parent pages--><a class="nav-link dropdown-indicator" href="#accounts" role="button" data-bs-toggle="collapse" aria-expanded="false" aria-controls="accounts">
                        <div class="d-flex align-items-center"><span class="nav-link-icon"><span class="fa-solid fa-chart-pie"></span></span><span class="nav-link-text ps-1">Chart of Accounts</span>
                        </div>
                    </a>
                    <ul class="nav collapse" id="accounts">
                        <!-- parent pages--><li class="nav-item"><a class="nav-link" href="{{ route('accounts.viewAccounts') }}" role="button" data-bs-toggle="" aria-expanded="false">
                            <div class="d-flex align-items-center"><span class="nav-link-icon"><span class="fa-solid fa-users-line"></span></span><span class="nav-link-text ps-1">Accounts </span>
                            </div>
                            </a></li>
                        <!-- parent pages--><li class="nav-item"><a class="nav-link" href="{{ route('accounts.accountSubCategories') }}" role="button" data-bs-toggle="" aria-expanded="false">
                            <div class="d-flex align-items-center"><span class="nav-link-icon"><span class="fa-solid fa-users-between-lines"></span></span><span class="nav-link-text ps-1">Grouped Ledgers </span>
                            </div>
                            </a></li>
                        <!-- parent pages--><li class="nav-item"><a class="nav-link" href="{{ route('accounts.viewChartAccounts') }}" role="button" data-bs-toggle="" aria-expanded="false">
                            <div class="d-flex align-items-center"><span class="nav-link-icon"><span class="fa-solid fa-user"></span></span><span class="nav-link-text ps-1">Ledgers </span>
                            </div>
                            </a></li>
                        <!-- parent pages--><li class="nav-item"><a class="nav-link" href="{{ route('accounts.viewClientAccounts') }}" role="button" data-bs-toggle="" aria-expanded="false">
                            <div class="d-flex align-items-center"><span class="nav-link-icon"><span class="fa-solid fa-money-bills"></span></span><span class="nav-link-text ps-1">Incomes/Expenses </span>
                            </div>
                            </a></li>
                    </ul>
                </li>

                  <li class="nav-item">
                      <!-- label-->
                          <div class="col ps-0">
                              <hr class="mb-0 navbar-vertical-divider" />
                          </div>
                      <!-- parent pages--><a class="nav-link dropdown-indicator" href="#currencies" role="button" data-bs-toggle="collapse" aria-expanded="false" aria-controls="currencies">
                          <div class="d-flex align-items-center"><span class="nav-link-icon"><span class="fa-solid fa-coins"></span></span><span class="nav-link-text ps-1">Currencies</span>
                          </div>
                      </a>
                      <ul class="nav collapse" id="currencies">
                      <!-- parent pages--><li class="nav-item"><a class="nav-link" href="{{ route('accounts.exchangeRates') }}" role="button" data-bs-toggle="" aria-expanded="false">
                          <div class="d-flex align-items-center"><span class="nav-link-icon"><span class="fa-solid fa-money-bill-trend-up"></span></span><span class="nav-link-text ps-1">Exchange Rates </span>
                          </div>
                              </a></li>
                      <!-- parent pages--><li class="nav-item"><a class="nav-link" href="{{ route('accounts.viewCurrencies') }}" role="button" data-bs-toggle="" aria-expanded="false">
                          <div class="d-flex align-items-center"><span class="nav-link-icon"><span class="fa-solid fa-money-bill"></span></span><span class="nav-link-text ps-1">Currencies </span>
                          </div>
                              </a></li>
                      </ul>
                  </li>

                <li class="nav-item">
                  <!-- label-->
                    <div class="col ps-0">
                      <hr class="mb-0 navbar-vertical-divider" />
                    </div>
                  <!-- parent pages--><a class="nav-link" href="{{ route('accounts.viewFinancialYears') }}" role="button" data-bs-toggle="" aria-expanded="false">
                    <div class="d-flex align-items-center"><span class="nav-link-icon"><span class="fa-solid fa-calendar-days"></span></span><span class="nav-link-text ps-1">View Financial Years </span>
                    </div>
                  </a>
                </li>

                  <li class="nav-item">
                      <!-- label-->
                          <div class="col ps-0">
                              <hr class="mb-0 navbar-vertical-divider" />
                          </div>
                      <!-- parent pages--><a class="nav-link dropdown-indicator" href="#taxes" role="button" data-bs-toggle="collapse" aria-expanded="false" aria-controls="taxes">
                          <div class="d-flex align-items-center"><span class="nav-link-icon"><span class="fas fa-file-invoice-dollar"></span></span><span class="nav-link-text ps-1">Taxes</span>
                          </div>
                      </a>
                      <ul class="nav collapse" id="taxes">
                          <!-- parent pages--><li class="nav-item"><a class="nav-link" href="{{ route('accounts.viewTaxBrackets') }}" role="button" data-bs-toggle="" aria-expanded="false">
                              <div class="d-flex align-items-center"><span class="nav-link-icon"><span class="fa-solid fa-hand-holding-dollar"></span></span><span class="nav-link-text ps-1">Tax Brackets</span>
                              </div>
                              </a></li>
                          <!-- parent pages--><li class="nav-item"><a class="nav-link" href="{{ route('accounts.viewTaxes') }}" role="button" data-bs-toggle="" aria-expanded="false">
                              <div class="d-flex align-items-center"><span class="nav-link-icon"><span class="fa-solid fa-sack-xmark"></span></span><span class="nav-link-text ps-1">Taxes</span>
                              </div>
                              </a></li>
                      </ul>
                  </li>

                  <li class="nav-item mb-0">
                      <!-- label-->
                          <div class="col ps-0">
                              <hr class="mb-0 navbar-vertical-divider" />
                          </div>
                      <!-- parent pages--><a class="nav-link dropdown-indicator" href="#report" role="button" data-bs-toggle="collapse" aria-expanded="false" aria-controls="report">
                          <div class="d-flex align-items-center"><span class="nav-link-icon"><span class="fa-solid fa-list-ul"></span></span><span class="nav-link-text ps-1">Accounts Reports</span>
                          </div>
                      </a>
                      <ul class="nav collapse" id="report">
                          <!-- parent pages--><li class="nav-item"><a class="nav-link" href="{{ route('accounts.getLedgerFinancialYears') }}" role="button" data-bs-toggle="" aria-expanded="false">
                                  <div class="d-flex align-items-center"><span class="nav-link-icon"><span class="fa-solid fa-money-check-dollar"></span></span><span class="nav-link-text ps-1">Income Statement</span>
                                  </div>
                              </a></li>
                          <!-- parent pages--><li class="nav-item"><a class="nav-link" href="{{ route('accounts.getExpenseLedgerFinancialYears') }}" role="button" data-bs-toggle="" aria-expanded="false">
                                  <div class="d-flex align-items-center"><span class="nav-link-icon"><span class="fas fa-money-check"></span></span><span class="nav-link-text ps-1">Expense Statement</span>
                                  </div>
                              </a></li>
                          <li class="nav-item">
                      <!-- parent pages--><li class="nav-item"><a class="nav-link" href="{{ route('accounts.getSalesFinancialYears') }}" role="button" data-bs-toggle="" aria-expanded="false">
                          <div class="d-flex align-items-center"><span class="nav-link-icon"><span class="fa-solid fa-list-check"></span></span><span class="nav-link-text ps-1">Sales Statements</span>
                          </div>
                              </a></li>
                      <!-- parent pages--><li class="nav-item"><a class="nav-link" href="{{ route('accounts.getPurchasesFinancialYears') }}" role="button" data-bs-toggle="" aria-expanded="false">
                          <div class="d-flex align-items-center"><span class="nav-link-icon"><span class="fa-solid fa-list-ol"></span></span><span class="nav-link-text ps-1">Purchases Statements</span>
                          </div>
                              </a></li>
                          <!-- parent pages--><li class="nav-item"><a class="nav-link" href="{{ route('accounts.viewAgingAnalysis') }}" role="button" data-bs-toggle="" aria-expanded="false">
                                  <div class="d-flex align-items-center"><span class="nav-link-icon"><span class="fa-regular fa-hourglass-half"></span></span><span class="nav-link-text ps-1">Aging Analysis</span>
                                  </div>
                              </a></li>
                      <!-- parent pages--><li class="nav-item"><a class="nav-link" href="{{ route('accounts.getAccountStatementFinancialYears') }}" role="button" data-bs-toggle="" aria-expanded="false">
                          <div class="d-flex align-items-center"><span class="nav-link-icon"><span class="fa-solid fa-filter-circle-dollar"></span></span><span class="nav-link-text ps-1">Trial Balance</span>
                          </div>
                      </a><li class="nav-item">
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
