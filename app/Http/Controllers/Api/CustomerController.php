<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Http\Controllers\Controller;
use App\Models\Bookmark;
use App\Models\Category;
use App\Models\ContactSupport;
use App\Models\Notification;
use App\Models\Playlist;
use App\Models\PlaylistDetail;
use App\Models\User;
use App\Models\Setting;
use App\Models\Video;
use App\Models\WatchVideoDuration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use File; 
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
            $bookmark_video_list = Bookmark::with(['video:id,title,category_id,unique_id,can_view_free_user,duration'])->where('user_id',$user_id)->paginate($request->input('perPage'), ['*'], 'page', $request->input('page'));
            $formattedBookmarkList = $bookmark_video_list->map(function ($bookmark) {
                if ($bookmark->video) {
                    $bookmark->category_title = $bookmark->video->category->title ?? null;
                    $bookmark->video->makeHidden(['image','video','category']);
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

    // CREATE PLAYLIST

    public function createPlayList(Request $request){
        try{
            $validateData = Validator::make($request->all(), [
                'title' =>  [
                                'required', Rule::unique('playlists')->where(function ($query) use($request) {
                                                return $query->where('user_id',  Auth::user()->id);
                                            })
                            ]
            ]);

            if ($validateData->fails()) {
                return $this->error($validateData->errors(),'Validation error',403);
            }

            $playlist                = new Playlist();
            $playlist->user_id       = Auth::user()->id;
            $playlist->title         = $request->title;
            $playlist->save();

            if(isset($request->video_id)){
                $playlistDetail                = new PlaylistDetail();
                $playlistDetail->user_id       = Auth::user()->id;
                $playlistDetail->playlist_id   = $playlist->id;
                $playlistDetail->video_id      = $request->video_id;
                $playlistDetail->video_title   = Video::where('id',$request->video_id)->pluck('title')->first();
                $playlistDetail->save();
            }
            return $this->success([],'Create playlist successfully');
        }catch(Exception $e){
            return $this->error($e->getMessage(),'Exception occur');
        }
        return $this->error('Something went wrong','Something went wrong');
    }
    
    // GET PLAYLIST
    
    public function getPlayList(Request $request){
        try{ 
            $user_id = Auth::user()->id;
            $palylist = Playlist::where('user_id',$user_id)->paginate($request->input('perPage'), ['*'], 'page', $request->input('page'));
         
            if(!empty($palylist)){
                $data['palylist']               = $palylist->values();
                $data['current_page']           = $palylist->currentPage();
                $data['per_page']               = $palylist->perPage();
                $data['total']                  = $palylist->total();
                $data['last_page']              = $palylist->lastPage();
                return $this->success($data,'Playlist list');
            }
            return $this->error('Something went wrong','Something went wrong');
        }catch(Exception $e){
            return $this->error($e->getMessage(),'Exception occur');
        }
        return $this->error('Something went wrong','Something went wrong');
    }

    // GET PLAYLIST DETAIL
    
    public function PlayListDetail(Request $request){
        try{ 
            $user_id     = Auth::user()->id;
            $playlist_id = $request->playlist_id;
            $playlist_video_list = PlaylistDetail::with(['video:id,title,category_id,unique_id,can_view_free_user'])->where('playlist_id',$playlist_id)->where('user_id',$user_id)->paginate($request->input('perPage'), ['*'], 'page', $request->input('page'));
            $formattedPlayList = $playlist_video_list->map(function ($playlist) {
                if ($playlist->video) {
                    $playlist->category_title = $playlist->video->category->title ?? null;
                    $playlist->video->makeHidden(['image','video','category']);
                }
                return $playlist;
            });

            if(!empty($playlist_video_list)){
                $data['playlist_video_list']    = $playlist_video_list->values();
                $data['current_page']           = $playlist_video_list->currentPage();
                $data['per_page']               = $playlist_video_list->perPage();
                $data['total']                  = $playlist_video_list->total();
                $data['last_page']              = $playlist_video_list->lastPage();
                return $this->success($data,'Playlist Video list');
            }
            return $this->error('Something went wrong','Something went wrong');
        }catch(Exception $e){
            return $this->error($e->getMessage(),'Exception occur');
        }
        return $this->error('Something went wrong','Something went wrong');
    }

    // ADD TO PLAYLIST

    public function addToPlaylist(Request $request){
        try{
            $validateData = Validator::make($request->all(), [
                'playlist_id' => 'required',
                'video_id'    => 'required',
            ]);

            if ($validateData->fails()) {
                return $this->error($validateData->errors(),'Validation error',403);
            }

            $user_id    = Auth::user()->id;
            $video_is_exist_in_playlist   =  PlaylistDetail::where('user_id',$user_id)->where('playlist_id',$request->playlist_id)->where('video_id',$request->video_id)->first();

            if(!empty($video_is_exist_in_playlist)){
                $video_is_exist_in_playlist->delete();
                return $this->success([],'Removed from playlist successfully');
            }

            $playlist                = new PlaylistDetail();
            $playlist->user_id       = Auth::user()->id;
            $playlist->playlist_id   = $request->playlist_id;
            $playlist->video_id      = $request->video_id;
            $playlist->video_title   = Video::where('id',$request->video_id)->pluck('title')->first();
            $playlist->save();

            return $this->success([],'Added to playlist successfully');
        }catch(Exception $e){
            return $this->error($e->getMessage(),'Exception occur');
        }
        return $this->error('Something went wrong','Something went wrong');
    }

    // DELETE PLAYLIST

    public function deletePlayList(Request $request){
        try{ 
            $playlist_id = $request->playlist_id;
            $playlist    = Playlist::where('id',$playlist_id)->delete();
            $playlist_video_list = PlaylistDetail::where('playlist_id',$playlist_id)->get();
            
            if(!empty($playlist_video_list)){
                $playlist_video_list->each(function($item) {
                    $item->delete();
                });
            }
            return $this->success([],'Playlist deleted successfully');
        }catch(Exception $e){
            return $this->error($e->getMessage(),'Exception occur');
        }
        return $this->error('Something went wrong','Something went wrong');
    }

    // CATEGORY NAME

    public function getCategoryName(Request $request){
        try{ 
            $data['category_list'] = Category::select('id','title')->get();
            return $this->success($data,'Category list');
        }catch(Exception $e){
            return $this->error($e->getMessage(),'Exception occur');
        }
        return $this->error('Something went wrong','Something went wrong');
    }
  
    // VIDEO NAME

    public function getVideoName($id){
        try{ 
            $data['video_list'] = Video::select('id','title')->where('category_id',$id)->get()->makeHidden(['image','video','thumbnail_image_url','video_url']);
            return $this->success($data,'Video list');
        }catch(Exception $e){
            return $this->error($e->getMessage(),'Exception occur');
        }
        return $this->error('Something went wrong','Something went wrong');
    }

    // GET STATISTICS OF USER

    public function getStatistics(Request $request)
    {
        try {
            $validateData = Validator::make($request->all(), [
                'start_date' => 'required|date_format:Y-m-d|before_or_equal:end_date',
                'end_date'   => 'required|date_format:Y-m-d|after_or_equal:start_date',
            ]);

            if ($validateData->fails()) {
                return $this->error($validateData->errors(), 'Validation error', 403);
            }

            $start_date = $request->start_date;
            $end_date   = $request->end_date;
            $datetime1  = new \DateTime($start_date);
            $datetime2  = new \DateTime($end_date);
            $interval   = $datetime1->diff($datetime2);
            $days = $interval->format('%a') + 1;

            $query = WatchVideoDuration::where('user_id', Auth::id())->whereBetween('play_date', [$start_date, $end_date])->select('play_date',\DB::raw("CAST(SUM(duration) AS UNSIGNED) as total_duration_in_second"),\DB::raw('MONTH(play_date) month'),\DB::raw('MONTHNAME(play_date) as month_name'),\DB::raw('DAYNAME(play_date) as day_name'));
            $query = $query->when($request->has('category_id'), function ($query) use ($request) {
                return $query->where('category_id', $request->category_id);
            });
            $query = $query->when($request->has('video_id'), function ($query) use ($request) {
                return $query->where('video_id', $request->video_id);
            });
            // $data['statistics_data'] = $query->select('*', \DB::raw("CONCAT(
            //     LPAD(FLOOR(duration / 3600), 2, '0'), 'h ',
            //     LPAD(FLOOR((duration % 3600) / 60), 2, '0'), 'm ',
            //     LPAD(duration % 60, 2, '0'), 's'
            // ) as formatted_time"))->groupBy('play_date')->get();
            if($days <= 7){
                $data['statistics_data'] = $query->groupBy('play_date')->get();
            }else{
                $data['statistics_data'] = $query->groupby('month')->get();
            }

            $grandTotal = $data['statistics_data']->sum('total_duration_in_second');
            $data['total_watch_time_hr'] = $grandTotal/3600;
            $data['total_watch_time']    = sprintf(
                '%02dh %02dm %02ds',
                floor($grandTotal / 3600),
                floor(($grandTotal % 3600) / 60),
                $grandTotal % 60
            );

            $data['avg_watch_time'] = sprintf(
                '%02dh %02dm %02ds',
                floor(($grandTotal/$days) / 3600),
                floor((($grandTotal/$days) % 3600) / 60),
                ($grandTotal/$days) % 60
            );
            $data['day_diff'] = $days;

            return $this->success($data, 'Statistics data');
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 'Exception occur');
        }
        return $this->error('Something went wrong', 'Something went wrong');
    }
    
    // SEARCH VIDEO

    public function searchVideo(Request $request){
        try{ 
            $validateData = Validator::make($request->all(), [
                'searched_title' => 'required',
            ]);

            if ($validateData->fails()) {
                return $this->error($validateData->errors(),'Validation error',403);
            }
            $query = new Video();
            $query = $query->when($request->has('category_id'), function ($query) use ($request) {
                return $query->where('category_id', $request->category_id);
            });
            $query = $query->when($request->has('start_time'), function ($query) use ($request) {
                $start_time = isset($request->start_time) ? (int)$request->start_time : -1;
                $end_time =  isset($request->end_time) ? (int)$request->end_time : -1;
                if ($start_time >= 0 && $end_time >= 0) {
                    return $query->whereRaw("TIME_TO_SEC(duration) >= $start_time AND TIME_TO_SEC(duration) <= $end_time");
                }else{
                    return $query->whereRaw("TIME_TO_SEC(duration) > $start_time");
                }
            });
            
            $video_list = $query->where('title','LIKE','%'.$request->searched_title.'%')->paginate($request->input('perPage'), ['*'], 'page', $request->input('page'));
            $formattedVideoList = $video_list->map(function ($videos) {
                if ($videos->video) {
                    $videos->category_title = $videos->category->title ?? null;
                    $videos->makeHidden(['image','video','category']);
                }
                return $videos;
            });
            
            if(!empty($video_list)){
                $data['video_list']             = $video_list->values();
                $data['current_page']           = $video_list->currentPage();
                $data['per_page']               = $video_list->perPage();
                $data['total']                  = $video_list->total();
                $data['last_page']              = $video_list->lastPage();
            }
            return $this->success($data,'Video suggestion list');
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
    
    // NOTIFICATION LIST

    public function notificationList(Request $request){
        try{
            $notification_id  = Notification::where('receiver_id',Auth::id())->orderBy('id','desc')->take(30)->pluck('id')->toArray();
            Notification::whereNotIn('id', $notification_id)->where('receiver_id',Auth::id())->delete();

            $notification_data  = Notification::where('receiver_id',Auth::id())->orderBy('id','desc')->take(30)->get();
            $data['notification_data'] = $notification_data->map(function ($notification){
                $date = date('d/m/Y', strtotime($notification->created_at));

                if($date == date('d/m/Y')) {
                    $notification->date = 'Today';
                }else if($date == date('d/m/Y', strtotime('-1 day'))) {
                    $notification->date = 'Yesterday';
                }else{
                    $notification->date = date('d M', strtotime($notification->created_at));
                }

                $notification_cus_data = json_decode($notification->data,true);
                if (isset($notification_cus_data['image'])) {
                    $imageName = basename(parse_url($notification_cus_data['image'], PHP_URL_PATH));
                    $folderPath = public_path('video');
                    $notification['image'] = file_exists($folderPath . '/' . $imageName) ? $notification_cus_data['image'] : asset('images/meditation.png');
                } else {
                    $notification['image'] = asset('images/meditation.png');
                }
                return $notification;
            })->values();

            return $this->success($data,'Notification data');
        }catch(Exception $e){
            return $this->error($e->getMessage(),'Exception occur');
        }
        return $this->error('Something went wrong','Something went wrong');
    }
    
    // NOTIFICATION READ
    
    public function notificationRead(){
        try{
            Notification::where('receiver_id',Auth::id())->update(['status'=>1]);
            return $this->success([],'Notification read successfully');
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
            $video_list = Video::with(['image:id,type_id,file_name,type','video:id,type_id,file_name,type','category:id,title','userBookmarks'])->select('id','title','category_id','duration','unique_id','can_view_free_user','video_type')->where('category_id',$id)->paginate($request->input('perPage'), ['*'], 'page', $request->input('page'));
            $transformed_video_list  = $video_list->getCollection()->transform(function ($item) {
                $item->category_title = $item->category->title; 
                $item->is_bookmark    = $item->userBookmarks->isNotEmpty() ? true : false;
                unset($item->category);  
                unset($item->userBookmarks);  
                return $item;
            });
            if(!empty($video_list)){
                $data['video_list']    = $transformed_video_list->values();
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
    
    // PLAY VIDEO

    public function playVideo($id){
        try{
            $video   =  Video::where('unique_id',$id)->first();
            if(!empty($video)){
                $data['can_play'] = 1;
                $data['video'] = $video;
                if($video->can_view_free_user == 0){
                    $data['can_play'] = 0;
                    $data['video'] = null;
                }
                return $this->success($data,'Video details');
            }
            return $this->error([],'Video not found');
        }catch(Exception $e){
            return $this->error($e->getMessage(),'Exception occur');
        }
        return $this->error('Something went wrong','Something went wrong');
    }

    // STORE WATCH DURATION

    public function storeWatchedVideoDuration(Request $request){
        try{
            $validateData = Validator::make($request->all(), [
                'video_id' => 'required',
                'duration' => 'required',
            ]);

            if ($validateData->fails()) {
                return $this->error($validateData->errors(),'Validation error',403);
            }

            $user_id     = Auth::user()->id;
            $video_id    = $request->video_id;
            $today_date  = date('Y-m-d');
            
            $watch_video_duration = WatchVideoDuration::where('user_id',$user_id)->where('video_id',$video_id)->where('play_date',$today_date)->first();
            if(!empty($watch_video_duration)){
                $watch_video_duration->duration = $watch_video_duration->duration + $request->duration;
                $watch_video_duration->update();
            }else{
                $category_id = Video::where('id',$video_id)->pluck('category_id')->first();
                $video_duraiton_store               = new WatchVideoDuration();
                $video_duraiton_store->user_id      = $user_id;
                $video_duraiton_store->video_id     = $video_id;
                $video_duraiton_store->category_id  = $category_id;
                $video_duraiton_store->duration     = $request->duration;
                $video_duraiton_store->play_date    = $today_date;
                $video_duraiton_store->save();
            }

            return $this->success([],'Watch video added successfully');
        }catch(Exception $e){
            return $this->error($e->getMessage(),'Exception occur');
        }
        return $this->error('Something went wrong','Something went wrong');
    }

    // GET FEATURED LIST
    
    public function getFeaturedList(Request $request){
        try{ 
            $featured_video_list = Video::with(['image:id,type_id,file_name,type','video:id,type_id,file_name,type'])->select('id','title','category_id','duration','unique_id','can_view_free_user')->where('is_featured',1)->paginate($request->input('perPage'), ['*'], 'page', $request->input('page'));
            $transformed_video_list  = $featured_video_list->getCollection()->transform(function ($item) {
                $item->category_title = $item->category->title; 
                $item->is_bookmark    = $item->userBookmarks->isNotEmpty() ? true : false;
                unset($item->category);  
                unset($item->userBookmarks);  
                return $item;
            });
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
