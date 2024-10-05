<?php

namespace App\Services;

use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ExportSTLReport implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize
{
    public $data;
    private $boldRows = [];

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

            $status = $tea->status == 0 ? 'SI CREATED' : ($tea->status == 1 ? 'SI PENDING APPROVAL' : ($tea->status == 2 ? 'SI APPROVED' : 'TEAS SHIPPED'));

            $teas[] = [
                '#' => $i++,
                'DATE CREATED' => Carbon::parse($tea->created_at)->format('D, d/m/Y H:i'),
                'CREATED BY' => $tea->surname.' '.$tea->first_name,
                'JOB SITE' => $tea->station_name,
                'SI NUMBER' => $tea->shipping_number,
                'CLIENT NAME' => $tea->client_name,
                'PACKAGES' => number_format($tea->stl_packages, 2),
                'NET WEIGHT' => number_format($tea->stl_weight, 2),
                'BLD/STL' => $tea->seal_number,
                'CONSIGNEE' => $tea->consignee,
                'CONTAINER SIZE' => $tea->container_size == 1 ? '20 FT' : ($tea->container_size == 2 ? '40 FT' : '40 FTHC'),
                'CARGO TYPE' => $tea->loading_type == 1 ? 'LOOSE LOADING' : 'PALLETIZED LOADING',
                'DESTINATION PORT' => $tea->port_name,
                'CLEARING AGENT' => $tea->agent_name,
                'TRANSPORTER NAME' => $tea->transporter_name,
                'STATUS' =>  $status
            ];
        }

        return collect($teas);
    }

    public function headings(): array
    {
        return [
            ['PACKMAC HOLDINGS LIMITED'],
            ['Chai Street Shimanzi'],
            ['High Level, Shimanzi Area. Mombasa'],
            ['P.O Box 41932-80100, Mombasa, Kenya'],
            ['STRAIGHT LINE STATUS REPORT. PRINTED ON ' . Carbon::now()->format('D, d-m-Y H:i:s')],
            ['#', 'DATE CREATED', 'CREATED BY', 'JOB SITE', 'SI NUMBER', 'CLIENT NAME', 'PKGS', 'WEIGHT', 'BLD/STL', 'CONSIGNEE', 'CONTAINER SIZE', 'CARGO TYPE', 'DESTINATION', 'CLEARING AGENT', 'TRANSPORTER', 'STATUS'],
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Merge and center the header cells
        $sheet->mergeCells('A1:P1');
        $sheet->mergeCells('A2:P2');
        $sheet->mergeCells('A3:P3');
        $sheet->mergeCells('A4:P4');
        $sheet->mergeCells('A5:P5');

        // Center align text in merged cells
        $sheet->getStyle('A1:P5')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // Apply bold styling to the headers
        $sheet->getStyle('A1:P1')->applyFromArray([
            'font' => ['bold' => true],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]
        ]);
        $sheet->getStyle('A2:P2')->applyFromArray(['font' => ['bold' => false]]);
        $sheet->getStyle('A3:P3')->applyFromArray(['font' => ['bold' => false]]);
        $sheet->getStyle('A4:P4')->applyFromArray(['font' => ['bold' => false]]);
        $sheet->getStyle('A5:P5')->applyFromArray(['font' => ['bold' => true]]);
        $sheet->getStyle('A6:P6')->applyFromArray(['font' => ['bold' => true]]);

        // Apply bold styling to the rows tracked in $this->boldRows
        foreach ($this->boldRows as $row) {
            $sheet->getStyle('A' . $row . ':P' . $row)->applyFromArray([
                'font' => ['bold' => true]
            ]);
        }

        return [];
    }
}
