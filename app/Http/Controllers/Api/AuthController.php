<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use Illuminate\Http\Request;
use App\Models\Temp;
use App\Models\User;
use Exception;
use Validator;

class AuthController extends BaseController
{
    //
     
    // SEND OTP FOR REGISTRATION, RESEND OTP, FORGOT PASSWORD

    public function sendOtp(Request $request){
        try{
            
            $otp    = substr(number_format(time() * rand(),0,'',''),0,4);
            $data   = [];
            $data['otp'] = (int)$otp;

            $validateData = Validator::make($request->all(), [ 
                'phone_no' => 'required|regex:/^([0-9\s\-\+\(\)]*)$/',
                'type' => 'required',
            ]);

            if ($validateData->fails()) {
                return $this->error($validateData->errors(),'Validation error',403);
            } 
            
            $is_user_exist = User::where('phone_no',$request->phone_no)->count();
            
            if($request->type == 'register' &&  $is_user_exist == 1){
                $can_not_find = "Account already exists with " . $request->phone_no;
                return $this->error($can_not_find,$can_not_find);
            }

            if($request->type == 'forgot_pwd' &&  $is_user_exist == 0){
                $can_not_find = "Sorry we can not find data with this credentials";
                return $this->error($can_not_find,$can_not_find);
            }

            $key         = $request->phone_no;
     
            $temp         = Temp::firstOrNew(['key' => $key]);
            $temp->key    = $key;
            $temp->value  = $otp;
            $temp->save();
            
            return $this->success($data,'OTP send successfully');

        }catch(Exception $e){
            return $this->error($e->getMessage(),'Exception occur');
        }
        return $this->error('Something went wrong','Something went wrong');
    }

    // VERIFY OTP 

    public function verifyOtp(Request $request){
        try{
            $validateData = Validator::make($request->all(), [
                'phone_no' => 'required',
                'otp' => 'required',
            ]);

            if ($validateData->fails()) {
                return $this->error($validateData->errors(),'Validation error',403);
            } 

            $temp         = Temp::where('key',$request->phone_no)->first();
            if($temp != null){
                $is_data_present = Temp::where('key',$request->phone_no)->where('value',$request->otp)->first();
                if($is_data_present != null){
                    $is_data_present->delete();
                    $data = [];
                    $data['phone_no'] = $request->phone_no;
                    $data['otp']      = (int)$request->otp;
                    return $this->success($data,'OTP verified successfully');
                }
                return $this->error('OTP is wrong','OTP is wrong');
            } 

            $can_not_find = "Sorry we can not find data with this credentials";
            return $this->error($can_not_find,$can_not_find);

        }catch(Exception $e){
            return $this->error($e->getMessage(),'Exception occur');
        }
        return $this->error('Something went wrong','Something went wrong');
    }
    
    // RESET PASSWORD 

    public function resetPassword(Request $request){
        try{
            $validateData = Validator::make($request->all(), [
                'phone_no'         => 'required',
                'password'         => 'required',
            ]);

            if ($validateData->fails()) {
                return $this->error($validateData->errors(),'Validation error',403);
            } 

            $user_data = User::where('phone_no',$request->phone_no)->first();
            
            if(!empty($user_data)){
                $user_data->password = bcrypt($request->password);
                $user_data->save();
                return $this->success([],'Password change successfully');
            }

            $can_not_find = "Sorry we can not find data with this credentials";
            return $this->error($can_not_find,$can_not_find);

        }catch(Exception $e){
            return $this->error($e->getMessage(),'Exception occur');
        }
        return $this->error('Something went wrong','Something went wrong');
    }

    // REGISTER

    public function register(Request $request){
        try{
            $validateData = Validator::make($request->all(), [
                'name'             => 'required',
                'email'            => 'required|email|unique:users,email|max:255',
                'phone_no'         => 'required|string|unique:users,phone_no|max:20',
                'birth_date'       => 'required',
                'gender'           => 'required',
                'password'         => 'required',
            ]);

            if ($validateData->fails()) {
                return $this->error($validateData->errors(),'Validation error',403);
            } 

           $input              = $request->all();
           $input['password']  = bcrypt($input['password']); 
           $input['gender']    = strtolower($input['gender']);  
           $input['user_type'] = 'user';  

           $user_data          = User::create($input);
           $data['token']      = $user_data->createToken('Auth token')->accessToken;
           return $this->success($data,'Registered successfully');

        }catch(Exception $e){
            return $this->error($e->getMessage(),'Exception occur');
        }
        return $this->error('Something went wrong','Something went wrong');
    }
    
    // LOGIN

    public function login(Request $request){
        try{
            $validateData = Validator::make($request->all(), [
                'phone_no'         => 'required|string|max:20',
                'password'         => 'required',
            ]);

            if ($validateData->fails()) {
                return $this->error($validateData->errors(),'Validation error',403);
            } 

            $is_user_exist = User::where('phone_no',$request->phone_no)->count();
          
            if($is_user_exist == 0){
                $can_not_find = "Sorry we can not find data with this credentials";
                return $this->error($can_not_find,$can_not_find);
            }

            $credential_data = [
                'phone_no' => $request->phone_no,
                'password' => $request->password
            ];

            if (auth()->attempt($credential_data)) {
                $data['token'] = auth()->user()->createToken('Auth token')->accessToken;
                return $this->success($data,'Login successfully');
            } 

            return $this->error('Wrong credentials','Wrong credentials');
        }catch(Exception $e){
            return $this->error($e->getMessage(),'Exception occur');
        }
        return $this->error('Something went wrong','Something went wrong');
    }
}
