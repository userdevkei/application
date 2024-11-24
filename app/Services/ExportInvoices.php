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
class ExportInvoices Implements FromCollection, WithHeadings, ShouldAutoSize, WithStyles
{
    public function __construct($invoices)
    {
        $this->invoices = $invoices;
    }
    public function collection()
    {
        $invoices = collect();  // Use a collection to store the result
        $inv = [];  // Initialize $inv array to store individual invoice data
        $summaryNetSale = 0;
        $summarytotalTax = 0;
        $summarytotalSale = 0;

        foreach ($this->invoices as $key => $invoice) {
            $invDate = Carbon::createFromTimestamp($invoice->date_invoiced)->format('Y-m-d');
           
            $type = $invoice->type == 1 ? 1 : -1;

            $totalTax = floatval($invoice->total_vat) * $type;
            $totalSale = ($invoice->total_sales + $totalTax) * $type;
            $netSale = floatval($invoice->total_sales) * $type;

            $inv[] = [
                '#' => ++$key,
                'CLIENT NAME' => $invoice->client_account_name,
                'INVOICE DATE' => Carbon::createFromTimestamp($invoice->date_invoiced)->format('d-m-Y'),
                'INVOICE NUMBER' => $invoice->invoice_number,
                'SI NUMBER' => $invoice->si_number,
                'NET SALE' => number_format($netSale, 2),
                'NET VAT' => number_format($totalTax, 2),
                'TOTAL SALE' => number_format($totalSale, 2),
            ];

            $summaryNetSale += $netSale;
            $summarytotalTax += $totalTax;
            $summarytotalSale += $totalSale;
        }

        // Add totals row
        $totals = [
            '#' => null,
            'CLIENT NAME' => null,
            'INVOICE DATE' => null,
            'INVOICE NUMBER' => null,
            'SI NUMBER' => null,
            'NET SALE' => number_format($summaryNetSale, 2),
            'NET VAT' => number_format($summarytotalTax, 2),
            'TOTAL SALE' => number_format($summarytotalSale, 2),
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
            ['#', 'CLIENT NAME', 'INVOICE DATE', 'INVOICE NUMBER', 'SI NUMBER', 'NET SALES', 'NET VAT', 'TOTAL SALE'],
        ];
    }
    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1:H1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('F:H')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
        $sheet->getStyle('1')->applyFromArray(['font' => ['bold' => true], 'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]]);
        $sheet->getStyle('F:H')
            ->getNumberFormat()
            ->setFormatCode(NumberFormat::FORMAT_NUMBER_00); // Ensures 2 decimal places
        return [];
    }
}
