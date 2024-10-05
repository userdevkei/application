<?php

namespace App\Services;

use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ExportExternalTransfer implements FromCollection, WithHeadings, ShouldAutoSize, WithStyles
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

            $currentDate = Carbon::now();

            if ($tea->extStatus == 3){
                $dateReceived = Carbon::parse($tea->received);
                $dateReceivedDiff = $dateReceived->diffInDays($currentDate).' days ago';
            }else{
                $dateReceivedDiff = 'PENDING';
            }

            $status = $tea->extStatus == null ? 'INITIATION STAGE' : ($tea->extStatus == 0 || $tea->extStatus == 1 ? 'APPROVAL STAGE' :($tea->extStatus == 2 ? 'TRANSFER APPROVED' : 'TRANSFER COMPLETED'));

            $teas[] = [
                'DELIVERY NUMBER' => $tea->delivery_number,
                'CLIENT NAME' => $tea->client_name,
                'GARDEN NAME' => $tea->garden_name,
                'GRADE' => $tea->grade_name,
                'INVOICE NUMBER' => $tea->invoice_number,
                'PACKAGES' => $tea->transferred_palettes,
                'NET WEIGHT' => $tea->transferred_weight,
                'TRANSFER FROM' => $tea->stocked_at,
                'DESTINATION' => $tea->warehouse_name,
                'TRANSPORTER' => $tea->transporter_name,
                'REGISTRATION' => $tea->registration,
                'DRIVER ID NUMBER' => $tea->id_number,
                'DRIVER NAME' => $tea->driver_name,
                'STATUS' =>  $status,
                'RELEASED ON' => $dateReceivedDiff
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
            [ 'EXTERNAL TRANSFERS PRINTED ON ' . Carbon::now()->format('D, d-m-Y H:i:s')],
            ['DELIVERY NUMBER', 'CLIENT NAME', 'GARDEN NAME', 'GRADE', 'INVOICE NUMBER', 'PACKAGES', 'NET WEIGHT', 'TRANSFER FROM', 'DESTINATION', 'TRANSPORTER', 'REGISTRATION', 'DRIVER ID NUMBER', 'DRIVER NAME', 'STATUS', 'RELEASED ON']
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Merge cells for static heading rows
        $sheet->mergeCells('A1:O1');
        $sheet->mergeCells('A2:O2');
        $sheet->mergeCells('A3:O3');
        $sheet->mergeCells('A4:O4');
        $sheet->mergeCells('A5:O5');

        // Center align text in merged cells
        $sheet->getStyle('A1:O5')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // Apply additional styling
        $sheet->getStyle('1')->applyFromArray(['font' => ['bold' => true], 'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]]);
        $sheet->getStyle('5')->applyFromArray(['font' => ['bold' => true]]);
        $sheet->getStyle('6')->applyFromArray(['font' => ['bold' => true]]);

        // Return styles
        return [];
    }
}
