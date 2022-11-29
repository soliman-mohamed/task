<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{

    public function register(Request $request)
    {
        $validator = validator()->make($request->all(), [
            'name' => 'required|string|max:255',
            'phone' => 'required|string|unique:users',
            'password' => 'required|string|min:6|confirmed'
        ]);
        if ($validator->fails()) {
            $errors = $validator->errors();
            $error_data = [];
            foreach ($errors->all() as $error) {
                array_push($error_data, $error);
            }
            $data = $error_data;
            $response = [
                'status' => false,
                'error' => $data,
            ];
            return response()->json($response);
        }

        try {
            $user = User::create([
                'name' => $request->name,
                'password' => bcrypt($request->password),
                'phone' => $request->phone,
                'verified' => 1,
            ]);

            return response()->json([
                'status' => true,
                'token' => $user->createToken('API Token')->plainTextToken,
                'user' => $user
            ]);
        }catch (\Exception $e) {
            return response()->json([
               'status' => false,
                'msg' => $e->getMessage()
            ]);
        }
    }

    public function login(Request $request)
    {
        $validator = validator()->make($request->all(), [
            'phone' => 'required|string',
            'password' => 'required'
        ]);
        if ($validator->fails()) {
            $errors = $validator->errors();
            $error_data = [];
            foreach ($errors->all() as $error) {
                array_push($error_data, $error);
            }
            $data = $error_data;
            $response = [
                'status' => false,
                'error' => $data,
            ];
            return response()->json($response);
        }

        try {
            $user = User::where('phone', $request->phone)->first();
            if (!is_null($user)) {
                if (!$user->verified){
                    return response()->json([
                       'status' => false,
                       'msg' => 'User is not verified'
                    ]);
                }
                // user exits lets check password
                if (Hash::check($request->password, $user->password)) {
                    if (Auth::attempt($request->only('phone', 'password'))) {
                        return response()->json([
                            'status'=>true,
                            'token' => auth()->user()->createToken('API Token')->plainTextToken,
                            'user' => auth()->user()
                        ], 200);
                    } else {
                        return response()->json(['status' => false, 'msg' => 'Unauthorized'], 200);
                    }
                }else {
                    // password not match
                    return response()->json(['status' => false, 'msg' => 'invalid password'], 200);
                }
            }else{
                return response()->json(['status' => false, 'msg' => 'invalid phone'], 200);
            }
        } catch (\Exception $exception) {
            return response()->json([
                'status'=>false,
                'msg' => $exception->getMessage()
            ], 200);
        }
    }

    public function profile(Request $request){
        try {
            $user = $request->user();
            if ($user){
                return response()->json([
                    'status' => false,
                    'msg' => $user
                ]);
            }else{
                return response()->json([
                   'status' => false,
                   'msg' => 'no profile available'
                ], 200);
            }
        }catch (\Exception $e){
            return response()->json([
               'status' => false,
               'msg' => $e->getMessage()
            ]);
        }
    }

    public function logout()
    {
        auth()->user()->tokens()->delete();

        return [
            'message' => 'Tokens Revoked'
        ];
    }

    public function verifyAccount(Request $request){
        $validator = validator()->make($request->all(), [
            'code' => 'required|string|max:255'
        ]);
        if ($validator->fails()) {
            $errors = $validator->errors();
            $error_data = [];
            foreach ($errors->all() as $error) {
                array_push($error_data, $error);
            }
            $data = $error_data;
            $response = [
                'status' => false,
                'error' => $data,
            ];
            return response()->json($response);
        }

        try {
            $user = User::where('verify_code', $request->code)->first();
            if ($user){
                $user->verified = 1;
                $user->verify_code = null;
                $user->save();
                return response()->json([
                    'status' => true,
                    'msg' => 'User has verified'
                ], 200);
            }else{
                return response()->json([
                   'status' => false,
                   'msg' => 'invalid code'
                ], 200);
            }
        }catch (\Exception $e){
            return response()->json([
                'status' => false,
                'msg' => $e->getMessage()
            ]);
        }
    }

    public function changeCode(Request $request){
        $validator = validator()->make($request->all(), [
            'phone' => 'required|string|max:255'
        ]);
        if ($validator->fails()) {
            $errors = $validator->errors();
            $error_data = [];
            foreach ($errors->all() as $error) {
                array_push($error_data, $error);
            }
            $data = $error_data;
            $response = [
                'status' => false,
                'error' => $data,
            ];
            return response()->json($response);
        }
        try {
            $user = User::where('phone', $request->phone)->first();
            if ($user){
                $user->verify_code = rand(11111, 99999);
                $user->save();
                return response()->json([
                    'status' => true,
                    'msg' => 'code has Changed' // send mail || sms ...!
                ], 200);
            }else{
                return response()->json([
                   'status' => false,
                   'msg' => 'invalid phone'
                ], 200);
            }
        }catch (\Exception $e){
            return response()->json([
                'status' => false,
                'msg' => $e->getMessage()
            ]);
        }
    }
}
