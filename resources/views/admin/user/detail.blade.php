@extends('admin.layout.app')
@section('title', 'Profile Detail')
@section('page', 'Profile Detail')
@section('content')
<style>
    .profile-card {
        background-color: #fff;
        border-radius: 10px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        padding: 20px;
    }

    .profile-name {
        font-size: 30px;
        font-weight: bold;
        text-align: center;
        margin-bottom: 15px;
        color: #007bff;
        text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.1);
    }
</style>
<div class="container-fluid mt-1 px-3">
    <div class="row">
        <div class="col-12">
            <div class="profile-card">
                <!-- <div class="profile-name">{{$userDetail->name}}</div> -->
                <div class="row mt-2">
                    <div class="col-5">
                        <span><b>Birth Date</b></span>
                    </div>
                    <div class="col-1">:</div>
                    <div class="col-6">
                        <span>{{$userDetail->name}}</span>
                    </div>
                </div>
                <div class="row mt-5">
                    <div class="col-5">
                        <span><b>Email</b></span>
                    </div>
                    <div class="col-1">:</div>
                    <div class="col-6">
                        <span>{{$userDetail->email}}</span>
                    </div>
                </div>
                <div class="row mt-5">
                    <div class="col-5">
                        <span><b>Birth Date</b></span>
                    </div>
                    <div class="col-1">:</div>
                    <div class="col-6">
                        <span>{{date('d-m-Y', strtotime($userDetail->birth_date))}}</span>
                    </div>
                </div>
                <div class="row mt-5">
                    <div class="col-5">
                        <span><b>Phone Number</b></span>
                    </div>
                    <div class="col-1">:</div>
                    <div class="col-6">
                        <span>{{$userDetail->phone_no}}</span>
                    </div>
                </div>
                <div class="row mt-5">
                    <div class="col-5">
                        <span><b>Gender</b></span>
                    </div>
                    <div class="col-1">:</div>
                    <div class="col-6">
                        <span>{{$userDetail->gender}}</span>
                    </div>
                </div>
                <div class="row mt-5">
                    <div class="col-5">
                        <span><b>Status</b></span>
                    </div>
                    <div class="col-1">:</div>
                    <div class="col-6">
                        <span class="profile-status" style="{{$userDetail->status == 1 ? 'color:green;' : 'color:red;'}}">{{$userDetail->status == 1 ? 'Active' : 'Inactive'}}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection