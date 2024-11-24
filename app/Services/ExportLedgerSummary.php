<?php

namespace App\Services;

use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Modules\Account\Entities\ForexExchange;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
class ExportLedgerSummary Implements FromCollection, WithHeadings, ShouldAutoSize, WithStyles
{
    public function __construct($invoices)
    {
        $this->invoices = $invoices;
    }
    public function collection()
    {
        $invoices = collect();  // Use a collection to store the result
        $inv = [];  // Initialize $inv array to store individual invoice data
        $totalInvoice = 0;

        foreach ($this->invoices as $key => $invoice) {
            $invDate = Carbon::createFromTimestamp($invoice->date_invoiced)->format('Y-m-d');
            $forex = ForexExchange::where('currency_id', $invoice->currency_id)
                ->where('date_active', '<=', $invDate)
                ->orderBy('date_active', 'desc')
                ->first();

            $rate = $forex ? $forex->exchange_rate : 1;  // If $forex is null, use 1 as the rate
            $type = $invoice->type == 1 ? 1 : -1;

            $totalSale = floatval($invoice->total_sales) * $rate * $type;
            $totalTax = floatval($invoice->total_vat) * $rate * $type;
            $netSale = ($invoice->total_sales - $invoice->total_vat) * $rate * $type;

            $inv[] = [
                '#' => ++$key,
                'INVOICE DATE' => Carbon::createFromTimestamp($invoice->date_invoiced)->format('d-m-Y'),
                'INVOICE NUMBER' => $invoice->invoice_number,
                'INVOICE TYPE' => $invoice->type == 1 ? 'INVOICE' : 'CREDIT NOTE',
                'CLIENT NAME' => $invoice->client_account_name,
                'TOTAL SALE' => number_format($invoice->amount_due * $type, 2),
            ];

            $totalInvoice += $invoice->amount_due * $type;
        }

        // Add totals row
        $totals = [
            '#' => null,
            'CLIENT NAME' => null,
            'INVOICE DATE' => null,
            'INVOICE NUMBER' => null,
            'SI NUMBER' => null,
            'NET SALE' => number_format($totalInvoice, 2),
        ];

        // Push all invoices and the totals into the collection
        $invoices->push(...$inv);
        $invoices->push($totals);

        return $invoices;
    }

    public function headings(): array
    {
        // TODO: Implement headings() method.

        return [
            ['#', 'INVOICE DATE', 'INVOICE NUMBER', 'INVOICE TYPE', 'CLIENT NAME', 'TOTAL INVOICE'],
        ];
    }
    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1:F1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('F:F')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
        $sheet->getStyle('1')->applyFromArray(['font' => ['bold' => true], 'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]]);
        $sheet->getStyle('F:F')
            ->getNumberFormat()
            ->setFormatCode(NumberFormat::FORMAT_NUMBER_00); // Ensures 2 decimal places
        return [];
    }
}
