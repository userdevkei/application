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
class ExportClientAgingStock Implements FromCollection, WithHeadings, ShouldAutoSize, WithStyles
{
    public function __construct($stocks)
    {
        $this->stocks = $stocks;
    }
    public function collection()
    {
        $reports = collect();  // Use a collection to store the result
        $stk = [];  // Initialize $stk array to store invoice data
        foreach ($this->stocks as $key => $stock) {
            $stk[] = [
                '#' => ++$key,
                'CLIENT NAME' => $stock->client_name,
                'Invoice Number' => $stock->invoice_number,
                'Delivery Type' => $stock->delivery,
                'Order Number' => $stock->order_number,
                'Garden Name' => $stock->garden_name,
                'Grade Name' => $stock->grade_name,
                'Packages' => number_format($stock->current_stock, 0),
                'Net Weight' => number_format($stock->current_weight, 2),
                'DATE RECEIVED' => $stock->min_date_received,
                'PERIOD' => $stock->aging_period.' Days',
            ];
        }
        // Push all invoices and the totals into the collection
        $reports->push(...$stk);
        return $reports;
    }
    public function headings(): array
    {
        // TODO: Implement headings() method.
        return [
            ['#', 'CLIENT NAME', 'INVOICE NUMBER', 'DELIVERY TYPE', 'ORDER NUMBER', 'GARDEN TYPE', 'GRADE NAME', 'PACKAGES', 'NET WEIGHT', 'DATE RECEIVED', 'AGING PERIOD'],
        ];
    }
    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1:H1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A:K')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
        $sheet->getStyle('1')->applyFromArray(['font' => ['bold' => true], 'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]]);
//        $sheet->getStyle('C:H')
//            ->getNumberFormat()
//            ->setFormatCode(NumberFormat::FORMAT_NUMBER_00); // Ensures 2 decimal places
        return [];
    }
}
