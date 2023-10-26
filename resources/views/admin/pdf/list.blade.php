@extends('admin.layout.app')
@section('title', 'Pdf List')
@section('page', 'Pdf List')
@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card my-4">
                <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                    <div class="bg-gradient-primary shadow-primary border-radius-lg pt-4 pb-3">
                        <h6 class="text-capitalize pl-3">Pdf table</h6>
                    </div>
                </div>

                <div class="ml-auto mr-3">
                    <a href="{{route('pdf.add')}}">
                        <button type="button" class="btn bg-gradient-primary mt-2">
                            Add Pdf
                        </button>
                    </a>
                </div>

                <div class="card-body px-0 pb-2">
                    <div class="table-responsive px-3">
                        <table class="table table-bordered table-hover display" id="video_list_table">
                            <thead>
                                <tr>
                                    <th>Id</th>
                                    <th>Pdf</th>
                                    <th>Category</th> 
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($pdfs as $key=>$pdf)
                                <tr>
                                    <td>{{ ++$key }}</td>
                                    <td>{{$pdf->title}}</td>
                                    <td>{{$pdf->category->title}}</td>
                                    <td>
                                        <a href="{{route('pdf.edit',['id' => $pdf->id])}}">
                                            <i class="fa fa-edit"></i>
                                        </a>
                                        <a class="ml-2" href="{{route('pdf.delete',['id' => $pdf->id])}}">
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
        $('#video_list_table').DataTable();
    });
</script>
@endsection