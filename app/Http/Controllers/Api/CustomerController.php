<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Http\Controllers\Controller;
use App\Models\Bookmark;
use App\Models\Category;
use App\Models\ContactSupport;
use App\Models\User;
use App\Models\Setting;
use App\Models\Video;
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
            return $this->error('Something went wrong','Something went wrong');
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
                $data['old_password'][] = "Old password is wrong";
                return $this->error($data,$can_not_find,403);  
            }

            $user_data->update(['password' => bcrypt($request->new_password)]);

            return $this->success([], 'Password change successfully');
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 'Exception occur');
        }
    }

    // GET BOOKMARK LIST
    
    public function getBookmarkList(Request $request){
        try{ 
            $user_id = Auth::user()->id;
            $bookmark_video_list = Bookmark::with(['video:id,title'])->where('user_id',$user_id)->paginate($request->input('perPage'), ['*'], 'page', $request->input('page'));
          
            $formattedBookmarkList = $bookmark_video_list->map(function ($bookmark) {
                if ($bookmark->video) {
                    $bookmark->video->makeHidden(['image','video']);
                }
                return $bookmark;
            });

            if(!empty($bookmark_video_list)){
                $data['bookmark_video_list']    = $formattedBookmarkList->values();
                $data['current_page']           = $bookmark_video_list->currentPage();
                $data['per_page']               = $bookmark_video_list->perPage();
                $data['total']                  = $bookmark_video_list->total();
                $data['last_page']              = $bookmark_video_list->lastPage();
                return $this->success($data,'Bookmark list');
            }
            return $this->error('Something went wrong','Something went wrong');
        }catch(Exception $e){
            return $this->error($e->getMessage(),'Exception occur');
        }
        return $this->error('Something went wrong','Something went wrong');
    }

    // ADD TO BOOKMARK

    public function addToBookmark(Request $request){
        try{
            $validateData = Validator::make($request->all(), [
                'video_id' => 'required',
            ]);

            if ($validateData->fails()) {
                return $this->error($validateData->errors(),'Validation error',403);
            }

            $user_id    = Auth::user()->id;
            $video_is_exist_in_bookmark   =  Bookmark::where('user_id',$user_id)->where('video_id',$request->video_id)->first();

            if(!empty($video_is_exist_in_bookmark)){
                $video_is_exist_in_bookmark->delete();
                return $this->success([],'Removed from bookmark successfully');
            }

            $bookmark                = new Bookmark();
            $bookmark->user_id       = Auth::user()->id;
            $bookmark->video_id      = $request->video_id;
            $bookmark->video_title   = Video::where('id',$request->video_id)->pluck('title')->first();
            $bookmark->save();

            return $this->success([],'Added to bookmark successfully');
        }catch(Exception $e){
            return $this->error($e->getMessage(),'Exception occur');
        }
        return $this->error('Something went wrong','Something went wrong');
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

    public function contactSupportDetail(Request $request){
        try{
            $validateData = Validator::make($request->all(), [
                'support_id' => 'required',
            ]);
    
            if ($validateData->fails()) {
                return $this->error($validateData->errors(), 'Validation error', 403);
            }
            $data['contact_support'] = ContactSupport::where('id',$request->support_id)->first();
            if(!empty($data['contact_support'])){
                return $this->success($data,'Support detail');
            }
            return $this->error([],'Detail not found');
        }catch(Exception $e){
            return $this->error($e->getMessage(),'Exception occur');
        }
        return $this->error('Something went wrong','Something went wrong');
    }

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
    
    // GET CATEGORY LIST

    public function getCategoryList(Request $request){
        try{ 
            $category_list = Category::with('image:id,type_id,file_name,type')->select('id','title','button_title')->paginate($request->input('perPage'), ['*'], 'page', $request->input('page'));
            if(!empty($category_list)){
                $data['category_list'] = $category_list->values();
                $data['current_page']  = $category_list->currentPage();
                $data['per_page']      = $category_list->perPage();
                $data['total']         = $category_list->total();
                $data['last_page']     = $category_list->lastPage();
                
                return $this->success($data,'Category list');
            }
            return $this->error('Something went wrong','Something went wrong');
        }catch(Exception $e){
            return $this->error($e->getMessage(),'Exception occur');
        }
        return $this->error('Something went wrong','Something went wrong');
    }
    
    // GET VIDEO LIST
    
    public function getVideoList(Request $request,$id){
        try{ 
            $video_list = Video::with(['image:id,type_id,file_name,type','video:id,type_id,file_name,type'])->select('id','title','category_id','duration')->where('category_id',$id)->paginate($request->input('perPage'), ['*'], 'page', $request->input('page'));
            if(!empty($video_list)){
                $data['video_list']    = $video_list->values();
                $data['current_page']  = $video_list->currentPage();
                $data['per_page']      = $video_list->perPage();
                $data['total']         = $video_list->total();
                $data['last_page']     = $video_list->lastPage();
                return $this->success($data,'Video list');
            }
            return $this->error('Something went wrong','Something went wrong');
        }catch(Exception $e){
            return $this->error($e->getMessage(),'Exception occur');
        }
        return $this->error('Something went wrong','Something went wrong');
    }
    
    // GET FEATURED LIST
    
    public function getFeaturedList(Request $request){
        try{ 
            $featured_video_list = Video::with(['image:id,type_id,file_name,type','video:id,type_id,file_name,type'])->select('id','title','category_id','duration')->where('is_featured',1)->paginate($request->input('perPage'), ['*'], 'page', $request->input('page'));
            if(!empty($featured_video_list)){
                $data['featured_video_list']    = $featured_video_list->values();
                $data['current_page']           = $featured_video_list->currentPage();
                $data['per_page']               = $featured_video_list->perPage();
                $data['total']                  = $featured_video_list->total();
                $data['last_page']              = $featured_video_list->lastPage();
                return $this->success($data,'Video list');
            }
            return $this->error('Something went wrong','Something went wrong');
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