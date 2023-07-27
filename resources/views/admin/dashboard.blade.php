@extends('admin.layout.app')
@section('title', 'Dashboard')
@section('page', 'Dashboard')
@section('content')
<div class="container-fluid">
    <!-- Small boxes (Stat box) -->
    <div class="row">
        <div class="col-lg-3 col-6">
            <!-- small box -->
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{$total_user_count}}</h3>

                    <p>Total users</p>
                </div>
                <div class="icon">
                    <i class="fa fa-users"></i>
                </div>
            </div>
        </div>
        <!-- ./col -->
        <div class="col-lg-3 col-6">
            <!-- small box -->
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{$today_user_count}}</h3>
                    
                    <p>Today Users</p>
                </div>
                <div class="icon">
                    <i class="fa fa-hourglass"></i>
                </div>
            </div>
        </div>
    </div>
    <!-- /.row -->
    <!-- Main row -->
    <div class="row">
        <!-- Left col -->
        <section class="col-lg-7 connectedSortable">

        </section>
    </div>
</div>
@endsection
@push('custom-scripts')
@endpush