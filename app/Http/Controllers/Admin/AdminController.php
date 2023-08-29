<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Support\Facades\File;
use Illuminate\Http\Request;
use App\Http\Controllers\BaseController;
use Illuminate\Support\Str;
use App\Models\Category;
use App\Models\ContactSupport;
use App\Models\Image;
use App\Models\Setting; 
use App\Models\Video; 
use App\Models\User; 
use Validator;
use Helper; 
use Auth;
use getID3;
use getid3_lib;

class AdminController extends BaseController
{
    // CATERGORY

    public function categoryList(){
        $categories = Category::all();
        return view('admin.category.list',compact('categories'));
    }

    public function categoryAdd(){ 
        return view('admin.category.add');
    }
    
    public function categoryStore(Request $request){
        
        $validator = Validator::make($request->all(),[
            'title'=>"required",
            'button_title'=>"required",
            'image'=>"required",
        ]);

        if ($validator->fails())
        {
            return back()->withInput()->withErrors($validator);
        }

        $category               = new Category;
        $category->title        = $request->title;
        $category->button_title = $request->button_title;
        $cat_data = $category->save();

        $folderPath = public_path().'/category_image';
        if (!is_dir($folderPath)) {
            mkdir($folderPath, 0777, true);
        }

        $mediaFiles = $request->file('image');
        $extension = $mediaFiles->getClientOriginalExtension();
        $filename = 'Category_' . $category->id . '_' . random_int(10000, 99999) . '.' . $extension;
        $mediaFiles->move(public_path('category_image'), $filename);

        Image::create(['type_id'=>$category->id,'file_name'=>$filename,'type' => 'category_image']);
        return redirect()->route('category.list')->with('message','Category Added Successfully'); 
    }
    
    public function categoryEdit($id){
        $categories = Category::where('id',$id)->with('image')->first(); 
        return view('admin.category.edit',compact('categories'));
    }

    public function categoryUpdate(Request $request){
        $validator = Validator::make($request->all(),[
            'title'=>"required",
            'button_title'=>"required",
        ]);

        if ($validator->fails())
        {
            return back()->withInput()->withErrors($validator);
        }

        $category = Category::find($request->id);
        if ($category) {
            $category->title        = $request->title;
            $category->button_title = $request->button_title;
            $category->save();

            $folderPath = public_path().'/category_image';

            if (!is_dir($folderPath)) {
                mkdir($folderPath, 0777, true);
            }
            
            if($request->file('image') != null){
                $image_data = Image::where('type_id',$request->id)->where('type','category_image')->first();

                if($image_data){
                    $path = public_path('category_image/' . $image_data->file_name);
                    if (File::exists($path)) {
                        if (!is_writable($path)) {
                            chmod($path, 0777);
                        }
                        File::delete($path);
                    }

                    $image_data->delete();
                }

                $mediaFiles = $request->file('image');
                $extension = $mediaFiles->getClientOriginalExtension();
                $filename = 'Category_' . $category->id . '_' . random_int(10000, 99999) . '.' . $extension;
                $mediaFiles->move(public_path('category_image'), $filename);

                Image::create(['type_id'=>$request->id,'file_name'=>$filename,'type' => 'category_image']);
            }
        } 
        return redirect()->route('category.list')->with('message','Category Updated Successfully'); 
    }
    
    public function categoryDelete($id){
        $categories = Category::findOrFail($id);
        $categories->delete();

        $image_data = Image::where('type_id',$id)->where('type','category_image')->first();

        if($image_data){
            $path = public_path('category_image/' . $image_data->file_name);
			if (File::exists($path)) {
				if (!is_writable($path)) {
					chmod($path, 0777);
				}
				File::delete($path);
			}

            $image_data->delete();
        }

        // Video related delete
        
        $video_id         =     Video::where('category_id',$id)->pluck('id')->toArray();
        $video_image_data =     Image::whereIn('type_id',$video_id)->where(function ($query) {
                                    $query->where('type', 'video_thumbnail_image')
                                        ->orWhere('type', 'video');
                                })->get();

        if($video_image_data){
            foreach($video_image_data as $key=>$video_image){
                $path = public_path('video/' . $video_image->file_name);
                if (!is_writable($path)) {
                    chmod($path, 0777);
                }
                File::delete($path);
            }
            $video_image_data->each->delete();
        }
        Video::whereIn('id', $video_id)->delete();

        return redirect()->route('category.list')->with('message','Category Deleted Successfully');
    }

    // VIDEO

    public function videoList(){
        $videos = Video::with('category')->get();
        return view('admin.video.list',compact('videos'));
    }

    public function videoAdd(){ 
        $categories = Category::all(); 
        return view('admin.video.add',compact('categories'));
    }
    
    public function videoStore(Request $request){
        
        $validator = Validator::make($request->all(),[
            'title'               => "required",
            'category'            => "required", 
            'image'               => "required|mimes:jpeg,png,jpg",
            'video'               => "required|mimes:mp4,mov",
            'can_view_free_user'  => "required",
        ]);

        if ($validator->fails())
        {
            return back()->withInput()->withErrors($validator);
        }

        $getID3                     = new getID3();
        do {
            $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
            $code = '';
            $code .= substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 1);
            $code .= substr(str_shuffle('abcdefghijklmnopqrstuvwxyz'), 0, 1);
            $code .= substr(str_shuffle('0123456789'), 0, 1);
            for ($i = 0; $i < 4; $i++) {
                $code .= $characters[rand(0, strlen($characters) - 1)];
            }
        } while (Video::where("unique_id", "=", $code)->first());

        $video                      = new Video;
        $video->title               = $request->title;
        $video->category_id         = $request->category;
        $video->duration            = date('H:i:s.v', $getID3->analyze($request->file('video'))['playtime_seconds']);
        $video->can_view_free_user  = $request->can_view_free_user;
        $video->unique_id           = $code;
        $video->save();

        $folderPath = public_path().'/video';
        if (!is_dir($folderPath)) {
            mkdir($folderPath, 0777, true);
        }

        // For storing thumbnail image
        $mediaFiles = $request->file('image');
        $extension = $mediaFiles->getClientOriginalExtension();
        $filename = 'Video_thumbnail_' . $video->id . '_' . random_int(10000, 99999) . '.' . $extension;
        $mediaFiles->move(public_path('video'), $filename);
        
        $media_array = ['type_id'=>$video->id,'file_name'=>$filename,'type' => 'video_thumbnail_image','created_at' => now() , 'updated_at' => now()];
        
        // For storing video image
        $videoFiles = $request->file('video');
        $video_extension = $videoFiles->getClientOriginalExtension();
        $video_file_name = 'Video_' . $video->id . '_' . random_int(10000, 99999) . '.' . $video_extension;
        $videoFiles->move(public_path('video'), $video_file_name);

        $video_array = ['type_id'=>$video->id,'file_name'=>$video_file_name,'type' => 'video','created_at' => now() , 'updated_at' => now()];

        $final_array = array($media_array,$video_array);

        Image::insert($final_array);
        return redirect()->route('video.list')->with('message','Video Added Successfully'); 
    }
    
    public function videoEdit($id){
        $videos = Video::where('id',$id)->with('category','image','video')->first(); 
        $categories = Category::all(); 
        return view('admin.video.edit',compact('videos','categories'));
    }

    public function videoUpdate(Request $request){

        $validator = Validator::make($request->all(),[
            'title'   =>"required",
            'category'=>"required", 
            'can_view_free_user'  => "required",
        ]);

        if ($validator->fails())
        {
            return back()->withInput()->withErrors($validator);
        }

        $video = Video::find($request->id);
        if ($video) {
            $video->title        = $request->title;
            $video->category_id  = $request->category;
            $video->can_view_free_user  = $request->can_view_free_user;
            $video_data = $video->save();

            $folderPath = public_path().'/video';

            if (!is_dir($folderPath)) {
                mkdir($folderPath, 0777, true);
            }
            if($request->file('image') != null){
                $image_data = Image::where('type','video_thumbnail_image')->first();

                if($image_data){
                    $path = public_path('video/' . $image_data->file_name);
                    if (File::exists($path)) {
                        if (!is_writable($path)) {
                            chmod($path, 0777);
                        }
                        File::delete($path);
                    }

                    $image_data->delete();
                }

                $mediaFiles = $request->file('image');
                $extension = $mediaFiles->getClientOriginalExtension();
                $filename = 'Video_thumbnail_' . $video->id . '_' . random_int(10000, 99999) . '.' . $extension;
                $mediaFiles->move(public_path('video'), $filename);
                Image::create(['type_id'=>$video->id,'file_name'=>$filename,'type' => 'video_thumbnail_image']);
            }
           
            if($request->file('video') != null){
                $video_data = Image::where('type','video')->first();

                if($video_data){
                    $path = public_path('video/' . $video_data->file_name);
                    if (File::exists($path)) {
                        if (!is_writable($path)) {
                            chmod($path, 0777);
                        }
                        File::delete($path);
                    }

                    $video_data->delete();
                }

                $mediaFiles = $request->file('video');

                $getID3              = new getID3();
                $file                = $getID3->analyze($mediaFiles);
                $video->duration     = date('H:i:s.v', $file['playtime_seconds']);
                $video->save();

                $extension = $mediaFiles->getClientOriginalExtension();
                $filename = 'Video_' . $video->id . '_' . random_int(10000, 99999) . '.' . $extension;
                $mediaFiles->move(public_path('video'), $filename);
                Image::create(['type_id'=>$video->id,'file_name'=>$filename,'type' => 'video']);
            }
        } 
        return redirect()->route('video.list')->with('message','Video Updated Successfully'); 
    }
    
    public function videoDelete($id){
        $videos = Video::findOrFail($id);
        $videos->delete();

        $image_data = Image::where('type_id',$id)->where('type','video_thumbnail_image')->orWhere('type','video')->get();
        
        if($image_data){
            foreach($image_data as $key=>$image){
                $path = public_path('video/' . $image->file_name);
                if (!is_writable($path)) {
                    chmod($path, 0777);
                }
                File::delete($path);
            }
            $image_data->each->delete();
        }
        return redirect()->route('video.list')->with('message','Video Deleted Successfully');
    }

    public function updatefeatured(Request $request){
        try{
            $video = Video::where('id',$request->id)->first();
            if($video){ 
                $video->is_featured = $request->is_featured; 
                $video->save();
                return $this->success([],'Featured change successfully');
            }
            return $this->error('Video not found','Video not found');
        }catch(Exception $e){
            return $this->error($e->getMessage(),'Exception occur');
        }
        return $this->error('Something went wrong','Something went wrong');
    }

    // FEEDBACK

    public function feedbackList(){
        $feedbacks = ContactSupport::all();
        return view('admin.feedback.list',compact('feedbacks'));
    }

    // SETTING

    public function staticPagesList(){
        $settings = Setting::all();
        return view('admin.setting.list',compact('settings'));
    }

    public function pageEdit($id){
        $settings = Setting::where('id',$id)->first();
        return view('admin.setting.edit',compact('settings'));
    }

    public function pageUpdate(Request $request){

        $validator = Validator::make($request->all(),[
            'id'=>"required",
            'title'=>"required",
            'description'=>"required",
        ]);

        if ($validator->fails())
        {
            return back()->withInput()->withErrors($validator);
        }

        $input = $request->all();
        $insert_data['title']       = $input['title'];
        $insert_data['value']       = $input['description'];

        Setting::where('id',$request->id)->update($insert_data);
        return redirect()->route('static-pages.list')->with('message','Page updated Successfully'); 
    }    
}
