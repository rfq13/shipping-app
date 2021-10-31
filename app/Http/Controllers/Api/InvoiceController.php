<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Billing;
use App\Models\BillingOrder;
use App\Models\Order;
use App\Models\OrderDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use PDF;

class InvoiceController extends Controller
{
    function store(Request $request)
    {
        reqValidate($request->all(),[
            'products'=>'required|array',
            'products.*.id'=>'required|numeric',
            'products.*.qty'=>'required|numeric',
        ]);
        
        DB::beginTransaction();
        try {
            $order = new Order;
            $order->invoice = generateInvoiceNumber();
            $order->payment_status = "unpaid";
            $order->user_id = $request->user()->id;
            $order->save();
    
            foreach ($request->products as $product) {
                $orderDetail = new OrderDetail;
                $orderDetail->product_id = $product['id'];
                $orderDetail->qty = $product['qty'];
                $orderDetail->order_id = $order->id;
                $orderDetail->save();
            }

            DB::commit();

            throwJson(compact('order'));
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'message'=>$th->getMessage(),
                'line'=>$th->getLine(),
                'code'=>$th->getCode()
            ],500);
        }
    }

    function index(Request $request)
    {
        $invoices = Order::where('user_id',$request->user()->id)
        ->with('orderDetails')
        ->get();
        throwJson(compact('invoices'));
    }

    function createbilling(Request $request)
    {
        reqValidate($request->all(),[
            "order_id"    => "required|array|min:2",
            "order_id.*"  => "required|numeric|distinct",
        ]);
        
        $orders = Order::where('user_id',$request->user()->id)
        ->whereIn("id",$request->order_id)
        ->select('id')
        ->get();
        
        $code = date('mdHYis');
        $billing = Billing::create([
            'code'=>$code,
            'user_id'=>$request->user()->id
        ]);
        
        $insertBillings = [];
        foreach ($orders as $key => $order) {
            array_push($insertBillings,[
                'billing_id'=>$billing->id,
                'order_id'=>$order->id,
                'created_at'=>date("Y-m-d H:i:s"),
            ]);
        }

        BillingOrder::insert($insertBillings);

        return $billing;
    }

    function data_billings($condition)
    {
        $query = Billing::where($condition)
        ->with('orders.orders.orderDetails')
        ->get()
        ->toArray();
        
        $billings = array_map(function ($billing)
        {
            $orders = array_column($billing['orders'],'orders');
            
            return [
                'code'=>$billing['code'],
                'orders'=>$orders,
                'created_at'=>$billing['created_at'],
            ];
        },$query);
        return $billings;
    }
    function billings(Request $request)
    {
        $billings = $this->data_billings([
            'user_id'=>$request->user()->id
        ]);
        return response()->json(compact('billings'));
    }

    function updatePayment(Request $request)
    {
        reqValidate($request->all(),[
            'invoice_number'=>'required|string',
            'payment_status'=>'required|string',
            'billing' => 'required|bool'
        ]);

        if ($request->billing) {
            $query = "UPDATE test_shipping_app.orders o 
            SET o.payment_status = '$request->payment_status'
            WHERE o.id IN (
            SELECT bo.order_id 
            FROM test_shipping_app.billings b
            JOIN test_shipping_app.billing_orders bo on b.id = bo.billing_id
            WHERE b.code = $request->invoice_number)";
    
            DB::statement($query);
        }else {
            $order = Order::where('invoice',$request->invoice_number)->first();
            if (!$order) {
                throwJson(['message'=>'invoice order not found'],400);
            }

            $order->payment_status = $request->payment_status;
            $order->save();
        }

        throwJson(['message'=>'ok']);
    }

    function bulkInvoice(Request $request)
    {
        reqValidate($request->all(),[
            'code'=>'required'
        ]);

        $billings=$this->data_billings([
                'user_id'=>$request->user()->id,
                'code'=>$request->code
            ]);

        if (!count($billings)) {
            throwJson(['message'=>'billing not found!'],400);
        }

        $billings['user'] = $request->user();

        $pdf = PDF::setOptions([
            'isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true,
            'logOutputFile' => storage_path('logs/log.htm'),
            'tempDir' => storage_path('logs/')
        ])->loadView('pdf.billing', compact('billings'));
        $content = $pdf->download('billing.pdf')->getOriginalContent();
        $path = 'public/pdf/invoice.pdf';
        if(Storage::put($path, $content)){   
            return url(Storage::url($path));
        }
    
    }
}
