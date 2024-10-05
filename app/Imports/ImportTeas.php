<?php

namespace App\Imports;

use App\Models\Client;
use App\Models\DeliveryOrder;
use App\Models\Garden;
use App\Models\Grade;
use App\Models\Station;
use App\Models\StockIn;
use App\Models\WarehouseBay;
use App\Services\CustomIds;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Row;

class ImportTeas implements OnEachRow, WithHeadingRow
{
    use Importable;
    protected $errors = [];
    protected $deliveryNumber;

    public function __construct($deliveryNumber)
    {
        $this->deliveryNumber = $deliveryNumber;
    }

    public function onRow(Row $row)
    {
        DB::beginTransaction();

        try {
            $client = Client::where('client_name', trim($row['client_name']))->first();
            $garden = Garden::where('garden_name', trim($row['garden']))->first();
            $grade = Grade::where('grade_name', trim($row['grade']))->first();
            $station = Station::where('station_name', trim($row['warehouse']))->first();
            $deliveryId = (new CustomIds())->generateId();
            $stockId = (new CustomIds())->generateId();
            $deliveryType = 2;
            $package = trim($row['package_type']) === 'PB' ? 1 : (trim($row['package_type']) === 'PS' ? 2 : null);

            // Check if invoice number is unique for the client_id and not soft deleted
            $existingOrder = DeliveryOrder::withoutTrashed()->where(['invoice_number' => $row['invoice_number'], 'client_id' => $client->client_id, 'garden_id' => $garden->garden_id])->whereNull('deleted_at')->first();

            if ($existingOrder) {
                // Log error if a non-soft-deleted record with the same invoice number and client id exists
                Log::error('Duplicate invoice number for client: ' . $client->client_name . ', Invoice Number: ' . $row['invoice_number']);

                // Add the error to an array or collection for handling later
                $this->addError($row->getIndex(), 'Duplicate invoice number for client: ' . $client->client_name . ', Invoice Number: ' . $row['invoice_number']);

                // Skip this row and continue with the import process
                return;
            }

            $do = [
                'delivery_id' => $deliveryId,
                'client_id' => $client->client_id,
                'tea_id' => $row['tea_type'] === 'AUCTION TEAS' ? 1 : ($row['tea_type'] === 'PRIVATE TEAS' ? 2 : ($row['tea_type'] === NULL ? 3 : 4)),
                'garden_id' => $garden->garden_id,
                'grade_id' => $grade->grade_id,
                'order_number' => $this->deliveryNumber,
                'invoice_number' => $row['invoice_number'],
                'package' => $package,
                'packet' => $row['packages'],
                'weight' => $row['net_weight'],
                'created_by' => auth()->user()->user_id,
                'delivery_type' => $deliveryType,
            ];

            DeliveryOrder::create($do);

            $bay = WarehouseBay::where(['station_id' => $station->station_id, 'bay_name' => $row['warehouse_bay']])->first();

            $stock = [
                'stock_id' => $stockId,
                'delivery_id' => $deliveryId,
                'station_id' => $station->station_id,
                'date_received' => time(),
                'delivery_number' => $this->deliveryNumber,
                'warehouse_bay' => $bay->bay_id,
                'total_weight' => $row['net_weight'],
                'total_pallets' => $row['packages'],
                'pallet_weight' => 0,
                'package_tare' => $row['package_tare'] ?? 0,
                'net_weight' => $row['net_weight'],
                'user_id' => auth()->user()->user_id
            ];

            StockIn::create($stock);

            DB::commit();

        } catch (\Exception $e) {
            // Log the error
            Log::error('Error importing row: ' . $e->getMessage());

            // Add specific error to array
            $this->addError($row->getIndex(), 'Error importing row: ' . $e->getMessage());
        }
    }

    protected function addError($rowIndex, $error)
    {
        $this->errors[$rowIndex] = $error;
    }

    public function getErrors()
    {
        return $this->errors;
    }

}

