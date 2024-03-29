<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Http\Controllers\Controller;
use App\Models\Bookmark;
use App\Models\Category;
use App\Models\ContactSupport;
use App\Models\Notification;
use App\Models\Pdf;
use App\Models\Playlist;
use App\Models\PlaylistDetail;
use App\Models\User;
use App\Models\UserSubscription;
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
            $bookmark_video_list = Bookmark::with(['video','video.category' => function ($query) {
                $query->with('image');
            }])->where('user_id',$user_id)->paginate($request->input('perPage'), ['*'], 'page', $request->input('page'));
            $formattedBookmarkList = $bookmark_video_list->map(function ($bookmark) use ($user_id){
                if ($bookmark->video) {
                    $bookmark->category_title = $bookmark->video->category->title ?? null;
                    if($bookmark->video->category->price < 1){
                        $bookmark->video->category->is_purchased = true; 
                    }else if($user_id == 0){
                        $bookmark->video->category->is_purchased = false; 
                    }else{
                        $bookmark->video->category->is_purchased = UserSubscription::where('category_id', $bookmark->video->category->id)->where('user_id', $user_id)->exists();
                    };
                    // $bookmark->video->makeHidden(['image','video','category']);
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

            $query = WatchVideoDuration::where('user_id', Auth::id())->whereBetween('play_date', [$start_date, $end_date])->select('play_date',\DB::raw("CAST(SUM(duration) AS UNSIGNED) as total_duration_in_second"),\DB::raw('MONTH(play_date) month'),\DB::raw('DATE_FORMAT(play_date, "%b") as month_name'),\DB::raw('DATE_FORMAT(play_date, "%a") as day_name'));
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
            
            $data['avg_watch_time_hr'] = $grandTotal/($days*3600);
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
            
            $video_list = $query->with(['category'=> function ($query) {$query->with('image');}])->where('title','LIKE','%'.$request->searched_title.'%')->paginate($request->input('perPage'), ['*'], 'page', $request->input('page'));
            $formattedVideoList = $video_list->map(function ($videos) {
                if ($videos->video) {
                    $videos->category_title = $videos->category->title ?? null;
                    $videos->is_featured    = (int)$videos->is_featured;
                    $videos->is_bookmark      =  $videos->userBookmarks->isNotEmpty() ? true : false;
                    // $videos->makeHidden(['image','video','category']);
                }
                return $videos;
            });
            
            if(!empty($video_list)){
                $data['list']                   = $video_list->values();
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
            $userId = 0;
            if ($request->bearerToken()) {
                $userId = auth('api')->user()->id;
            }
            $category_list = Category::with('image:id,type_id,file_name,type')->select('id','title','button_title','price')->paginate($request->input('perPage'), ['*'], 'page', $request->input('page'));
            if(!empty($category_list)){

                $transformed_category_list  = $category_list->getCollection()->transform(function ($item) use ($userId) {
                    if($item->price < 1){
                        $item->is_purchased = true; 
                    }else if($userId == 0){
                        $item->is_purchased = false; 
                    }else{
                        $item->is_purchased = UserSubscription::where('category_id', $item->id)->where('user_id', $userId)->exists();
                    };
                    return $item;
                });
                 

                $data['category_list'] = $transformed_category_list->values();
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
    
    public function getVideoList(Request $request){
        try{ 

            $validateData = Validator::make($request->all(), [
                'category_id' => 'required',
                'type'    => 'required',
            ]);

            if ($validateData->fails()) {
                return $this->error($validateData->errors(),'Validation error',403);
            } 
            $userId = 0;
            if ($request->bearerToken()) {
                $userId = auth('api')->user()->id;
            }
            $video_list = Video::with(['category'=> function ($query) {$query->with('image');},'image:id,type_id,file_name,type','video:id,type_id,file_name,type','userBookmarks'])->select('id','title','category_id','duration','is_featured','unique_id','can_view_free_user','video_type','created_at','updated_at')->where('category_id',$request->category_id)->where('video_type',$request->type)->paginate($request->input('perPage'), ['*'], 'page', $request->input('page'));
            $transformed_video_list  = $video_list->getCollection()->transform(function ($item) use ($userId){
                $item->category_title = $item->category->title; 
                $item->is_bookmark    = $item->userBookmarks->isNotEmpty() ? true : false;
                $item->is_featured    = (int)$item->is_featured;
                // unset($item->category);
                if($item->category->price < 1){
                    $item->category->is_purchased = true; 
                }else if($userId == 0){
                    $item->category->is_purchased = false; 
                }else{
                    $item->category->is_purchased = UserSubscription::where('category_id', $item->category->id)->where('user_id', $userId)->exists();
                };
                unset($item->userBookmarks);  
                return $item;
            });
            if(!empty($video_list)){
                $data['list']          = $transformed_video_list->values();
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
                $data['access'] = true;
                $data['video'] = $video;
                $purchased_item = UserSubscription::where('user_id',Auth::id())->pluck('category_id')->toArray();
                if($video->video_type == 1 && !in_array($video->category_id,$purchased_item)){
                    $data['access'] = false;
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
            $featured_video_list = Video::with(['category'=> function ($query) {$query->with('image');},'image:id,type_id,file_name,type','video:id,type_id,file_name,type'])->select('id','title','category_id','duration','is_featured','unique_id','can_view_free_user','created_at','updated_at')->where('is_featured',1)->where('video_type',0)->paginate($request->input('perPage'), ['*'], 'page', $request->input('page'));
            $transformed_video_list  = $featured_video_list->getCollection()->transform(function ($item) {
                $item->is_bookmark    = $item->userBookmarks->isNotEmpty() ? true : false; 
                $item->is_featured    = (int)$item->is_featured;
                unset($item->userBookmarks);  
                return $item;
            });
            if(!empty($featured_video_list)){
                $data['list']                   = $featured_video_list->values();
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
    
    // GET PDF LIST
    
    public function getPdfList(Request $request){
        try{ 

            $validateData = Validator::make($request->all(), [
                'category_id' => 'required',
                'type'    => 'required',
            ]);

            if ($validateData->fails()) {
                return $this->error($validateData->errors(),'Validation error',403);
            }

            $userId = 0;
            if ($request->bearerToken()) {
                $userId = auth('api')->user()->id;
            }
            $pdf_list = Pdf::with(['category'=> function ($query) {$query->with('image');},'image:id,type_id,file_name,type','pdf:id,type_id,file_name,type'])->select('id','title','category_id','unique_id','can_view_free_user','pdf_type','created_at','updated_at')->where('category_id',$request->category_id)->where('pdf_type',$request->type)->paginate($request->input('perPage'), ['*'], 'page', $request->input('page'));
            $transformed_pdf_list  = $pdf_list->getCollection()->transform(function ($item) use ($userId){
                $item->category_title = $item->category->title; 
                // unset($item->category); 
                if($item->category->price < 1){
                    $item->category->is_purchased = true; 
                }else if($userId == 0){
                    $item->category->is_purchased = false; 
                }else{
                    $item->category->is_purchased = UserSubscription::where('category_id', $item->category->id)->where('user_id', $userId)->exists();
                };
                return $item;
            });
            if(!empty($pdf_list)){
                $data['list']          = $transformed_pdf_list->values();
                $data['current_page']  = $pdf_list->currentPage();
                $data['per_page']      = $pdf_list->perPage();
                $data['total']         = $pdf_list->total();
                $data['last_page']     = $pdf_list->lastPage();
                return $this->success($data,'Pdf list');
            }
            return $this->error('Something went wrong','Something went wrong');
        }catch(Exception $e){
            return $this->error($e->getMessage(),'Exception occur');
        }
        return $this->error('Something went wrong','Something went wrong');
    }
    
    // GET SHARE LIST
    
    public function getShareContentDetail(Request $request,$id){
        try{ 
            $pdf_list = Pdf::with(['image:id,type_id,file_name,type','pdf:id,type_id,file_name,type','category:id,title'])->select('id','title','category_id','unique_id','can_view_free_user','pdf_type')->where('unique_id',$id)->first();
            $video_list = Video::with(['image:id,type_id,file_name,type','video:id,type_id,file_name,type','category:id,title','userBookmarks'])->select('id','title','category_id','duration','unique_id','can_view_free_user','video_type')->where('unique_id',$id)->first();

            $userId = null;
            $purchased_item  = array();
            if ($request->bearerToken()) {
                $userId = auth('api')->user()->id;
                $purchased_item = UserSubscription::where('user_id',$userId)->pluck('category_id')->toArray();
            }
            if (!empty($pdf_list)) {
                $pdf_list->category_title  = $pdf_list->category->title; 
                unset($pdf_list->category);
                $pdf_list->type = 'pdf';
                $pdf_list->access = true;
                if($pdf_list->pdf_type == 1 && !in_array($pdf_list->category_id,$purchased_item)){
                    $pdf_list->access = false;
                }
                $data['content_detail']  = $pdf_list;
                return $this->success($data,'Pdf list');
            }
            if (!empty($video_list)) {
                $video_list->category_title  = $video_list->category->title; 
                unset($video_list->category);
                $video_list->type = 'video';
                $video_list->access = true;
                if($video_list->video_type == 1 && !in_array($video_list->category_id,$purchased_item)){
                    $video_list->access = false;
                }
                $data['content_detail']  = $video_list;
                return $this->success($data,'Video list');
            }
            return $this->error('Something went wrong','Something went wrong');
        }catch(Exception $e){
            return $this->error($e->getMessage(),'Exception occur');
        }
        return $this->error('Something went wrong','Something went wrong');
    }
 
    
    public function purchaseSubscription(Request $request){
        try{
            $user_id = Auth::id();
            $is_purchased = UserSubscription::where('user_id',$user_id)->where('category_id','=',$request->category_id)->first();
            if($is_purchased === null){
                $plan_data = Category::where('id',$request->category_id)->first();
                $user_subscription                  =  new UserSubscription();
                $user_subscription->user_id         =  $user_id; 
                $user_subscription->category_id     =  $plan_data->id;  
                $user_subscription->title           =  $plan_data->title; 
                $user_subscription->price           =  $plan_data->price; 
                $user_subscription->transaction_id  =  $request->transaction_id; 
                $user_subscription->save(); 

                return $this->success([],'Subscription purchased successfully');
            }
            return $this->error('You have already purchased plan','You have already purchased plan');
        }catch(Exception $e){
            return $this->error($e->getMessage(),'Exception occur');
        }
        return $this->error('Something went wrong','Something went wrong');
    }

    // GET PURCHASE LIST
    
    public function getPurchaseSubscriptionList(Request $request){
        try{ 
            $user_id = Auth::id();
            $category_list = UserSubscription::with(['category'=> function ($query) {$query->with('image:id,type_id,file_name,type');}])->where('user_id',$user_id)->paginate($request->input('perPage'), ['*'], 'page', $request->input('page'));
            if($category_list !== null){
                if(!empty($category_list)){
                    $data['category_list'] = $category_list->values();
                    $data['current_page']  = $category_list->currentPage();
                    $data['per_page']      = $category_list->perPage();
                    $data['total']         = $category_list->total();
                    $data['last_page']     = $category_list->lastPage();
                    
                    return $this->success($data,'Category list');
                }
                return $this->success([],'Subscription purchased list');
            }
            return $this->success('Not purchases any subscription','Not purchases any subscription');
        }catch(Exception $e){
            return $this->error($e->getMessage(),'Exception occur');
        }
        return $this->error('Something went wrong','Something went wrong');
    }
   
    // GET CURRENTLY PROGRESS LIST
    
    public function getCurrentlyProgress(Request $request){
        try{ 
            $user_id = Auth::id();
            $category_list = WatchVideoDuration::with(['category'=> function ($query) {$query->with('image:id,type_id,file_name,type');}])
                            ->join('categories', 'categories.id', '=', 'watch_video_durations.category_id')
                            ->where('watch_video_durations.user_id',$user_id)
                            ->whereRaw('CAST(categories.price AS DECIMAL(10, 2)) > 0.00')
                            ->groupBy('watch_video_durations.category_id')
                            ->orderBy('watch_video_durations.id')
                            ->paginate($request->input('perPage'), ['*'], 'page', $request->input('page'));
            if($category_list !== null){
                if(!empty($category_list)){
                    $data['category_list'] = $category_list->values();
                    $data['current_page']  = $category_list->currentPage();
                    $data['per_page']      = $category_list->perPage();
                    $data['total']         = $category_list->total();
                    $data['last_page']     = $category_list->lastPage();
                    
                    return $this->success($data,'Category list');
                }
                return $this->success([],'Subscription purchased list');
            }
            return $this->success('Not purchases any subscription','Not purchases any subscription');
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
