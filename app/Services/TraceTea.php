<?php

namespace App\Services;

use App\Models\DeliveryOrder;

class TraceTea
{
    public function traceDeliveryOrder($deliveryOrderId)
    {
        $deliveryOrder = DeliveryOrder::with([
            'stockIns.internalTransfers',
            'stockIns.externalTransfers',
            'stockIns.blendProcessings',
            'stockIns.straightLineShippings',
            'loadingInstructions'
        ])
            ->where('delivery_id', $deliveryOrderId)
            ->orWhere('invoice_number', $deliveryOrderId)
            ->orWhere('lot_number', $deliveryOrderId)
            ->get();

        if (!$deliveryOrder) {
            return null; // Or handle the error as needed
        }

        return $deliveryOrder;
    }
}
