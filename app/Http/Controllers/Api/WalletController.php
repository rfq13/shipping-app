<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WalletController extends Controller
{
    function topup(Request $request)
    {
        reqValidate($request->all(),[
            'amount'=>'required|numeric'
        ]);

        $topup = [
            'description' => "top up ".rupiah($request->amount)." successfully",
            'amount' => $request->amount,
            'user_id' => $request->user()->id,
            'type' => env('WALLET_TOPUP')
        ];

        if (updateBalance($topup)) {
            return response()->json(["message"=>"topUp successfully!"]);
        }

    }

    function balance(Request $request)
    {
        return response()->json(['balance'=>$request->user()->balances->sum('amount')]);
    }

    function transfer(Request $request)
    {
        reqValidate($request->all(),[
            'amount'=>'required|numeric',
            'destination_account'=>'required|numeric'
        ]);

        $destination = User::select('wallet_number','id')
        ->where('wallet_number',$request->destination_account)
        ->first();

        // sc mean source
        $sc = $request->user();
        $check = $destination && ($sc->balances->sum('amount') >= $request->amount) && $sc->wallet_number != $request->destination_account;

        if (!$check) {
            throwJson(['message'=>'transfer failed! make sure your balance is sufficient and the destination wallet account is appropriate'],400);
        }
        
        $transfer_sc = [
            'description' => "transfer saldo to $destination->wallet_number",
            'amount' => -$request->amount,
            'user_id' => $request->user()->id,
            'type' => env('WALLET_TRANSFER'),
            'destination_id'=>$destination->id
        ];
        $saldo_sc = updateBalance($transfer_sc);
        
        
        // dst mean destination
        $transfer_dst = [
            'description' => "receive saldo from ".$request->user()->wallet_number,
            'amount' => $request->amount,
            'user_id' => $destination->id,
            'type' => env('WALLET_RECEIVE')
        ];
        $saldo_dst = updateBalance($transfer_dst);

        if ($saldo_sc['success'] && $saldo_dst['success']) {
            return response()->json(["message"=>"transfer successfully!"]);
        }
    }

    function mutation(Request $request)
    {
        $wallet_mutations = DB::select(DB::raw('
            SELECT 
            (IF(wb.`type`=1,"topup",
                IF(wb.`type`=2,"transfer",
                    IF(wb.`type`=3, "receive",
                        IF(wb.`type`=4,"withdraw",NULL)
                    )
                )
            )) as transaction_type,
            (IF(wb.amount < 0, CONCAT("- Rp",fRupiah(wb.amount*-1)),CONCAT("Rp",fRupiah(wb.amount)))) as amount,
            u.wallet_number as receiver,
            wb.created_at, wb.updated_at
            FROM wallet_balances wb
            LEFT JOIN users u on u.id = wb.destination_id
            WHERE wb.user_id ='.$request->user()->id.'
        '));

        return response()->json(compact('wallet_mutations'));
    }
}
