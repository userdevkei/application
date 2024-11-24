<?php

namespace App\Imports;

use App\Models\Client;
use App\Models\Broker;
use App\Models\Warehouse;
use App\Models\SubWarehouse;
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
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use Illuminate\Validation\ValidationException;

class ImportDOS implements OnEachRow, WithHeadingRow
{
    use Importable;
    protected $errors = [];
    protected $clientId;

    public function __construct($clientId)
    {
        $this->clientId = $clientId;
    }

    public function onRow(Row $row)
    {
        DB::beginTransaction();

        try {
            $client = Client::where('client_id',  $this->clientId)->first();
            $garden = Garden::where('garden_name', trim($row['garden_name']))->first();
            $grade = Grade::where('grade_name', trim($row['grade_name']))->first();
            $broker = Broker::where('broker_name', trim($row['broker_name']))->first();
            $warehouse = Warehouse::where('warehouse_name', $row['producer_warehouse'])->first();
            $subWarehouse = SubWarehouse::where(['sub_warehouse_name' => $row['sub_warehouse'], 'warehouse_id' => $warehouse->warehouse_id])->first();
            $locatlity = $row['warehouse_locality'] === 'ISLAND' ? 1 : ($row['warehouse_locality'] === 'CHANGAMWE' ? 2 : ($row['warehouse_locality'] === 'JOMVU' ? 3 : ($row['warehouse_locality'] === 'BONJE' ? 4 : 5)));
            $deliveryId = (new CustomIds())->generateId();
            $stockId = (new CustomIds())->generateId();
            $deliveryType = 1;
            $package = trim($row['package']) === 'PB' ? 1 : (trim($row['package']) === 'PS' ? 2 : null);

            // dd($row['sale_date']);

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

                // Check and convert Excel serial dates or standard d/m/Y formatted dates
                function parseExcelDate($date) {
                    if (is_numeric($date)) {
                        // Convert Excel serial date to Carbon instance
                        return Carbon::instance(ExcelDate::excelToDateTimeObject($date));
                    } else {
                        // Handle dates in d/m/Y format
                        return Carbon::createFromFormat('d/m/Y', $date);
                    }
                }
            
                $saleDate = parseExcelDate($row['sale_date']);
                $promptDate = parseExcelDate($row['prompt_date']);
            
                // Ensure sale_date is not greater than prompt_date
                if ($saleDate->greaterThan($promptDate)) {
                    throw ValidationException::withMessages([
                        'prompt_date' => 'Prompt date must be after or on the same day as sale date.'
                    ]);
                }

            $do = [
                'delivery_id' => $deliveryId,
                'client_id' => $this->clientId,
                'tea_id' => $row['tea_type'] === 'AUCTION TEAS' ? 1 : ($row['tea_type'] === 'PRIVATE TEAS' ? 2 : ($row['tea_type'] === 'FACTORY TEAS' ? 3 : ($row['tea_type'] === 'BLEND REMNANTS' ? 4 : null))),
                'garden_id' => $garden->garden_id,
                'grade_id' => $grade->grade_id,
                'order_number' => $row['do_number'],
                'invoice_number' => $row['invoice_number'],
                'package' => $package,
                'packet' => $row['total_packages'],
                'weight' => $row['net_weight'],
                'broker_id' => $broker->broker_id,
                'lot_number' => $row['lot_number'],
                'sale_number' => $row['sale_number'],
                'sale_date' => Carbon::parse($saleDate)->format('Y-m-d'),
                'prompt_date' => Carbon::parse($promptDate)->format('Y-m-d'),
                'warehouse_id' => $warehouse->warehouse_id,
                'sub_warehouse_id' => $subWarehouse->sub_warehouse_id,
                'locality' => $locatlity,
                'created_by' => auth()->user()->user_id,
                'delivery_type' => $deliveryType,
            ];

            DeliveryOrder::create($do);
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

