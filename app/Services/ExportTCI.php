<?php

namespace App\Services;

use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ExportTCI implements FromCollection, WithHeadings, ShouldAutoSize, WithStyles
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
                'GARDEN NAME' => $tea->garden_name,
                'GRADE' => $tea->grade_name,
                'DO NUMBER' => $tea->order_number,
                'INVOICE NUMBER' => $tea->invoice_number,
                'LOT NUMBER' => $tea->lot_number,
                'SALE NUMBER' => $tea->sale_number,
                'PACKAGES' => $tea->packet,
                'NET WEIGHT' => $tea->weight,
                'PROMPT DATE' => $tea->prompt_date,
                'PACKAGES RECEIVED' => $tea->total_pallets,
                'WEIGHT RECEIVED' => $tea->total_weight,
                'STATUS' => $tea->load_status == 2 ? 'TEA RECEIVED' : 'UNDER COLLECTION',
                'KEYED IN BY' => $tea->first_name.' '.$tea->surname,
                'DATE KEYED IN' => Carbon::parse($tea->created_at)->format('D, d-m-Y H:i'),
            ];
        }

        return collect($teas);
    }

    public function headings(): array
    {
        // TODO: Implement headings() method.

        $locality = $this->data[0]['locality'] == 1 ? 'ISLAND' : ($this->data[0]['locality'] == 2 ? 'CHANGAMWE' : ($this->data[0]['locality'] == 3 ? 'JOMVU' : ($this->data[0]['locality'] == 4 ? 'BONJE' : 'MIRITINI')));
        return [
            ['PACKMAC HOLDINGS LIMITED'],
            ['Chai Street Shimanzi'],
            ['High Level, Shimanzi Area. Mombasa'],
            ['P.O Box 41932-80100, Mombasa, Kenya'],
            ['TMSA 186'],
            ['TEA COLLECTION INSTRUCTION (' . $this->data[0]['loading_number'] .')'],
            ['CLIENT NAME '. $this->data[0]['client_name']],
            ['PRODUCER WAREHOUSE: '. $this->data[0]['warehouse_name'].', '. $this->data[0]['sub_warehouse_name'].', '.$locality],
            ['GARDEN NAME', 'GRADE', 'DO NUMBER', 'INVOICE NUMBER', 'LOT NUMBER', 'SALE NUMBER', 'PACKAGES', 'NET WEIGHT', 'PROMT DATE', 'PACKAGES RECEIVED', 'WEIGHT RECEIVED', 'STATUS', 'KEYED IN BY', 'DATE KEYED IN']
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
        $sheet->mergeCells('A6:N6');
        $sheet->mergeCells('A7:N7');
        $sheet->mergeCells('A8:N8');

        // Center align text in merged cells
        $sheet->getStyle('A1:N6')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // Apply additional styling
        $sheet->getStyle('1')->applyFromArray(['font' => ['bold' => true], 'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]]);
        $sheet->getStyle('5')->applyFromArray(['font' => ['bold' => true]]);
        $sheet->getStyle('6')->applyFromArray(['font' => ['bold' => true]]);
//        $sheet->getStyle('7')->applyFromArray(['font' => ['bold' => true]]);
//        $sheet->getStyle('8')->applyFromArray(['font' => ['bold' => true]]);
        $sheet->getStyle('9')->applyFromArray(['font' => ['bold' => true]]);

        // Return styles
        return [];
    }

}
