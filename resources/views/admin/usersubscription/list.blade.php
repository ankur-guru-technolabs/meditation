@extends('admin.layout.app')
@section('title', 'User Subscription List')
@section('page', 'User Subscription List')
@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card my-4">
                <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                    <div class="bg-gradient-primary shadow-primary border-radius-lg pt-4 pb-3">
                        <h5 class="text-capitalize pl-3">Users table</h5>
                    </div>
                </div>
                <div class="card-body px-0 pb-2">
                    <div class="table-responsive px-3">
                        <table class="table table-bordered table-hover display" id="users_list_table">

                            <thead>
                                <tr>
                                    <th>Id</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Category Name</th>
                                    <th>Purchase Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($userSubscription as $key=>$user)
                                <tr>
                                    <td>{{++$key}}</td>
                                    <td>{{$user->user->name ?? '-'}}</td>
                                    <td>{{$user->user->email ?? '-'}}</td>
                                    <td>{{$user->category->title ?? '-'}}</td>
                                    <td>{{date ('d - m - Y', strtotime($user->created_at))}}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
