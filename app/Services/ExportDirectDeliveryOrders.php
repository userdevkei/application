<?php

namespace App\Services;

use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ExportDirectDeliveryOrders implements FromCollection, WithHeadings, ShouldAutoSize, WithStyles
{
    public $data;
    public function __construct($data)
    {
        $this->data = $data;
    }

    public function collection()
    {
        $teas = [];
        // TODO: Implement collection() method.
        foreach ( $this->data as $tea){

            $teas[] = [
                'DELIVERY TYPE' => $tea['delivery_type'] == 1 ? 'DO ENTRY' : 'DIRECT ENTRY',
                'DO NUMBER' => $tea['order_number'],
                'CLIENT NAME' => $tea['client_name'],
                'TEA TYPE' => $tea['tea_id'] == 1 ? 'AUCTION TEA' :($tea['tea_id'] == 2 ? 'PRIVATE TEA' : ($tea['tea_id'] == 3 ? 'FACTORY TEA' : 'BLEND REMNANT')),
                'GARDEN NAME' => $tea['garden_name'],
                'GRADE' => $tea['grade_name'],
                'INVOICE NUMBER' => $tea['invoice_number'],
                'PACKAGES' => $tea['total_pallets'],
                'NET WEIGHT' => $tea['net_weight'],
                'PACKAGE' => $tea['package'] == 1 ? 'PB' : 'PS',
                'PRODUCER WAREHOUSE' => $tea['warehouse_name'].', '.$tea['sub_warehouse_name'],
                'STATUS' => $tea['status'] == null || $tea['status'] == 1 ? 'NOT RECEIVED' : 'TEA RECEIVED',
                'KEYED IN BY' => $tea['first_name'].' '.$tea['surname'],
                'DATE KEYED IN' => Carbon::parse($tea['created_at'])->format('D, d-m-Y H:i'),
            ];
        }

        return collect($teas);
    }

    public function headings(): array
    {
        // TODO: Implement headings() method.

        return [
            ['PACKMAC HOLDINGS LIMITED'],
            ['Chai Street Shimanzi'],
            ['High Level, Shimanzi Area. Mombasa'],
            ['P.O Box 41932-80100, Mombasa, Kenya'],
            ['DIRECT DELIVERY TALLY ' . Carbon::now()->format('D, d-m-Y H:i:s')],
            ['DELIVERY TYPE', 'DO NUMBER', 'CLIENT NAME', 'TEA TYPE', 'GARDEN NAME', 'GRADE', 'INVOICE NUMBER', 'PACKAGES', 'NET WEIGHT', 'PACKAGE', 'PRODUCER WAREHOUSE', 'STATUS', 'KEYED IN BY', 'DATE KEYED IN']
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Merge cells for static heading rows
        $sheet->mergeCells('A1:N1');
        $sheet->mergeCells('A2:N2');
        $sheet->mergeCells('A3:N3');
        $sheet->mergeCells('A4:N4');
        $sheet->mergeCells('A5:N5');

        // Center align text in merged cells
        $sheet->getStyle('A1:N5')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // Apply additional styling
        $sheet->getStyle('1')->applyFromArray(['font' => ['bold' => true], 'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]]);
        $sheet->getStyle('5')->applyFromArray(['font' => ['bold' => true]]);
        $sheet->getStyle('6')->applyFromArray(['font' => ['bold' => true]]);

        // Return styles
        return [];
    }
}
