<?php

namespace App\Services;

use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ExportVATTaxReport implements FromCollection, WithHeadings, ShouldAutoSize, WithStyles
{
    public $statements;
    public function __construct($statements)
    {
        $this->statements = $statements;
    }

    public function collection(){
        $taxes = [];
        $totalDebit = 0;
        $totalCredit = 0;
        foreach($this->statements as $key => $statement){
            $taxes [] =[
                '#' => ++$key,
                'DATE INVOICED' => $statement->date_invoiced,
                'INVOICE NUMBER' => $statement->invoice_number,
                'CLIENT NAME' => $statement->client_name,
                'KRA PIN NUMBER' => $statement->kra_number,
                'DEBIT' => number_format($statement->debit, 2),
                'CREDIT' => number_format($statement->credit, 2),
            ];

            $totalDebit += $statement->debit;
            $totalCredit += $statement->credit;
        }

        $totals = [
            '#' => null,
            'DATE INVOICED' => null,
            'INVOICE NUMBER' => null,
            'CLIENT NAME' => null,
            'KRA PIN NUMBER' => null,
            'DEBIT' => number_format($totalDebit, 2),  // Replace these with the actual totals if needed
            'CREDIT' => number_format($totalCredit, 2), // Replace these with the actual totals if needed
        ];

        // Add totals to the taxes array
        $taxes[] = $totals;

        // Return the collection
        return collect($taxes);
    }

    public function headings(): array
    {
        // TODO: Implement headings() method.

        return [
            ['PACKMAC HOLDINGS LIMITED'],
            ['Chai Street Shimanzi'],
            ['High Level, Shimanzi Area. Mombasa'],
            ['P.O Box 41932-80100, Mombasa, Kenya'],
            ['VAT TAX RETURNS REPORT. PRINTED ON ' . Carbon::now()->format('D, d-m-Y H:i:s')],
            ['#', 'DATE INVOICED', 'INVOICE NUMBER', 'CLIENT NAME', 'KRA PIN NUMBER', 'DEBIT', 'CREDIT']
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Merge cells for static heading rows
        $sheet->mergeCells('A1:G1');
        $sheet->mergeCells('A2:G2');
        $sheet->mergeCells('A3:G3');
        $sheet->mergeCells('A4:G4');
        $sheet->mergeCells('A5:G5');

        // Center align text in merged cells
        $sheet->getStyle('A1:G5')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // Apply additional styling
        $sheet->getStyle('1')->applyFromArray(['font' => ['bold' => true], 'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]]);
        $sheet->getStyle('5')->applyFromArray(['font' => ['bold' => true]]);
        $sheet->getStyle('6')->applyFromArray(['font' => ['bold' => true]]);

        // Return styles
        return [];
    }

}
