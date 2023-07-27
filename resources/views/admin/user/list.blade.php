@extends('admin.layout.app')
@section('title', 'User List')
@section('page', 'User List')
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
                                    <th>Phone no</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($users as $key=>$user)
                                <tr>
                                    <td>{{++$key}}</td>
                                    <td>{{$user->name}}</td>
                                    <td>{{$user->email}}</td>
                                    <td>{{$user->phone_no}}</td>
                                    <td>
                                        <input type="checkbox" data-toggle="toggle" class="user_status_switch" id="user_status_switch" data-size="xs" data-on="Active" data-off="InActive" data-id="{{$user->id}}" data-onstyle="success" data-offstyle="danger" @if($user->status == 1) checked @endif>
                                    </td>
                                    <td>
                                        <a href="{{ route('users.detail', ['id' => $user->id]) }}"><i class="fa fa-eye"></i></a>
                                    </td>
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
@section('scripts')
<script>
    $(document).ready(function() {
        $('#users_list_table').DataTable();
    });
    $('.user_status_switch').change(function() {
        var status = $(this).prop('checked') ? 1 : 0;
        var id = $(this).data('id');
        var url = "{{ route('users.status-update') }}";
        $.ajax({
            url: url,
            type: "POST",
            data: {
                id: id,
                status: status,
                "_token": "{{ csrf_token() }}"
            },
            success: function(data) {
                var checkbox = $('.user_status_switch[data-id="' + id + '"]');
                checkbox.prop('checked', status);
            },
            error: function(xhr) {
                console.log(xhr.responseJSON.message);
            }
        });
    });
</script>
@endsection