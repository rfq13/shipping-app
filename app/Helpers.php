<?php

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

if (!function_exists('randomNumber')) {
    function randomNumber($length) {
        $result = '';

        for($i = 1; $i < $length+1; $i++) {
            $result .= mt_rand(0, 9);
        }

        return $result;
    }
}

if (!function_exists('getUrlParamsfromStr')) {
    function getUrlParamsfromStr($url) {
        $url_components = parse_url($url);
        parse_str($url_components['query'], $params);
        return $params;
    }
}

if (!function_exists('reqValidate')) {
    function reqValidate($body,$rules,$messages=[]) {
        $validator = Validator::make($body,$rules,$messages);

        if ($validator->fails()) {
            throwJson($validator->errors(),400);
        }
    }
}

if (!function_exists('throwJson')) {
    function throwJson($response,$code=200) {
        header('Content-Type: application/json');
        http_response_code($code);
        echo json_encode($response);
        exit;
    }
}

if (!function_exists('rupiah')) {
    function rupiah($angka){

        $hasil_rupiah = "Rp " . number_format($angka,2,',','.');
        return $hasil_rupiah;
        
    }
}

if (!function_exists('updateBalance')) {
    function updateBalance($transaction){
        DB::beginTransaction();
        try {

            $balance = new \App\Models\WalletBalance;
            $balance->type = $transaction['type'];
            $balance->user_id = $transaction['user_id'];
            $balance->destination_id = $transaction['destination_id'] ?? 0;
            $balance->amount = $transaction['amount'];
            $balance->description = $transaction['description'];
            $balance->save();

            DB::commit();
            return ['success'=>true];
        } catch (\Throwable $th) {
            DB::rollBack();

            return ['success'=>false,'message'=>$th->getMessage(),'line'=>$th->getLine()];
        }
        
    }
}