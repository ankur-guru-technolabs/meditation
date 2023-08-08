@extends('admin.layout.app')
@section('title', 'Category List')
@section('page', 'Category List')
@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card my-4">
                <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                    <div class="bg-gradient-primary shadow-primary border-radius-lg pt-4 pb-3">
                        <h6 class="text-capitalize pl-3">Category table</h6>
                    </div>
                </div>

                <div class="ml-auto mr-3">
                    <a href="{{route('category.add')}}">
                        <button type="button" class="btn bg-gradient-primary mt-2">
                            Add Category
                        </button>
                    </a>
                </div>

                <div class="card-body px-0 pb-2">
                    <div class="table-responsive px-3">
                        <table class="table table-bordered table-hover display" id="category_list_table">
                            <thead>
                                <tr>
                                    <th>Id</th>
                                    <th>Category</th> 
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($categories as $key=>$category)
                                <tr>
                                    <td>{{ ++$key }}</td>
                                    <td>{{$category->title}}</td> 
                                    <td>
                                        <a href="{{route('category.edit',['id' => $category->id])}}">
                                            <i class="fa fa-edit"></i>
                                        </a>
                                        <a class="ml-2" href="{{route('category.delete',['id' => $category->id])}}">
                                            <i class="fa fa-trash"></i>
                                        </a>
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
        $('#category_list_table').DataTable();
    });
</script>
@endsection