<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Password as ResetPassword;
use Illuminate\Auth\Events\PasswordReset;

class AuthController extends Controller
{
    function login(Request $request)
    {
        $user = User::where('email',$request->email)->first();
        $message = 'unathorized';
        $check = Hash::check($request->password, $user->password);
        if (!$user || !$check) {
            return response()->json(compact('message'),401);
        }
        $message = 'success';
        $token = $user->createToken('login',['server:update'])->plainTextToken;
        return response()->json(compact('user','token','message'));
    }

    function logout(Request $request)
    {
        if($request->user()->currentAccessToken()->delete()){
            return response()->json([
                'message'=>'berhasil logout'
            ]);
        }
        return response()->json([
            'message'=>'gagal logout'
        ],500);
    }

    function cekid(Request $req)
    {
        $user = User::find(1);
        $user->name = \Illuminate\Support\Str::random(60);
        $user->save();
        return $user;
    }

    function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'password'=>['required',Password::min(7)->symbols()->letters()->numbers(),'confirmed'],
            'email'=>['required','email:rfc,dns','string','unique:users'],
            'name'=>'required'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors());
        }

        $user= new User;
        $user->password = Hash::make($request->password);
        $user->email = $request->email;
        $user->name = $request->name;
        $user->save();
        return $user->makeHidden('id');
    }

    function user(Request $request)
    {
        return response()->json($request->user());
    }

    function password_update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required',
            'password'=>['required',Password::min(7)->symbols()->letters()->numbers(),'confirmed']
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors());
        }
    
        $status = ResetPassword::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                foreach (array_column($user->passlogs->toArray(),'password') as $prev_pass) {
                    if (Hash::check($password, $prev_pass)) {
                        header('Content-Type: application/json');
                        http_response_code(400);
                        echo json_encode(['message'=>'Reset password can\'t the same as the password that has been used!']);exit;
                    }
                }
                
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->setRememberToken(\Illuminate\Support\Str::random(60));
                $user->save();
    
                event(new PasswordReset($user));
            }
        );
        
        if ($status===ResetPassword::PASSWORD_RESET) {
            $message = "password changed successfully!";
            $code = 200;
        }else{
            $message = $status === 'passwords.token' ? 'invalid token!' : 'reset password failed';
            $code = 400;
        }
        
        return response()->json(compact('message'),$code);
    }

    function forgot_password(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $status = ResetPassword::sendResetLink($request->only('email'));
        
        $message = $status===ResetPassword::RESET_LINK_SENT ? 'reset password link sended!' : 'send reset password link failed!';

        return compact('message');
    }
}
