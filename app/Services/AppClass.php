<?php

namespace App\Services;

use App\Models\Station;
use Illuminate\Support\Facades\DB;

class AppClass
{
    public function clientsWithStock($id)
    {
        $locationId = Station::where('station_id', auth()->user()->station_id)->first()->location_id;
        $stations = Station::where('location_id', $locationId)->pluck('station_id')->toArray();

        $clients = DB::table('currentstock')->select('client_name', 'client_id')
            ->whereNotNull('current_stock')
            ->where('current_stock', '>', 0)
            ->whereNotNull('current_weight')
            ->where('current_weight', '>', 0)
            ->whereIn('station_id', $stations)
            ->where( 'current_stock', '>', 0)
            ->orderBy('client_name')
            ->get()
            ->groupBy('client_name');

        return $clients;

    }

public function currentStock()
{
//    return DB::table('stock_ins as si')
//        ->join('delivery_orders as d', function($join) {
//            $join->on('si.delivery_id', '=', 'd.delivery_id')
//                ->where('d.status', '=', 2);
//        })
//        ->join('clients as cl', 'cl.client_id', '=', 'd.client_id')
//        ->join('gardens as gd', 'gd.garden_id', '=', 'd.garden_id')
//        ->join('grades as gr', 'gr.grade_id', '=', 'd.grade_id')
//        ->join('users as u', 'u.user_id', '=', 'd.created_by')
//        ->join('warehouse_bays as wb', 'wb.bay_id', '=', 'si.warehouse_bay')
//        ->join('stations as st', 'st.station_id', '=', 'si.station_id')
//        ->leftJoin('loading_instructions as ls', function($join) {
//            $join->on('ls.delivery_id', '=', 'd.delivery_id')
//                ->whereNull('ls.deleted_at');
//        })
//        ->leftJoin('brokers as br', 'br.broker_id', '=', 'd.broker_id')
//        ->leftJoin('warehouses as wh', 'wh.warehouse_id', '=', 'd.warehouse_id')
//        ->leftJoin(DB::raw('(SELECT delivery_id, stock_id, SUM(shipped_packages) AS shipped_packages_sum, SUM(shipped_weight) AS shipped_weight_sum FROM shipments WHERE status = 1 AND deleted_at IS NULL GROUP BY delivery_id, stock_id) sh'), function($join) {
//            $join->on('sh.delivery_id', '=', 'si.delivery_id')
//                ->on('sh.stock_id', '=', 'si.stock_id');
//        })
//        ->leftJoin(DB::raw('(SELECT delivery_id, stock_id, SUM(blended_packages) AS blended_packages_sum, SUM(blended_weight) AS blended_weight_sum FROM blend_teas WHERE status = 1 AND deleted_at IS NULL GROUP BY delivery_id, stock_id) bt'), function($join) {
//            $join->on('bt.delivery_id', '=', 'si.delivery_id')
//                ->on('bt.stock_id', '=', 'si.stock_id');
//        })
//        ->leftJoin(DB::raw('(SELECT delivery_id, stock_id, SUM(transferred_palettes) AS transferred_palettes_sum, SUM(transferred_weight) AS transferred_weight_sum FROM external_transfers WHERE status = 3 AND deleted_at IS NULL GROUP BY delivery_id, stock_id) xt'), function($join) {
//            $join->on('xt.delivery_id', '=', 'si.delivery_id')
//                ->on('xt.stock_id', '=', 'si.stock_id');
//        })
//        ->leftJoin(DB::raw('(SELECT delivery_id, stock_id, SUM(requested_palettes) AS requested_palettes_sum, SUM(requested_weight) AS requested_weight_sum FROM transfers WHERE status = 3 AND deleted_at IS NULL GROUP BY delivery_id, stock_id) t'), function($join) {
//            $join->on('t.delivery_id', '=', 'si.delivery_id')
//                ->on('t.stock_id', '=', 'si.stock_id');
//        })
//        ->leftJoin('drivers as dr', 'dr.driver_id', '=', 'ls.driver_id')
//        ->leftJoin('logistics as ts', 'ts.transporter_id', '=', 'ls.transporter_id')
//        ->leftJoin('users as lus', 'lus.user_id', '=', 'ls.created_by')
//        ->whereNull('si.deleted_at')
//        ->groupBy(
//            'u.username', 'gd.garden_name', 'gr.grade_name', 'br.broker_name',
//            'wh.warehouse_name', 'cl.client_name', 'd.delivery_id', 'd.order_number',
//            'd.client_id', 'd.tea_id', 'd.garden_id', 'd.grade_id', 'd.packet',
//            'd.package', 'd.weight', 'd.warehouse_id', 'd.sub_warehouse_id',
//            'd.locality', 'd.broker_id', 'd.sale_number', 'd.invoice_number',
//            'd.lot_number', 'd.sale_date', 'd.prompt_date', 'd.created_by', 'd.status',
//            'd.created_at', 'ts.transporter_id', 'ts.transporter_name', 'dr.driver_id',
//            'dr.driver_name', 'dr.phone', 'dr.id_number', 'ls.loading_id',
//            'ls.loading_number', 'ls.registration', 'ls.created_by', 'ls.status',
//            'lus.username', 'ls.deleted_at', 'sh.shipped_packages_sum',
//            'sh.shipped_weight_sum', 'bt.blended_packages_sum', 'bt.blended_weight_sum',
//            'si.stock_id', 'si.status', 'si.total_pallets', 'si.total_weight',
//            'si.pallet_weight', 'si.package_tare', 'si.net_weight', 'si.warehouse_bay',
//            'si.delivery_number', 'si.date_received', 'si.user_id', 'st.station_name',
//            'st.station_id', 'wb.bay_id', 'wb.bay_name', 'si.created_at'
//        )
//        ->select(
//            'u.username AS username', 'gd.garden_name AS garden_name', 'gr.grade_name AS grade_name',
//            'br.broker_name AS broker_name', 'wh.warehouse_name AS warehouse_name', 'cl.client_name AS client_name',
//            'd.delivery_id AS delivery_id', 'd.order_number AS order_number', 'd.client_id AS client_id',
//            'd.tea_id AS tea_id', 'd.garden_id AS garden_id', 'd.grade_id AS grade_id', 'd.packet AS packet',
//            'd.package AS package', 'd.weight AS weight', 'd.warehouse_id AS warehouse_id', 'd.sub_warehouse_id AS sub_warehouse_id',
//            'd.locality AS locality', 'd.broker_id AS broker_id', 'd.sale_number AS sale_number', 'd.invoice_number AS invoice_number',
//            'd.lot_number AS lot_number', 'd.sale_date AS sale_date', 'd.prompt_date AS prompt_date', 'd.created_by AS created_by',
//            'd.status AS STATUS', 'd.created_at AS created_at', 'ts.transporter_id AS transporter_id', 'ts.transporter_name AS transporter_name',
//            'dr.driver_id AS driver_id', 'dr.driver_name AS driver_name', 'dr.phone AS phone', 'dr.id_number AS id_number',
//            'ls.loading_id AS loading_id', 'ls.loading_number AS loading_number', 'ls.status AS load_status',
//            'ls.registration AS registration', 'ls.created_by AS load_user_id', 'lus.username AS load_user',
//            'ls.deleted_at AS deleted_at', 'sh.shipped_packages_sum AS shipped_packages', 'sh.shipped_weight_sum AS shipped_weight',
//            'bt.blended_packages_sum AS blended_packages', 'bt.blended_weight_sum AS blended_weight', 'si.stock_id AS stock_id',
//            'si.status AS stock_status', 'si.total_pallets AS total_pallets', 'si.total_weight AS total_weight',
//            'si.pallet_weight AS pallet_weight', 'si.package_tare AS package_tare', 'si.net_weight AS net_weight',
//            'si.warehouse_bay AS warehouse_bay', 'si.delivery_number AS delivery_number', 'si.date_received AS date_received',
//            'si.user_id AS received_by', 'st.station_name AS stocked_at', 'st.station_id AS station_id',
//            'wb.bay_id AS bay_id', 'wb.bay_name AS bay_name',
//            DB::raw('si.total_pallets - COALESCE(SUM(t.requested_palettes), 0) - COALESCE(SUM(xt.transferred_palettes), 0) - COALESCE(SUM(sh.shipped_packages_sum), 0) - COALESCE(SUM(bt.blended_packages_sum), 0) AS current_stock'),
//            DB::raw('si.net_weight - COALESCE(SUM(t.requested_weight), 0) - COALESCE(SUM(xt.transferred_weight), 0) - COALESCE(SUM(sh.shipped_weight_sum), 0) - COALESCE(SUM(bt.blended_weight_sum), 0) AS current_weight'),
//            'si.created_at AS sortOrder'
//        )
//        ->get();

    return DB::table('stock_ins as si')
        ->join('delivery_orders as d', function($join) {
            $join->on('si.delivery_id', '=', 'd.delivery_id')
                ->where('d.status', '=', 2);
        })
        ->join('clients as cl', 'cl.client_id', '=', 'd.client_id')
        ->join('gardens as gd', 'gd.garden_id', '=', 'd.garden_id')
        ->join('grades as gr', 'gr.grade_id', '=', 'd.grade_id')
        ->join('users as u', 'u.user_id', '=', 'd.created_by')
        ->join('warehouse_bays as wb', 'wb.bay_id', '=', 'si.warehouse_bay')
        ->join('stations as st', 'st.station_id', '=', 'si.station_id')
        ->leftJoin('loading_instructions as ls', function($join) {
            $join->on('ls.delivery_id', '=', 'd.delivery_id')
                ->whereNull('ls.deleted_at');
        })
        ->leftJoin('brokers as br', 'br.broker_id', '=', 'd.broker_id')
        ->leftJoin('warehouses as wh', 'wh.warehouse_id', '=', 'd.warehouse_id')
        ->leftJoin('shipments as sh', function($join) {
            $join->on('sh.delivery_id', '=', 'si.delivery_id')
                ->on('sh.stock_id', '=', 'si.stock_id')
                ->where('sh.status', '=', 1)
                ->whereNull('sh.deleted_at');
        })
        ->leftJoin('blend_teas as bt', function($join) {
            $join->on('bt.delivery_id', '=', 'si.delivery_id')
                ->on('bt.stock_id', '=', 'si.stock_id')
                ->where('bt.status', '=', 1)
                ->whereNull('bt.deleted_at');
        })
        ->leftJoin('external_transfers as xt', function($join) {
            $join->on('xt.delivery_id', '=', 'si.delivery_id')
                ->on('xt.stock_id', '=', 'si.stock_id')
                ->where('xt.status', '=', 3)
                ->whereNull('xt.deleted_at');
        })
        ->leftJoin('transfers as t', function($join) {
            $join->on('t.delivery_id', '=', 'si.delivery_id')
                ->on('t.stock_id', '=', 'si.stock_id')
                ->where('t.status', '=', 3)
                ->whereNull('t.deleted_at');
        })
        ->leftJoin('drivers as dr', 'dr.driver_id', '=', 'ls.driver_id')
        ->leftJoin('logistics as ts', 'ts.transporter_id', '=', 'ls.transporter_id')
        ->leftJoin('users as lus', 'lus.user_id', '=', 'ls.created_by')
        ->whereNull('si.deleted_at')
        ->groupBy(
            'u.username', 'gd.garden_name', 'gr.grade_name', 'br.broker_name',
            'wh.warehouse_name', 'cl.client_name', 'd.delivery_id', 'd.order_number',
            'd.client_id', 'd.tea_id', 'd.garden_id', 'd.grade_id', 'd.packet',
            'd.package', 'd.weight', 'd.warehouse_id', 'd.sub_warehouse_id',
            'd.locality', 'd.broker_id', 'd.sale_number', 'd.invoice_number',
            'd.lot_number', 'd.sale_date', 'd.prompt_date', 'd.created_by', 'd.status',
            'd.created_at', 'ts.transporter_id', 'ts.transporter_name', 'dr.driver_id',
            'dr.driver_name', 'dr.phone', 'dr.id_number', 'ls.loading_id',
            'ls.loading_number', 'ls.registration', 'ls.created_by', 'ls.status',
            'lus.username', 'ls.deleted_at', 'sh.shipped_packages',
            'sh.shipped_weight', 'bt.blended_packages', 'bt.blended_weight',
            'si.stock_id', 'si.status', 'si.total_pallets', 'si.total_weight',
            'si.pallet_weight', 'si.package_tare', 'si.net_weight', 'si.warehouse_bay',
            'si.delivery_number', 'si.date_received', 'si.user_id', 'st.station_name',
            'st.station_id', 'wb.bay_id', 'wb.bay_name', 'si.created_at'
        )
        ->select(
            'u.username AS username', 'gd.garden_name AS garden_name', 'gr.grade_name AS grade_name',
            'br.broker_name AS broker_name', 'wh.warehouse_name AS warehouse_name', 'cl.client_name AS client_name',
            'd.delivery_id AS delivery_id', 'd.order_number AS order_number', 'd.client_id AS client_id',
            'd.tea_id AS tea_id', 'd.garden_id AS garden_id', 'd.grade_id AS grade_id', 'd.packet AS packet',
            'd.package AS package', 'd.weight AS weight', 'd.warehouse_id AS warehouse_id', 'd.sub_warehouse_id AS sub_warehouse_id',
            'd.locality AS locality', 'd.broker_id AS broker_id', 'd.sale_number AS sale_number', 'd.invoice_number AS invoice_number',
            'd.lot_number AS lot_number', 'd.sale_date AS sale_date', 'd.prompt_date AS prompt_date', 'd.created_by AS created_by',
            'd.status AS STATUS', 'd.created_at AS created_at', 'ts.transporter_id AS transporter_id', 'ts.transporter_name AS transporter_name',
            'dr.driver_id AS driver_id', 'dr.driver_name AS driver_name', 'dr.phone AS phone', 'dr.id_number AS id_number',
            'ls.loading_id AS loading_id', 'ls.loading_number AS loading_number', 'ls.status AS load_status',
            'ls.registration AS registration', 'ls.created_by AS load_user_id', 'lus.username AS load_user',
            'ls.deleted_at AS deleted_at', 'sh.shipped_packages', 'sh.shipped_weight',
            'bt.blended_packages', 'bt.blended_weight', 'si.stock_id AS stock_id',
            'si.status AS stock_status', 'si.total_pallets AS total_pallets', 'si.total_weight AS total_weight',
            'si.pallet_weight AS pallet_weight', 'si.package_tare AS package_tare', 'si.net_weight AS net_weight',
            'si.warehouse_bay AS warehouse_bay', 'si.delivery_number AS delivery_number', 'si.date_received AS date_received',
            'si.user_id AS received_by', 'st.station_name AS stocked_at', 'st.station_id AS station_id',
            'wb.bay_id AS bay_id', 'wb.bay_name AS bay_name',
            DB::raw('si.total_pallets - COALESCE(SUM(t.requested_palettes), 0) - COALESCE(SUM(xt.transferred_palettes), 0) - COALESCE(SUM(sh.shipped_packages), 0) - COALESCE(SUM(bt.blended_packages), 0) AS current_stock'),
            DB::raw('si.net_weight - COALESCE(SUM(t.requested_weight), 0) - COALESCE(SUM(xt.transferred_weight), 0) - COALESCE(SUM(sh.shipped_weight), 0) - COALESCE(SUM(bt.blended_weight), 0) AS current_weight'),
            'si.created_at AS sortOrder'
        )
        ->get();
}

}
