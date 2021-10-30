<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\WithdrawalAccount;
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
            'description' => $request->description,
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
            'description' => $request->description,
            'amount' => -$request->amount,
            'user_id' => $request->user()->id,
            'type' => env('WALLET_TRANSFER'),
            'destination'=>$destination->id
        ];
        $saldo_sc = updateBalance($transfer_sc);
        
        
        // dst mean destination
        $transfer_dst = [
            'description' => $request->description,
            'amount' => $request->amount,
            'user_id' => $destination->id,
            'type' => env('WALLET_RECEIVE'),
            'destination' => $request->user()->id
        ];
        $saldo_dst = updateBalance($transfer_dst);

        if ($saldo_sc['success'] && $saldo_dst['success']) {
            return response()->json(["message"=>"transfer successfully!"]);
        }
    }

    function withdraw(Request $request)
    {
        reqValidate($request->all(),[
            'amount'=>'required|numeric',
            'bank_id'=>'required|numeric'
        ]);

        $acc = WithdrawalAccount::where([
            'user_id'=>$request->user()->id,
            'id'=>$request->bank_id
        ])
        ->first();

        $check = $acc && $request->user()->balances->sum('amount') >= $request->amount;

        if(!$check) return response()->json(['message'=>'transfer failed! make sure your balance is sufficient and bank account is appropriate'],401);

        $withdrawal = [
            'description' => $request->description,
            'amount' => -$request->amount,
            'destination' => $request->bank_id,
            'user_id' => $request->user()->id,
            'type' => env('WALLET_WITHDRAW')
        ];

        if (updateBalance($withdrawal)) {
            return response()->json(["message"=>"wthdraw successfully!"]);
        }
    }

    function mutation(Request $request)
    {
        $wallet_mutations = DB::select(DB::raw('
            SELECT 
                (IF(wb.`type`='.env('WALLET_TOPUP').',"topup",
                    IF(wb.`type`='.env('WALLET_TRANSFER').',"transfer",
                        IF(wb.`type`='.env('WALLET_RECEIVE').', "receive",
                            IF(wb.`type`='.env('WALLET_WITHDRAW').',"withdraw",NULL)
                        )
                    )
                )) as type_name,
                (IF(wb.amount < 0, CONCAT("- Rp",fRupiah(wb.amount*-1)),CONCAT("Rp",fRupiah(wb.amount)))) as amount,
                (IF (wb.`type`='.env('WALLET_WITHDRAW').',CONCAT("bank ",wa.`bank`," ",wa.`number`) ,u.wallet_number)) as destination,
                wb.description,
                wb.created_at, 
                wb.updated_at
            FROM  test_shipping_app.wallet_balances wb
            LEFT JOIN test_shipping_app.users u on u.id = wb.destination
            LEFT JOIN test_shipping_app.withdrawal_accounts wa on wa.id = wb.destination
            WHERE wb.user_id ='.$request->user()->id.'
        '));

        return response()->json(compact('wallet_mutations'));
    }

    function getWithdrawalAcc(Request $request)
    {
        throwJson($request->user()->withdrawAccounts);
    }

    function addWithdrawalAcc(Request $request)
    {
        reqValidate($request->all(),[
            'bank'=>'required|regex:/^[a-zA-Z]+$/u',
            'number'=>'required|numeric'
        ]);

        $wd_account = new WithdrawalAccount;
        $wd_account->bank = $request->bank;
        $wd_account->number = $request->number;
        $wd_account->user_id = $request->user()->id;
        $wd_account->save();

        throwJson($wd_account);
    }
}
