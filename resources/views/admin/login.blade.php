<!DOCTYPE html>
<html lang="en">

<head>
    <title>
        Login | Meditation
    </title>
    @include('admin.layout.common-head')
</head>
<style>
    .error{
        color: red;
    }
</style>
<body class="hold-transition login-page">
    <div class="login-box">
        <div class="card card-outline card-primary">
            <div class="card-header text-center">
                <a href="{{route('/')}}" class="h1"><b>Meditation</b></a>
            </div>
            <div class="card-body">
                <form class="text-start" name="login" id="login_form" method="post" action="{{ route('login-admin')}}">
                    @csrf
                    <div class="input-group input-group-outline my-3">
                        <input type="email" name="email" id="email" class="form-control" placeholder="Email" value="{{ old('email') }}">
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-envelope"></span>
                            </div>
                        </div>
                    </div>
                    @if ($errors->has('email'))
                        <span class="text-danger">{{ $errors->first('email') }}</span>
                    @endif
                    <p class="error"  id="email-error"></p>
                    <div class="input-group input-group-outline mb-3">
                        <input type="password" name="password" id="password" class="form-control" placeholder="Password">
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-lock"></span>
                            </div>
                        </div>
                    </div>
                    @if ($errors->has('password'))
                        <span class="text-danger">{{ $errors->first('password') }}</span>
                    @endif
                    <p class="error"  id="password-error"></p>
                    <div class="form-check form-switch d-flex align-items-center mb-3">
                        <input class="form-check-input" name="remember_me" type="checkbox" id="rememberMe">
                        <label class="form-check-label mb-0 ms-3" for="rememberMe">Remember me</label>
                    </div>
                    @if ($errors->has('error'))
                        <span class="text-danger">{{ $errors->first('error') }}</span>
                    @endif
                    <div class="text-center">
                        <button type="submit" class="btn bg-gradient-primary w-100 my-4 mb-2">Login</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @include('admin.layout.common-end')
    <script>
        $('#login_form').validate({
            rules: {
                email: {
                    required: true,
                    email: true
                },
                password: {
                    required: true,
                    minlength: 6
                }
            },
            messages: {
                email: {
                    required: "Please enter your email",
                    email: "Please enter a valid email address"
                },
                password: {
                    required: "Please enter your password",
                    minlength: "Your password must be at least 6 characters long"
                }
            },
            errorPlacement: function(error, element) {
                var elementId = element.attr('id'); 
                error.appendTo("#" + elementId + "-error");
            }
        });
    </script>
</body>

</html>