<?php

namespace App\Services;

use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ExportInternalTransfers Implements FromCollection, WithHeadings, ShouldAutoSize, WithStyles
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

            $createdAt = Carbon::parse($tea->created);
            $dateReceived = Carbon::parse($tea->recieved);
            $currentDate = Carbon::now();

            $createdAtFormatted = $createdAt->format('d-m-Y H:i');
            $currentDateFormatted = $currentDate->format('d-m-Y H:i');
            $recievedDate = $dateReceived->format('d-m-Y H:i');

            // dd($recievedDate);

            if ($tea->status == 3){
                $dateReceivedDiff = 'RECEIVED';
            } else {
                $dateReceivedDiff = 'PENDING';
            }

            $diffInDays = $createdAt->diffInDays($currentDate);
            $status = $tea->status == null ? 'INITIATION STAGE' : ($tea->status == 0 || $tea->status == 1 ? 'APPROVAL STAGE' :($tea->status == 2 ? 'TRANSFER APPROVED' : 'TRANSFER COMPLETED'));

            $teas[] = [
                'DELIVERY NUMBER' => $tea->delivery_number,
                'CLIENT NAME' => $tea->client_name,
                'GARDEN NAME' => $tea->garden_name,
                'GRADE' => $tea->grade_name,
                'INVOICE NUMBER' => $tea->invoice_number,
                'PACKAGES' => $tea->requested_palettes,
                'NET WEIGHT' => $tea->requested_weight,
                'TRANSFER FROM' => $tea->station_name,
                'DESTINATION' => $tea->destination_name,
                'TRANSPORTER' => $tea->transporter_name,
                'REGISTRATION' => $tea->registration,
                'DRIVER ID NUMBER' => $tea->id_number,
                'DRIVER NAME' => $tea->driver_name,
                'STATUS' =>  $status,
                'INITIATED' => $diffInDays . ' days ago',
                'RECEIVED' => $dateReceivedDiff
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
            [ 'INTERNAL TRANSFERS PRINTED ON ' . Carbon::now()->format('D, d-m-Y H:i:s')],
            ['DELIVERY NUMBER', 'CLIENT NAME', 'GARDEN NAME', 'GRADE', 'INVOICE NUMBER', 'PACKAGES', 'NET WEIGHT', 'TRANSFER FROM', 'DESTINATION', 'TRANSPORTER', 'REGISTRATION', 'DRIVER ID NUMBER', 'DRIVER NAME', 'STATUS', 'INITIATED', 'RECEIVED']
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Merge cells for static heading rows
        $sheet->mergeCells('A1:P1');
        $sheet->mergeCells('A2:P2');
        $sheet->mergeCells('A3:P3');
        $sheet->mergeCells('A4:P4');
        $sheet->mergeCells('A5:P5');

        // Center align text in merged cells
        $sheet->getStyle('A1:P5')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // Apply additional styling
        $sheet->getStyle('1')->applyFromArray(['font' => ['bold' => true], 'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]]);
        $sheet->getStyle('5')->applyFromArray(['font' => ['bold' => true]]);
        $sheet->getStyle('6')->applyFromArray(['font' => ['bold' => true]]);

        // Return styles
        return [];
    }
}
