<!-- Main Sidebar Container -->
<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <a href="{{route('/')}}" class="brand-link">
        <img src="{{ asset('assets/dist/img/AdminLTELogo.png')}}" alt="AdminLTE Logo" class="brand-image img-circle elevation-3" style="opacity: .8">
        <span class="brand-text font-weight-light">Meditation</span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
        <!-- Sidebar user panel (optional) -->
        @php

        function isActivePrefix($routeName,$className) {
            if (Str::startsWith(Route::getCurrentRoute()->getPrefix(), 'questions/') && $routeName == "questions") {
                return $className;
            }
            return trim(Route::getCurrentRoute()->getPrefix(), '/') == $routeName ? $className : '';
        }

        function isActive($routeName) {
            return Route::currentRouteName() == $routeName ? 'active' : '';
        }
        
        @endphp
        <!-- Sidebar Menu -->
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                <li class="nav-item  {{isActivePrefix('dashboard','menu-open')}}">
                    <a class="nav-link {{ (Route::currentRouteName() == 'dashboard') ? 'active' : '' }}" href="{{route('dashboard')}}">
                        <i class="nav-icon fas fa-tachometer-alt"></i>
                        <p>
                            Dashboard
                            <!-- <span class="badge badge-info right">6</span> -->
                        </p>
                    </a>
                </li>
                <li class="nav-item {{isActivePrefix('users','menu-open')}} mt-2" role="button">
                    <a class="nav-link dropDownMenu {{isActive('users.list')}}">
                        <i class="nav-icon fa fa-users"></i>
                        <p>
                            User
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a class="nav-link {{isActive('users.list')}}" href="{{route('users.list')}}">
                                <i class="fa fa-list-ul nav-icon"></i>
                                <p>List</p>
                            </a>
                        </li>
                    </ul>
                </li>
                <li class="nav-item {{isActivePrefix('category','menu-open')}} mt-2" role="button">
                    <a class="nav-link dropDownMenu {{isActive('category.list')}}">
                        <i class="nav-icon fa fa-qrcode"></i>
                        <p>
                            Category
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a class="nav-link {{isActive('category.list')}}" href="{{route('category.list')}}">
                                <i class="fa fa-list-ul nav-icon"></i>
                                <p>List</p>
                            </a>
                        </li>
                    </ul>
                </li>
                <li class="nav-item {{isActivePrefix('video','menu-open')}} mt-2" role="button">
                    <a class="nav-link dropDownMenu {{isActive('video.list')}}">
                        <i class="nav-icon fa fa-video"></i>
                        <p>
                            Video
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a class="nav-link {{isActive('video.list')}}" href="{{route('video.list')}}">
                                <i class="fa fa-list-ul nav-icon"></i>
                                <p>List</p>
                            </a>
                        </li>
                    </ul>
                </li>
                <li class="nav-item {{isActivePrefix('pdf','menu-open')}} mt-2" role="button">
                    <a class="nav-link dropDownMenu {{isActive('pdf.list')}}">
                        <i class="nav-icon fa fa-file-pdf"></i>
                        <p>
                            PDF
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a class="nav-link {{isActive('pdf.list')}}" href="{{route('pdf.list')}}">
                                <i class="fa fa-list-ul nav-icon"></i>
                                <p>List</p>
                            </a>
                        </li>
                    </ul>
                </li>
                <li class="nav-item {{isActivePrefix('feedback','menu-open')}} mt-2" role="button">
                    <a class="nav-link dropDownMenu {{isActive('feedback.list')}}">
                        <i class="nav-icon fa fa-comment"></i>
                        <p>
                            Feedback
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a class="nav-link {{isActive('feedback.list')}}" href="{{route('feedback.list')}}">
                                <i class="fa fa-list-ul nav-icon"></i>
                                <p>List</p>
                            </a>
                        </li>
                    </ul>
                </li> 
                <li class="nav-item {{isActivePrefix('static-pages','menu-open')}} mt-2" role="button">
                    <a class="nav-link dropDownMenu {{isActive('static-pages.list')}}">
                        <i class="nav-icon  fa fa-file-alt"></i>
                        <p>
                            Static Pages
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a class="nav-link {{isActive('static-pages.list')}}" href="{{route('static-pages.list')}}">
                                <i class="fa fa-list-ul nav-icon"></i>
                                <p>List</p>
                            </a>
                        </li>
                    </ul>
                </li>
            </ul>
        </nav>
        <!-- /.sidebar-menu -->
    </div>
    <!-- /.sidebar -->
</aside>