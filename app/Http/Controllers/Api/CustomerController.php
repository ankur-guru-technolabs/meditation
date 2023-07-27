<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Http\Controllers\Controller;
use App\Models\ContactSupport;
use App\Models\User;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Helper; 
use Validator;
use Exception;

class CustomerController extends BaseController
{
    //

    // GET LOGGED IN USER PROFILE

    public function getProfile(Request $request){
        try{ 
           $user_data = User::where('id',Auth::user()->id)->first();
            if(!empty($user_data)){
                $data['user_data'] = $user_data;
                return $this->success($data,'User profile data');
            }
        }catch(Exception $e){
            return $this->error($e->getMessage(),'Exception occur');
        }
        return $this->error('Something went wrong','Something went wrong');
    }

    // UPDATE PROFILE

    public function updateProfile(Request $request){
        try{
            $user_data = Auth::user();

            $validateData = Validator::make($request->all(), [
                'name'             => 'required',
                'email'            => 'required|email|max:255|unique:users,email,'.$user_data->id,
                'birth_date'       => 'required',
                'gender'           => 'required',
            ]);

            if ($validateData->fails()) {
                return $this->error($validateData->errors(),'Validation error',403);
            } 

            $input              = $request->all();
            $input['gender']    = strtolower($input['gender']);  
            $user_data->update($input);

           return $this->success([],'Profile update successfully');

        }catch(Exception $e){
            return $this->error($e->getMessage(),'Exception occur');
        }
        return $this->error('Something went wrong','Something went wrong');
    }

    // UPDATE PASSWORD

    public function updatePassword(Request $request){
        try {
            $validateData = Validator::make($request->all(), [
                'old_password' => 'required',
                'new_password' => 'required',
            ]);
    
            if ($validateData->fails()) {
                return $this->error($validateData->errors(), 'Validation error', 403);
            }
    
            $user_data = Auth::user();
    
            if (!\Hash::check($request->old_password, $user_data->password)) {
                $can_not_find = "Old password is wrong";
                return $this->error($can_not_find); // Adjust the status code as needed
            }

            $user_data->update(['password' => bcrypt($request->new_password)]);

            return $this->success([], 'Password change successfully');
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 'Exception occur');
        }
    }

    // CONTACT SUPPORT 

    public function contactSupport(Request $request){
        try{
            $validateData = Validator::make($request->all(), [
                'name' => 'required',
                'email' => 'required',
                'description' => 'required',
            ]);

            if ($validateData->fails()) {
                return $this->error($validateData->errors(),'Validation error',403);
            }

            $support                = new ContactSupport();
            $support->user_id       = Auth::user()->id;
            $support->name          = $request->name;
            $support->email         = $request->email;
            $support->description   = $request->description;
            $support->save();

            return $this->success([],'Request added successfully');
        }catch(Exception $e){
            return $this->error($e->getMessage(),'Exception occur');
        }
        return $this->error('Something went wrong','Something went wrong');
    }
   
    // CONTACT SUPPORT LIST 

    public function contactSupportList(Request $request){
        try{
            $data['contact_support'] = ContactSupport::where('user_id',Auth::user()->id)->get();
            return $this->success($data,'Support list');
        }catch(Exception $e){
            return $this->error($e->getMessage(),'Exception occur');
        }
        return $this->error('Something went wrong','Something went wrong');
    }
   
    // CONTACT SUPPORT DETAIL 

    // public function contactSupportDetail(Request $request){
    //     try{
    //         $data['contact_support'] = ContactSupport::where('user_id',Auth::user()->id)->get();
    //         return $this->success($data,'Support list');
    //     }catch(Exception $e){
    //         return $this->error($e->getMessage(),'Exception occur');
    //     }
    //     return $this->error('Something went wrong','Something went wrong');
    // }

    // STATIC PAGE DATA

    public function staticPage(Request $request){
        try{
            $data['static_page_data']  = Setting::all();
            return $this->success($data,'Static page data');
        }catch(Exception $e){
            return $this->error($e->getMessage(),'Exception occur');
        }
        return $this->error('Something went wrong','Something went wrong');
    }

    // NOTIFICATION SETTING

    public function notificationSetting(){
        try{
            $user_data = User::where('id',Auth::id())->first();
            if($user_data['is_notification_mute'] == '0'){
                $user_data['is_notification_mute'] = '1';
                $user_data->save();
                return $this->success([],'Notification disable successfully');
            }

            if($user_data['is_notification_mute'] == 1){
                $user_data['is_notification_mute'] = 0;
                $user_data->save();
                return $this->success([],'Notification enable successfully');
            }
        }catch(Exception $e){
            return $this->error($e->getMessage(),'Exception occur');
        }
        return $this->error('Something went wrong','Something went wrong');
    }

    // USER LOGOUT

    public function logout(){
        try{
            if (Auth::user()) {
                User::where('id',Auth::id())->update(['fcm_token' => null]);
                $user = Auth::user()->token();
                $user->revoke();
                return $this->success([],'You are successfully logout');
            }
            return $this->error('Something went wrong','Something went wrong');
        }catch(Exception $e){
            return $this->error($e->getMessage(),'Exception occur');
        }
        return $this->error('Something went wrong','Something went wrong');
    }
    
}
