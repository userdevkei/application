<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

use Illuminate\Support\Facades\Route;
use Modules\Account\Http\Controllers\AccountController;

Route::prefix('account')->middleware(['auth', 'web'])->group(function() {
    Route::get('/', [AccountController::class, 'index'])->name('accounts.dashboard');
    Route::get('view-accounts', [AccountController::class, 'viewAccounts'])->name('accounts.viewAccounts');
    Route::post('register-account', [AccountController::class, 'registerAccount'])->name('accounts.registerAccount');
    Route::post('update-account/{id}', [AccountController::class, 'updateAccount'])->name('accounts.updateAccount');
    Route::get('delete-account/{id}', [AccountController::class, 'deleteAccount'])->name('accounts.deleteAccount');
    Route::get('get-account-type', [AccountController::class, 'getAccountType'])->name('accounts.getAccountType');

    Route::get('chart-accounts', [AccountController::class, 'viewChartAccounts'])->name('accounts.viewChartAccounts');
    Route::post('add-chart-of-account', [AccountController::class, 'addChartAccount'])->name('accounts.addChartAccount');
    Route::post('update-chart-of-account/{id}', [AccountController::class, 'updateChartAccount'])->name('accounts.updateChartAccount');
    Route::get('delete-chart-of-account/{id}', [AccountController::class, 'deleteChartAccount'])->name('accounts.deleteChartAccount');

    Route::get('view-client-accounts', [AccountController::class, 'viewClientAccounts'])->name('accounts.viewClientAccounts');
    Route::post('add-client-accounts', [AccountController::class, 'addClientAccount'])->name('accounts.addClientAccount');
    Route::post('update-client-accounts/{id}', [AccountController::class, 'updateClientAccount'])->name('accounts.updateClientAccount');
    Route::get('delete-client-accounts/{id}', [AccountController::class, 'deleteClientAccount'])->name('accounts.deleteClientAccount');


    Route::get('financial-years', [AccountController::class, 'viewFinancialYears'])->name('accounts.viewFinancialYears');
    Route::post('add-financial-year', [AccountController::class, 'addFinancialYears'])->name('accounts.addFinancialYears');
    Route::post('update-financial-year/{id}', [AccountController::class, 'updateFinancialYears'])->name('accounts.updateFinancialYears');
    Route::get('delete-financial-year/{id}', [AccountController::class, 'deleteFinancialYears'])->name('accounts.deleteFinancialYears');

    Route::get('currencies', [AccountController::class, 'viewCurrencies'])->name('accounts.viewCurrencies');
    Route::post('add-currency', [AccountController::class, 'addCurrency'])->name('accounts.addCurrency');
    Route::post('update-currency/{id}', [AccountController::class, 'updateCurrency'])->name('accounts.updateCurrency');
    Route::get('delete-currency/{id}', [AccountController::class, 'deleteCurrency'])->name('accounts.deleteCurrency');

    Route::get('view-account-sub-categories', [AccountController::class, 'accountSubCategories'])->name('accounts.accountSubCategories');
    Route::post('add-account-sub-category', [AccountController::class, 'addAccountSubCategory'])->name('accounts.addAccountSubCategory');
    Route::post('update-account-sub-category/{id}', [AccountController::class, 'updateAccountSubCategory'])->name('accounts.updateAccountSubCategory');
    Route::get('delete-account-sub-category/{id}', [AccountController::class, 'deleteAccountSubCategory'])->name('accounts.deleteAccountSubCategory');

    Route::get('view-product-categories', [AccountController::class, 'viewProductCategories'])->name('accounts.viewProductCategories');
    Route::post('add-product-categories', [AccountController::class, 'addProductCategory'])->name('accounts.addProductCategory');
    Route::post('update-product-categories/{id}', [AccountController::class, 'updateProductCategory'])->name('accounts.updateProductCategory');
    Route::get('delete-product-categories/{id}', [AccountController::class, 'deleteProductCategory'])->name('accounts.deleteProductCategory');

    Route::get('invoices', [AccountController::class, 'viewInvoices'])->name('accounts.viewInvoices');
    Route::get('add-invoice', [AccountController::class, 'addInvoice'])->name('accounts.addInvoice');
    Route::get('download-invoice/{id}', [AccountController::class, 'downloadInvoice'])->name('accounts.downloadInvoice');
    Route::get('view-invoice/{id}', [AccountController::class, 'viewInvoice'])->name('accounts.viewInvoice');
    Route::post('post-created-invoice/{id}', [AccountController::class, 'postInvoice'])->name('accounts.postInvoice');
    Route::get('delete-sale-invoice/{id}', [AccountController::class, 'deleteInvoice'])->name('accounts.deleteInvoice');

    Route::get('financial-year-sales-taxes', [AccountController::class, 'salesFYTaxes'])->name('accounts.salesFYTaxes');
    Route::get('view-yearly-sales-taxes/{id}', [AccountController::class, 'yearlyTaxes'])->name('accounts.yearlyTaxes');
    Route::get('view-sales-tax-statement/{id}', [AccountController::class, 'taxStatement'])->name('accounts.taxStatement');

    Route::get('accounts-fetch-account', [AccountController::class, 'fetchAccount'])->name('accounts.fetchAccount');
    Route::post('accounts-store-invoice', [AccountController::class, 'storeInvoice'])->name('accounts.storeInvoice');

    Route::get('get-sales-statements-per-financial-year', [AccountController::class, 'getSalesFinancialYears'])->name('accounts.getSalesFinancialYears');
    Route::get('get-purchases-statements-per-financial-year', [AccountController::class, 'getPurchasesFinancialYears'])->name('accounts.getPurchasesFinancialYears');
    Route::get('get-sales-invoices-per-financial-year/{id}', [AccountController::class, 'getClientsSalesWithInvoices'])->name('accounts.getClientsSalesWithInvoices');
    Route::get('get-purchases-invoices-per-financial-year/{id}', [AccountController::class, 'getClientsPurchasesWithInvoices'])->name('accounts.getClientsPurchasesWithInvoices');
    Route::get('view-client-statement-per-year/{id}', [AccountController::class, 'viewClientStatement'])->name('accounts.viewClientStatement');
    Route::get('view-supplier-statement-per-year/{id}', [AccountController::class, 'viewSupplierStatement'])->name('accounts.viewSupplierStatement');

    Route::get('get-account-statements-per-financial-year', [AccountController::class, 'getAccountStatementFinancialYears'])->name('accounts.getAccountStatementFinancialYears');
    Route::get('get-account-with-invoices-per-financial-year/{id}', [AccountController::class, 'getAccountsWithInvoices'])->name('accounts.getAccountsWithInvoices');
    Route::get('view-account-statement-per-year/{id}', [AccountController::class, 'viewAccountStatement'])->name('accounts.viewAccountStatement');
    Route::get('download-account-statement-per-year/{id}', [AccountController::class, 'downloadClientStatement'])->name('accounts.downloadClientStatement');
    Route::get('download-supplier-statement-per-year/{id}', [AccountController::class, 'downloadSupplierStatement'])->name('accounts.downloadSupplierStatement');

    Route::get('view-all-transactions', [AccountController::class, 'viewAllTransactions'])->name('accounts.viewAllTransactions');
    Route::post('store-payment-invoice', [AccountController::class, 'storePaymentInvoice'])->name('accounts.storePaymentInvoice');
    Route::get('get-payment-invoice/{id}', [AccountController::class, 'downloadPaymentReceipt'])->name('accounts.downloadPaymentReceipt');
    Route::get('get-payment-methods', [AccountController::class, 'getPaymentMethods'])->name('accounts.getPaymentMethods');

    Route::get('view-taxes', [AccountController::class, 'viewTaxes'])->name('accounts.viewTaxes');
    Route::post('store-tax', [AccountController::class, 'storeTax'])->name('accounts.storeTax');
    Route::post('update-tax/{id}', [AccountController::class, 'updateTax'])->name('accounts.updateTax');
    Route::get('delete-tax/{id}', [AccountController::class, 'deleteTax'])->name('accounts.deleteTax');

    Route::get('view-tax-brackets', [AccountController::class, 'viewTaxBrackets'])->name('accounts.viewTaxBrackets');
    Route::post('store-tax-bracket', [AccountController::class, 'storeTaxBracket'])->name('accounts.storeTaxBracket');
    Route::post('update-tax-bracket/{id}', [AccountController::class, 'updateTaxBracket'])->name('accounts.updateTaxBracket');
    Route::get('delete-tax-bracket/{id}', [AccountController::class, 'deleteTaxBracket'])->name('accounts.deleteTaxBracket');

    Route::get('view-all-purchase', [AccountController::class, 'viewPurchases'])->name('accounts.viewPurchases');
    Route::get('add-purchase-invoice', [AccountController::class, 'addPurchaseInvoice'])->name('accounts.addPurchaseInvoice');
    Route::post('store-purchase-invoice', [AccountController::class, 'storePurchaseInvoice'])->name('accounts.storePurchaseInvoice');
    Route::get('view-purchase-invoice/{id}', [AccountController::class, 'viewPurchaseInvoice'])->name('accounts.viewPurchaseInvoice');
    Route::post('post-purchase-created-invoice/{id}', [AccountController::class, 'postPurchaseInvoice'])->name('accounts.postPurchaseInvoice');
    Route::get('delete-purchase-sale-invoice/{id}', [AccountController::class, 'deletePurchaseInvoice'])->name('accounts.deletePurchaseInvoice');
    Route::get('create-credit-note/{id}', [AccountController::class, 'createCreditNote'])->name('accounts.createCreditNote');
    Route::post('store-credit-note/{id}', [AccountController::class, 'storeCreditNote'])->name('accounts.storeCreditNote');

    Route::get('fetch-purchase-invoice_number', [AccountController::class, 'fetchPurchaseInvNumber'])->name('accounts.fetchPurchaseInvNumber');
    Route::get('download-purchase-invoice/{id}', [AccountController::class, 'downloadPurchaseVoucher'])->name('accounts.downloadPurchaseVoucher');

    Route::get('financial-year-purchase-taxes', [AccountController::class, 'purchaseFYTaxes'])->name('accounts.purchaseFYTaxes');
    Route::get('view-yearly-purchase-taxes/{id}', [AccountController::class, 'yearlyPurchaseTaxes'])->name('accounts.yearlyPurchaseTaxes');
    Route::get('view-purchase-tax-statement/{id}', [AccountController::class, 'purchaseTaxStatement'])->name('accounts.purchaseTaxStatement');


    Route::get('get-income-streams', [AccountController::class, 'getIncomeStreams'])->name('accounts.getIncomeStreams');
    Route::get('get-expenses-streams', [AccountController::class, 'getExpenseItems'])->name('accounts.getExpenseItems');

    Route::get('view-exchange-rate', [AccountController::class, 'exchangeRates'])->name('accounts.exchangeRates');
    Route::post('store-exchange-rate', [AccountController::class, 'addCurrencyExchangeRate'])->name('accounts.addCurrencyExchangeRate');
    Route::post('update-exchange-rate/{id}', [AccountController::class, 'updateCurrencyExchangeRate'])->name('accounts.updateCurrencyExchangeRate');
    Route::get('delete-exchange-rate/{id}', [AccountController::class, 'deleteCurrencyExchangeRate'])->name('accounts.deleteCurrencyExchangeRate');

    Route::any('generate-vat-tax-report', [AccountController::class, 'generateVatTaxReport'])->name('accounts.generateVatTaxReport');
});
