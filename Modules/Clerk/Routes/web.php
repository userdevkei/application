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
use Modules\Clerk\Http\Controllers\ClerkController;

Route::prefix('clerk')->middleware(['auth', 'web', 'userRoles', 'userRole:2,3,4,5,6'])->group(function() {
    Route::get('/', [ClerkController::class, 'index'])->name('clerk.dashboard');
    Route::get('dashboard-report/{id}', [ClerkController::class, 'dashboardReport'])->name('clerk.dashboardReport');
    Route::get('internal-transfers', [ClerkController::class, 'viewInternalTransfers'])->name('clerk.viewInternalTransfers');
    Route::get('external-transfers', [ClerkController::class, 'viewExternalTransfers'])->name('clerk.viewExternalTransfers');
    Route::get('filter-clients-per-warehouse', [ClerkController::class, 'selectClients'])->name('clerk.selectClients');
    Route::get('filter-client-per-warehouse', [ClerkController::class, 'selectClient'])->name('clerk.selectClient');
    Route::post('register-internal-transfer-request', [ClerkController::class, 'registerInternalRequest'])->name('clerk.registerInternalRequest');
    Route::post('register-external-transfer-request', [ClerkController::class, 'registerExternalRequest'])->name('clerk.registerExternalRequest');
    Route::get('initiate-transfer-request/{id}', [ClerkController::class, 'initiateTransfer'])->name('clerk.initiateTransfer');
    Route::get('initiate-external-transfer-request/{id}', [ClerkController::class, 'initiateExternalTransfer'])->name('clerk.initiateExternalTransfer');
    Route::get('approve-external-transfer-request/{id}', [ClerkController::class, 'approveExternalTransfer'])->name('clerk.approveExternalTransfer');
    Route::get('release-external-transfer-request/{id}', [ClerkController::class, 'releaseExternalTransfer'])->name('clerk.releaseExternalTransfer');
    Route::get('service-transfer-request/{id}', [ClerkController::class, 'serviceRequest'])->name('clerk.serviceRequest');
    Route::post('update-transfer-request/{id}', [ClerkController::class, 'updateInterTransferRequest'])->name('clerk.updateInterTransferRequest');
    Route::get('cancel-transfer-request/{id}', [ClerkController::class, 'cancelInterTransferRequest'])->name('clerk.cancelInterTransferRequest');
    Route::get('cancel-external-transfer-request/{id}', [ClerkController::class, 'cancelExternalTransferRequest'])->name('clerk.cancelExternalTransferRequest');
    Route::post('receive-transfer-request/{id}', [ClerkController::class, 'receiveInterTransferRequest'])->name('clerk.receiveInterTransferRequest');
    Route::post('update-external-transfer-request/{id}', [ClerkController::class, 'updateExternalTransferRequest'])->name('clerk.updateExternalTransferRequest');
    Route::get('download-inter-transfer-delivery-note/{id}', [ClerkController::class, 'downloadInterDelNote'])->name('clerk.downloadInterDelNote');
    Route::get('download-extra-transfer-delivery-note/{id}', [ClerkController::class, 'downloadExtraDelNote'])->name('clerk.downloadExtraDelNote');
    Route::any('prepare-internal-transfer', [ClerkController::class, 'prepareInternalTransfer'])->name('clerk.prepareInternalTransfer');
    Route::any('prepare-external-transfer', [ClerkController::class, 'prepareExternalTransfer'])->name('clerk.prepareExternalTransfer');
    Route::get('prepare-to-receive-transfer/{id}', [ClerkController::class, 'prepareToReceiveTransfer'])->name('clerk.prepareToReceiveTransfer');
    Route::get('view-internal-transfer-details/{id}', [ClerkController::class, 'viewInternalTransferDetails'])->name('clerk.viewInternalTransferDetails');
    Route::get('view-external-transfer-details/{id}', [ClerkController::class, 'viewExternalTransferDetails'])->name('clerk.viewExternalTransferDetails');

    Route::get('view-delivery-orders', [ClerkController::class, 'viewDeliveryOrders'])->name('clerk.viewDeliveryOrders');
    Route::get('add-delivery-orders', [ClerkController::class, 'addDeliveryOrders'])->name('clerk.addDeliveryOrders');
    Route::post('register-delivery-order', [ClerkController::class, 'registerDeliveryOrder'])->name('clerk.registerDeliveryOrder');
    Route::post('update-delivery-order/{id}', [ClerkController::class, 'updateDeliveryOrder'])->name('clerk.updateDeliveryOrder');
    Route::get('filter-warehouse-branches', [ClerkController::class, 'filterWarehouseBranch'])->name('clerk.filterWarehouseBranch');
    Route::get('filter-warehouse-bays', [ClerkController::class, 'filterWarehouseBay'])->name('clerk.filterWarehouseBay');

    Route::post('clerk-register-warehouse', [ClerkController::class, 'registerWarehouse'])->name('clerk.registerWarehouse');
    Route::post('clerk-register-tea-grade', [ClerkController::class, 'registerTeaGrade'])->name('clerk.registerTeaGrade');
    Route::post('clerk-register-garden', [ClerkController::class, 'registerGarden'])->name('clerk.registerGarden');
    Route::post('clerk-register-broker', [ClerkController::class, 'registerBroker'])->name('clerk.registerBroker');
    Route::post('clear-register-transporter', [ClerkController::class, 'registerTransporter'])->name('clerk.registerTransporter');

    Route::get('clerk-fetch-driver-by-id', [ClerkController::class, 'fetchIdNumber'])->name('clerk.fetchIdNumber');
    Route::post('clerk-create-Llis', [ClerkController::class, 'createLLI'])->name('clerk.createLLI');
    Route::get('download-loading-instructions/{id}', [ClerkController::class, 'downloadLLI'])->name('clerk.downloadLLI');
    Route::post('cancel-loading-instructions', [ClerkController::class, 'revertLLI'])->name('clerk.revertLLI');
    Route::post('update-loading-instructions/{id}', [ClerkController::class, 'updateLLI'])->name('clerk.updateLLI');

    Route::get('view-deliveries', [ClerkController::class, 'viewDeliveries'])->name('clerk.viewDeliveries');
    Route::post('fetch-do-number', [ClerkController::class, 'getDoNumber'])->name('clerk.getDoNumber');
    Route::post('receive-delivery', [ClerkController::class, 'receiveDelivery'])->name('clerk.receiveDelivery');
    Route::post('update-delivery/{id}', [ClerkController::class, 'updateStock'])->name('clerk.updateStock');
    Route::post('download-stock-report', [ClerkController::class, 'StockReport'])->name('clerk.StockReport');
    Route::get('export-stock-report', [ClerkController::class, 'exportStock'])->name('clerk.exportStock');
    Route::post('download-collection-report', [ClerkController::class, 'collectionReport'])->name('clerk.collectionReport');

    Route::get('tea-samples-request', [ClerkController::class,'teaSamplesRequest'])->name('clerk.teaSamplesRequest');
    Route::get('withdraw-sample/{id}', [ClerkController::class,'withdrawSample'])->name('clerk.withdrawSample');
    Route::post('store-sample-request/{id}', [ClerkController::class,'storeSampleRequest'])->name('clerk.storeSampleRequest');

    Route::get('view-tea-collection-instructions', [ClerkController::class, 'viewLLIs'])->name('clerk.viewLLIs');
    Route::get('filter-dos-by-garden', [ClerkController::class, 'filterByGarden'])->name('clerk.filterByGarden');
    Route::get('filter-dos-by-client', [ClerkController::class, 'filterByClient'])->name('clerk.filterByClient');
    Route::get('filter-dos-by-sale-number', [ClerkController::class, 'filterBySaleNumber'])->name('clerk.filterBySaleNumber');
    Route::get('view-tci-details/{id}', [ClerkController::class, 'viewTciDetails'])->name('clerk.viewTciDetails');
    Route::get('add-tci', [ClerkController::class, 'addTCI'])->name('clerk.addTCI');

    Route::get('view-shipping-instructions', [ClerkController::class, 'viewShippingInstructions'])->name('clerk.viewShippingInstructions');
    Route::post('add-shipping-instruction', [ClerkController::class, 'addShippingInstruction'])->name('clerk.addShippingInstruction');
    Route::get('add-teas-to-shipping-instruction/{id}', [ClerkController::class, 'addShipmentTeas'])->name('clerk.addShipmentTeas');
    Route::post('store-shipping-instruction/{id}', [ClerkController::class, 'storeShippingInstruction'])->name('clerk.storeShippingInstruction');
    Route::get('update-shipping-instruction/{id}', [ClerkController::class, 'updateShippingInstruction'])->name('clerk.updateShippingInstruction');
    Route::get('initiate-shipping-instruction/{id}', [ClerkController::class, 'initateSI'])->name('clerk.initateSI');
    Route::post('update-shipping-instruction-details/{id}', [ClerkController::class, 'updateShippingInstructionDetails'])->name('clerk.updateShippingInstructionDetails');
    Route::get('update-shipped-details/{id}', [ClerkController::class, 'markAsShipped'])->name('clerk.markAsShipped');
    Route::get('download-shipping-instruction/{id}', [ClerkController::class, 'downloadSIDocument'])->name('clerk.downloadSIDocument');
    Route::get('download-driver-clearance-form/{id}', [ClerkController::class, 'downloadDriverClearance'])->name('clerk.downloadDriverClearance');
    Route::get('create-shipping-instruction', [ClerkController::class, 'createSI'])->name('clerk.createSI');

    Route::get('view-all-blend-requests', [ClerkController::class, 'viewBlendProcessing'])->name('clerk.viewBlendProcessing');
    Route::get('create-blend-sheet', [ClerkController::class, 'createBlendSheet'])->name('clerk.createBlendSheet');
    Route::post('add-a-blend-sheet', [ClerkController::class, 'addBlendSheet'])->name('clerk.addBlendSheet');
    Route::get('add-a-blend-teas/{id}', [ClerkController::class, 'addBlendTeas'])->name('clerk.addBlendTeas');
    Route::post('add-a-blend-balance{id}', [ClerkController::class, 'addBlendBalanceTeas'])->name('clerk.addBlendBalanceTeas');
    Route::post('store-blend-teas/{id}', [ClerkController::class, 'storeBlendTeas'])->name('clerk.storeBlendTeas');
    Route::get('update-blend-sheet-status/{id}', [ClerkController::class, 'updateBlendSheet'])->name('clerk.updateBlendSheet');
    Route::post('update-blend-sheet-details/{id}', [ClerkController::class, 'updateBlendSheetDetails'])->name('clerk.updateBlendSheetDetails');
    Route::get('update-blend-sheet-teas/{id}', [ClerkController::class, 'markBlendTeaAsShipped'])->name('clerk.markBlendTeaAsShipped');
    Route::get('blend-balance-in-stock', [ClerkController::class, 'viewBlendBalances'])->name('clerk.viewBlendBalances');
    Route::get('clerk-download-blend-sheet/{id}', [ClerkController::class, 'downloadBlendSheet'])->name('clerk.downloadBlendSheet');
    Route::get('clerk-download-blend-sheet-release/{id}', [ClerkController::class, 'downloadBlendDriverClearance'])->name('clerk.downloadBlendDriverClearance');
    Route::get('clerk-download-blend-out_turn-report/{id}', [ClerkController::class, 'downloadOutturReport'])->name('clerk.downloadOutturReport');
    Route::get('update-outturn-report-details/{id}', [ClerkController::class, 'updateOutTurnReport'])->name('clerk.updateOutTurnReport');

    Route::get('view-direct-deliveries', [ClerkController::class, 'viewDirectDeliveries'])->name('clerk.viewDirectDeliveries');
    Route::get('add-direct-delivery', [ClerkController::class, 'addDirectDelivery'])->name('clerk.addDirectDelivery');
    Route::get('receive-direct-deliveries/{id}', [ClerkController::class, 'receiveDirectDeliveries'])->name('clerk.receiveDirectDeliveries');
    Route::get('download-direct-deliveries/{id}', [ClerkController::class, 'downloadDirectDeliveries'])->name('clerk.downloadDirectDeliveries');
    Route::post('register-direct-delivery', [ClerkController::class, 'registerDirectDeliveryOrder'])->name('clerk.registerDirectDeliveryOrder');
    Route::get('view-direct-delivery/{id}', [ClerkController::class, 'viewDirectDeliveryOrder'])->name('clerk.viewDirectDeliveryOrder');


    Route::get('trace-delivery-order/{id}', [ClerkController::class, 'traceTea'])->name('clerk.traceTea');
    Route::post('trace-by-invoice-number', [ClerkController::class, 'traceTeaByInvoice'])->name('clerk.traceTeaByInvoice');

    Route::post('clerk-download-blend-balance-report', [ClerkController::class, 'downloadBlendBalances'])->name('clerk.downloadBlendBalances');

    Route::get('clerk-fetch-warehouse-by-id', [ClerkController::class, 'filterWarehouses'])->name('clerk.filterWarehouses');
    Route::get('clerk-fetch-station-to-request-transfer-from', [ClerkController::class, 'selectStation'])->name('clerk.selectStation');

    Route::get('view-all-report-requests', [ClerkController::class, 'viewReportRequest'])->name('clerk.viewReportRequest');
    Route::get('filter-report-request', [ClerkController::class, 'filterReports'])->name('clerk.filterReports');
    Route::post('store-report-request', [ClerkController::class, 'storeReport'])->name('clerk.storeReport');
    Route::get('approve-report-request/{id}', [ClerkController::class, 'approveReportRequest'])->name('clerk.approveReportRequest');
    Route::get('download-report-request/{id}', [ClerkController::class, 'downloadReportRequest'])->name('clerk.downloadReportRequest');

    Route::post('export-transport-report', [ClerkController::class, 'exportTransportReport'])->name('clerk.exportTransportReport');
    Route::post('import-dos', [ClerkController::class, 'ImportDOS'])->name('clerk.ImportDOS');

});
