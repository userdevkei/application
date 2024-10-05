<?php

namespace Modules\Account\Http\Controllers;

use App\Models\Client;
use App\Models\Destination;
use App\Models\Transporter;
use App\Models\UserInfo;
use App\Services\CustomIds;
use App\Services\ExportVATTaxReport;
use App\Services\Log;
use Carbon\Carbon;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Account\Entities\Account;
use Modules\Account\Entities\AccountSubCategories;
use Modules\Account\Entities\ChartOfAccount;
use Modules\Account\Entities\ClientAccount;
use Modules\Account\Entities\Currency;
use Modules\Account\Entities\FinancialYear;
use Modules\Account\Entities\ForexExchange;
use Modules\Account\Entities\Invoice;
use Modules\Account\Entities\InvoiceItem;
use Modules\Account\Entities\Journal;
use Modules\Account\Entities\Purchase;
use Modules\Account\Entities\PurchaseItem;
use Modules\Account\Entities\PurchaseJournal;
use Modules\Account\Entities\Tax;
use Modules\Account\Entities\TaxBrackets;
use Modules\Account\Entities\Transaction;
use NcJoes\OfficeConverter\OfficeConverter;
use PhpOffice\PhpWord\Element\Table;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Settings;
use PhpOffice\PhpWord\TemplateProcessor;

class AccountController extends Controller
{
    protected $logger;

    public function __construct(Log $logger)
    {
        $this->logger = $logger;
    }
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index()
    {
        $accounts = Account::all();
        $group = AccountSubCategories::all();
        $ledger = ChartOfAccount::all();
        $incomes = ClientAccount::where('type', 1)->get();
        $expenses = ClientAccount::where('type', 2)->get();
        return view('account::welcome')->with(['accounts' => $accounts, 'group' => $group, 'ledger' => $ledger, 'incomes' => $incomes, 'expenses' => $expenses]);
    }
    public function viewInvoices()
    {
        $invoices = Invoice::join('client_accounts', 'client_accounts.client_account_id', '=', 'invoices.client_id')
            ->join('currencies', 'currencies.currency_id', '=', 'client_accounts.currency_id')
            ->join('financial_years', 'financial_years.financial_year_id', '=', 'invoices.financial_year_id')
            ->select('invoices.invoice_id', 'currency_symbol', 'client_accounts.client_account_name as clientAccount', 'invoice_number', 'date_invoiced', 'due_date', 'invoices.financial_year_id','amount_due', 'year_starting', 'year_ending', 'invoices.status', 'posted', 'kra_number', 'invoices.type')
            ->orderBy('invoices.created_at', 'desc')
            ->get();

        return view('account::sales.index')->with(['invoices' => $invoices]);
    }
    public function postInvoice(Request $request, $id)
    {
        Invoice::find($id)->update(['posted' => 1, 'kra_number' => $request->kraNumber]);
        return redirect()->back()->with('success', 'Success! Invoice successfully posted');
    }
    public function viewInvoice($id) {
        $invoices = Invoice::join('invoice_items', 'invoice_items.invoice_id', '=', 'invoices.invoice_id')
            ->join('client_accounts', 'client_accounts.client_account_id', '=', 'invoice_items.ledger_id')
            ->join('currencies', 'currencies.currency_id', '=', 'client_accounts.currency_id')
            ->leftJoin('tax_brackets', 'tax_brackets.tax_bracket_id', '=', 'invoice_items.tax_id')
            ->leftJoin('taxes', 'taxes.tax_id', '=', 'tax_brackets.tax_id')
            ->select('client_account_name as account_name', 'tax_rate', 'quantity', 'unit_price', 'currency_symbol', 'tax_name', 'posted', 'kra_number')
            ->where(['invoices.invoice_id' => $id])
            ->whereNull( 'tax_brackets.deleted_at')
            ->orderBy('client_accounts.client_account_name', 'ASC')
            ->get();

        $account = ClientAccount::join('invoices', 'invoices.client_id', '=', 'client_accounts.client_account_id')
            ->join('currencies', 'currencies.currency_id', '=', 'client_accounts.currency_id')
            ->select('invoice_number', 'due_date', 'date_invoiced', 'invoices.status', 'currency_symbol', 'client_account_name', 'invoices.invoice_id')
            ->where('invoice_id', $id)->first();

        return view('account::sales.viewInvoice')->with(['invoices' => $invoices, 'account' => $account]);
    }
    public function deleteInvoice($id)
    {
        Invoice::find($id)->delete();
        InvoiceItem::where('invoice_id', $id)->delete();
        return redirect()->back()->with('success', 'Success! Invoice successfully deleted');
    }
    public function downloadInvoice($id)
    {
        $inv = Invoice::join('client_accounts', 'client_accounts.client_account_id', '=', 'invoices.client_id')
            ->join('user_infos', 'user_infos.user_id', '=', 'invoices.user_id')
            ->leftJoin('destinations', 'destinations.destination_id', '=', 'invoices.destination_id')
            ->join('currencies', 'currencies.currency_id', '=', 'client_accounts.currency_id')
            ->join('invoice_items', 'invoice_items.invoice_id', '=', 'invoices.invoice_id')
            ->leftJoin('tax_brackets', 'tax_brackets.tax_bracket_id', '=', 'invoice_items.tax_id')
            ->leftJoin('taxes', 'taxes.tax_id', '=', 'tax_brackets.tax_id')
            ->join('client_accounts as cc', 'cc.client_account_id', '=', 'invoice_items.ledger_id')
            ->join('financial_years', 'financial_years.financial_year_id', '=', 'invoices.financial_year_id')
            ->select('invoices.type', 'invoice_number', 'si_number', 'customer_message', 'client_accounts.client_address', 'client_accounts.kra_pin', 'kra_number', 'port_name', 'container_type', 'date_invoiced', 'due_date', 'invoice_items.quantity', 'invoice_items.unit_price', 'invoice_items.quantity', 'currency_symbol', 'cc.client_account_name as ledgerName', 'client_accounts.client_account_name', 'surname', 'first_name', 'year_starting', 'year_ending', 'tax_rate', 'tax_name', 'cc.currency_id', 'invoice_items.description as hscode', 'inv_reference')
            ->where('invoices.invoice_id', $id)
            ->orderBy('ledgerName', 'asc')
            ->get();

        $values = $inv->first();
        $invDate = Carbon::createFromTimestamp($values->date_invoiced)->format('Y-m-d');
        $forex = ForexExchange::where('currency_id', $values->currency_id)
            ->where('date_active', '<=', $invDate)
            ->orderBy('date_active', 'desc')
            ->first();

        $type = $values->type == 1 ? 'SALES' : 'CREDIT NOTE';
        $narration = $values->customer_message.' INVOICE TO BE SETTLED BY OR BEFORE '. Carbon::createFromTimestamp($values->due_date)->format('D, d-m-Y');

        $fYear = Carbon::parse($values->year_starting)->format('Y') == Carbon::parse($values->year_ending)->format('Y') ? Carbon::parse($values->year_starting)->format('Y') : Carbon::parse($values->year_starting)->format('Y').'/'.Carbon::parse($values->year_ending)->format('y');

        $domPdfPath = base_path('vendor/dompdf/dompdf');
        Settings::setPdfRendererPath($domPdfPath);
        Settings::setPdfRendererName('DomPDF');

        $table = new Table(['unit' => \PhpOffice\PhpWord\SimpleType\TblWidth::PERCENT, 'width' => 100 * 50, 'align' => 'center']);

        $header = ['size' => 10, 'name' => 'Cambria', 'space' => ['before' => 100, 'after' => 100], 'bold' => true];
        $text = ['size' => 9, 'name' => 'Cambria', 'space' => ['before' => 100, 'after' => 100], 'bold' => false];

        $table->addRow();
        $table->addCell(500, ['borderSize' => 1, 'align' => 'center', 'bgColor' => 'cccccc'])->addText('#', $header, ['space' => ['before' => 100, 'after' => 100]]);
        $table->addCell(900, ['borderSize' => 1, 'align' => 'center', 'bgColor' => 'cccccc'])->addText('HS CODE', $header, ['space' => ['before' => 100, 'after' => 100]]);
        $table->addCell(2500, ['borderSize' => 1, 'align' => 'center', 'bgColor' => 'cccccc'])->addText('INVOICE ITEM', $header, ['space' => ['before' => 100, 'after' => 100]]);
        $table->addCell(600, ['borderSize' => 1, 'align' => 'center', 'bgColor' => 'cccccc'])->addText('QTY', $header, ['space' => ['before' => 100, 'after' => 100]]);
        $table->addCell(900, ['borderSize' => 1, 'align' => 'center', 'bgColor' => 'cccccc'])->addText('UNIT PRICE', $header, ['space' => ['before' => 100, 'after' => 100]]);
        $table->addCell(1200, ['borderSize' => 1, 'align' => 'center', 'bgColor' => 'cccccc'])->addText('TOTAL PRICE', $header, ['space' => ['before' => 100, 'after' => 100]]);

        $amountDue = 0;
        $totalTax = 0;

        foreach ($inv as $key => $invoiceItem) {
            $table->addRow();
            $table->addCell(500, ['borderSize' => 1, 'align' => 'center'])->addText(++$key, $text, ['space' => ['before' => 100, 'after' => 100]]);
            $table->addCell(900, ['borderSize' => 1, 'align' => 'center'])->addText($invoiceItem->hscode, $text, ['space' => ['before' => 100, 'after' => 100]]);
            $table->addCell(2500, ['borderSize' => 1, 'align' => 'center'])->addText($invoiceItem->ledgerName, $text, ['space' => ['before' => 100, 'after' => 100]]);
            $table->addCell(600, ['borderSize' => 1, 'align' => 'center'])->addText($invoiceItem->quantity, $text, ['space' => ['before' => 100, 'after' => 100]]);
            $table->addCell(900, ['borderSize' => 1, 'align' => 'center'])->addText(number_format($invoiceItem->unit_price, 3), $text, ['space' => ['before' => 100, 'after' => 100]]);
            $table->addCell(1200, ['borderSize' => 1, 'align' => 'center'])->addText(number_format($invoiceItem->quantity * $invoiceItem->unit_price, 3), $text, ['space' => ['before' => 100, 'after' => 100]]);

            $taxRate = $invoiceItem->tax_rate == null ? 0 : $invoiceItem->tax_rate;
            $totalTax +=  floatval($taxRate)/100 * ($invoiceItem->quantity * $invoiceItem->unit_price);
            $amountDue += $invoiceItem->quantity * $invoiceItem->unit_price;

        }

        $table->addRow();

        $table->addCell(null, [ 'gridSpan' => 4])->addText('');
        $table->addCell(null, [ 'gridSpan' => 1])->addText('SUBTOTAL', $header, ['size' => 7, 'space' => ['before' => 100, 'after' => 100]]);
        $table->addCell(null, ['gridSpan' => 1, 'borderBottomSize' => 1])->addText($invoiceItem->currency_symbol.' '.number_format($amountDue, 3), $header, ['size' => 8, 'space' => ['before' => 100, 'after' => 100]]);

        $table->addRow();
        $table->addCell(null, [ 'gridSpan' => 4])->addText('');
        $table->addCell(null, [ 'gridSpan' => 1])->addText('TOTAL TAX', $header, ['size' => 7, 'space' => ['before' => 100, 'after' => 100]]);
        $table->addCell(null, ['gridSpan' => 1, 'borderBottomSize' => 1])->addText($invoiceItem->currency_symbol.' '.number_format($totalTax, 3), $header, ['size' => 8, 'space' => ['before' => 100, 'after' => 100]]);

        $table->addRow();
        $table->addCell(null, [ 'gridSpan' => 4])->addText('');
        $table->addCell(null, [ 'gridSpan' => 1, 'borderBottomSize' => 4])->addText('AMOUNT DUE', $header, ['size' => 7, 'space' => ['before' => 100, 'after' => 100]]);
        $table->addCell(null, ['gridSpan' => 1, 'borderBottomSize' => 4])->addText($invoiceItem->currency_symbol.' '.number_format($totalTax + $amountDue, 3), $header, ['size' => 8, 'space' => ['before' => 100, 'after' => 100]]);

        $fNarration = null;
        if ($forex){
            $fNarration = 'Exchange rate of Ksh.'.$forex->exchange_rate.' was applied. Total amount due Ksh.'.number_format(($totalTax + $amountDue) * $forex->exchange_rate, 3);
        }

        $invoice = new TemplateProcessor(storage_path('client_invoice.docx'));
        $invoice->setComplexBlock('{table}', $table);
        $invoice->setValue('clientName', $values->client_account_name);
        $invoice->setValue('invNumber', $values->invoice_number);
        $invoice->setValue('fYear', $fYear);
        $invoice->setValue('type', $type);
        $invoice->setValue('ref', $values->type == 1 ? 'DUE DATE' : 'INV. REF');
        $invoice->setValue('siNumber', $values->si_number);
        $invoice->setValue('pinNo', $values->kra_pin);
        $invoice->setValue('cuNumber', $values->kra_number);
        $invoice->setValue('conts', $values->container_type);
        $invoice->setValue('destination', $values->port_name);
        $invoice->setValue('invoice', $fNarration);
        $invoice->setValue('clientAddress', $values->client_address);
        $invoice->setValue('narration', $narration);
        $invoice->setValue('printer', auth()->user()->user->surname.' '.auth()->user()->user->first_name);
        $invoice->setValue('user', $values->surname.' '.$values->first_name);
        $invoice->setValue('date', Carbon::now()->format('D, d M Y H:i:s'));
        $invoice->setValue('invDate', Carbon::createFromTimestamp($values->date_invoiced)->format('d/m/Y'));
        $invoice->setValue('dueDate', $values->type == 1 ? Carbon::createFromTimestamp($values->due_date)->format('d/m/Y') : $values->inv_reference);
        $docPath = 'Files/'.$values->invoice_number.'.docx';
        $invoice->saveAs($docPath);
//        return response()->download($docPath)->deleteFileAfterSend(true);
        $phpWord = IOFactory::load($docPath);
        $contents = \PhpOffice\PhpWord\IOFactory::load($docPath);
        $pdfPath = 'Files/TempFiles/'.$values->invoice_number. ".pdf";
        $converter =  new OfficeConverter($docPath, 'Files/TempFiles/');
        $converter->convertTo($values->invoice_number.".pdf");
        unlink($docPath);
        // return response()->file($pdfPath);
        // return response()->download($pdfPath)->deleteFileAfterSend(true);

        return view('account::sales.printInvoice', ['pdfPath' => $pdfPath]);
    }
    public function addInvoice()
    {
        $accounts = ClientAccount::join('chart_of_accounts', 'chart_of_accounts.chart_id', '=', 'client_accounts.chart_id')
            ->join('account_sub_categories', 'account_sub_categories.sub_account_id', '=', 'chart_of_accounts.sub_account_id')
            ->join('accounts', 'accounts.account_id', '=', 'account_sub_categories.account_id')
            ->join('currencies', 'currencies.currency_id', '=', 'client_accounts.currency_id')
            ->select('client_account_id', 'client_account_number', 'client_account_name', 'opening_date', 'chart_name', 'sub_account_name', 'account_name', 'account_type', 'currency_name', 'currency_symbol')
            ->orderBy('client_account_name', 'asc')
            ->whereNull('client_accounts.type')
            ->get();
        $financialYears = FinancialYear::orderBy('year_starting', 'desc')->get()->map(function($year) {
            $formattedYear = Carbon::parse($year->year_starting)->format('Y') == Carbon::parse($year->year_ending)->format('Y')
                ? Carbon::parse($year->year_starting)->format('Y')
                : Carbon::parse($year->year_starting)->format('Y').'/'.Carbon::parse($year->year_ending)->format('y');

            return [
                'financial_year_id' => $year->financial_year_id, // Assuming there's an 'id' field
                'financial_year' => $formattedYear
            ];
        });

        $taxes = TaxBrackets::join('taxes', 'taxes.tax_id', '=', 'tax_brackets.tax_id')->where(['tax_brackets.status' => 1, 'effect' => 1])->orderBy('tax_name', 'asc')->get();
        $items =  $accounts->where('account_type', 1);
        $debtors = $accounts->where('account_type', 2);
        $destinations = Destination::where('status', 1)->get();
        return view('account::sales.addInvoice')->with(['debtors' => collect($debtors), 'items' => collect($items), 'financialYears' => $financialYears, 'taxes' => $taxes, 'destinations' => $destinations]);
    }
    public function createCreditNote($id)
    {
        $invoice = Invoice::join('invoice_items', 'invoice_items.invoice_id', '=', 'invoices.invoice_id')
            ->join('client_accounts', 'client_accounts.client_account_id', '=', 'invoice_items.ledger_id')
            ->join('client_accounts as client', 'client.client_account_id', '=', 'invoices.client_id')
            ->join('currencies', 'currencies.currency_id', '=', 'client_accounts.currency_id')
            ->leftJoin('tax_brackets', 'tax_brackets.tax_bracket_id', '=', 'invoice_items.tax_id')
            ->leftJoin('taxes', 'taxes.tax_id', '=', 'tax_brackets.tax_id')
            ->select('client_accounts.client_account_name as account_name', 'invoice_number', 'client.client_account_name as client_name', 'tax_rate', 'quantity', 'unit_price', 'currency_symbol', 'tax_name', 'posted', 'kra_number', 'invoice_items.description', 'ledger_id', 'invoices.invoice_id', 'invoice_items.tax_id')
            ->where(['invoices.invoice_id' => $id])
            ->whereNull( 'tax_brackets.deleted_at')
            ->orderBy('client_accounts.client_account_name', 'ASC')
            ->get();
        return view('account::sales.creditNote')->with(['invoice' => $invoice]);

    }
    public function storeCreditNote(Request $request, $id)
    {
        $creditItems = array_filter($request->creditItems, function ($creditItem) {
            // Check if all required keys exist in the delivery array
            return array_key_exists('credit_quantity', $creditItem)
                && array_key_exists('credit_rate', $creditItem)
                // Check if any of the values are null
                && $creditItem['credit_quantity'] !== null
                && $creditItem['credit_rate'] !== null;
        });

        if ($creditItems == null){
            return back()->with('error', 'Oops! Select invoice items to add a credit note');
        }

//        return $request->all();
        DB::beginTransaction();
        try {
            $invoiceId = (new CustomIds())->generateId();
            $invoiceNumber = Invoice::newCreditNote();
            $invCreditNote = Invoice::find($id);
            $invoice = [
                'invoice_id' => $invoiceId,
                'invoice_number' => $invoiceNumber,
                'client_id' => $invCreditNote->client_id,
                'date_invoiced' => strtotime($request->creditNoteDate),
                'due_date' =>$invCreditNote->due_date,
                'customer_message' => 'CREDIT NOTE FOR '.$invCreditNote->invoice_number.' '.$request->customerMessage,
                'container_type' => $invCreditNote->container_type,
                'si_number' => $invCreditNote->si_number,
                'destination_id' => $invCreditNote->destination_id,
                'financial_year_id' => $invCreditNote->financial_year_id,
                'amount_due' => number_format($request->totalAmount, 3, '.', ''),
                'status' => 1,
                'posted' => 1,
                'type' => 2,
                'user_id' => auth()->user()->user_id,
                'inv_reference' => $invCreditNote->invoice_number
            ];

            Invoice::create($invoice);

            foreach ($creditItems as $keyItem => $invoice){
                $invoiceItems = [
                    'invoice_item_id' => (new CustomIds())->generateId(),
                    'invoice_id' => $invoiceId,
                    'ledger_id' => $keyItem,
                    'quantity' => $invoice['credit_quantity'],
                    'unit_price' => number_format($invoice['credit_rate'], 3, '.', ''),
                    'tax_id' => $invoice['credit_tax'],
                ];

                InvoiceItem::create($invoiceItems);
            }


            $accountToBill = ClientAccount::where('client_account_id', $invCreditNote->client_id)->first();

            $journalEntry = [
                'journal_id' => (new CustomIds())->generateId(),
                'invoice_id' => $invoiceId,
                'account_id' => $accountToBill->chart_id,
                'debit' => '0.000',
                'credit' => number_format($request->totalAmount, 3, '.', ''),
                'description' => 'INVOICE '.$invoiceNumber,
            ];
            Journal::create($journalEntry);

            if (floatval($request->totalTaxAmount) > 0){
                $currencyId = ClientAccount::where('client_account_id', $invCreditNote->client_id)->first()->currency_id;
                $currency = Currency::where('currency_id', $currencyId)->first();
                if ($currency->priority !== 1){
                    $invDate = $request->creditNoteDate;
                    $forex = ForexExchange::where('currency_id', $currencyId)->where('date_active', '<=', $invDate)->orderBy('date_active', 'desc')->first();
                    $invoiceAmount = $request->totalTaxAmount * $forex->exchange_rate;
                }else{
                    $invoiceAmount = $request->totalTaxAmount;
                }

                $taxId = InvoiceItem::where('invoice_id', $id)->whereNotNull('tax_id')->first();
                $vatTax = [
                    'journal_id' => (new CustomIds())->generateId(),
                    'invoice_id' => $invoiceId,
                    'account_id' => $taxId->tax_id,
                    'debit' => '0.000',
                    'credit' => number_format($invoiceAmount, 3, '.', ''),
                    'description' => 'INVOICE '.$invoiceNumber,
                ];
                Journal::create($vatTax);
            }

            foreach ($creditItems as $keyCredit => $credit){
                $accountToCredit = ClientAccount::where('client_account_id', $keyCredit)->first();
                $journalEntries = [
                    'journal_id' => (new CustomIds())->generateId(),
                    'invoice_id' => $invoiceId,
                    'account_id' => $accountToCredit->chart_id,
                    'debit' => number_format( $credit['credit_quantity'] * $credit['credit_rate'], 3, '.', ''),
                    'credit' => '0.000',
                    'description' => 'CREDIT NOTE INVOICE '. $accountToCredit->client_account_name,
                ];

                Journal::create($journalEntries);
            }

            DB::commit();
            $this->logger->create();
            return redirect()->route('accounts.viewInvoices')->with('success', 'Success! Credit Note created successfully');
        } catch (\Exception $e) {
////            // Rollback the transaction if an exception occurs
            DB::rollback();
////            // Handle or log the exception
            return redirect()->back()->with('error', 'Oops! An error occurred please try again');
        }
    }
    public function fetchAccount(Request $request)
    {
        $data = ClientAccount::join('chart_of_accounts', 'chart_of_accounts.chart_id', '=', 'client_accounts.chart_id')
            ->join('account_sub_categories', 'account_sub_categories.sub_account_id', '=', 'chart_of_accounts.sub_account_id')
            ->join('accounts', 'accounts.account_id', '=', 'account_sub_categories.account_id')
            ->join('currencies', 'currencies.currency_id', '=', 'client_accounts.currency_id')
            ->where('client_account_id', $request->account)
            ->select('client_account_id', 'account_number', 'client_account_number', 'client_account_name', 'opening_date', 'chart_name', 'sub_account_name', 'account_name', 'account_type', 'currency_name', 'currency_symbol', 'sub_category_number', 'chart_number')
            ->first();

        return response()->json($data);

    }

    public function getIncomeStreams(Request $request)
    {
        $currency = ClientAccount::where('client_account_id', $request->account)->first()->currency_id;
        $data = ClientAccount::join('currencies', 'currencies.currency_id', '=', 'client_accounts.currency_id')
            ->where(['client_accounts.currency_id' => $currency])
            ->whereIn('type', [1, 3])
            ->orderBy('client_account_name')
            ->get();

        return response()->json($data);
    }

    public function getExpenseItems(Request $request)
    {
        $currency = ClientAccount::where('client_account_id', $request->account)->first()->currency_id;
        $data = ClientAccount::join('currencies', 'currencies.currency_id', '=', 'client_accounts.currency_id')
            ->where(['client_accounts.currency_id' => $currency])
            ->whereIn('type', [2, 3])
            ->orderBy('client_account_name')
            ->get();

        return response()->json($data);
    }
    public function storeInvoice(Request $request)
    {
        $request->all();
        $invoiceNumber = Invoice::newInvNumber();

        DB::beginTransaction();
        try {
            $invoiceId = (new CustomIds())->generateId();
            $invoice = [
                'invoice_id' => $invoiceId,
                'invoice_number' => $invoiceNumber,
                'client_id' => $request->accountId,
                'date_invoiced' => strtotime($request->invoiceDate),
                'due_date' => strtotime($request->dueDate),
                'customer_message' => $request->customerMessage,
                'container_type' => $request->container,
                'si_number' => $request->siNumber,
                'destination_id' => $request->destination,
                'financial_year_id' => $request->financialYear,
                'amount_due' => $request->amountDue,
                'user_id' => auth()->user()->user_id
            ];

            Invoice::create($invoice);

            foreach ($request->items as $keyItem => $invoice){
                $invoiceItems = [
                    'invoice_item_id' => (new CustomIds())->generateId(),
                    'invoice_id' => $invoiceId,
                    'ledger_id' => $keyItem,
                    'description' => $invoice['description'],
                    'quantity' => $invoice['quantity'],
                    'unit_price' => $invoice['rate'],
                    'tax_id' => $invoice['vatable'] == 0 ? null : $request->taxBracket,
                ];

                InvoiceItem::create($invoiceItems);
            }

            $accountToBill = ClientAccount::where('client_account_id', $request->accountId)->first();

            $journalEntry = [
                'journal_id' => (new CustomIds())->generateId(),
                'invoice_id' => $invoiceId,
                'account_id' => $accountToBill->chart_id,
                'debit' => $request->amountDue,
                'credit' => '0.00',
                'description' => 'INVOICE '.$invoiceNumber,
            ];
            Journal::create($journalEntry);

            if ($request->taxBracket != null){
                $currencyId = ClientAccount::where('client_account_id', $request->accountId)->first()->currency_id;
                $currency = Currency::where('currency_id', $currencyId)->first();
                if ($currency->priority !== 1){
                    $invDate = $request->invoiceDate;
                    $forex = ForexExchange::where('currency_id', $currencyId)->where('date_active', '<=', $invDate)->orderBy('date_active', 'desc')->first();
                    $invoiceAmount = $request->totalTax * $forex->exchange_rate;
                }else{
                    $invoiceAmount = $request->totalTax;
                }
                $vatTax = [
                    'journal_id' => (new CustomIds())->generateId(),
                    'invoice_id' => $invoiceId,
                    'account_id' => $request->taxBracket,
                    'debit' => $invoiceAmount,
                    'credit' => '0.00',
                    'description' => 'INVOICE '.$invoiceNumber,
                ];
                Journal::create($vatTax);
            }

            foreach ($request->items as $keyCredit => $credit){
                $accountToCredit = ClientAccount::where('client_account_id', $keyCredit)->first();
                $journalEntries = [
                    'journal_id' => (new CustomIds())->generateId(),
                    'invoice_id' => $invoiceId,
                    'account_id' => $accountToCredit->chart_id,
                    'debit' => '0.00',
                    'credit' => number_format( $credit['quantity'] * $credit['rate'], 2, '.', ''),
                    'description' => 'INVOICE FOR '. $accountToCredit->client_account_name,
                ];

                Journal::create($journalEntries);
            }

            DB::commit();

            $this->logger->create();
            return redirect()->route('accounts.viewInvoices')->with('success', 'Success! Client invoiced successfully');
        } catch (\Exception $e) {
//            // Rollback the transaction if an exception occurs
            DB::rollback();
//            // Handle or log the exception
            return redirect()->back()->with('error', 'Oops! An error occurred please try again');
        }
    }
    public function viewAllTransactions()
    {
        $transactions = Transaction::join('client_accounts', 'client_accounts.client_account_id', '=', 'transactions.client_id')
            ->join('client_accounts as acc', 'acc.client_account_id', '=', 'transactions.account_id')
            ->join('currencies', 'currencies.currency_id', '=', 'client_accounts.currency_id')
            ->join('financial_years', 'financial_years.financial_year_id', '=', 'transactions.financial_year_id')
            ->select('transaction_id', 'invoice_number', 'transaction_code', 'client_accounts.client_account_name', 'amount_received', 'acc.client_account_name as account', 'year_starting', 'year_ending', 'date_received', 'acc.type')
            ->orderBy('transactions.created_at', 'desc')
            ->get();
        $years = FinancialYear::orderBy('year_starting', 'desc')->get()->map(function($year) {
            $formattedYear = Carbon::parse($year->year_starting)->format('Y') == Carbon::parse($year->year_ending)->format('Y')
                ? Carbon::parse($year->year_starting)->format('Y')
                : Carbon::parse($year->year_starting)->format('Y').'/'.Carbon::parse($year->year_ending)->format('y');

            return [
                'financial_year_id' => $year->financial_year_id, // Assuming there's an 'id' field
                'financial_year' => $formattedYear,
                'year_starting' => $year->year_starting,
                'year_ending' => $year->year_ending,
            ];
        });

        $accounts = ClientAccount::join('chart_of_accounts', 'chart_of_accounts.chart_id', '=', 'client_accounts.chart_id')
            ->join('account_sub_categories', 'account_sub_categories.sub_account_id', '=', 'chart_of_accounts.sub_account_id')
            ->join('accounts', 'accounts.account_id', '=', 'account_sub_categories.account_id')
            ->join('currencies', 'currencies.currency_id', '=', 'client_accounts.currency_id')
            ->select('client_account_id', 'client_account_number', 'client_account_name', 'opening_date', 'chart_name', 'sub_account_name', 'account_name', 'account_type', 'currency_name', 'currency_symbol')
            ->orderBy('client_account_name', 'asc')
            ->get();

        return view('account::sales.transactions')->with(['transactions' => $transactions, 'accounts' => $accounts, 'years' => $years]);

    }

    public function downloadPaymentReceipt($id)
    {
        $payment = Transaction::join('client_accounts', 'client_accounts.client_account_id', '=', 'transactions.client_id')
            ->join('client_accounts as account', 'account.client_account_id', '=', 'transactions.account_id')
            ->join('chart_of_accounts', 'chart_of_accounts.chart_id', '=', 'account.chart_id')
            ->join('financial_years', 'financial_years.financial_year_id', '=', 'transactions.financial_year_id')
            ->join('user_infos', 'user_infos.user_id', '=', 'transactions.user_id')
            ->select('invoice_number', 'client_accounts.client_account_name as clientName', 'chart_of_accounts.chart_name', 'transaction_code', 'year_starting', 'year_ending', 'date_received', 'amount_received', 'transactions.description', 'first_name', 'surname')
            ->where('transactions.transaction_id', $id)
            ->first();
        $type = 'PAYMENT';
        $fYear = Carbon::parse($payment->year_starting)->format('Y') == Carbon::parse($payment->year_ending)->format('Y') ? Carbon::parse($payment->year_starting)->format('Y') : Carbon::parse($payment->year_starting)->format('Y').'/'.Carbon::parse($payment->year_ending)->format('y');

        $invoice = new TemplateProcessor(storage_path('payment_invoice.docx'));
        $invoice->setValue('clientName', $payment->clientName);
        $invoice->setValue('invNumber', $payment->invoice_number);
        $invoice->setValue('invMethod', $payment->chart_name);
        $invoice->setValue('transCode', $payment->transaction_code);
        $invoice->setValue('fYear', $fYear);
        $invoice->setValue('type', $type);
        $invoice->setValue('invAmount', number_format($payment->amount_received, 2));
        $invoice->setValue('description', $payment->description);
        $invoice->setValue('printer', auth()->user()->user->surname.' '.auth()->user()->user->first_name);
        $invoice->setValue('user', $payment->surname.' '.$payment->first_name);
        $invoice->setValue('date', Carbon::now()->format('D, d M Y H:i:s'));
        $invoice->setValue('invDate', Carbon::createFromTimestamp($payment->date_received)->format('d/m/Y'));
        $docPath = 'Files/'.$payment->invoice_number.'.docx';
        $invoice->saveAs($docPath);

        $phpWord = IOFactory::load($docPath);
        $contents = \PhpOffice\PhpWord\IOFactory::load($docPath);
        $pdfPath = 'Files/TempFiles/'.$payment->invoice_number. ".pdf";
        $converter =  new OfficeConverter($docPath, 'Files/TempFiles/');
        $converter->convertTo($payment->invoice_number.".pdf");
        unlink($docPath);
        return response()->download($pdfPath)->deleteFileAfterSend(true);
    }
    public function getPaymentMethods(Request $request)
    {
        $clientAccount = ClientAccount::find($request->clientAccount);
        $data = ClientAccount::join('currencies', 'currencies.currency_id', '=', 'client_accounts.currency_id')
            ->select('currencies.currency_symbol', 'currencies.currency_name', 'client_accounts.*')
            ->where(['type' => 4, 'client_accounts.currency_id' => $clientAccount->currency_id])->get();
        return response()->json($data);
    }
    public function storePaymentInvoice(Request $request)
    {
        $request->validate([
            'clientAccount' => 'string|required',
            'amountReceived' => 'required',
            'dateReceived' => 'required',
            'description' => 'required',
            'financialYear' => 'required|string',
            'account' => 'required|string',
            'transaction' => 'nullable|string|unique:transactions,transaction_code'
        ]);
        DB::beginTransaction();
        try {
            $inv = [
                'transaction_id' => (new CustomIds())->generateId(),
                'invoice_number' => Transaction::newPayInvNumber(),
                'client_id' => $request->get('clientAccount'),
                'date_received' => strtotime($request->get('dateReceived')),
                'amount_received' => $request->get('amountReceived'),
                'financial_year_id' => $request->get('financialYear'),
                'description' => $request->get('description'),
                'user_id' => auth()->user()->user_id,
                'transaction_code' => $request->transaction,
                'account_id' => $request->account
            ];

            Transaction::create($inv);
            $transactions = Transaction::where(['financial_year_id' => $request->financialYear, 'client_id' => $request->clientAccount])->sum('amount_received');
            // Retrieve all pending invoices sorted by due amount (ascending)
            $pendingInvoices = Invoice::where(['status' => 0, 'client_id' => $request->clientAccount, 'financial_year_id' => $request->financialYear])->orderBy('created_at', 'asc')->get();
            $paymentAmount = $transactions;
            foreach ($pendingInvoices as $invoice) {
                if ($paymentAmount >= $invoice->amount_due) {
                    $paymentAmount -= $invoice->amount_due;
                    $invoice->update(['status' => 1]);
                }else{
                    break;
                }
            }

            DB::commit();

            $this->logger->create();
            return redirect()->back()->with('success', 'Success! Payment Invoice Created Successfully');
        } catch (\Exception $e) {
            //            // Rollback the transaction if an exception occurs
            DB::rollback();
            //            // Handle or log the exception
            return redirect()->back()->with('error', 'Oops! An error occurred please try again');
        }
    }
    public function salesFYTaxes()
    {
        $fyIds = Invoice::pluck('financial_year_id')->toArray();
        $years = FinancialYear::whereIn('financial_year_id', $fyIds)->orderBy('year_starting', 'desc')->get()->map(function($year) {
            $formattedYear = Carbon::parse($year->year_starting)->format('Y') == Carbon::parse($year->year_ending)->format('Y')
                ? Carbon::parse($year->year_starting)->format('Y')
                : Carbon::parse($year->year_starting)->format('Y').'/'.Carbon::parse($year->year_ending)->format('y');

            return [
                'financial_year_id' => $year->financial_year_id, // Assuming there's an 'id' field
                'financial_year' => $formattedYear,
                'year_starting' => $year->year_starting,
                'year_ending' => $year->year_ending,
            ];
        });

        return view('account::sales.salesFYTaxes')->with('years', $years);
    }
    public function yearlyTaxes($id)
    {
        $taxes = Journal::join('tax_brackets', 'tax_brackets.tax_bracket_id', '=', 'journals.account_id')
            ->join('invoices', 'invoices.invoice_id', '=', 'journals.invoice_id')
            ->join('taxes', 'taxes.tax_id', '=', 'tax_brackets.tax_id')
            ->where('financial_year_id', $id)
            ->select('tax_bracket_id', 'tax_name', 'tax_rate')
            ->get()
            ->groupBy('tax_bracket_id');

        return view('account::sales.yearTaxes')->with(['taxes' => $taxes, 'id' => $id]);
    }
    public function taxStatement($id)
    {
        list($year, $taxId) = explode(':', base64_decode($id));
       $statements = DB::table('taxstatement')
            ->join('tax_brackets', 'tax_brackets.tax_bracket_id', '=', 'taxstatement.client_id')
            ->join('taxes', 'taxes.tax_id', '=', 'tax_brackets.tax_id')
            ->join('financial_years', 'financial_years.financial_year_id', '=', 'taxstatement.financial_year_id')
            ->where(['taxstatement.financial_year_id' => $year, 'client_id' => $taxId])
            // ->where(function($query) {
            //        $query->where('debit', '>', 0)
            //            ->orWhere('credit', '>', 0);
            //    })
            ->orderBy('date_invoiced', 'asc')
            ->get();
        $fy = FinancialYear::find($year);

        $years = FinancialYear::where('financial_year_id', $year)->orderBy('year_starting', 'desc')->get()->map(function($year) {
            $formattedYear = Carbon::parse($year->year_starting)->format('Y') == Carbon::parse($year->year_ending)->format('Y')
                ? Carbon::parse($year->year_starting)->format('Y')
                : Carbon::parse($year->year_starting)->format('Y').'/'.Carbon::parse($year->year_ending)->format('y');

            return [
                'financial_year_id' => $year->financial_year_id, // Assuming there's an 'id' field
                'financial_year' => $formattedYear,
                'year_starting' => $year->year_starting,
                'year_ending' => $year->year_ending,
            ];
        });

        $taxIds = Journal::join('tax_brackets', 'tax_brackets.tax_bracket_id', '=', 'journals.account_id')->join('invoices', 'invoices.invoice_id', '=', 'journals.invoice_id')->join('taxes', 'taxes.tax_id', '=', 'tax_brackets.tax_id')->where('financial_year_id', $year)->pluck('tax_bracket_id')->toArray();
        $taxAccounts = TaxBrackets::join('taxes', 'taxes.tax_id', '=', 'tax_brackets.tax_id')->whereIn('tax_brackets.tax_bracket_id', $taxIds)->get();

        $accounts = ClientAccount::join('chart_of_accounts', 'chart_of_accounts.chart_id', '=', 'client_accounts.chart_id')
            ->join('account_sub_categories', 'account_sub_categories.sub_account_id', '=', 'chart_of_accounts.sub_account_id')
            ->join('accounts', 'accounts.account_id', '=', 'account_sub_categories.account_id')
            ->join('currencies', 'currencies.currency_id', '=', 'client_accounts.currency_id')
            ->select('client_account_id', 'client_account_number', 'client_account_name', 'opening_date', 'chart_name', 'sub_account_name', 'account_name', 'account_type', 'currency_name', 'currency_symbol', 'type')
            ->where(['client_accounts.type' => 4, 'priority' => 1])
            ->orderBy('client_account_name', 'asc')
            ->get();
        return view('account::sales.taxStatement')->with(['statements' => $statements, 'fy' => $fy, 'years' => $years, 'taxAccounts' => $taxAccounts, 'accounts' => $accounts]);
    }
    public function viewPurchases()
    {
        $invoices = Purchase::join('client_accounts', 'client_accounts.client_account_id', '=', 'purchases.client_id')
            ->join('currencies', 'currencies.currency_id', '=', 'client_accounts.currency_id')
            ->join('financial_years', 'financial_years.financial_year_id', '=', 'purchases.financial_year_id')
            ->select('purchases.purchase_id', 'currency_symbol', 'client_accounts.client_account_name as clientAccount', 'invoice_number', 'date_invoiced', 'due_date', 'purchases.financial_year_id','amount_due', 'year_starting', 'year_ending', 'voucher_number', 'kra_number', 'posted')
            ->orderBy('purchases.created_at', 'desc')
            ->get();;
        return view('account::purchases.index')->with(['invoices' => $invoices]);
    }

    public function addPurchaseInvoice()
    {
        $accounts = ClientAccount::join('chart_of_accounts', 'chart_of_accounts.chart_id', '=', 'client_accounts.chart_id')
            ->join('account_sub_categories', 'account_sub_categories.sub_account_id', '=', 'chart_of_accounts.sub_account_id')
            ->join('accounts', 'accounts.account_id', '=', 'account_sub_categories.account_id')
            ->join('currencies', 'currencies.currency_id', '=', 'client_accounts.currency_id')
            ->select('client_account_id', 'client_account_number', 'client_account_name', 'opening_date', 'chart_name', 'sub_account_name', 'account_name', 'account_type', 'currency_name', 'currency_symbol')
            ->orderBy('client_account_name', 'asc')
            ->whereNull('type')
            ->get();

        $financialYears = FinancialYear::orderBy('year_starting', 'desc')->get()->map(function($year) {
            $formattedYear = Carbon::parse($year->year_starting)->format('Y') == Carbon::parse($year->year_ending)->format('Y')
                ? Carbon::parse($year->year_starting)->format('Y')
                : Carbon::parse($year->year_starting)->format('Y').'/'.Carbon::parse($year->year_ending)->format('y');

            return [
                'financial_year_id' => $year->financial_year_id, // Assuming there's an 'id' field
                'financial_year' => $formattedYear
            ];
        });

        $taxes = TaxBrackets::join('taxes', 'taxes.tax_id', '=', 'tax_brackets.tax_id')->where(['tax_brackets.status' => 1])->orderBy('tax_name', 'asc')->get();
        $debtors =  $accounts->where('account_type', 1);
        $items = $accounts->where('account_type', 2);
        return view('account::purchases.addVoucher')->with(['debtors' => collect($debtors), 'items' => collect($items), 'financialYears' => $financialYears, 'taxes' => $taxes]);
    }
    public function fetchPurchaseInvNumber(Request $request)
    {
        $data = Purchase::where('invoice_number', $request->invoiceNumber)->exists();
        return response()->json(['exists' => $data]);
    }
    public function storePurchaseInvoice(Request $request)
    {
       $voucherNumber = Purchase::newPINumber();
        // return $request->all();
        DB::beginTransaction();
        try {
            $invoiceId = (new CustomIds())->generateId();
            $invoice = [
                'purchase_id' => $invoiceId,
                'voucher_number' => $voucherNumber,
                'invoice_number' => $request->invoiceNumber,
                'client_id' => $request->accountId,
                'tax_id' => $request->withHoldingTax,
                'date_invoiced' => strtotime($request->invoiceDate),
                'due_date' => strtotime($request->dueDate),
                'customer_message' => $request->customerMessage,
                'financial_year_id' => $request->financialYear,
                'amount_due' => $request->amountDue,
                'user_id' => auth()->user()->user_id
            ];

            Purchase::create($invoice);

            foreach ($request->items as $invoice){
              $invoiceItems = [
                    'purchase_item_id' => (new CustomIds())->generateId(),
                    'purchase_id' => $invoiceId,
                    'ledger_id' => $invoice['client_id'],
                    'description' => $invoice['description'],
                    'quantity' => $invoice['quantity'],
                    'unit_price' => $invoice['rate'],
                    'tax_id' => $invoice['vatable'] == 0 ? null : $request->taxBracket,
                ];

                PurchaseItem::create($invoiceItems);
            }

            $accountToBill = ClientAccount::where('client_account_id', $request->accountId)->first();

            $journalEntry = [
                'purchase_journal_id' => (new CustomIds())->generateId(),
                'purchase_id' => $invoiceId,
                'account_id' => $accountToBill->chart_id,
                'debit' => '0.00',
                'credit' => $request->amountDue,
                'description' => 'VOUCHER NUMBER '.$voucherNumber,
            ];

            PurchaseJournal::create($journalEntry);

            if ($request->taxBracket != null){

                $currencyId = ClientAccount::where('client_account_id', $request->accountId)->first()->currency_id;
                $currency = Currency::where('currency_id', $currencyId)->first();
                if ($currency->priority !== 1){
                    $invDate = $request->invoiceDate;
                    $forex = ForexExchange::where('currency_id', $currencyId)->where('date_active', '<=', $invDate)->orderBy('date_active', 'desc')->first();
                    $invoiceAmount = $request->withHoldingTaxTotal * $forex->exchange_rate;
                    $totalVATAmount = $request->totalTax * $forex->exchange_rate;
                }else{
                    $invoiceAmount = $request->withHoldingTaxTotal;
                    $totalVATAmount = $request->totalTax;
                }

                $whTax = [
                    'purchase_journal_id' => (new CustomIds())->generateId(),
                    'purchase_id' => $invoiceId,
                    'account_id' => $request->withHoldingTax,
                    'credit' => '0.00',
                    'debit' => $invoiceAmount,
                    'description' => 'VOUCHER NUMBER '.$voucherNumber,
                ];

                PurchaseJournal::create($whTax);

                $vatTax = [
                    'purchase_journal_id' => (new CustomIds())->generateId(),
                    'purchase_id' => $invoiceId,
                    'account_id' => $request->taxBracket,
                    'debit' => '0.00',
                    'credit' => $totalVATAmount,
                    'description' => 'VOUCHER NUMBER '.$voucherNumber,
                ];

                PurchaseJournal::create($vatTax);
            }

            foreach ($request->items as $credit){
                $accountToCredit = ClientAccount::where('client_account_id', $credit['client_id'])->first();
                $journalEntries = [
                    'purchase_journal_id' => (new CustomIds())->generateId(),
                    'purchase_id' => $invoiceId,
                    'account_id' => $accountToCredit->chart_id,
                    'debit' => number_format( $credit['quantity'] * $credit['rate'], 2, '.', ''),
                    'credit' => '0.00',
                    'description' => 'INVOICE FOR '. $accountToCredit->client_account_name,
                ];

                PurchaseJournal::create($journalEntries);
            }

            // Commit the transaction
            DB::commit();

            $this->logger->create();
            return redirect()->route('accounts.viewPurchases')->with('success', 'Success! Purchase invoiced successfully');
        } catch (\Exception $e) {
            // Rollback the transaction if an exception occurs
            DB::rollback();
            // Handle or log the exception
            return redirect()->back()->with('error', 'Oops! An error occurred please try again');
        }

    }

    public function downloadPurchaseVoucher($id)
    {
        $purchases = Purchase::join('purchase_items', 'purchase_items.purchase_id', '=', 'purchases.purchase_id')
            ->join('client_accounts', 'client_accounts.client_account_id', '=', 'purchase_items.ledger_id')
            ->join('currencies', 'currencies.currency_id', '=', 'client_accounts.currency_id')
            ->join('financial_years', 'financial_years.financial_year_id', '=', 'purchases.financial_year_id')
            ->leftJoin('tax_brackets', 'tax_brackets.tax_bracket_id', '=', 'purchase_items.tax_id')
            ->leftJoin('tax_brackets as tb', 'tb.tax_bracket_id', '=', 'purchases.tax_id')
            ->leftJoin('taxes', 'taxes.tax_id', '=', 'tax_brackets.tax_id')
            ->select('client_account_name as account_name', 'tb.tax_rate as taxRate', 'tax_name', 'tax_brackets.tax_rate', 'quantity', 'unit_price', 'currency_symbol', 'purchase_items.description', 'date_invoiced', 'due_date', 'year_starting', 'year_ending', 'voucher_number', 'invoice_number', 'user_id')
            ->where('purchases.purchase_id', $id)
            ->orderBy('client_accounts.client_account_name', 'ASC')
            ->get();

        $account = ClientAccount::join('purchases', 'purchases.client_id', '=', 'client_accounts.client_account_id')
            ->join('currencies', 'currencies.currency_id', '=', 'client_accounts.currency_id')
            ->where('purchase_id', $id)->first();
        $values = $purchases->first();

        $user = UserInfo::where('user_id', $values->user_id)->first();

        $type = 'PURCHASE';
        $narration = 'INVOICE TO BE SETTLED BY OR BEFORE '. Carbon::createFromTimestamp($values->due_date)->format('D, d-m-Y');

        $fYear = Carbon::parse($values->year_starting)->format('Y') == Carbon::parse($values->year_ending)->format('Y') ? Carbon::parse($values->year_starting)->format('Y') : Carbon::parse($values->year_starting)->format('Y').'/'.Carbon::parse($values->year_ending)->format('y');

        $domPdfPath = base_path('vendor/dompdf/dompdf');
        Settings::setPdfRendererPath($domPdfPath);
        Settings::setPdfRendererName('DomPDF');

        $table = new Table(['unit' => \PhpOffice\PhpWord\SimpleType\TblWidth::PERCENT, 'width' => 100 * 50, 'align' => 'center']);

        $header = ['size' => 10, 'name' => 'Cambria', 'space' => ['before' => 100, 'after' => 100], 'bold' => true];
        $text = ['size' => 9, 'name' => 'Cambria', 'space' => ['before' => 100, 'after' => 100], 'bold' => false];

        $table->addRow();
        $table->addCell(500, ['borderSize' => 1, 'align' => 'center', 'bgColor' => 'cccccc'])->addText('#', $header, ['space' => ['before' => 100, 'after' => 100]]);
        $table->addCell(2500, ['borderSize' => 1, 'align' => 'center', 'bgColor' => 'cccccc'])->addText('INVOICE ITEM', $header, ['space' => ['before' => 100, 'after' => 100]]);
        $table->addCell(1200, ['borderSize' => 1, 'align' => 'center', 'bgColor' => 'cccccc'])->addText('DEBIT', $header, ['space' => ['before' => 100, 'after' => 100]]);
        $table->addCell(1200, ['borderSize' => 1, 'align' => 'center', 'bgColor' => 'cccccc'])->addText('CREDIT', $header, ['space' => ['before' => 100, 'after' => 100]]);

        $amountDue = 0;
        $totalTax = 0;
        $withHoldingTax = 0;

        foreach ($purchases as $key => $purchase) {
            $table->addRow();
            $table->addCell(500, ['borderSize' => 1, 'align' => 'center'])->addText(++$key, $text, ['space' => ['before' => 100, 'after' => 100]]);
            $table->addCell(2500, ['borderSize' => 1, 'align' => 'center'])->addText($purchase->account_name, $text, ['space' => ['before' => 100, 'after' => 100]]);
            $table->addCell(1500, ['borderSize' => 1, 'align' => 'center'])->addText($purchase->currency_symbol.' '.number_format($purchase->quantity * $purchase->unit_price, 2), $text, ['space' => ['before' => 100, 'after' => 100]]);
            $table->addCell(1500, ['borderSize' => 1, 'align' => 'center'])->addText($purchase->currency_symbol.' '.number_format(0, 2), $text, ['space' => ['before' => 100, 'after' => 100]]);

            $taxRate = $purchase->tax_rate == null ? 0 : $purchase->tax_rate;
            $totalTax +=  floatval($taxRate)/100 * ($purchase->quantity * $purchase->unit_price);
            $amountDue += $purchase->quantity * $purchase->unit_price;

        }
        $withHoldingTax = ($amountDue) * $purchases[0]['taxRate']/100;

        $table->addRow();
        $table->addCell(500, ['borderSize' => 1, 'align' => 'center'])->addText($purchases->count() + 1, $text, ['space' => ['before' => 100, 'after' => 100]]);
        $table->addCell(2500, ['borderSize' => 1, 'align' => 'center'])->addText('VALUE ADDED TAX', $text, ['space' => ['before' => 100, 'after' => 100]]);
        $table->addCell(2500, ['borderSize' => 1, 'align' => 'center'])->addText($purchase->currency_symbol.' '.number_format($totalTax, 2), $text, ['space' => ['before' => 100, 'after' => 100]]);
        $table->addCell(2500, ['borderSize' => 1, 'align' => 'center'])->addText($purchase->currency_symbol.' '.number_format(0, 2), $text, ['space' => ['before' => 100, 'after' => 100]]);
        $table->addRow();
        $table->addCell(500, ['borderSize' => 1, 'align' => 'center'])->addText($purchases->count() + 2, $text, ['space' => ['before' => 100, 'after' => 100]]);
        $table->addCell(2500, ['borderSize' => 1, 'align' => 'center'])->addText('WITHHOLDING TAX', $text, ['space' => ['before' => 100, 'after' => 100]]);
        $table->addCell(2500, ['borderSize' => 1, 'align' => 'center'])->addText($purchase->currency_symbol.' '.number_format(0, 2), $text, ['space' => ['before' => 100, 'after' => 100]]);
        $table->addCell(2500, ['borderSize' => 1, 'align' => 'center'])->addText($purchase->currency_symbol.' '.number_format($withHoldingTax, 2), $text, ['space' => ['before' => 100, 'after' => 100]]);

        $table->addRow();
        $table->addCell(null, [ 'gridSpan' => 2])->addText('SUBTOTALS', $header, ['align' => 'center', 'size' => 7, 'space' => ['before' => 100, 'after' => 100]]);
        $table->addCell(null, ['gridSpan' => 1, 'borderBottomSize' => 1])->addText($purchase->currency_symbol.' '.number_format($amountDue + $totalTax, 2), $header, ['size' => 8, 'space' => ['before' => 100, 'after' => 100]]);
        $table->addCell(null, ['gridSpan' => 1, 'borderBottomSize' => 1])->addText($purchase->currency_symbol.' '.number_format($withHoldingTax, 2), $header, ['size' => 8, 'space' => ['before' => 100, 'after' => 100]]);

        $table->addRow();
        $table->addCell(null, [ 'gridSpan' => 2, 'align' => 'center'])->addText('AMOUNT DUE', $header, ['align' => 'center', 'size' => 7, 'space' => ['before' => 100, 'after' => 100]]);
        $table->addCell(null, ['gridSpan' => 2, 'borderBottomSize' => 1, 'align' => 'center'])->addText($purchase->currency_symbol.' '.number_format($totalTax + $amountDue - $withHoldingTax, 2), $header, ['align' => 'center', 'size' => 8, 'space' => ['before' => 100, 'after' => 100]]);

        $invoice = new TemplateProcessor(storage_path('purchase_voucher.docx'));
        $invoice->setComplexBlock('{table}', $table);
        $invoice->setValue('clientName', $account->client_account_name);
        $invoice->setValue('invNumber', $values->invoice_number);
        $invoice->setValue('vouNumber', $values->voucher_number);
        $invoice->setValue('fYear', $fYear);
        $invoice->setValue('type', $type);
        $invoice->setValue('narration', $narration);
        $invoice->setValue('printer', auth()->user()->user->surname.' '.auth()->user()->user->first_name);
        $invoice->setValue('user', $user->surname.' '.$user->first_name);
        $invoice->setValue('date', Carbon::now()->format('D, d M Y H:i:s'));
        $invoice->setValue('invDate', Carbon::createFromTimestamp($values->date_invoiced)->format('d/m/Y'));
        $invoice->setValue('dueDate', Carbon::createFromTimestamp($values->due_date)->format('d/m/Y'));
        $docPath = 'Files/'.$values->voucher_number.'.docx';
        $invoice->saveAs($docPath);
        //  return response()->download($docPath)->deleteFileAfterSend(true);
        $phpWord = IOFactory::load($docPath);
        $contents = \PhpOffice\PhpWord\IOFactory::load($docPath);
        $pdfPath = 'Files/TempFiles/'.$values->voucher_number. ".pdf";
        $converter =  new OfficeConverter($docPath, 'Files/TempFiles/');
        $converter->convertTo($values->voucher_number.".pdf");
        unlink($docPath);
        return response()->download($pdfPath)->deleteFileAfterSend(true);
    }

    public function viewPurchaseInvoice($id) {
        $invoices = Purchase::join('purchase_items', 'purchase_items.purchase_id', '=', 'purchases.purchase_id')
            ->join('client_accounts', 'client_accounts.client_account_id', '=', 'purchase_items.ledger_id')
            ->join('currencies', 'currencies.currency_id', '=', 'client_accounts.currency_id')
            ->leftJoin('tax_brackets', 'tax_brackets.tax_bracket_id', '=', 'purchase_items.tax_id')
            ->leftJoin('tax_brackets as tb', 'tb.tax_bracket_id', '=', 'purchases.tax_id')
            ->leftJoin('taxes', 'taxes.tax_id', '=', 'tax_brackets.tax_id')
            ->select('client_account_name as account_name', 'tb.tax_rate as taxRate', 'tax_name', 'tax_brackets.tax_rate', 'quantity', 'unit_price', 'currency_symbol', 'purchase_items.description')
            ->where('purchases.purchase_id', $id)
            ->orderBy('client_accounts.client_account_name', 'ASC')
            ->get();

        $account = ClientAccount::join('purchases', 'purchases.client_id', '=', 'client_accounts.client_account_id')
            ->join('currencies', 'currencies.currency_id', '=', 'client_accounts.currency_id')
            ->where('purchase_id', $id)->first();

        return view('account::purchases.viewVoucher')->with(['invoices' => $invoices, 'account' => $account]);
    }
    public function deletePurchaseInvoice($id)
    {
        Purchase::find($id)->delete();
        PurchaseItem::where('purchase_id', $id)->delete();
        return redirect()->back()->with('success', 'Success! Invoice successfully deleted');
    }
    public function purchaseFYTaxes()
    {
        $fyIds = Invoice::pluck('financial_year_id')->toArray();
        $years = FinancialYear::whereIn('financial_year_id', $fyIds)->orderBy('year_starting', 'desc')->get()->map(function($year) {
            $formattedYear = Carbon::parse($year->year_starting)->format('Y') == Carbon::parse($year->year_ending)->format('Y')
                ? Carbon::parse($year->year_starting)->format('Y')
                : Carbon::parse($year->year_starting)->format('Y').'/'.Carbon::parse($year->year_ending)->format('y');

            return [
                'financial_year_id' => $year->financial_year_id, // Assuming there's an 'id' field
                'financial_year' => $formattedYear,
                'year_starting' => $year->year_starting,
                'year_ending' => $year->year_ending,
            ];
        });

        return view('account::purchases.purchaseTaxes')->with('years', $years);
    }
    public function yearlyPurchaseTaxes($id)
    {
        $taxes = PurchaseJournal::join('tax_brackets', 'tax_brackets.tax_bracket_id', '=', 'purchase_journals.account_id')
            ->join('purchases', 'purchases.purchase_id', '=', 'purchase_journals.purchase_id')
            ->join('taxes', 'taxes.tax_id', '=', 'tax_brackets.tax_id')
            ->where('financial_year_id', $id)
            ->select('tax_bracket_id', 'tax_name', 'tax_rate')
            ->get()
            ->groupBy('tax_bracket_id');

        return view('account::purchases.yearTaxes')->with(['taxes' => $taxes, 'id' => $id]);
    }
    public function purchaseTaxStatement($id)
    {
        list($year, $taxId) = explode(':', base64_decode($id));
        $statements = DB::table('purchasetaxstatement')
            ->join('tax_brackets', 'tax_brackets.tax_bracket_id', '=', 'purchasetaxstatement.client_id')
            ->join('taxes', 'taxes.tax_id', '=', 'tax_brackets.tax_id')
            ->join('financial_years', 'financial_years.financial_year_id', '=', 'purchasetaxstatement.financial_year_id')
            ->where(['purchasetaxstatement.financial_year_id' => $year, 'client_id' => $taxId])
            ->where(function($query) {
                $query->where('debit', '>', 0)
                    ->orWhere('credit', '>', 0);
            })
            ->orderBy('date_invoiced', 'asc')
            ->get();
        $fy = FinancialYear::find($year);

        $years = FinancialYear::where('financial_year_id', $year)->orderBy('year_starting', 'desc')->get()->map(function($year) {
            $formattedYear = Carbon::parse($year->year_starting)->format('Y') == Carbon::parse($year->year_ending)->format('Y')
                ? Carbon::parse($year->year_starting)->format('Y')
                : Carbon::parse($year->year_starting)->format('Y').'/'.Carbon::parse($year->year_ending)->format('y');

            return [
                'financial_year_id' => $year->financial_year_id, // Assuming there's an 'id' field
                'financial_year' => $formattedYear,
                'year_starting' => $year->year_starting,
                'year_ending' => $year->year_ending,
            ];
        });

        $taxIds = PurchaseJournal::join('tax_brackets', 'tax_brackets.tax_bracket_id', '=', 'purchase_journals.account_id')->join('purchases', 'purchases.purchase_id', '=', 'purchase_journals.purchase_id')->join('taxes', 'taxes.tax_id', '=', 'tax_brackets.tax_id')->where('financial_year_id', $year)->pluck('tax_bracket_id')->toArray();
        $taxAccounts = TaxBrackets::join('taxes', 'taxes.tax_id', '=', 'tax_brackets.tax_id')->whereIn('tax_brackets.tax_bracket_id', $taxIds)->get();

        $accounts = ClientAccount::join('chart_of_accounts', 'chart_of_accounts.chart_id', '=', 'client_accounts.chart_id')
            ->join('account_sub_categories', 'account_sub_categories.sub_account_id', '=', 'chart_of_accounts.sub_account_id')
            ->join('accounts', 'accounts.account_id', '=', 'account_sub_categories.account_id')
            ->join('currencies', 'currencies.currency_id', '=', 'client_accounts.currency_id')
            ->select('client_account_id', 'client_account_number', 'client_account_name', 'opening_date', 'chart_name', 'sub_account_name', 'account_name', 'account_type', 'currency_name', 'currency_symbol')
            ->where(['type' => 4, 'priority' => 1])
            ->orderBy('client_account_name', 'asc')
//            ->whereNull('client_accounts.type')
            ->get();
        return view('account::purchases.taxStatement')->with(['statements' => $statements, 'fy' => $fy, 'years' => $years, 'taxAccounts' => $taxAccounts, 'accounts' => $accounts]);
    }
    public function viewAccounts()
    {
        $accounts = Account::latest()->get();
        return view('account::accounts.viewAccounts')->with('accounts', $accounts);
    }
    public function registerAccount(Request $request)
    {
        $request->validate([
            'account_name' => 'string|required|unique:accounts,account_name',
            'account_type' => 'numeric|required'
        ]);

        $accNumber = Account::withTrashed()->count();
        $newAccount = ($accNumber + 1) * 1000;
        $account = [
            'account_id' => (new CustomIds())->generateId(),
            'account_number' => $newAccount,
            'account_name' => $request->account_name,
            'account_type' =>  $request->account_type,
            'description' =>  $request->description,
        ];

        Account::create($account);
        $this->logger->create();

        return back()->with('success', 'Successful! Account created successfully');
    }
    public function updateAccount(Request $request,$id)
    {
        $request->validate([
            'account_name' => 'string|required|unique:accounts,account_name,'.$id.',account_id',
            'account_type' => 'numeric|required',
            'account_status' => 'numeric|required'
        ]);

        $account = [
            'account_name' => $request->account_name,
            'account_type' =>  $request->account_type,
            'status' =>  $request->account_status,
            'description' =>  $request->description,
        ];

        Account::where('account_id', $id)->update($account);
        $this->logger->create();
        return back()->with('success', 'Successful! Account updated successfully');
    }
    public function deleteAccount($id)
    {
        Account::where('account_id', $id)->delete();
        $this->logger->create();
        return back()->with('success', 'Successful! Account deleted successfully');
    }
    public function accountSubCategories()
    {
        $categories = Account::withoutTrashed()->orderBy('account_number', 'asc')->get();
        $accounts = AccountSubCategories::join('accounts', 'accounts.account_id', '=', 'account_sub_categories.account_id')
            ->withoutTrashed()
            ->select('accounts.account_id', 'accounts.account_name', 'account_sub_categories.sub_account_id', 'account_sub_categories.sub_category_number', 'account_sub_categories.sub_account_name', 'account_sub_categories.description', 'account_sub_categories.status')
            ->orderBy('sub_category_number', 'asc')->get();
        $currencies = Currency::withoutTrashed()->latest()->get();
        return view('account::accounts.accountSubCategories')->with(['accounts' => $accounts, 'categories' => $categories, 'currencies' => $currencies]);
    }
    public function addAccountSubCategory(Request $request)
    {
        $request->validate([
            'account' => 'required|string',
            'account_name' => 'required|string|unique:account_sub_categories,sub_account_name'
        ]);

        $category = Account::where('account_id', $request->account)->first();
        $subCat = AccountSubCategories::withTrashed()->where('account_id', $request->account)->get();
        $subAccNo = $category->account_number + ($subCat->count() +1) * 100;
        $account = [
            'sub_account_id' => (new CustomIds())->generateId(),
            'sub_category_number' => $subAccNo,
            'account_id' => $request->account,
            'sub_account_name' => $request->account_name,
            'description' => $request->description
        ];
        AccountSubCategories::create($account);
        $this->logger->create();
        return back()->with('success', 'Successful!, Account Subcategory created successfully');
    }
    public function updateAccountSubCategory(Request $request, $id)
    {
        $request->validate([
            'account_category' => 'required|string',
            'account_name' => 'required|string|unique:account_sub_categories,sub_account_name,'.$id.',sub_account_id',
            'status' => 'required'
        ]);

        $account = [
            'account_id' => $request->account_category,
            'sub_account_name' => $request->account_name,
            'description' => $request->description,
            'status' => $request->status
        ];
        AccountSubCategories::where('sub_account_id', $id)->update($account);
        $this->logger->create();
        return back()->with('success', 'Successful!, Account Subcategory updated successfully');
    }
    public function deleteAccountSubCategory($id)
    {
        AccountSubCategories::where('sub_account_id', $id)->delete();
        $this->logger->create();
        return back()->with('success', 'Success! Account Subcategory delete successfully');
    }
    public function viewChartAccounts()
    {
        $accounts = ChartOfAccount::join('account_sub_categories', 'account_sub_categories.sub_account_id', '=', 'chart_of_accounts.sub_account_id')
            ->join('accounts', 'accounts.account_id', '=', 'account_sub_categories.account_id')
            ->select('chart_id', 'chart_number', 'chart_name', 'account_name', 'chart_of_accounts.description', 'chart_of_accounts.status', 'account_sub_categories.sub_account_name', 'account_sub_categories.sub_account_id')
            ->orderBy('chart_number', 'asc')
            ->get();
        $categories = AccountSubCategories::latest()->get();
        $currencies = Currency::latest()->get();
        return view('account::accounts.allAccounts')->with(['accounts' => $accounts, 'categories' => $categories, 'currencies' => $currencies]);
    }
    public function addChartAccount(Request $request)
    {
        $request->validate([
            'account_name' => 'required|string',
            'account_category' => 'required|string',
        ]);

        $exists = ChartOfAccount::where(['chart_name' => $request->account_name, 'sub_account_id' => $request->account_category])->exists();
        if ($exists){
            return back()->with('info', 'Oops! Account already exists');
        }else{

            $existing = ChartOfAccount::withTrashed()->where('sub_account_id', $request->account_category)->count();
            $accNumber = AccountSubCategories::where('sub_account_id', $request->account_category)->first();

            $chart = [
                'chart_id' => (new CustomIds())->generateId(),
                'chart_name' => $request->account_name,
                'chart_number' => $accNumber->sub_category_number + $existing + 1,
                'sub_account_id' => $request->account_category,
                'description' => $request->description
            ];

            ChartOfAccount::create($chart);

        }
        $this->logger->create();
        return back()->with('success', 'Success! Chart of account created successfully');
    }
    public function updateChartAccount(Request $request, $id)
    {
        $request->validate([
            'account_name' => 'required|string',
            'account_category' => 'required|string',
            'status' => 'required|string',
        ]);

        $exists = ChartOfAccount::where(['chart_name' => $request->account_name, 'sub_account_id' => $request->account_category])->where('chart_id', '!=', $id)->exists();

        if ($exists){
            return back()->with('info', 'Oops! Account already exists');
        }else{
            $chart = [
                'chart_name' => $request->account_name,
                'sub_account_id' => $request->account_category,
                'description' => $request->description,
                'status' => $request->status,
            ];

            ChartOfAccount::where('chart_id', $id)->update($chart);
        }
        $this->logger->create();
        return back()->with('success', 'Success! Chart of account created successfully');
    }
    public function deleteChartAccount($id)
    {
        ChartOfAccount::where('chart_id', $id)->delete();
        $this->logger->create();
        return back()->with('success', 'Success! Chart of account delete successfully');
    }
    public function viewClientAccounts()
    {
        $clientsAccounts = ClientAccount::join('chart_of_accounts', 'chart_of_accounts.chart_id', '=', 'client_accounts.chart_id')
            ->join('account_sub_categories', 'account_sub_categories.sub_account_id', '=', 'chart_of_accounts.sub_account_id')
            ->join('accounts', 'accounts.account_id', '=', 'account_sub_categories.account_id')
            ->join('currencies', 'currencies.currency_id', '=', 'client_accounts.currency_id')
            ->select('client_accounts.chart_id', 'client_account_id', 'client_account_number', 'client_account_name', 'client_accounts.currency_id' ,'chart_name', 'currency_name', 'currency_symbol', 'opening_date', 'closing_date', 'client_accounts.description', 'account_sub_categories.sub_account_name', 'type', 'kra_pin', 'client_address')
            ->latest('client_accounts.created_at')
            ->get();

        $categories = ChartOfAccount::all();
        $currencies = Currency::all();
        $client = Client::pluck('client_name');
        $transporter = Transporter::pluck('transporter_name');
        $clients = $client->merge($transporter);
        return view('account::accounts.viewClientAccounts')->with(['clients' => $clients, 'categories' => $categories, 'currencies' => $currencies, 'clientsAccounts' => $clientsAccounts]);
    }
    public function addClientAccount(Request $request)
    {
        $request->validate([
            'account_name' => 'required|string',
            'account_category' => 'required|string',
            'account_currency' => 'required|string'
        ]);

        $exists = ClientAccount::where(['client_account_name' => $request->account_name, 'chart_id' => $request->account_category, 'currency_id' => $request->currency_id])->exists();

        if ($exists){
            return back()->with('info', 'Oops! Account already exists');
        }else{
            $coa = ChartOfAccount::where('chart_id', $request->account_category)->first();
            $ca = ClientAccount::withTrashed()->where(['chart_id' => $request->account_category])->get()->count();
            $accountNumber = $coa->chart_number. str_pad($ca + 1, 3, '0', STR_PAD_LEFT);
            $account = [
                'client_account_id' => (new CustomIds())->generateId(),
                'client_account_number' => $accountNumber,
                'client_account_name' => $request->account_name,
                'currency_id' => $request->account_currency,
                'chart_id' => $request->account_category,
                'description' => $request->description,
                'opening_date' => time(),
                'type' => $request->type,
                'kra_pin' => $request->kraPin,
                'client_address' => $request->client_address
            ];

            ClientAccount::create($account);
            $this->logger->create();
        }

        return back()->with('success', 'Success! Client account created successfully');

    }
    public function updateClientAccount(Request $request, $id)
    {
        $request->validate([
            'account_name' => 'required|string',
            'account_currency' => 'required|string',
//            'type' => 'required|string',
        ]);

        $unique = ClientAccount::where(['client_account_name' => $request->account_name])->where('client_account_id', '!==', $id)->first();

        if ($unique){
            return back()->with('info', 'Another account exists with the same client name exists');
        }

        ClientAccount::where('client_account_id', $id)->update([
            'client_account_name' => $request->account_name,
            'closing_date' => $request->account_status == 1 ? null : time(),
            'currency_id' => $request->account_currency,
            'description' => $request->description,
            'type' => $request->type,
            'kra_pin' => $request->kraPin,
            'client_address' => $request->client_address
        ]);

        $this->logger->create();
        return back()->with('success', 'Success! Client account updated successfully');
    }
    public function deleteClientAccount($id)
    {
        ClientAccount::where('client_account_id', $id)->delete();
        $this->logger->create();
        return back()->with('success', 'Success! Client account created successfully');
    }
    public function exchangeRates()
    {
        $forexes = ForexExchange::join('currencies as exchange', 'exchange.currency_id', '=', 'forex_exchanges.exchange_id')
            ->join('currencies', 'currencies.currency_id', '=', 'forex_exchanges.currency_id')
            ->select('forex_id', 'exchange_rate', 'exchange.currency_name as exchange_currency_name', 'currencies.currency_name as currency', 'exchange.currency_symbol as exchange_currency_symbol', 'date_active', 'forex_exchanges.currency_id')
            ->orderBy('date_active', 'desc')
            ->get();
        $currencies = Currency::where('priority', '!=', 1)->where('status', 1)->orderBy('priority', 'asc')->get();
        return view('account::currencies.exchangeRates')->with(['currencies' => $currencies, 'forexes' => $forexes]);
    }
    public function addCurrencyExchangeRate(Request $request)
    {
        $request->validate([
            'currency_id' => 'required|string',
            'exchange_rate' => 'required|string',
            'date_active' => 'required|date',
        ]);
        $primary = Currency::where('priority', 1)->first()->currency_id;
        $exchange = [
            'forex_id' => (new CustomIds())->generateId(),
            'currency_id' => $request->currency_id,
            'exchange_id' => $primary,
            'exchange_rate' => $request->exchange_rate,
            'date_active' => $request->date_active
        ];
        ForexExchange::create($exchange);
        return back()->with('success', 'Success! Exchange rate added successfully');
    }
    public function updateCurrencyExchangeRate(Request $request, $id)
    {
        $request->validate([
            'currency_id' => 'required|string',
            'exchange_rate' => 'required|string',
            'date_active' => 'required|date',
        ]);

        $primary = Currency::where('priority', 1)->first()->currency_id;
        $exchange = [
            'currency_id' => $request->currency_id,
            'exchange_id' => $primary,
            'exchange_rate' => $request->exchange_rate,
            'date_active' => $request->date_active
        ];
        ForexExchange::find($id)->update($exchange);
        return back()->with('success', 'Success! Exchange rate updated successfully');
    }
    public function deleteCurrencyExchangeRate($id)
    {
        ForexExchange::find($id)->delete();
        return back()->with('success', 'Success! Exchange rate deleted successfully');
    }
    public function viewCurrencies()
    {
        $currencies = Currency::orderBy('priority', 'asc')->get();
        return view('account::currencies.viewCurrency')->with('currencies', $currencies);
    }
    public function addCurrency(Request $request)
    {
        $request->validate([
            'currency_name' => 'required|string|unique:currencies,currency_name',
            'currency_symbol' => 'required|string|unique:currencies,currency_symbol',
            'priority' => 'numeric|required'
        ]);

        if ($request->priority == 1){
            Currency::where(['priority' => 1])->update(['priority' => 2]);;
        }

        $currency = [
            'currency_id' => (new CustomIds())->generateId(),
            'currency_name' => $request->currency_name,
            'currency_symbol' => $request->currency_symbol,
            'priority' => $request->priority
        ];

        Currency::create($currency);
        $this->logger->create();
        return back()->with('success', 'Success! Currency created successfully');
    }
    public function updateCurrency(Request $request, $id)
    {
        $request->validate([
            'currency_name' => 'required|string|unique:currencies,currency_name,'.$id.',currency_id',
            'currency_symbol' => 'required|string|unique:currencies,currency_symbol,'.$id.',currency_id',
            'status' => 'numeric|required',
            'priority' => 'numeric|required'
        ]);

        $currency = [
            'status' => $request->status,
            'currency_name' => $request->currency_name,
            'currency_symbol' => $request->currency_symbol,
            'priority' => $request->priority
        ];

        if ($request->priority == 1){
//            return Currency::whereIn(['priority' => 1])->first();
            Currency::where(['priority' => 1])->update(['priority' => 2]);
        }

        Currency::where('currency_id', $id)->update($currency);
        $this->logger->create();
        return back()->with('success', 'Success! Currency updated successfully');
    }
    public function deleteCurrency($id)
    {
        Currency::where('currency_id', $id)->delete();
        $this->logger->create();
        return back()->with('success', 'Success! Currency deleted successfully');
    }
    public function viewFinancialYears()
    {
        $years = FinancialYear::latest()->get();
        return view('account::years.financialYears')->with('years', $years);
    }
    public function addFinancialYears(Request $request)
    {
        $request->validate([
            'year_starting' => [
                'required',
                'date',
                'before:year_ending'
            ],
            'year_ending' => [
                'required',
                'date',
                'after:year_starting',
            ],
        ]);

        $fy = [
            'financial_year_id' => (new CustomIds())->generateId(),
            'year_starting' => $request->year_starting,
            'year_ending' => $request->year_ending,
        ];

        FinancialYear::create($fy);
        $this->logger->create();
        return back()->with('success', 'Success! Financial year created successfully');
    }
    public function updateFinancialYears(Request $request, $id)
    {
        $request->validate([
            'year_starting' => ['required', 'date', 'before:year_ending'],
            'year_ending' => 'required|date|after:year_starting'
        ]);

        $fy = [
            'status' => $request->status,
            'year_starting' => $request->year_starting,
            'year_ending' => $request->year_ending,
        ];

        FinancialYear::where('financial_year_id', $id)->update($fy);
        $this->logger->create();
        return back()->with('success', 'Success! Financial year updated successfully');

    }
    public function deleteFinancialYears($id)
    {
        FinancialYear::where('financial_year_id', $id)->delete();
        $this->logger->create();
        return back()->with('success', 'Success! Financial year deleted successfully');
    }
    public function viewTaxes()
    {
        return view('account::taxes.taxes')->with(['taxes' => Tax::all()]);
    }
    public function viewTaxBrackets()
    {
        $taxes = TaxBrackets::join('taxes', 'taxes.tax_id', '=', 'tax_brackets.tax_id')
            ->select('tax_name', 'tax_rate', 'tax_bracket_id', 'tax_brackets.status', 'taxes.tax_id')
            ->latest('tax_brackets.created_at')
            ->get();
        return view('account::taxes.taxBrackets')->with(['taxes' => Tax::all(), 'brackets' => $taxes]);
    }
    public function storeTax(Request $request)
    {
        $request->validate(['tax' => 'required|string|unique:taxes,tax_name']);
        $tax = [
            'tax_id' => (new CustomIds())->generateId(),
            'tax_name' => $request->tax,
            'status' => $request->status,
            'effect' => $request->effect
        ];
        Tax::create($tax);
        return redirect()->back()->with('success', 'Success! Tax Created Successfully');
    }
    public function updateTax(Request $request, $id)
    {
        $request->validate(['tax' => 'required|string|unique:taxes,tax_name,'.$id.',tax_id']);
        $tax = [
            'tax_name' => $request->tax,
            'status' => $request->status,
            'effect' => $request->effect
        ];
        Tax::find($id)->update($tax);

        return redirect()->back()->with('success', 'Success! Tax Updated Successfully');
    }
    public function deleteTax($id)
    {
        Tax::find($id)->delete();
        return redirect()->back()->with('success', 'Success! Tax deleted successfully');
    }
    public function storeTaxBracket(Request $request)
    {
        $request->validate(['tax' => 'required|string|unique:tax_brackets,tax_rate']);
        $tax = [
            'tax_bracket_id' => (new CustomIds())->generateId(),
            'tax_id' => $request->tax_id,
            'tax_rate' => $request->tax,
            'status' => $request->status
        ];

        $taxExists =  TaxBrackets::where('tax_id', $request->tax_id)->first();
        if($taxExists){
            $taxExists->update(['status' => 2]);
        }
        TaxBrackets::create($tax);
        return redirect()->back()->with('success', 'Success! Tax Bracket Created Successfully');
    }
    public function updateTaxBracket(Request $request, $id)
    {
        $request->validate(['tax' => 'required|string|unique:tax_brackets,tax_rate,'.$id.',tax_bracket_id']);
        $tax = [
            'tax_id' => $request->tax_id,
            'tax_rate' => $request->tax,
            'status' => $request->status
        ];
        if ($request->tax_id != $id){
            TaxBrackets::where('tax_id', $request->tax_id)->update(['status' => 2]);
        }

        TaxBrackets::find($id)->update($tax);

        return redirect()->back()->with('success', 'Success! Tax Bracket Updated Successfully');
    }
    public function deleteTaxBracket($id)
    {
        TaxBrackets::find($id)->delete();
        return redirect()->back()->with('success', 'Success! Tax Bracket deleted successfully');
    }
    public function getSalesFinancialYears()
    {
        $fyIds = Invoice::pluck('financial_year_id')->toArray();
        $years = FinancialYear::whereIn('financial_year_id', $fyIds)->orderBy('year_starting', 'desc')->get()->map(function($year) {
            $formattedYear = Carbon::parse($year->year_starting)->format('Y') == Carbon::parse($year->year_ending)->format('Y')
                ? Carbon::parse($year->year_starting)->format('Y')
                : Carbon::parse($year->year_starting)->format('Y').'/'.Carbon::parse($year->year_ending)->format('y');

            return [
                'financial_year_id' => $year->financial_year_id, // Assuming there's an 'id' field
                'financial_year' => $formattedYear,
                'year_starting' => $year->year_starting,
                'year_ending' => $year->year_ending,
            ];
        });

        return view('account::reports.sales.financialYears')->with('years', $years);
    }
    public function getClientsSalesWithInvoices($id)
    {
        $invoices = Invoice::join('client_accounts', 'client_accounts.client_account_id', '=', 'invoices.client_id')
            ->join('currencies', 'currencies.currency_id', '=', 'client_accounts.currency_id')
            ->join('financial_years', 'financial_years.financial_year_id', '=', 'invoices.financial_year_id')
            ->select('financial_years.financial_year_id','invoices.invoice_id', 'currency_symbol', 'client_accounts.client_account_name as clientAccount', 'invoice_number', 'date_invoiced', 'due_date', 'client_account_number', 'currency_symbol', 'currencies.currency_id', 'client_account_id', 'amount_due')
            ->where('financial_years.financial_year_id', $id)
            ->orderBy('client_account_number')
            ->whereNull('client_accounts.deleted_at')
            ->get()
            ->groupBy(['client_account_number', 'currency_symbol']);

        return view('account::reports.sales.invoicesPerClient')->with(['invoices' => $invoices]);
    }
    public function downloadClientStatement($id)
    {
        list($client, $fyId) = explode(':', base64_decode($id));
        $statements = Db::table('accountstatements')->where(['client_id' => $client, 'financial_year_id' => $fyId])->orderBy('date_invoiced', 'asc')->get();
        $fy = FinancialYear::where('financial_year_id', $fyId)->orderBy('year_starting', 'desc')->get()->map(function($year) {
            $formattedYear = Carbon::parse($year->year_starting)->format('Y') == Carbon::parse($year->year_ending)->format('Y')
                ? Carbon::parse($year->year_starting)->format('Y')
                : Carbon::parse($year->year_starting)->format('Y').'/'.Carbon::parse($year->year_ending)->format('y');
            return ['fYear' => $formattedYear];
        });

        $user = $statements[0]->first_name.' '.$statements[0]->surname;
        $client = ClientAccount::find($client);
        $currency = Currency::find($client->currency_id);

        $domPdfPath = base_path('vendor/dompdf/dompdf');
        Settings::setPdfRendererPath($domPdfPath);
        Settings::setPdfRendererName('DomPDF');

        $table = new Table(['unit' => \PhpOffice\PhpWord\SimpleType\TblWidth::PERCENT, 'width' => 100 * 50, 'align' => 'center']);

        $header = ['size' => 10, 'name' => 'Cambria', 'space' => ['before' => 100, 'after' => 100], 'bold' => true];
        $text = ['size' => 9, 'name' => 'Cambria', 'space' => ['before' => 100, 'after' => 100], 'bold' => false];

        $table->addRow();
        $table->addCell(500, ['borderSize' => 1, 'align' => 'center', 'bgColor' => 'cccccc'])->addText('#', $header, ['space' => ['before' => 100, 'after' => 100]]);
        $table->addCell(1000, ['borderSize' => 1, 'align' => 'center', 'bgColor' => 'cccccc'])->addText('TRANSACTION', $header, ['space' => ['before' => 100, 'after' => 100]]);
        $table->addCell(1300, ['borderSize' => 1, 'align' => 'center', 'bgColor' => 'cccccc'])->addText('DATE CREATED', $header, ['space' => ['before' => 100, 'after' => 100]]);
        $table->addCell(1000, ['borderSize' => 1, 'align' => 'center', 'bgColor' => 'cccccc'])->addText('INVOICE NUMBER', $header, ['space' => ['before' => 100, 'after' => 100]]);
        $table->addCell(2500, ['borderSize' => 1, 'align' => 'center', 'bgColor' => 'cccccc'])->addText('DESCRIPTION', $header, ['space' => ['before' => 100, 'after' => 100]]);
        $table->addCell(1200, ['borderSize' => 1, 'align' => 'center', 'bgColor' => 'cccccc'])->addText('DEBIT', $header, ['space' => ['before' => 100, 'after' => 100]]);
        $table->addCell(1200, ['borderSize' => 1, 'align' => 'center', 'bgColor' => 'cccccc'])->addText('CREDIT', $header, ['space' => ['before' => 100, 'after' => 100]]);

        $totalDebit = 0;
        $totalCredit = 0;

        foreach ($statements as $key => $statement) {
            $table->addRow();
            $table->addCell(500, ['borderSize' => 1, 'align' => 'center'])->addText(++$key, $text, ['space' => ['before' => 100, 'after' => 100]]);
            $table->addCell(1000, ['borderSize' => 1, 'align' => 'center'])->addText($statement->type, $text, ['space' => ['before' => 100, 'after' => 100]]);
            $table->addCell(1300, ['borderSize' => 1, 'align' => 'center'])->addText(Carbon::createFromTimestamp($statement->date_invoiced)->format('D, d/m/y') , $text, ['space' => ['before' => 100, 'after' => 100]]);
            $table->addCell(1000, ['borderSize' => 1, 'align' => 'center'])->addText($statement->invoice_number, $text, ['space' => ['before' => 100, 'after' => 100]]);
            $table->addCell(2500, ['borderSize' => 1, 'align' => 'center'])->addText($statement->description, $text, ['space' => ['before' => 100, 'after' => 100]]);
            $table->addCell(1200, ['borderSize' => 1, 'align' => 'center'])->addText(number_format($statement->debit, 2), $text, ['space' => ['before' => 100, 'after' => 100]]);
            $table->addCell(1200, ['borderSize' => 1, 'align' => 'center'])->addText(number_format($statement->credit, 2), $text, ['space' => ['before' => 100, 'after' => 100]]);

            $totalDebit +=  $statement->debit;
            $totalCredit += $statement->credit;

        }

        $table->addRow();
        $table->addCell(7300, ['borderSize' => 1, 'align' => 'center', 'gridSpan' => 5])->addText('TOTALS', $header, ['space' => ['before' => 100, 'after' => 100]]);
        $table->addCell(1200, ['borderSize' => 1, 'align' => 'center'])->addText($currency->currency_symbol.' '.number_format($totalDebit, 2), $header, ['space' => ['before' => 100, 'after' => 100]]);
        $table->addCell(1200, ['borderSize' => 1, 'align' => 'center'])->addText($currency->currency_symbol.' '.number_format($totalCredit, 2), $header, ['space' => ['before' => 100, 'after' => 100]]);

        $table->addRow();
        $table->addCell(7300, ['borderSize' => 1, 'align' => 'center', 'gridSpan' => 5])->addText('BALANCE', $header, ['space' => ['before' => 100, 'after' => 100]]);
        $table->addCell(2400, ['borderSize' => 1, 'align' => 'center', 'gridSpan' => 2])->addText($currency->currency_symbol.' '.number_format($totalDebit - $totalCredit, 2), $header, ['space' => ['before' => 100, 'after' => 100], 'align' => 'center']);

        $invoice = new TemplateProcessor(storage_path('account_statement.docx'));
        $invoice->setComplexBlock('{table}', $table);
        $invoice->setValue('fYear', $fy[0]['fYear']);
        $invoice->setValue('printer', auth()->user()->user->surname.' '.auth()->user()->user->first_name);
        $invoice->setValue('user', $user);
        $invoice->setValue('date', Carbon::now()->format('D, d M Y H:i:s'));
        $invoice->setValue('clientName', $client->client_account_name);
//        $invoice->setValue('dueDate', Carbon::createFromTimestamp($values->due_date)->format('d/m/Y'));
        $docPath = 'Files/'.$client->client_account_number.'.docx';
        $invoice->saveAs($docPath);
        // return response()->download($docPath)->deleteFileAfterSend(true);

        $phpWord = IOFactory::load($docPath);
        $contents = \PhpOffice\PhpWord\IOFactory::load($docPath);
        $pdfPath = 'Files/TempFiles/'.$client->client_account_number. ".pdf";
        $converter =  new OfficeConverter($docPath, 'Files/TempFiles/');
        $converter->convertTo($client->client_account_number.".pdf");
        unlink($docPath);
        return response()->download($pdfPath)->deleteFileAfterSend(true);

    }
    public function viewClientStatement($id)
    {
        list($client, $year) = explode(':', base64_decode($id));
        $statements = Db::table('accountstatements')->where(['client_id' => $client, 'financial_year_id' => $year])->orderBy('date_invoiced', 'asc')->get();
        $fy = FinancialYear::find($year);
       return $client = ClientAccount::find($client);
        $currency = Currency::find($client->currency_id);

        return view('account::reports.sales.clientStatement')->with(['statements' => $statements, 'fy' => $fy, 'client' => $client, 'currency' => $currency]);
    }
    public function getPurchasesFinancialYears()
    {
        $fyIds = Invoice::pluck('financial_year_id')->toArray();
        $years = FinancialYear::whereIn('financial_year_id', $fyIds)->orderBy('year_starting', 'desc')->get()->map(function($year) {
            $formattedYear = Carbon::parse($year->year_starting)->format('Y') == Carbon::parse($year->year_ending)->format('Y')
                ? Carbon::parse($year->year_starting)->format('Y')
                : Carbon::parse($year->year_starting)->format('Y').'/'.Carbon::parse($year->year_ending)->format('y');

            return [
                'financial_year_id' => $year->financial_year_id, // Assuming there's an 'id' field
                'financial_year' => $formattedYear,
                'year_starting' => $year->year_starting,
                'year_ending' => $year->year_ending,
            ];
        });
        return view('account::reports.purchase.financialYears')->with('years', $years);
    }
    public function getClientsPurchasesWithInvoices($id)
    {
        $invoices = Purchase::join('client_accounts', 'client_accounts.client_account_id', '=', 'purchases.client_id')
            ->join('currencies', 'currencies.currency_id', '=', 'client_accounts.currency_id')
            ->join('financial_years', 'financial_years.financial_year_id', '=', 'purchases.financial_year_id')
            ->select('financial_years.financial_year_id','purchases.purchase_id', 'currency_symbol', 'client_accounts.client_account_name as clientAccount', 'voucher_number', 'invoice_number', 'date_invoiced', 'due_date', 'client_account_number', 'currency_symbol', 'currencies.currency_id', 'client_account_id', 'amount_due')
            ->where('financial_years.financial_year_id', $id)
            ->get()
            ->groupBy(['client_account_number', 'currency_symbol']);

        return view('account::reports.purchase.invoicesPerClient')->with(['invoices' => $invoices]);
    }
    public function viewSupplierStatement($id)
    {
        list($client, $year) = explode(':', base64_decode($id));
        $statements = Db::table('purchasestatement')->where(['client_id' => $client, 'financial_year_id' => $year])->orderBy('date_invoiced', 'asc')->get();
        $fy = FinancialYear::find($year);
        $client = ClientAccount::find($client);
        $currency = Currency::find($client->currency_id);

        return view('account::reports.purchase.clientStatement')->with(['statements' => $statements, 'fy' => $fy, 'client' => $client, 'currency' => $currency]);
    }
    public function downloadSupplierStatement($id)
    {
        list($client, $fyId) = explode(':', base64_decode($id));
        $statements = Db::table('purchasestatement')->where(['client_id' => $client, 'financial_year_id' => $fyId])->orderBy('date_invoiced', 'asc')->get();
        $fy = FinancialYear::where('financial_year_id', $fyId)->orderBy('year_starting', 'desc')->get()->map(function($year) {
            $formattedYear = Carbon::parse($year->year_starting)->format('Y') == Carbon::parse($year->year_ending)->format('Y')
                ? Carbon::parse($year->year_starting)->format('Y')
                : Carbon::parse($year->year_starting)->format('Y').'/'.Carbon::parse($year->year_ending)->format('y');
            return ['fYear' => $formattedYear];
        });

        $user = $statements[0]->first_name.' '.$statements[0]->surname;
        $client = ClientAccount::find($client);
        $currency = Currency::find($client->currency_id);

        $domPdfPath = base_path('vendor/dompdf/dompdf');
        Settings::setPdfRendererPath($domPdfPath);
        Settings::setPdfRendererName('DomPDF');

        $table = new Table(['unit' => \PhpOffice\PhpWord\SimpleType\TblWidth::PERCENT, 'width' => 100 * 50, 'align' => 'center']);

        $header = ['size' => 10, 'name' => 'Cambria', 'space' => ['before' => 100, 'after' => 100], 'bold' => true];
        $text = ['size' => 9, 'name' => 'Cambria', 'space' => ['before' => 100, 'after' => 100], 'bold' => false];

        $table->addRow();
        $table->addCell(500, ['borderSize' => 1, 'align' => 'center', 'bgColor' => 'cccccc'])->addText('#', $header, ['space' => ['before' => 100, 'after' => 100]]);
        $table->addCell(1300, ['borderSize' => 1, 'align' => 'center', 'bgColor' => 'cccccc'])->addText('DATE CREATED', $header, ['space' => ['before' => 100, 'after' => 100]]);
        $table->addCell(1300, ['borderSize' => 1, 'align' => 'center', 'bgColor' => 'cccccc'])->addText('TRANSACTION', $header, ['space' => ['before' => 100, 'after' => 100]]);
        $table->addCell(1300, ['borderSize' => 1, 'align' => 'center', 'bgColor' => 'cccccc'])->addText('INVOICE NUMBER', $header, ['space' => ['before' => 100, 'after' => 100]]);
        $table->addCell(2500, ['borderSize' => 1, 'align' => 'center', 'bgColor' => 'cccccc'])->addText('DESCRIPTION', $header, ['space' => ['before' => 100, 'after' => 100]]);
        $table->addCell(1200, ['borderSize' => 1, 'align' => 'center', 'bgColor' => 'cccccc'])->addText('DEBIT', $header, ['space' => ['before' => 100, 'after' => 100]]);
        $table->addCell(1200, ['borderSize' => 1, 'align' => 'center', 'bgColor' => 'cccccc'])->addText('CREDIT', $header, ['space' => ['before' => 100, 'after' => 100]]);

        $totalDebit = 0;
        $totalCredit = 0;

        foreach ($statements as $key => $statement) {
            $table->addRow();
            $table->addCell(500, ['borderSize' => 1, 'align' => 'center'])->addText(++$key, $text, ['space' => ['before' => 100, 'after' => 100]]);
            $table->addCell(1300, ['borderSize' => 1, 'align' => 'center'])->addText(Carbon::createFromTimestamp($statement->date_invoiced)->format('D, d/m/y') , $text, ['space' => ['before' => 100, 'after' => 100]]);
            $table->addCell(1300, ['borderSize' => 1, 'align' => 'center'])->addText($statement->type, $text, ['space' => ['before' => 100, 'after' => 100]]);
            $table->addCell(1300, ['borderSize' => 1, 'align' => 'center'])->addText($statement->invoice_number, $text, ['space' => ['before' => 100, 'after' => 100]]);
            $table->addCell(2500, ['borderSize' => 1, 'align' => 'center'])->addText($statement->description, $text, ['space' => ['before' => 100, 'after' => 100]]);
            $table->addCell(1200, ['borderSize' => 1, 'align' => 'center'])->addText(number_format($statement->debit, 2), $text, ['space' => ['before' => 100, 'after' => 100]]);
            $table->addCell(1200, ['borderSize' => 1, 'align' => 'center'])->addText(number_format($statement->credit, 2), $text, ['space' => ['before' => 100, 'after' => 100]]);

            $totalDebit +=  $statement->debit;
            $totalCredit += $statement->credit;

        }

        $table->addRow();
        $table->addCell(7900, ['borderSize' => 1, 'align' => 'center', 'gridSpan' => 5])->addText('TOTALS', $header, ['space' => ['before' => 100, 'after' => 100]]);
        $table->addCell(1200, ['borderSize' => 1, 'align' => 'center'])->addText($currency->currency_symbol.' '.number_format($totalDebit, 2), $header, ['space' => ['before' => 100, 'after' => 100]]);
        $table->addCell(1200, ['borderSize' => 1, 'align' => 'center'])->addText($currency->currency_symbol.' '.number_format($totalCredit, 2), $header, ['space' => ['before' => 100, 'after' => 100]]);

        $table->addRow();
        $table->addCell(7000, ['borderSize' => 1, 'align' => 'center', 'gridSpan' => 5])->addText('BALANCE', $header, ['space' => ['before' => 100, 'after' => 100]]);
        $table->addCell(2400, ['borderSize' => 1, 'align' => 'center', 'gridSpan' => 2])->addText($currency->currency_symbol.' '.number_format($totalDebit - $totalCredit, 2), $header, ['space' => ['before' => 100, 'after' => 100], 'align' => 'center']);

        $invoice = new TemplateProcessor(storage_path('account_statement.docx'));
        $invoice->setComplexBlock('{table}', $table);
        $invoice->setValue('fYear', $fy[0]['fYear']);
        $invoice->setValue('printer', auth()->user()->user->surname.' '.auth()->user()->user->first_name);
        $invoice->setValue('user', $user);
        $invoice->setValue('date', Carbon::now()->format('D, d M Y H:i:s'));
        $invoice->setValue('clientName', $client->client_account_name);
//        $invoice->setValue('dueDate', Carbon::createFromTimestamp($values->due_date)->format('d/m/Y'));
        $docPath = 'Files/'.$client->client_account_number.'.docx';
        $invoice->saveAs($docPath);
        // return response()->download($docPath)->deleteFileAfterSend(true);

        $phpWord = IOFactory::load($docPath);
        $contents = \PhpOffice\PhpWord\IOFactory::load($docPath);
        $pdfPath = 'Files/TempFiles/'.$client->client_account_number. ".pdf";
        $converter =  new OfficeConverter($docPath, 'Files/TempFiles/');
        $converter->convertTo($client->client_account_number.".pdf");
        unlink($docPath);
        return response()->download($pdfPath)->deleteFileAfterSend(true);

    }
    public function getAccountStatementFinancialYears()
    {
        $fyIds = Invoice::pluck('financial_year_id')->toArray();
        $years = FinancialYear::whereIn('financial_year_id', $fyIds)->orderBy('year_starting', 'desc')->get()->map(function($year) {
            $formattedYear = Carbon::parse($year->year_starting)->format('Y') == Carbon::parse($year->year_ending)->format('Y')
                ? Carbon::parse($year->year_starting)->format('Y')
                : Carbon::parse($year->year_starting)->format('Y').'/'.Carbon::parse($year->year_ending)->format('y');

            return [
                'financial_year_id' => $year->financial_year_id, // Assuming there's an 'id' field
                'financial_year' => $formattedYear,
                'year_starting' => $year->year_starting,
                'year_ending' => $year->year_ending,
            ];
        });

        return view('account::reports.other.financialYears')->with('years', $years);
    }
    public function getAccountsWithInvoices($id)
    {
        $invoices = DB::table('invoice_items')
            ->join('client_accounts', 'invoice_items.ledger_id', '=', 'client_accounts.client_account_id')
            ->join('invoices', 'invoices.invoice_id', '=', 'invoice_items.invoice_id')
            ->join('financial_years', 'financial_years.financial_year_id', '=', 'invoices.financial_year_id')
            ->join('currencies', 'currencies.currency_id', '=', 'client_accounts.currency_id')
            ->join('chart_of_accounts', 'chart_of_accounts.chart_id', '=', 'client_accounts.chart_id')
            ->join('account_sub_categories', 'account_sub_categories.sub_account_id', '=', 'chart_of_accounts.sub_account_id')
            ->join('accounts', 'accounts.account_id', '=', 'account_sub_categories.account_id')
            ->select(
                'accounts.account_name',
                'account_sub_categories.sub_account_name',
                'client_accounts.client_account_number',
                DB::raw('invoice_items.quantity * invoice_items.unit_price as debit'),
                DB::raw('0 as credit')
            )
            ->where('financial_years.financial_year_id', $id);

        $transactions = DB::table('transactions')
            ->join('client_accounts', 'transactions.client_id', '=', 'client_accounts.client_account_id')
            ->join('currencies', 'currencies.currency_id', '=', 'client_accounts.currency_id')
            ->join('chart_of_accounts', 'chart_of_accounts.chart_id', '=', 'client_accounts.chart_id')
            ->join('account_sub_categories', 'account_sub_categories.sub_account_id', '=', 'chart_of_accounts.sub_account_id')
            ->join('accounts', 'accounts.account_id', '=', 'account_sub_categories.account_id')
            ->select(
                'accounts.account_name',
                'account_sub_categories.sub_account_name',
                'client_accounts.client_account_number',
                DB::raw('0 as debit'),
                'transactions.amount_received as credit'
            )
            ->where('transactions.financial_year_id', $id);

        $combined = $invoices->union($transactions)->get()
            ->groupBy(['account_name', 'sub_account_name'])
            ->map(function ($groupedSubAccounts, $accountName) {
                // Initialize an empty array to hold the sub-account details
                $subAccountDetails = [];

                // Iterate through each sub-account group
                foreach ($groupedSubAccounts as $subAccountName => $subAccountGroup) {
                    // Calculate total debit and credit
                    $totalDebit = $subAccountGroup->sum('debit');
                    $totalCredit = $subAccountGroup->sum('credit');

                    // Extract details from the first item in the sub-account group
                    $firstItem = $subAccountGroup->first();

                    // Append the sub-account details to the array
                    $subAccountDetails[$subAccountName] = [
                        'sub_account_number' => $firstItem->client_account_number,
                        'sub_account_name' => $subAccountName,
                        'totalDebit' => $totalDebit,
                        'totalCredit' => $totalCredit,
                    ];
                }

                return $subAccountDetails;
            });

        $report = [];

// Organize by account_name
        foreach ($combined as $accountName => $subAccounts) {
            $report[$accountName] = $subAccounts;
        }

// Return as JSON
//        return response()->json($report);

        return view('account::reports.other.trialBalance')->with(['invoices' => $report]);
    }
    public function generateVatTaxReport(Request $request)
    {
        $dateFrom = $request->dateFrom;
        $dateTo = $request->dateTo;
        $rating = $request->rating;
//        return $request->all();

        $query = DB::table('taxstatement')
            ->join('tax_brackets', 'tax_brackets.tax_bracket_id', '=', 'taxstatement.client_id')
            ->join('taxes', 'taxes.tax_id', '=', 'tax_brackets.tax_id')
            ->orderBy('date_invoiced', 'asc');

        if (!is_null($dateFrom)) {
            $query->where('date_invoiced', '>=', $dateFrom);
        }

        if (!is_null($dateTo)) {
            $query->where('date_invoiced', '<=', $dateTo);
        }

        if (!is_null($rating) && $rating == 2) {
            $query->where(function($query) {
                    $query->where('debit', '>', 0)
                        ->orWhere('credit', '>', 0);
                });
        }

        $statements = $query->get();
        return Excel::download(new ExportVATTaxReport($statements), 'VAT REPORT'.time().'.xlsx', \Maatwebsite\Excel\Excel::XLSX);
    }
}
