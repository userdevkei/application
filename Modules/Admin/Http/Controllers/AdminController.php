<?php

namespace Modules\Admin\Http\Controllers;

use DateTime;
use Carbon\Carbon;
use App\Models\Role;
use App\Models\User;
use App\Models\Grade;
use App\Services\Log;
use App\Models\Broker;
use App\Models\Client;
use App\Models\Driver;
use App\Models\Garden;
use App\Models\Station;
use App\Models\StockIn;
use App\Models\BlendTea;
use App\Models\Shipment;
use App\Models\UserInfo;
use App\Models\Transfers;
use App\Models\Warehouse;
use App\Models\BlendSheet;
use App\Services\TraceTea;
use AllowDynamicProperties;
use App\Imports\ImportTeas;
use App\Models\Destination;
use App\Models\Transporter;
use App\Services\CustomIds;
use App\Services\ExportTCI;
use App\Models\BlendBalance;
use App\Models\SubWarehouse;
use App\Models\WarehouseBay;
use Illuminate\Http\Request;
use App\Models\BlendMaterial;
use App\Models\BlendShipment;
use App\Models\ClearingAgent;
use App\Models\DeliveryOrder;
use App\Services\ExportStock;
use Illuminate\Validation\Rule;
use App\Models\BlendSupervision;
use App\Models\ExternalTransfer;
use PhpOffice\PhpWord\IOFactory;
use App\Models\ShipmentContainer;
use App\Services\ExportSTLReport;
use App\Models\LoadingInstruction;
use App\Services\ExportBlendSheet;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use App\Models\ShippingInstruction;
use App\Services\ExportBlendReport;
use App\Services\ExportTeaTransport;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpWord\Element\Table;
use PhpOffice\PhpWord\SimpleType\Jc;
use App\Services\ExportDeliveryOrders;
use Modules\Clerk\Entities\TeaSamples;
use function PHPUnit\Framework\isEmpty;
use App\Services\ExportExternalTransfer;
use PhpOffice\PhpWord\TemplateProcessor;
use App\Services\ExportInternalTransfers;
use Modules\Clerk\Entities\ReportRequest;
use Illuminate\Validation\Rules\RequiredIf;
use NcJoes\OfficeConverter\OfficeConverter;
use App\Services\ExportDirectDeliveryOrders;
use App\Services\ExportShippingInstructions;
use Illuminate\Contracts\Support\Renderable;
use Modules\Clerk\Entities\WarehouseLocation;

#[AllowDynamicProperties] class AdminController extends Controller
{
    protected $logger;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(Log $logger, TraceTea $traceService)
    {
        $this->logger = $logger;
        $this->traceService = $traceService;
    }
    public function traceTeaByInvoice(Request $request)
    {
        $traceData = $this->traceService->traceDeliveryOrder($request->invoice);

        if (!$traceData) {
            return back(['error' => 'Delivery order not found'], 404);
        }
        return view('admin::DOS.traceTea')->with('teas', $traceData);
    }

    public function traceTea($id)
    {
        $traceData = $this->traceService->traceDeliveryOrder($id);
        if (!$traceData) {
            return response()->json(['error' => 'Delivery order not found'], 404);
        }
        return view('admin::DOS.traceTea')->with('teas', $traceData);
    }
    public function index()
    {
        $orders = DeliveryOrder::leftJoin('loading_instructions', 'loading_instructions.delivery_id', '=', 'delivery_orders.delivery_id')
            ->select('loading_instructions.status as load_status', 'loading_instructions.deleted_at', 'delivery_orders.created_at as date_received', 'loading_number')
            ->whereNull('delivery_orders.deleted_at');

//// Clone the query builder instance for each variable
        $uncollected = clone $orders;
        $late = clone $orders;
        $noTCI = clone $orders;
        $overstayed = clone $orders;
//
        $uncollected = $uncollected->where('loading_instructions.status', 1)->where('loading_instructions.deleted_at', '=', null)->get();
        $threshold = Carbon::now();
        $late = $late->whereRaw("DATE_ADD(loading_instructions.created_at, INTERVAL 2 DAY) <= '$threshold'")->where('loading_instructions.deleted_at', '=', null)->where('loading_instructions.status', 1)->get();
        $noTCI = $noTCI->where('delivery_orders.delivery_type', 1)
                ->where(function ($noTCI) {
                    $noTCI->where('delivery_orders.status', 0)
                        ->orWhereNull('delivery_orders.status'); // Fixed: checks for null status
                })
                ->where(function ($noTCI) {
                    $noTCI->whereNull('loading_instructions.delivery_id')  // No matching loading instruction
                        ->orWhereNotNull('loading_instructions.deleted_at');  // Exists but only where deleted_at is not null
                })
                ->get();
        $now = \Carbon\Carbon::now();
        $overstayed = $overstayed->whereRaw("DATE_ADD(delivery_orders.prompt_date, INTERVAL 7 DAY) <= '$now'")
            ->where('loading_instructions.status', 1)
            ->where('loading_instructions.deleted_at', '=', null)
            ->get();
//
        $internal = Transfers::where('transfers.status', '<', 3)
            ->orWhere('transfers.status', null)
            ->whereNull('transfers.deleted_at')
            ->latest('transfers.created_at')
            ->get()
            ->groupBy('delivery_number');
//
        $external = ExternalTransfer::latest('external_transfers.created_at')
            ->where('external_transfers.status', '<', 3)
            ->orWhere('external_transfers.status', null)
            ->whereNull('external_transfers.deleted_at')
            ->orderBy('delivery_number', 'desc')
            ->get()
            ->groupBy('delivery_number');
//
        $si = ShippingInstruction::latest('shipping_instructions.created_at')
            ->where('shipping_instructions.status', '<', 4)
            ->orWhere('shipping_instructions.status', null)
            ->whereNull('shipping_instructions.deleted_at')
            ->get();

        $blend = DB::table('blend_sheets')->where('blend_sheets.status', '<', 4)
            ->orWhere('blend_sheets.status', null)
            ->whereNull('blend_sheets.deleted_at')
            ->latest('blend_sheets.created_at')
            ->get();

        $stocks = DB::table('currentstock')
            ->select('client_name')
            ->selectRaw('SUM(current_stock) as packages, SUM(current_weight) as net_weight')
            ->groupBy('client_name')
            ->orderBy('net_weight', 'desc')
            ->where('current_stock', '>', 0)
            ->get();

        $tcis = LoadingInstruction::whereIn('status',[null, 0, 1])->whereNull('deleted_at')->select('loading_number')->get()->groupBy('loading_number')->count();
        $clients = Client::all();
//
        return view('admin::welcome')->with(['blend' => $blend, 'si' => $si, 'internal' => $internal, 'external' => $external, 'uncollected' => $uncollected, 'late' => $late, 'noTCI' => $noTCI, 'overstayed' => $overstayed, 'tcis' => $tcis, 'clients' => $clients, 'stocks' => $stocks]);
    }

    public function dashboardReport($id)
    {
        $id = base64_decode($id);
        $orders = DeliveryOrder::join('users as u', 'u.user_id', '=', 'delivery_orders.created_by')
            ->join('gardens as g', 'g.garden_id', '=', 'delivery_orders.garden_id')
            ->join('grades as gr', 'gr.grade_id', '=', 'delivery_orders.grade_id')
            ->join('brokers as br', 'br.broker_id', '=', 'delivery_orders.broker_id')
            ->join('warehouses as wh', 'wh.warehouse_id', '=', 'delivery_orders.warehouse_id')
            ->leftJoin('sub_warehouses as sub', 'sub.sub_warehouse_id', '=', 'delivery_orders.sub_warehouse_id')
            ->join('clients as cl', 'cl.client_id', '=', 'delivery_orders.client_id')
            ->leftJoin('loading_instructions as li', function ($join) {
                $join->on('li.delivery_id', '=', 'delivery_orders.delivery_id')
                    ->whereNull('li.deleted_at');
            })
            ->leftJoin('drivers as dr', 'dr.driver_id', '=', 'li.driver_id')
            ->leftJoin('transporters as tr', 'tr.transporter_id', '=', 'li.transporter_id')
            ->leftJoin('stations as st', 'st.station_id', '=', 'li.station_id')
            ->leftJoin('users as lu', 'lu.user_id', '=', 'li.created_by')
            ->select('u.username', 'g.garden_name', 'gr.grade_name', 'br.broker_name', 'wh.warehouse_name', 'wh.warehouse_id', 'cl.client_name', 'delivery_orders.*', 'tr.transporter_id', 'tr.transporter_name', 'dr.driver_id', 'dr.driver_name', 'dr.id_number', 'dr.phone', 'li.loading_id', 'li.loading_number', 'li.status as load_status', 'li.registration', 'li.created_by as load_user_id', 'lu.username as load_user', 'st.station_name', 'st.station_id', 'sub.sub_warehouse_name', 'li.deleted_at', 'delivery_orders.created_at as date_received')
            ->whereNull('delivery_orders.deleted_at')
            ->where('delivery_orders.delivery_type', 1)
            ->orderBy('delivery_orders.created_at', 'desc');

        // Clone the query builder instance for each variable
        $uncollected = clone $orders;
        $late = clone $orders;
        $noTCI = clone $orders;
        $overstayed = clone $orders;

        $uncollected = $uncollected->where('li.status', 1)->get();
        $threshold = Carbon::now();
        $late = $late->whereRaw("DATE_ADD(li.created_at, INTERVAL 2 DAY) <= '$threshold'")->where('li.status', 1)->get();
        $noTCI = $noTCI->where('delivery_orders.delivery_type', 1)
                ->where(function ($noTCI) {
                    $noTCI->where('delivery_orders.status', 0)
                        ->orWhereNull('delivery_orders.status'); // Fixed: checks for null status
                })
                ->where(function ($noTCI) {
                    $noTCI->whereNull('li.delivery_id')  // No matching loading instruction
                        ->orWhereNotNull('li.deleted_at');  // Exists but only where deleted_at is not null
                })
                ->get();
        $now = \Carbon\Carbon::now();
        $overstayed = $overstayed->whereRaw("DATE_ADD(delivery_orders.prompt_date, INTERVAL 7 DAY) <= '$now'")->where('li.status', 1)->get();

        $internal = Transfers::join('stations', 'stations.station_id', '=', 'transfers.station_id')
            ->join('stations as destination_station', 'destination_station.station_id', '=', 'transfers.destination')
            ->join('delivery_orders', 'delivery_orders.delivery_id', '=', 'transfers.delivery_id')
            ->join('clients', 'clients.client_id', '=', 'delivery_orders.client_id')
            ->join('currentstock', function($join) {
                $join->on('currentstock.delivery_id', '=', 'transfers.delivery_id')
                    ->on('currentstock.stock_id', '=', 'transfers.stock_id');
            })
            ->select('transfers.created_at', 'stations.station_name', 'clients.client_name', 'destination', 'destination_station.station_name as destination_name', 'transfers.status', 'transfers.delivery_number')
            ->selectRaw('SUM(transfers.requested_palettes) as total_palettes, SUM(transfers.requested_weight) as total_weight')
            ->where(function ($query) {
                $query->where('transfers.status', '<', 3)
                    ->orWhereNull('transfers.status');
            })
            ->whereNull('transfers.deleted_at')
            ->latest('transfers.created_at')
            ->groupBy('transfers.created_at', 'stations.station_name', 'clients.client_name', 'transfers.destination', 'destination_station.station_name', 'transfers.status', 'transfers.delivery_number', 'stations.station_name')
            ->get();


        $external = ExternalTransfer::join('currentstock', 'currentstock.stock_id', '=', 'external_transfers.stock_id')
            ->leftJoin('warehouses', 'warehouses.warehouse_id', '=', 'external_transfers.warehouse_id')
            ->select('external_transfers.created_at', 'currentstock.client_name', 'external_transfers.status', 'warehouses.warehouse_name', 'external_transfers.delivery_number', 'currentstock.stocked_at as station_name', 'warehouses.warehouse_name as destination_name')
            ->latest('external_transfers.created_at')
            ->where('external_transfers.status', '<', 3)
            ->orWhere('external_transfers.status', null)
            ->whereNull('external_transfers.deleted_at')
            ->orderBy('delivery_number', 'desc')
            ->selectRaw('SUM(external_transfers.transferred_palettes) as total_palettes')
            ->selectRaw('SUM(external_transfers.transferred_weight) as total_weight')
            ->groupBy('external_transfers.created_at', 'currentstock.client_name', 'external_transfers.status', 'warehouses.warehouse_name', 'external_transfers.delivery_number', 'currentstock.stocked_at', 'warehouses.warehouse_name')
            ->get();

        $si = ShippingInstruction::join('clients', 'clients.client_id', '=', 'shipping_instructions.client_id')
            ->join('destinations', 'destinations.destination_id', '=', 'shipping_instructions.destination_id')
            ->join('stations', 'stations.station_id', '=', 'shipping_instructions.station_id')
            ->leftJoin('clearing_agents', 'clearing_agents.agent_id', '=', 'shipping_instructions.clearing_agent')
            ->leftJoin('transporters', 'transporters.transporter_id', '=', 'shipping_instructions.transporter_id')
            ->select('shipping_instructions.shipping_id', 'shipping_instructions.created_at', 'clients.client_name', 'shipping_instructions.clearing_agent', 'shipping_number', 'vessel_name', 'port_name', 'load_type', 'container_size', 'shipping_mark', 'consignee', 'shipping_instructions.status', 'shipping_instructions', 'escort', 'seal_number', 'agent_name', 'ship_date', 'container_number', 'container_tare', 'station_name')
            ->latest('shipping_instructions.created_at')
            ->where('shipping_instructions.status', '<', 4)
            ->orWhere('shipping_instructions.status', null)
            ->whereNull('shipping_instructions.deleted_at')
            ->get();

        $blend = DB::table('blend_sheets')
            ->join('clients', 'clients.client_id', '=', 'blend_sheets.client_id')
            ->join('destinations', 'destinations.destination_id', '=', 'blend_sheets.destination_id')
            ->leftJoin('blend_teas', 'blend_teas.blend_id', '=', 'blend_sheets.blend_id')
            ->leftJoin('drivers', 'drivers.driver_id', '=', 'blend_sheets.driver_id')
            ->leftJoin('clearing_agents', 'clearing_agents.agent_id', '=', 'blend_sheets.agent_id')
            ->leftJoin('transporters', 'transporters.transporter_id', '=', 'blend_sheets.transporter_id')
            ->leftJoin('stations', 'stations.station_id', '=', 'blend_sheets.station_id')
            ->select('blend_sheets.created_at', 'blend_sheets.blend_id as shipping_id', 'blend_sheets.client_id', 'client_name', 'clients.phone as cPhone', 'email', 'blend_number as shipping_number', 'vessel_name', 'blend_sheets.destination_id', 'port_name', 'shipping_mark', 'consignee', 'contract', 'grade', 'garden', 'blend_date', 'blend_sheets.status', 'container_size', 'clients.address', 'package_type', 'registration', 'transporter_name', 'transporters.transporter_id', 'driver_name', 'drivers.phone as driver_phone', 'container_tare', 'blend_shipped', 'agent_name', 'seal_number', 'escort', 'output_packages', 'output_weight', 'blend_sheets.packet_tare', 'blend_sheets.agent_id', 'id_number', 'stations.station_id', 'stations.station_name', 'stations.location_id', 'standard_details')
            ->selectRaw('SUM(blend_teas.blended_packages) as input_packages')
            ->selectRaw('SUM(blend_teas.blended_weight) as input_weight')
            ->groupBy('created_at', 'blend_sheets.blend_id', 'blend_sheets.client_id', 'client_name', 'clients.phone', 'email', 'blend_number', 'vessel_name', 'blend_sheets.destination_id', 'port_name', 'shipping_mark', 'consignee', 'contract', 'grade', 'garden', 'blend_date', 'blend_sheets.status', 'container_size', 'clients.address', 'package_type', 'registration', 'transporter_name', 'driver_name', 'driver_phone', 'container_tare', 'blend_shipped', 'agent_name', 'seal_number', 'escort', 'output_packages', 'output_weight', 'packet_tare', 'agent_id', 'transporter_id', 'id_number', 'station_id', 'station_name', 'standard_details', 'location_id')
            ->where('blend_sheets.status', '<', 4)
            ->orWhere('blend_sheets.status', null)
            ->whereNull('blend_teas.deleted_at')
            ->whereNull('blend_sheets.deleted_at')
            ->latest('blend_sheets.created_at')
            ->get();

        $id == 1 ? $report = $uncollected : ($id == 2 ? $report = $late : ($id == 3 ? $report = $noTCI : ($id == 4 ? $report = $overstayed : ($id == 5 ? $report = $internal : ($id == 6 ? $report = $external : ($id == 7 ? $report = $si : ($id == 8 ? $report = $blend : null )))))));
        if ($id <= 4){
            return view('admin::dashboard.collections')->with(['orders' => $report, 'id' => $id]);
        }elseif ($id >= 5 && $id <= 6){
            return view('admin::dashboard.transfers')->with(['orders' => $report, 'id' => $id]);
        }elseif($id >= 7){
            return view('admin::dashboard.sis')->with(['orders' => $report, 'id' => $id]);
        }
    }
    public function users()
    {
        $users = User::join('user_infos', 'user_infos.user_id', '=', 'users.user_id')
            ->join('roles', 'roles.id', '=', 'users.role_id')
            ->join('stations', 'stations.station_id', '=', 'users.station_id')
            ->join('warehouse_locations', 'warehouse_locations.location_id', '=', 'stations.location_id')
            ->select('users.username', 'users.username as staff_number', 'user_infos.first_name', 'user_infos.middle_name', 'user_infos.surname', DB::raw('CONCAT(user_infos.surname," ",user_infos.first_name," ",user_infos.middle_name) as staff_name'), 'user_infos.gender', 'user_infos.phone_number', 'user_infos.email_address', 'user_infos.id_number', 'roles.role_name', 'roles.type', 'stations.station_name', 'users.role_id', 'users.station_id', 'users.user_id', 'users.created_by', 'users.updated_by', 'users.created_at', 'users.status', 'stations.location_id', 'location_name')
            ->orderBy('roles.id', 'asc')
            ->orderBy('users.username', 'asc')
            ->orderBy('users.role_id', 'asc')
            ->get();
        $roles = Role::latest()->get();
        $stations = Station::latest()->get();
        return view('admin::users.users')->with(['users' => $users, 'roles' => $roles, 'stations' => $stations]);
    }

    public function registerUser(Request $request)
    {
        $request->validate([
            'first_name' => 'required|alpha',
            'surname' => 'required|alpha',
//            'other' => 'required_if:other_field_name,1|alpha',
            'id_number' => 'required|max:8',
            'gender' => 'required',
            'station' => 'required',
            'role' => 'required',
            'phone' => ['required', 'max:13', 'min:9', 'regex:/^(07|\+2547|01|\+2541|7|1)\d{8}$/'],
            'email' => 'string|required'
        ]);

        $initial = $request->role == 1 ? 'AD' :($request->role == 2 ? 'OP' :( $request->role == 7 ? 'GA' : 'CL'));

        $users = User::whereRaw("LEFT(username, 2) = '" . date('y') . "'")->count();
        $staffNumber = date('y') . $initial. str_pad($users + 1, 3, '0', STR_PAD_LEFT);

        $customId = new CustomIds();
        $userID = $customId->generateId();

        $userLogin = [
            'user_id' => $userID,
            'username' => $staffNumber,
            'password' => Hash::make($request->id_number),
            'role_id' => $request->role,
            'station_id' => $request->station,
            'created_by' => auth()->user()->user_id
        ];

        $userInfo = [
            'user_id' => $userID,
            'first_name' => strtoupper($request->first_name),
            'middle_name' => strtoupper($request->other),
            'surname' => strtoupper($request->surname),
            'gender' => $request->gender,
            'phone_number' => $request->phone,
            'email_address' => $request->email,
            'id_number' => $request->id_number
        ];

        User::create($userLogin);
        UserInfo::create($userInfo);

        $this->logger->create();

        return redirect()->back()->with('success', 'User account registered successfully');

    }

    public function updateUser(Request $request, $id)
    {
        $request->validate([
            'first_name' => 'required|alpha',
            'surname' => 'required|alpha',
            'id_number' => 'required|max:8',
            'gender' => 'required',
            'station' => 'required',
            'role' => 'required',
            'phone' => ['required', 'max:13', 'min:9', 'regex:/^(07|\+2547|01|\+2541|7|1)\d{8}$/'],
            'email' => 'string|required'
        ]);

        $userLogin = [
            'role_id' => $request->role,
            'station_id' => $request->station,
            'updated_by' => auth()->user()->user_id
        ];

        $userInfo = [
            'first_name' => strtoupper($request->first_name),
            'middle_name' => strtoupper($request->other),
            'surname' => strtoupper($request->surname),
            'gender' => $request->gender,
            'phone_number' => $request->phone,
            'email_address' => $request->email,
            'id_number' => $request->id_number,
        ];

        User::where('user_id', $id)->update($userLogin);

        UserInfo::where('user_id', $id)->update($userInfo);

        $this->logger->create();

        return redirect()->back()->with('success', 'User account details updated successfully');
    }

    public function disableStaff($id)
    {
        User::find($id)->update(['status' => 2]);
        return redirect()->back()->with('success', 'User account details updated successfully');
    }

    public function registerRole(Request $request)
    {
        $request->validate([
            'role_name' => 'required|unique:roles',
        ]);

        Role::create(['role_name' => strtoupper($request->role_name), 'created_by' => auth()->user()->user_id]);

        $this->logger->create();

        return redirect()->back()->with('success', 'Role registered successfully');
    }

    public function registerStation(Request $request)
    {
        $request->validate([
            'station_name' => 'required|unique:stations,station_name|string',
        ]);

        $customId = new CustomIds();
        $stationId = $customId->generateId();

        $station = [
            'station_name' => strtoupper($request->station_name),
            'capacity' => strtoupper($request->capacity),
            'address' => strtoupper($request->address),
            'status' => $request->status,
            'station_id' => $stationId,
            'location_id' => $request->location,
            'created_by' => auth()->user()->user_id
        ];

        Station::create($station);

        $this->logger->create();
        return redirect()->back()->with('success', 'Station registered successfully');
    }

    public function viewStations()
    {
        $stations = Station::leftJoin('users', 'users.user_id', '=', 'stations.created_by')
            ->leftJoin('warehouse_locations', 'warehouse_locations.location_id', '=', 'stations.location_id')
            ->orderBy('stations.created_at', 'desc')
            ->select('stations.station_id', 'stations.station_name', 'stations.capacity', 'stations.address', 'stations.status', 'users.username', 'stations.location_id', 'location_name')
            ->get();
        $locations = WarehouseLocation::where('status', 1)->get();
        return view('admin::warehouses.stations')->with(['stations' => $stations, 'locations' => $locations]);
    }

    public function updateStation(Request $request, $id)
    {
        $request->validate([
            'station_name' => 'required|string|unique:stations,station_name,'.$id.',station_id',
        ]);

        $station = [
            'station_name' => strtoupper($request->station_name),
            'capacity' => strtoupper($request->capacity),
            'address' => strtoupper($request->address),
            'location_id' => $request->location,
            'status' => $request->status,
            'updated_by' => auth()->user()->user_id
        ];

        Station::where('station_id', $id)->update($station);

        $this->logger->create();

        return redirect()->back()->with('success', 'Station details updated successfully');
    }

    public function updateWarehouseBays(Request $request, $id)
    {
        $request->validate([
            'warehouseBay.*' => 'required|string'
        ]);

        $customId  = new CustomIds();

        foreach ($request->warehouseBay as $bay){
            $sub = [
                'bay_id' => $customId->generateId(),
                'station_id' => $id,
                'bay_name' => strtoupper($bay),
                'created_by' => auth()->user()->user_id
            ];

            WarehouseBay::create($sub);

            $this->logger->create();
        }

        return redirect()->back()->with('success', 'Success! Warehouse bay(s) registered successfully');
    }

    public function updateSubwarehouseName(Request $request, $id)
    {
        $request->validate(['newBay' => 'required']);
        WarehouseBay::where('bay_id', $id)->update(['bay_name' => $request->newBay]);
        return redirect()->back()->with('success', 'Success! Warehouse bay name updated successfully');
    }

    public function viewRoles()
    {
        $roles = Role::leftJoin('users', 'users.user_id', '=', 'roles.created_by')
            ->orderBy('roles.created_at', 'asc')
            ->select('roles.id', 'roles.role_name', 'users.username')
            ->get();
        return view('admin::users.roles')->with(['roles' => $roles]);
    }

    public function updateRoles(Request $request, $id)
    {
        $request->validate([
            'role_name' => 'required|unique:roles',
        ]);

        Role::where('id', $id)->update(['role_name' => strtoupper($request->role_name), 'updated_by' => auth()->user()->user_id]);
        $this->logger->create();
        return redirect()->back()->with('success', 'Role updated successfully');
    }

    public function viewClients()
    {
        $clients = Client::join('users', 'users.user_id', '=', 'clients.created_by')
            ->orderBy('clients.created_at', 'desc')
            ->select('clients.client_id', 'clients.client_name', 'clients.client_type', 'clients.phone', 'clients.email', 'clients.address', 'users.username')
            ->get();

        return view('admin::users.clients')->with(['clients' => $clients]);
    }

    public function registerClient(Request $request)
    {
        $request->validate([
            'client' => 'required|string|unique:clients,client_name',
            'client_type' => 'required',
            'phone' => [
                'nullable',
                'max:15', 'min:9',
                Rule::unique('clients', 'phone')->ignore($request->input('phone'), 'phone'),
            ],
            'email' => [
                'nullable',
                Rule::unique('clients', 'email')->ignore($request->input('email'), 'email'),
            ], 'address' => [
                'nullable',
                Rule::unique('clients', 'address')->ignore($request->input('address'), 'address'),
            ],

        ]);

        $customId = new CustomIds();
        $clientId = $customId->generateId();

        $client = [
            'client_id' => $clientId,
            'client_name' => strtoupper($request->client),
            'client_type' => $request->client_type,
            'email' => strtolower($request->email),
            'phone' => $request->phone,
            'address' => strtoupper($request->address),
            'created_by' => auth()->user()->user_id,
        ];

        Client::create($client);
        $this->logger->create();
        return redirect()->back()->with('success', 'Successful! New client added successfully');
    }

    public function updateClient(Request $request, $id)
    {
        $request->validate([
            'client' => 'required|string',
            'client_type' => 'required',
            'phone' => [
                'nullable',
                'max:15', 'min:9',
                Rule::unique('clients', 'phone')->ignore($request->input('phone'), 'phone'),
            ],
            'email' => [
                'nullable',
                Rule::unique('clients', 'email')->ignore($request->input('email'), 'email'),
            ], 'address' => [
                'nullable',
                Rule::unique('clients', 'address')->ignore($request->input('address'), 'address'),
            ],

        ]);

        Client::where('client_id', $id)->update(
            [
                'client_name' => strtoupper($request->client),
                'client_type' => $request->client_type,
                'email' => strtolower($request->email),
                'phone' => $request->phone,
                'address' => strtoupper($request->address),
                'updated_by' => auth()->user()->user_id,
            ]
        );

        $this->logger->create();
        return redirect()->back()->with('success', 'Successful! Client updated successfully');
    }

    public function viewTeaGrade()
    {
        $grades = Grade::join('users', 'users.user_id', '=', 'grades.created_by')
            ->orderBy('grades.created_at', 'desc')
            ->select('grades.grade_id', 'grades.grade_name', 'grades.description', 'users.username')
            ->get();
        return view('admin::teas.grades')->with(['grades' => $grades]);

    }

    public function registerTeaGrade(Request $request)
    {
        $request->validate([
            'grade' => 'required|string|unique:grades,grade_name'
        ]);

        $customId = new CustomIds();
        $gradeId = $customId->generateId();

        $grade = [
            'grade_id' => $gradeId,
            'grade_name' => $request->grade,
            'description' => $request->description,
            'created_by' => auth()->user()->user_id,
        ];

        Grade::create($grade);

        $this->logger->create();
        return redirect()->back()->with('success', 'Successful! New tea grade added successfully');
    }

    public function updateTeaGrade(Request $request, $id)
    {
        $request->validate([
            'grade' => 'required|string'
        ]);

        Grade::where('grade_id', $id)->update(
            [
                'grade_name' => $request->grade,
                'description' => $request->description,
                'updated_by' => auth()->user()->user_id,
            ]
        );

        $this->logger->create();

        return redirect()->back()->with('success', 'Successful! Client updated successfully');
    }

    public function viewGardens()
    {
        $gardens = Garden::join('users', 'users.user_id', '=', 'gardens.created_by')
            ->orderBy('gardens.created_at', 'desc')
            ->select('gardens.garden_id', 'gardens.garden_name', 'gardens.garden_type', 'gardens.description', 'users.username')
            ->get();
        return view('admin::teas.gardens')->with(['gardens' => $gardens]);
    }

    public function registerGarden(Request $request)
    {
        $request->validate([
            'garden' => 'required|string|unique:gardens,garden_name',
            'garden_type' => 'required'
        ]);

        $customId = new CustomIds();
        $gardenId = $customId->generateId();

        $garden = [
            'garden_id' => $gardenId,
            'garden_name' => strtoupper($request->garden),
            'garden_type' => $request->garden_type,
            'created_by' => auth()->user()->user_id,
            'description' => $request->description,
            'status' => 1,
        ];

        Garden::create($garden);

        $this->logger->create();
        return redirect()->back()->with('success', 'Successful! New garden added successfully');
    }

    public function updateGarden(Request $request, $id)
    {
        $request->validate([
            'garden' => 'required|string',
            'garden_type' => 'required'
        ]);

        Garden::where('garden_id', $id)->update([
            'garden_name' => strtoupper($request->garden),
            'garden_type' => $request->garden_type,
            'updated_by' => auth()->user()->user_id,
            'description' => $request->description,
            'status' => 1,
        ]);

        $this->logger->create();
        return redirect()->back()->with('success', 'Successful! Garden updated successfully');
    }

    public function viewTransporters()
    {
        $transporters = Transporter::join('users', 'users.user_id', '=', 'transporters.created_by')
            ->orderBy('transporters.created_at', 'desc')
            ->select('transporters.transporter_id', 'transporters.transporter_name', 'transporters.description', 'transporters.transporter_type', 'users.username')
            ->get();
        return view('admin::logistics.transporters')->with('transporters', $transporters);
    }

    public function registerTransporter(Request $request)
    {
        $request->validate([
            'transporter' => 'required|string|unique:transporters,transporter_name',
            'transporter_type' => 'required',
        ]);

        if ($request->transporter_type == 3){

            $customId = new CustomIds();
            $transporterId = $customId->generateId();

            $transporter = [
                'transporter_id' => $transporterId,
                'transporter_name' => strtoupper($request->transporter).' (INBOUND)',
                'transporter_type' => 1,
                'description' => $request->description,
                'created_by' => auth()->user()->user_id,
            ];

            Transporter::create($transporter);

            $customId = new CustomIds();
            $transporterId = $customId->generateId();

            $transporter = [
                'transporter_id' => $transporterId,
                'transporter_name' => strtoupper($request->transporter).' (OUTBOUND)',
                'transporter_type' => 2,
                'description' => $request->description,
                'created_by' => auth()->user()->user_id,
            ];

            Transporter::create($transporter);
        }else{
            $customId = new CustomIds();
            $transporterId = $customId->generateId();

            $transporter = [
                'transporter_id' => $transporterId,
                'transporter_name' => strtoupper($request->transporter),
                'transporter_type' => $request->transporter_type,
                'description' => $request->description,
                'created_by' => auth()->user()->user_id,
            ];

            Transporter::create($transporter);
        }

        $this->logger->create();

        return redirect()->back()->with('success', 'Successful! New transporter added successfully');
    }

    public function updateTransporter(Request $request, $id)
    {
        $request->validate([
            'transporter' => 'required|string',
            'transporter_type' => 'required',
        ]);
        Transporter::where('transporter_id', $id)->update(
            [
                'transporter_name' => strtoupper($request->transporter),
                'transporter_type' => $request->transporter_type,
                'description' => $request->description,
                'updated_by' => auth()->user()->user_id,
            ]
        );

        $this->logger->create();
        return redirect()->back()->with('success', 'Successful! Transporter updated successfully');
    }

    public function viewWarehouses()
    {
        $warehouses = Warehouse::join('users', 'users.user_id', '=', 'warehouses.created_by')
            ->orderBy('warehouses.created_at', 'desc')
            ->select('users.username', 'warehouses.warehouse_id', 'warehouses.warehouse_name', 'warehouses.phone', 'warehouses.address', 'warehouses.updated_by')
            ->get();
        return view('admin::warehouses.warehouses')->with('warehouses', $warehouses);
    }

    public function registerWarehouse(Request $request)
    {
        $request->validate([
            'warehouse' => 'required|string|unique:warehouses,warehouse_name',
        ]);

        $customId = new CustomIds();
        $warehouseId = $customId->generateId();

        $warehouse = [
            'warehouse_id' => $warehouseId,
            'warehouse_name' => strtoupper($request->warehouse),
            'phone' => $request->phone,
            'address' => strtoupper($request->address),
            'created_by' => auth()->user()->user_id
        ];

        Warehouse::create($warehouse);

        $this->logger->create();
        return redirect()->back()->with('success', 'Successful! New warehouse added successfully');
    }

    public function updateWarehouse(Request $request, $id)
    {
        $request->validate([
            'warehouse' => 'required|string',
        ]);

        Warehouse::where('warehouse_id', $id)->update([
            'warehouse_name' => strtoupper($request->warehouse),
            'phone' => $request->phone,
            'address' => strtoupper($request->address),
            'updated_by' => auth()->user()->user_id
        ]);

        $this->logger->create();
        return redirect()->back()->with('success', 'Successful! New warehouse added successfully');
    }

    public function updateWarehouseLocations(Request $request, $id)
    {
        $request->validate([
            'subWarehouse.*' => 'required|string|unique:sub_warehouses,sub_warehouse_name'
        ]);

        $customId  = new CustomIds();

        foreach ($request->subWarehouse as $warehouse){
            $sub = [
                'sub_warehouse_id' => $customId->generateId(),
                'warehouse_id' => $id,
                'sub_warehouse_name' => strtoupper($warehouse),
                'created_by' => auth()->user()->user_id
            ];

            SubWarehouse::create($sub);
            $this->logger->create();
        }

        return redirect()->back()->with('success', 'Success! Warehouse sub stations created successfully');
    }

    public function viewBrokers()
    {
        $brokers = Broker::join('users', 'users.user_id', '=', 'brokers.created_by')
            ->orderBy('brokers.created_at', 'desc')
            ->select('users.username', 'brokers.broker_id', 'brokers.broker_name', 'brokers.phone', 'brokers.email', 'brokers.address', 'brokers.broker_type')
            ->get();
        return view('admin::users.brokers')->with('brokers', $brokers);
    }

    public function registerBroker(Request $request)
    {
        $request->validate([
            'broker' => 'required|string|unique:brokers,broker_name',
            'broker_type' => 'required',
            'phone' => [
                'nullable',
                'max:15', 'min:9',
                Rule::unique('clients', 'phone')->ignore($request->input('phone'), 'phone'),
            ],
            'email' => [
                'nullable',
                Rule::unique('clients', 'email')->ignore($request->input('email'), 'email'),
            ], 'address' => [
                'nullable',
                Rule::unique('clients', 'address')->ignore($request->input('address'), 'address'),
            ],

        ]);

        $customId = new CustomIds();
        $brokerId = $customId->generateId();

        $client = [
            'broker_id' => $brokerId,
            'broker_name' => strtoupper($request->broker),
            'broker_type' => $request->broker_type,
            'email' => strtolower($request->email),
            'phone' => $request->phone,
            'address' => strtoupper($request->address),
            'created_by' => auth()->user()->user_id,
        ];

        Broker::create($client);
        $this->logger->create();
        return redirect()->back()->with('success', 'Successful! New broker added successfully');
    }

    public function updateBroker(Request $request, $id)
    {
        $request->validate([
            'broker' => 'required|string',
            'broker_type' => 'required',
            'phone' => [
                'nullable',
                'max:15', 'min:9',
                Rule::unique('clients', 'phone')->ignore($request->input('phone'), 'phone'),
            ],
            'email' => [
                'nullable',
                Rule::unique('clients', 'email')->ignore($request->input('email'), 'email'),
            ], 'address' => [
                'nullable',
                Rule::unique('clients', 'address')->ignore($request->input('address'), 'address'),
            ],

        ]);

        Broker::where('broker_id', $id)->update(
            [
                'broker_name' => strtoupper($request->broker),
                'broker_type' => $request->broker_type,
                'email' => strtolower($request->email),
                'phone' => $request->phone,
                'address' => strtoupper($request->address),
                'updated_by' => auth()->user()->user_id,
            ]
        );

        $this->logger->create();
        return redirect()->back()->with('success', 'Successful! Broker updated successfully');
    }

    public function viewShippingVessels ()
    {
        $vessels = Vessel::latest()->get();
        return view('admin::admin.vessels.viewShippingVessels')->with(['vessels' => $vessels]);
    }

    public function addShippingVessel(Request $request)
    {
        $request->validate([
            'company_name' => 'required',
            'vessel_name' => 'required',
        ]);

        $customId = new CustomIds();

        $vessel = [
            'vessel_id' => $customId->generateId(),
            'company_name' => $request->company_name,
            'vessel_name' => $request->vessel_name,
            'status' => 1,
            'created_by' => auth()->user()->user_id
        ];

        $exVessel = Vessel::where(['company_name' => $request->company_name, 'vessel_name' => $request->vessel_name])->first();

        if ($exVessel){
            return redirect()->back()->with('info', 'Oops! Vessel already created');
        }else{
            Vessel::create($vessel);
            $this->logger->create();
        }
        return redirect()->back()->with('success', 'Success! Vessel created successfully');
    }

    public function updateShippingVessel(Request $request, $id)
    {
        $request->validate([
            'company_name' => 'required',
            'vessel_name' => 'required',
        ]);

        $vessel = [
            'company_name' => $request->company_name,
            'vessel_name' => $request->vessel_name,
        ];
        Vessel::where('vessel_id', $id)->update($vessel);
        $this->logger->create();

        return redirect()->back()->with('success', 'Success! Vessel updated successfully');
    }

    public function viewShippingDestinations ()
    {
        $destinations = Destination::latest()->get();
        return view('admin::logistics.destinations')->with(['destinations' => $destinations]);
    }

    public function addShippingDestination(Request $request)
    {
        $request->validate([
            'country_name' => 'required',
            'port_name' => 'required',
        ]);

        $customId = new CustomIds();

        $destination = [
            'destination_id' => $customId->generateId(),
            'country_name' => $request->country_name,
            'port_name' => $request->port_name,
            'status' => 1,
            'created_by' => auth()->user()->user_id
        ];

        $exDestination = Destination::where(['country_name' => $request->country_name, 'port_name' => $request->port_name])->first();

        if ($exDestination){
            return redirect()->back()->with('info', 'Oops! Destination already created');
        }else{
            Destination::create($destination);
            $this->logger->create();
        }
        return redirect()->back()->with('success', 'Success! Destination created successfully');
    }

    public function updateShippingDestination(Request $request, $id)
    {
        $request->validate([
            'country_name' => 'required',
            'port_name' => 'required',
        ]);

        $destination = [
            'country_name' => $request->country_name,
            'port_name' => $request->port_name,
        ];
        Destination::where('destination_id', $id)->update($destination);
        $this->logger->create();

        return redirect()->back()->with('success', 'Success! Destination updated successfully');
    }

    public function viewClearingAgents()
    {
        $agents = ClearingAgent::latest()->get();
        return view('admin::users.agents')->with(['agents' => $agents]);
    }

    public function addClearingAgent(Request $request)
    {
        $request->validate([
            'agent_name' => 'required',
            'agent_type' => 'required',
        ]);

        $customId = new CustomIds();

        $agent = [
            'agent_id' => $customId->generateId(),
            'agent_name' => $request->agent_name,
            'agent_type' => $request->agent_type,
            'status' => 1,
            'created_by' => auth()->user()->user_id
        ];

        $exAgent= ClearingAgent::where(['agent_name' => $request->agent_name, 'agent_type' => $request->agent_type])->first();

        if ($exAgent){
            return redirect()->back()->with('info', 'Oops! Clerical agent already created');
        }else{
            ClearingAgent::create($agent);
            $this->logger->create();
        }
        return redirect()->back()->with('success', 'Success! Clerical agent created successfully');
    }

    public function updateClearingAgent(Request $request, $id)
    {
        $request->validate([
            'agent_name' => 'required',
            'agent_type' => 'required',
        ]);

        $agent = [
            'agent_name' => $request->agent_name,
            'agent_type' => $request->agent_type,
        ];
        ClearingAgent::where('agent_id', $id)->update($agent);
        $this->logger->create();

        return redirect()->back()->with('success', 'Success!  Clerical agent updated successfully');
    }

    public function viewLLIs ()
    {
        $instructions  = LoadingInstruction::join('stations', 'stations.station_id', '=', 'loading_instructions.station_id')
            ->join('delivery_orders', 'delivery_orders.delivery_id', '=', 'loading_instructions.delivery_id')
            ->join('warehouses', 'warehouses.warehouse_id', '=', 'delivery_orders.warehouse_id')
            ->join('sub_warehouses', 'sub_warehouses.sub_warehouse_id', '=', 'delivery_orders.sub_warehouse_id')
            ->join('clients', 'clients.client_id', '=', 'delivery_orders.client_id')
            ->select('stations.*', 'delivery_orders.*', 'loading_instructions.*', 'warehouses.*', 'sub_warehouses.sub_warehouse_name', 'clients.*', 'loading_instructions.status as lli_status')
            ->latest('loading_instructions.created_at')
            ->get()
            ->groupBy('loading_number');

        return view('admin::DOS.collection')->with(['instructions' => $instructions]);

    }
    public function fetchIdNumber(Request $request)
    {
        $data = Driver::where('id_number', $request->idNumber)->latest()->first();

        $driver = [
            'driver_name' => $data->driver_name,
            'driver_id' => $data->id_number,
            'driver_phone' => $data->phone,
        ];

        return response()->json($driver);
    }
    public function createLLI(Request $request)
    {
        $request->validate([
            'station' => 'required',
            'deliveryIds' => 'required',
        ]);


        $instructions =  LoadingInstruction::withTrashed()->get()->groupBy('loading_number')->count();
        $loadingNumber = 'TCI'.str_pad($instructions + 1, 4, '0', STR_PAD_LEFT);
        $driver = Driver::where('id_number', $request->idNumber)->first();

        if ($driver || $request->idNumber == null){

            $customId = new CustomIds();

            foreach ($request->deliveryIds as $delivery){

                $loadingId = $customId->generateId();
                $load = [
                    'loading_id' => $loadingId,
                    'station_id' =>  $request->station,
                    'loading_number' => $loadingNumber,
                    'transporter_id' => $request->transporter,
                    'delivery_id' => $delivery,
                    'registration' => strtoupper($request->registration),
                    'driver_id' => $driver == null ? null : $driver->driver_id,
                    'created_by' => auth()->user()->user_id,
                    'status' => 1,
                ];

                if (LoadingInstruction::create($load)){
                    DeliveryOrder::where('delivery_id', $delivery)->update(['status' => 1]);
                }
            }

        }else{

            $customId = new CustomIds();
            $driverId = $customId->generateId();

            $newDriver = [
                'driver_id' => $driverId,
                'id_number' => $request->idNumber,
                'driver_name' => strtoupper($request->driverName),
                'phone' => $request->driverPhone
            ];

            Driver::create($newDriver);

            foreach ($request->deliveryIds as $delivery){
                $loadingId = $customId->generateId();

                $load = [
                    'loading_id' => $loadingId,
                    'station_id' =>  $request->station,
                    'loading_number' => $loadingNumber,
                    'transporter_id' => $request->transporter,
                    'delivery_id' => $delivery,
                    'registration' => strtoupper($request->registration),
                    'driver_id' => $driverId,
                    'created_by' => auth()->user()->user_id,
                    'status' => 1,
                ];

                if (LoadingInstruction::create($load)){
                    DeliveryOrder::where('delivery_id', $delivery)->update(['status' => 1]);
                }

            }

        }

        $this->logger->create();

        return redirect()->back()->with('success', 'Successful! Local loading instructions added successfully');
    }

    public function downloadLLI($id)
    {
        list($loadNumber, $type) = explode(':', base64_decode($id));

        $orders = DeliveryOrder::join('users', 'users.user_id', '=', 'delivery_orders.created_by')
            ->join('user_infos', 'user_infos.user_id', '=', 'delivery_orders.created_by')
            ->join('gardens', 'gardens.garden_id', '=', 'delivery_orders.garden_id')
            ->join('grades', 'grades.grade_id', '=', 'delivery_orders.grade_id')
            ->join('brokers', 'brokers.broker_id', '=', 'delivery_orders.broker_id')
            ->join('warehouses', 'warehouses.warehouse_id', '=', 'delivery_orders.warehouse_id')
            ->join('sub_warehouses', 'sub_warehouses.sub_warehouse_id', '=', 'delivery_orders.sub_warehouse_id')
            ->join('clients', 'clients.client_id', '=', 'delivery_orders.client_id')
            ->leftJoin('loading_instructions', 'loading_instructions.delivery_id', '=', 'delivery_orders.delivery_id')
            ->leftJoin('drivers', 'drivers.driver_id', '=', 'loading_instructions.driver_id')
            ->leftJoin('transporters', 'transporters.transporter_id', '=', 'loading_instructions.transporter_id')
            ->leftJoin('stations', 'stations.station_id', '=', 'loading_instructions.station_id')
            ->leftJoin('users as loading_user', 'loading_user.user_id', '=', 'loading_instructions.created_by')
            ->leftJoin('stock_ins', 'stock_ins.delivery_id', '=', 'delivery_orders.delivery_id')
            ->whereNull('loading_instructions.deleted_at')
            ->select('users.username','users.user_id', 'gardens.garden_name', 'grades.grade_name', 'brokers.broker_name', 'warehouses.warehouse_name', 'clients.client_name', 'delivery_orders.*', 'transporters.transporter_id', 'transporters.transporter_name', 'drivers.driver_id', 'drivers.driver_name', 'drivers.id_number', 'drivers.phone', 'loading_instructions.loading_id', 'loading_instructions.loading_number', 'loading_instructions.status as load_status', 'loading_instructions.registration', 'loading_instructions.created_by as load_user_id', 'loading_user.username as load_user', 'stations.station_name', 'loading_instructions.deleted_at', 'stock_ins.total_pallets', 'stock_ins.total_weight', 'first_name', 'surname', 'sub_warehouse_name')
            ->orderBy('delivery_orders.created_at', 'desc')
            ->where('loading_number', $loadNumber)
            ->get();

        if ($type == 2){
            return Excel::download(new ExportTCI($orders), $loadNumber.' '.time().'.xlsx', \Maatwebsite\Excel\Excel::XLSX);
        }

        $details = $orders[0];

        $prepared = UserInfo::where('user_id', $details->user_id)->first();

        $user = $prepared->first_name.' '.$prepared->surname;
        $by = auth()->user()->user->first_name.' '.auth()->user()->user->last_name;

        $domPdfPath = base_path('vendor/dompdf/dompdf');
        \PhpOffice\PhpWord\Settings::setPdfRendererPath($domPdfPath);
        \PhpOffice\PhpWord\Settings::setPdfRendererName('DomPDF');

        $table = new Table(['unit' => \PhpOffice\PhpWord\SimpleType\TblWidth::TWIP, 'width' => 1400 * 1400, 'align' => 'center']);

        $header = ['size' => 8, 'name' => 'Cambria', 'space' => ['before' => 100, 'after' => 100], 'bold' => true];
        $text = ['size' => 8, 'name' => 'Cambria', 'space' => ['before' => 100, 'after' => 100], 'bold' => false];

        $table->addRow();
        $table->addCell(600, ['borderSize' => 1])->addText('#', $header, ['space' => ['before' => 100, 'after' => 100]]);
        $table->addCell(2000, ['borderSize' => 1])->addText('Garden', $header, ['space' => ['before' => 100, 'after' => 100]]);
        $table->addCell(800, ['borderSize' => 1])->addText('Grade', $header, ['space' => ['before' => 100, 'after' => 100]]);
        $table->addCell(1300, ['borderSize' => 1])->addText('DO Number', $header, ['space' => ['before' => 100, 'after' => 100]]);
        $table->addCell(1300, ['borderSize' => 1])->addText('INV Number', $header, ['space' => ['before' => 100, 'after' => 100]]);
        $table->addCell(1300, ['borderSize' => 1])->addText('Lot Number', $header, ['space' => ['before' => 100, 'after' => 100]]);
        $table->addCell(1300, ['borderSize' => 1])->addText('Sale Number', $header, ['space' => ['before' => 100, 'after' => 100]]);
        $table->addCell(1300, ['borderSize' => 1])->addText('Packages', $header, ['space' => ['before' => 100, 'after' => 100]]);
        $table->addCell(1300, ['borderSize' => 1])->addText('Weight', $header, ['space' => ['before' => 100, 'after' => 100]]);
        $table->addCell(1300, ['borderSize' => 1])->addText('Prompt Dte', $header, ['space' => ['before' => 100, 'after' => 100]]);
        $table->addCell(1300, ['borderSize' => 1])->addText("Pckgs Rec'/d", $header, ['space' => ['before' => 100, 'after' => 100]]);
        $table->addCell(1300, ['borderSize' => 1])->addText("Weight Rec'/d", $header, ['space' => ['before' => 100, 'after' => 100]]);

        $packets = 0;
        $weight = 0;
        $totalPallets = 0;
        $totalWeight = 0;

        foreach ($orders as $key => $order){

            $table->addRow();
            $table->addCell(600, ['borderSize' => 1])->addText(++$key, $text, ['space' => ['before' => 100, 'after' => 100]]);
            $table->addCell(2000, ['borderSize' => 1])->addText($order->garden_name, $text, ['space' => ['before' => 100, 'after' => 100]]);
            $table->addCell(800, ['borderSize' => 1])->addText($order->grade_name, $text, ['space' => ['before' => 100, 'after' => 100]]);
            $table->addCell(1300, ['borderSize' => 1])->addText($order->order_number, $text, ['space' => ['before' => 100, 'after' => 100]]);
            $table->addCell(1300, ['borderSize' => 1])->addText($order->invoice_number, $text, ['space' => ['before' => 100, 'after' => 100]]);
            $table->addCell(1300, ['borderSize' => 1])->addText($order->lot_number, $text, ['space' => ['before' => 100, 'after' => 100]]);
            $table->addCell(1300, ['borderSize' => 1])->addText($order->sale_number, $text, ['space' => ['before' => 100, 'after' => 100]]);
            $table->addCell(1300, ['borderSize' => 1])->addText($order->packet, $text, ['space' => ['before' => 100, 'after' => 100]]);
            $table->addCell(1300, ['borderSize' => 1])->addText($order->weight, $text, ['space' => ['before' => 100, 'after' => 100]]);
            $table->addCell(1300, ['borderSize' => 1])->addText($order->prompt_date, $text, ['space' => ['before' => 100, 'after' => 100]]);
            $table->addCell(1300, ['borderSize' => 1])->addText($order->total_pallets, $text, ['space' => ['before' => 100, 'after' => 100]]);
            $table->addCell(1300, ['borderSize' => 1])->addText($order->total_weight, $text, ['space' => ['before' => 100, 'after' => 100]]);

            $packets += $order->packet;
            $weight += $order->weight;

            $totalPallets += $order->total_pallets;
            $totalWeight += $order->total_weight;
        }

        $table->addRow();
        $table->addCell('8600', ['gridSpan' => 7])->addText();
        $table->addCell('1300', ['gridSpan' => 1, 'borderSize' => 1])->addText($packets, $header, ['space' => ['before' => 100, 'after' => 100]]);
        $table->addCell('1300', ['gridSpan' => 1, 'borderSize' => 1])->addText(number_format($weight, 2), $header, ['space' => ['before' => 100, 'after' => 100]]);
        $table->addCell('1300', ['gridSpan' => 1])->addText();
        $table->addCell('1300', ['gridSpan' => 1, 'borderSize' => 1])->addText($totalPallets == 0 ? '':$totalPallets, $header, ['space' => ['before' => 100, 'after' => 100]]);
        $table->addCell('1300', ['gridSpan' => 1, 'borderSize' => 1])->addText($totalWeight == 0 ?'':number_format($totalWeight, 2), $header, ['space' => ['before' => 100, 'after' => 100]]);

        $localli = new TemplateProcessor(storage_path('LLI_template.docx'));
        $localli->setComplexBlock('{table}', $table);
        $localli->setValue('client', strtoupper($details['client_name']));
        $localli->setValue('warehouse', strtoupper($details['warehouse_name']));
        $localli->setValue('date', date('D, d/m/y h:i:s'));
        $localli->setValue('LLINO', $loadNumber);
        $localli->setValue('transporter', $details['transporter_name']);
        $localli->setValue('registration', $details['registration']);
        $localli->setValue('driver', $details['driver_name']);
        $localli->setValue('idNo', $details['id_number']);
        $localli->setValue('phone', $details['phone']);
        $localli->setValue('station', $details['station_name']);
        $localli->setValue('prepared', $user);
        $localli->setValue('by', $by);
        $docPath = 'Files/TempFiles/'.$order->loading_number . '.docx';
        $localli->saveAs($docPath);

        //  return response()->download($docPath)->deleteFileAfterSend(true);

        $phpWord = IOFactory::load($docPath);
        $contents = \PhpOffice\PhpWord\IOFactory::load($docPath);
        $pdfPath = 'Files/TempFiles/'.$order->loading_number . ".pdf";
        $converter =  new OfficeConverter($docPath, 'Files/TempFiles/');
        $converter->convertTo($order->loading_number . ".pdf");
        unlink($docPath);
        return response()->download($pdfPath)->deleteFileAfterSend();
    }

    public function removeTeaFromTCI($id)
    {
        $tci = LoadingInstruction::where('loading_id', $id)->first();
        DeliveryOrder::where('delivery_id', $tci->delivery_id)->update(['status' => 0]);
        $tci->delete();
        $this->logger->create();
        return redirect()->back()->with('success', 'Success! Tea removed from TCI');
    }

    public function revertTCI($id)
    {
        $tciNumber = base64_decode($id);
        $tciCollection = LoadingInstruction::where('loading_number', $tciNumber)->get();
        DeliveryOrder::whereIn('delivery_id', $tciCollection->pluck('delivery_id')->toArray())->update(['status' => 0]);
        LoadingInstruction::whereIn('loading_id', $tciCollection->pluck('loading_id')->toArray())->delete();
        $this->logger->create();
        return redirect()->back()->with('success', 'Success! Loading instructions canceled');

    }

    public function updateLLI(Request $request, $id)
    {
        $liNumber = base64_decode($id);
        $tci = LoadingInstruction::where('loading_number', $liNumber)->first();
        foreach ($request->delNumbers as $delivery){

            $load = [
                'loading_id' => (new CustomIds())->generateId(),
                'station_id' => $tci->station_id,
                'loading_number' => $liNumber,
                'transporter_id' => $tci->transporter_id,
                'delivery_id' => $delivery,
                'registration' => $tci->registration,
                'driver_id' => $tci->driver_id,
                'created_by' => auth()->user()->user_id,
                'status' => 1,
            ];

            if (LoadingInstruction::create($load)){
                DeliveryOrder::where('delivery_id', $delivery)->update(['status' => 1]);
            }
        }
        $this->logger->create();

        return redirect()->back()->with('success', 'Successful! Local loading instructions added successfully');
    }

    public function filterByGarden(Request $request)
    {
        $data = DeliveryOrder::join('users', 'users.user_id', '=', 'delivery_orders.created_by')
            ->join('gardens', 'gardens.garden_id', '=', 'delivery_orders.garden_id')
            ->join('grades', 'grades.grade_id', '=', 'delivery_orders.grade_id')
            ->join('brokers', 'brokers.broker_id', '=', 'delivery_orders.broker_id')
            ->join('warehouses', 'warehouses.warehouse_id', '=', 'delivery_orders.warehouse_id')
            ->join('sub_warehouses', 'sub_warehouses.warehouse_id', '=', 'warehouses.warehouse_id')
            ->join('clients', 'clients.client_id', '=', 'delivery_orders.client_id')
            ->leftJoin('loading_instructions', function ($join) {
                $join->on('loading_instructions.delivery_id', '=', 'delivery_orders.delivery_id')
                    ->whereNull('loading_instructions.deleted_at');
            })
            ->leftJoin('drivers', 'drivers.driver_id', '=', 'loading_instructions.driver_id')
            ->leftJoin('transporters', 'transporters.transporter_id', '=', 'loading_instructions.transporter_id')
            ->leftJoin('stations', 'stations.station_id', '=', 'loading_instructions.station_id')
            ->leftJoin('users as loading_user', 'loading_user.user_id', '=', 'loading_instructions.created_by')
            ->where('delivery_orders.warehouse_id', $request->warehouseId)
            ->where(function ($query) {
                $query->where('delivery_orders.status', 0)
                    ->orWhereNull('delivery_orders.status');
            })
            ->select('warehouses.warehouse_id', 'sub_warehouses.sub_warehouse_id', 'sub_warehouses.sub_warehouse_name')
            ->orderBy('sub_warehouses.sub_warehouse_name', 'asc')
            ->get()
            ->groupBy('sub_warehouse_name');


        return response()->json($data);

    }

    public function filterByClient(Request $request)
    {
        $data = DeliveryOrder::join('users', 'users.user_id', '=', 'delivery_orders.created_by')
            ->join('gardens', 'gardens.garden_id', '=', 'delivery_orders.garden_id')
            ->join('grades', 'grades.grade_id', '=', 'delivery_orders.grade_id')
            ->join('brokers', 'brokers.broker_id', '=', 'delivery_orders.broker_id')
            ->join('warehouses', 'warehouses.warehouse_id', '=', 'delivery_orders.warehouse_id')
            ->join('sub_warehouses', 'sub_warehouses.sub_warehouse_id', '=', 'delivery_orders.sub_warehouse_id')
            ->join('clients', 'clients.client_id', '=', 'delivery_orders.client_id')
            ->leftJoin('loading_instructions', function ($join) {
                $join->on('loading_instructions.delivery_id', '=', 'delivery_orders.delivery_id')
                    ->whereNull('loading_instructions.deleted_at');
            })
            ->leftJoin('drivers', 'drivers.driver_id', '=', 'loading_instructions.driver_id')
            ->leftJoin('transporters', 'transporters.transporter_id', '=', 'loading_instructions.transporter_id')
            ->leftJoin('stations', 'stations.station_id', '=', 'loading_instructions.station_id')
            ->leftJoin('users as loading_user', 'loading_user.user_id', '=', 'loading_instructions.created_by')
            ->where(['warehouses.warehouse_id' => $request->warehouseId, 'sub_warehouses.sub_warehouse_id' => $request->warehouseBranchId])
            ->where(function ($query) {
                $query->where('delivery_orders.status', 0)
                    ->orWhereNull('delivery_orders.status');
            })
            ->select('clients.client_id', 'clients.client_name')
            ->orderBy( 'clients.client_name', 'desc')
            ->get()
            ->groupBy('client_name');

        return response()->json($data);

    }

    public function filterBySaleNumber(Request $request)
    {
//        return $request->all();
        $data = DeliveryOrder::join('users', 'users.user_id', '=', 'delivery_orders.created_by')
            ->join('gardens', 'gardens.garden_id', '=', 'delivery_orders.garden_id')
            ->join('grades', 'grades.grade_id', '=', 'delivery_orders.grade_id')
            ->join('brokers', 'brokers.broker_id', '=', 'delivery_orders.broker_id')
            ->join('warehouses', 'warehouses.warehouse_id', '=', 'delivery_orders.warehouse_id')
            ->join('sub_warehouses', 'sub_warehouses.sub_warehouse_id', '=', 'delivery_orders.sub_warehouse_id')
            ->join('clients', 'clients.client_id', '=', 'delivery_orders.client_id')
            ->leftJoin('loading_instructions', function ($join) {
                $join->on('loading_instructions.delivery_id', '=', 'delivery_orders.delivery_id')
                    ->whereNull('loading_instructions.deleted_at');
            })
            ->leftJoin('drivers', 'drivers.driver_id', '=', 'loading_instructions.driver_id')
            ->leftJoin('transporters', 'transporters.transporter_id', '=', 'loading_instructions.transporter_id')
            ->leftJoin('stations', 'stations.station_id', '=', 'loading_instructions.station_id')
            ->leftJoin('users as loading_user', 'loading_user.user_id', '=', 'loading_instructions.created_by')
            ->where(['clients.client_id' => $request->clientId, 'warehouses.warehouse_id' => $request->warehouseId, 'sub_warehouses.sub_warehouse_id' => $request->warehouseBranchId])
            ->where(function ($query) {
                $query->where('delivery_orders.status', 0)
                    ->orWhereNull('delivery_orders.status');
            })
            ->select('users.username', 'gardens.garden_name', 'grades.grade_name', 'brokers.broker_name', 'warehouses.warehouse_name', 'warehouses.warehouse_id', 'clients.client_name', 'delivery_orders.*', 'transporters.transporter_id', 'transporters.transporter_name', 'drivers.driver_id', 'drivers.driver_name', 'drivers.id_number', 'drivers.phone', 'loading_instructions.loading_id', 'loading_instructions.loading_number', 'loading_instructions.status as load_status', 'loading_instructions.registration', 'loading_instructions.created_by as load_user_id', 'loading_user.username as load_user', 'stations.station_name', 'stations.station_id', 'sub_warehouses.sub_warehouse_name', 'loading_instructions.deleted_at')
            ->orderBy('clients.client_name', 'asc')
            ->orderBy('gardens.garden_name', 'asc')
            ->get();

        return response()->json($data);

    }

    public function viewDeliveryOrders()
    {
        $orders = DeliveryOrder::join('clients', 'clients.client_id', '=', 'delivery_orders.client_id')
            ->join('gardens', 'gardens.garden_id', '=', 'delivery_orders.garden_id')
            ->join('grades', 'grades.grade_id', '=', 'delivery_orders.grade_id')
            ->join('brokers', 'brokers.broker_id', '=', 'delivery_orders.broker_id')
            ->join('warehouses', 'warehouses.warehouse_id', '=', 'delivery_orders.warehouse_id')
            ->leftJoin('sub_warehouses', 'sub_warehouses.sub_warehouse_id', '=', 'delivery_orders.sub_warehouse_id')
            ->leftJoin('loading_instructions', function ($join) {
                $join->on('loading_instructions.delivery_id', '=', 'delivery_orders.delivery_id')
                    ->whereNull('loading_instructions.deleted_at');
            })
            ->select('delivery_orders.client_id', 'delivery_orders.warehouse_id', 'delivery_orders.delivery_id','gardens.garden_name', 'grades.grade_name', 'brokers.broker_name', 'warehouses.warehouse_name', 'clients.client_name', 'delivery_orders.invoice_number', 'sub_warehouses.sub_warehouse_name', 'locality', 'lot_number', 'delivery_orders.status', 'delivery_orders.created_at', 'loading_number')
            ->where('delivery_orders.delivery_type', 1)
            ->whereNull('delivery_orders.deleted_at')
            ->orderBy('delivery_orders.created_at', 'desc')
            ->take(3000)
            ->get();

        return view('admin::DOS.index')->with(['orders' => $orders]);
    }

    public function viewTciDetails($id)
    {
        $tciNumber = base64_decode($id);
        $orders = LoadingInstruction::join('stations', 'stations.station_id', '=', 'loading_instructions.station_id')
            ->join('delivery_orders', 'delivery_orders.delivery_id', '=', 'loading_instructions.delivery_id')
            ->join('warehouses', 'warehouses.warehouse_id', '=', 'delivery_orders.warehouse_id')
            ->join('sub_warehouses', 'sub_warehouses.sub_warehouse_id', '=', 'delivery_orders.sub_warehouse_id')
            ->join('clients', 'clients.client_id', '=', 'delivery_orders.client_id')
            ->join('grades', 'grades.grade_id', '=', 'delivery_orders.grade_id')
            ->join('gardens', 'gardens.garden_id', '=', 'delivery_orders.garden_id')
            ->select('stations.station_name', 'delivery_orders.delivery_id', 'loading_instructions.status', 'invoice_number', 'lot_number', 'sale_number', 'weight', 'packet', 'warehouses.warehouse_name', 'sub_warehouses.sub_warehouse_name', 'clients.client_name', 'garden_name', 'grade_name', 'prompt_date', 'sale_date', 'loading_number')
            ->where('loading_number', $tciNumber)
            ->get();
        $tci = $orders->first();
        return view('admin::DOS.tciDetails')->with(['orders' => $orders, 'tci' => $tci]);
    }

    public function amendTciDetails($id)
    {
        $tciNumber = base64_decode($id);
        $orders = LoadingInstruction::join('stations', 'stations.station_id', '=', 'loading_instructions.station_id')
            ->join('delivery_orders', 'delivery_orders.delivery_id', '=', 'loading_instructions.delivery_id')
            ->join('warehouses', 'warehouses.warehouse_id', '=', 'delivery_orders.warehouse_id')
            ->join('sub_warehouses', 'sub_warehouses.sub_warehouse_id', '=', 'delivery_orders.sub_warehouse_id')
            ->join('clients', 'clients.client_id', '=', 'delivery_orders.client_id')
            ->join('grades', 'grades.grade_id', '=', 'delivery_orders.grade_id')
            ->join('gardens', 'gardens.garden_id', '=', 'delivery_orders.garden_id')
            ->select('stations.station_name', 'delivery_orders.delivery_id', 'loading_instructions.status', 'invoice_number', 'lot_number', 'sale_number', 'weight', 'packet', 'warehouses.warehouse_name', 'sub_warehouses.sub_warehouse_name', 'clients.client_name', 'garden_name', 'grade_name', 'prompt_date', 'sale_date', 'loading_number', 'loading_id', 'delivery_orders.client_id', 'delivery_orders.warehouse_id', 'delivery_orders.sub_warehouse_id')
            ->where('loading_number', $tciNumber)
            ->get();

        $tci = $orders->first();

        $teas = DeliveryOrder::join('users', 'users.user_id', '=', 'delivery_orders.created_by')
                ->join('gardens', 'gardens.garden_id', '=', 'delivery_orders.garden_id')
                ->join('grades', 'grades.grade_id', '=', 'delivery_orders.grade_id')
                ->join('brokers', 'brokers.broker_id', '=', 'delivery_orders.broker_id')
                ->join('warehouses', 'warehouses.warehouse_id', '=', 'delivery_orders.warehouse_id')
                ->join('sub_warehouses', 'sub_warehouses.sub_warehouse_id', '=', 'delivery_orders.sub_warehouse_id')
                ->join('clients', 'clients.client_id', '=', 'delivery_orders.client_id')
                ->leftJoin('loading_instructions', function ($join) {
                    $join->on('loading_instructions.delivery_id', '=', 'delivery_orders.delivery_id')
                        ->whereNull('loading_instructions.deleted_at');
                })
                ->leftJoin('drivers', 'drivers.driver_id', '=', 'loading_instructions.driver_id')
                ->leftJoin('transporters', 'transporters.transporter_id', '=', 'loading_instructions.transporter_id')
                ->leftJoin('stations', 'stations.station_id', '=', 'loading_instructions.station_id')
                ->leftJoin('users as loading_user', 'loading_user.user_id', '=', 'loading_instructions.created_by')
                ->where(['delivery_orders.client_id' => $tci->client_id, 'delivery_orders.warehouse_id' => $tci->warehouse_id, 'delivery_orders.sub_warehouse_id' => $tci->sub_warehouse_id])
                ->where(function ($query) {
                    $query->where('delivery_orders.status', 0)
                        ->orWhereNull('delivery_orders.status')
                        ->orWhere('delivery_orders.status', null)
                    ;
                })
                ->select('users.username', 'gardens.garden_name', 'grades.grade_name', 'brokers.broker_name', 'warehouses.warehouse_name', 'warehouses.warehouse_id', 'clients.client_name', 'delivery_orders.*', 'sub_warehouses.sub_warehouse_name')
                ->orderBy('clients.client_name', 'asc')
                ->orderBy('gardens.garden_name', 'asc')
                ->get();

        if ($tci == null){
            return redirect()->route('admin.viewLLIs')->with('info', 'Oops! TCI is empty');
        }
        return view('admin::DOS.editTciDetails')->with(['orders' => $orders, 'tci' => $tci, 'teas' => $teas]);
    }

    public function addDeliveryOrders()
    {
        $clients = Client::all();
        $gardens = Garden::all();
        $grades = Grade::all();
        $warehouses = Warehouse::all();
        $brokers = Broker::all();
        return view('admin::DOS.addDO')->with(['clients' => $clients, 'gardens' => $gardens, 'grades' => $grades, 'warehouses' => $warehouses, 'brokers' => $brokers]);
    }

    public function registerDeliveryOrder(Request $request)
    {
        $request->validate([
            'client_id' => 'required',
            'order_number' => ['required', Rule::unique('delivery_orders')->where(function ($query) use ($request) {
                return $query->where('invoice_number', $request->invoice_number);
            })],
            'invoice_number' => ['required', Rule::unique('delivery_orders')->where(function ($query) use ($request) {
                return $query->where('order_number', $request->order_number);
            })],
            'tea_id' => 'required',
            'garden_id' => 'required',
            'grade_id' => 'required',
            'packet' => 'required|string',
            'package' => 'required',
            'weight' => 'required|string',
            'warehouse_id' => 'required',
            'broker_id' => 'required',
            'sale_number' => [
                new RequiredIf($request->tea_id == 1),
            ],
            'lot_number' => 'required|string',
            'sale_date' => 'required|date',
            'prompt_date' => 'required|date|after:sale_date',
            'branch' => 'required',
            'locality' => 'required|numeric'
        ]);

        $exits = DeliveryOrder::where(['client_id' => $request->client_id, 'invoice_number' => $request->invoice_number, 'garden_id' => $request->garden_id])->exists();

        if ($exits){
            return redirect()->back()->with('error', 'Oops! The invoice number for this client exists already exists');
        }

        $customId = new CustomIds();
        $deliveryId = $customId->generateId();
        $order = [
            'delivery_id' => $deliveryId,
            'order_number' => $request->order_number,
            'client_id' => $request->client_id,
            'tea_id' => $request->tea_id,
            'garden_id' => $request->garden_id,
            'grade_id' => $request->grade_id,
            'packet' => $request->packet,
            'package' => $request->package,
            'weight' => $request->weight,
            'warehouse_id' => $request->warehouse_id,
            'broker_id' => $request->broker_id,
            'sale_number' => $request->tea_id == 1 ? $request->sale_number : ($request->tea_id == 2 ? 'P/Sale' : ($request->tea_id == 3 ? 'F/Sale' : 'B/Rem')),
            'invoice_number' => $request->invoice_number,
            'lot_number' => $request->lot_number,
            'sale_date' => $request->sale_date,
            'prompt_date' => $request->prompt_date,
            'sub_warehouse_id' => $request->branch,
            'locality' => $request->locality,
            'created_by' => auth()->user()->user_id,
            'delivery_type' => 1,
        ];

        DeliveryOrder::create($order);

        $this->logger->create();

        return redirect()->route('admin.viewDeliveryOrders')->with('success', 'Successful! Delivery order created successfully');

    }

    public function editDO($id)
    {
      $order = DeliveryOrder::leftJoin('sub_warehouses', 'sub_warehouses.sub_warehouse_id', '=', 'delivery_orders.sub_warehouse_id')->select('delivery_orders.*', 'sub_warehouse_name')->findOrFail($id);

        $gardens = Garden::orderBy('garden_name', 'asc')->get();
        $warehouses = Warehouse::orderBy('warehouse_name', 'asc')->get();
        $grades = Grade::orderBy('grade_name', 'asc')->get();
        $brokers = Broker::orderBy('broker_name', 'asc')->get();
        $clients = Client::orderBy('client_name', 'asc')->get();
        $transporters  = Transporter::orderBy('transporter_name')->get();
        $users  = Driver::orderBy('id_number', 'asc')->get();
        $registrations = LoadingInstruction::orderBy('registration', 'asc')->get()->groupBy('registration');
        $stations = Station::where('status', 1)->orderBy('station_name', 'asc')->get();

        return view('admin::DOS.editDO')->with(['order' => $order, 'gardens' => $gardens, 'warehouses' => $warehouses, 'grades' => $grades, 'brokers' => $brokers, 'clients' => $clients, 'transporters' => $transporters, 'users' => $users, 'registrations' => $registrations, 'allStations' => $stations]);
    }

    public function updateDeliveryOrder(Request $request, $id)
    {
        $request->validate([
            'order_number' => 'required',
            'tea_id' => 'required',
            'client_id' => 'required',
            'garden_id' => 'required',
            'grade_id' => 'required',
            'packet' => 'required|string',
            'package' => 'required',
            'weight' => 'required|string',
//            'warehouse_id' => 'required',
//            'broker_id' => 'required',
//            'sale_number' => [
//                new RequiredIf($request->tea_id == 1),
//            ],
            'invoice_number' => 'required|string',
//            'lot_number' => 'required|string',
//            'sale_date' => 'required|date',
//            'prompt_date' => 'required|date|after:sale_date',
//            'branch' => 'required',
//            'locality' => 'required'
        ]);


        $order = [
            'order_number' => $request->order_number,
            'tea_id' => $request->tea_id,
            'client_id' => $request->client_id,
            'garden_id' => $request->garden_id,
            'grade_id' => $request->grade_id,
            'packet' => $request->packet,
            'package' => $request->package,
            'weight' => $request->weight,
            'warehouse_id' => $request->warehouse_id,
            'broker_id' => $request->broker_id,
            'sale_number' => $request->tea_id == 1 ? $request->sale_number : ($request->tea_id == 4 ? 'B/Rem' : ($request->tea_id == 3 ? 'F/Sale' : 'P/Sale')),
            'invoice_number' => $request->invoice_number,
            'lot_number' => $request->lot_number,
            'sale_date' => $request->sale_date,
            'prompt_date' => $request->prompt_date,
            'sub_warehouse_id' => $request->branch,
            'locality' => $request->locality,
            'created_by' => auth()->user()->user_id
        ];

        DeliveryOrder::where('delivery_id', $id)->update($order);

        $this->logger->create();

        return redirect()->route('admin.viewDeliveryOrders')->with('success', 'Successful! Delivery order updated successfully');

    }

    public function deleteDeliveryOrder($id)
    {
        DeliveryOrder::where('delivery_id', $id)->delete();
        LoadingInstruction::where('delivery_id', $id)->delete();
        StockIn::where('delivery_id', $id)->delete();
        return redirect()->back()->with('success', 'Delivery order deleted successfully');
    }

    public function collectionReport(Request $request)
    {
        $client = $request->input('client');
        $from = $request->input('from');
        $to = $request->input('to');
        $collection = $request->input('collection');
        $delivery = $request->input('delivery');
        $warehouse = $request->input('warehouse');

        // return $request->all();

        $query = DeliveryOrder::join('users', 'users.user_id', '=', 'delivery_orders.created_by')
            ->join('user_infos', 'user_infos.user_id', '=', 'delivery_orders.created_by')
            ->join('gardens', 'gardens.garden_id', '=', 'delivery_orders.garden_id')
            ->join('grades', 'grades.grade_id', '=', 'delivery_orders.grade_id')
            ->leftJoin('brokers', 'brokers.broker_id', '=', 'delivery_orders.broker_id')
            ->leftJoin('warehouses', 'warehouses.warehouse_id', '=', 'delivery_orders.warehouse_id')
            ->join('clients', 'clients.client_id', '=', 'delivery_orders.client_id')
            ->leftJoin('loading_instructions', function ($join) {
                $join->on('loading_instructions.delivery_id', '=', 'delivery_orders.delivery_id')
                    ->whereNull('loading_instructions.deleted_at');
            })
            ->leftJoin('stock_ins', 'stock_ins.delivery_id', '=', 'delivery_orders.delivery_id')
           ->whereNull('delivery_orders.deleted_at')
            ->select('gardens.garden_name', 'grades.grade_name', 'warehouse_name', 'loading_instructions.status as load_status', 'date_received', 'stock_ins.status as stock_status', 'brokers.broker_name', 'warehouses.warehouse_name', 'clients.client_name', 'delivery_orders.*', 'user_infos.first_name', 'user_infos.surname')
            ->orderBy('delivery_orders.created_at', 'desc');

        if (!is_null($client)) {
            $query->where('delivery_orders.client_id', $client);
        }

        if (!is_null($collection)) {
            if ($collection == 1){
                $query->where('loading_instructions.status', 1);
            }elseif ($collection == 2){
                $query->where('loading_instructions.status', 2)->orWhere('delivery_orders.status', 2);
            }else{
                $query->where(['loading_instructions.status' => null, 'delivery_orders.delivery_type' => 1]);
            }
        }

        if (!is_null($delivery)) {
            if ($delivery == 1){
                $query->where('delivery_orders.delivery_type', 1);
            }elseif ($delivery == 2){
                $query->where('delivery_orders.delivery_type', 2);
            }else{
                $query->whereIn('delivery_orders.delivery_type', [1, 2]);
            }
        }

        if (!is_null($warehouse)) {
            $query->where('delivery_orders.warehouse_id', $warehouse);
        }

        if (!is_null($from)) {
            $query->where('delivery_orders.created_at', '>=', $from);
        }

        if (!is_null($to)) {
            $query->where('delivery_orders.created_at', '<=', $to);
        }

        $results = $query->get();

        if ($request->report == 2){
            return Excel::download(new ExportDeliveryOrders($results), 'TEA COLLECTION '.time().'.xlsx', \Maatwebsite\Excel\Excel::XLSX);
        }

        $date = date('D, d-m-Y, h:i:s');
        $by = auth()->user()->user->first_name.' '.auth()->user()->user->last_name;
        $domPdfPath = base_path('vendor/dompdf/dompdf');
        \PhpOffice\PhpWord\Settings::setPdfRendererPath($domPdfPath);
        \PhpOffice\PhpWord\Settings::setPdfRendererName('DomPDF');

        $headers = ['size' => 8, 'name' => 'New Times Roman', 'bold' => true,/* 'space' => ['before' => 50]*/];
        $text = ['size' => 7, 'name' => 'New Times Roman', 'bold' => false, /*'space' => ['before' => 50]*/];

        $table = new Table(['unit' => \PhpOffice\PhpWord\SimpleType\TblWidth::TWIP, 'width' => 1450 * 1450, 'align' => 'center']);
        $table->addRow();
        $table->addCell(600, ['borderSize' => 1])->addText('#', $headers, ['space' => ['before' => 70]]);
        $table->addCell(2000, ['borderSize' => 1])->addText('Client Name', $headers, ['space' => ['before' => 70]]);
        $table->addCell(1700, ['borderSize' => 1])->addText('Garden Name', $headers, ['space' => ['before' => 70]]);
        $table->addCell(900, ['borderSize' => 1])->addText('Grade', $headers, ['space' => ['before' => 70]]);
        $table->addCell(1600, ['borderSize' => 1])->addText('Invoice #', $headers, ['space' => ['before' => 70]]);
        $table->addCell(900, ['borderSize' => 1])->addText('Pkgs', $headers, ['space' => ['before' => 70]]);
        $table->addCell(1800, ['borderSize' => 1])->addText('Weight', $headers, ['space' => ['before' => 70]]);
        $table->addCell(1300, ['borderSize' => 1])->addText("Pro't Date", $headers, ['space' => ['before' => 70]]);
        $table->addCell(2200, ['borderSize' => 1])->addText('Producer Warehouse', $headers, ['space' => ['before' => 70]]);
        $table->addCell(1400, ['borderSize' => 1])->addText('Status', $headers, ['space' => ['before' => 70]]);
        $table->addCell(1100, ['borderSize' => 1])->addText('Aging Date', $headers, ['space' => ['before' => 70]]);

        $totalPackets = 0;
        $totalWeight = 0;
        foreach ($results as $key => $stock){
            $table->addRow();
            $table->addCell(600, ['borderSize' => 1])->addText(++$key, $text, ['space' =>['before' => 70]]);
            $table->addCell(2000, ['borderSize' => 1])->addText($stock->client_name, $text, ['space' =>['before' => 70]]);
            $table->addCell(1700, ['borderSize' => 1])->addText($stock->garden_name, $text, ['space' =>['before' => 70]]);
            $table->addCell(900, ['borderSize' => 1])->addText($stock->grade_name, $text, ['space' =>['before' => 70]]);
            $table->addCell(1600, ['borderSize' => 1])->addText($stock->invoice_number, $text, ['space' =>['before' => 70]]);
            $table->addCell(900, ['borderSize' => 1])->addText(number_format($stock->packet, 2), $text, ['space' =>['before' => 70]]);
            $table->addCell(1800, ['borderSize' => 1])->addText(number_format($stock->weight, 2), $text, ['space' =>['before' => 70]]);
            $table->addCell(1300, ['borderSize' => 1])->addText($stock->prompt_date, $text, ['space' =>['before' => 70]]);
            $table->addCell(2200, ['borderSize' => 1])->addText($stock->warehouse_name, $text, ['space' =>['before' => 70]]);
            $table->addCell(1400, ['borderSize' => 1])->addText($stock->load_status == null ? "No TCI" : ($stock->load_status == 2 ? "Received" : "Under Collection"), $text, ['space' =>['before' => 70]]);

            $today = Carbon::now()->format('Y/m/d');
            if ($stock->stock_status == null || $stock->stock_status == 0){
                $date1 = new DateTime($today);
                $date2 = new DateTime($stock->created_at);
                $interval = $date2->diff($date1);
                $dates = $interval->format('%R%a days');
            }elseif ($stock->stock_status == 1 && $stock->load_status == 1){
                $date1 = new DateTime($today);
                $date3 = new DateTime($stock->created_at);
                $interval = $date3->diff($date1);
                $dates = $interval->format('%R%a days');
            }else{
                $date1 = new DateTime($today);
                $date4 = new DateTime(Carbon::createFromTimestamp($stock->date_received));
                $interval = $date4->diff($date1);
                $dates = $interval->format('%R%a days');
            }
            $table->addCell(1100, ['borderSize' => 1])->addText($dates, $text, ['space' =>['before' => 70]]);

            $totalPackets += $stock->packet;
            $totalWeight += $stock->weight;
        }

        $table->addRow();
        $table->addCell(6800, ['gridSpan' => 5])->addText('TOTALS', $headers, ['align' => 'left' ,'space' =>['before' => 70]]);
        $table->addCell(900, ['gridSpan' => 1, 'borderSize' => 1])->addText(number_format($totalPackets, 2), $headers, ['space' =>['before' => 70]]);
        $table->addCell(1800, ['gridSpan' => 1, 'borderSize' => 1])->addText(number_format($totalWeight, 2), $headers, ['space' =>['before' => 70]]);
        $table->addCell(5800, ['gridSpan' => 4])->addText('', $headers, ['align' => 'center' ,'space' =>['before' => 70]]);

        $table->addRow();
        $table->addCell(12800, ['gridSpan' => 11])->addText('SUMMARY PER CLIENT', $headers, ['space' => ['before' => 200, 'after' => 100]]);

        $table->addRow();
        $table->addCell(600, ['gridSpan' => 1, 'borderSize' => 1])->addText('#', $headers, ['space' =>['before' => 70]]);
        $table->addCell(3700, ['gridSpan' => 2, 'borderSize' => 1])->addText('CLIENT NAME', $headers, ['space' =>['before' => 70]]);
        $table->addCell(3400, ['gridSpan' => 3, 'borderSize' => 1])->addText('TOTAL PACKAGES', $headers, ['space' =>['before' => 70]]);
        $table->addCell(2400, ['gridSpan' => 2, 'borderSize' => 1])->addText('TOTAL WEIGHT', $headers, ['space' =>['before' => 70]]);
        $table->addCell(3600, ['gridSpan' => 2, 'borderSize' => 1])->addText('PERCENTAGE (%)', $headers, ['space' =>['before' => 70]]);
        $table->addCell(1100, ['gridSpan' => 1])->addText();
        $i = 0;
        foreach ($results->groupBy('client_name') as $client => $stocks){
            $table->addRow();
            $table->addCell(600, ['gridSpan' => 1, 'borderSize' => 1])->addText(++$i, $headers, ['space' =>['before' => 70]]);
            $table->addCell(3700, ['gridSpan' => 2, 'borderSize' => 1])->addText($client, $headers, ['space' =>['before' => 70]]);
            $clientPackets = 0;
            $clientWeight = 0;
            foreach ($stocks as $stock){
                $clientWeight += $stock->weight;
                $clientPackets += $stock->packet;
            }
            $table->addCell(2900, ['gridSpan' => 3, 'borderSize' => 1])->addText(number_format($clientPackets, 2), $text, ['space' =>['before' => 70]]);
            $table->addCell(2000, ['gridSpan' => 2, 'borderSize' => 1])->addText(number_format($clientWeight, 2), $text, ['space' =>['before' => 70]]);
            $table->addCell(2800, ['gridSpan' => 2, 'borderSize' => 1])->addText(number_format($clientWeight/$totalWeight*100, 2).'%', $headers, ['space' =>['before' => 70]]);
            $table->addCell(1100, ['gridSpan' => 1])->addText();
        }

        $table->addRow();
        $table->addCell(12800, ['gridSpan' => 11])->addText('SUMMARY PER WAREHOUSE', $headers, ['space' => ['before' => 200, 'after' => 100]]);

        $table->addRow();
        $table->addCell(600, ['gridSpan' => 1, 'borderSize' => 1])->addText('#', $headers, ['space' =>['before' => 70]]);
        $table->addCell(3700, ['gridSpan' => 2, 'borderSize' => 1])->addText('WAREHOUSE NAME', $headers, ['space' =>['before' => 70]]);
        $table->addCell(2900, ['gridSpan' => 3, 'borderSize' => 1])->addText('TOTAL PACKAGES', $headers, ['space' =>['before' => 70]]);
        $table->addCell(2000, ['gridSpan' => 2, 'borderSize' => 1])->addText('TOTAL WEIGHT', $headers, ['space' =>['before' => 70]]);
        $table->addCell(2800, ['gridSpan' => 2, 'borderSize' => 1])->addText('PERCENTAGE (%)', $headers, ['space' =>['before' => 70]]);
        $table->addCell(1100, ['gridSpan' => 1])->addText();

        $i = 0;
        $groupedResults = $results->groupBy('warehouse_name');
        $sortedResults = $groupedResults->map(function ($group) {
            return $group->sortBy('warehouse_name', SORT_NATURAL | SORT_FLAG_CASE);
        });
        foreach ($sortedResults as $station => $stocks){
            $table->addRow();
            $table->addCell(600, ['gridSpan' => 1, 'borderSize' => 1])->addText(++$i, $headers, ['space' =>['before' => 70]]);
            $table->addCell(3700, ['gridSpan' => 2, 'borderSize' => 1])->addText($station == null ? 'UNCLASSIFIED' : $station, $headers, ['space' =>['before' => 70]]);

            $stationPackets = 0;
            $stationWeight = 0;
            foreach ($stocks as $stock){
                $stationWeight += $stock->weight;
                $stationPackets += $stock->packet;
            }

            $table->addCell(2900, ['gridSpan' => 3, 'borderSize' => 1])->addText(number_format($stationPackets, 2), $text, ['space' =>['before' => 70]]);
            $table->addCell(2000, ['gridSpan' => 2, 'borderSize' => 1])->addText(number_format($stationWeight, 2), $text, ['space' =>['before' => 70]]);
            $table->addCell(2800, ['gridSpan' => 2, 'borderSize' => 1])->addText(number_format($stationWeight/$totalWeight*100, 2).'%', $headers, ['space' =>['before' => 70]]);
            $table->addCell(1100, ['gridSpan' => 1])->addText();
        }


        $stock = new TemplateProcessor(storage_path('delivery_template.docx'));
        $stock->setComplexBlock('{table}', $table);
        $stock->setValue('date', $date);
        $stock->setValue('by', $by);
        $docPath = 'Files/TempFiles/COLLECTION '.time().'.docx';
        $stock->saveAs($docPath);

        // return response()->download($docPath)->deleteFileAfterSend(true);

        $phpWord = IOFactory::load($docPath);
        $contents = \PhpOffice\PhpWord\IOFactory::load($docPath);
        $pdfPath = 'Files/TempFiles/COLLECTION'.time(). ".pdf";
        $converter =  new OfficeConverter($docPath, 'Files/TempFiles/');
        $converter->convertTo('COLLECTION'.time().".pdf");
        unlink($docPath);
        return response()->download($pdfPath)->deleteFileAfterSend(true);
    }

    public function filterWarehouseBay(Request $request)
    {
        $data = WarehouseBay::where('station_id', $request->selectedStation)->orderBy('bay_name', 'asc')->get();
        return response()->json($data);
    }
    public function filterWarehouseBranch(Request $request)
    {
        $data = SubWarehouse::where(['warehouse_id' => $request->warehouseId, 'status' => 1])->orderBy('sub_warehouse_name', 'asc')->get();
        return response()->json($data);

    }

    public function viewDeliveries ()
    {
        $deliveries = DB::table('currentstock')
            ->orderBy('sortOrder', 'desc')->where('current_stock', '>', 0)->where('current_weight', '>', 0)
            ->select('delivery_id', 'garden_name', 'grade_name', 'client_name', 'order_number', 'lot_number', 'invoice_number', 'date_received', 'stocked_at', 'bay_name', 'current_stock', 'current_weight', 'sortOrder', 'client_id', 'grade_id', 'garden_id', 'station_id', 'sale_number', 'tea_id', 'stock_id', DB::raw('CASE WHEN shipped_packages > 0 OR transferred_palettes > 0 OR blended_packages > 0 OR sample_palletes > 0 OR requested_palettes > 0 THEN 1 ELSE 0 END AS used'))
            ->whereNull('deleted_at')
            ->get();

        $transporters = DB::table('transportreport')->select('transporter_id', 'transporter_name')->groupBy('transporter_id', 'transporter_name')->get();
        return view('admin::stock.index')->with(['stocks' => $deliveries, 'transporters' => $transporters]);
    }

    public function editStock($id)
    {
        $order = DB::table('currentstock')->where('stock_id', $id)->first();
        $stations = Station::orderBy('station_name', 'asc')->get();
        $transporters  = Transporter::orderBy('transporter_name')->get();
        $drivers  = Driver::orderBy('id_number', 'asc')->get();
        $registrations = LoadingInstruction::orderBy('registration', 'asc')->get()->groupBy('registration');
        return view('admin::stock.editStock')->with(['stations' => $stations, 'transporters' => $transporters, 'drivers' => $drivers, 'registrations' => $registrations, 'order' => $order]);
    }

    public function updateStock(Request $request, $id)
    {
        $request->validate([
            'delivery_number' => 'required|string',
            'station' => 'required|string',
            'bay' => 'required|string',
            'numberPackages' => 'required|string',
            'total_weight' => 'required|string',
            'tare' => 'required|string',
            'pallet_weight' => 'required|string',
            'netWeight' => 'required|string',
        ]);

        $data = StockIn::join('delivery_orders', 'delivery_orders.delivery_id', '=', 'stock_ins.delivery_id')
            ->whereNull('delivery_orders.deleted_at')
            ->where(['stock_ins.stock_id' => $id])
            ->first();

        if ($request->numberPackages > $data->packet){
            return redirect()->back()->with('error', 'Oops! The total pallets cannot be more than requested on the DO');
        }

        $customId = new CustomIds();
        $driver = Driver::where('id_number', $request->idNumber)->first();

        if ($driver === null && $request->idNumber !== null){
            $driverID = $customId->generateId();
            $drDetails = [
                'driver_id' => $driverID,
                'id_number' => $request->idNumber,
                'driver_name' => strtoupper($request->driverName),
                'phone' => $request->driverPhone
            ];

            Driver::create($drDetails);

            LoadingInstruction::where('delivery_id', $data->delivery_id)->update([
                'driver_id' => $driverID,
                'registration' => strtoupper($request->registration),
                'transporter_id' => $request->transporter
            ]);

        }elseif($driver !== null){
            LoadingInstruction::where('delivery_id', $data->delivery_id)->update([
                'driver_id' => $driver->driver_id,
                'registration' => strtoupper($request->registration),
                'transporter_id' => $request->transporter
            ]);
        }

        $data->update([
            'station_id' => $request->station,
            'delivery_number' => $request->delivery_number,
            'warehouse_bay' => $request->bay,
            'total_weight' => $request->netWeight,
            'total_pallets' => $request->numberPackages,
            'net_weight' => $request->total_weight,
            'pallet_weight' => $request->pallet_weight,
            'package_tare' => $request->tare,
            'date_received' => $request->date_received == null ? time() : strtotime($request->date_received),
            'transporter_id' => $request->transporter == '' ? null : $request->transporter_id,
            'driver_id' => $driver === null && $request->idNumber === null ? '' : ($driver !== null ? $driver->driver_id : $driverID),
            'registration' => $request->registration == null ? '' : $request->registration
        ]);

        $this->logger->create();

        return redirect()->route('admin.viewDeliveries')->with('success', 'Success! Stock entry has been updated');
    }

    public function StockReport(Request $request)
    {
        $client = $request->input('client');
        $garden = $request->input('garden');
        $grade = $request->input('grade');
        $station = $request->input('station');
        $from = $request->input('from');
        $to = $request->input('to');

        $query = DB::table('currentstock')
            ->where('current_stock', '>', 0)
            ->where('current_weight', '>', 0)
            ->select('client_name', 'garden_name', 'grade_name', 'invoice_number', 'sale_number', 'loading_number', 'warehouse_name', 'date_received', 'order_number', 'current_stock', 'current_weight', 'package_tare', 'pallet_weight',
            'created_at', 'received_by', 'delivery_orders.delivery_type', 'lot_number', 'prompt_date', 'total_weight', 'stocked_at', 'bay_name')
            ->orderBy('sortOrder', 'desc')
            ->orderBy('client_name', 'asc')
            ->orderBy('stocked_at', 'asc');

        // Apply filtering conditions based on the request parameters
        if (!is_null($client)) {
            $query->where('client_id', $client);
        }
        if (!is_null($garden)) {
            $query->where('garden_id', $garden);
        }
        if (!is_null($grade)) {
            $query->where('grade_id', $grade);
        }
        if (!is_null($station)) {
            $query->where('station_id', $station);
        }
        if (!is_null($from)) {
            $fromTimestamp = strtotime($from);
            $query->where('date_received', '>=', $fromTimestamp);
        }

        if (!is_null($to)) {
            $toTimestamp = strtotime($to);
            $query->where('date_received', '<=', $toTimestamp);
        }

        $results = $query->get();

        if ($request->report == 2){
            return Excel::download(new ExportStock($results), 'STOCK '.time().'.xlsx', \Maatwebsite\Excel\Excel::XLSX);
        }

        ini_set('memory_limit', '10000M');
        ini_set('max_execution_time', 30000);


        $date = date('D, d-m-Y, h:i:s');
        $printed = auth()->user()->user;
        $by = $printed->first_name.' '.$printed->surname;

        $domPdfPath = base_path('vendor/dompdf/dompdf');
        \PhpOffice\PhpWord\Settings::setPdfRendererPath($domPdfPath);
        \PhpOffice\PhpWord\Settings::setPdfRendererName('DomPDF');

        $headers = ['size' => 8, 'name' => 'New Times Roman', 'bold' => true, 'space' => ['after' => 50, 'before' => 100]];
        $text = ['size' => 7, 'name' => 'New Times Roman', 'bold' => false, 'space' => ['after' => 40, 'before' => 100]];

        $table = new Table(['unit' => \PhpOffice\PhpWord\SimpleType\TblWidth::PERCENT, 'width' => 100 * 50, 'align' => Jc::CENTER]);

        foreach ($results->groupBy('client_name') as $clientName => $result) {
            $table->addRow();
            $table->addCell(null, ['gridSpan' => 15])->addText('CLIENT NAME : '.$clientName, $headers);
            // $table->addRow();
            // $table->addCell(13250, ['gridSpan' => 15])->addText('');
            $table->addRow();
            $table->addCell(600, ['borderSize' => 1])->addText('#', $headers, ['space' => ['before' => 100]]);
            $table->addCell(1200, ['borderSize' => 1])->addText('Invoice #', $headers, ['space' => ['before' => 100]]);
            $table->addCell(1300, ['borderSize' => 1])->addText('Garden Name', $headers, ['space' => ['before' => 100]]);
            $table->addCell(750, ['borderSize' => 1])->addText('Grade', $headers, ['space' => ['before' => 100]]);
            $table->addCell(800, ['borderSize' => 1])->addText('Sale #', $headers, ['space' => ['before' => 100]]);

            $table->addCell(700, ['borderSize' => 1])->addText('Gross', $headers, ['space' => ['before' => 100]]);
            $table->addCell(600, ['borderSize' => 1])->addText('Tare', $headers, ['space' => ['before' => 100]]);
            $table->addCell(600, ['borderSize' => 1])->addText('Nett', $headers, ['space' => ['before' => 100]]);

            $table->addCell(1000, ['borderSize' => 1])->addText('Pkgs', $headers, ['space' => ['before' => 100]]);
            $table->addCell(1200, ['borderSize' => 1])->addText('Net Weight', $headers, ['space' => ['before' => 100]]);
            $table->addCell(1200, ['borderSize' => 1])->addText('Gross Weight', $headers, ['space' => ['before' => 100]]);

            $table->addCell(900, ['borderSize' => 1])->addText('TCI #', $headers, ['space' => ['before' => 100]]);
            $table->addCell(900, ['borderSize' => 1])->addText('DO #', $headers, ['space' => ['before' => 100]]);


            $table->addCell(900, ['borderSize' => 1])->addText("Date Rec'd", $headers, ['space' => ['before' => 100]]);
            $table->addCell(2000, ['borderSize' => 1])->addText('Recieved From', $headers, ['space' => ['before' => 100]]);

            $totalPackets = 0;
            $totalWeight = 0;
            $grossWeight = 0;

            foreach ($result as $key => $stock){
                $table->addRow();
                $table->addCell(600, ['borderSize' => 1])->addText(++$key, $text, ['space' => ['before' => 100]]);
                $table->addCell(1200, ['borderSize' => 1])->addText($stock->invoice_number, $text, ['setNoWrap' => true, 'space' => ['before' => 100]]);
                $table->addCell(1300, ['borderSize' => 1])->addText($stock->garden_name, $text, ['space' => ['before' => 100]]);
                $table->addCell(750, ['borderSize' => 1])->addText($stock->grade_name, $text, ['space' => ['before' => 100]]);
                $table->addCell(800, ['borderSize' => 1])->addText($stock->sale_number, $text, ['space' => ['before' => 100]]);

                $table->addCell(700, ['borderSize' => 1])->addText(number_format(floatval($stock->current_weight/$stock->current_stock), 2), $text, ['space' => ['before' => 100]]);
                $table->addCell(600, ['borderSize' => 1])->addText(number_format(floatval($stock->package_tare * $stock->current_stock + $stock->pallet_weight), 2), $text, ['space' => ['before' => 100]]);
                $table->addCell(600, ['borderSize' => 1])->addText(number_format(floatval($stock->current_weight/$stock->current_stock), 2), $text, ['space' => ['before' => 100]]);

                $table->addCell(1000, ['borderSize' => 1])->addText(number_format($stock->current_stock, 2), $text, ['space' => ['before' => 100]]);
                $table->addCell(1200, ['borderSize' => 1])->addText(number_format($stock->current_weight, 2), $text, ['space' => ['before' => 100]]);
                $table->addCell(1200, ['borderSize' => 1])->addText(number_format(floatval($stock->current_weight + ($stock->package_tare * $stock->current_stock + $stock->pallet_weight)), 2), $text, ['space' => ['before' => 100]]);

                $table->addCell(900, ['borderSize' => 1])->addText($stock->loading_number, $text, ['space' => ['before' => 100]]);
                $table->addCell(900, ['borderSize' => 1])->addText($stock->order_number, $text, ['space' => ['before' => 100]]);

                $table->addCell(900, ['borderSize' => 1])->addText(\Carbon\Carbon::createFromTimestamp($stock->date_received)->format('d-m-y'), $text, ['space' => ['before' => 100]]);
                $table->addCell(2000, ['borderSize' => 1])->addText($stock->warehouse_name, $text, ['space' => ['before' => 100]]);

                $totalPackets += $stock->current_stock;
                $totalWeight += $stock->current_weight;
                $grossWeight += floatval($stock->current_weight + ($stock->package_tare * $stock->current_stock + $stock->pallet_weight));
            }

            $table->addRow();
            $table->addCell(6550, ['gridSpan' => 8])->addText('');
            $table->addCell(1000, ['borderSize' => 1])->addText(number_format($totalPackets, 2), $headers, ['space' => ['before' => 100]]);
            $table->addCell(1200, ['borderSize' => 1])->addText(number_format($totalWeight, 2), $headers, ['space' => ['before' => 100]]);
            $table->addCell(1200, ['borderSize' => 1])->addText(number_format($grossWeight, 2), $headers, ['space' => ['before' => 100]]);
            $table->addCell(4700, ['gridSpan' => 4])->addText('');

            $table->addRow();
            $table->addCell(13250, ['gridSpan' => 15])->addText('');
            $table->addRow();
            $table->addCell(13250, ['gridSpan' => 15])->addText('');

        }

        $stock = new TemplateProcessor(storage_path('stock_template.docx'));
        $stock->setComplexBlock('{table}', $table);
        $stock->setValue('date', $date);
        $stock->setValue('by', $by);
        $stock->setValue('client_name', $results[0]->client_name);
        $docPath = 'Files/TempFiles/STOCK '.time().'.docx';
        $stock->saveAs($docPath);

        //  return response()->download($docPath)->deleteFileAfterSend();


        $phpWord = IOFactory::load($docPath);
        $contents = \PhpOffice\PhpWord\IOFactory::load($docPath);
        $pdfPath = 'Files/TempFiles/STOCK'.time(). ".pdf";
        $converter =  new OfficeConverter($docPath, 'Files/TempFiles/');
        $converter->convertTo('STOCK'.time().".pdf");
        unlink($docPath);
        return response()->download($pdfPath)->deleteFileAfterSend(true);

    }

    public function getDoNumber (Request $request)
    {
        $data = DeliveryOrder::join('users', 'users.user_id', '=', 'delivery_orders.created_by')
            ->join('gardens', 'gardens.garden_id', '=', 'delivery_orders.garden_id')
            ->join('grades', 'grades.grade_id', '=', 'delivery_orders.grade_id')
            ->join('brokers', 'brokers.broker_id', '=', 'delivery_orders.broker_id')
            ->join('warehouses', 'warehouses.warehouse_id', '=', 'delivery_orders.warehouse_id')
            ->join('clients', 'clients.client_id', '=', 'delivery_orders.client_id')
            ->leftJoin('loading_instructions', function ($join) {
                $join->on('loading_instructions.delivery_id', '=', 'delivery_orders.delivery_id')
                    ->whereIn('loading_instructions.status', [1, 2])
                    ->whereNull('loading_instructions.deleted_at');
            })
            ->leftJoin('drivers', 'drivers.driver_id', '=', 'loading_instructions.driver_id')
            ->leftJoin('transporters', 'transporters.transporter_id', '=', 'loading_instructions.transporter_id')
            ->leftJoin('stations', 'stations.station_id', '=', 'loading_instructions.station_id')
            ->leftJoin('users as loading_user', 'loading_user.user_id', '=', 'loading_instructions.created_by')
            ->select('users.username', 'gardens.garden_name','grades.grade_name', 'brokers.broker_name', 'warehouses.warehouse_name', 'clients.client_name', 'delivery_orders.*', 'transporters.transporter_id', 'transporters.transporter_name', 'drivers.driver_id', 'drivers.driver_name', 'drivers.id_number', 'drivers.phone', 'loading_instructions.loading_id', 'loading_instructions.loading_number', 'loading_instructions.status as load_status', 'loading_instructions.registration', 'loading_instructions.created_by as load_user_id', 'loading_user.username as load_user', 'stations.station_name', 'loading_instructions.deleted_at')
            ->whereIn('delivery_orders.status', [1, 2])
            ->where(function($query) use ($request) {
                $query->where('order_number', $request->doNumber)
                    ->orWhere('loading_number', $request->doNumber);
            })
            ->selectSub(function ($query) {
                $query->from('stock_ins')
                    ->select(DB::raw('delivery_orders.packet - COALESCE(SUM(CAST(total_pallets AS SIGNED INTEGER)), 0)'))
                    ->whereColumn('delivery_id', 'delivery_orders.delivery_id');
            }, 'maxPallets')
            ->selectSub(function ($query) {
                $query->from('stock_ins')
                    ->select(DB::raw('delivery_orders.weight - COALESCE(SUM(CAST(net_weight AS SIGNED INTEGER)), 0)'))
                    ->whereColumn('delivery_id', 'delivery_orders.delivery_id');
            }, 'maxWeight')
            ->havingRaw('maxWeight > 0')
            ->get();


        return response()->json($data);
    }

    public function receiveDelivery(Request $request)
    {
        $request->validate([
            'delivery_number' => 'required|string',
            'station' => 'required|string',
            'bay' => 'required|string',
            'transporter' => 'required|string',
            'registration' => 'required|string',
            'idNumber' => 'required|string',
            'driverName' => 'required|string',
            'driverPhone' => 'required|string'
        ]);

//        return $request->date_received;

        $errors = [];

        $deliveries = array_filter($request->orders, function ($delivery) {
            // Check if all required keys exist in the delivery array
            return array_key_exists('numberPackages', $delivery)
                && array_key_exists('totalWeight', $delivery)
                && array_key_exists('packageTare', $delivery)
                && array_key_exists('paletteTare', $delivery)
                && array_key_exists('netWeight', $delivery)
                && array_key_exists('deliveryId', $delivery)
                // Check if any of the values are null
                && $delivery['numberPackages'] !== null
                && $delivery['totalWeight'] !== null
                && $delivery['packageTare'] !== null
                && $delivery['paletteTare'] !== null
                && $delivery['netWeight'] !== null
                && $delivery['deliveryId'] !== null
                /*&& $delivery['palletTare'] !== null*/;
        });

        foreach ($deliveries as $delivery){
            $data = DeliveryOrder::join('users', 'users.user_id', '=', 'delivery_orders.created_by')
                ->join('gardens', 'gardens.garden_id', '=', 'delivery_orders.garden_id')
                ->join('grades', 'grades.grade_id', '=', 'delivery_orders.grade_id')
                ->join('brokers', 'brokers.broker_id', '=', 'delivery_orders.broker_id')
                ->join('warehouses', 'warehouses.warehouse_id', '=', 'delivery_orders.warehouse_id')
                ->join('clients', 'clients.client_id', '=', 'delivery_orders.client_id')
                ->leftJoin('loading_instructions', function ($join) {
                    $join->on('loading_instructions.delivery_id', '=', 'delivery_orders.delivery_id')
                        ->whereIn('loading_instructions.status', [1, 2])
                        ->whereNull('loading_instructions.deleted_at');
                })
                ->leftJoin('drivers', 'drivers.driver_id', '=', 'loading_instructions.driver_id')
                ->leftJoin('transporters', 'transporters.transporter_id', '=', 'loading_instructions.transporter_id')
                ->leftJoin('stations', 'stations.station_id', '=', 'loading_instructions.station_id')
                ->leftJoin('users as loading_user', 'loading_user.user_id', '=', 'loading_instructions.created_by')
                ->select('users.username', 'gardens.garden_name','grades.grade_name', 'brokers.broker_name', 'warehouses.warehouse_name', 'clients.client_name', 'delivery_orders.*', 'transporters.transporter_id', 'transporters.transporter_name', 'drivers.driver_id', 'drivers.driver_name', 'drivers.id_number', 'drivers.phone', 'loading_instructions.loading_id', 'loading_instructions.loading_number', 'loading_instructions.status as load_status', 'loading_instructions.registration', 'loading_instructions.created_by as load_user_id', 'loading_user.username as load_user', 'stations.station_name', 'loading_instructions.deleted_at')
                ->whereIn('delivery_orders.status', [1, 2])
                ->selectSub(function ($query) {
                    $query->from('stock_ins')
                        ->select(DB::raw('delivery_orders.packet - COALESCE(SUM(CAST(total_pallets AS SIGNED INTEGER)), 0)'))
                        ->whereColumn('delivery_id', 'delivery_orders.delivery_id');
                }, 'maxPallets')
                ->selectSub(function ($query) {
                    $query->from('stock_ins')
                        ->select(DB::raw('delivery_orders.weight - COALESCE(SUM(CAST(net_weight AS SIGNED INTEGER)), 0)'))
                        ->whereColumn('delivery_id', 'delivery_orders.delivery_id');
                }, 'maxWeight')
                ->havingRaw('maxWeight > 0')
                ->where('delivery_orders.delivery_id', $delivery['deliveryId'])
                ->first();

            if ($data->maxPallets >= $delivery['numberPackages'] && $data->maxWeight >= $delivery['netWeight']){

                $customId = new CustomIds();
                $driver = Driver::where('id_number', $request->idNumber)->first();

                if ($driver === null){
                    $driverID = $customId->generateId();
                    $drDetails = [
                        'driver_id' => $driverID,
                        'id_number' => $request->idNumber,
                        'driver_name' => strtoupper($request->driverName),
                        'phone' => $request->driverPhone
                    ];

                    $stockId = $customId->generateId();

                    $stock = [
                        'stock_id' => $stockId,
                        'delivery_id' => $delivery['deliveryId'],
                        'station_id' => $request->station,
                        'date_received' => $request->date_received == null ? time() : Carbon::parse($request->date_received)->timestamp,
                        'delivery_number' => $request->delivery_number,
                        'warehouse_bay' => $request->bay,
                        'total_weight' => $delivery['totalWeight'],
                        'net_weight' => $delivery['netWeight'],
                        'pallet_weight' => $delivery['paletteTare'],
                        'package_tare' => $delivery['packageTare'],
                        'total_pallets' => $delivery['numberPackages'],
                        'transporter_id' => $request->transporter,
                        'driver_id' => $driverID,
                        'registration' => $request->registration,
                        'user_id' => auth()->user()->user_id,
                    ];

                    StockIn::create($stock);

                    Driver::create($drDetails);

                    LoadingInstruction::where('delivery_id', $delivery['deliveryId'])->update([
                        'status' => 2,
                        'driver_id' => $driverID,
                        'registration' => strtoupper($request->registration),
                        'transporter_id' => $request->transporter
                    ]);

                    DeliveryOrder::where('delivery_id', $delivery['deliveryId'])->update([
                        'status' => 2
                    ]);

                }else{

                    $stockId = $customId->generateId();

                    $stock = [
                        'stock_id' => $stockId,
                        'delivery_id' => $delivery['deliveryId'],
                        'station_id' => $request->station,
                        'date_received' => $request->date_received == null ? time() : Carbon::parse($request->date_received)->timestamp,
                        'delivery_number' => $request->delivery_number,
                        'warehouse_bay' => $request->bay,
                        'total_weight' => $delivery['totalWeight'],
                        'net_weight' => $delivery['netWeight'],
                        'pallet_weight' => $delivery['paletteTare'],
                        'package_tare' => $delivery['packageTare'],
                        'total_pallets' => $delivery['numberPackages'],
                        'transporter_id' => $request->transporter,
                        'driver_id' => $driver->driver_id,
                        'registration' => $request->registration,
                        'user_id' => auth()->user()->user_id,
                    ];

                    StockIn::create($stock);

                    LoadingInstruction::where('delivery_id',$delivery['deliveryId'])->update([
                        'status' => 2,
                        'driver_id' => $driver->driver_id,
                        'registration' => strtoupper($request->registration),
                        'transporter_id' => $request->transporter
                    ]);

                    DeliveryOrder::where('delivery_id', $delivery['deliveryId'])->update([
                        'status' => 2
                    ]);
                }
            }else{
                $errors[] = 'Oops! Pallets cannot exceed '.$data['maxPallets'].' and weight cannot exceed '. $data['maxWeight'].' for INVOICE NUMBER: ' . $delivery['invNumber'];
            }
            $this->logger->create();
        }

        if (!empty($errors)){
            return redirect()->back()->with(['importErrors' => $errors]);
        }else{
            return redirect()->back()->with('success', 'Success! Delivery has been received');
        }
    }


    public function viewInternalTransfers()
    {
        $transfers = Transfers::join('stations', 'stations.station_id', '=', 'transfers.station_id')
            ->join('delivery_orders', 'delivery_orders.delivery_id', '=', 'transfers.delivery_id')
            ->join('clients', 'clients.client_id', '=', 'delivery_orders.client_id')
            ->join('stations as destination_station', 'destination_station.station_id', '=', 'transfers.destination')
            ->join('warehouse_locations', 'warehouse_locations.location_id', '=', 'destination_station.location_id')
            ->orderBy('transfers.created_at', 'desc')
            ->select('stations.station_name', 'stations.station_id', 'clients.client_name', 'destination_station.station_name as destination_name', 'destination_station.station_id as destination', 'transfers.status', 'transfers.delivery_number', DB::raw('DATE(transfers.created_at) as created_at'), 'warehouse_locations.location_id', 'stations.location_id as origin')
            ->selectRaw('SUM(requested_palettes) as total_palettes')
            ->selectRaw('SUM(requested_weight) as total_weight')
            ->groupBy('delivery_number', 'station_name', 'client_name', 'destination_name', 'status', 'created_at', 'station_id', 'destination_station.station_id', 'location_id', 'stations.location_id')
            ->get();

        return view('admin::transfers.internalTransfers')->with(['transfers' => $transfers]);
    }

    public function prepareInternalTransfer(Request $request)
    {
        $transfers = DB::table('currentstock')->where('current_stock', '>', 0)
            ->where('current_weight', '>', 0)
            ->where(['client_id' => $request->client, 'station_id' => $request->station])
            ->select('client_id', 'stock_id', 'order_number', 'garden_name', 'grade_name', 'invoice_number', 'lot_number', 'current_stock', 'current_weight')
            ->orderBy('garden_name', 'asc')
            ->orderBy('invoice_number', 'asc')
            ->get();

        $client = Client::find($request->client);
        $destination = Station::find($request->station);
        $station = Station::find($request->location);
        $transporters = Transporter::all();
        $registrations = Transfers::pluck('registration')->toArray();
        $drivers = Driver::all();

        return view('admin::transfers.prepareInternalTransfer')->with(['transfers' => $transfers, 'client' => $client, 'station' => $station, 'destination' => $destination, 'transporters' => $transporters, 'registrations' => $registrations, 'users' => $drivers]);

    }

    public function prepareToReceiveTransfer($id)
    {
        $transfers = Transfers::join('stations', 'stations.station_id', '=', 'transfers.station_id')
            ->join('delivery_orders', 'delivery_orders.delivery_id', '=', 'transfers.delivery_id')
            ->join('grades', 'grades.grade_id', '=', 'delivery_orders.grade_id')
            ->join('gardens', 'gardens.garden_id', '=', 'delivery_orders.garden_id')
            ->join('clients', 'clients.client_id', '=', 'delivery_orders.client_id')
            ->join('stations as destination_station', 'destination_station.station_id', '=', 'transfers.destination')
            ->join('warehouse_locations', 'warehouse_locations.location_id', '=', 'destination_station.location_id')
            ->leftJoin('transporters', 'transporters.transporter_id', '=', 'transfers.transporter_id')
            ->leftJoin('drivers', 'drivers.driver_id', '=', 'transfers.driver_id')
            ->orderBy('transfers.created_at', 'desc')
            ->select('stations.station_name', 'stations.station_id', 'clients.client_name', 'destination_station.station_name as destination_name', 'destination_station.station_id as destination', 'transfers.status', 'transfers.delivery_number', 'transfers.created_at', 'warehouse_locations.location_id', 'stations.location_id as origin', 'requested_palettes', 'requested_weight', 'garden_name', 'grade_name', 'invoice_number', 'lot_number', 'stock_id', 'registration', 'driver_name', 'id_number', 'drivers.phone', 'transporters.transporter_id', 'transporter_name', 'transfer_id')
            ->where(['delivery_number' => base64_decode($id)])
            ->where('transfers.status', '!=', 3)
            ->get();

        $transporters = Transporter::all();
        $registrations = Transfers::pluck('registration')->toArray();
        $drivers = Driver::all();
        $stations = WarehouseBay::where('station_id', $transfers[0]->station_id)->get();
        return view('admin::transfers.prepareToReceiveTransfer')->with(['transfers' => $transfers, 'transporters' => $transporters, 'registrations' => $registrations, 'users' => $drivers, 'stations' => $stations]);
    }

    public function viewExternalTransferDetails($id)
    {
        $transfers = ExternalTransfer::join('stock_ins', 'stock_ins.stock_id', '=', 'external_transfers.stock_id')
            ->join('stations', 'stations.station_id', '=', 'stock_ins.station_id')
            ->join('delivery_orders', 'delivery_orders.delivery_id', '=', 'external_transfers.delivery_id')
            ->join('gardens', 'gardens.garden_id', '=', 'delivery_orders.garden_id')
            ->join('grades', 'grades.grade_id', '=', 'delivery_orders.grade_id')
            ->join('clients', 'clients.client_id', '=', 'delivery_orders.client_id')
            ->join('warehouses', 'warehouses.warehouse_id', '=', 'external_transfers.warehouse_id')
            ->select('external_transfers.status', 'client_name', 'warehouses.warehouse_name', 'station_name', 'external_transfers.delivery_number', DB::raw('DATE(external_transfers.created_at) as created_at'), 'location_id', 'transferred_palettes', 'transferred_weight', 'garden_name', 'grade_name', 'invoice_number', 'lot_number', 'ex_transfer_id')
            ->orderBy('garden_name', 'asc')
            ->where(['external_transfers.delivery_number' => base64_decode($id)])
            ->get();

        return view('admin::transfers.viewExternalTransfer')->with(['transfers' => $transfers]);
    }

    public function viewExternalTransfers()
    {
        $transfers = ExternalTransfer::join('stock_ins', 'stock_ins.stock_id', '=', 'external_transfers.stock_id')
            ->join('stations', 'stations.station_id', '=', 'stock_ins.station_id')
            ->join('delivery_orders', 'delivery_orders.delivery_id', '=', 'external_transfers.delivery_id')
            ->join('clients', 'clients.client_id', '=', 'delivery_orders.client_id')
            ->join('warehouses', 'warehouses.warehouse_id', '=', 'external_transfers.warehouse_id')
            ->select('external_transfers.status', 'client_name', 'warehouses.warehouse_name', 'station_name', 'external_transfers.delivery_number', DB::raw('DATE(external_transfers.created_at) as created_at'), 'location_id')
            ->selectRaw('SUM(transferred_palettes) AS total_palettes')
            ->selectRaw('SUM(transferred_weight) AS total_weight')
            ->orderBy('delivery_number', 'desc')
            ->orderBy('created_at', 'desc')
            ->groupBy('delivery_number', 'status', 'client_name', 'warehouse_name', 'delivery_number', 'created_at', 'station_name', 'location_id')
            ->get();

        $warehouses = Warehouse::all();

        return view('admin::transfers.externalTransfers')->with(['transfers' => $transfers, 'warehouses' => $warehouses]);
    }

    public function prepareExternalTransfer(Request $request)
    {
        $transfers = DB::table('currentstock')->where('current_stock', '>', 0)
            ->where('current_weight', '>', 0)
            ->where(['client_id' => $request->client, 'station_id' => $request->location])
            ->select('client_id', 'stock_id', 'order_number', 'garden_name', 'grade_name', 'invoice_number', 'lot_number', 'current_stock', 'current_weight')
            ->orderBy('garden_name', 'asc')
            ->orderBy('invoice_number', 'asc')
            ->get();

        $client = Client::find($request->client);
        $destination = Warehouse::find($request->warehouse);
        $station = Station::find($request->location);
        $transporters = Transporter::all();
        $registrations = Transfers::pluck('registration')->toArray();
        $drivers = Driver::all();

        return view('admin::transfers.prepareExternalTransfer')->with(['transfers' => $transfers, 'client' => $client, 'station' => $station, 'destination' => $destination, 'transporters' => $transporters, 'registrations' => $registrations, 'users' => $drivers]);

    }

    public function selectClients(Request $request)
    {
        $data = DB::table('currentstock')
            ->whereNotNull('current_stock')
            ->where('current_stock', '>', 0)
            ->whereNotNull('current_weight')
            ->where('current_weight', '>', 0)
            ->where(['station_id' => $request->warehouseId])
            ->where( 'current_stock', '>', 0)
            ->orderBy('client_name')
            ->get()
            ->groupBy('client_name');

        return response()->json($data);
    }

    public function selectClient(Request $request)
    {
        $data = DB::table('currentstock')
            ->whereNotNull('current_stock')
            ->where('current_stock', '>', 0)
            ->whereNotNull('current_weight')
            ->where('current_weight', '>', 0)
            ->where(['station_id' => $request->warehouseId, 'client_id' => $request->clientId])
            ->where( 'current_stock', '>', 0)
            ->orderBy('client_name')
            ->get();

        return response()->json($data);
    }

    public function registerInternalRequest(Request $request)
    {
        $requestData = json_decode($request->allDeliveries, true);
        if (isset($requestData['deliveries']) && !empty($requestData['deliveries'])) {
            DB::beginTransaction();
            try {
//                 Loop through each delivery item
                $customId = new CustomIds();
                $driver = Driver::where('id_number', $request->idNumber)->first();
                if ($driver || $request->idNumber === null){
                    $delID = Transfers::newDelivery();

                    foreach ($requestData['deliveries'] as $key => $delivery) {

                        $transferId = $customId->generateId();

                        $stock = StockIn::where('stock_id', $delivery['deliveryId'])->first();
                        $transfer = [
                            'stock_id' => $stock->stock_id,
                            'delivery_number' => $delID,
                            'transfer_id' => $transferId,
                            'driver_id' => $driver == null ? null : $driver->driver_id,
                            'delivery_id' => $stock->delivery_id,
                            'registration' => $request->registration,
                            'transporter_id' => $request->transporter,
                            'station_id' => $request->station,
                            'requested_palettes' => $delivery['palette'],
                            'requested_weight' => $delivery['weight'],
                            'destination' => $request->location,
                            'created_by' => auth()->user()->user_id,
                        ];

                        Transfers::create($transfer);
                    }

                }else {
                    $driverId = $customId->generateId();

                    $newDriver = [
                        'driver_id' => $driverId,
                        'id_number' => $request->idNumber,
                        'driver_name' => strtoupper($request->driverName),
                        'phone' => $request->driverPhone
                    ];

                    Driver::create($newDriver);

                    $delID = Transfers::newDelivery();

                    foreach ($requestData['deliveries'] as $key => $delivery) {

                        $transferId = $customId->generateId();
                        $stock = StockIn::where('stock_id', $delivery['deliveryId'])->first();

                        $transfer = [
                            'transfer_id' => $transferId,
                            'stock_id' => $stock->stock_id,
                            'delivery_number' => $delID,
                            'driver_id' => $driverId,
                            'delivery_id' => $stock->delivery_id,
                            'registration' => $request->registration,
                            'transporter_id' => $request->transporter,
                            'station_id' => $request->station,
                            'requested_palettes' => $delivery['palette'],
                            'requested_weight' => $delivery['weight'],
                            'destination' => $request->location,
                            'created_by' => auth()->user()->user_id,
                        ];

                        Transfers::create($transfer);
                    }
                }

                $this->logger->create();
                DB::commit();
                return redirect()->route('admin.viewInternalTransfers')->with('success', 'Success! Transfer created successfully');
            } catch (\Exception $e) {
                // Rollback the transaction if an exception occurs
                DB::rollback();
                // Handle or log the exception
                return redirect()->back()->with('error', 'Oops! An error occurred please try again');
            }

        }else {

            return redirect()->back()->with('error', "Oops! You need to select at least 1 tea and the number of palettes and weight you are requesting to proceed");
        }


    }

    public function registerExternalRequest(Request $request)
    {
        $request->all();
        $requestData = json_decode($request->allDeliveries, true);
        if (isset($requestData['deliveries']) && !empty($requestData['deliveries'])) {

            DB::beginTransaction();
            try {
                $customId = new CustomIds();

                $driver = Driver::where('id_number', $request->idNumber)->first();

                if ($driver || $request->idNumber === null) {

                    $delID = ExternalTransfer::newDelivery();

                    foreach ($requestData['deliveries'] as $delivery) {
                        $transferId = $customId->generateId();
                        $stock = StockIn::where('stock_id', $delivery['deliveryId'])->first();

                        $transfer = [
                            'stock_id' => $stock->stock_id,
                            'ex_transfer_id' => $transferId,
                            'delivery_number' => $delID,
                            'driver_id' => $driver == null ? null : $driver->driver_id,
                            'delivery_id' => $stock->delivery_id,
                            'warehouse_id' => $request->station,
                            'registration' => $request->registration,
                            'transporter_id' => $request->transporter,
                            'transferred_palettes' => $delivery['palette'],
                            'transferred_weight' => $delivery['weight'],
                            'created_by' => auth()->user()->user_id,
                            'status' => 0
                        ];

                        ExternalTransfer::create($transfer);
                    }

                } else {
                    $driverId = $customId->generateId();

                    $newDriver = [
                        'driver_id' => $driverId,
                        'id_number' => $request->idNumber,
                        'driver_name' => strtoupper($request->driverName),
                        'phone' => $request->driverPhone
                    ];

                    Driver::create($newDriver);
                    $delID = ExternalTransfer::newDelivery();
                    foreach ($requestData['deliveries'] as $delivery) {
                        $transferId = $customId->generateId();
                        $stock = StockIn::where('stock_id', $delivery['deliveryId'])->first();

                        $transfer = [
                            'stock_id' => $stock->stock_id,
                            'ex_transfer_id' => $transferId,
                            'delivery_number' => $delID,
                            'driver_id' => $driverId,
                            'delivery_id' => $stock->delivery_id,
                            'warehouse_id' => $request->station,
                            'registration' => $request->registration,
                            'transporter_id' => $request->transporter,
                            'transferred_palettes' => $delivery['palette'],
                            'transferred_weight' => $delivery['weight'],
                            'created_by' => auth()->user()->user_id,
                            'status' => 0
                        ];

                        ExternalTransfer::create($transfer);
                    }
                }
                $this->logger->create();
                DB::commit();

                return redirect()->route('admin.viewExternalTransfers')->with('success', 'Success! External transfer created successfully');
            } catch (\Exception $e) {
//                // Rollback the transaction if an exception occurs
                DB::rollback();
//                // Handle or log the exception
                return redirect()->back()->with('error', 'Oops! An error occurred please try again');
            }
        }
    }

    public function initiateTransfer($id)
    {
        Transfers::where('delivery_number', base64_decode($id))->update(['status' => 1]);
        $this->logger->create();
        return redirect()->back()->with('success', 'Success! Transfer request initiated successfully');

    }

    public function initiateExternalTransfer($id)
    {
        ExternalTransfer::where('delivery_number',  base64_decode($id))->update(['status' => 1]);
        $this->logger->create();
        return redirect()->back()->with('success', 'Success! Transfer request initiated successfully');

    }

    public function approveExternalTransfer($id)
    {
        ExternalTransfer::where('delivery_number', base64_decode($id))->update(['status' => 2]);
        $this->logger->create();
        return redirect()->back()->with('success', 'Success! Transfer request approved successfully');
    }

    public function releaseExternalTransfer($id)
    {
        ExternalTransfer::where('delivery_number', base64_decode($id))->update(['status' => 3]);
        $this->logger->create();
        return redirect()->back()->with('success', 'Success! Transfer request initiated successfully');
    }

    public function serviceRequest($id)
    {
        Transfers::where('delivery_number', base64_decode($id))->update([
            'status' => 2
        ]);
        $this->logger->create();
        return redirect()->back()->with('success', 'Success! Transfer request serviced and stock updated successfully');
    }

    public function receiveInterTransferRequest(Request $request, $id)
    {
        $request->validate([
            'idNumber' => 'required',
            'driverName' => 'required',
            'driverPhone' => 'required',
        ]);
        $transfers = json_decode($request->allDeliveries, TRUE);
        DB::beginTransaction();
        try {
            foreach ($transfers['deliveries'] as $transferItem){
                $transfer = Transfers::where('transfer_id', $transferItem['deliveryId'])->first();
                $driver = Driver::where('id_number', $request->idNumber)->first();

                $customId = new CustomIds();
                $stockId = $customId->generateId();

                if ($driver){

                    $stock = [
                        'stock_id' => $stockId,
                        'delivery_id' => $transfer->delivery_id,
                        'station_id' => $transfer->destination,
                        'date_received' => time(),
                        'delivery_number' => $transfer->delivery_number,
                        'warehouse_bay' => $request->bayId,
                        'total_weight' => $transferItem['weight'],
                        'total_pallets' => $transferItem['palette'],
                        'pallet_weight' => 0,
                        'package_tare' => 0,
                        'net_weight' => $transferItem['weight'],
                        'user_id' => auth()->user()->user_id,
                        'registration' => $request->registration,
                        'driver_id' => $driver->driver_id,
                        'transporter_id' => $request->transporter,
                    ];
                    StockIn::create($stock);

                    $transfer->update([
                        'status' => 3,
                        'driver_id' => $driver->driver_id,
                        'registration' => $request->registration,
                        'transporter_id' => $request->transporter,
                        'requested_palettes' => $transferItem['palette'],
                        'requested_weight' => $transferItem['weight'],
                    ]);
                }else{
                    $driverId = $customId->generateId();
                    $newDriver = [
                        'driver_id' => $driverId,
                        'id_number' => $request->idNumber,
                        'driver_name' => strtoupper($request->driverName),
                        'phone' => $request->driverPhone
                    ];

                    Driver::create($newDriver);

                    $stock = [
                        'stock_id' => $stockId,
                        'delivery_id' => $transfer->delivery_id,
                        'station_id' => $transfer->destination,
                        'date_received' => time(),
                        'delivery_number' => $transfer->delivery_number,
                        'warehouse_bay' => $request->bayId,
                        'total_weight' => $transferItem['weight'],
                        'total_pallets' => $transferItem['palette'],
                        'pallet_weight' => 0,
                        'package_tare' => 0,
                        'net_weight' => $transferItem['weight'],
                        'user_id' => auth()->user()->user_id,
                        'registration' => $request->registration,
                        'driver_id' => $driverId,
                        'transporter_id' => $request->transporter,
                    ];

                    StockIn::create($stock);

                    $transfer->update([
                        'status' => 3,
                        'driver_id' => $driverId,
                        'registration' => $request->registration,
                        'transporter_id' => $request->transporter,
                        'requested_palettes' => $transferItem['palette'],
                        'requested_weight' => $transferItem['weight'],
                    ]);
                }
            }

            $this->logger->create();
            DB::commit();
            return redirect()->route('admin.viewInternalTransfers')->with('success', 'Success! Transfer request received successfully');

        } catch (\Exception $e) {
            // Rollback the transaction if an exception occurs
            DB::rollback();
            // Handle or log the exception
            return redirect()->back()->with('error', 'Oops! An error occurred please try again');
        }
    }

    public function viewInternalTransferDetails($id)
    {
        $transfers = Transfers::join('stations', 'stations.station_id', '=', 'transfers.station_id')
            ->join('delivery_orders', 'delivery_orders.delivery_id', '=', 'transfers.delivery_id')
            ->join('grades', 'grades.grade_id', '=', 'delivery_orders.grade_id')
            ->join('gardens', 'gardens.garden_id', '=', 'delivery_orders.garden_id')
            ->join('clients', 'clients.client_id', '=', 'delivery_orders.client_id')
            ->join('stations as destination_station', 'destination_station.station_id', '=', 'transfers.destination')
            ->join('warehouse_locations', 'warehouse_locations.location_id', '=', 'destination_station.location_id')
            ->leftJoin('transporters', 'transporters.transporter_id', '=', 'transfers.transporter_id')
            ->leftJoin('drivers', 'drivers.driver_id', '=', 'transfers.driver_id')
            ->orderBy('transfers.created_at', 'desc')
            ->select('stations.station_name', 'stations.station_id', 'clients.client_name', 'destination_station.station_name as destination_name', 'destination_station.station_id as destination', 'transfers.status', 'transfers.delivery_number', 'transfers.created_at', 'warehouse_locations.location_id', 'stations.location_id as origin', 'requested_palettes', 'requested_weight', 'garden_name', 'grade_name', 'invoice_number', 'lot_number', 'stock_id', 'registration', 'driver_name', 'id_number', 'drivers.phone', 'transporters.transporter_id', 'transporter_name', 'transfer_id')
            ->where(['delivery_number' => base64_decode($id)])
            ->get();
        return view('admin::transfers.viewInternalTransfer')->with(['transfers' => $transfers]);
    }

    public function updateInterTransferRequest(Request $request, $id)
    {
        $request->validate([
            'update' => 'required',
            "station" => 'required',
            "transporter" => 'required',
            "registration" => 'required',
            "idNumber" => 'required',
            "driverName" => 'required',
            "driverPhone" => 'required'
        ]);

        $driver = Driver::where('id_number', $request->idNumber)->first();
        if ($driver){

            foreach ($request->update as $transfer){
                $update = [
                    'requested_palettes' => $transfer['pallets'],
                    'requested_weight' => $transfer['weight'],
                    'driver_id' => $driver->driver_id,
                    'destination' => $request->station,
                    'registration' => $request->registration,
                    'transporter_id' => $request->transporter,
                ];
                Transfers::where('transfer_id', $transfer['transferId'])->update($update);
            }


        }else{

            $customId = new CustomIds();
            $driverId = $customId->generateId();

            $driver = [
                'driver_id' => $driverId, 'id_number' => $request->idNumber, 'driver_name' => $request->driverName, 'phone' => $request->driverPhone
            ];

            Driver::create($driver);

            foreach ($request->update as $transfer){
                Transfers::where('transfer_id', $transfer['transfer_id'])->update([
                    'requested_palettes' => $transfer['pallets'],
                    'requested_weight' => $transfer['weight'],
                    'destination' => $request->station,
                    'registration' => $request->registration,
                    'transporter_id' => $request->transporter,
                    'driver_id' => $driverId
                ]);
            }
        }

        $this->logger->create();

        return redirect()->back()->with('success', 'Success! Transfer request updated successfully');
    }

    public function updateExternalTransferRequest (Request $request, $id)
    {
        $request->validate([
            "warehouse" => 'required',
            "transporter" => 'required',
            "registration" => 'required',
            "idNumber" => 'required',
            "driverName" => 'required',
            "driverPhone" => 'required'
        ]);

//        return $request->all();

        $driver = Driver::where('id_number', $request->idNumber)->first();

        if ($driver){
            foreach ($request->update as $transfer){
                $update = [
                    'warehouse_id' => $request->warehouse,
                    'registration' => $request->registration,
                    'transporter_id' => $request->transporter,
                    'transferred_palettes' => $transfer['packagesRequested'],
                    'transferred_weight' => $transfer['weight'],
                    'driver_id' => $driver->driver_id
                ];

                ExternalTransfer::where('ex_transfer_id', $transfer['transferId'])->update($update);
            }
        }else{

            $customId = new CustomIds();
            $driverId = $customId->generateId();

            $driver = [
                'driver_id' => $driverId, 'id_number' => $request->idNumber, 'driver_name' => $request->driverName, 'phone' => $request->driverPhone
            ];

            Driver::create($driver);

            foreach ($request->update as $transfer){
                $update = [
                    'warehouse_id' => $request->warehouse,
                    'registration' => $request->registration,
                    'transporter_id' => $request->transporter,
                    'transferred_palettes' => $transfer['packagesRequested'],
                    'transferred_weight' => $transfer['weight'],
                    'driver_id' => $driverId
                ];

                ExternalTransfer::where('ex_transfer_id', $transfer['transferId'])->update($update);
            }
        }

        return redirect()->back()->with('success', 'Success! Transfer request was successfully updated');

    }

    public function removeInterTransferRequestTea($id)
    {
        Transfers::where('transfer_id', $id)->delete();
        $this->logger->create();
        return redirect()->back()->with('success', 'Success! Transfer request canceled successfully');
    }

    public function cancelInterTransferRequest($id)
    {
//        return base64_decode($id);
        Transfers::where('delivery_number', base64_decode($id))->delete();
        $this->logger->create();
        return redirect()->back()->with('success', 'Success! Transfer request canceled successfully');
    }

    public function cancelExternalTransferRequest($id)
    {
        ExternalTransfer::where('delivery_number', base64_decode($id))->delete();
        $this->logger->create();
        return redirect()->back()->with('success', 'Success! Transfer request canceled successfully');
    }

    public function removeExTransferRequestTea($id)
    {
        ExternalTransfer::where('ex_transfer_id', $id)->delete();
        $this->logger->create();
        return redirect()->back()->with('success', 'Success! Remove tea from transfer request');
    }

    public function viewShippingInstructions()
    {
       $data = ShippingInstruction::join('clients', 'clients.client_id', '=', 'shipping_instructions.client_id')
            ->join('destinations', 'destinations.destination_id', '=', 'shipping_instructions.destination_id')
            ->join('stations', 'stations.station_id', '=', 'shipping_instructions.station_id')
            ->select('shipping_id', 'client_name', 'shipping_instructions.created_at', 'shipping_instructions.status', 'station_name', 'shipping_number', 'vessel_name', 'port_name', 'load_type', 'location_id')
            ->latest('shipping_instructions.created_at');

        $shipping = $data->get();
        $clients = $data->get()->groupBy('client_name');
        $stations = $data->get()->groupBy('station_name');
        $agents = ClearingAgent::all();
        $transporters = Transporter::all();
        $registrations = ShippingInstruction::pluck('registration')->toArray();
        $drivers = Driver::all();

        return view('admin::shipping.SIs')->with(['users' => $drivers, 'registrations' =>$registrations, 'shipping' => $shipping, 'clients' => $clients, 'stations' => $stations, 'agents' => $agents, 'transporters' => $transporters]);
    }

    public function createSI()
    {
        $stations = Station::all();
        $ports = Destination::all();
        $clients = DB::table('currentstock')
            ->whereIn('station_id', $stations->pluck('station_id')->toArray())
            ->groupBy('client_id', 'client_name')
            ->select('client_id', 'client_name')
            ->get();

        return view('admin::shipping.createSI')->with(['ports' => $ports, 'stations' => $stations, 'clients' => $clients]);
    }
    public function addShippingInstruction(Request $request)
    {
        $request->validate([
            'client' => 'string|required',
            'vessel' => 'string|required',
            'shipmentNumber' => 'required|string|unique:shipping_instructions,shipping_number',
            'destination' => 'required|string',
            'package' => 'required|string',
            'containerSize' => 'required|string',
            'consignee' => 'required|string',
            'mark' => 'required|string',
            'shippingInstruction' => 'required|string'
        ]);

        $customId = new CustomIds();
        $siId = $customId->generateId();

        $shipment = new ShippingInstruction;
        $shipment->shipping_id = $siId;
        $shipment->client_id = $request->client;
        $shipment->vessel_name = $request->vessel;
        $shipment->shipping_number = $request->shipmentNumber;
        $shipment->destination_id = $request->destination;
        $shipment->load_type = $request->package;
        $shipment->container_size = $request->containerSize;
        $shipment->consignee = $request->consignee;
        $shipment->shipping_mark = $request->mark;
        $shipment->station_id = $request->station;
        $shipment->shipping_instructions = $request->shippingInstruction;
        $shipment->user_id = auth()->user()->user_id;
        $shipment->save();

        $this->logger->create();

        return redirect()->route('admin.addShipmentTeas', $siId)->with('success', 'Success! Shipping instruction created successfully');

    }

    public function addShipmentTeas($id)
    {
        $si = ShippingInstruction::join('stations', 'stations.station_id', '=', 'shipping_instructions.station_id')
            ->join('clients', 'clients.client_id', '=', 'shipping_instructions.client_id')
            ->join('destinations', 'destinations.destination_id', '=', 'shipping_instructions.destination_id')
            ->leftJoin('transporters', 'transporters.transporter_id', '=', 'shipping_instructions.transporter_id')
            ->leftJoin('drivers', 'drivers.driver_id', 'shipping_instructions.driver_id')
            ->leftJoin('clearing_agents', 'clearing_agents.agent_id', 'shipping_instructions.clearing_agent')
            ->select('shipping_instructions.*', 'location_id', 'client_name', 'clients.phone as client_phone', 'clients.email', 'clients.address', 'port_name', 'transporter_name', 'driver_name', 'drivers.phone', 'agent_name')
            ->find($id);

        $teas = Shipment::join('stock_ins', 'stock_ins.stock_id', '=', 'shipments.stock_id')
            ->join('delivery_orders', 'delivery_orders.delivery_id', '=', 'stock_ins.delivery_id')
            ->join('gardens', 'gardens.garden_id', '=', 'delivery_orders.garden_id')
            ->join('grades', 'grades.grade_id', '=', 'delivery_orders.grade_id')
            ->select('shipment_id', 'shipments.shipped_packages', 'shipments.shipped_weight', 'shipments.status', 'garden_name', 'grade_name', 'invoice_number')
            ->where('shipping_id', $id)
            ->orderBy('garden_name', 'asc')
            ->get();

        $clientTeas = DB::table('currentstock')
            ->where('current_stock', '>', 0)
            ->where('current_weight', '>', 0)
            ->where(['client_id' => $si->client_id, 'station_id' =>  $si->station_id])
            ->orderBy('garden_name', 'asc')
            ->get();
        return view('admin::shipping.addTeasToSI')->with(['teas' => $teas, 'clientTeas' => $clientTeas, 'si' => $si]);

    }

    public function storeShippingInstruction(Request $request, $id)
    {
        $data = json_decode($request->form_data);

        foreach ($data as $tea){
            $stock = StockIn::where('stock_id', $tea->stock_id)->first();
            $customId =  new CustomIds();
            $shipment = [
                'shipment_id' =>  $customId->generateId(),
                'shipping_id' => $id,
                'stock_id' => $tea->stock_id,
                'delivery_id' => $stock->delivery_id,
                'shipped_packages' => $tea->stock,
                'shipped_weight' => $tea->weight,
                'status' => 0
            ];

            Shipment::create($shipment);
        }

        $this->logger->create();

        return redirect()->back()->with('success', 'Successful! Teas added to shipping instruction');

    }

    public function initateSI($id){
        ShippingInstruction::where('shipping_id', $id)->update(['status' => 1]);
        $this->logger->create();
        return redirect()->back()->with('success', 'Success! Shipping instructions updated successfully');
    }

    public function updateShippingInstruction($id)
    {
        ShippingInstruction::where('shipping_id', $id)->update(['status' => 2]);
        $this->logger->create();

        return redirect()->back()->with('success', 'Success! Shipping instructions updated successfully');
    }

    public function deleteShippingInstruction($id)
    {
        Shipment::where('shipping_id', $id)->delete();
        ShippingInstruction::find($id)->delete();
        $this->logger->create();
        return redirect()->back()->with('success', 'Shipping instruction deleted successfully');
    }

    public function markAsShipped($id)
    {
        ShippingInstruction::where('shipping_id', $id)->update(['ship_date' => time(), 'status' => 4]);
        Shipment::where('shipping_id', $id)->update(['status' => 1]);
        return redirect()->back()->with('success', 'Success! Shipment details updated successfully');
    }

    public function updateShippingInstructionDetails(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $driver = Driver::where('id_number', $request->idNumber)->first();

            if ($driver){
                $shipment = [
                    'container_number' => $request->containerNumber,
                    'container_tare' => $request->tare,
                    'clearing_agent' => $request->agent,
                    'seal_number' => $request->seal,
                    'transporter_id' => $request->transporter,
                    'driver_id' => $driver->driver_id,
                    'registration' => $request->registration,
                    'ship_date' => time(),
                    'escort' => $request->escort,
                    'status' => 2
                ];
            }else{

                $customId = new CustomIds();
                $driverId = $customId->generateId();

                $newDriver = [
                    'driver_id' => $driverId,
                    'id_number' => $request->idNumber,
                    'driver_name' => strtoupper($request->driverName),
                    'phone' => $request->driverPhone
                ];

                Driver::create($newDriver);

                $shipment = [
                    'container_number' => $request->containerNumber,
                    'container_tare' => $request->tare,
                    'clearing_agent' => $request->agent,
                    'seal_number' => $request->seal,
                    'transporter_id' => $request->transporter,
                    'driver_id' => $driverId,
                    'registration' => $request->registration,
                    'ship_date' => time(),
                    'escort' => $request->escort,
                    'status' => 2
                ];

            }

            ShippingInstruction::where('shipping_id', $id)->update($shipment);
            $this->logger->create();

            // Commit the transaction
            DB::commit();

        } catch (\Exception $e) {
            // Rollback the transaction if an exception occurs
            DB::rollback();
            // Handle or log the exception
            return redirect()->back()->with('error', 'Oops! An error occurred please try again');
        }

        return redirect()->back()->with('success', 'Successful!, Shipping Instructions updated successfully');
    }

    public function deleteShippingInstructionTea($id)
    {
        Shipment::find($id)->delete();
        $this->logger->create();
        return redirect()->back()->with('success', 'Tea deleted from shipping instruction successfully');

    }

    public function downloadSIDocument($id)
    {
        $shippings = ShippingInstruction::join('clients', 'clients.client_id', '=', 'shipping_instructions.client_id')
            ->leftJoin('clearing_agents', 'clearing_agents.agent_id', '=', 'shipping_instructions.clearing_agent')
            ->join('destinations', 'destinations.destination_id', '=', 'shipping_instructions.destination_id')
            ->leftJoin('transporters', 'transporters.transporter_id', '=', 'shipping_instructions.transporter_id')
            ->leftJoin('drivers', 'drivers.driver_id', '=', 'shipping_instructions.driver_id')
            ->leftJoin('shipments', 'shipments.shipping_id', '=', 'shipping_instructions.shipping_id')
            ->leftJoin('delivery_orders', 'delivery_orders.delivery_id', 'shipments.delivery_id')
            ->leftJoin('gardens', 'gardens.garden_id', 'delivery_orders.garden_id')
            ->leftJoin('grades', 'grades.grade_id', 'delivery_orders.grade_id')
            ->select('sale_number', 'garden_name', 'grade_name', 'invoice_number', 'registration', 'driver_name', 'drivers.phone', 'shipping_instructions', 'ship_date' ,'client_name', 'shipping_number', 'vessel_name', 'port_name', 'shipped_packages', 'shipped_weight', 'consignee', 'shipping_mark', 'container_number', 'agent_name', 'transporter_name',)
            ->where('shipping_instructions.shipping_id', $id)
            ->whereNull('shipments.deleted_at')
            ->get();

        $domPdfPath = base_path('vendor/dompdf/dompdf');
        \PhpOffice\PhpWord\Settings::setPdfRendererPath($domPdfPath);
        \PhpOffice\PhpWord\Settings::setPdfRendererName('DomPDF');

        $table = new Table(['unit' => \PhpOffice\PhpWord\SimpleType\TblWidth::TWIP, 'width' => 1400 * 1400, 'align' => 'center']);

        $header = ['size' => 8, 'name' => 'Cambria', 'space' => ['before' => 100, 'after' => 100], 'bold' => true];
        $text = ['size' => 8, 'name' => 'Cambria', 'space' => ['before' => 100, 'after' => 100], 'bold' => false];

        $table->addRow();
        $table->addCell(1000, ['borderSize' => 1])->addText('#', $header, ['space' => ['before' => 100, 'after' => 100]]);
        $table->addCell(2500, ['borderSize' => 1])->addText('Garden', $header, ['space' => ['before' => 100, 'after' => 100]]);
        $table->addCell(2000, ['borderSize' => 1])->addText('Grade', $header, ['space' => ['before' => 100, 'after' => 100]]);
        $table->addCell(2000, ['borderSize' => 1])->addText('Sale Number', $header, ['space' => ['before' => 100, 'after' => 100]]);
        $table->addCell(2500, ['borderSize' => 1])->addText('INV Number', $header, ['space' => ['before' => 100, 'after' => 100]]);
        $table->addCell(2000, ['borderSize' => 1])->addText('Packages', $header, ['space' => ['before' => 100, 'after' => 100]]);
        $table->addCell(2000, ['borderSize' => 1])->addText('Weight', $header, ['space' => ['before' => 100, 'after' => 100]]);

        $totalWeight = 0;
        $totalPackages = 0;

        foreach ($shippings as $key => $tea){
            $table->addRow();
            $table->addCell(1000, ['borderSize' => 1])->addText(++$key, $text, ['space' => ['before' => 100, 'after' => 100]]);
            $table->addCell(2500, ['borderSize' => 1])->addText($tea->garden_name, $text, ['space' => ['before' => 100, 'after' => 100]]);
            $table->addCell(2000, ['borderSize' => 1])->addText($tea->grade_name, $text, ['space' => ['before' => 100, 'after' => 100]]);
            $table->addCell(2000, ['borderSize' => 1])->addText($tea->sale_number, $text, ['space' => ['before' => 100, 'after' => 100]]);
            $table->addCell(2500, ['borderSize' => 1])->addText($tea->invoice_number, $text, ['space' => ['before' => 100, 'after' => 100]]);
            $table->addCell(2000, ['borderSize' => 1])->addText(number_format($tea->shipped_packages, 2), $text, ['space' => ['before' => 100, 'after' => 100]]);
            $table->addCell(2000, ['borderSize' => 1])->addText(number_format(str_replace([',', '.00'], '', $tea->shipped_weight), 2), $text, ['space' => ['before' => 100, 'after' => 100]]);

            $totalPackages += floatval($tea->shipped_packages);
            $totalWeight += floatval($tea->shipped_weight);
        }


        $table->addRow();
        $table->addCell(9500, ['gridSpan' => 5])->addText();
        $table->addCell(2500, ['borderSize' => 1, 'gridSpan' => 1])->addText(number_format($totalPackages, 2), $header, ['space' => ['before' => 100, 'after' => 100]]);
        $table->addCell(2000, ['borderSize' => 1, 'gridSpan' => 1])->addText(number_format($totalWeight, 2), $header, ['space' => ['before' => 100, 'after' => 100]]);

        $sheet = $shippings[0];
        $staffName = auth()->user()->user->surname.' '.auth()->user()->user->first_name;
        $date = Carbon::now()->format('D, d-m-Y H:i:s');

        $stock = new TemplateProcessor(storage_path('shipping_instructions_template.docx'));
        $stock->setComplexBlock('{table}', $table);
        $stock->setValue('client', $sheet->client_name);
        $stock->setValue('blendNumber', $sheet->shipping_number);
        $stock->setValue('port', $sheet->port_name);
        $stock->setValue('consignee', $sheet->consignee);
        $stock->setValue('shipMark', $sheet->shipping_mark);
        $stock->setValue('vesselName', $sheet->vessel_name);
        $stock->setValue('sealNumber', $sheet->seal_number);
        $stock->setValue('container', $sheet->container_number);
        $stock->setValue('shippedPac', number_format($totalPackages, 2));
        $stock->setValue('shippedWei', number_format($totalWeight, 2));
        $stock->setValue('staffName', $staffName);
        $stock->setValue('agent', $sheet->agent_name);
        $stock->setValue('transporter', $sheet->transporter_name);
        $stock->setValue('registration', $sheet->registration);
        $stock->setValue('driverName', $sheet->driver_name);
        $stock->setValue('driverPhone', $sheet->phone);
        $stock->setValue('details', $sheet->shipping_instructions);
        $stock->setValue('status', $sheet->ship_date == null ? 'Being Processed' : 'Shipped On :'. Carbon::createFromTimestamp($sheet->ship_date)->toDateString());
        $stock->setValue('date', $date);
        $stock->setValue('by', $staffName);
        $docPath = 'Files/TempFiles/SI '.time().'.docx';
        $stock->saveAs($docPath);

        // return response()->download($docPath)->deleteFileAfterSend(true);

        $phpWord = IOFactory::load($docPath);
        $contents = \PhpOffice\PhpWord\IOFactory::load($docPath);
        $pdfPath = 'Files/TempFiles/SI '.time(). ".pdf";
        $converter =  new OfficeConverter($docPath, 'Files/TempFiles/');
        $converter->convertTo('SI '.time().".pdf");
        unlink($docPath);
        return response()->download($pdfPath)->deleteFileAfterSend(true);

    }
    public function downloadDriverClearance($id)
    {
        $shippings = ShippingInstruction::join('clients', 'clients.client_id', '=', 'shipping_instructions.client_id')
            ->leftJoin('clearing_agents', 'clearing_agents.agent_id', '=', 'shipping_instructions.clearing_agent')
            ->join('destinations', 'destinations.destination_id', '=', 'shipping_instructions.destination_id')
            ->leftJoin('transporters', 'transporters.transporter_id', '=', 'shipping_instructions.transporter_id')
            ->leftJoin('drivers', 'drivers.driver_id', '=', 'shipping_instructions.driver_id')
            ->leftJoin('shipments', 'shipments.shipping_id', '=', 'shipping_instructions.shipping_id')
            ->leftJoin('currentstock', function($join) {
                $join->on('currentstock.stock_id', '=', 'shipments.stock_id')
                    ->on('currentstock.delivery_id', '=', 'shipments.delivery_id');
            })
            ->where('shipping_instructions.shipping_id', $id)
            ->get();

        $totalWeight = 0;
        $totalPackages = 0;

        foreach ($shippings as $key => $tea){
            $totalPackages += floatval($tea->shipped_packages);
            $totalWeight += floatval($tea->shipped_weight);
        }

        $sheet = $shippings[0];
        $staffName = auth()->user()->user->surname.' '.auth()->user()->user->first_name;
        $date = Carbon::now()->format('D, d-m-Y H:i:s');

        $stock = new TemplateProcessor(storage_path('driver_clearance_template.docx'));
        $stock->setValue('blendNumber', $sheet->shipping_number);
        $stock->setValue('port', $sheet->port_name);
        $stock->setValue('consignee', $sheet->consignee);
        $stock->setValue('shipMark', $sheet->shipping_mark);
        $stock->setValue('vesselName', $sheet->vessel_name);
        $stock->setValue('sealNumber', $sheet->seal_number);
        $stock->setValue('container', $sheet->container_number);
        $stock->setValue('shippedPac', number_format($totalPackages, 2));
        $stock->setValue('shippedWei', number_format($totalWeight, 2));
        $stock->setValue('staffName', $staffName);
        $stock->setValue('agent', $sheet->agent_name);
        $stock->setValue('transporter', $sheet->transporter_name);
        $stock->setValue('registration', $sheet->registration);
        $stock->setValue('driverName', $sheet->driver_name);
        $stock->setValue('driverPhone', $sheet->phone);
        $stock->setValue('date', $date);
        $stock->setValue('by', $staffName);
        $stock->setValue('loading', $sheet->loading_type == 1 ? 'LOOSE LOADING' : 'PALLETIZED LOADING');
        $docPath = 'Files/TempFiles/DRIVER CLEARANCE '.time().'.docx';
        $stock->saveAs($docPath);

        // return response()->download($docPath)->deleteFileAfterSend(true);

        $phpWord = IOFactory::load($docPath);
        $contents = \PhpOffice\PhpWord\IOFactory::load($docPath);
        $pdfPath = 'Files/TempFiles/DRIVER CLEARANCE '.time(). ".pdf";
        $converter =  new OfficeConverter($docPath, 'Files/TempFiles/');
        $converter->convertTo('DRIVER CLEARANCE '.time().".pdf");
        unlink($docPath);
        return response()->download($pdfPath)->deleteFileAfterSend(true);

    }

    public function viewBlendProcessing()
    {
        $data = DB::table('blend_sheets')
            ->join('clients', 'clients.client_id', '=', 'blend_sheets.client_id')
            ->join('destinations', 'destinations.destination_id', '=', 'blend_sheets.destination_id')
            ->join('stations', 'stations.station_id', '=', 'blend_sheets.station_id')
            ->select('blend_sheets.blend_id', 'blend_sheets.created_at', 'stations.station_id', 'station_name', 'client_name', 'blend_sheets.client_id', 'blend_number', 'vessel_name', 'port_name', 'blend_sheets.status', 'output_packages', 'output_weight', 'location_id')
            ->latest('blend_sheets.created_at');

        $sheets = $data->whereNull('blend_sheets.deleted_at')->get();
        $clients = $data->get()->groupBy('client_name');
        $stations = $data->get()->groupBy('station_name');
        return view('admin::shipping.blendSheets')->with(['sheets' => $sheets, 'clients' => $clients, 'stations' => $stations]);
    }

    public function createBlendSheet()
    {
        $stations = Station::all();
        $ports = Destination::all();
        $clients = DB::table('currentstock')
            ->whereIn('station_id', $stations->pluck('station_id')->toArray())
            ->groupBy('client_id', 'client_name')
            ->select('client_id', 'client_name')
            ->get();
        return view('admin::shipping.createBlend')->with(['ports' => $ports, 'stations' => $stations, 'clients' => $clients]);
    }

    public function addBlendSheet(Request $request)
    {
        $request->validate([
            'client' => 'required|string',
            'station' => 'required|string',
            'shipmentNumber' =>'required|string',
            'contract' => 'required|string',
            'destination' => 'required|string',
            'packagingType' =>'required|string',
            'containerSize' => 'required|string',
            'consignee' => 'required|string',
            'mark' => 'required|string',
        ]);

        $customId = new CustomIds();
        $sheet = [
            'blend_id' => $customId->generateId(),
            'client_id' => $request->client,
            'vessel_name' => $request->vessel,
            'blend_number' => $request->shipmentNumber,
            'contract' => $request->contract,
            'destination_id' => $request->destination,
            'garden' => $request->gardenName,
            'grade' => $request->blendGrade,
            'blend_date' => $request->blendDate,
            'package_type' => $request->packagingType,
            'container_size' => $request->containerSize,
            'consignee' => $request->consignee,
            'shipping_mark' => $request->mark,
            'standard_details' => $request->shippingInstruction,
            'station_id' => $request->station,
            'user_id' => auth()->user()->user_id
        ];
        BlendSheet::create($sheet);
        $this->logger->create();
        return redirect()->route('admin.addBlendTeas', $sheet['blend_id'])->with('success', 'Success! Blend sheet created successfully');
    }

    public function addBlendTeas($id)
    {
        $bs = BlendSheet::join('clients', 'clients.client_id', '=', 'blend_sheets.client_id')
            ->join('destinations', 'destinations.destination_id', '=', 'blend_sheets.destination_id')
            ->join('stations', 'stations.station_id', '=', 'blend_sheets.station_id')
            ->leftJoin('transporters', 'transporters.transporter_id', '=', 'blend_sheets.transporter_id')
            ->leftJoin('drivers', 'drivers.driver_id', '=', 'blend_sheets.driver_id')
            ->leftJoin('clearing_agents', 'clearing_agents.agent_id', '=', 'blend_sheets.agent_id')
            ->leftJoin('blend_shipments', 'blend_shipments.blend_id', '=', 'blend_sheets.blend_id')
            ->select(
                'blend_sheets.blend_id',
                'blend_sheets.blend_number',
                'blend_sheets.status',
                'blend_sheets.consignee',
                'blend_sheets.vessel_name',
                'blend_sheets.shipping_mark',
                'blend_sheets.standard_details',
                'blend_sheets.registration',
                'blend_sheets.escort',
                'blend_sheets.container_tare',
                'blend_sheets.seal_number',
                'clients.client_name',
                'clients.phone as client_phone',
                'clients.email',
                'clients.address',
                'destinations.port_name',
                'transporters.transporter_name',
                'location_id',
                'drivers.driver_name',
                'drivers.phone',
                'clearing_agents.agent_name',
                'blend_sheets.station_id',
                'blend_sheets.client_id'
            )
            ->selectRaw('SUM(blend_shipments.blended_packages) as outputPackages')
            ->selectRaw('SUM(blend_shipments.net_weight) as outputWeight')
            ->where('blend_sheets.blend_id', $id) // Use where instead of find
            ->groupBy(
                'blend_sheets.blend_id',
                'blend_sheets.blend_number',
                'blend_sheets.status',
                'blend_sheets.consignee',
                'blend_sheets.vessel_name',
                'blend_sheets.shipping_mark',
                'blend_sheets.standard_details',
                'blend_sheets.registration',
                'blend_sheets.escort',
                'blend_sheets.container_tare',
                'blend_sheets.seal_number',
                'clients.client_name',
                'clients.phone',
                'clients.email',
                'clients.address',
                'destinations.port_name',
                'transporters.transporter_name',
                'location_id',
                'drivers.driver_name',
                'drivers.phone',
                'clearing_agents.agent_name',
                'blend_sheets.station_id',
                'blend_sheets.client_id'
            )
            ->first(); // Fetch the first record that matches the conditions

        $teas = BlendTea::leftJoin('delivery_orders', 'delivery_orders.delivery_id', '=', 'blend_teas.delivery_id')
            ->leftJoin('gardens', 'gardens.garden_id', '=', 'delivery_orders.garden_id')
            ->leftJoin('grades','grades.grade_id','=','delivery_orders.grade_id')
            ->leftJoin('loading_instructions', function($join) {
                $join->on('loading_instructions.delivery_id','=','delivery_orders.delivery_id')
                    ->whereNull('loading_instructions.deleted_at');
            })
            ->leftJoin('blendBalances', function ($join){
                $join->on('blendBalances.blend_balance_id', '=', 'blend_teas.stock_id')
                    ->on('blendBalances.blend_id', '=', 'blend_teas.delivery_id');
            })
            ->select('blended_id', 'blend_teas.blended_packages', 'blend_teas.blended_weight', 'blend_teas.status', 'garden_name', 'grade_name', 'grade', 'garden', 'loading_number', 'sale_number', 'prompt_date', 'invoice_number', 'blend_number', 'blend_date')
            ->where('blend_teas.blend_id', $id)
            ->orderBy('blend_teas.created_at', 'desc')
            ->get();
        $clientTeas = DB::table('currentstock')
            ->where('current_stock', '>', 0)
            ->where('current_weight', '>', 0)
            ->where(['client_id' => $bs->client_id, 'station_id' => $bs->station_id])
            ->orderBy('sortOrder', 'desc')
            ->get();
        $blendBalances = DB::table('blendBalances')->where(['client_id' => $bs->client_id, 'status' => 0])->where('current_weight', '>', 0)->get();
        return view('admin::shipping.addTeasToBlend')->with(['teas' => $teas, 'clientTeas' => $clientTeas, 'bs' => $bs, 'blendBalances' => $blendBalances]);
    }

    public function storeBlendTeas(Request $request, $id)
    {
        $data = json_decode($request->form_data);
        foreach ($data as $tea){
            $stock = StockIn::where('stock_id', $tea->stock_id)->first();
            $customId =  new CustomIds();
            $sheet = [
                'blended_id' =>  $customId->generateId(),
                'blend_id' => $id,
                'stock_id' => $tea->stock_id,
                'delivery_id' => $stock->delivery_id,
                'blended_packages' => $tea->stock,
                'blended_weight' => round($tea->weight, 2),
            ];
            BlendTea::create($sheet);
        }
        $this->logger->create();
        return redirect()->back()->with('success', 'Successful! Teas added to blend sheet');
    }

    public function addBlendBalanceTeas(Request $request, $id)
    {
        $filteredBlends = array_filter($request->blends, function ($record) {
            // Check if all required keys exist in the delivery array
            return array_key_exists('packages', $record)
                && array_key_exists('weights', $record)
                // Check if any of the values are null
                && $record['packages'] !== null
                && $record['weights'] !== null;
        });

        DB::beginTransaction();
        try {
            foreach ($filteredBlends as $blendID => $stock){
                $blendB = BlendBalance::find($blendID);
                $customId =  new CustomIds();
                $sheet = [
                    'blended_id' =>  $customId->generateId(),
                    'blend_id' => $id,
                    'stock_id' => $blendB->blend_balance_id,
                    'delivery_id' => $blendB->blend_id,
                    'blended_packages' => $stock['packages'],
                    'blended_weight' => $stock['weights'],
                ];

                BlendTea::create($sheet);
            }
            $this->logger->create();
            DB::commit();
            return redirect()->back()->with('success', 'Successful! Teas added to blend sheet');

        } catch (\Exception $e) {
            // Rollback the transaction if an exception occurs
            DB::rollback();
            // Handle or log the exception
            return redirect()->back()->with('error', 'Oops! An error occurred please try again');
        }
    }

    public function deleteBlendTea($id)
    {
        $bt = BlendTea::find($id);
        BlendBalance::where('blend_balance_id', $bt->stock_id)->update(['status' => 0]);
        $bt->delete();

        return redirect()->back()->with('success', 'Successful! Teas removed from blend sheet successfully');
    }

    public function updateOutTurnReport ($id)
    {
        // return $id;
       $bs = BlendSheet::withTrashed()->leftJoin('blend_teas', 'blend_teas.blend_id', '=', 'blend_sheets.blend_id')
            ->join('clients', 'clients.client_id', 'blend_sheets.client_id')
            ->select('blend_sheets.blend_id', 'client_name', 'blend_number') // Specify necessary columns
            ->selectRaw('SUM(blend_teas.blended_packages) as input_packages')
            ->selectRaw('SUM(blend_teas.blended_weight) as input_weight')
            ->where('blend_sheets.blend_id', $id) // Assuming $id is for blend_sheets.blend_id
            ->whereNull('blend_teas.deleted_at')
            ->groupBy('blend_id', 'blend_number', 'client_name') // Group by specific columns
            ->first();

        $agents = ClearingAgent::all();
        $transporters = Transporter::all();
        $registrations = BlendSheet::pluck('registration')->toArray();
        $users = Driver::all();

        return view('admin::shipping.updateOutTurnReport')->with(['bs' => $bs, 'agents' => $agents, 'transporters' => $transporters, 'registrations' => $registrations, 'users' => $users]);
    }
    public function updateBlendSheet($id)
    {
        BlendSheet::where('blend_id', $id)->update(['status' => 2]);
        $this->logger->create();
        return redirect()->back()->with('success', 'Success! Blend sheet updated successfully');
    }

    public function updateBlendSheetDetails(Request $request, $id)
    {
        $request->validate(['containerNumbers' => 'required']);
        DB::beginTransaction();
        try {

            $blendSheet = BlendSheet::find($id);
            $customIds = new CustomIds();
            $driver = Driver::where('id_number', $request->idNumber)->first();

            $outputPacks = 0;
            $outputWeight = 0;

            if ($request->blend !== null){
                foreach ($request->blend as $shipping){
                    $blendShipment = $customIds->generateId();
                    $shipment = [
                        'blend_shipment_id' => $blendShipment,
                        'blend_id' => $blendSheet->blend_id,
                        'blended_packages' => $shipping['packages'],
                        'unit_weight' => $shipping['weight'],
                        'weight_variance' => $request->tareVariance,
                        'net_weight' => floatval($shipping['packages']) * floatval($shipping['weight']),
                        'package_tare' => $request->packageTare,
                        'gross_weight' => floatval($shipping['packages']) * floatval($shipping['weight']) + (floatval($shipping['packages']) * floatval($request->packageTare)) + floatval($shipping['packages']) * floatval($request->tareVariance)
                    ];

                    $outputPacks += floatval( $shipping['packages']);
                    $outputWeight += floatval( $shipping['weight']) * floatval( $shipping['packages']);

                    BlendShipment::create($shipment);
                }
            }


            if ($driver){

                $blendSheet->update([
                    'driver_id' => $driver->driver_id,
                    'agent_id' => $request->agentId,
                    'transporter_id' => $request->transporter,
                    'registration' => $request->registration,
                    'seal_number' => $request->seal,
                    'escort' => $request->escortId,
                    'blend_date' => $request->blendDate,
                    'container_tare' => $request->tare,
                    'output_packages' => $outputPacks,
                    'output_weight' => $outputWeight,
                    'packet_tare' => $request->packageTare,
                    'b_dust' => $request->bDust,
                    'c_dust' => $request->cDust,
                    'fibre' => $request->fibre,
                    'sweepings' => $request->sweepings,
                    'status' => 2,
                ]);

            }else{

                $driverId = $customIds->generateId();

                $newDriver = [
                    'driver_id' => $driverId,
                    'id_number' => $request->idNumber,
                    'driver_name' => strtoupper($request->driverName),
                    'phone' => $request->driverPhone
                ];

                Driver::create($newDriver);

                $blendSheet->update( [
                    'driver_id' => $driverId,
                    'agent_id' => $request->agentId,
                    'transporter_id' => $request->transporter,
                    'registration' => $request->registration,
                    'seal_number' => $request->seal,
                    'escort' => $request->escortId,
                    'blend_date' => $request->blendDate,
                    'container_tare' => $request->tare,
                    'output_packages' => $outputPacks,
                    'output_weight' => $outputWeight,
                    'packet_tare' => $request->packageTare,
                    'b_dust' => $request->bDust,
                    'c_dust' => $request->cDust,
                    'fibre' => $request->fibre,
                    'sweepings' => $request->sweepings,
                    'status' => 2,
                ]);
            }

            if($request->balances !== null){
                foreach ($request->balances as $blendBal){
                    $blendBalance = $customIds->generateId();
                    $balance = [
                        'blend_balance_id' => $blendBalance,
                        'blend_id' => $blendSheet->blend_id,
                        'ex_packages' => $blendBal['packages'],
                        'unit_weight' => floatval($blendBal['weight']),
                        'net_weight' => floatval($blendBal['weight']) * floatval($blendBal['packages']),
                        'station_id' => $request->station_id,
                        'gross_weight' => floatval($blendBal['weight']) * floatval($blendBal['packages']),
                        'type' => 1
                    ];

                    BlendBalance::create($balance);
                }
            }

            BlendBalance::create([
                'blend_balance_id' => (new CustomIds())->generateId(),
                'blend_id' => $blendSheet->blend_id,
                'ex_packages' => 1,
                'unit_weight' => $request->bDust,
                'net_weight' => $request->bDust,
                'station_id' => $request->station_id,
                'gross_weight' => $request->bDust,
                'type' => 2
            ]);

            if ($request->containerNumbers !== null){
                foreach (explode(',', $request->containerNumbers) as $containerNumber){
                    $container =[
                        'container_id' => $customIds->generateId(),
                        'container_number' => $containerNumber,
                        'blend_id' => $blendSheet->blend_id
                    ];
                    ShipmentContainer::create($container);
                }
            }

            $customId = new CustomIds();

            foreach ($request->supervisor as $key => $user){

                $supervisor = [
                    'supervision_id' => $customId->generateId(),
                    'blend_id' => $id,
                    'supervisor_type' => $key,
                    'supervisor_name' => $user['name'],
                    'compiled_by' => auth()->user()->user_id
                ];

                BlendSupervision::create($supervisor);
            }

            foreach ($request->new as $key => $new){
                $newMaterial = [
                    'material_id' => $customId->generateId(),
                    'blend_id' => $id,
                    'material_type' => $key,
                    'total' => $new['name'],
                    'condition' => 1
                ];
                BlendMaterial::create($newMaterial);
            }

            foreach ($request->inUse as $key => $used){
                $usedMaterial = [
                    'material_id' => $customId->generateId(),
                    'blend_id' => $id,
                    'material_type' => $key,
                    'total' => $used['name'],
                    'condition' => 2
                ];

                BlendMaterial::create($usedMaterial);
            }

            foreach ($request->damaged as $key => $used){
                $usedMaterial = [
                    'material_id' => $customId->generateId(),
                    'blend_id' => $id,
                    'material_type' => $key,
                    'total' => $used['name'],
                    'condition' => 3
                ];

                BlendMaterial::create($usedMaterial);
            }

            BlendSheet::where('blend_id', $id)->update(['status' => 2]);
            BlendTea::where('blend_id', $id)->update(['status' => 1]);

            $this->logger->create();

            // Commit the transaction
            DB::commit();
            return redirect()->route('admin.viewBlendProcessing')->with('success', 'Success! Blend sheet updated successfully');

        } catch (\Exception $e) {
            // Rollback the transaction if an exception occurs
            DB::rollback();
            // Handle or log the exception
            return redirect()->back()->with('error', 'Oops! An error occurred please try again');
        }
    }

    public function markBlendTeaAsShipped($id)
    {
        BlendSheet::where('blend_id', $id)->update(['blend_shipped' => time(), 'status' => 4]);
        BlendTea::where('blend_id', $id)->update(['status' => 1]);
        return redirect()->back()->with('success', 'Success! Blend sheet details updated successfully');
    }

    public function deleteBlendSheet($id)
    {
        DB::beginTransaction();
        try {
            BlendBalance::where('blend_id', $id)->delete();
            BlendTea::where('blend_id', $id)->delete();
            BlendShipment::where('blend_id', $id)->delete();
            ShipmentContainer::where('blend_id', $id)->delete();
            BlendSupervision::where('blend_id', $id)->delete();
            BlendMaterial::where('blend_id', $id)->delete();
            BlendSheet::where('blend_id', $id)->delete();
            $this->logger->create();

            // Commit the transaction
            DB::commit();
            return redirect()->back()->with('success', 'Success! Blend sheet updated successfully');

        } catch (\Exception $e) {
                // Rollback the transaction if an exception occurs
            DB::rollback();
            // Handle or log the exception
            return redirect()->back()->with('error', 'Oops! An error occurred please try again');
        }
    }

    public function viewBlendBalances()
    {
        $balances = DB::table('blendBalances')->where('current_weight', '>', 0)->whereNull('deleted_at')->orderBy('blend_number', 'asc')->get();
        return view('admin::stock.blendBalances')->with(['balances' => $balances]);
    }
    public function downloadBlendSheet($id)
    {

        $sheet = DB::table('blend_sheets')
            ->join('clients', 'clients.client_id', '=', 'blend_sheets.client_id')
            ->join('destinations', 'destinations.destination_id', '=', 'blend_sheets.destination_id')
            ->leftJoin('blend_teas', 'blend_teas.blend_id', '=', 'blend_sheets.blend_id')
            ->leftJoin('drivers', 'drivers.driver_id', '=', 'blend_sheets.driver_id')
            ->leftJoin('clearing_agents', 'clearing_agents.agent_id', '=', 'blend_sheets.agent_id')
            ->leftJoin('transporters', 'transporters.transporter_id', '=', 'blend_sheets.transporter_id')
            ->leftJoin('stations', 'stations.station_id', '=', 'blend_sheets.station_id')
            ->select('blend_sheets.blend_id', 'blend_sheets.client_id', 'client_name', 'clients.phone as cPhone', 'email', 'blend_number', 'vessel_name', 'blend_sheets.destination_id', 'port_name', 'shipping_mark', 'consignee', 'contract', 'grade', 'garden', 'standard_details', 'blend_date', 'blend_sheets.status', 'container_size', 'clients.address', 'package_type', 'registration', 'transporter_name', 'transporters.transporter_id', 'driver_name', 'drivers.phone as driver_phone', 'container_tare', 'blend_shipped', 'agent_name', 'seal_number', 'escort', 'output_packages', 'output_weight', 'blend_sheets.packet_tare', 'blend_sheets.agent_id', 'id_number', 'stations.station_id', 'stations.station_name')
            ->selectRaw('SUM(blend_teas.blended_packages) as input_packages')
            ->selectRaw('SUM(blend_teas.blended_weight) as input_weight')
            ->groupBy('blend_sheets.blend_id', 'blend_sheets.client_id', 'client_name', 'clients.phone', 'email', 'blend_number', 'vessel_name', 'blend_sheets.destination_id', 'port_name', 'shipping_mark', 'consignee', 'contract', 'grade', 'garden', 'standard_details', 'blend_date', 'blend_sheets.status', 'container_size', 'clients.address', 'package_type', 'registration', 'transporter_name', 'driver_name', 'driver_phone', 'container_tare', 'blend_shipped', 'agent_name', 'seal_number', 'escort', 'output_packages', 'output_weight', 'packet_tare', 'agent_id', 'transporter_id', 'id_number', 'station_id', 'station_name')
            ->whereNull('blend_teas.deleted_at')
            ->where('blend_sheets.blend_id', $id)
            ->latest('blend_sheets.created_at')
            ->first();

        $teas = BlendTea::leftJoin('delivery_orders', 'delivery_orders.delivery_id', '=', 'blend_teas.delivery_id')
            ->leftJoin('gardens', 'gardens.garden_id', '=', 'delivery_orders.garden_id')
            ->leftJoin('grades','grades.grade_id','=','delivery_orders.grade_id')
            ->leftJoin('loading_instructions', function($join) {
                $join->on('loading_instructions.delivery_id','=','delivery_orders.delivery_id')
                    ->whereNull('loading_instructions.deleted_at');
            })
            ->leftJoin('blendBalances', function ($join){
                $join->on('blendBalances.blend_balance_id', '=', 'blend_teas.stock_id')
                    ->on('blendBalances.blend_id', '=', 'blend_teas.delivery_id');
            })
            ->select('blended_id', 'blend_teas.blended_packages', 'blend_teas.blended_weight', 'blend_teas.status', 'garden_name', 'grade_name', 'grade', 'garden', 'loading_number', 'sale_number', 'prompt_date', 'invoice_number', 'blend_number', 'blend_date')
            ->where('blend_teas.blend_id', $id)
            ->orderBy('blend_teas.created_at', 'desc')
            ->get();

        $date = Carbon::now()->format('D, d-m-Y H:i:s');

        $balPacks = 0;
        $balWeight = 0;
        $blendBalances = BlendBalance::where('blend_id', $id)->whereNull('deleted_at')->get();

        foreach ($blendBalances as $bal){
            if($bal->type == 1){
                $balPacks += $bal->ex_packages;
                $balWeight += $bal->net_weight;
            }
        }

        $domPdfPath = base_path('vendor/dompdf/dompdf');
        \PhpOffice\PhpWord\Settings::setPdfRendererPath($domPdfPath);
        \PhpOffice\PhpWord\Settings::setPdfRendererName('DomPDF');

        $table = new Table(['unit' => \PhpOffice\PhpWord\SimpleType\TblWidth::TWIP, 'width' => 1400 * 1400, 'align' => 'center']);

        $header = ['size' => 8, 'name' => 'Cambria', 'space' => ['before' => 100, 'after' => 100], 'bold' => true];
        $text = ['size' => 8, 'name' => 'Cambria', 'space' => ['before' => 100, 'after' => 100], 'bold' => false];

        $table->addRow();
        $table->addCell(1000, ['borderSize' => 1])->addText('#', $header, ['space' => ['before' => 100, 'after' => 100]]);
        $table->addCell(2000, ['borderSize' => 1])->addText('Garden', $header, ['space' => ['before' => 100, 'after' => 100]]);
        $table->addCell(2000, ['borderSize' => 1])->addText('Grade', $header, ['space' => ['before' => 100, 'after' => 100]]);
        $table->addCell(2000, ['borderSize' => 1])->addText('INV Number', $header, ['space' => ['before' => 100, 'after' => 100]]);
        $table->addCell(2000, ['borderSize' => 1])->addText('Packages', $header, ['space' => ['before' => 100, 'after' => 100]]);
        $table->addCell(2000, ['borderSize' => 1])->addText('Weight', $header, ['space' => ['before' => 100, 'after' => 100]]);
        $table->addCell(2000, ['borderSize' => 1])->addText('Status', $header, ['space' => ['before' => 100, 'after' => 100]]);

        $totalWeight = 0;
        $totalPackages = 0;

        foreach ($teas as $key => $tea){
            $table->addRow();
            $table->addCell(1000, ['borderSize' => 1])->addText(++$key, $header, ['space' => ['before' => 100, 'after' => 100]]);
            $table->addCell(2500, ['borderSize' => 1])->addText($tea->garden_name == null ? $tea->garden : $tea->garden_name, $text, ['space' => ['before' => 100, 'after' => 100]]);
            $table->addCell(2000, ['borderSize' => 1])->addText($tea->grade_name == null ? $tea->grade : $tea->grade_name, $text, ['space' => ['before' => 100, 'after' => 100]]);
            $table->addCell(2000, ['borderSize' => 1])->addText($tea->invoice_number == null ? $tea->blend_number : $tea->invoice_number, $text, ['space' => ['before' => 100, 'after' => 100]]);
            $table->addCell(2000, ['borderSize' => 1])->addText($tea->blended_packages, $text, ['space' => ['before' => 100, 'after' => 100]]);
            $table->addCell(2000, ['borderSize' => 1])->addText($tea->blended_weight, $text, ['space' => ['before' => 100, 'after' => 100]]);
            $table->addCell(2000, ['borderSize' => 1])->addText('In Stock', $text, ['space' => ['before' => 100, 'after' => 100]]);

            $totalPackages += floatval($tea->blended_packages);
            $totalWeight += floatval($tea->blended_weight);
        }

        $blendBal = $totalWeight - $sheet->output_weight;

        $table->addRow();
        $table->addCell(7500, ['gridSpan' => 4])->addText();
        $table->addCell(2000, ['borderSize' => 1, 'gridSpan' => 1])->addText(number_format($totalPackages, 2), $header, ['space' => ['before' => 100, 'after' => 100]]);
        $table->addCell(2000, ['borderSize' => 1, 'gridSpan' => 1])->addText(number_format($totalWeight, 2), $header, ['space' => ['before' => 100, 'after' => 100]]);
        $table->addCell(2000, ['gridSpan' => 1])->addText();

        $containers = ShipmentContainer::where('blend_id', $id)->pluck('container_number')->toArray();
        $staffName = auth()->user()->user->surname.' '.auth()->user()->user->first_name;

        $stock = new TemplateProcessor(storage_path('blend_remnants_stock_template.docx'));
        $stock->setComplexBlock('{table}', $table);
        $stock->setValue('client', $sheet->client_name);
        $stock->setValue('blendNumber', $sheet->blend_number);
        $stock->setValue('port', $sheet->port_name);
        $stock->setValue('contract', $sheet->contract);
        $stock->setValue('consignee', $sheet->consignee);
        $stock->setValue('shipMark', $sheet->shipping_mark);
        $stock->setValue('vesselName', $sheet->vessel_name);
        $stock->setValue('sealNumber', $sheet->seal_number);
        $stock->setValue('container', implode(', ', $containers));
        $stock->setValue('grade', $sheet->grade);
        $stock->setValue('garden', $sheet->garden);
        $stock->setValue('inputPac', number_format($totalPackages, 2));
        $stock->setValue('inputWei', number_format($totalWeight, 2));
        $stock->setValue('shippedPac', number_format($sheet->output_packages, 2));
        $stock->setValue('shippedWei', number_format($sheet->output_weight, 2));
        $stock->setValue('blendBal', number_format($balWeight, 2));
        $stock->setValue('blendBalPac', number_format($balPacks, 2));
        $stock->setValue('by', $staffName);
        $stock->setValue('agent', $sheet->agent_name);
        $stock->setValue('transporter', $sheet->transporter_name);
        $stock->setValue('registration', $sheet->registration);
        $stock->setValue('driverName', $sheet->driver_name);
        $stock->setValue('driverPhone', $sheet->driver_phone);
        $stock->setValue('station', $sheet->station_name);
        $stock->setValue('details', $sheet->standard_details);
        $stock->setValue('date', $date);
        $stock->setValue('blendDate', $sheet->blend_date == null ? '' : $sheet->blend_date);
        $stock->setValue('status', $sheet->blend_shipped == null ? 'Being Processed' : 'SHIPPED '. Carbon::createFromTimestamp($sheet->blend_shipped)->format('D, d-m-Y H:i'));
        $docPath = 'Files/TempFiles/BLEND '.time().'.docx';
        $stock->saveAs($docPath);
        // return response()->download($docPath)->deleteFileAfterSend(true);
        $phpWord = IOFactory::load($docPath);
        $contents = \PhpOffice\PhpWord\IOFactory::load($docPath);
        $pdfPath = 'Files/TempFiles/BLEND'.time(). ".pdf";
        $converter =  new OfficeConverter($docPath, 'Files/TempFiles/');
        $converter->convertTo('BLEND'.time().".pdf");
        unlink($docPath);
        return response()->download($pdfPath)->deleteFileAfterSend(true);

    }

    public function downloadBlendDriverClearance($id)
    {
        $sheet = DB::table('blend_sheets')
            ->join('clients', 'clients.client_id', '=', 'blend_sheets.client_id')
            ->join('destinations', 'destinations.destination_id', '=', 'blend_sheets.destination_id')
            ->leftJoin('blend_teas', 'blend_teas.blend_id', '=', 'blend_sheets.blend_id')
            ->leftJoin('drivers', 'drivers.driver_id', '=', 'blend_sheets.driver_id')
            ->leftJoin('clearing_agents', 'clearing_agents.agent_id', '=', 'blend_sheets.agent_id')
            ->leftJoin('transporters', 'transporters.transporter_id', '=', 'blend_sheets.transporter_id')
            ->leftJoin('stations', 'stations.station_id', '=', 'blend_sheets.station_id')
            ->select('blend_sheets.blend_id', 'blend_sheets.client_id', 'client_name', 'clients.phone as cPhone', 'email', 'blend_number', 'vessel_name', 'blend_sheets.destination_id', 'port_name', 'shipping_mark', 'consignee', 'contract', 'grade', 'garden', 'standard_details', 'blend_date', 'blend_sheets.status', 'container_size', 'clients.address', 'package_type', 'registration', 'transporter_name', 'transporters.transporter_id', 'driver_name', 'drivers.phone as driver_phone', 'container_tare', 'blend_shipped', 'agent_name', 'seal_number', 'escort', 'output_packages', 'output_weight', 'blend_sheets.packet_tare', 'blend_sheets.agent_id', 'id_number', 'stations.station_id', 'stations.station_name')
            ->selectRaw('SUM(blend_teas.blended_packages) as input_packages')
            ->selectRaw('SUM(blend_teas.blended_weight) as input_weight')
            ->groupBy('blend_sheets.blend_id', 'blend_sheets.client_id', 'client_name', 'clients.phone', 'email', 'blend_number', 'vessel_name', 'blend_sheets.destination_id', 'port_name', 'shipping_mark', 'consignee', 'contract', 'grade', 'garden', 'standard_details', 'blend_date', 'blend_sheets.status', 'container_size', 'clients.address', 'package_type', 'registration', 'transporter_name', 'driver_name', 'driver_phone', 'container_tare', 'blend_shipped', 'agent_name', 'seal_number', 'escort', 'output_packages', 'output_weight', 'packet_tare', 'agent_id', 'transporter_id', 'id_number', 'station_id', 'station_name')
            ->whereNull('blend_teas.deleted_at')
            ->where('blend_sheets.blend_id', $id)
            ->latest('blend_sheets.created_at')
            ->first();

        $totalWeight = 0;
        $totalPackages = 0;


        $totalPackages = floatval($sheet->output_packages);
        $totalWeight = floatval($sheet->output_weight);

        $containers = ShipmentContainer::where('blend_id', $id)->pluck('container_number')->toArray();
        $staffName = auth()->user()->user->surname.' '.auth()->user()->user->first_name;
        $date = Carbon::now()->format('D, d-m-Y H:i:s');

        $stock = new TemplateProcessor(storage_path('driver_clearance_template.docx'));
        $stock->setValue('blendNumber', $sheet->blend_number);
        $stock->setValue('port', $sheet->port_name);
        $stock->setValue('consignee', $sheet->consignee);
        $stock->setValue('shipMark', $sheet->shipping_mark);
        $stock->setValue('vesselName', $sheet->vessel_name);
        $stock->setValue('sealNumber', $sheet->seal_number);
        $stock->setValue('container', implode(', ', $containers));
        $stock->setValue('shippedPac', number_format($totalPackages, 2));
        $stock->setValue('shippedWei', number_format($totalWeight, 2));
        $stock->setValue('staffName', $staffName);
        $stock->setValue('agent', $sheet->agent_name);
        $stock->setValue('transporter', $sheet->transporter_name);
        $stock->setValue('registration', $sheet->registration);
        $stock->setValue('driverName', $sheet->driver_name);
        $stock->setValue('driverPhone', $sheet->driver_phone);
        $stock->setValue('date', $date);
        $stock->setValue('by', $staffName);
        $stock->setValue('loading', $sheet->package_type == 1 ? 'PALLETIZED CARDBOARD' : ($sheet->package_type == 2 ? 'PALLETIZED SLIPSHEET' : ($sheet->package_type == 3 ? 'PALLETIZED WOODEN' : 'LOOSE LOADING')));
        $docPath = 'Files/TempFiles/DRIVER CLEARANCE '.time().'.docx';
        $stock->saveAs($docPath);
        // return response()->download($docPath)->deleteFileAfterSend(true);

        $phpWord = IOFactory::load($docPath);
        $contents = \PhpOffice\PhpWord\IOFactory::load($docPath);
        $pdfPath = 'Files/TempFiles/DRIVER CLEARANCE '.time(). ".pdf";
        $converter =  new OfficeConverter($docPath, 'Files/TempFiles/');
        $converter->convertTo('DRIVER CLEARANCE '.time().".pdf");
        unlink($docPath);
        return response()->download($pdfPath)->deleteFileAfterSend(true);
    }

    public function viewDirectDeliveries()
    {
        $orders = DeliveryOrder::join('clients', 'clients.client_id', '=', 'delivery_orders.client_id')
            ->join('stock_ins', 'stock_ins.delivery_id', '=', 'delivery_orders.delivery_id')
            ->leftJoin('stations', 'stations.station_id', '=', 'stock_ins.station_id')
            ->leftJoin('warehouses', 'warehouses.warehouse_id', '=', 'delivery_orders.warehouse_id')
            ->select(
                'order_number',
                'client_name',
                'tea_id',
                'warehouse_name',
                'station_name',
                'delivery_orders.status as order_status',
            )
            ->selectRaw('SUM(total_pallets) AS total_packages')
            ->selectRaw('SUM(net_weight) AS total_net_weight')
            ->where('delivery_orders.delivery_type', 2)
            ->groupBy('order_number', 'client_name',
                'tea_id',
                'warehouse_name',
                'station_name',
                'order_status',
            )
            ->latest('delivery_orders.created_at')
            ->get();

        return view('admin::DOS.directDelivery')->with(['orders' => $orders]);
    }

    public function viewDirectDeliveryOrder($id)
    {
        $delId = base64_decode($id);
        $orders = DeliveryOrder::join('clients', 'clients.client_id', '=', 'delivery_orders.client_id')
            ->join('stock_ins', 'stock_ins.delivery_id', '=', 'delivery_orders.delivery_id')
            ->join('grades', 'grades.grade_id', '=', 'delivery_orders.grade_id')
            ->join('gardens', 'gardens.garden_id', '=', 'delivery_orders.garden_id')
            ->leftJoin('stations', 'stations.station_id', '=', 'stock_ins.station_id')
            ->leftJoin('warehouses', 'warehouses.warehouse_id', '=', 'delivery_orders.warehouse_id')
            ->select('client_name', 'invoice_number', 'tea_id', 'garden_name', 'grade_name',  'delivery_orders.delivery_id', 'order_number', 'client_name', 'tea_id', 'warehouse_name', 'station_name', 'delivery_orders.status as status', 'garden_name', 'grade_name', 'packet', 'weight')
            ->where(['delivery_orders.delivery_type' => 2, 'order_number' => $delId])
            ->latest('delivery_orders.created_at')
            ->get();

        return view('admin::DOS.viewDirectDelivery')->with(['orders' => $orders, 'delivery' => $delId]);
    }

    public function addDirectDelivery()
    {
        $clients = Client::all();
        $gardens = Garden::all();
        $grades = Grade::all();
        $warehouses = Warehouse::all();
        $locationId = Station::where('station_id', auth()->user()->station_id)->first()->location_id;
        $stations = Station::where('location_id', $locationId)->get();
        return view('admin::DOS.addDirectDelivery')->with(['clients' => $clients, 'gardens' => $gardens, 'grades' => $grades, 'warehouses' => $warehouses, 'stations' => $stations]);

    }

    public function registerDirectDeliveryOrder(Request $request)
    {
        $customId = new CustomIds();
        $deliveryId = $customId->generateId();
        $stockId = $customId->generateId();

        $do = [
            'delivery_id' => $deliveryId,
            'order_number' => $request->order_number,
            'tea_id' => $request->tea_id,
            'garden_id' => $request->garden_id,
            'grade_id' => $request->grade_id,
            'packet' => $request->packet,
            'weight' => $request->weight,
            'package' => $request->package,
            'invoice_number' => $request->invoice_number,
            'client_id' => $request->client_id,
            'created_by' => auth()->user()->user_id,
            'delivery_type' => 2,
            'warehouse_id' => $request->warehouse_id
        ];

        DeliveryOrder::create($do);

        $stock = [
            'stock_id' => $stockId,
            'delivery_id' => $deliveryId,
            'station_id' => auth()->user()->station_id,
            'date_received' => time(),
            'delivery_number' => $request->order_number,
            'warehouse_bay' => $request->bay,
            'total_weight' => $request->weight,
            'total_pallets' => $request->packet,
            'pallet_weight' => $request->pallet_weight,
            'package_tare' => $request->tare,
            'net_weight' => $request->netWeight,
            'user_id' => auth()->user()->user_id
        ];

        StockIn::create($stock);

        return redirect()->back()->with('success', 'Tea added successfully');

    }

    public function importStock(Request $request)
    {
        $deliveryNumber = $request->delivery_number;
        $import = new ImportTeas($deliveryNumber);

        // Perform the import
        Excel::import($import, $request->file('uploadFile'));

        // Get specific errors
        $errors = $import->getErrors();

        if (!empty($errors)) {
            return redirect()->back()->with('importErrors', $errors);
        } else {
            // If no errors, continue with your desired action

            return redirect()->back()->with('success', 'Successful! Tea have been imported to the system successfully');
        }
    }

    public function receiveDirectDeliveries($id)
    {
        DeliveryOrder::where('order_number', base64_decode($id))->update(['status' => 2]);
        return redirect()->back()->with('success', 'Tea received and stock updated successfully');

    }

    public function downloadDirectDeliveries($id)
    {
        list($doNumber, $type) = explode(':', base64_decode($id));
        $orders = DeliveryOrder::join('clients', 'clients.client_id', '=', 'delivery_orders.client_id')
            ->join('grades', 'grades.grade_id', '=', 'delivery_orders.grade_id')
            ->join('gardens', 'gardens.garden_id', '=', 'delivery_orders.garden_id')
            ->join('stock_ins', 'stock_ins.delivery_id', '=', 'delivery_orders.delivery_id')
            ->leftJoin('warehouses', 'warehouses.warehouse_id', '=', 'delivery_orders.warehouse_id')
            ->join('stations', 'stations.station_id', '=', 'stock_ins.station_id')
            ->join('warehouse_bays', 'warehouse_bays.bay_id', '=', 'stock_ins.warehouse_bay')
            ->join('user_infos', 'user_infos.user_id', '=', 'delivery_orders.created_by')
            ->select('order_number', 'clients.client_name', 'delivery_orders.tea_id', 'warehouses.warehouse_name', 'stock_ins.total_pallets', 'stock_ins.net_weight', 'delivery_orders.status as order_status', 'gardens.garden_name', 'grades.grade_name', 'delivery_orders.invoice_number', 'stations.station_name', 'warehouse_bays.bay_name', 'total_weight', 'date_received', 'delivery_orders.created_at', 'delivery_orders.status', 'first_name', 'surname', 'delivery_orders.created_by')
            ->where(['delivery_orders.delivery_type' => 2, 'delivery_orders.order_number' => $doNumber])
            ->get();

        if ($type == 2){
            return Excel::download(new ExportDirectDeliveryOrders($orders), 'DIRECT DELIVERY TALLY '.time().'.xlsx', \Maatwebsite\Excel\Excel::XLSX);
        }

        $detail = $orders[0];
        $staffName = auth()->user()->user->surname.' '.auth()->user()->user->first_name;
        $date = Carbon::now()->format('D, d-m-Y H:i:s');
        $user = UserInfo::where('user_id', $detail['created_by'])->first();
        $by = $user->surname.' '.$user->first_name;

        $domPdfPath = base_path('vendor/dompdf/dompdf');
        \PhpOffice\PhpWord\Settings::setPdfRendererPath($domPdfPath);
        \PhpOffice\PhpWord\Settings::setPdfRendererName('DomPDF');

        $table = new Table(['unit' => \PhpOffice\PhpWord\SimpleType\TblWidth::TWIP, 'width' => 1400 * 1400, 'align' => 'center']);

        $header = ['size' => 8, 'name' => 'Cambria', 'space' => ['before' => 100, 'after' => 100], 'bold' => true];
        $text = ['size' => 8, 'name' => 'Cambria', 'space' => ['before' => 100, 'after' => 100], 'bold' => false];

        $table->addRow();
        $table->addCell(600, ['borderSize' => 1])->addText('#', $header, ['space' => ['before' => 100, 'after' => 100]]);
        $table->addCell(1500, ['borderSize' => 1])->addText('Garden', $header, ['space' => ['before' => 100, 'after' => 100]]);
        $table->addCell(1500, ['borderSize' => 1])->addText('Grade', $header, ['space' => ['before' => 100, 'after' => 100]]);
        $table->addCell(1500, ['borderSize' => 1])->addText('INV Number', $header, ['space' => ['before' => 100, 'after' => 100]]);
        $table->addCell(1200, ['borderSize' => 1])->addText('Gross Weight', $header, ['space' => ['before' => 100, 'after' => 100]]);
        $table->addCell(2000, ['borderSize' => 1])->addText('Packages', $header, ['space' => ['before' => 100, 'after' => 100]]);
        $table->addCell(2000, ['borderSize' => 1])->addText('Net Weight', $header, ['space' => ['before' => 100, 'after' => 100]]);
        $table->addCell(2000, ['borderSize' => 1])->addText('Date Rec\'d', $header, ['space' => ['before' => 100, 'after' => 100]]);
        $table->addCell(2000, ['borderSize' => 1])->addText('Producer Whs', $header, ['space' => ['before' => 100, 'after' => 100]]);
        $sn = 0;
        $totalGrossWeight = $totalNetWeight = $totalPackages = 0;
        foreach ($orders as $order){
            $table->addRow();
            $table->addCell(600, ['borderSize' => 1])->addText(++$sn, $text, ['space' => ['before' => 50, 'after' => 50]]);
            $table->addCell(1500, ['borderSize' => 1])->addText($order->garden_name, $text, ['space' => ['before' => 50, 'after' => 50]]);
            $table->addCell(1500, ['borderSize' => 1])->addText($order->grade_name, $text, ['space' => ['before' => 50, 'after' => 50]]);
            $table->addCell(1500, ['borderSize' => 1])->addText($order->invoice_number, $text, ['space' => ['before' => 50, 'after' => 50]]);
            $table->addCell(1200, ['borderSize' => 1])->addText($order->total_weight, $text, ['space' => ['before' => 50, 'after' => 50]]);
            $table->addCell(1500, ['borderSize' => 1])->addText($order->total_pallets, $text, ['space' => ['before' => 50, 'after' => 50]]);
            $table->addCell(1500, ['borderSize' => 1])->addText($order->net_weight, $text, ['space' => ['before' => 50, 'after' => 50]]);
            $table->addCell(1500, ['borderSize' => 1])->addText(Carbon::createFromTimestamp($order->date_received)->format('d-m-Y h:i'), $text, ['space' => ['before' => 50, 'after' => 50]]);
            $table->addCell(2500, ['borderSize' => 1])->addText($order->warehouse_name, $text, ['space' => ['before' => 50, 'after' => 50]]);

            $totalGrossWeight += $order->total_weight;
            $totalNetWeight += $order->net_weight;
            $totalPackages += $order->total_pallets;
        }

        $table->addRow();
        $table->addCell(5100, ['gridSpan' => 4])->addText();
        $table->addCell(1500,['borderSize' => 1])->addText(number_format($totalGrossWeight, 2), $header, ['space' => ['before' => 50, 'after' => 50]] );
        $table->addCell(1500,['borderSize' => 1])->addText(number_format($totalPackages, 2), $header, ['space' => ['before' => 50, 'after' => 50]] );
        $table->addCell(1500,['borderSize' => 1])->addText(number_format($totalNetWeight, 2), $header, ['space' => ['before' => 50, 'after' => 50]] );
        $table->addCell(4000, ['gridSpan' => 2])->addText();

        $stock = new TemplateProcessor(storage_path('direct_delivery_report.docx'));
        $stock->setComplexBlock('table', $table);
        $stock->setValue('client', $detail->client_name);
        $stock->setValue('station', $detail->station_name);
        $stock->setValue('bay', $detail->bay_name);
        $stock->setValue('orderNumber', $detail->order_number);
        $stock->setValue('by', $staffName);
        $stock->setValue('prepared', $by);
        $stock->setValue('date', $date);
        $docPath = 'Files/TempFiles/DELIVERIES TALLY '.time().'.docx';
        $stock->saveAs($docPath);

//         return response()->download($docPath)->deleteFileAfterSend(true);

        $phpWord = IOFactory::load($docPath);
        $contents = \PhpOffice\PhpWord\IOFactory::load($docPath);
        $pdfPath = 'Files/TempFiles/DELIVERIES TALLY '.time(). ".pdf";
        $converter =  new OfficeConverter($docPath, 'Files/TempFiles/');
        $converter->convertTo('DELIVERIES TALLY '.time().".pdf");
        unlink($docPath);
        return response()->download($pdfPath)->deleteFileAfterSend(true);

    }

    public function removeDirectDeliveryTea($id)
    {
        DeliveryOrder::where(['delivery_id' => $id])->delete();
        StockIn::where(['delivery_id' => $id])->delete();
        return redirect()->back()->with('success', 'Selected tea removed from the delivery');
    }

    public function removeDirectDeliveryTeas($id)
    {
        $teasToDelete = DeliveryOrder::where(['order_number' => base64_decode($id), 'status' => null])->pluck('delivery_id');
        DeliveryOrder::whereIn('delivery_id', $teasToDelete)->delete();
        StockIn::whereIn('delivery_id', $teasToDelete)->delete();
        return redirect()->back()->with('success', 'Selected teas removed from the delivery');

    }

    public function downloadInterDelNote($id)
    {
        $orders = Transfers::join('stations', 'stations.station_id', '=', 'transfers.station_id')
            ->join('delivery_orders', 'delivery_orders.delivery_id', '=', 'transfers.delivery_id')
            ->join('grades', 'grades.grade_id', '=', 'delivery_orders.grade_id')
            ->join('gardens', 'gardens.garden_id', '=', 'delivery_orders.garden_id')
            ->join('clients', 'clients.client_id', '=', 'delivery_orders.client_id')
            ->join('stations as destination_station', 'destination_station.station_id', '=', 'transfers.destination')
            ->join('currentstock', function($join) {
                $join->on('currentstock.delivery_id', '=', 'transfers.delivery_id')
                    ->on('currentstock.stock_id', '=', 'transfers.stock_id');
            })
            ->orderBy('transfers.created_at', 'desc')
            ->where(['transfers.delivery_number' => base64_decode($id)])
            ->leftJoin('transporters', 'transporters.transporter_id', '=', 'transfers.transporter_id')
            ->leftJoin('drivers', 'drivers.driver_id', '=', 'transfers.driver_id')
            ->select('stations.station_name', 'transfers.created_by as prepared_by', 'stations.station_id', 'delivery_orders.grade_id', 'delivery_orders.garden_id', 'delivery_orders.client_id', 'clients.client_name', 'gardens.garden_name', 'grades.grade_name', 'transfers.requested_palettes', 'transfers.requested_weight', 'destination', 'destination_station.station_name as destination_name', 'transfers.status', 'transfers.transfer_id', 'currentstock.current_stock', 'currentstock.current_weight', 'transporters.*', 'drivers.*', 'transfers.registration', 'delivery_orders.order_number', 'delivery_orders.lot_number', 'delivery_orders.invoice_number', 'transfers.delivery_number', 'currentstock.sale_number', 'currentstock.pallet_weight', 'currentstock.package_tare', 'delivery_orders.delivery_id', 'currentstock.date_received', 'currentstock.stocked_at')
            ->get();

        $details = $orders[0];

        $prepared = UserInfo::where('user_id', $details->prepared_by)->first();

        $prepare = $prepared->first_name.' '.$prepared->surname;
        $by = auth()->user()->user->surname.' '.auth()->user()->user->first_name;

        $domPdfPath = base_path('vendor/dompdf/dompdf');
        \PhpOffice\PhpWord\Settings::setPdfRendererPath($domPdfPath);
        \PhpOffice\PhpWord\Settings::setPdfRendererName('DomPDF');

        $table = new Table(['unit' => \PhpOffice\PhpWord\SimpleType\TblWidth::TWIP, 'width' => 1400 * 1400, 'align' => 'center']);

        $header = ['size' => 8, 'name' => 'Cambria', 'space' => ['before' => 100, 'after' => 100], 'bold' => true];
        $text = ['size' => 8, 'name' => 'Cambria', 'space' => ['before' => 100, 'after' => 100], 'bold' => false];

        $table->addRow();
        $table->addCell(600, ['borderSize' => 1])->addText('#', $header, ['space' => ['before' => 100, 'after' => 100]]);
        $table->addCell(1500, ['borderSize' => 1])->addText('Garden', $header, ['space' => ['before' => 100, 'after' => 100]]);
        $table->addCell(900, ['borderSize' => 1])->addText('Grade', $header, ['space' => ['before' => 100, 'after' => 100]]);
        $table->addCell(1000, ['borderSize' => 1])->addText('DO NO.', $header, ['space' => ['before' => 100, 'after' => 100]]);
        $table->addCell(1300, ['borderSize' => 1])->addText('INV Number', $header, ['space' => ['before' => 100, 'after' => 100]]);
        $table->addCell(1300, ['borderSize' => 1])->addText('Lot Number', $header, ['space' => ['before' => 100, 'after' => 100]]);
        $table->addCell(1300, ['borderSize' => 1])->addText('Sale Number', $header, ['space' => ['before' => 100, 'after' => 100]]);
        $table->addCell(900, ['borderSize' => 1])->addText('Packages', $header, ['space' => ['before' => 100, 'after' => 100]]);
        $table->addCell(1300, ['borderSize' => 1])->addText('Weight', $header, ['space' => ['before' => 100, 'after' => 100]]);
        $table->addCell(1300, ['borderSize' => 1])->addText('Date Rec\'d', $header, ['space' => ['before' => 100, 'after' => 100]]);

        $packets = 0;
        $weight = 0;

        foreach ($orders as $key => $order){
            $table->addRow();
            $table->addCell(600, ['borderSize' => 1])->addText(++$key, $text, ['space' => ['before' => 100, 'after' => 100]]);
            $table->addCell(1500, ['borderSize' => 1])->addText($order->garden_name, $text, ['space' => ['before' => 100, 'after' => 100]]);
            $table->addCell(900, ['borderSize' => 1])->addText($order->grade_name, $text, ['space' => ['before' => 100, 'after' => 100]]);
            $table->addCell(1000, ['borderSize' => 1])->addText($order->order_number, $text, ['space' => ['before' => 100, 'after' => 100]]);
            $table->addCell(1300, ['borderSize' => 1])->addText($order->invoice_number, $text, ['space' => ['before' => 100, 'after' => 100]]);
            $table->addCell(1300, ['borderSize' => 1])->addText($order->lot_number, $text, ['space' => ['before' => 100, 'after' => 100]]);
            $table->addCell(1300, ['borderSize' => 1])->addText($order->sale_number, $text, ['space' => ['before' => 100, 'after' => 100]]);
            $table->addCell(900, ['borderSize' => 1])->addText($order->requested_palettes, $text, ['space' => ['before' => 100, 'after' => 100]]);
            $table->addCell(1300, ['borderSize' => 1])->addText($order->requested_weight, $text, ['space' => ['before' => 100, 'after' => 100]]);
            $table->addCell(1300, ['borderSize' => 1])->addText(Carbon::createFromTimestamp($order->date_received)->format('d-m-Y'), $text, ['space' => ['before' => 100, 'after' => 100]]);

            $packets += $order->requested_palettes;
            $weight += $order->requested_weight;
        }

        $table->addRow();
        $table->addCell('7900', ['gridSpan' => 7])->addText();
        $table->addCell('1900', ['gridSpan' => 1, 'borderSize' => 1])->addText($packets, $header, ['space' => ['before' => 100, 'after' => 100]]);
        $table->addCell('1300', ['gridSpan' => 1, 'borderSize' => 1])->addText(number_format($weight, 2), $header, ['space' => ['before' => 100, 'after' => 100]]);
        $table->addCell('1300', ['gridSpan' => 1])->addText();

        $localli = new TemplateProcessor(storage_path('inter_delivery_note.docx'));
        $localli->setComplexBlock('{table}', $table);
        $localli->setValue('client', strtoupper($details['client_name']));
        $localli->setValue('station', strtoupper($details['destination_name']));
        $localli->setValue('date', date('D, d/m/y h:i:s'));
        $localli->setValue('dNumber', base64_decode($id));
        $localli->setValue('transporter', $details['transporter_name']);
        $localli->setValue('registration', $details['registration']);
        $localli->setValue('driver', $details['driver_name']);
        $localli->setValue('idNo', $details['id_number']);
        $localli->setValue('phone', $details['phone']);
        $localli->setValue('station', $details['station_name']);
        $localli->setValue('prepared', $prepare);
        $localli->setValue('from', $details['stocked_at']);
        $localli->setValue('by', $by);
        $docPath = 'Files/TempFiles/'.base64_decode($id). '.docx';
        $localli->saveAs($docPath);

//         return response()->download($docPath)->deleteFileAfterSend(true);

        $phpWord = IOFactory::load($docPath);
        $contents = \PhpOffice\PhpWord\IOFactory::load($docPath);
        $pdfPath = 'Files/TempFiles/'.base64_decode($id).".pdf";
        $converter =  new OfficeConverter($docPath, 'Files/TempFiles/');
        $converter->convertTo(base64_decode($id). ".pdf");
        unlink($docPath);
        return response()->download($pdfPath)->deleteFileAfterSend();

    }

    public function downloadExtraDelNote($id)
    {
        $orders = ExternalTransfer::join('currentstock', 'currentstock.stock_id', '=', 'external_transfers.stock_id')
            ->leftJoin('drivers', 'drivers.driver_id', '=', 'external_transfers.driver_id')
            ->leftJoin('warehouses', 'warehouses.warehouse_id', '=', 'external_transfers.warehouse_id')
            ->leftJoin('transporters', 'transporters.transporter_id', '=', 'external_transfers.transporter_id')
            ->select('currentstock.client_name', 'currentstock.garden_name', 'currentstock.grade_name', 'currentstock.current_stock', 'currentstock.current_weight', 'currentstock.package', 'currentstock.station_id', 'external_transfers.driver_id', 'external_transfers.registration', 'external_transfers.warehouse_id', 'external_transfers.ex_transfer_id', 'external_transfers.status as extStatus', 'external_transfers.transferred_palettes', 'external_transfers.transferred_weight', 'transporters.transporter_name', 'transporters.transporter_id', 'drivers.driver_name', 'drivers.id_number', 'drivers.phone', 'warehouses.warehouse_name', 'external_transfers.created_at as sortOrder', 'currentstock.invoice_number', 'external_transfers.delivery_number', 'external_transfers.created_by', 'currentstock.date_received', 'currentstock.order_number', 'currentstock.lot_number', 'currentstock.sale_number', 'currentstock.stocked_at')
            ->where('external_transfers.delivery_number', base64_decode($id))
            ->orderBy('extStatus', 'asc')
            ->orderBy('sortOrder', 'desc')
            ->get();

        $details = $orders[0];

        $prepared = UserInfo::where('user_id', $details->created_by)->first();

        $user = $prepared->first_name.' '.$prepared->surname;
        $printed = auth()->user()->user;
        $by = $printed->first_name.' '.$printed->surname;

        $domPdfPath = base_path('vendor/dompdf/dompdf');
        \PhpOffice\PhpWord\Settings::setPdfRendererPath($domPdfPath);
        \PhpOffice\PhpWord\Settings::setPdfRendererName('DomPDF');

        $table = new Table(['unit' => \PhpOffice\PhpWord\SimpleType\TblWidth::TWIP, 'width' => 1400 * 1400, 'align' => 'center']);

        $header = ['size' => 8, 'name' => 'Cambria', 'space' => ['before' => 100, 'after' => 100], 'bold' => true];
        $text = ['size' => 8, 'name' => 'Cambria', 'space' => ['before' => 100, 'after' => 100], 'bold' => false];

        $table->addRow();
        $table->addCell(600, ['borderSize' => 1])->addText('#', $header, ['space' => ['before' => 100, 'after' => 100]]);
        $table->addCell(1500, ['borderSize' => 1])->addText('Garden', $header, ['space' => ['before' => 100, 'after' => 100]]);
        $table->addCell(900, ['borderSize' => 1])->addText('Grade', $header, ['space' => ['before' => 100, 'after' => 100]]);
        $table->addCell(1300, ['borderSize' => 1])->addText('DO Number', $header, ['space' => ['before' => 100, 'after' => 100]]);
        $table->addCell(1300, ['borderSize' => 1])->addText('INV Number', $header, ['space' => ['before' => 100, 'after' => 100]]);
        $table->addCell(1100, ['borderSize' => 1])->addText('Lot Number', $header, ['space' => ['before' => 100, 'after' => 100]]);
        $table->addCell(1200, ['borderSize' => 1])->addText('Sale Number', $header, ['space' => ['before' => 100, 'after' => 100]]);
        $table->addCell(900, ['borderSize' => 1])->addText('Packages', $header, ['space' => ['before' => 100, 'after' => 100]]);
        $table->addCell(1300, ['borderSize' => 1])->addText('Weight', $header, ['space' => ['before' => 100, 'after' => 100]]);
        $table->addCell(1300, ['borderSize' => 1])->addText('Date Rec\'d', $header, ['space' => ['before' => 100, 'after' => 100]]);

        $packets = 0;
        $weight = 0;

        foreach ($orders as $key => $order){
            $table->addRow();
            $table->addCell(600, ['borderSize' => 1])->addText(++$key, $text, ['space' => ['before' => 100, 'after' => 100]]);
            $table->addCell(1500, ['borderSize' => 1])->addText($order->garden_name, $text, ['space' => ['before' => 100, 'after' => 100]]);
            $table->addCell(900, ['borderSize' => 1])->addText($order->grade_name, $text, ['space' => ['before' => 100, 'after' => 100]]);
            $table->addCell(1300, ['borderSize' => 1])->addText($order->order_number, $text, ['space' => ['before' => 100, 'after' => 100]]);
            $table->addCell(1300, ['borderSize' => 1])->addText($order->invoice_number, $text, ['space' => ['before' => 100, 'after' => 100]]);
            $table->addCell(1100, ['borderSize' => 1])->addText($order->lot_number, $text, ['space' => ['before' => 100, 'after' => 100]]);
            $table->addCell(1200, ['borderSize' => 1])->addText($order->sale_number, $text, ['space' => ['before' => 100, 'after' => 100]]);
            $table->addCell(900, ['borderSize' => 1])->addText($order->transferred_palettes, $text, ['space' => ['before' => 100, 'after' => 100]]);
            $table->addCell(1300, ['borderSize' => 1])->addText($order->transferred_weight, $text, ['space' => ['before' => 100, 'after' => 100]]);
            $table->addCell(1300, ['borderSize' => 1])->addText(Carbon::createFromTimestamp($order->date_received)->format('d-m-Y'), $text, ['space' => ['before' => 100, 'after' => 100]]);

            $packets += $order->transferred_palettes;
            $weight += $order->transferred_weight;
        }

        $table->addRow();
        $table->addCell('7900', ['gridSpan' => 7])->addText();
        $table->addCell('900', ['gridSpan' => 1, 'borderSize' => 1])->addText($packets, $header, ['space' => ['before' => 100, 'after' => 100]]);
        $table->addCell('1300', ['gridSpan' => 1, 'borderSize' => 1])->addText(number_format($weight, 2), $header, ['space' => ['before' => 100, 'after' => 100]]);
        $table->addCell('1300', ['gridSpan' => 1])->addText();

        $localli = new TemplateProcessor(storage_path('extra_delivery_note.docx'));
        $localli->setComplexBlock('{table}', $table);
        $localli->setValue('client', strtoupper($details['client_name']));
        $localli->setValue('station', strtoupper($details['destination_name']));
        $localli->setValue('date', date('D, d/m/y h:i:s'));
        $localli->setValue('dNumber', base64_decode($id));
        $localli->setValue('transporter', $details['transporter_name']);
        $localli->setValue('registration', $details['registration']);
        $localli->setValue('driver', $details['driver_name']);
        $localli->setValue('idNo', $details['id_number']);
        $localli->setValue('phone', $details['phone']);
        $localli->setValue('by', $by);
        $localli->setValue('warehouse', $orders[0]['warehouse_name']);
        $localli->setValue('prepared', $user);
        $localli->setValue('from', $details['stocked_at']);
        $docPath = 'Files/TempFiles/'.base64_decode($id). '.docx';
        $localli->saveAs($docPath);

//         return response()->download($docPath)->deleteFileAfterSend(true);

        $phpWord = IOFactory::load($docPath);
        $contents = \PhpOffice\PhpWord\IOFactory::load($docPath);
        $pdfPath = 'Files/TempFiles/'.base64_decode($id).".pdf";
        $converter =  new OfficeConverter($docPath, 'Files/TempFiles/');
        $converter->convertTo(base64_decode($id). ".pdf");
        unlink($docPath);
        return response()->download($pdfPath)->deleteFileAfterSend();

    }

    public function exportBlendsReport(Request $request)
    {
       $sheets = DB::table('blend_sheets')
            ->leftJoin('blend_shipments', 'blend_shipments.blend_id', '=', 'blend_sheets.blend_id')
            ->join('clients', 'clients.client_id', '=', 'blend_sheets.client_id')
            ->join('destinations', 'destinations.destination_id', '=', 'blend_sheets.destination_id')
            ->join('stations', 'stations.station_id', '=', 'blend_sheets.station_id')
            ->leftJoin('clearing_agents', 'clearing_agents.agent_id', '=', 'blend_sheets.agent_id')
            ->leftJoin('transporters', 'transporters.transporter_id', '=', 'blend_sheets.transporter_id')
            ->leftJoin('user_infos', 'user_infos.user_id', '=', 'blend_sheets.user_id')
            ->select('client_name', 'blend_sheets.created_at', 'station_name', 'blend_number', 'vessel_name', 'port_name', 'consignee', 'contract', 'blend_sheets.status', 'container_size', 'package_type', 'transporter_name', 'agent_name', 'output_packages', 'output_weight', 'stations.station_name', 'surname', 'first_name', 'blend_sheets.deleted_at')
            ->selectRaw('SUM(blend_shipments.blended_packages) as shipped_packages, SUM(blend_shipments.gross_weight) as shipped_weight')
            ->groupBy('client_name', 'blend_sheets.created_at', 'stations.station_id', 'station_name', 'blend_number', 'vessel_name', 'port_name', 'consignee', 'contract', 'blend_sheets.status', 'container_size', 'package_type', 'transporter_name', 'agent_name', 'output_packages', 'output_weight', 'stations.station_name', 'surname', 'first_name', 'deleted_at')
            ->latest('blend_sheets.created_at')
            ->orderBy('clients.client_name', 'asc');

        $client = $request->client;
        $station = $request->station;
        $from = $request->from;
        $to = $request->to;
        $status = $request->report;

//            return $request->all();

        if (!is_null($from)) {
            $fromTimestamp = strtotime($from);
            $sheets->where('blend_sheets.created_at', '>=', $from);
        }
        if (!is_null($to)) {
            $toTimestamp = strtotime($to);
            $sheets->where('blend_sheets.created_at', '<=', $to);
        }
        if (!is_null($station)) {
            $sheets->where('station_id', $station);
        }

        if (!is_null($client)) {
            $sheets->where('blend_sheets.client_id', $client);
        }

        if (!is_null($status)) {
            if ($status == 1) {
                $sheets->where('blend_sheets.status', '<', 4) ;
            }else{
                $sheets->where('blend_sheets.status', 4);
            }
        }

       $blends = $sheets->get()->groupBy('blend_number');

        return Excel::download(new ExportBlendReport($blends), 'BLEND STATUS'.' '.time().'.xlsx', \Maatwebsite\Excel\Excel::XLSX);

    }

    public function exportSTLReport(Request $request)
    {
        $shippings = ShippingInstruction::join('clients', 'clients.client_id', '=', 'shipping_instructions.client_id')
            ->join('stations', 'stations.station_id', '=', 'shipping_instructions.station_id')
            ->leftjoin('user_infos', 'user_infos.user_id', '=', 'shipping_instructions.user_id')
            ->leftJoin('shipments', 'shipments.shipping_id', '=', 'shipping_instructions.shipping_id')
            ->join('destinations', 'destinations.destination_id', '=', 'shipping_instructions.destination_id')
            ->leftJoin('clearing_agents', 'clearing_agents.agent_id', '=', 'shipping_instructions.clearing_agent')
            ->leftJoin('transporters', 'transporters.transporter_id', '=', 'shipping_instructions.transporter_id')
            ->leftJoin('drivers', 'drivers.driver_id', '=', 'shipping_instructions.driver_id')
            ->select('shipping_instructions.created_at', 'shipping_instructions.clearing_agent', 'client_name', 'shipping_number', 'port_name', 'load_type', 'container_size','consignee', 'shipping_instructions.status', 'seal_number', 'agent_name', 'transporter_name', 'surname', 'first_name', 'station_name', 'shipping_instructions.deleted_at')
            ->selectRaw('SUM(shipments.shipped_packages) as stl_packages, SUM(shipments.shipped_weight) as stl_weight')
            ->groupBy('shipping_instructions.created_at', 'shipping_instructions.clearing_agent', 'client_name', 'shipping_number', 'port_name', 'load_type', 'container_size','consignee', 'shipping_instructions.status', 'seal_number', 'agent_name', 'transporter_name', 'surname', 'first_name', 'station_name', 'deleted_at')
            ->latest('shipping_instructions.created_at');


        $client = $request->client;
        $station = $request->station;
        $from = $request->from;
        $to = $request->to;
        $status = $request->report;

//            return $request->all();

        if (!is_null($from)) {
            $fromTimestamp = strtotime($from);
            $shippings->where('shipping_instructions.created_at', '>=', $from);
        }
        if (!is_null($to)) {
            $toTimestamp = strtotime($to);
            $shippings->where('shipping_instructions.created_at', '<=', $to);
        }
        if (!is_null($station)) {
            $shippings->where('shipping_instructions.station_id', $station);
        }

        if (!is_null($client)) {
            $shippings->where('shipping_instructions.client_id', $client);
        }

        if (!is_null($status)) {
            if ($status == 1) {
                $shippings->where('shipping_instructions.status', '<', 4) ;
            }else{
                $shippings->where('shipping_instructions.status', 4);
            }
        }

       $shipments = $shippings->get();

        return Excel::download(new ExportSTLReport($shipments), 'STL STATUS'.' '.time().'.xlsx', \Maatwebsite\Excel\Excel::XLSX);
    }
    public function downloadOutturReport($id)
    {

        $blendSheet = BlendSheet::join('destinations', 'destinations.destination_id', '=', 'blend_sheets.destination_id')
            ->join('blend_teas', 'blend_teas.blend_id', '=', 'blend_sheets.blend_id')
            ->select('blend_date', 'vessel_name', 'consignee', 'standard_details', 'port_name', 'blend_number', 'b_dust', 'c_dust', 'fibre', 'sweepings')
            ->where('blend_sheets.blend_id', $id)
            ->selectRaw('SUM(blend_teas.blended_packages) as input_packages')
            ->selectRaw('SUM(blend_teas.blended_weight) as input_weight')
            ->groupBy('blend_date', 'vessel_name', 'consignee', 'standard_details', 'port_name', 'blend_number', 'b_dust', 'c_dust', 'fibre', 'sweepings')
            ->whereNull('blend_teas.deleted_at')
            ->first();

        $blendSummaries = BlendShipment::where('blend_id', $id)->get();
        $blendBalances = BlendBalance::where('blend_id', $id)->get();

        $totalShipped = 0;
        $totalRemnant = 0;
        $totalShippedPackets = 0;

        $supervisors = BlendSupervision::where('blend_id', $id)->get();
        $materials = BlendMaterial::where('blend_id', $id)->get();

        if ($supervisors){
            $prepared = UserInfo::where('user_id', $supervisors[0]['compiled_by'])->first();
            $user = $prepared->first_name.' '.$prepared->surname;
        }else{
            $user = null;
        }

        $domPdfPath = base_path('vendor/dompdf/dompdf');
        \PhpOffice\PhpWord\Settings::setPdfRendererPath($domPdfPath);
        \PhpOffice\PhpWord\Settings::setPdfRendererName('DomPDF');

        $table = new Table(['unit' => \PhpOffice\PhpWord\SimpleType\TblWidth::TWIP, 'width' => 1400 * 1400, 'align' => 'center']);

        $header = ['size' => 8, 'name' => 'Cambria', 'space' => ['before' => 100, 'after' => 100], 'bold' => true];
        $text = ['size' => 8, 'align' => 'center' , 'name' => 'Cambria', 'space' => ['before' => 100, 'after' => 100], 'bold' => false];

        $table->addRow();
        $table->addCell(5000, ['gridSpan' => 2, 'borderSize' => 1])->addText('BLEND DATE', $header, ['space' => ['before' => 100, 'after' => 50]]);
        $table->addCell(6000, ['gridSpan' => 3, 'borderSize' => 1])->addText($blendSheet->blend_date, $text, ['align' => 'center' ,'space' => ['before' => 100, 'after' => 50]]);

        $table->addRow();
        $table->addCell(5000, ['gridSpan' => 2, 'borderSize' => 1])->addText('SHIPPER', $header, ['space' => ['before' => 100, 'after' => 50]]);
        $table->addCell(6000, ['gridSpan' => 3, 'borderSize' => 1])->addText($blendSheet->vessel_name, $text, ['align' => 'center' ,'space' => ['before' => 100, 'after' => 50]]);

        $table->addRow();
        $table->addCell(5000, ['gridSpan' => 2, 'borderSize' => 1])->addText('CONSIGNEE', $header, ['space' => ['before' => 100, 'after' => 50]]);
        $table->addCell(6000, ['gridSpan' => 3, 'borderSize' => 1])->addText($blendSheet->consignee, $text, ['align' => 'center' ,'space' => ['before' => 100, 'after' => 50]]);

        $table->addRow();
        $table->addCell(5000, ['gridSpan' => 2, 'borderSize' => 1])->addText('SI/BLEND NUMBER', $header, ['space' => ['before' => 100, 'after' => 50]]);
        $table->addCell(6000, ['gridSpan' => 3, 'borderSize' => 1])->addText($blendSheet->blend_number, $text, ['align' => 'center' ,'space' => ['before' => 100, 'after' => 50]]);

        $table->addRow();
        $table->addCell(5000, ['gridSpan' => 2, 'borderSize' => 1])->addText('DESTINATION', $header, ['space' => ['before' => 100, 'after' => 50]]);
        $table->addCell(6000, ['gridSpan' => 3, 'borderSize' => 1])->addText($blendSheet->port_name, $text, ['align' => 'center' ,'space' => ['before' => 100, 'after' => 50]]);

        if ($blendSheet->standard_details !== null){
            $table->addRow();
            $table->addCell(5000, ['gridSpan' => 2, 'borderSize' => 1])->addText('STANDARD DETAILS', $header, ['space' => ['before' => 100, 'after' => 50]]);
            $table->addCell(6000, ['gridSpan' => 3, 'borderSize' => 1])->addText($blendSheet->standard_details, $text, ['align' => 'center' ,'space' => ['before' => 100, 'after' => 50]]);
        }

        $table->addRow();
        $table->addCell(11000, ['gridSpan' => 5])->addText('BLEND SUMMARY', $header, ['size' => 12, 'align' => 'center', 'space' => ['before' => 150, 'after' => 150]]);

        $table->addRow();
        $table->addCell(5000, ['gridSpan' => 2, 'borderSize' => 1])->addText('ITEM', $header, ['align' => 'center', 'space' => ['before' => 100, 'after' => 50]]);
        $table->addCell(2000, ['gridSpan' => 1, 'borderSize' => 1])->addText('PACKAGES', $header, ['align' => 'center', 'space' => ['before' => 100, 'after' => 50]]);
        $table->addCell(2000, ['gridSpan' => 1, 'borderSize' => 1])->addText('UNIT WEIGHT', $header, ['align' => 'center', 'space' => ['before' => 100, 'after' => 50]]);
        $table->addCell(2000, ['gridSpan' => 1, 'borderSize' => 1])->addText('TOTAL WEIGHT', $header, ['align' => 'center', 'space' => ['before' => 100, 'after' => 50]]);

        $table->addRow();
        $table->addCell(5000, ['gridSpan' => 2, 'borderSize' => 1])->addText('TOTAL BLEND INPUT', $header, ['space' => ['before' => 100, 'after' => 50]]);
        $table->addCell(2000, ['gridSpan' => 1, 'borderSize' => 1])->addText($blendSheet->input_packages, $text, ['align' => 'center' ,'space' => ['before' => 100, 'after' => 50]]);
        $table->addCell(2000, ['gridSpan' => 1, 'borderSize' => 1])->addText(number_format(floatval($blendSheet->input_weight) / floatval($blendSheet->input_packages), 2), $text, ['align' => 'center' ,'space' => ['before' => 100, 'after' => 50]]);
        $table->addCell(2000, ['gridSpan' => 1, 'borderSize' => 1])->addText(number_format($blendSheet->input_weight, 2), $text, ['align' => 'center' ,'space' => ['before' => 100, 'after' => 50]]);

        foreach ($blendSummaries as $key => $summary){
            $table->addRow();
            $table->addCell(5000, ['gridSpan' => 2, 'borderSize' => 1])->addText('SHIPMENT TOTAL '.++$key, $header, ['space' => ['before' => 100, 'after' => 50]]);
            $table->addCell(2000, ['gridSpan' => 1, 'borderSize' => 1])->addText($summary->blended_packages, $text, ['align' => 'center' ,'space' => ['before' => 100, 'after' => 50]]);
            $table->addCell(2000, ['gridSpan' => 1, 'borderSize' => 1])->addText(number_format($summary->unit_weight, 2), $text, ['align' => 'center' ,'space' => ['before' => 100, 'after' => 50]]);
            $table->addCell(2000, ['gridSpan' => 1, 'borderSize' => 1])->addText(number_format($summary->net_weight, 2), $text, ['align' => 'center' ,'space' => ['before' => 100, 'after' => 50]]);

            $totalShipped += $summary->net_weight;
            $totalShippedPackets += $summary->blended_packages;
        }

        $variance = $totalShippedPackets * floatval($summary->weight_variance);
        $table->addRow();
        $table->addCell(5000, ['gridSpan' => 2, 'borderSize' => 1])->addText('TARE WEIGHT VARIANCE', $header, ['space' => ['before' => 100, 'after' => 50]]);
        $table->addCell(2000, ['gridSpan' => 1, 'borderSize' => 1])->addText($totalShippedPackets, $text, ['align' => 'center' ,'space' => ['before' => 100, 'after' => 50]]);
        $table->addCell(2000, ['gridSpan' => 1, 'borderSize' => 1])->addText(number_format($summary->weight_variance, 2), $text, ['align' => 'center' ,'space' => ['before' => 100, 'after' => 50]]);
        $table->addCell(2000, ['gridSpan' => 1, 'borderSize' => 1])->addText(number_format($variance, 2), $text, ['align' => 'center' ,'space' => ['before' => 100, 'after' => 50]]);

        foreach ($blendBalances as $key => $bBalance){
            $table->addRow();
            $table->addCell(5000, ['gridSpan' => 2, 'borderSize' => 1])->addText('BLEND REMNANT '.++$key, $header, ['space' => ['before' => 100, 'after' => 50]]);
            $table->addCell(2000, ['gridSpan' => 1, 'borderSize' => 1])->addText($bBalance->ex_packages, $text, ['align' => 'center' ,'space' => ['before' => 100, 'after' => 50]]);
            $table->addCell(2000, ['gridSpan' => 1, 'borderSize' => 1])->addText(number_format($bBalance->unit_weight, 2), $text, ['align' => 'center' ,'space' => ['before' => 100, 'after' => 50]]);
            $table->addCell(2000, ['gridSpan' => 1, 'borderSize' => 1])->addText(number_format($bBalance->net_weight, 2), $text, ['align' => 'center' ,'space' => ['before' => 100, 'after' => 50]]);

            $totalRemnant += $bBalance->net_weight;
        }

        $table->addRow();
        $table->addCell(5000, ['gridSpan' => 2, 'borderSize' => 1])->addText('SIEVED DUST ', $header, ['space' => ['before' => 100, 'after' => 50]]);
        $table->addCell(2000, ['gridSpan' => 1, 'borderSize' => 1])->addText(1, $text, ['align' => 'center' ,'space' => ['before' => 100, 'after' => 50]]);
        $table->addCell(2000, ['gridSpan' => 1, 'borderSize' => 1])->addText(number_format($blendSheet->b_dust, 2), $text, ['align' => 'center' ,'space' => ['before' => 100, 'after' => 50]]);
        $table->addCell(2000, ['gridSpan' => 1, 'borderSize' => 1])->addText(number_format($blendSheet->b_dust, 2), $text, ['align' => 'center' ,'space' => ['before' => 100, 'after' => 50]]);

        $table->addRow();
        $table->addCell(5000, ['gridSpan' => 2, 'borderSize' => 1])->addText('CYCLONE/DUST ', $header, ['space' => ['before' => 100, 'after' => 50]]);
        $table->addCell(2000, ['gridSpan' => 1, 'borderSize' => 1])->addText(1, $text, ['align' => 'center' ,'space' => ['before' => 100, 'after' => 50]]);
        $table->addCell(2000, ['gridSpan' => 1, 'borderSize' => 1])->addText(number_format($blendSheet->c_dust, 2), $text, ['align' => 'center' ,'space' => ['before' => 100, 'after' => 50]]);
        $table->addCell(2000, ['gridSpan' => 1, 'borderSize' => 1])->addText(number_format($blendSheet->c_dust, 2), $text, ['align' => 'center' ,'space' => ['before' => 100, 'after' => 50]]);

        $table->addRow();
        $table->addCell(5000, ['gridSpan' => 2, 'borderSize' => 1])->addText('FIBRE ', $header, ['space' => ['before' => 100, 'after' => 50]]);
        $table->addCell(2000, ['gridSpan' => 1, 'borderSize' => 1])->addText(1, $text, ['align' => 'center' ,'space' => ['before' => 100, 'after' => 50]]);
        $table->addCell(2000, ['gridSpan' => 1, 'borderSize' => 1])->addText(number_format($blendSheet->fibre, 2), $text, ['align' => 'center' ,'space' => ['before' => 100, 'after' => 50]]);
        $table->addCell(2000, ['gridSpan' => 1, 'borderSize' => 1])->addText(number_format($blendSheet->fibre, 2), $text, ['align' => 'center' ,'space' => ['before' => 100, 'after' => 50]]);

        $table->addRow();
        $table->addCell(5000, ['gridSpan' => 2, 'borderSize' => 1])->addText('SWEEPINGS ', $header, ['space' => ['before' => 100, 'after' => 50]]);
        $table->addCell(2000, ['gridSpan' => 1, 'borderSize' => 1])->addText(1, $text, ['align' => 'center' ,'space' => ['before' => 100, 'after' => 50]]);
        $table->addCell(2000, ['gridSpan' => 1, 'borderSize' => 1])->addText(number_format($blendSheet->sweepings, 2), $text, ['align' => 'center' ,'space' => ['before' => 100, 'after' => 50]]);
        $table->addCell(2000, ['gridSpan' => 1, 'borderSize' => 1])->addText(number_format($blendSheet->sweepings, 2), $text, ['align' => 'center' ,'space' => ['before' => 100, 'after' => 50]]);

        $totalOutput =  floatval($totalRemnant) + floatval($totalShipped) + floatval($blendSheet->sweepings) + floatval($blendSheet->fibre) + floatval($blendSheet->c_dust) + floatval($blendSheet->b_dust) + floatval($variance);

        $table->addRow();
        $table->addCell(5000, ['gridSpan' => 4, 'borderSize' => 1])->addText('TOTAL BLEND OUTPUT ', $header, ['space' => ['before' => 100, 'after' => 50]]);
        $table->addCell(2000, ['gridSpan' => 1, 'borderSize' => 1])->addText(number_format($totalOutput, 2), $header, ['bold' => true, 'align' => 'center' ,'space' => ['before' => 100, 'after' => 50]]);

        $table->addRow();
        $table->addCell(5000, ['gridSpan' => 4, 'borderSize' => 1])->addText('BLEND GAIN/LOSS', $header, ['space' => ['before' => 100, 'after' => 50]]);
        $table->addCell(2000, ['gridSpan' => 1, 'borderSize' => 1])->addText(number_format($totalOutput - $blendSheet->input_weight, 2), $header, ['bold' => true, 'align' => 'center' ,'space' => ['before' => 100, 'after' => 50]]);

        $table->addRow();
        $table->addCell(10000, ['gridSpan' => 5])->addText('NEW MATERIALS ISSUED', $header, ['size' => 12, 'align' => 'center', 'space' => ['before' => 150, 'after' => 150]]);

        $table->addRow();
        $table->addCell(3000, ['gridSpan' => 1, 'borderSize' => 1])->addText('PAPER SACK', $header, ['align' => 'center', 'space' => ['before' => 100, 'after' => 50]]);
        $table->addCell(2000, ['gridSpan' => 1, 'borderSize' => 1])->addText('POLY BAG', $header, ['align' => 'center', 'space' => ['before' => 100, 'after' => 50]]);
        $table->addCell(2000, ['gridSpan' => 1, 'borderSize' => 1])->addText('SMALL POUCH', $header, ['align' => 'center', 'space' => ['before' => 100, 'after' => 50]]);
        $table->addCell(2000, ['gridSpan' => 1, 'borderSize' => 1])->addText('PALLETS', $header, ['align' => 'center', 'space' => ['before' => 100, 'after' => 50]]);
        $table->addCell(2000, ['gridSpan' => 1, 'borderSize' => 1])->addText('GUNNY BAGS', $header, ['align' => 'center', 'space' => ['before' => 100, 'after' => 50]]);

        $table->addRow();
        $table->addCell(3000, ['gridSpan' => 1, 'borderSize' => 1])->addText($materials->where('condition', 1)->where('material_type', 1)->first() == null ? 0: $materials->where('condition', 1)->where('material_type', 1)->first()->total, $text, ['align' => 'center', 'space' => ['before' => 100, 'after' => 50]]);
        $table->addCell(2000, ['gridSpan' => 1, 'borderSize' => 1])->addText($materials->where('condition', 1)->where('material_type', 2)->first() == null ? 0: $materials->where('condition', 1)->where('material_type', 2)->first()->total, $text, ['align' => 'center', 'space' => ['before' => 100, 'after' => 50]]);
        $table->addCell(2000, ['gridSpan' => 1, 'borderSize' => 1])->addText($materials->where('condition', 1)->where('material_type', 3)->first() == null ? 0: $materials->where('condition', 1)->where('material_type', 3)->first()->total, $text, ['align' => 'center', 'space' => ['before' => 100, 'after' => 50]]);
        $table->addCell(2000, ['gridSpan' => 1, 'borderSize' => 1])->addText($materials->where('condition', 1)->where('material_type', 4)->first() == null ? 0: $materials->where('condition', 1)->where('material_type', 4)->first()->total, $text, ['align' => 'center', 'space' => ['before' => 100, 'after' => 50]]);
        $table->addCell(2000, ['gridSpan' => 1, 'borderSize' => 1])->addText($materials->where('condition', 1)->where('material_type', 5)->first() == null ? 0: $materials->where('condition', 1)->where('material_type', 5)->first()->total, $text, ['align' => 'center', 'space' => ['before' => 100, 'after' => 50]]);

        $table->addRow();
        $table->addCell(10000, ['gridSpan' => 5])->addText('RETRIEVALS', $header, ['size' => 12, 'align' => 'center', 'space' => ['before' => 150, 'after' => 150]]);

        $table->addRow();
        $table->addCell(3000, ['gridSpan' => 1, 'borderSize' => 1])->addText('ITEM', $header, ['align' => 'center', 'space' => ['before' => 100, 'after' => 50]]);
        $table->addCell(2000, ['gridSpan' => 1, 'borderSize' => 1])->addText('PAPER SACK', $header, ['align' => 'center', 'space' => ['before' => 100, 'after' => 50]]);
        $table->addCell(2000, ['gridSpan' => 1, 'borderSize' => 1])->addText('POLY BAG', $header, ['align' => 'center', 'space' => ['before' => 100, 'after' => 50]]);
        $table->addCell(2000, ['gridSpan' => 1, 'borderSize' => 1])->addText('PALLETS', $header, ['align' => 'center', 'space' => ['before' => 100, 'after' => 50]]);
        $table->addCell(2000, ['gridSpan' => 1, 'borderSize' => 1])->addText('GUNNY BAGS', $header, ['align' => 'center', 'space' => ['before' => 100, 'after' => 50]]);

        $table->addRow();
        $table->addCell(3000, ['gridSpan' => 1, 'borderSize' => 1])->addText('USED MATERIAL RETRIEVALS', $header, ['align' => 'center', 'space' => ['before' => 100, 'after' => 50]]);
        $table->addCell(2000, ['gridSpan' => 1, 'borderSize' => 1])->addText($materials->where('condition', 2)->where('material_type', 1)->first() == null ? 0: $materials->where('condition', 2)->where('material_type', 1)->first()->total, $text, ['align' => 'center', 'space' => ['before' => 100, 'after' => 50]]);
        $table->addCell(2000, ['gridSpan' => 1, 'borderSize' => 1])->addText($materials->where('condition', 2)->where('material_type', 2)->first() == null ? 0: $materials->where('condition', 2)->where('material_type', 2)->first()->total, $text, ['align' => 'center', 'space' => ['before' => 100, 'after' => 50]]);
        $table->addCell(2000, ['gridSpan' => 1, 'borderSize' => 1])->addText($materials->where('condition', 2)->where('material_type', 3)->first() == null ? 0: $materials->where('condition', 2)->where('material_type', 3)->first()->total, $text, ['align' => 'center', 'space' => ['before' => 100, 'after' => 50]]);
        $table->addCell(2000, ['gridSpan' => 1, 'borderSize' => 1])->addText($materials->where('condition', 2)->where('material_type', 4)->first() == null ? 0: $materials->where('condition', 2)->where('material_type', 4)->first()->total, $text, ['align' => 'center', 'space' => ['before' => 100, 'after' => 50]]);

        $table->addRow();
        $table->addCell(3000, ['gridSpan' => 1, 'borderSize' => 1])->addText('IN USE/DAMAGED MATERIALS', $header, ['align' => 'center', 'space' => ['before' => 100, 'after' => 50]]);
        $table->addCell(2000, ['gridSpan' => 1, 'borderSize' => 1])->addText($materials->where('condition', 3)->where('material_type', 1)->first() == null ? 0: $materials->where('condition', 3)->where('material_type', 1)->first()->total, $text, ['align' => 'center', 'space' => ['before' => 100, 'after' => 50]]);
        $table->addCell(2000, ['gridSpan' => 1, 'borderSize' => 1])->addText($materials->where('condition', 3)->where('material_type', 2)->first() == null ? 0: $materials->where('condition', 3)->where('material_type', 2)->first()->total, $text, ['align' => 'center', 'space' => ['before' => 100, 'after' => 50]]);
        $table->addCell(2000, ['gridSpan' => 1, 'borderSize' => 1])->addText($materials->where('condition', 3)->where('material_type', 3)->first() == null ? 0: $materials->where('condition', 3)->where('material_type', 3)->first()->total, $text, ['align' => 'center', 'space' => ['before' => 100, 'after' => 50]]);
        $table->addCell(2000, ['gridSpan' => 1, 'borderSize' => 1])->addText($materials->where('condition', 3)->where('material_type', 4)->first() == null ? 0: $materials->where('condition', 3)->where('material_type', 4)->first()->total, $text, ['align' => 'center', 'space' => ['before' => 100, 'after' => 50]]);


        $ps = floatval($materials->where('condition', 2)->where('material_type', 1)->first()->total) - floatval($materials->where('condition', 3)->where('material_type', 1)->first()->total);
        $pb = floatval($materials->where('condition', 2)->where('material_type', 2)->first()->total) - floatval($materials->where('condition', 3)->where('material_type', 2)->first()->total);
        $sp = floatval($materials->where('condition', 2)->where('material_type', 3)->first()->total) - floatval($materials->where('condition', 3)->where('material_type', 3)->first()->total);
        $gu = floatval($materials->where('condition', 2)->where('material_type', 4)->first()->total) - floatval($materials->where('condition', 3)->where('material_type', 4)->first()->total);

        $table->addRow();
        $table->addCell(3000, ['gridSpan' => 1, 'borderSize' => 1])->addText('NET USED MATERIALS', $header, ['align' => 'center', 'space' => ['before' => 100, 'after' => 50]]);
        $table->addCell(2000, ['gridSpan' => 1, 'borderSize' => 1])->addText($ps, $text, ['align' => 'center', 'space' => ['before' => 100, 'after' => 50]]);
        $table->addCell(2000, ['gridSpan' => 1, 'borderSize' => 1])->addText($pb, $text, ['align' => 'center', 'space' => ['before' => 100, 'after' => 50]]);
        $table->addCell(2000, ['gridSpan' => 1, 'borderSize' => 1])->addText($sp, $text, ['align' => 'center', 'space' => ['before' => 100, 'after' => 50]]);
        $table->addCell(2000, ['gridSpan' => 1, 'borderSize' => 1])->addText($gu, $text, ['align' => 'center', 'space' => ['before' => 100, 'after' => 50]]);

        $table->addRow();
        $table->addCell(11000, ['gridSpan' => 5])->addText('OFFICERS INVOLVED', $header, ['size' => 12, 'align' => 'center', 'space' => ['before' => 150, 'after' => 150]]);

        $table->addRow();
        $table->addCell(2000, ['gridSpan' => 1, 'borderSize' => 1])->addText('COMPILED BY', $header, ['align' => 'center', 'space' => ['before' => 100, 'after' => 50]]);
        $table->addCell(2000, ['gridSpan' => 1, 'borderSize' => 1])->addText('MACHINE OPERATOR', $header, ['align' => 'center', 'space' => ['before' => 100, 'after' => 50]]);
        $table->addCell(2000, ['gridSpan' => 1, 'borderSize' => 1])->addText('BLEND SUPERVISOR', $header, ['align' => 'center', 'space' => ['before' => 100, 'after' => 50]]);
        $table->addCell(5000, ['gridSpan' => 2, 'borderSize' => 1])->addText('3rd PARTY INSPECTION CLERK', $header, ['align' => 'center', 'space' => ['before' => 100, 'after' => 50]]);

        $table->addRow();
        $table->addCell(2000, ['gridSpan' => 1, 'borderSize' => 1])->addText($user == null ? '' : $user, $text, ['align' => 'center', 'space' => ['before' => 100, 'after' => 50]]);
        $table->addCell(2000, ['gridSpan' => 1, 'borderSize' => 1])->addText($supervisors->where('supervisor_type', 1)->first() == null ? '' :$supervisors->where('supervisor_type', 1)->first()['supervisor_name'], $text, ['align' => 'center', 'space' => ['before' => 100, 'after' => 50]]);
        $table->addCell(2000, ['gridSpan' => 1, 'borderSize' => 1])->addText($supervisors->where('supervisor_type', 2)->first() == null ? '' : $supervisors->where('supervisor_type', 2)->first()['supervisor_name'], $text, ['align' => 'center', 'space' => ['before' => 100, 'after' => 50]]);
        $table->addCell(2000, ['gridSpan' => 2, 'borderSize' => 1])->addText($supervisors->where('supervisor_type', 3)->first() == null ? '' : $supervisors->where('supervisor_type', 3)->first()['supervisor_name'], $text, ['align' => 'center', 'space' => ['before' => 100, 'after' => 50]]);

        $table->addRow();
        $table->addCell(2000, ['gridSpan' => 1, 'borderSize' => 1])->addText('', $text, ['align' => 'center', 'space' => ['before' => 100, 'after' => 50]]);
        $table->addCell(2000, ['gridSpan' => 1, 'borderSize' => 1])->addText('', $text, ['align' => 'center', 'space' => ['before' => 100, 'after' => 50]]);
        $table->addCell(2000, ['gridSpan' => 1, 'borderSize' => 1])->addText('', $text, ['align' => 'center', 'space' => ['before' => 100, 'after' => 50]]);
        $table->addCell(2000, ['gridSpan' => 2, 'borderSize' => 1])->addText('', $text, ['align' => 'center', 'space' => ['before' => 100, 'after' => 50]]);

        $localli = new TemplateProcessor(storage_path('blend_outturn_report_template.docx'));
        $localli->setComplexBlock('table', $table);
        $localli->setValue('date', date('D, d/m/y h:i:s'));
        $localli->setValue('by', auth()->user()->user->first_name.' '.auth()->user()->user->surname);
        $docPath = 'Files/TempFiles/'.str_replace(['.', '/', ''], '', $blendSheet->blend_number). '.docx';
        $localli->saveAs($docPath);

//         return response()->download($docPath)->deleteFileAfterSend(true);

        $phpWord = IOFactory::load($docPath);
        $contents = \PhpOffice\PhpWord\IOFactory::load($docPath);
        $pdfPath = 'Files/TempFiles/'.str_replace(['.', '/', ''], '', $blendSheet->blend_number). ".pdf";
        $converter =  new OfficeConverter($docPath, 'Files/TempFiles/');
        $converter->convertTo(str_replace(['.', '/', ''], '', $blendSheet->blend_number). ".pdf");
        unlink($docPath);
        return response()->download($pdfPath)->deleteFileAfterSend();

    }

    public function exportTransportReport(Request $request)
    {
        $from = $request->from;
        $to = $request->to;
        $report = $request->report == 1 ? 'COLLECTION' :($request->report == 2 ? 'TRANSFER' : null);
        $transporter = $request->transporter;
        $query = DB::table('transportreport');

        if (!is_null($from)) {
            $fromTimestamp = strtotime($from);
            $query->where('date_received', '>=', $fromTimestamp);
        }
        if (!is_null($to)) {
            $toTimestamp = strtotime($to);
            $query->where('date_received', '<=', $toTimestamp);
        }
        if (!is_null($transporter)){
            $query->where('transporter_id', $transporter);
        }
        if (!is_null($report)){
            $query->where('delivery_type', $report);
        }
       $orders = $query->orderBy('date_received', 'desc')->get();

        ini_set('memory_limit', '10000M');
        ini_set('max_execution_time', 30000);

        return Excel::download(new ExportTeaTransport($orders), 'TRANSPORTERS'.' '.time().'.xlsx', \Maatwebsite\Excel\Excel::XLSX);

    }

    public function exportInterTransferReport(Request $request)
    {
        $client = $request->input('name');
        $from = $request->input('monthAgo');
        $to = $request->input('todayDate');

        $query = Transfers::join('stations', 'stations.station_id', '=', 'transfers.station_id')
            ->join('stations as destination_station', 'destination_station.station_id', '=', 'transfers.destination')
            ->join('delivery_orders', 'delivery_orders.delivery_id', '=', 'transfers.delivery_id')
            ->join('grades', 'grades.grade_id', '=', 'delivery_orders.grade_id')
            ->join('gardens', 'gardens.garden_id', '=', 'delivery_orders.garden_id')
            ->join('clients', 'clients.client_id', '=', 'delivery_orders.client_id')
            ->join('stock_ins', function($join) {
                $join->on('stock_ins.delivery_id', '=', 'transfers.delivery_id')
                    ->on('stock_ins.stock_id', '=', 'transfers.stock_id');
            })
            ->leftJoin('transporters', 'transporters.transporter_id', '=', 'transfers.transporter_id')
            ->leftJoin('drivers', 'drivers.driver_id', '=', 'transfers.driver_id')
            ->select('stations.station_name', 'clients.client_name', 'gardens.garden_name', 'grades.grade_name', 'requested_palettes', 'requested_weight', 'destination', 'destination_station.station_name as destination_name', 'transfers.status', 'stock_ins.total_pallets', 'stock_ins.net_weight', 'transporters.transporter_id', 'transporters.transporter_name', 'drivers.id_number', 'drivers.driver_name', 'drivers.phone as driver_phone', 'transfers.registration', 'delivery_orders.invoice_number', 'transfers.delivery_number', 'stock_ins.date_received', 'transfers.updated_at as received', 'transfers.created_at as created')
            ->latest('transfers.created_at');

        if (!is_null($client)) {
            $query->where('delivery_orders.client_id', $client);
        }

        if (!is_null($from)) {
            $query->where('stock_ins.date_received', '>=', strtotime($from));
        }

        if (!is_null($to)) {
            $query->where('stock_ins.date_received', '<=', strtotime($to));
        }

        $results = $query->get();

        return Excel::download(new ExportInternalTransfers($results), 'INTERNAL TRANSFERS'.' '.time().'.xlsx', \Maatwebsite\Excel\Excel::XLSX);
    }

    public function exportExterTransferReport(Request $request)
    {
        $client = $request->input('name');
        $from = $request->input('monthAgo');
        $to = $request->input('todayDate');

        $query = ExternalTransfer::join('currentstock', 'currentstock.stock_id', '=', 'external_transfers.stock_id')
            ->leftJoin('drivers', 'drivers.driver_id', '=', 'external_transfers.driver_id')
            ->leftJoin('warehouses', 'warehouses.warehouse_id', '=', 'external_transfers.warehouse_id')
            ->leftJoin('transporters', 'transporters.transporter_id', '=', 'external_transfers.transporter_id')
            ->select('currentstock.client_name', 'currentstock.garden_name', 'currentstock.grade_name', 'currentstock.stocked_at', 'external_transfers.registration', 'external_transfers.status as extStatus', 'external_transfers.transferred_palettes', 'external_transfers.transferred_weight', 'transporters.transporter_name', 'transporters.transporter_id', 'drivers.driver_name', 'drivers.id_number', 'drivers.phone', 'warehouses.warehouse_name', 'external_transfers.created_at as sortOrder', 'currentstock.invoice_number', 'external_transfers.delivery_number', 'currentstock.lot_number', 'currentstock.sale_number', 'currentstock.created_at as received')
            ->orderBy('extStatus', 'asc')
            ->orderBy('sortOrder', 'desc');

        if (!is_null($client)) {
            $query->where('currentstock.client_id', $client);
        }

        if (!is_null($from)) {
            $query->where('currentstock.date_received', '>=', strtotime($from));
        }

        if (!is_null($to)) {
            $query->where('currentstock.date_received', '<=', strtotime($to));
        }

        $results = $query->get();

        return Excel::download(new ExportExternalTransfer($results), 'EXTERNAL TRANSFERS'.' '.time().'.xlsx', \Maatwebsite\Excel\Excel::XLSX);
    }

    public function deleteInStock($id){
        StockIn::where('stock_id', $id)->delete();
        return back()->with('success', 'Tea removed from stock successfully');
    }

    public function downloadBlendBalances(Request $request){
        $blendBalances = DB::table('blendBalances')->where('current_weight', '>', 0);

        if (!is_null($request->client)) {
            $blendBalances->where('client_id', $request->client);
        }

        if (!is_null($request->from)) {
            $blendBalances->where('blend_date', '>=', $request->from);
        }

        if (!is_null($request->to)) {
            $blendBalances->where('blend_date', '<=', $request->to);
        }

        $balances = $blendBalances->get();

        $date = date('D, d-m-Y, h:i:s');
        $printed = auth()->user()->user;
        $by = $printed->first_name.' '.$printed->surname;

        $domPdfPath = base_path('vendor/dompdf/dompdf');
        \PhpOffice\PhpWord\Settings::setPdfRendererPath($domPdfPath);
        \PhpOffice\PhpWord\Settings::setPdfRendererName('DomPDF');

        $headers = ['size' => 8, 'name' => 'New Times Roman', 'bold' => true, 'space' => ['after' => 50, 'before' => 100]];
        $text = ['size' => 7, 'name' => 'New Times Roman', 'bold' => false, 'space' => ['after' => 40, 'before' => 100]];

        $table = new Table(['unit' => \PhpOffice\PhpWord\SimpleType\TblWidth::TWIP, 'width' => 1400 * 1400, 'align' => 'center']);
        $table->addRow();
        $table->addCell(600, ['borderSize' => 1])->addText('#', $headers, ['space' => ['before' => 100]]);
        $table->addCell(1800, ['borderSize' => 1])->addText('BLEND NUMBER', $headers, ['space' => ['before' => 50]]);
        $table->addCell(2600, ['borderSize' => 1])->addText('CLIENT NAME', $headers, ['space' => ['before' => 50]]);
        $table->addCell(2300, ['borderSize' => 1])->addText('GARDEN NAME', $headers, ['space' => ['before' => 50]]);
        $table->addCell(1800, ['borderSize' => 1])->addText('GRADE NAME', $headers, ['space' => ['before' => 50]]);
        $table->addCell(1200, ['borderSize' => 1])->addText('PACKAGES', $headers, ['space' => ['before' => 50]]);
        $table->addCell(1200, ['borderSize' => 1])->addText('WEIGHT', $headers, ['space' => ['before' => 50]]);
        $table->addCell(2000, ['borderSize' => 1])->addText("BLEND DATE", $headers, ['space' => ['before' => 50]]);
        $table->addCell(1800, ['borderSize' => 1])->addText('WAREHOUSE', $headers, ['space' => ['before' => 50]]);

        $totalPackets = 0;
        $totalWeight = 0;
        foreach ($balances as $key => $stock){
            $table->addRow();
            $table->addCell(600, ['borderSize' => 1])->addText(++$key, $text, ['space' => ['before' => 50]]);
            $table->addCell(1800, ['borderSize' => 1])->addText($stock->blend_number, $text, ['space' => ['before' => 50]]);
            $table->addCell(2000, ['borderSize' => 1])->addText($stock->client_name, $text, ['space' => ['before' => 50]]);
            $table->addCell(1800, ['borderSize' => 1])->addText($stock->garden, $text, ['space' => ['before' => 50]]);
            $table->addCell(1800, ['borderSize' => 1])->addText($stock->grade, $text, ['space' => ['before' => 50]]);
            $table->addCell(1200, ['borderSize' => 1])->addText(number_format($stock->current_packages, 2), $text, ['space' => ['before' => 50]]);
            $table->addCell(1200, ['borderSize' => 1])->addText(number_format($stock->current_weight, 2), $text, ['space' => ['before' => 50]]);
            $table->addCell(2000, ['borderSize' => 1])->addText($stock->blend_date, $text, ['space' => ['before' => 50]]);
            $table->addCell(1800, ['borderSize' => 1])->addText($stock->station_name, $text, ['space' => ['before' => 50]]);
            $totalPackets += $stock->current_packages;
            $totalWeight += $stock->current_weight;
        }

        $table->addRow();
        $table->addCell(7700, ['gridSpan' => 5])->addText();
        $table->addCell(900, ['borderSize' => 1])->addText(number_format($totalPackets, 2), $headers, ['space' => ['before' => 50]]);
        $table->addCell(1000, ['borderSize' => 1])->addText(number_format($totalWeight, 2), $headers, ['space' => ['before' => 50]]);
        $table->addCell(5000, ['gridSpan' => 2])->addText();


        $stock = new TemplateProcessor(storage_path('blend_stock_template.docx'));
        $stock->setComplexBlock('{table}', $table);
        $stock->setValue('date', $date);
        $stock->setValue('by', $by);
        $docPath = 'Files/TempFiles/BLEND BALANCES '.time().'.docx';
        $stock->saveAs($docPath);

        //  //return response()->download($docPath)->deleteFileAfterSend();


        $phpWord = IOFactory::load($docPath);
        $contents = \PhpOffice\PhpWord\IOFactory::load($docPath);
        $pdfPath = 'Files/TempFiles/BLEND BALANCES'.time(). ".pdf";
        $converter =  new OfficeConverter($docPath, 'Files/TempFiles/');
        $converter->convertTo('BLEND BALANCES'.time().".pdf");
        unlink($docPath);
        return response()->download($pdfPath)->deleteFileAfterSend(true);

    }

    public function collectionStatus($id)
    {
        $orders = DeliveryOrder::join('users as u', 'u.user_id', '=', 'delivery_orders.created_by')
            ->join('gardens as g', 'g.garden_id', '=', 'delivery_orders.garden_id')
            ->join('grades as gr', 'gr.grade_id', '=', 'delivery_orders.grade_id')
            ->join('brokers as br', 'br.broker_id', '=', 'delivery_orders.broker_id')
            ->join('warehouses as wh', 'wh.warehouse_id', '=', 'delivery_orders.warehouse_id')
            ->leftJoin('sub_warehouses as sub', 'sub.sub_warehouse_id', '=', 'delivery_orders.sub_warehouse_id')
            ->join('clients as cl', 'cl.client_id', '=', 'delivery_orders.client_id')
            ->leftJoin('loading_instructions as li', function ($join) {
                $join->on('li.delivery_id', '=', 'delivery_orders.delivery_id')
                    ->whereNull('li.deleted_at');
            })
            ->leftJoin('drivers as dr', 'dr.driver_id', '=', 'li.driver_id')
            ->leftJoin('transporters as tr', 'tr.transporter_id', '=', 'li.transporter_id')
            ->leftJoin('stations as st', 'st.station_id', '=', 'li.station_id')
            ->leftJoin('users as lu', 'lu.user_id', '=', 'li.created_by')
            ->select('u.username', 'g.garden_name', 'gr.grade_name', 'br.broker_name', 'wh.warehouse_name', 'wh.warehouse_id', 'cl.client_name', 'delivery_orders.*', 'tr.transporter_id', 'tr.transporter_name', 'dr.driver_id', 'dr.driver_name', 'dr.id_number', 'dr.phone', 'li.loading_id', 'li.loading_number', 'li.status as load_status', 'li.registration', 'li.created_by as load_user_id', 'lu.username as load_user', 'st.station_name', 'st.station_id', 'sub.sub_warehouse_name', 'li.deleted_at', 'delivery_orders.created_at as date_received')
            ->whereNull('delivery_orders.deleted_at')
            ->orderBy('delivery_orders.created_at', 'desc');

        // Clone the query builder instance for each variable
        $uncollected = clone $orders;
        $late = clone $orders;
        $noTCI = clone $orders;
        $overstayed = clone $orders;

        $uncollected = $uncollected->where('li.status', 1)->get();
        $threshold = Carbon::now();
        $late = $late->whereRaw("DATE_ADD(li.created_at, INTERVAL 2 DAY) <= '$threshold'")->where('li.status', 1)->get();
        $noTCI = $noTCI->whereNull('li.loading_number')->get();
        $now = \Carbon\Carbon::now();
        $overstayed = $overstayed->whereRaw("DATE_ADD(delivery_orders.prompt_date, INTERVAL 7 DAY) <= '$now'")->where('li.status', 1)->get();

        $data = $id == 1 ? $uncollected : ($id == 2 ? $late : ($id == 3 ? $noTCI : $overstayed ));
        $file = $id == 1 ? 'UNDER-COLLECTION' : ($id == 2 ? 'LATE-COLLECTION' : ($id == 3 ? 'NO-TCI' : 'OVERSTAYED' ));

        return Excel::download(new ExportDeliveryOrders($data), $file.time().'.xlsx', \Maatwebsite\Excel\Excel::XLSX);

    }

    public function transferReport($id)
    {
        $internal = Transfers::join('stations', 'stations.station_id', '=', 'transfers.station_id')
            ->join('stations as destination_station', 'destination_station.station_id', '=', 'transfers.destination')
            ->leftJoin('transporters', 'transporters.transporter_id', '=', 'transfers.transporter_id')
            ->leftJoin('drivers', 'drivers.driver_id', '=', 'transfers.driver_id')
            ->join('delivery_orders', 'delivery_orders.delivery_id', '=', 'transfers.delivery_id')
            ->join('clients', 'clients.client_id', '=', 'delivery_orders.client_id')
            ->join('currentstock', function($join) {
                $join->on('currentstock.delivery_id', '=', 'transfers.delivery_id')
                    ->on('currentstock.stock_id', '=', 'transfers.stock_id');
            })
            ->select('transfers.created_at', 'stations.station_name', 'clients.client_name', 'transfers.requested_palettes', 'transfers.requested_weight', 'destination', 'destination_station.station_name as destination_name', 'transfers.status', 'currentstock.current_stock', 'currentstock.current_weight', 'transfers.delivery_number', 'currentstock.lot_number', 'currentstock.sale_number', 'currentstock.invoice_number', 'garden_name', 'grade_name', 'transporters.transporter_name', 'transfers.registration', 'drivers.id_number', 'drivers.driver_name')
            ->where('transfers.status', '<', 3)
            ->orWhere('transfers.status', null)
            ->whereNull('transfers.deleted_at')
            ->latest('transfers.created_at')
            ->get();
//            ->groupBy('delivery_number');

        $external = ExternalTransfer::join('currentstock', 'currentstock.stock_id', '=', 'external_transfers.stock_id')
            ->leftJoin('warehouses', 'warehouses.warehouse_id', '=', 'external_transfers.warehouse_id')
            ->select('external_transfers.created_at', 'currentstock.client_name', 'external_transfers.status as extStatus', 'external_transfers.transferred_palettes', 'external_transfers.transferred_weight', 'warehouses.warehouse_name', 'external_transfers.delivery_number', 'currentstock.stocked_at', 'garden_name', 'grade_name', 'invoice_number', 'external_transfers.registration', 'driver_name', 'transporter_name', 'id_number')
            ->latest('external_transfers.created_at')
            ->where('external_transfers.status', '<', 3)
            ->orWhere('external_transfers.status', null)
            ->whereNull('external_transfers.deleted_at')
            ->orderBy('delivery_number', 'desc')
            ->get();
//            ->groupBy('delivery_number');

        $si = ShippingInstruction::join('clients', 'clients.client_id', '=', 'shipping_instructions.client_id')
            ->join('destinations', 'destinations.destination_id', '=', 'shipping_instructions.destination_id')
            ->leftJoin('clearing_agents', 'clearing_agents.agent_id', '=', 'shipping_instructions.clearing_agent')
            ->leftJoin('transporters', 'transporters.transporter_id', '=', 'shipping_instructions.transporter_id')
            ->select('shipping_instructions.created_at', 'clients.client_name', 'shipping_instructions.clearing_agent', 'shipping_number', 'vessel_name', 'port_name', 'load_type', 'container_size', 'shipping_mark', 'consignee', 'shipping_instructions.status', 'shipping_instructions', 'escort', 'seal_number', 'agent_name', 'ship_date', 'container_number', 'container_tare')
            ->latest('shipping_instructions.created_at')
            ->where('shipping_instructions.status', '<', 4)
            ->orWhere('shipping_instructions.status', null)
            ->whereNull('shipping_instructions.deleted_at')
            ->get();

        $blend = DB::table('blend_sheets')
            ->join('clients', 'clients.client_id', '=', 'blend_sheets.client_id')
            ->join('destinations', 'destinations.destination_id', '=', 'blend_sheets.destination_id')
            ->leftJoin('blend_teas', 'blend_teas.blend_id', '=', 'blend_sheets.blend_id')
            ->leftJoin('drivers', 'drivers.driver_id', '=', 'blend_sheets.driver_id')
            ->leftJoin('clearing_agents', 'clearing_agents.agent_id', '=', 'blend_sheets.agent_id')
            ->leftJoin('transporters', 'transporters.transporter_id', '=', 'blend_sheets.transporter_id')
            ->leftJoin('stations', 'stations.station_id', '=', 'blend_sheets.station_id')
            ->select('blend_sheets.created_at', 'blend_sheets.blend_id', 'blend_sheets.client_id', 'client_name', 'clients.phone as cPhone', 'email', 'blend_number', 'vessel_name', 'blend_sheets.destination_id', 'port_name', 'shipping_mark', 'consignee', 'contract', 'grade', 'garden', 'blend_date', 'blend_sheets.status', 'container_size', 'clients.address', 'package_type', 'registration', 'transporter_name', 'transporters.transporter_id', 'driver_name', 'drivers.phone as driver_phone', 'container_tare', 'blend_shipped', 'agent_name', 'seal_number', 'escort', 'output_packages', 'output_weight', 'blend_sheets.packet_tare', 'blend_sheets.agent_id', 'id_number', 'stations.station_id', 'stations.station_name', 'standard_details')
            ->selectRaw('SUM(blend_teas.blended_packages) as input_packages')
            ->selectRaw('SUM(blend_teas.blended_weight) as input_weight')
            ->groupBy('created_at', 'blend_sheets.blend_id', 'blend_sheets.client_id', 'client_name', 'clients.phone', 'email', 'blend_number', 'vessel_name', 'blend_sheets.destination_id', 'port_name', 'shipping_mark', 'consignee', 'contract', 'grade', 'garden', 'blend_date', 'blend_sheets.status', 'container_size', 'clients.address', 'package_type', 'registration', 'transporter_name', 'driver_name', 'driver_phone', 'container_tare', 'blend_shipped', 'agent_name', 'seal_number', 'escort', 'output_packages', 'output_weight', 'packet_tare', 'agent_id', 'transporter_id', 'id_number', 'station_id', 'station_name', 'standard_details')
            ->where('blend_sheets.status', '<', 4)
            ->orWhere('blend_sheets.status', null)
            ->whereNull('blend_teas.deleted_at')
            ->whereNull('blend_sheets.deleted_at')
            ->latest('blend_sheets.created_at')
            ->get();

        if ($id == 5){
            return Excel::download(new ExportInternalTransfers($internal), 'PENDING INTERNAL TRANSFERS'.' '.time().'.xlsx', \Maatwebsite\Excel\Excel::XLSX);
        }elseif ($id == 6){
            return Excel::download(new ExportExternalTransfer($external), 'EXTERNAL TRANSFERS'.' '.time().'.xlsx', \Maatwebsite\Excel\Excel::XLSX);
        }elseif ($id == 7){
            return Excel::download(new ExportShippingInstructions($si), 'SHIPPING INSTRUCTION'.' '.time().'.xlsx', \Maatwebsite\Excel\Excel::XLSX);
        }elseif($id == 8){
            return Excel::download(new ExportBlendSheet($blend), 'BLEND SHEETS'.' '.time().'.xlsx', \Maatwebsite\Excel\Excel::XLSX);
        }
    }

    public function viewOurLocations()
    {
        $locations = WarehouseLocation::latest()->get();
        return view('admin::warehouses.locations')->with('locations', $locations);

    }

    public function registerLocation(Request $request)
    {
        $request->validate([
            'location' => 'string|required'
        ]);

        $location = [
            'location_id' =>  (new CustomIds())->generateId(),
            'location_name' => $request->location,
            'location_address' => $request->address,
            'status' => 1
        ];

        WarehouseLocation::create($location);
        $this->logger->create();
        return back()->with('success', 'Location added successfully');

    }

    public function updateLocation (Request $request, $id)
    {
        $request->validate([
            'location' => [
                'required',
                'string',
                Rule::unique('warehouse_locations', 'location_name')->ignore($id, 'location_id'),
            ],
        ]);

        $location = [
            'location_name' => $request->location,
            'location_address' => $request->address,
            'status' => 1
        ];

        WarehouseLocation::where('location_id', $id)->update($location);
        $this->logger->create();
        return back()->with('success', 'Location updated successfully');

    }

    /*public function manageStock (){
        // Assuming 'tea_id' is the column to check against in all tables

        $stockInTeas = StockIn::join('delivery_orders', 'delivery_orders.delivery_id', '=', 'stock_ins.delivery_id')
            ->join('clients', 'clients.client_id', '=', 'delivery_orders.client_id')
            ->join('gardens', 'gardens.garden_id', '=', 'delivery_orders.garden_id')
            ->join('grades', 'grades.grade_id', '=', 'delivery_orders.grade_id')
            ->join('user_infos', 'user_infos.user_id', '=', 'stock_ins.user_id');

        $transferredTeas = Transfers::select('stock_id')->pluck('stock_id')->toArray();
        $externalTransferredTeas = ExternalTransfer::select('stock_id')->pluck('stock_id')->toArray();
        $blendedTeas = BlendTea::select('stock_id')->pluck('stock_id')->toArray();
        $shippedTeas = Shipment::select('stock_id')->pluck('stock_id')->toArray();

        $excludedTeas = array_merge($transferredTeas, $externalTransferredTeas, $blendedTeas, $shippedTeas);

        $teas = $stockInTeas->whereNull('delivery_orders.deleted_at')
            ->whereNotIn('stock_id', $excludedTeas)
            ->select('delivery_type', 'stock_id', 'client_name', 'garden_name', 'grade_name', 'invoice_number', 'total_pallets', 'net_weight', 'surname', 'first_name', 'order_number', 'lot_number')
            ->orderBy('client_name', 'asc')
            ->orderBy('stock_ins.created_at', 'desc')
            ->get();

        return view('admin::stock.manageStock')->with(['teas' => $teas]);

    }*/

    /*public function deleteMultipleTeas(Request $request){
        $toDelete = StockIn::whereIn('stock_id', $request->stockId);
        DeliveryOrder::whereIn('delivery_id', $toDelete->pluck('delivery_id')->toArray())->delete();
        $toDelete->delete();
        return back()->with('success','Success! Selected teas deleted successfully');
    }*/

    public function deleteTea($id){
        StockIn::where('stock_id', $id)->delete();
        return back()->with('success','Success! Selected tea deleted successfully');
    }

    public function withdrawSample($id)
    {
        $stock = DB::table('currentstock')->where('stock_id', $id)->first();
        return view('admin::stock.withdrawSample')->with('data', $stock);
    }

    public function storeSampleRequest(Request $request, $id)
    {
        $stock = DB::table('currentstock')->where('stock_id', $id)->first();
        $newWeight = $stock->current_weight/$stock->current_stock;
        if ($newWeight - floatval($request->sample_weight) >= 0){
            DB::beginTransaction();
            try {

                $sample = [
                    'sample_id' => (new CustomIds())->generateId(),
                    'delivery_id' => $stock->delivery_id,
                    'stock_id' => $stock->stock_id,
                    'sample_weight' => $request->sample_weight,
                    'sample_palletes' => 1,
                    'package_weight' => number_format($newWeight, 2),
                    'status' => 1,
                    'user_id' => auth()->user()->user_id
                ];

                TeaSamples::create($sample);

                $newStock = [
                    'stock_id' => (new CustomIds())->generateId(),
                    'delivery_id' => $stock->delivery_id,
                    'station_id' => $stock->station_id,
                    'date_received' => time(),
                    'delivery_number' => 'SP'.time(),
                    'warehouse_bay' => $stock->warehouse_bay,
                    'total_weight' => number_format(floatval($newWeight )- floatval($request->sample_weight), 2),
                    'total_pallets' => 1,
                    'pallet_weight' => 0,
                    'package_tare' => 0,
                    'net_weight' => number_format(floatval($newWeight )- floatval($request->sample_weight), 2),
                    'user_id' => auth()->user()->user_id
                ];

                StockIn::create($newStock);

                $this->logger->create();
                DB::commit();
                return redirect()->route('admin.teaSamplesRequest')->with('success', 'Success! Sample request created successfully');
            } catch (\Exception $e) {
                // Rollback the transaction if an exception occurs
                DB::rollback();
                // Handle or log the exception
                return redirect()->back()->with('error', 'Oops! An error occurred please try again');
            }
        }else{
            return back()->with('error', 'Oops! Sample weight cannot be more than weight of one bag of tea. Try again');
        }
    }

    public function teaSamplesRequest()
    {
        $samples = TeaSamples::join('delivery_orders', 'delivery_orders.delivery_id', '=', 'tea_samples.delivery_id')
            ->join('grades', 'grades.grade_id', '=', 'delivery_orders.grade_id')
            ->join('gardens', 'gardens.garden_id', '=', 'delivery_orders.garden_id')
            ->join('clients', 'clients.client_id', '=', 'delivery_orders.client_id')
            ->select('invoice_number', 'lot_number', 'grade_name', 'garden_name', 'client_name', 'sample_weight')
            ->get();
        return view('admin::stock.teaSamples')->with('samples', $samples);
    }

    public function allArchivedTeas()
    {
    //    $stocks = StockIn::onlyTrashed()
    //        ->join('delivery_orders', 'delivery_orders.delivery_id', 'stock_ins.delivery_id')
    //        ->join('clients', 'clients.client_id', 'delivery_orders.client_id')
    //        ->join('gardens', 'gardens.garden_id', '=', 'delivery_orders.garden_id')
    //        ->join('grades', 'grades.grade_id', 'delivery_orders.grade_id')
    //        ->select('stock_id', 'client_name', 'order_number', 'invoice_number', 'lot_number', 'garden_name', 'grade_name', 'total_pallets', 'total_weight', 'delivery_orders.delivery_type')
    //        ->get();

            $stocks = StockIn::onlyTrashed()
                    ->join('delivery_orders', 'delivery_orders.delivery_id', '=', 'stock_ins.delivery_id')
                    ->join('clients', 'clients.client_id', '=', 'delivery_orders.client_id')
                    ->join('gardens', 'gardens.garden_id', '=', 'delivery_orders.garden_id')
                    ->join('grades', 'grades.grade_id', '=', 'delivery_orders.grade_id')
                    ->orWhere(function($query) {
                        $query->whereNotNull('stock_ins.deleted_at')
                            ->orWhereNotNull('delivery_orders.deleted_at');
                    })
                    ->where('delivery_orders.status', 2)
                    ->select(
                        'stock_id',
                        'client_name',
                        'order_number',
                        'invoice_number',
                        'lot_number',
                        'garden_name',
                        'grade_name',
                        'total_pallets',
                        'total_weight',
                        'delivery_orders.delivery_type'
                    )
                    ->get();

       return view('admin::stock.archivedTeas')->with(['stocks' => $stocks]);
    }

    public function restoreArchivedTea($id)
    {
        StockIn::withTrashed()->where('stock_id', $id)->restore();
        DeliveryOrder::withTrashed()->where('delivery_id', StockIn::where('stock_id', $id)->first()->delivery_id)->restore();
        if(LoadingInstruction::withTrashed()->where('delivery_id', StockIn::where('stock_id', $id)->first()->delivery_id)->first()){
        LoadingInstruction::withTrashed()->where('delivery_id', StockIn::where('stock_id', $id)->first()->delivery_id)->first()->restore();
        }
        return redirect()->route('admin.editStock', $id)->with('success', 'Success! Tea restored Proceed to update');
    }

    public function viewReportRequest ()
    {
        $requests = ReportRequest::leftJoin('clients', 'clients.client_id', '=', 'report_requests.client_id')->select('report_requests.*', 'clients.client_name')->orderBy('service_number', 'desc')->get();
        $clients = Client::latest()->get();

        return view('admin::reports.index')->with(['requests' => $requests, 'clients' => $clients]);

    }

    public function filterReports(Request $request)
    {
        if($request->typeReport == 1){
            $data = DB::table('currentstock')
                ->where('current_stock', '>', 0)
                ->where('current_weight', '>', 0)
                ->where(['client_id' => $request->idClient])
                ->orderBy('delivery_number', 'asc')
                ->get()
                ->groupBy('delivery_number');

        }elseif ($request->typeReport == 2){
            $data = DB::table('blendBalances')
                ->where('current_packages', '>', 0)
                ->where('current_weight', '>', 0)
                ->where(['client_id' => $request->idClient])
                ->orderBy('blend_number', 'asc')
                ->get()
                ->groupBy('blend_number');

        }elseif ($request->typeReport == 3){
            $data = ShippingInstruction::where(['client_id' => $request->idClient])->orderBy('shipping_number', 'asc')->get()->groupBy('shipping_number');

        }elseif ($request->typeReport == 4){
            $data = BlendSheet::where(['client_id' => $request->idClient])->orderBy('blend_number', 'asc')->get()->groupBy('blend_number');

        }elseif ($request->typeReport == 5){
            $data = ExternalTransfer::join('delivery_orders', 'delivery_orders.delivery_id', '=', 'external_transfers.delivery_id')
                ->where(['client_id' => $request->idClient])
                ->orderBy('delivery_number', 'asc')
                ->get()
                ->groupBy('delivery_number');
        }elseif ($request->typeReport == 6){
            $collection = DeliveryOrder::where(['client_id' => $request->idClient])->orderBy('invoice_number', 'asc')->get()->groupBy('invoice_number');
        }

        return response()->json($data);
    }

    public function storeReport(Request $request)
    {
        $serviceId = ReportRequest::serviceId();

        $reportRequest = [
            'request_id' => (new CustomIds())->generateId(),
            'service_number' => $serviceId,
            'request_type' => $request->request_type,
            'client_id' => $request->client_id,
            'request_number' => $request->request_number,
            'date_from' => $request->date_from == null ? null : $request->date_from,
            'date_to' => $request->date_to == null ? Carbon::today() : $request->date_to,
            'priority' => $request->priority,
            'user_id' => auth()->user()->user_id
        ];

        ReportRequest::create($reportRequest);
        $this->logger->create();
        return redirect()->back()->with('success', 'Success! Report request has been sent');

    }

    public function approveReportRequest($id)
    {
        ReportRequest::where(['request_id' => $id])->update(['status' => 1, 'approved_by' => auth()->user()->user_id]);
        return redirect()->back()->with('success', 'Success! Report request has been approved');
    }

    public function downloadReportRequest ($id)
    {
       $request = ReportRequest::find($id);
        $image = $request->service_number.'.png';
        ini_set('memory_limit', '10000M');
        ini_set('max_execution_time', 30000);

        //   return strtotime($request->date_to);

        if($request->request_type === 1){
            $data = DB::table('currentstock')
                ->where('current_stock', '>', 0)
                ->where('current_weight', '>', 0)
                ->orderBy('garden_name', 'asc');

            if ($request->request_number !== null){
                $data->where('delivery_number', $request->request_number);
            }

            if ($request->date_from !== null){
                $data->where('stock_date', '>=', $request->date_from);
            }

            if ($request->date_to !== null){
                $data->where('stock_date', '<=', $request->date_to);
            }

            if ($request->client_id !== null){
                $data->where('client_id', $request->client_id);
            }

            $reports = $data->get()->groupBy('client_name');

            $date = date('D, d-m-Y, h:i:s');
            $printed = auth()->user()->user;
            $by = $printed->first_name.' '.$printed->surname;

            if ($request->date_from == null){
                $period = 'FULL STATEMENT UPTO '.$request->date_to;
            }else{
                $period = 'FOR PERIOD BETWEEN '.$request->date_from.' AND '.$request->date_to;
            }

            $domPdfPath = base_path('vendor/dompdf/dompdf');
            \PhpOffice\PhpWord\Settings::setPdfRendererPath($domPdfPath);
            \PhpOffice\PhpWord\Settings::setPdfRendererName('DomPDF');

            $headers = ['size' => 8, 'name' => 'New Times Roman', 'bold' => true, 'space' => ['after' => 50, 'before' => 100]];
            $text = ['size' => 7, 'name' => 'New Times Roman', 'bold' => false, 'space' => ['after' => 40, 'before' => 100]];

            $table = new Table(['unit' => \PhpOffice\PhpWord\SimpleType\TblWidth::PERCENT, 'width' => 100 * 50, 'align' => Jc::CENTER]);
            foreach($reports as $clientName => $results){

            $table->addRow();
            $table->addCell(null, ['gridSpan' => 12])->addText($clientName.'\'s ACCOUNT', $headers, ['space' => ['before' => 100]]);

            $table->addRow();
            $table->addCell(500, ['borderSize' => 1])->addText('#', $headers, ['space' => ['before' => 100]]);
            $table->addCell(1100, ['borderSize' => 1])->addText('Garden', $headers, ['space' => ['before' => 100]]);
            $table->addCell(1000, ['borderSize' => 1])->addText('Invoice #', $headers, ['space' => ['before' => 100]]);
            $table->addCell(750, ['borderSize' => 1])->addText('Grade', $headers, ['space' => ['before' => 100]]);
            $table->addCell(800, ['borderSize' => 1])->addText('Sale #', $headers, ['space' => ['before' => 100]]);
            $table->addCell(800, ['borderSize' => 1])->addText('Lot #', $headers, ['space' => ['before' => 100]]);

            $table->addCell(1000, ['borderSize' => 1])->addText('Pkgs', $headers, ['space' => ['before' => 100]]);
            $table->addCell(1200, ['borderSize' => 1])->addText('Net Weight', $headers, ['space' => ['before' => 100]]);

            $table->addCell(900, ['borderSize' => 1])->addText('TCI #', $headers, ['space' => ['before' => 100]]);
            $table->addCell(900, ['borderSize' => 1])->addText('DO #', $headers, ['space' => ['before' => 100]]);


            $table->addCell(900, ['borderSize' => 1])->addText("Date Rec'd", $headers, ['space' => ['before' => 100]]);
            $table->addCell(2000, ['borderSize' => 1])->addText('Recieved From', $headers, ['space' => ['before' => 100]]);

            $totalPackets = 0;
            $totalWeight = 0;
            $grossWeight = 0;
            foreach ($results as $key => $stock){

                $table->addRow();
                $table->addCell(500, ['borderSize' => 1])->addText(++$key, $text, ['space' => ['before' => 100]]);
                $table->addCell(1100, ['borderSize' => 1])->addText($stock->garden_name, $text, ['space' => ['before' => 100]]);
                $table->addCell(1000, ['borderSize' => 1])->addText($stock->invoice_number, $text, ['setNoWrap' => true, 'space' => ['before' => 100]]);
                $table->addCell(750, ['borderSize' => 1])->addText($stock->grade_name, $text, ['space' => ['before' => 100]]);
                $table->addCell(800, ['borderSize' => 1])->addText($stock->sale_number, $text, ['space' => ['before' => 100]]);
                $table->addCell(800, ['borderSize' => 1])->addText($stock->lot_number, $text, ['space' => ['before' => 100]]);

                $table->addCell(1000, ['borderSize' => 1])->addText(number_format($stock->current_stock, 2), $text, ['space' => ['before' => 100]]);
                $table->addCell(1200, ['borderSize' => 1])->addText(number_format($stock->current_weight, 2), $text, ['space' => ['before' => 100]]);

                $table->addCell(900, ['borderSize' => 1])->addText($stock->loading_number, $text, ['space' => ['before' => 100]]);
                $table->addCell(900, ['borderSize' => 1])->addText($stock->order_number, $text, ['space' => ['before' => 100]]);

                $table->addCell(900, ['borderSize' => 1])->addText(\Carbon\Carbon::createFromTimestamp($stock->date_received)->format('d-m-y'), $text, ['space' => ['before' => 100]]);
                $table->addCell(2000, ['borderSize' => 1])->addText($stock->warehouse_name, $text, ['space' => ['before' => 100]]);
                $totalPackets += $stock->current_stock;
                $totalWeight += $stock->current_weight;
                $grossWeight += floatval($stock->current_weight + ($stock->package_tare * $stock->current_stock + $stock->pallet_weight));
            }

            $table->addRow();
            $table->addCell(4950, ['gridSpan' => 6])->addText();
            $table->addCell(1000, ['borderSize' => 1])->addText(number_format($totalPackets, 2), $headers, ['space' => ['before' => 100]]);
            $table->addCell(1200, ['borderSize' => 1])->addText(number_format($totalWeight, 2), $headers, ['space' => ['before' => 100]]);
//              $table->addCell(1200, ['borderSize' => 1])->addText(number_format($grossWeight, 2), $headers, ['space' => ['before' => 100]]);
            $table->addCell(4700, ['gridSpan' => 4])->addText();
        }

            if ($request->approved_by !== null){
                $approved = UserInfo::find($request->approved_by);
                \QrCode::size(300)
                    ->format('png')
                    ->generate('REQUEST NUMBER: ' . $request->service_number . "\n" .
                        'REPORT TYPE: ' . 'CURRENT STOCK POSITION' . "\n" .
                        'CLIENT NAME: ' . 'ALL CLIENTS' . "\n" .
                        'REPORTING: ' . $period . "\n" .
                        'TOTAL PACKAGES: ' . number_format($totalPackets, 2). "\n" .
                        'TOTAL NET WEIGHT: ' . number_format($totalWeight, 2). "\n" .
                        'REPORT APPROVED BY: ' . $approved->first_name.' '.$approved->surname . "\n",
                        'Files/QrCodes/'.$image);
            }

            $stock = new TemplateProcessor(storage_path('verified_stock_template.docx'));
            $stock->setComplexBlock('{table}', $table);
            if ($request->approved_by !== null) {
                $stock->setImageValue('qr', array('path' => 'Files/QrCodes/' . $image, 'width' => 100, 'height' => 100, 'ratio' => true));
            }else{
                $stock->setValue('qr','NOT APPROVED');
            }
            $stock->setValue('date', $date);
            $stock->setValue('by', $by);
            $stock->setValue('period', $period);
            $docPath = 'Files/TempFiles/REPORT '.$request->service_number.'.docx';
            $stock->saveAs($docPath);

            if (file_exists('Files/QrCodes/' . $image)){
                unlink('Files/QrCodes/' . $image);
            }

//              return response()->download($docPath)->deleteFileAfterSend(true);
            $phpWord = IOFactory::load($docPath);
            $contents = \PhpOffice\PhpWord\IOFactory::load($docPath);
            $pdfPath = 'Files/TempFiles/REPORT '.$request->service_number. ".pdf";
            $converter =  new OfficeConverter($docPath, 'Files/TempFiles/');
            $converter->convertTo('REPORT '.$request->service_number.".pdf");
            unlink($docPath);
            return response()->download($pdfPath)->deleteFileAfterSend(true);

        }elseif ($request->request_type == 2){

            $data = DB::table('blendBalances')
                ->where('current_packages', '>', 0)
                ->where('current_weight', '>', 0)
                ->orderBy('garden', 'asc');

            if ($request->request_number !== null){
                $data->where('blend_number', $request->request_number);
            }

            if ($request->date_from !== null){
                $data->where('blend_date', '>=', $request->date_from);
            }

            if ($request->date_to !== null){
                $data->where('blend_date', '<=', $request->date_to);
            }

            if ($request->client_id !== null){
                $data->where('client_id', $request->client_id);
            }

            $reports = $data->get()->groupBy('client_name');

            $date = date('D, d-m-Y, h:i:s');
            $printed = auth()->user()->user;
            $by = $printed->first_name.' '.$printed->surname;

            if ($request->date_from == null){
                $period = 'FULL STATEMENT UPTO '.$request->date_to;
            }else{
                $period = 'FOR PERIOD BETWEEN '.$request->date_from.' AND '.$request->date_to;
            }

            $domPdfPath = base_path('vendor/dompdf/dompdf');
            \PhpOffice\PhpWord\Settings::setPdfRendererPath($domPdfPath);
            \PhpOffice\PhpWord\Settings::setPdfRendererName('DomPDF');

            $headers = ['size' => 8, 'name' => 'New Times Roman', 'bold' => true, 'space' => ['after' => 50, 'before' => 100]];
            $text = ['size' => 7, 'name' => 'New Times Roman', 'bold' => false, 'space' => ['after' => 40, 'before' => 100]];

            $table = new Table(['unit' => \PhpOffice\PhpWord\SimpleType\TblWidth::PERCENT, 'width' => 100 * 50, 'align' => Jc::CENTER]);
            foreach($reports as $clientName => $results){
            $table->addRow();
            $table->addCell(600, ['gridSpan' => 7])->addText($clientName.'\'s ACCOUNT', $headers, ['space' => ['before' => 100]]);

            $table->addRow();
            $table->addCell(600, ['borderSize' => 1])->addText('#', $headers, ['space' => ['before' => 100]]);
            $table->addCell(1200, ['borderSize' => 1])->addText('Blend Number', $headers, ['space' => ['before' => 100]]);
            $table->addCell(1300, ['borderSize' => 1])->addText('Garden', $headers, ['space' => ['before' => 100]]);
            $table->addCell(750, ['borderSize' => 1])->addText('Grade', $headers, ['space' => ['before' => 100]]);

            $table->addCell(1000, ['borderSize' => 1])->addText('Packages', $headers, ['space' => ['before' => 100]]);
            $table->addCell(1200, ['borderSize' => 1])->addText('Net Weight', $headers, ['space' => ['before' => 100]]);
            $table->addCell(1200, ['borderSize' => 1])->addText('Blend Date', $headers, ['space' => ['before' => 100]]);

            $totalPackets = 0;
            $totalWeight = 0;
            foreach ($results as $key => $stock){
                $table->addRow();
                $table->addCell(600, ['borderSize' => 1])->addText(++$key, $text, ['space' => ['before' => 100]]);
                $table->addCell(1500, ['borderSize' => 1])->addText($stock->blend_number, $text, ['setNoWrap' => true, 'space' => ['before' => 100]]);
                $table->addCell(1500, ['borderSize' => 1])->addText($stock->garden, $text, ['space' => ['before' => 100]]);
                $table->addCell(1000, ['borderSize' => 1])->addText($stock->grade, $text, ['space' => ['before' => 100]]);

                $table->addCell(1200, ['borderSize' => 1])->addText(number_format($stock->current_packages, 2), $text, ['space' => ['before' => 100]]);
                $table->addCell(1200, ['borderSize' => 1])->addText(number_format($stock->current_weight, 2), $text, ['space' => ['before' => 100]]);
                $table->addCell(2000, ['borderSize' => 1])->addText($stock->blend_date, $text, ['space' => ['before' => 100]]);
                $totalPackets += $stock->current_packages;
                $totalWeight += $stock->current_weight;
            }

            $table->addRow();
            $table->addCell(4650, ['gridSpan' => 4])->addText();
            $table->addCell(1200, ['borderSize' => 1])->addText(number_format($totalPackets, 2), $headers, ['space' => ['before' => 100]]);
            $table->addCell(1200, ['borderSize' => 1])->addText(number_format($totalWeight, 2), $headers, ['space' => ['before' => 100]]);
            $table->addCell(1200, ['gridSpan' => 1])->addText();

        }

            if ($request->approved_by !== null){
                $approved = UserInfo::find($request->approved_by);
                \QrCode::size(300)
                    ->format('png')
                    ->generate('REQUEST NUMBER: ' . $request->service_number . "\n" .
                        'REPORT TYPE: ' . 'CURRENT BLEND BALANCES POSITION' . "\n" .
                        'CLIENT NAME: ' . 'ALL CLIENTS' . "\n" .
                        'REPORTING: ' . $period . "\n" .
                        'TOTAL PACKAGES: ' . number_format($totalPackets, 2). "\n" .
                        'TOTAL NET WEIGHT: ' . number_format($totalWeight, 2). "\n" .
                        'REPORT APPROVED BY: ' . $approved->first_name.' '.$approved->surname . "\n",
                        'Files/QrCodes/'.$image);
            }

            $stock = new TemplateProcessor(storage_path('verified_blend_balance_template.docx'));
            $stock->setComplexBlock('{table}', $table);
            if ($request->approved_by !== null) {
                $stock->setImageValue('qr', array('path' => 'Files/QrCodes/' . $image, 'width' => 100, 'height' => 100, 'ratio' => true));
            }else{
                $stock->setValue('qr','NOT APPROVED');
            }
            $stock->setValue('date', $date);
            $stock->setValue('by', $by);
            $stock->setValue('period', $period);
            $stock->setValue('client_name', null);
            $docPath = 'Files/TempFiles/REPORT '.$request->service_number.'.docx';
            $stock->saveAs($docPath);

            if (file_exists('Files/QrCodes/' . $image)){
                unlink('Files/QrCodes/' . $image);
            }
//              return response()->download($docPath)->deleteFileAfterSend(true);
            $phpWord = IOFactory::load($docPath);
            $contents = \PhpOffice\PhpWord\IOFactory::load($docPath);
            $pdfPath = 'Files/TempFiles/REPORT '.$request->service_number. ".pdf";
            $converter =  new OfficeConverter($docPath, 'Files/TempFiles/');
            $converter->convertTo('REPORT '.$request->service_number.".pdf");
            unlink($docPath);
            return response()->download($pdfPath)->deleteFileAfterSend(true);

        }elseif ($request->request_type == 3){
            $data = ShippingInstruction::join('clients', 'clients.client_id', '=', 'shipping_instructions.client_id')
                ->join('clearing_agents', 'clearing_agents.agent_id', '=', 'shipping_instructions.clearing_agent')
                ->join('destinations', 'destinations.destination_id', '=', 'shipping_instructions.destination_id')
                ->join('shipments', 'shipments.shipping_id', '=', 'shipping_instructions.shipping_id')
                ->select('shipping_number', 'consignee', 'vessel_name', 'client_name', 'shipping_mark', 'container_number', 'ship_date', 'clients.client_name', 'clearing_agents.agent_name', 'destinations.port_name')
                ->selectRaw('SUM(shipments.shipped_packages) as packagesShipped')
                ->selectRaw('SUM(shipments.shipped_weight) as weightShipped')
                ->groupBy('shipping_number', 'consignee', 'vessel_name', 'client_name', 'shipping_mark', 'container_number', 'ship_date', 'agent_name', 'port_name')
                ->orderBy('shipping_number', 'asc');

            if ($request->request_number !== null){
                $data->where('shipping_number', $request->request_number);
            }

            if ($request->date_from !== null){
                $data->where('ship_date', '>=', strtotime($request->date_from));
            }

            if ($request->date_to !== null){
                $data->where('ship_date', '<=', strtotime($request->date_to));
            }

            if ($request->client_id !== null){
                $data->where('client_id', $request->client_id);
            }

            $reports = $data->get()->groupBy('client_name');

            $date = date('D, d-m-Y, h:i:s');
            $printed = auth()->user()->user;
            $by = $printed->first_name.' '.$printed->surname;

            if ($request->date_from == null){
                $period = 'FULL STATEMENT UPTO '.$request->date_to;
            }else{
                $period = 'FOR PERIOD BETWEEN '.$request->date_from.' AND '.$request->date_to;
            }

            $domPdfPath = base_path('vendor/dompdf/dompdf');
            \PhpOffice\PhpWord\Settings::setPdfRendererPath($domPdfPath);
            \PhpOffice\PhpWord\Settings::setPdfRendererName('DomPDF');

            $headers = ['size' => 8, 'name' => 'New Times Roman', 'bold' => true, 'space' => ['after' => 50, 'before' => 100]];
            $text = ['size' => 7, 'name' => 'New Times Roman', 'bold' => false, 'space' => ['after' => 40, 'before' => 100]];

            $table = new Table(['unit' => \PhpOffice\PhpWord\SimpleType\TblWidth::PERCENT, 'width' => 100 * 50, 'align' => Jc::CENTER]);
            foreach($reports as $clientName => $results){
                $table->addRow();
                $table->addCell(null, ['gridSpan' => 11])->addText($clientName.'\'s ACCOUNT', $headers, ['space' => ['before' => 100]]);

            $table->addRow();
            $table->addCell(500, ['borderSize' => 1])->addText('#', $headers, ['space' => ['before' => 100]]);
            $table->addCell(900, ['borderSize' => 1])->addText('SI Number', $headers, ['space' => ['before' => 100]]);
            $table->addCell(1500, ['borderSize' => 1])->addText('Shipping Agent', $headers, ['space' => ['before' => 100]]);
            $table->addCell(1000, ['borderSize' => 1])->addText('Destination', $headers, ['space' => ['before' => 100]]);
            $table->addCell(1500, ['borderSize' => 1])->addText('Consignee', $headers, ['space' => ['before' => 100]]);
            $table->addCell(1200, ['borderSize' => 1])->addText('Vessel Name', $headers, ['space' => ['before' => 100]]);
            $table->addCell(1200, ['borderSize' => 1])->addText('Shipping Mark', $headers, ['space' => ['before' => 100]]);
            $table->addCell(1000, ['borderSize' => 1])->addText('Container No.', $headers, ['space' => ['before' => 100]]);
            $table->addCell(900, ['borderSize' => 1])->addText('Total Pckgs', $headers, ['space' => ['before' => 100]]);
            $table->addCell(1100, ['borderSize' => 1])->addText('Total Weight', $headers, ['space' => ['before' => 100]]);
            $table->addCell(950, ['borderSize' => 1])->addText('Date Shipped', $headers, ['space' => ['before' => 100]]);

            $totalPackets = 0;
            $totalWeight = 0;
            foreach ($results as $key => $stock){
                $table->addRow();
                $table->addCell(500, ['borderSize' => 1])->addText(++$key, $text, ['space' => ['before' => 100]]);
                $table->addCell(900, ['borderSize' => 1])->addText($stock->shipping_number, $text, ['setNoWrap' => true, 'space' => ['before' => 100]]);
                $table->addCell(1500, ['borderSize' => 1])->addText($stock->agent_name, $text, ['space' => ['before' => 100]]);
                $table->addCell(1000, ['borderSize' => 1])->addText($stock->port_name, $text, ['space' => ['before' => 100]]);
                $table->addCell(1500, ['borderSize' => 1])->addText($stock->consignee, $text, ['space' => ['before' => 100]]);
                $table->addCell(1200, ['borderSize' => 1])->addText($stock->vessel_name, $text, ['space' => ['before' => 100]]);
                $table->addCell(1200, ['borderSize' => 1])->addText($stock->shipping_mark, $text, ['space' => ['before' => 100]]);
                $table->addCell(1000, ['borderSize' => 1])->addText($stock->container_number, $text, ['space' => ['before' => 100]]);

                $table->addCell(900, ['borderSize' => 1])->addText(number_format($stock->packagesShipped, 2), $text, ['space' => ['before' => 100]]);
                $table->addCell(1100, ['borderSize' => 1])->addText(number_format($stock->weightShipped, 2), $text, ['space' => ['before' => 100]]);
                $table->addCell(950, ['borderSize' => 1])->addText($stock->ship_date == null ? 'Pending' : Carbon::createFromTimestamp($stock->ship_date)->format('Y-m-d'), $text, ['space' => ['before' => 100]]);
                $totalPackets += $stock->packagesShipped;
                $totalWeight += $stock->weightShipped;
            }

            $table->addRow();
            $table->addCell(8800, ['gridSpan' => 8])->addText();
            $table->addCell(900, ['borderSize' => 1])->addText(number_format($totalPackets, 2), $headers, ['space' => ['before' => 100]]);
            $table->addCell(1100, ['borderSize' => 1])->addText(number_format($totalWeight, 2), $headers, ['space' => ['before' => 100]]);
            $table->addCell(950, ['gridSpan' => 1])->addText();
        }

            if ($request->approved_by !== null){
                $approved = UserInfo::find($request->approved_by);
                \QrCode::size(300)
                    ->format('png')
                    ->generate('REQUEST NUMBER: ' . $request->service_number . "\n" .
                        'REPORT TYPE: ' . 'STRAIGHT LINE REPORT' . "\n" .
                        'CLIENT NAME: ' . 'ALL CLIENTS '. "\n" .
                        'REPORTING: ' . $period . "\n" .
                        'TOTAL PACKAGES: ' . number_format($totalPackets, 2). "\n" .
                        'TOTAL NET WEIGHT: ' . number_format($totalWeight, 2). "\n" .
                        'REPORT APPROVED BY: ' . $approved->first_name.' '.$approved->surname . "\n",
                        'Files/QrCodes/'.$image);
            }

            $stock = new TemplateProcessor(storage_path('verified_shipping_instructions_template.docx'));
            $stock->setComplexBlock('{table}', $table);
            if ($request->approved_by !== null) {
                $stock->setImageValue('qr', array('path' => 'Files/QrCodes/' . $image, 'width' => 100, 'height' => 100, 'ratio' => true));
            }else{
                $stock->setValue('qr','NOT APPROVED');
            }
            $stock->setValue('date', $date);
            $stock->setValue('by', $by);
            $stock->setValue('period', $period);
            $stock->setValue('client_name', null);
            $docPath = 'Files/TempFiles/REPORT '.$request->service_number.'.docx';
            $stock->saveAs($docPath);

            if (file_exists('Files/QrCodes/' . $image)){
                unlink('Files/QrCodes/' . $image);
            }
//              return response()->download($docPath)->deleteFileAfterSend(true);
            $phpWord = IOFactory::load($docPath);
            $contents = \PhpOffice\PhpWord\IOFactory::load($docPath);
            $pdfPath = 'Files/TempFiles/REPORT '.$request->service_number. ".pdf";
            $converter =  new OfficeConverter($docPath, 'Files/TempFiles/');
            $converter->convertTo('REPORT '.$request->service_number.".pdf");
            unlink($docPath);
            return response()->download($pdfPath)->deleteFileAfterSend(true);

        }elseif ($request->request_type == 4){
            $data = BlendSheet::join('clients', 'clients.client_id', '=', 'blend_sheets.client_id')
                ->join('clearing_agents', 'clearing_agents.agent_id', '=', 'blend_sheets.agent_id')
                ->join('destinations', 'destinations.destination_id', '=', 'blend_sheets.destination_id')
                ->join('blend_teas', 'blend_teas.blend_id', '=', 'blend_sheets.blend_id')
                ->select('client_name', 'port_name', 'agent_name', 'blend_number', 'consignee', 'shipping_mark', 'vessel_name', 'garden', 'blend_shipped')
                ->selectRaw('SUM(blend_teas.blended_packages) as blendedPackages')
                ->selectRaw('SUM(blend_teas.blended_weight) as blendedWeight')
                ->groupBy('client_name', 'port_name', 'agent_name', 'blend_number', 'consignee', 'shipping_mark', 'vessel_name', 'garden', 'blend_shipped')
                // ->where(['blend_sheets.client_id' => $request->client_id])
                ->orderBy('blend_number', 'asc');

            if ($request->request_number !== null){
                $data->where('blend_number', $request->request_number);
            }

            if ($request->date_from !== null){
                $data->where('blend_shipped', '>=', strtotime($request->date_from));
            }

            if ($request->date_to !== null){
                $data->where('blend_shipped', '<=', strtotime($request->date_to));
            }

            if ($request->client_id !== null){
                $data->where('client_id', $request->client_id);
            }

            $reports = $data->get()->groupBy('client_name');

            $date = date('D, d-m-Y, h:i:s');
            $printed = auth()->user()->user;
            $by = $printed->first_name.' '.$printed->surname;

            if ($request->date_from == null){
                $period = 'FULL STATEMENT UPTO '.$request->date_to;
            }else{
                $period = 'FOR PERIOD BETWEEN '.$request->date_from.' AND '.$request->date_to;
            }

            $domPdfPath = base_path('vendor/dompdf/dompdf');
            \PhpOffice\PhpWord\Settings::setPdfRendererPath($domPdfPath);
            \PhpOffice\PhpWord\Settings::setPdfRendererName('DomPDF');

            $headers = ['size' => 8, 'name' => 'New Times Roman', 'bold' => true, 'space' => ['after' => 50, 'before' => 100]];
            $text = ['size' => 7, 'name' => 'New Times Roman', 'bold' => false, 'space' => ['after' => 40, 'before' => 100]];

            $table = new Table(['unit' => \PhpOffice\PhpWord\SimpleType\TblWidth::PERCENT, 'width' => 100 * 50, 'align' => Jc::CENTER]);

            foreach($reports as $clientName => $results){

            $table->addRow();
            $table->addCell(null, ['gridSpan' => 11])->addText($clientName. '\'s ACCOUNT', $headers, ['space' => ['before' => 100]]);

            $table->addRow();
            $table->addCell(500, ['borderSize' => 1])->addText('#', $headers, ['space' => ['before' => 100]]);
            $table->addCell(900, ['borderSize' => 1])->addText('SI Number', $headers, ['space' => ['before' => 100]]);
            $table->addCell(1100, ['borderSize' => 1])->addText('Garden', $headers, ['space' => ['before' => 100]]);
            $table->addCell(1500, ['borderSize' => 1])->addText('Shipping Agent', $headers, ['space' => ['before' => 100]]);
            $table->addCell(1000, ['borderSize' => 1])->addText('Destination', $headers, ['space' => ['before' => 100]]);
            $table->addCell(1500, ['borderSize' => 1])->addText('Consignee', $headers, ['space' => ['before' => 100]]);
            $table->addCell(1200, ['borderSize' => 1])->addText('Vessel Name', $headers, ['space' => ['before' => 100]]);
            $table->addCell(1000, ['borderSize' => 1])->addText('Shipping Mark', $headers, ['space' => ['before' => 100]]);
            $table->addCell(900, ['borderSize' => 1])->addText('Total Pckgs', $headers, ['space' => ['before' => 100]]);
            $table->addCell(1100, ['borderSize' => 1])->addText('Total Weight', $headers, ['space' => ['before' => 100]]);
            $table->addCell(950, ['borderSize' => 1])->addText('Date Shipped', $headers, ['space' => ['before' => 100]]);

            $totalPackets = 0;
            $totalWeight = 0;
            foreach ($results as $key => $stock){
                $table->addRow();
                $table->addCell(500, ['borderSize' => 1])->addText(++$key, $text, ['space' => ['before' => 100]]);
                $table->addCell(900, ['borderSize' => 1])->addText($stock->blend_number, $text, ['setNoWrap' => true, 'space' => ['before' => 100]]);
                $table->addCell(1100, ['borderSize' => 1])->addText($stock->garden, $text, ['space' => ['before' => 100]]);
                $table->addCell(1500, ['borderSize' => 1])->addText($stock->agent_name, $text, ['space' => ['before' => 100]]);
                $table->addCell(1000, ['borderSize' => 1])->addText($stock->port_name, $text, ['space' => ['before' => 100]]);
                $table->addCell(1500, ['borderSize' => 1])->addText($stock->consignee, $text, ['space' => ['before' => 100]]);
                $table->addCell(1200, ['borderSize' => 1])->addText($stock->vessel_name, $text, ['space' => ['before' => 100]]);
                $table->addCell(1000, ['borderSize' => 1])->addText($stock->shipping_mark, $text, ['space' => ['before' => 100]]);

                $table->addCell(900, ['borderSize' => 1])->addText(number_format($stock->blendedPackages, 2), $text, ['space' => ['before' => 100]]);
                $table->addCell(1100, ['borderSize' => 1])->addText(number_format($stock->blendedWeight, 2), $text, ['space' => ['before' => 100]]);
                $table->addCell(950, ['borderSize' => 1])->addText($stock->blend_shipped == null ? 'Pending' : Carbon::createFromTimestamp($stock->blend_shipped)->format('Y-m-d'), $text, ['space' => ['before' => 100]]);
                $totalPackets += $stock->blendedPackages;
                $totalWeight += $stock->blendedWeight;
            }

            $table->addRow();
            $table->addCell(8800, ['gridSpan' => 8])->addText();
            $table->addCell(900, ['borderSize' => 1])->addText(number_format($totalPackets, 2), $headers, ['space' => ['before' => 100]]);
            $table->addCell(1100, ['borderSize' => 1])->addText(number_format($totalWeight, 2), $headers, ['space' => ['before' => 100]]);
            $table->addCell(950, ['gridSpan' => 1])->addText();
        }

            if ($request->approved_by !== null){
                $approved = UserInfo::find($request->approved_by);
                \QrCode::size(300)
                    ->format('png')
                    ->generate('REQUEST NUMBER: ' . $request->service_number . "\n" .
                        'REPORT TYPE: ' . 'BLEND REPORT' . "\n" .
                        'CLIENT NAME: ' . 'ALL CLIENTS' . "\n" .
                        'REPORTING: ' . $period . "\n" .
                        'TOTAL PACKAGES: ' . number_format($totalPackets, 2). "\n" .
                        'TOTAL NET WEIGHT: ' . number_format($totalWeight, 2). "\n" .
                        'REPORT APPROVED BY: ' . $approved->first_name.' '.$approved->surname . "\n",
                        'Files/QrCodes/'.$image);
            }

            $stock = new TemplateProcessor(storage_path('verified_blend_sheets_template.docx'));
            $stock->setComplexBlock('{table}', $table);
            if ($request->approved_by !== null) {
                $stock->setImageValue('qr', array('path' => 'Files/QrCodes/' . $image, 'width' => 100, 'height' => 100, 'ratio' => true));
            }else{
                $stock->setValue('qr','NOT APPROVED');
            }
            $stock->setValue('date', $date);
            $stock->setValue('by', $by);
            $stock->setValue('period', $period);
            $stock->setValue('client_name', null);
            $docPath = 'Files/TempFiles/REPORT '.$request->service_number.'.docx';
            $stock->saveAs($docPath);

            if (file_exists('Files/QrCodes/' . $image)){
                unlink('Files/QrCodes/' . $image);
            }
//              return response()->download($docPath)->deleteFileAfterSend(true);
            $phpWord = IOFactory::load($docPath);
            $contents = \PhpOffice\PhpWord\IOFactory::load($docPath);
            $pdfPath = 'Files/TempFiles/REPORT '.$request->service_number. ".pdf";
            $converter =  new OfficeConverter($docPath, 'Files/TempFiles/');
            $converter->convertTo('REPORT '.$request->service_number.".pdf");
            unlink($docPath);
            return response()->download($pdfPath)->deleteFileAfterSend(true);

        }elseif ($request->request_type == 5){

            $data = ExternalTransfer::join('delivery_orders', 'delivery_orders.delivery_id', '=', 'external_transfers.delivery_id')
                ->join('clients', 'clients.client_id', '=', 'delivery_orders.client_id')
                ->join('warehouses', 'warehouses.warehouse_id', '=', 'external_transfers.warehouse_id')
                ->join('gardens', 'gardens.garden_id', '=', 'delivery_orders.garden_id')
                ->join('grades', 'grades.grade_id', '=', 'delivery_orders.grade_id')
                ->select('client_name', 'warehouse_name', 'garden_name', 'grade_name', 'invoice_number', 'lot_number', 'order_number', 'delivery_number', 'transferred_palettes', 'transferred_weight', 'external_transfers.updated_at')
                // ->where(['delivery_orders.client_id' => $request->client_id])
                ->orderBy('garden_name', 'asc');

            if ($request->request_number !== null){
                $data->where('delivery_number', $request->request_number);
            }

            if ($request->date_from !== null){
                $data->where('external_transfers.created_at', '>=', $request->date_from);
            }

            if ($request->date_to !== null){
                $data->where('external_transfers.created_at', '<=', $request->date_to);
            }

            if ($request->client_id !== null){
                $data->where('client_id', $request->client_id);
            }

            $reports = $data->get()->groupBy('client_name');

            $date = date('D, d-m-Y, h:i:s');
            $printed = auth()->user()->user;
            $by = $printed->first_name.' '.$printed->surname;

            if ($request->date_from == null){
                $period = 'FULL STATEMENT UPTO '.$request->date_to;
            }else{
                $period = 'FOR PERIOD BETWEEN '.$request->date_from.' AND '.$request->date_to;
            }

            $domPdfPath = base_path('vendor/dompdf/dompdf');
            \PhpOffice\PhpWord\Settings::setPdfRendererPath($domPdfPath);
            \PhpOffice\PhpWord\Settings::setPdfRendererName('DomPDF');

            $headers = ['size' => 8, 'name' => 'New Times Roman', 'bold' => true, 'space' => ['after' => 50, 'before' => 100]];
            $text = ['size' => 7, 'name' => 'New Times Roman', 'bold' => false, 'space' => ['after' => 40, 'before' => 100]];

            $table = new Table(['unit' => \PhpOffice\PhpWord\SimpleType\TblWidth::PERCENT, 'width' => 100 * 50, 'align' => Jc::CENTER]);
            foreach($reports as $clientName => $results){
            $table->addRow();
            $table->addCell(null, ['gridSpan' => 11])->addText($clientName. '\'s ACCOUNT', $headers, ['space' => ['before' => 100]]);

            $table->addRow();
            $table->addCell(500, ['borderSize' => 1])->addText('#', $headers, ['space' => ['before' => 100]]);
            $table->addCell(1200, ['borderSize' => 1])->addText('Delivery Number', $headers, ['space' => ['before' => 100]]);
            $table->addCell(1100, ['borderSize' => 1])->addText('Order Number', $headers, ['space' => ['before' => 100]]);
            $table->addCell(1100, ['borderSize' => 1])->addText('Invoice Number', $headers, ['space' => ['before' => 100]]);
            $table->addCell(1300, ['borderSize' => 1])->addText('Garden', $headers, ['space' => ['before' => 100]]);
            $table->addCell(1000, ['borderSize' => 1])->addText('Grade', $headers, ['space' => ['before' => 100]]);
            $table->addCell(1000, ['borderSize' => 1])->addText('Lot Number', $headers, ['space' => ['before' => 100]]);
            $table->addCell(900, ['borderSize' => 1])->addText('Total Pckgs', $headers, ['space' => ['before' => 100]]);
            $table->addCell(1100, ['borderSize' => 1])->addText('Total Weight', $headers, ['space' => ['before' => 100]]);
            $table->addCell(1500, ['borderSize' => 1])->addText('Destination Whs', $headers, ['space' => ['before' => 100]]);
            $table->addCell(1000, ['borderSize' => 1])->addText('Transfer Date', $headers, ['space' => ['before' => 100]]);

            $totalPackets = 0;
            $totalWeight = 0;
            foreach ($results as $key => $stock){
                $table->addRow();
                $table->addCell(500, ['borderSize' => 1])->addText(++$key, $text, ['space' => ['before' => 100]]);
                $table->addCell(1200, ['borderSize' => 1])->addText($stock->delivery_number, $text, ['setNoWrap' => true, 'space' => ['before' => 100]]);
                $table->addCell(1200, ['borderSize' => 1])->addText($stock->order_number, $text, ['space' => ['before' => 100]]);
                $table->addCell(1100, ['borderSize' => 1])->addText($stock->invoice_number, $text, ['space' => ['before' => 100]]);
                $table->addCell(1300, ['borderSize' => 1])->addText($stock->garden_name, $text, ['space' => ['before' => 100]]);
                $table->addCell(1000, ['borderSize' => 1])->addText($stock->grade_name, $text, ['space' => ['before' => 100]]);
                $table->addCell(1000, ['borderSize' => 1])->addText($stock->lot_number, $text, ['space' => ['before' => 100]]);

                $table->addCell(900, ['borderSize' => 1])->addText(number_format($stock->transferred_palettes, 2), $text, ['space' => ['before' => 100]]);
                $table->addCell(1100, ['borderSize' => 1])->addText(number_format($stock->transferred_weight, 2), $text, ['space' => ['before' => 100]]);
                $table->addCell(1500, ['borderSize' => 1])->addText($stock->warehouse_name, $text, ['space' => ['before' => 100]]);
                $table->addCell(1000, ['borderSize' => 1])->addText($stock->updated_at == null ? 'Pending' : Carbon::parse($stock->updated_at)->format('Y-m-d'), $text, ['space' => ['before' => 100]]);
                $totalPackets += $stock->transferred_palettes;
                $totalWeight += $stock->transferred_weight;
            }

            $table->addRow();
            $table->addCell(7200, ['gridSpan' => 7])->addText();
            $table->addCell(900, ['borderSize' => 1])->addText(number_format($totalPackets, 2), $headers, ['space' => ['before' => 100]]);
            $table->addCell(1100, ['borderSize' => 1])->addText(number_format($totalWeight, 2), $headers, ['space' => ['before' => 100]]);
            $table->addCell(2250, ['gridSpan' => 2])->addText();
        }

            if ($request->approved_by !== null){
                $approved = UserInfo::find($request->approved_by);
                \QrCode::size(300)
                    ->format('png')
                    ->generate('REQUEST NUMBER: ' . $request->service_number . "\n" .
                        'REPORT TYPE: ' . 'EXTERNAL TRANSFERS REPORT' . "\n" .
                        'CLIENT NAME: ' . 'ALL CLIENTS '. "\n" .
                        'REPORTING: ' . $period . "\n" .
                        'TOTAL PACKAGES: ' . number_format($totalPackets, 2). "\n" .
                        'TOTAL NET WEIGHT: ' . number_format($totalWeight, 2). "\n" .
                        'REPORT APPROVED BY: ' . $approved->first_name.' '.$approved->surname . "\n",
                        'Files/QrCodes/'.$image);
            }

            $stock = new TemplateProcessor(storage_path('verified_external_transfers_template.docx'));
            $stock->setComplexBlock('{table}', $table);
            if ($request->approved_by !== null) {
                $stock->setImageValue('qr', array('path' => 'Files/QrCodes/' . $image, 'width' => 100, 'height' => 100, 'ratio' => true));
            }else{
                $stock->setValue('qr','NOT APPROVED');
            }
            $stock->setValue('date', $date);
            $stock->setValue('by', $by);
            $stock->setValue('period', $period);
            $stock->setValue('client_name', null);
            $docPath = 'Files/TempFiles/REPORT '.$request->service_number.'.docx';
            $stock->saveAs($docPath);

            if (file_exists('Files/QrCodes/' . $image)){
                unlink('Files/QrCodes/' . $image);
            }
//              return response()->download($docPath)->deleteFileAfterSend(true);
            $phpWord = IOFactory::load($docPath);
            $contents = \PhpOffice\PhpWord\IOFactory::load($docPath);
            $pdfPath = 'Files/TempFiles/REPORT '.$request->service_number. ".pdf";
            $converter =  new OfficeConverter($docPath, 'Files/TempFiles/');
            $converter->convertTo('REPORT '.$request->service_number.".pdf");
            unlink($docPath);
            return response()->download($pdfPath)->deleteFileAfterSend(true);

        }elseif ($request->request_type == 6){

            $data = DeliveryOrder::join('clients', 'clients.client_id', '=', 'delivery_orders.client_id')
                ->leftJoin('warehouses', 'warehouses.warehouse_id', '=', 'delivery_orders.warehouse_id')
                ->join('gardens', 'gardens.garden_id', '=', 'delivery_orders.garden_id')
                ->join('grades', 'grades.grade_id', '=', 'delivery_orders.grade_id')
                ->select('client_name', 'sale_number', 'warehouse_name', 'garden_name', 'grade_name', 'delivery_orders.delivery_type','invoice_number', 'lot_number', 'order_number', 'packet', 'weight', 'delivery_orders.status', 'delivery_orders.created_at')
                // ->where(['delivery_orders.client_id' => $request->client_id])
                ->orderBy('garden_name', 'asc');

            if ($request->request_number !== null){
                $data->where('invoice_number', $request->request_number);
            }

            if ($request->date_from !== null){
                $data->where('delivery_orders.created_at', '>=', $request->date_from);
            }

            if ($request->date_to !== null){
                $data->where('delivery_orders.created_at', '<=', $request->date_to);
            }

            if ($request->client_id !== null){
                $data->where('delivery_orders.client_id', $request->client_id);
            }

           $reports = $data->get()->groupBy('client_name');


            $date = date('D, d-m-Y, h:i:s');
            $printed = auth()->user()->user;
            $by = $printed->first_name.' '.$printed->surname;

            if ($request->date_from == null){
                $period = 'FULL STATEMENT UPTO '.$request->date_to;
            }else{
                $period = 'FOR PERIOD BETWEEN '.$request->date_from.' AND '.$request->date_to;
            }


            $domPdfPath = base_path('vendor/dompdf/dompdf');
            \PhpOffice\PhpWord\Settings::setPdfRendererPath($domPdfPath);
            \PhpOffice\PhpWord\Settings::setPdfRendererName('DomPDF');

            $headers = ['size' => 8, 'name' => 'New Times Roman', 'bold' => true, 'space' => ['after' => 50, 'before' => 100]];
            $text = ['size' => 7, 'name' => 'New Times Roman', 'bold' => false, 'space' => ['after' => 40, 'before' => 100]];

            $table = new Table(['unit' => \PhpOffice\PhpWord\SimpleType\TblWidth::PERCENT, 'width' => 100 * 50, 'align' => Jc::CENTER]);

            $summaryTotalWeight = 0;
            $summaryTotalPackages = 0;

            foreach($reports as $clientName => $results){

            $totalPackets = 0;
            $totalWeight = 0;

            $table->addRow();
            $table->addCell(null, ['borderSize' => 1, 'gridSpan' => 12])->addText('CLIENT NAME '. $clientName, $headers, ['space' => ['before' => 100]]);

            $table->addRow();
            $table->addCell(500, ['borderSize' => 1])->addText('#', $headers, ['space' => ['before' => 100]]);
            $table->addCell(900, ['borderSize' => 1])->addText('Del. Type', $headers, ['space' => ['before' => 100]]);
            $table->addCell(1000, ['borderSize' => 1])->addText('Invoice #', $headers, ['space' => ['before' => 100]]);
            $table->addCell(1100, ['borderSize' => 1])->addText('Garden', $headers, ['space' => ['before' => 100]]);
            $table->addCell(900, ['borderSize' => 1])->addText('Grade', $headers, ['space' => ['before' => 100]]);
            $table->addCell(1000, ['borderSize' => 1])->addText('Order #', $headers, ['space' => ['before' => 100]]);
            $table->addCell(1000, ['borderSize' => 1])->addText('Lot #', $headers, ['space' => ['before' => 100]]);
            $table->addCell(1000, ['borderSize' => 1])->addText('Sale #', $headers, ['space' => ['before' => 100]]);
            $table->addCell(900, ['borderSize' => 1])->addText('Packages', $headers, ['space' => ['before' => 100]]);
            $table->addCell(1100, ['borderSize' => 1])->addText('Weight', $headers, ['space' => ['before' => 100]]);
            $table->addCell(1800, ['borderSize' => 1])->addText('Producer Whs', $headers, ['space' => ['before' => 100]]);
            $table->addCell(1000, ['borderSize' => 1])->addText('Collection Status', $headers, ['space' => ['before' => 100]]);


            foreach ($results as $key => $stock){
                $table->addRow();
                $table->addCell(500, ['borderSize' => 1])->addText(++$key, $text, ['space' => ['before' => 100]]);
                $table->addCell(900, ['borderSize' => 1])->addText($stock->delivery_type == 1 ? 'DO Entry' : 'Direct Del', $text, ['space' => ['before' => 100]]);
                $table->addCell(1000, ['borderSize' => 1])->addText($stock->invoice_number, $text, ['space' => ['before' => 100]]);
                $table->addCell(1100, ['borderSize' => 1])->addText($stock->garden_name, $text, ['space' => ['before' => 100]]);
                $table->addCell(900, ['borderSize' => 1])->addText($stock->grade_name, $text, ['space' => ['before' => 100]]);
                $table->addCell(1000, ['borderSize' => 1])->addText($stock->order_number, $text, ['space' => ['before' => 100]]);
                $table->addCell(1000, ['borderSize' => 1])->addText($stock->lot_number, $text, ['space' => ['before' => 100]]);
                $table->addCell(1000, ['borderSize' => 1])->addText($stock->sale_number, $text, ['space' => ['before' => 100]]);

                $table->addCell(900, ['borderSize' => 1])->addText(number_format($stock->packet, 2), $text, ['space' => ['before' => 100]]);
                $table->addCell(1100, ['borderSize' => 1])->addText(number_format($stock->weight, 2), $text, ['space' => ['before' => 100]]);
                $table->addCell(2000, ['borderSize' => 1])->addText($stock->warehouse_name, $text, ['space' => ['before' => 100]]);
                $table->addCell(1100, ['borderSize' => 1])->addText($stock->status == null || $stock->status == 1 ? 'Under Collection' : 'Collected', $text, ['space' => ['before' => 100]]);
                $totalPackets += $stock->packet;
                $totalWeight += $stock->weight;
            }

            $summaryTotalWeight += $totalWeight;
            $summaryTotalPackages += $totalPackets;


            $table->addRow();
            $table->addCell(7300, ['gridSpan' => 8])->addText();
            $table->addCell(900, ['borderSize' => 1])->addText(number_format($totalPackets, 2), $headers, ['space' => ['before' => 100]]);
            $table->addCell(1100, ['borderSize' => 1])->addText(number_format($totalWeight, 2), $headers, ['space' => ['before' => 100]]);
            $table->addCell(3100, ['gridSpan' => 2])->addText();

            $table->addRow();
            $table->addCell(null, ['gridSpan' => 12])->addText('', $headers, ['space' => ['before' => 100]]);

            }

            $approved = UserInfo::find($request->approved_by);

            $qrCodePath = 'Files/QrCodes/'.$request->service_number.'.png';
            \QrCode::format('png')
            ->size(1000)
            ->generate('REQUEST NUMBER: ' . $request->service_number . "\n" .
               'REPORT TYPE: ' . 'TEA ARRIVAL REPORT' . "\n" .
               'CLIENT NAME: ' . 'ALL CLIENTS' . "\n" .
               'REPORTING: ' . $period . "\n" .
               'TOTAL PACKAGES: ' . number_format($summaryTotalPackages, 2) . "\n" .
               'TOTAL NET WEIGHT: ' . number_format($summaryTotalWeight, 2) . "\n" .
               'REPORT APPROVED BY: ' . $approved->first_name.' '.$approved->surname,
               $qrCodePath);

            $stock = new TemplateProcessor(storage_path('verified_tea_collection_template.docx'));
            $stock->setComplexBlock('{table}', $table);

                if ($approved) {
                    $stock->setImageValue('qr', array('path' => $qrCodePath, 'width' => 90, 'height' => 100, 'ratio' => true, 'embed' => true));
                } else {
                    $stock->setValue('qr', 'NOT APPROVED'); // Fallback if QR code not generated
                }

            $stock->setValue('date', $date);
            $stock->setValue('by', $by);
            $stock->setValue('period', $period);
            $docPath = 'Files/TempFiles/REPORT '.$request->service_number.'.docx';
            $stock->saveAs($docPath);

            $phpWord = IOFactory::load($docPath);
            $pdfPath = 'Files/TempFiles/REPORT '.$request->service_number. time().".pdf";
            $converter =  new OfficeConverter($docPath, 'Files/TempFiles/');
            $converter->convertTo('REPORT '.$request->service_number.time().".pdf");
            unlink($docPath);
            if (file_exists('Files/QrCodes/' . $image)){
                unlink('Files/QrCodes/' . $image);
            }
            return response()->download($pdfPath)->deleteFileAfterSend(true);
        }
    }
    public function deleteReportRequest($id)
    {
        ReportRequest::find($id)->delete();
        return redirect()->back()->with('success', 'Success! Report Request has been deleted.');
    }

    public function selectStation(Request $request)
    {
        $warehouseId = $request->input('stationId');
        $data = Station::whereNot('station_id', $warehouseId)->get();
        return response()->json($data);
    }


}
