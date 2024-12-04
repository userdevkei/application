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
class ExportAgingStock Implements FromCollection, WithHeadings, ShouldAutoSize, WithStyles
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
                '<30' => $stock->weight_0_30 == 0 ? '0.00' : number_format($stock->weight_0_30, 2),
                '<90' => $stock->weight_31_90 == 0 ? '0.00' : number_format($stock->weight_31_90, 2),
                '<180' => $stock->weight_91_180 == 0 ? '0.00' : number_format($stock->weight_91_180, 2),
                '<365' => $stock->weight_181_365 == 0 ? '0.00' : number_format($stock->weight_181_365, 2),
                '>365' => $stock->weight_more_than_1yr == 0 ? '0.00' : number_format($stock->weight_more_than_1yr, 2),
                'summary' => number_format($stock->total_weight, 2).' ('.number_format($stock->total_stock, 0).')'
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
            ['#', 'CLIENT NAME', '<30 DAYS', '31-90 DAYS', '91-180 DAYS', '181-365 DAYS', '> 365 DAYS', 'TOTAL NET WEIGHT (PACKAGES)'],
        ];
    }
    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1:H1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('C:H')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
        $sheet->getStyle('1')->applyFromArray(['font' => ['bold' => true], 'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]]);
        $sheet->getStyle('C:H')
            ->getNumberFormat()
            ->setFormatCode(NumberFormat::FORMAT_NUMBER_00); // Ensures 2 decimal places
        return [];
    }
}
