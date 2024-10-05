<?php

namespace App\Services;

use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ExportShippingInstructions Implements FromCollection, WithHeadings, ShouldAutoSize, WithStyles
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
    $i = 1;
    foreach ( $this->data as $tea){

        $status = $tea->status == 0 || $tea->status == null ? 'SI CREATED' : ($tea->status == 1 ? 'SI PENDING APPROVAL' : ($tea->status == 2 ? 'SI APPROVED' : 'TEAS SHIPPED'));

        $teas[] = [
            '#' => $i++,
            'SI NUMBER' => $tea->shipping_number,
            'CLIENT NAME' => $tea->client_name,
            'VESSEL NAME' => $tea->vessel_name,
            'DESTINATION PORT' => $tea->port_name,
            'CONSIGNEE' => $tea->consignee,
            'SEAL NUMBER' => $tea->seal_number,
            'SHIPPING INSTRUCTIONS' => $tea->shipping_instructions,
            'CONTAINER SIZE' => $tea->container_size == 1 ? '20 FT' : ($tea->container_size == 2 ? '40 FT' : '40 FTHC'),
            'ESCORTED' => $tea->escorted == 1 ? 'YES' : 'NO',
            'LOAD TYPE' => $tea->loading_type == 1 ? 'LOOSE LOADING' : 'PALLETIZED LOADING',
            'STATUS' =>  $status,
            'INITIATED' => Carbon::parse($tea->created_at)->format('D, d/m/y H:i'),
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
        ['SHIPPING INSTRUCTION PRINTED ON ' . Carbon::now()->format('D, d-m-Y H:i:s')],
        ['#', 'SI NUMBER', 'CLIENT NAME', 'VESSEL NAME', 'DESTINATION PORT', 'CONSIGNEE', 'SEAL NUMBER', 'SHIPPING INSTRUCTIONS', 'CONTAINER SIZE', 'ESCORTED', 'LOAD TYPE', 'STATUS', 'INITIATED']
    ];
}

    public function styles(Worksheet $sheet)
{
    // Merge cells for static heading rows
    $sheet->mergeCells('A1:M1');
    $sheet->mergeCells('A2:M2');
    $sheet->mergeCells('A3:M3');
    $sheet->mergeCells('A4:M4');
    $sheet->mergeCells('A5:M5');

    // Center align text in merged cells
    $sheet->getStyle('A1:M5')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

    // Apply additional styling
    $sheet->getStyle('1')->applyFromArray(['font' => ['bold' => true], 'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]]);
    $sheet->getStyle('5')->applyFromArray(['font' => ['bold' => true]]);
    $sheet->getStyle('6')->applyFromArray(['font' => ['bold' => true]]);

    // Return styles
    return [];
}
}
