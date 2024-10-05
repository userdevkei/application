<?php

namespace App\Services;

use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
//class ExportBlendReport implements FromCollection, WithHeadings, ShouldAutoSize, WithStyles
//{
//    public $data;
//    private $boldRows = [];
//
//    public function __construct($data)
//    {
//        $this->data = $data;
//    }
//
//    public function collection()
//    {
//        $teas = collect([]);
//        $blendKey = 1;
//        $currentRow = 7; // Starting row after the headers
//
//        collect($this->data)->each(function ($blendTeas, $blendNumber) use (&$teas, &$blendKey, &$currentRow) {
//            $input_packages = 0;
//            $input_weight = 0;
////            foreach ($blendTeas as $totalTeas) {
////                $input_packages += $totalTeas->blended_packages;
////                $input_weight += $totalTeas->blended_weight;
////            }
//
//            $blendDetails = collect([
//                [
//                    '#' => $blendKey++,
//                    'DATE CREATED' => Carbon::parse($blendTeas[0]->created_at)->format('D, d/m/y H:i'),
//                    'CREATED BY' => $blendTeas[0]->surname.' '.$blendTeas[0]->first_name,
//                    'JOB SITE' => $blendTeas[0]->station_name,
//                    'SI NUMBER' => $blendNumber,
//                    'CLIENT NAME' => $blendTeas[0]->client_name,
//                    'PACKAGES' => number_format(floatval($input_packages), 2),
//                    'WEIGHT' => number_format(floatval($input_weight), 2),
//                    'BLD/STL' => $blendTeas[0]->contract,
//                    'CONSIGNEE' => $blendTeas[0]->consignee,
//                    'CONTAINER SIZE' => $blendTeas[0]->container_size == 1 ? '20 FT' :($blendTeas[0]->container_size == 2 ? '40 FT' : '40 FTHC'),
//                    'CARGO TYPE' => $blendTeas[0]->package_type == 1 ? 'PALLETIZED CARDBOARD' : ($blendTeas[0]->package_type == 2 ? 'PALLETIZED SLIPSHEETS' : ($blendTeas[0]->package_type == 3 ? 'PALLETIZED WOODEN' : 'LOOSE LOADING')),
//                    'DESTINATION' => $blendTeas[0]->port_name,
//                    'CLEARING AGENT' => $blendTeas[0]->agent_name,
//                    'TRANSPORTER' => $blendTeas[0]->transporter_name,
//                    'STATUS' => $blendTeas[0]->status == 0 ? 'BLEND CREATED' : ($blendTeas[0]->status == 1 ? 'TEAS UPDATED' : ($blendTeas[0]->status == 2 ? 'OUTTURN UPDATED' : ($blendTeas[0]->status == 3 ? 'PENDING APPROVAL' : 'BLEND SHIPPED')))
//                ],
//            ]);
//
//            // Keep track of the row number for bold styling
//            $this->boldRows[] = $currentRow;
//            $currentRow += 1;
//
//            $subHeadings = [
//                '#' => null,
//                'S/N' => null,
//                'GARDEN NAME' => null,
//                'GRADE' => null,
//                'INVOICE NUMBER' => null,
//                'PACKAGES' => null,
//                'NET WEIGHT' => null,
//            ];
//
//            $teaKey = 0;
//            $blendTeas->each(function ($tea) use (&$blendDetails, &$teaKey, &$currentRow) {
//                $blendDetails->push([
//                    '#' => null,
//                    'DATE CREATED' => ++$teaKey,
//                    'CREATED BY' => null,
//                    'DRIVER ID No.' => null,
//                    'CLIENT NAME' => null,
//                    'INVOICE NUMBER' => null,
//                    'PACKAGES' => null,
//                    'WEIGHT' => null,
//                ]);
//                $currentRow += 1;
//            });
//
//            $totals = [
//                'VEHICLE REGISTRATION' => 'TOTALS',
//                'DRIVER NAME' => null,
//                'DRIVER ID NO.' => null,
//                'CLIENT NAME' => null,
//                'INVOICE NUMBER' => null,
//                'PRODUCER WAREHOUSE' => null,
//                'PACKAGES' => number_format($blendTeas->sum('blended_packages'), 2),
//                'GROSS WEIGHT' => number_format($blendTeas->sum('blended_weight'), 2),
//                'WAREHOUSE BRANCH' => null,
//                'BRANCH LOCALITY' => null,
//                'PMHL WAREHOUSE' => null,
//                'DATE RECEIVED' => null,
//            ];
//
//            $extra = [
//                'VEHICLE REGISTRATION' => null,
//                'DRIVER NAME' => null,
//                'DRIVER ID NO.' => null,
//                'CLIENT NAME' => null,
//                'INVOICE NUMBER' => null,
//                'PACKAGES' => null,
//                'GROSS WEIGHT' => null,
//                'PRODUCER WAREHOUSE' => null,
//                'WAREHOUSE BRANCH' => null,
//                'BRANCH LOCALITY' => null,
//                'PMHL WAREHOUSE' => null,
//                'DATE RECEIVED' => null,
//            ];
//
//            $teas->push($blendDetails);
//            $teas->push([$subHeadings]);
//            $teas->push([$totals]);
//            $teas->push([$extra]);
//            $currentRow += 2; // Increment for the totals and extra rows
//        });
//
//        return $teas->flatten(1);
//    }
//
//    public function headings(): array
//    {
//        return [
//            ['PACKMAC HOLDINGS LIMITED'],
//            ['Chai Street Shimanzi'],
//            ['High Level, Shimanzi Area. Mombasa'],
//            ['P.O Box 41932-80100, Mombasa, Kenya'],
//            ['BLEND PROCESSING STATUS REPORT. PRINTED ON ' . Carbon::now()->format('D, d-m-Y H:i:s')],
//            ['#', 'DATE CREATED', 'CREATED BY', 'JOB SITE', 'SI NUMBER', 'CLIENT NAME', 'BLD/STL', 'CONSIGNEE', 'CONTAINER SIZE', 'CARGO TYPE', 'DESTINATION', 'PKGS', 'WEIGHT', 'CLEARING AGENT', 'TRANSPORTER', 'STATUS'],
//        ];
//    }
//
//    public function styles(Worksheet $sheet)
//    {
//        // Merge and center the header cells
//        $sheet->mergeCells('A1:P1');
//        $sheet->mergeCells('A2:P2');
//        $sheet->mergeCells('A3:P3');
//        $sheet->mergeCells('A4:P4');
//        $sheet->mergeCells('A5:P5');
//
//        // Center align text in merged cells
//        $sheet->getStyle('A1:P5')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
//
//        // Apply bold styling to the headers
//        $sheet->getStyle('A1:P1')->applyFromArray([
//            'font' => ['bold' => true],
//            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]
//        ]);
//        $sheet->getStyle('A2:P2')->applyFromArray(['font' => ['bold' => false]]);
//        $sheet->getStyle('A3:P3')->applyFromArray(['font' => ['bold' => false]]);
//        $sheet->getStyle('A4:P4')->applyFromArray(['font' => ['bold' => false]]);
//        $sheet->getStyle('A5:P5')->applyFromArray(['font' => ['bold' => true]]);
//        $sheet->getStyle('A6:P6')->applyFromArray(['font' => ['bold' => true]]);
//
//        // Apply bold styling to the rows tracked in $this->boldRows
//        foreach ($this->boldRows as $row) {
//            $sheet->getStyle('A' . $row . ':P' . $row)->applyFromArray([
//                'font' => ['bold' => true]
//            ]);
//        }
//
//        return [];
//    }
//}

class ExportBlendReport implements FromCollection, WithHeadings, ShouldAutoSize, WithStyles
{
    public $data;
    private $boldRows = [];

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function collection()
    {
        $teas = collect([]);
        $blendKey = 1;
        collect($this->data)->each(function ($blendTeas, $blendNumber) use (&$teas, &$blendKey, &$currentRow) {
            $blendDetails = collect([
                [
                    '#' => $blendKey++,
                    'DATE CREATED' => Carbon::parse($blendTeas[0]->created_at)->format('D, d/m/y H:i'),
                    'CREATED BY' => $blendTeas[0]->surname.' '.$blendTeas[0]->first_name,
                    'JOB SITE' => $blendTeas[0]->station_name,
                    'SI NUMBER' => $blendNumber,
                    'CLIENT NAME' => $blendTeas[0]->client_name,
                    'PACKAGES' => number_format(floatval($blendTeas[0]->shipped_packages), 2),
                    'WEIGHT' => number_format(floatval($blendTeas[0]->shipped_weight), 2),
                    'BLD/STL' => $blendTeas[0]->contract,
                    'CONSIGNEE' => $blendTeas[0]->consignee,
                    'CONTAINER SIZE' => $blendTeas[0]->container_size == 1 ? '20 FT' :($blendTeas[0]->container_size == 2 ? '40 FT' : '40 FTHC'),
                    'CARGO TYPE' => $blendTeas[0]->package_type == 1 ? 'PALLETIZED CARDBOARD' : ($blendTeas[0]->package_type == 2 ? 'PALLETIZED SLIPSHEETS' : ($blendTeas[0]->package_type == 3 ? 'PALLETIZED WOODEN' : 'LOOSE LOADING')),
                    'DESTINATION' => $blendTeas[0]->port_name,
                    'CLEARING AGENT' => $blendTeas[0]->agent_name,
                    'TRANSPORTER' => $blendTeas[0]->transporter_name,
                    'STATUS' => $blendTeas[0]->deleted_at !==null ? 'BLEND CANCELLED' : ($blendTeas[0]->status == 0 ? 'BLEND CREATED' : ($blendTeas[0]->status == 1 ? 'TEAS UPDATED' : ($blendTeas[0]->status == 2 ? 'OUTTURN UPDATED' : ($blendTeas[0]->status == 3 ? 'PENDING APPROVAL' : 'BLEND SHIPPED'))))
                ],
            ]);

            $teas->push($blendDetails);
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
            ['BLEND PROCESSING STATUS REPORT. PRINTED ON ' . Carbon::now()->format('D, d-m-Y H:i:s')],
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
