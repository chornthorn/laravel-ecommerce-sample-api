<?php

namespace App\Http\Controllers;

use App\Models\Billing;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderTracking;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $orders = Order::with([
            'customer:id,name,email',
            'transactions',
            'transactions.paymentMethod:id,name',
            'products',
            'orderTrackings',
            'billing'
        ])->get();

            // check if there are no orders
            if ($orders->isEmpty()) {
                return response()->json([
                    'message' => 'There are no orders yet.'
                ], 404);
            }

        $formattedOrders = $orders->map(function ($order) {

            // tranform total_amount to float
            $order->total_amount = (float) $order->total_amount;

            return [
                'id' => $order->id,
                'order_date' => $order->order_date,
                'total_amount' => $order->total_amount,
                'status' => $order->status,
                'note' => $order->note,
                'created_at' => $order->created_at,
                'updated_at' => $order->updated_at,
                'customer' => [
                    'id' => $order->customer->id,
                    'name' => $order->customer->name,
                    'email' => $order->customer->email,
                ],
                // billing
                'billing' => [
                    'id' => $order->id,
                    'first_name' => $order->billing->first_name,
                    'last_name' => $order->billing->last_name,
                    'phone' => $order->billing->phone,
                    'email' => $order->billing->email,
                    'address' => $order->billing->address,
                    'created_at' => $order->billing->created_at,
                    'updated_at' => $order->billing->updated_at,
                ],
                // transactions is an array, so we need to map it again
                'transactions' => $order->transactions->map(function ($transaction) {

                    // tranform transaction_amount to float
                    $transaction->transaction_amount = (float) $transaction->transaction_amount;
                    return [
                        'id' => $transaction->id,
                        'transaction_date' => $transaction->transaction_date,
                        'transaction_amount' => $transaction->transaction_amount,
                        'status' => $transaction->status,
                        'transaction_reference' => $transaction->transaction_reference_id,
                        'created_at' => $transaction->created_at,
                        'updated_at' => $transaction->updated_at,
                        'payment_method' => [
                            'id' => $transaction->paymentMethod->id,
                            'name' => $transaction->paymentMethod->name,
                        ],
                    ];
                }),
                'order_items' => $order->products->map(function ($product) {
                    // tranform price to float
                    $product->pivot->price = (float) $product->pivot->price;
                    return [
                        'product_id' => $product->id,
                        'product_name' => $product->name,
                        'price' => $product->pivot->price,
                        'quantity' => $product->pivot->quantity,
                    ];
                }),
                'order_trackings' => $order->orderTrackings->map(function ($orderTracking) {
                    return [
                        'id' => $orderTracking->id,
                        'status' => $orderTracking->status,
                        'created_at' => $orderTracking->created_at,
                        'updated_at' => $orderTracking->updated_at,
                    ];
                }),
            ];
        });

        return response()->json($formattedOrders, 200);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, $order_id)
    {
        $order = Order::with([
            'customer:id,name,email',
            'transactions',
            'transactions.paymentMethod:id,name',
            'products',
            'billing'
            ])->where('id', $order_id)->first();

            // check if order exists
            if (!$order) {
                return response()->json([
                    'message' => 'Order not found.'
                ], 404);
            }

            // tranform total_amount to float
            $order->total_amount = (float) $order->total_amount;

            $formattedOrder = [
                'id' => $order->id,
                'order_date' => $order->order_date,
                'total_amount' => $order->total_amount,
                'status' => $order->status,
                'note' => $order->note,
                'created_at' => $order->created_at,
                'updated_at' => $order->updated_at,
                'customer' => [
                    'id' => $order->customer->id,
                    'name' => $order->customer->name,
                    'email' => $order->customer->email,
                ],
                // billing
                'billing' => [
                    'id' => $order->id,
                    'first_name' => $order->billing->first_name,
                    'last_name' => $order->billing->last_name,
                    'phone' => $order->billing->phone,
                    'email' => $order->billing->email,
                    'address' => $order->billing->address,
                    'created_at' => $order->billing->created_at,
                    'updated_at' => $order->billing->updated_at,
                ],
                // transactions is an array, so we need to map it again
                'transactions' => $order->transactions->map(function ($transaction) {
                    // tranform transaction_amount to float
                    $transaction->transaction_amount = (float) $transaction->transaction_amount;
                    return [
                        'id' => $transaction->id,
                        'transaction_date' => $transaction->transaction_date,
                        'transaction_amount' => $transaction->transaction_amount,
                        'status' => $transaction->status,
                        'transaction_reference' => $transaction->transaction_reference_id,
                        'created_at' => $transaction->created_at,
                        'updated_at' => $transaction->updated_at,
                        'payment_method' => [
                            'id' => $transaction->paymentMethod->id,
                            'name' => $transaction->paymentMethod->name,
                        ],
                    ];
                }),
                'order_items' => $order->products->map(function ($product) {
                    // tranform price to float
                    $product->pivot->price = (float) $product->pivot->price;
                    return [
                        'product_id' => $product->id,
                        'product_name' => $product->name,
                        'price' => $product->pivot->price,
                        'quantity' => $product->pivot->quantity,
                    ];
                }),
                'order_trackings' => $order->orderTrackings->map(function ($orderTracking) {
                    return [
                        'id' => $orderTracking->id,
                        'status' => $orderTracking->status,
                        'created_at' => $orderTracking->created_at,
                        'updated_at' => $orderTracking->updated_at,
                    ];
                }),
            ];

            return response()->json($formattedOrder, 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'customer_id' => 'required|exists:users,id',
            'order_date' => 'required|date',
            'payment_method_id' => 'required|exists:payment_methods,id',
            'order_notes' => 'nullable|string',
            // order items
            'order_items' => 'required|array|min:1',
            'order_items.*.product_id' => 'required|exists:products,id',
            'order_items.*.quantity' => 'required|integer|min:1',
            'order_items.*.price' => 'required|numeric',
            // billing object
            'billing.first_name' => 'required|string',
            'billing.last_name' => 'required|string',
            'billing.phone' => 'required|string',
            'billing.email' => 'sometimes|nullable|email',
            'billing.address' => 'sometimes|nullable|string',
        ]);

        $totalAmount = collect($validatedData['order_items'])->sum(function ($item) {
            return $item['quantity'] * $item['price'];
        });

        DB::beginTransaction();

        try {
            $order = new Order();
            $order->customer_id = $validatedData['customer_id'];
            $order->order_date = $validatedData['order_date'];
            $order->total_amount = $totalAmount;
            $order->notes = $validatedData['order_notes'];
            $order->save();

            foreach ($validatedData['order_items'] as $itemData) {
                $item = new OrderItem();
                $item->order_id = $order->id;
                $item->product_id = $itemData['product_id'];
                $item->quantity = $itemData['quantity'];
                $item->price = $itemData['price'];
                $item->save();
            }

            // billing
            $billing = new Billing();
            $billing->order_id = $order->id;
            $billing->first_name = $validatedData['billing']['first_name'];
            $billing->last_name = $validatedData['billing']['last_name'];
            $billing->phone = $validatedData['billing']['phone'];
            $billing->email = $validatedData['billing']['email'];
            $billing->address = $validatedData['billing']['address'];
            $billing->save();

            // transaction
            $transaction = new Transaction();
            $transaction->order_id = $order->id;
            $transaction->payment_method_id = $validatedData['payment_method_id'];
            $transaction->transaction_date = date('Y-m-d H:i:s'); // 2021-03-01 12:00:00
            $transaction->transaction_amount = $totalAmount;
            $transaction->save();

            // order tracking
            $orderTracking = new OrderTracking();
            $orderTracking->order_id = $order->id;
            $orderTracking->status = 'pending'; // default status is pending
            $orderTracking->save();

            DB::commit();

            return response()->json(['message' => 'Order created successfully'], 200);
        } catch (\Exception $e) {
              dump($e);
            DB::rollback();
            return response()->json(['message' => 'Error creating order'], 500);
        }
    }

    // updateOrderStatus
    public function updateOrderStatus(Request $request)
    {
        // pending, 1: approved, 2: in_progress, 3: shipped, 4: cancelled, 5: refunded
        $validatedData = $request->validate([
            'order_id' => 'required',
            'status' => 'required|in:pending,approved,in_progress,shipped,cancelled,refunded',
        ]);

        $order = Order::find($validatedData['order_id']);

        // check if order exists
        if (!$order) {
            return response()->json([
                'message' => 'Order not found.'
            ], 404);
        }

        $order->status = $validatedData['status'];
        $order->update();

        return response()->json(['message' => 'Order status updated successfully'], 200);
    }


    // updateOrderTrackingStatus and updateOrderStatus by using transaction
    public function updateOrderTrackingStatus(Request $request)
    {
        // pending, 1: approved, 2: in_progress, 3: shipped, 4: cancelled, 5: refunded
        $validatedData = $request->validate([
            'order_id' => 'required',
            'status' => 'required|in:pending,approved,in_progress,shipped,cancelled,refunded',
        ]);

        DB::beginTransaction();

        try {
            $order = Order::find($validatedData['order_id']);

            // check if order exists
            if (!$order) {
                return response()->json([
                    'message' => 'Order not found.'
                ], 404);
            }

            $order->status = $validatedData['status'];
            $order->update();

            // create new order tracking when order status is not exist in order tracking table
            $orderTracking = OrderTracking::where('order_id',"=", $validatedData['order_id'])
                ->status($validatedData['status'])
                ->first();

            if (!$orderTracking) {
                $orderTracking = new OrderTracking();
                $orderTracking->order_id = $validatedData['order_id'];
                $orderTracking->status = $validatedData['status'];
                $orderTracking->save();
            }

            DB::commit();

            return response()->json(['message' => 'Order status updated successfully'], 200);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['message' => 'Error updating order status'], 500);
        }
    }

    // refundOrder and create new order tracking and create new transaction
    public function refundOrder(Request $request)
    {
         $validatedData = $request->validate([
            'order_id' => 'required',
            'refund_amount' => 'sometimes|required|numeric|min:0',
        ]);

        DB::beginTransaction();

        $refundStatus = 'refunded';

        try {

            $order = Order::with('transactions.paymentMethod')
                ->where('id', $validatedData['order_id'])
                ->canRefund()
                ->first();

            // check if order exists
            if (!$order) {
                return response()->json([
                    'message' => 'Order not found.'
                ], 404);
            }

            $order->status = $refundStatus;
            $order->update();

            // create new order tracking when order status is not exist in order tracking table
            $orderTracking = OrderTracking::where('order_id',"=", $validatedData['order_id'])
                ->status($refundStatus)
                ->first();

            if (!$orderTracking) {
                $orderTracking = new OrderTracking();
                $orderTracking->order_id = $validatedData['order_id'];
                $orderTracking->status = $refundStatus;
                $orderTracking->save();
            }

            // get payment method id
            $paymentMethodId = $order->transactions->first()->paymentMethod->id;

            // if refund amount is not set, refund total amount
            if (isset($validatedData['refund_amount'])) {

            // refund amount is set, refund amount must be less than or equal to total amount
            if ($validatedData['refund_amount'] > $order->total_amount) {
                return response()->json([
                    'message' => 'Refund amount must be less than or equal to total amount.'
                ], 400);
            }

            $refundAmount = $validatedData['refund_amount'];

            } else {
                $refundAmount = $order->total_amount;
            }


            // create new transaction
            $transaction = new Transaction();
            $transaction->order_id = $validatedData['order_id'];
            $transaction->payment_method_id = $paymentMethodId;
            $transaction->transaction_date = date('Y-m-d H:i:s');
            $transaction->transaction_amount = $refundAmount;
            $transaction->status = $refundStatus;
            $transaction->save();

            DB::commit();

            return response()->json(['message' => 'Order refunded successfully'], 200);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['message' => 'Error updating order status'], 500);
        }
    }

    // cancelOrder and create new order tracking and create new transaction
    public function cancelOrder(Request $request)
    {
        $validatedData = $request->validate([
            'order_id' => 'required',
        ]);

        DB::beginTransaction();

        $cancelStatus = 'cancelled';

        try {

            $order = Order::with('transactions.paymentMethod')
                ->where('id', $validatedData['order_id'])
                ->canCancel()
                ->first();

            // check if order exists
            if (!$order) {
                return response()->json([
                    'message' => 'Order not found.'
                ], 404);
            }

            $order->status = $cancelStatus;
            $order->update();

            // create new order tracking when order status is not exist in order tracking table
            $orderTracking = OrderTracking::where('order_id',"=", $validatedData['order_id'])
                ->status($cancelStatus)
                ->first();

            if (!$orderTracking) {
                $orderTracking = new OrderTracking();
                $orderTracking->order_id = $validatedData['order_id'];
                $orderTracking->status = $cancelStatus;
                $orderTracking->save();
            }

           // get payment method id from order that use for first transaction
            $paymentMethodId = $order->transactions->first()->paymentMethod->id;

            // create new transaction
            $transaction = new Transaction();
            $transaction->order_id = $validatedData['order_id'];
            $transaction->payment_method_id = $paymentMethodId;
            $transaction->transaction_date = date('Y-m-d H:i:s'); // 2021-03-01 12:00:00
            $transaction->transaction_amount = $order->total_amount;
            $transaction->status = 'chargeback';
            $transaction->save();

            DB::commit();

            return response()->json(['message' => 'Order cancelled successfully'], 200);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['message' => 'Error updating order status'], 500);
        }
    }

    // reorder
    public function reorder(Request $request)
    {
        // reorder user can add new order items but it's optional then proceed with the old order items

        $validatedData = $request->validate([
            'order_id' => 'required',
            'order_date' => 'required|date',
            'order_items' => 'array',
            'order_items.*.product_id' => 'sometimes|required|exists:products,id',
            'order_items.*.quantity' => 'sometimes|required|numeric|min:1',
            'order_items.*.price' => 'sometimes|required|numeric|min:0',
            'payment_method_id' => 'required|exists:payment_methods,id',
            'order_notes' => 'sometimes|required|string',

            // billing
            'billing.first_name' => 'sometimes|required|string',
            'billing.last_name' => 'sometimes|required|string',
            'billing.phone' => 'sometimes|required|string',
            'billing.email' => 'sometimes|nullable|email',
            'billing.address' => 'sometimes|nullable|string',
        ]);

        DB::beginTransaction();

        try {
            // get order with order items
            $order = Order::with([
                'customer:id,name,email',
                'transactions',
                'transactions.paymentMethod:id,name',
                'products',
                'orderTrackings'
            ])
            ->where('id', $validatedData['order_id'])
            ->canReorder()
            ->first();

            // check if order exists
            if (!$order) {
                return response()->json([
                    'message' => 'Order not found.'
                ], 404);
            }

            // create new order with old order items and if new order items is exist then add it
            $newOrder = new Order();
            $newOrder->customer_id = $order->customer_id;
            $newOrder->total_amount = $order->total_amount;
            $newOrder->notes = $validatedData['order_notes'];
            $newOrder->order_date = $validatedData['order_date'];
            $newOrder->save();

            // create new order billing if request is not provided
            if (!isset($validatedData['billing'])) {
                $billing = new Billing();
                $billing->order_id = $newOrder->id;
                $billing->first_name = $order->billing->first_name;
                $billing->last_name = $order->billing->last_name;
                $billing->phone = $order->billing->phone;
                $billing->email = $order->billing->email;
                $billing->address = $order->billing->address;
                $billing->save();
            } else {
                $billing = new Billing();
                $billing->order_id = $newOrder->id;
                $billing->first_name = $validatedData['billing']['first_name'];
                $billing->last_name = $validatedData['billing']['last_name'];
                $billing->phone = $validatedData['billing']['phone'];
                $billing->email = $validatedData['billing']['email'];
                $billing->address = $validatedData['billing']['address'];
                $billing->save();
            }

            // create new order items
            foreach ($order->products as $orderItem) {
                $newOrderItem = new OrderItem();
                $newOrderItem->order_id = $newOrder->id;
                $newOrderItem->product_id = $orderItem->id;
                $newOrderItem->quantity = $orderItem->pivot->quantity;
                $newOrderItem->price = $orderItem->pivot->price;
                $newOrderItem->save();
            }

            // check if new order items is exist then add it
            if (isset($validatedData['order_items'])) {
                foreach ($validatedData['order_items'] as $orderItem) {
                    $newOrderItem = new OrderItem();
                    $newOrderItem->order_id = $newOrder->id;
                    $newOrderItem->product_id = $orderItem['product_id'];
                    $newOrderItem->quantity = $orderItem['quantity'];
                    $newOrderItem->price = $orderItem['price'];
                    $newOrderItem->save();
                }


                // total amount by price * quantity
                $newOrderItemTotalAmount = collect($validatedData['order_items'])->sum(function ($item) {
                    return $item['price'] * $item['quantity'];
                });

                // total amount with old order items and new order items
                $newOrder->total_amount = $newOrder->total_amount + $newOrderItemTotalAmount;

                // update increment total amount
                $newOrder->update();
            }

            // create new order tracking
            $orderTracking = new OrderTracking();
            $orderTracking->order_id = $newOrder->id;
            $orderTracking->status = 'pending';
            $orderTracking->save();

            // dump($order->transactions->toArray());

            // create new transaction
            $transaction = new Transaction();
            $transaction->order_id = $newOrder->id;
            $transaction->payment_method_id = $validatedData['payment_method_id'];
            $transaction->transaction_date = date('Y-m-d H:i:s'); // 2021-03-01 12:00:00
            $transaction->transaction_amount = $newOrder->total_amount;
            $transaction->status = 'pending';
            $transaction->save();

            DB::commit();

            return response()->json(['message' => 'Order reordered successfully'], 200);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['message' => 'Error reordering order'], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Order $order)
    {
        //TODO: update order,order_items and transaction
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, $order_id)
    {

        DB::beginTransaction();

        try {
            //TODO: remove order,order_items and transaction

            DB::commit();
            return response()->json(['message' => 'Order deleted successfully'], 200);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['message' => 'Error deleting order'], 500);
        }
    }
}
