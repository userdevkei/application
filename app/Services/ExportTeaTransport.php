<?php

namespace App\Services;

use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ExportTeaTransport implements FromCollection, WithHeadings, ShouldAutoSize, WithStyles
{
    public $data;
    public function __construct($data)
    {
        $this->data = $data;
    }

    public function collection()
    {
        $teas = collect([]);

        collect($this->data)->groupBy('transporter_name')->each(function ($transporterTeas, $transporterName) use ($teas) {
            $transporterDetails = collect([
                [
                    'VEHICLE REG' => $transporterName,
                    'DRIVER NAME' => null,
                    'DRIVER ID NO.' => null,
                    'CLIENT NAME' => null,
                    'INVOICE NUMBER' => null,
                    'PACKAGES' => null,
                    'GROSS WEIGHT' => null,
                    'PRODUCER WAREHOUSE' => null,
                    'WAREHOUSE BRANCH' => null,
                    'BRANCH LOCALITY' => null,
                    'PMHL WAREHOUSE' => null,
                    'DATE RECEIVED' => null,
                ],
            ]);

            $transporterTeas->each(function ($tea) use ($transporterDetails) {
                $transporterDetails->push([
                    'VEHICLE REG' => $tea->registration,
                    'DRIVER NAME' => $tea->driver_name,
                    'DRIVER ID No.' => $tea->id_number,
                    'CLIENT NAME' => $tea->client_name,
                    'INVOICE NUMBER' => $tea->invoice_number,
                    'PACKAGES' => $tea->total_pallets,
                    'GROSS WEIGHT' => number_format($tea->total_weight, 2),
                    'PRODUCER WAREHOUSE' => $tea->warehouse_name,
                    'WAREHOUSE BRANCH' => $tea->sub_warehouse_name,
                    'BRANCH LOCALITY' => $tea->locality == 1 ? 'ISLAND' : ($tea->locality == 2 ? 'CHANGAMWE' : ($tea->locality == 3 ? 'JOMVU' : ($tea->locality == 4 ? 'BONJE' : 'MIRITINI'))),
                    'PMHL WAREHOUSE' => $tea->station_name,
                    'DATE RECEIVED' => Carbon::createFromTimestamp($tea->date_received)->format('D, d-m-Y H:i'),
                ]);
            });

            $totals = [
                'VEHICLE REGISTRATION' => 'TOTALS',
                'DRIVER NAME' => null,
                'DRIVER ID NO.' => null,
                'CLIENT NAME' => null,
                'INVOICE NUMBER' => null,
                'PACKAGES' => number_format($transporterTeas->sum('total_pallets'), 2),
                'GROSS WEIGHT' => number_format($transporterTeas->sum('total_weight'), 2),
                'PRODUCER WAREHOUSE' => null,
                'WAREHOUSE BRANCH' => null,
                'BRANCH LOCALITY' => null,
                'PMHL WAREHOUSE' => null,
                'DATE RECEIVED' => null,
            ];

            $extra = [
                'VEHICLE REGISTRATION' => null,
                'DRIVER NAME' => null,
                'DRIVER ID NO.' => null,
                'CLIENT NAME' => null,
                'INVOICE NUMBER' => null,
                'PACKAGES' => null,
                'GROSS WEIGHT' => null,
                'PRODUCER WAREHOUSE' => null,
                'WAREHOUSE BRANCH' => null,
                'BRANCH LOCALITY' => null,
                'PMHL WAREHOUSE' => null,
                'DATE RECEIVED' => null,
            ];

            $teas->push($transporterDetails);
            $teas->push([$totals]);
            $teas->push([$extra]);
        });

        return $teas->flatten(1);
    }

    public function headings(): array
    {
        return [
            ['PACKMAC HOLDINGS LIMITED'],
            ['Chai Street Shimanzi'],
            ['High Level, Shimanzi Area. Mombasa'],
            ['P.O Box 41932-80100, Mombasa, Kenya'],
            ['TEA TRANSPORT REPORT. PRINTED ON ' . Carbon::now()->format('D, d-m-Y H:i:s')],
            ['VEHICLE REG', 'DRIVER NAME', 'DRIVER ID NO.', 'CLIENT NAME', 'INVOICE NUMBER', 'PACKAGES', 'GROSS WEIGHT', 'PRODUCER WAREHOUSE', 'WAREHOUSE BRANCH', 'BRANCH LOCALITY', 'PMHL WAREHOUSE', 'DATE RECEIVED'],
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->mergeCells('A1:M1');
        $sheet->mergeCells('A2:M2');
        $sheet->mergeCells('A3:M3');
        $sheet->mergeCells('A4:M4');
        $sheet->mergeCells('A5:M5');

        // Center align text in merged cells
        $sheet->getStyle('A1:M5')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // Center align text in merged cells
//        $sheet->getStyle('A1:M1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
//        $sheet->getStyle('A3:M3')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // Apply additional styling
        $sheet->getStyle('1')->applyFromArray(['font' => ['bold' => true], 'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]]);
        $sheet->getStyle('5')->applyFromArray(['font' => ['bold' => true]]);
        $sheet->getStyle('6')->applyFromArray(['font' => ['bold' => true]]);
//        $sheet->getStyle('1')->applyFromArray(['font' => ['bold' => true]]);
//        $sheet->getStyle('3')->applyFromArray(['font' => ['bold' => true]]);

        // Return styles
        return [];
    }

}
