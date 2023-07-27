<!DOCTYPE html>
<html lang="en">

<head>
    @include('admin.layout.common-head')
</head>
<style>
    th,td {
     text-align:center !important; 
    }
    .text-danger.error{
        font-size: 100%;
        font-weight: 600;
    }
</style>
<body class="hold-transition sidebar-mini layout-fixed">
    <div class="wrapper">
        @include('admin.layout.header')
        @include('admin.layout.sidebar')
        <div class="content-wrapper">
            <div class="content-header">
                <div class="container-fluid">
                    <div class="row mb-2">
                        <div class="col-sm-6">
                            <h1 class="m-0"> @yield('page')</h1>
                        </div>
                    </div>
                </div>
            </div>
            @yield('content')
        </div>
        @include('admin.layout.footer')
    </div>
    @include('admin.layout.common-end')
    @yield('scripts')
    @stack('custom-scripts')
</body>

</html>