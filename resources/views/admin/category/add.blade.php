@extends('admin.layout.app')
@section('title', 'Category Add')
@section('page', 'Category Add')
@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card my-4">
                <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                    <div class="bg-gradient-primary shadow-primary border-radius-lg pt-4 pb-3">
                        <h6 class="text-white text-capitalize pl-3">Category Add</h6>
                    </div>
                </div>
                <div class="card-body px-0 pb-2">
                    <div class="p-4">
                        <form method="post" action="{{route('category.store')}}" enctype="multipart/form-data">
                            @csrf
                            <div>
                                <div class="form-group">
                                    <label class="form-label">Title</label>
                                    <input type="text" class="form-control" name="title" autocomplete="off">
                                </div>
                                @if($errors->has('title'))
                                    <small class="text-danger error" >
                                        {{ $errors->first('title') }}
                                    </small>
                                @endif
                            </div>
                            <div>
                                <div class="form-group">
                                    <label class="form-label">Button Title</label>
                                    <input type="text" class="form-control" name="button_title" autocomplete="off">
                                </div>
                                @if($errors->has('button_title'))
                                    <small class="text-danger error" >
                                        {{ $errors->first('button_title') }}
                                    </small>
                                @endif
                            </div>
                            <div>
                                <div class="form-group">
                                    <label class="form-label">Image</label>
                                    <input type="file" class="form-control h-auto" name="image" autocomplete="off" accept="image/png, image/gif, image/jpeg"/>
                                </div>
                                @if($errors->has('image'))
                                    <small class="text-danger error" >
                                        {{ $errors->first('image') }}
                                    </small>
                                @endif
                            </div>
                            <div class="row pt-4">
                                <div class="col s12 m12 input-field">
                                    <button type="submit" class="btn bg-gradient-primary">Save</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@section('scripts')
@endsection