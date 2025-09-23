@extends('layouts.auth2')
@section('title', __('lang_v1.login'))

@section('content')
<div class="login-form col-md-12 col-xs-12 right-col-content">
    <p class="form-header text-white">@lang('lang_v1.login')</p>
    <form method="POST" action="{{ route('login') }}" id="login-form">
        {{ csrf_field() }}
        <div class="form-group has-feedback {{ $errors->has('username') ? ' has-error' : '' }}">
            @php
            $username = old('username');
            $password = null;

            if(getenv('APP_ENV') == 'demo'){

                $username = 'slym';
                $password = '12345';

                $demo_types = array(
                'all_in_one' => 'admin',
                'super_market' => 'admin',
                'pharmacy' => 'admin-pharmacy',
                'electronics' => 'admin-electronics',
                'services' => 'admin-services',
                'restaurant' => 'admin-restaurant',
                'superadmin' => 'superadmin',
                'woocommerce' => 'woocommerce_user',
                'essentials' => 'admin-essentials',
                'manufacturing' => 'manufacturer-demo',
                );

                if( !empty($_GET['demo_type']) && array_key_exists($_GET['demo_type'], $demo_types) ){
                    $username = $demo_types[$_GET['demo_type']];
                }
            }
            @endphp
            <input id="username" type="text" class="form-control" name="username" @isset($CookieUserName)) value="{{$CookieUserName}}" @endif required autofocus placeholder="@lang('lang_v1.username')">
            <span class="fa fa-user form-control-feedback"></span>
            @if ($errors->has('username'))
            <span class="help-block">
                <strong>{{ $errors->first('username') }}</strong>
            </span>
            @endif
        </div>
        <div class="form-group has-feedback {{ $errors->has('password') ? ' has-error' : '' }}">
            <input id="password" type="password" class="form-control" name="password" @isset($Cookiepass)) value="{{$Cookiepass}}" @endif required placeholder="@lang('lang_v1.password')">
            <span class="glyphicon glyphicon-lock form-control-feedback"></span>
            @if ($errors->has('password'))
            <span class="help-block">
                <strong>{{ $errors->first('password') }}</strong>
            </span>
            @endif
        </div>
        <div class="form-group">
            <div class="checkbox icheck">
                <label>
                    <input type="checkbox" name="remember" @isset($Cookieremember) @if($Cookieremember == true) checked @endif @endif> @lang('lang_v1.remember_me')
                </label>
            </div>
        </div>
        <br>
        <div class="form-group">
            <button type="submit" class="btn btn-success btn-flat btn-login">@lang('lang_v1.login')</button>
            @if(config('app.env') != 'demo')
            <a href="{{ route('password.request') }}" class="pull-right">
                @lang('lang_v1.forgot_your_password')
            </a>
            @endif
        </div>
    </form>
</div>
@if(config('app.env') == 'demo')
<div class="col-md-12 col-xs-12" style="padding-bottom: 30px;">
    @component('components.widget', ['class' => 'box-primary', 'header' => '<h4 class="text-center">Credencias de demonstração - <small><i> As demonstrações são apenas para fins de exemplo da aplicação.</u></i></small></h4>'])


    <button onclick="demoLogin('slym', '12345')" class="btn bg-red-active btn-app demo-login" data-toggle="tooltip" title="Usuário superadmin" data-admin="{{$demo_types['superadmin']}}"><i class="fas fa-university"></i> Login Superadmin Sass</button>


    <button onclick="demoLogin('gestor', '123456')" href="?demo_type=user" class="btn bg-maroon btn-app demo-login" data-toggle="tooltip" title="Usuário comum com todas as permissões" style="color:white !important" data-admin="{{$demo_types['superadmin']}}">
        <i class="fas fa-user"></i>
        Login Usuário Admin
    </button>

    <button onclick="demoLogin('caixa', '123456')" href="?demo_type=user_caixa" class="btn bg-info btn-app demo-login" data-toggle="tooltip" title="Usuário acesso caixa" style="color:white !important" data-admin="{{$demo_types['superadmin']}}">
        <i class="fas fa-user"></i>
        Login Usuário Caixa
    </button>
    @endcomponent   
</div>
@endif 
@stop
@section('javascript')
<script type="text/javascript">
    // $(document).ready(function(){
    //     $('#change_lang').change( function(){
    //         window.location = "{{ route('login') }}?lang=" + $(this).val();
    //     });

    //     $('a.demo-login').click( function (e) {
    //      e.preventDefault();
    //      $('#username').val($(this).data('admin'));
    //      $('#password').val("{{$password}}");
    //      $('form#login-form').submit();
    //  });
    // })
    function demoLogin(login, pass){

        $('#username').val(login);
        $('#password').val(pass);
        $('form#login-form').submit();
    }
</script>
@endsection
