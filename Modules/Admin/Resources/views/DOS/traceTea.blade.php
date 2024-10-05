@php use Illuminate\Support\Facades\DB; @endphp
@extends('admin::layouts.default')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/2.1.5/css/dataTables.dataTables.css">
@section('admin::dashboard')
    <div class="card">
        <div class="card-header">
            <div class="row flex-between-center">
                <div class="col-6 col-sm-auto d-flex align-items-center pe-0">
                    <h5 class="fs-9 mb-0 text-nowrap py-0 py-xl-0">Tea Global Search </h5>
                </div>
            </div>
        </div>
        <div class="card-body overflow-hidden p-lg-3">
            @foreach($teas as $data)
                    <?php $stocks = $data->toArray() ?>
                <div class="table-responsive">
                    <table class="table table-striped table-bordered table-sm fs-sm mb-4">
                        <thead>
                        <th colspan="6">DELIVERY ORDER DETAILS 
                            <div class="col-6 col-sm-auto ms-auto text-end ps-0">
                            <div id="table-simple-pagination-replace-element">
                                <a class="link-info" href="{{ route('admin.editDO', $data->delivery_id) }}"><span class="fas fa-edit" data-fa-transform="shrink-3 down-2"></span><span class="d-none d-sm-inline-block ms-1">Edit DO</span></a>
                               @if($data->status < 2)
                                     <a class="link-danger" onclick="return confirm('Are you sure you want to delete DO INV {{ $data->invoice_number }}')" href="{{ route('admin.deleteDeliveryOrder', $data->delivery_id) }}"><span class="fas fa-trash-alt" data-fa-transform="shrink-3 down-2"></span><span class="d-none d-sm-inline-block ms-1">Delete DO</span></a>
                               @endif
                            </div>
                            </div>
                        </th>
                        </thead>
                        <thead>
                        <th colspan="2">Delivery Order</th>
                        <th>DO Created By</th>
                        <th>{{ \App\Models\UserInfo::where('user_id', $data->created_by)->first()->surname.' '.\App\Models\UserInfo::where('user_id', $data->created_by)->first()->first_name }}</th>
                        <td>DO status</td>
                        <td>{{ $data->status == null || $data->status == 1 ? 'Under Collection' : 'Collected' }}</td>
                        </thead>
                        <tbody>
                        <tr>
                            <td>Client Name</td>
                            <td>{{ \App\Models\Client::where('client_id', $data->client_id)->first()->client_name  }}</td>
                            <td>Delivery Type</td>
                            <td>{{ $data->delivery_type == 1 ? 'DO Entry' : 'Direct Delivery'  }}</td>
                            <td>Tea Type</td>
                            <td>{{ $data->tea_id == 1 ? 'Auction Tea' : ($data->tea_id == 2 ? 'Blend Balance' : ($data->tea_id == 3 ? 'Factory Tea' : 'Private Tea')) }}</td>
                        </tr>

                        <tr>
                            <td>Order Number</td>
                            <td>{{ $data->order_number }}</td>
                            <td>Garden Name</td>
                            <td>{{ \App\Models\Garden::where('garden_id', $data->garden_id)->first()->garden_name }}</td>
                            <td>Garden Name</td>
                            <td>{{ \App\Models\Grade::where('grade_id', $data->grade_id)->first()->grade_name }}</td>
                        </tr>

                        <tr>
                            <td>Number of packages</td>
                            <td>{{ $data->packet }}</td>
                            <td>Net Weight</td>
                            <td>{{ $data->weight }}</td>
                            <td>Package</td>
                            <td>{{ $data->package == 1 ? 'Poly Bag' : 'Paper Sack' }}</td>
                        </tr>

                        <tr>
                            <td>Producer Warehouse</td>
                            <td>{{ \App\Models\Warehouse::where('warehouse_id', $data->warehouse_id)->first() == null ? : \App\Models\Warehouse::where('warehouse_id', $data->warehouse_id)->first()->warehouse_name }}</td>
                            <td>Sub-warehouse</td>
                            <td>{{ \App\Models\SubWarehouse::where('sub_warehouse_id', $data->sub_warehouse_id)->first() == null ? : \App\Models\SubWarehouse::where('sub_warehouse_id', $data->sub_warehouse_id)->first()->sub_warehouse_name }}</td>
                            <td>Sub-warehouse Locality</td>
                            <td>{{ $data->locality == 1 ? 'ISLAND' : ( $data->locality ==  2 ? 'CHANGAMWE' : ($data->locality == 3 ? 'JOMVU' : ($data->locality == 4 ? 'BONJE' : ($data->locality == 5 ? 'MIRITINI' : '')))) }} </td>
                        </tr>
                        <tr>
                            <td>Broker</td>
                            <td>{{ \App\Models\Broker::where('broker_id',  $data->broker_id)->first() == null ? : \App\Models\Broker::where('broker_id',  $data->broker_id)->first()->broker_name }}</td>
                            <td>Sale Number</td>
                            <td>{{ $data->sale_number }}</td>
                            <td>Invoice Number</td>
                            <td>{{ $data->invoice_number }}</td>
                        </tr>

                        <tr>
                            <td>Lot Number </td>
                            <td>{{ $data->lot_number }}</td>
                            <td>Sale Date</td>
                            <td>{{ $data->sale_date }}</td>
                            <td>Prompt Date</td>
                            <td>{{ $data->prompt_date }}</td>
                        </tr>

                        </tbody>
                    </table>
                        <?php $tcis = $data->toArray() ?>
                    @if(!empty($tcis['loading_instructions']))
                        <hr class="mb-4 mt-4">
                        <table  class="table table-striped table-bordered table-sm fs-sm mb-4">
                            <thead>
                            <th colspan="6">TEA COLLECTION DETAILS</th>
                            </thead>
                            <thead>
                            <th>TCI NUMBER</th>
                            <th>ASSIGNED TRANSPORTER</th>
                            <th>ASSIGNED DRIVER</th>
                            <th>ASSIGNED IDNO</th>
                            <td>CREATED BY</td>
                            <td>STATUS</td>
                            </thead>
                            <tbody>
                            @foreach($tcis['loading_instructions'] as $tci)
                                <tr>
                                    <td> {{ $tci['loading_number']  }} </td>
                                    <td> {{ \App\Models\Transporter::where('transporter_id', $tci['transporter_id'])->first() == null ? '' : \App\Models\Transporter::where('transporter_id', $tci['transporter_id'])->first()->transporter_name  }} </td>
                                    <td> {{ \App\Models\Driver::where('driver_id', $tci['driver_id'])->first() == null ? '' : \App\Models\Driver::where('driver_id', $tci['driver_id'])->first()->driver_name }} </td>
                                    <td> {{ \App\Models\Driver::where('driver_id', $tci['driver_id'])->first() == null ? '' : \App\Models\Driver::where('driver_id', $tci['driver_id'])->first()->id_number }} </td>
                                    <td>{{ \App\Models\UserInfo::where('user_id', $tci['created_by'])->first()->surname.' '.\App\Models\UserInfo::where('user_id', $tci['created_by'])->first()->first_name }}</td>
                                    <td> {{ $tci['status'] == 1 || $tci['status'] == null ? 'UNDER COLLECTION' : 'TCI RECEIVED' }} </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    @endif
                    @if(!empty($stocks['stock_ins'][0]['internal_transfers']))
                        <hr class="mb-4 mt-4">
                        <table  class="table table-striped table-bordered table-sm fs-sm mb-4">
                            <thead>
                            <th colspan="11">INTER-STATION TRANSFERS</th>
                            </thead>

                            <thead>
                            <th>#</th>
                            <th>DELIVERY NUMBER</th>
                            <th>PACKAGES TRANSFERRED</th>
                            <th>WEIGHT TRANSFERRED</th>
                            <th>FROM</th>
                            <th>TO</th>
                            <th>TRANSPORTER </th>
                            <th>DRIVER</th>
                            <th>ID NUMBER</th>
                            <th>DATE TRANSFERRED</th>
                            <th>STATUS</th>
                            </thead>

                            <tbody>
                            @foreach(collect($stocks['stock_ins']) as $internalTs)
                                @foreach($internalTs['internal_transfers'] as $internalT)
                                    <tr>
                                        <td> {{ $internalT['delivery_number'] }} </td>
                                        <td> {{ $internalT['delivery_number'] }} </td>
                                        <td> {{ $internalT['requested_palettes'] }} </td>
                                        <td> {{ $internalT['requested_weight'] }} </td>
                                        <td> {{ \App\Models\Station::where('station_id', $internalT['station_id'])->first()['station_name'] }} </td>
                                        <td> {{ \App\Models\Station::where('station_id', $internalT['destination'])->first()['station_name'] }} </td>
                                        <td> {{ isset($internalT['transporter_id']) ? \App\Models\Transporter::where('transporter_id', $internalT['transporter_id'])->first()->transporter_name ?? null : null }} </td>
                                        <td> {{ isset($internalT['driver_id']) ? \App\Models\Driver::where('driver_id', $internalT['driver_id'])->first()['driver_name'] ?? null : null }} </td>
                                        <td> {{ isset($internalT['driver_id']) ? \App\Models\Driver::where('driver_id', $internalT['driver_id'])->first()['id_number'] ?? null : null }} </td>
                                        <td> {{ Carbon\Carbon::parse($internalT['updated_at'])->format('D, d/m/y')  }} </td>
                                        <td> {{ $internalT['status'] < 1 || null ? 'Under Processing' : 'Transferred' }} </td>
                                    </tr>
                                @endforeach
                            @endforeach
                            </tbody>

                        </table>
                    @endif
                    @if(!empty($stocks['stock_ins'][0]['external_transfers']))
                        <hr class="mb-4 mt-4">
                        <table  class="table table-striped table-bordered table-sm fs-sm mb-4">
                            <thead>
                            <th colspan="11">EXTERNAL TRANSFERS</th>
                            </thead>

                            <thead>
                            <th>#</th>
                            <th>DELIVERY NUMBER</th>
                            <th>PACKAGES TRANSFERRED</th>
                            <th>WEIGHT TRANSFERRED</th>
                            <th>FROM</th>
                            <th>TO</th>
                            <th>TRANSPORTER </th>
                            <th>DRIVER</th>
                            <th>ID NUMBER</th>
                            <th>DATE TRANSFERRED</th>
                            <th>STATUS</th>
                            </thead>

                            <tbody>
                            @foreach(collect($stocks['stock_ins']) as $externalTs)
                                @foreach($externalTs['external_transfers'] as $externalT)
                                        <?php $station = \App\Models\User::where('user_id', $externalT['created_by'])->first()->station_id; ?>
                                    <tr>
                                        <td> {{ $externalT['delivery_number'] }} </td>
                                        <td> {{ $externalT['delivery_number'] }} </td>
                                        <td> {{ $externalT['transferred_palettes'] }} </td>
                                        <td> {{ $externalT['transferred_weight'] }} </td>
                                        <td> {{ \App\Models\Station::where('station_id', $station)->first()['station_name'] }} </td>
                                        <td> {{ \App\Models\Warehouse::where('warehouse_id', $externalT['warehouse_id'])->first()['warehouse_name'] }} </td>
                                        <td> {{ \App\Models\Transporter::where('transporter_id', $externalT['transporter_id'])->first()['transporter_name'] }} </td>
                                        <td> {{ \App\Models\Driver::where('driver_id', $externalT['driver_id'])->first()['driver_name'] }} </td>
                                        <td> {{ \App\Models\Driver::where('driver_id', $externalT['driver_id'])->first()['id_number'] }} </td>
                                        <td> {{ Carbon\Carbon::parse($externalT['updated_at'])->format('D, d/m/y')  }} </td>
                                        <td> {{ $externalT['status'] < 1 || null ? 'Under Processing' : 'Transferred' }} </td>
                                    </tr>
                                @endforeach
                            @endforeach
                            </tbody>

                        </table>
                    @endif
                    @if(!empty($stocks['stock_ins'][0]['blend_processings']))
                        <hr class="mb-4 mt-4">
                        <table  class="table table-striped table-bordered table-sm fs-sm mb-4">
                            <thead>
                            <th colspan="6">TEA USAGE IN BLEND PROCESSING</th>
                            </thead>

                            <thead>
                            <th>#</th>
                            <th>BLEND NUMBER</th>
                            <th>PACKAGES USED</th>
                            <th>WEIGHT USED</th>
                            <th>BLEND CREATED</th>
                            <th>BLEND STATUS</th>
                            </thead>

                            <tbody>
                            @foreach(collect($stocks['stock_ins']) as $blends)
                                @foreach($blends['blend_processings'] as $blend)
                                    <tr>
                                        <td> {{ $loop->iteration }} </td>
                                        <td> {{ \App\Models\BlendSheet::where('blend_id', $blend['blend_id'])->first()->blend_number }} </td>
                                        <td> {{ $blend['blended_packages'] }} </td>
                                        <td> {{ $blend['blended_weight'] }} </td>
                                        <td> {{ \Carbon\Carbon::parse($blend['created_at'])->format('d/m/y H:i') }} </td>
                                        <td> {{ \App\Models\BlendSheet::where('blend_id', $blend['blend_id'])->first()['status'] > 4 ? 'BLEND BEING PROCESSED' : 'BLEND SHIPPED ON '.\Carbon\Carbon::createFromTimestamp(\App\Models\BlendSheet::where('blend_id', $blend['blend_id'])->first()['blend_shipped'])->format('D, d/m/y H:i') }} </td>
                                    </tr>
                                @endforeach
                            @endforeach
                            </tbody>
                        </table>
                    @endif
                    @if(!empty($stocks['stock_ins'][0]['straight_line_shippings'] ))
                        <hr class="mb-4 mt-4">
                        <table  class="table table-striped table-bordered table-sm fs-sm mb-4">
                            <thead>
                            <th colspan="6">STRAIGHT LINE SHIPMENT</th>
                            </thead>

                            <thead>
                            <th>#</th>
                            <th>SI NUMBER</th>
                            <th>PACKAGES SHIPPED</th>
                            <th>WEIGHT SHIPPED</th>
                            <th>SHIPPING DATE</th>
                            <th>SI STATUS</th>
                            </thead>

                            <tbody>
                            @foreach(collect($stocks['stock_ins']) as $shippings)
                                @foreach($shippings['straight_line_shippings'] as $shipping)
                                    <tr>
                                        <td> {{ $loop->iteration }} </td>
                                        <td> {{ \App\Models\ShippingInstruction::where('shipping_id', $shipping['shipping_id'])->first()->shipping_number }} </td>
                                        <td> {{ $shipping['shipped_packages'] }} </td>
                                        <td> {{ $shipping['shipped_weight'] }} </td>
                                        <td> {{ \Carbon\Carbon::parse($shipping['created_at'])->format('d/m/y H:i') }} </td>
                                        <td> {{ \App\Models\ShippingInstruction::where('shipping_id', $shipping['shipping_id'])->first()['status'] > 4 ? 'BLEND BEING PROCESSED' : 'BLEND SHIPPED ON '.\Carbon\Carbon::createFromTimestamp(\App\Models\ShippingInstruction::where('shipping_id', $shipping['shipping_id'])->first()['date_shipped'])->format('D, d/m/y H:i') }} </td>
                                    </tr>
                                @endforeach
                            @endforeach
                            </tbody>
                        </table>
                    @endif
                    @if(!empty($stocks['stock_ins'][0]['delivery_id']))
                        <hr class="mb-4 mt-4">
                    <table  class="table table-striped table-bordered table-sm fs-sm mb-4">
                    <thead>
                    <th colspan="12">STOCK </th>
                    </thead>
                    <thead>
                    <th>#</th>
                    <th>Delivery Number</th>
                    <th>Station</th>
                    <th>Packages</th>
                    <th>Weight</th>
                    <th>Transporter</th>
                    <th>Truck Reg</th>
                    <th>Driver Name</th>
                    <th>Driver IDNO.</th>
                    <th>Received On</th>
                    <th>Received By</th>

                    </thead>
                    <tbody>
                    <tr></tr>
                    <?php
                    $teas = \Illuminate\Support\Facades\DB::table('currentstock')->where(['delivery_id' => $stocks['stock_ins'][0]['delivery_id']])->get();
                    ?>
                    @foreach($teas as $stock)
                        <tr>
                            <td> {{ $loop->iteration }} </td>
                            <td> {{ $stock->delivery_number }} </td>
                            <td>
                                {{ $stock->stocked_at }} {{ $stock->bay_name }}
                            </td>
                            <td> {{ $stock->current_stock }} </td>
                            <td> {{ $stock->current_weight }} </td>
                            <td> {{ $stock->transporter_name }} </td>
                            <td> {{ $stock->registration }} </td>
                            <td> {{ $stock->driver_name }} </td>
                            <td> {{ $stock->id_number }} </td>
                            <td> {{ \Carbon\Carbon::createFromTimestamp($stock->date_received)->format('D, d/m/y H:i') }} </td>
                            <?php $user = \App\Models\User::join('user_infos', 'user_infos.user_id', '=', 'users.user_id')->where('username', $stock->username)->first(); ?>
                            <td> {{ $user->surname.' '.$user->first_name }} </td>

                        </tr>
                    @endforeach
                    </tbody>
                </table>

                @endif
                    <div class="d-flex justify-content-end mb-5">
                        <?php
                        if(empty($stocks['stock_ins'][0]['delivery_id'])){
                            $currentStock = [
                                'current_stock' => 0,
                                'current_weight' => 0
                            ];
                        }else {
                            $currentStock = DB::table('currentstock')
                                ->where('delivery_id', $stocks['stock_ins'][0]['delivery_id'])
                                ->where('current_stock', '>', 0)
                                ->where('current_weight', '>', 0)
                                ->selectRaw('SUM(current_stock) as stockAtHand')
                                ->selectRaw('SUM(current_weight) as weightAtHand')
//                                ->groupBy('stock_id', 'delivery_id')
                                ->get();
                        }

                        ?>
                        <hr>
                    @if(empty($stocks['stock_ins'][0]['delivery_id']))
                        <span class="badge text-bg-danger m-2"> Tea not yet received </span>
                    @else
                        CURRENT BALANCE : Packages : <span class="badge text-bg-success m-2 ">{{ number_format($currentStock[0]->stockAtHand, 2) }} </span> Weight : <span class="badge text-bg-primary m-2">{{ number_format($currentStock[0]->weightAtHand, 2) }} </span>

                    @endif
                </div>
                </div>
            @endforeach
        </div>
    </div>
@endsection
