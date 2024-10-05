<?php

namespace App\Services;

use App\Models\UserInfo;
use Carbon\Carbon;
use Carbon\CarbonInterval;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithDrawings;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\MemoryDrawing;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx\Drawing;

class ExportStock implements FromCollection, WithHeadings, ShouldAutoSize, WithStyles
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

            $createdAt = Carbon::parse($tea->created_at);
            $currentDate = Carbon::now();
            $diffInDays = $createdAt->diffInDays($currentDate);
            $user = UserInfo::where('user_id', $tea->received_by)->first();

            $teas[] = [
                'CLIENT NAME' => $tea->client_name,
                'DELIVERY TYPE' => $tea->delivery_type == 1 ? 'DO ENTRY' : 'DIRECT DELIVERY',
                'DO NUMBER' => $tea->order_number,
                'GARDEN NAME' => $tea->garden_name,
                'GRADE' => $tea->grade_name,
                'INVOICE NUMBER' => $tea->invoice_number,
                'LOT NUMBER' => $tea->lot_number,
                'PACKAGES' => $tea->current_stock,
                'PALLET WEIGHT' => $tea->pallet_weight,
                'TARE WEIGHT' => $tea->package_tare,
                'NET WEIGHT' => $tea->current_weight,
                'GROSS WEIGHT' => $tea->total_weight,
                'SALE NUMBER' => $tea->sale_number,
                'DATE RECEIVED' => Carbon::createFromTimestamp( $tea->date_received)->format('D, d-m-Y H:i'),
                'WAREHOUSE' => $tea->stocked_at,
                'WAREHOUSE BAY' => $tea->bay_name,
                'AGING DATE' => $diffInDays . ' days',
                'RECEIVED BY' => $user->surname.' '.$user->first_name,
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
            ['STOCK POSITION OF TEAS IN OUR WAREHOUSES AS OF ' . Carbon::now()->format('D, d-m-Y H:i:s')],
            ['CLIENT NAME', 'DELIVERY TYPE', 'DO NUMBER', 'GARDEN NAME', 'GRADE', 'INVOICE NUMBER', 'LOT NUMBER', 'PACKAGES', 'PALLET WEIGHT',  'TARE WEIGHT',  'NET WEIGHT',  'GROSS WEIGHT', 'SALE NUMBER', 'DATE RECEIVED', 'WAREHOUSE', 'WAREHOUSE BAY', 'AGING DATE', 'RECEIVED BY']
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Merge cells for static heading rows
        $sheet->mergeCells('A1:R1');
        $sheet->mergeCells('A2:R2');
        $sheet->mergeCells('A3:R3');
        $sheet->mergeCells('A4:R4');
        $sheet->mergeCells('A5:R5');

        // Center align text in merged cells
        $sheet->getStyle('A1:R5')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // Apply additional styling
        $sheet->getStyle('1')->applyFromArray(['font' => ['bold' => true], 'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]]);
        $sheet->getStyle('5')->applyFromArray(['font' => ['bold' => true]]);
        $sheet->getStyle('6')->applyFromArray(['font' => ['bold' => true]]);

        // Return styles
        return [];
    }

}
