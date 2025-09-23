<?php $__env->startSection('title', __('lang_v1.login')); ?>

<?php $__env->startSection('content'); ?>
<div class="login-form col-md-12 col-xs-12 right-col-content">
    <p class="form-header text-white"><?php echo app('translator')->get('lang_v1.login'); ?></p>
    <form method="POST" action="<?php echo e(route('login'), false); ?>" id="login-form">
        <?php echo e(csrf_field(), false); ?>

        <div class="form-group has-feedback <?php echo e($errors->has('username') ? ' has-error' : '', false); ?>">
            <?php
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
            ?>
            <input id="username" type="text" class="form-control" name="username" <?php if(isset($CookieUserName)): ?>) value="<?php echo e($CookieUserName, false); ?>" <?php endif; ?> required autofocus placeholder="<?php echo app('translator')->get('lang_v1.username'); ?>">
            <span class="fa fa-user form-control-feedback"></span>
            <?php if($errors->has('username')): ?>
            <span class="help-block">
                <strong><?php echo e($errors->first('username'), false); ?></strong>
            </span>
            <?php endif; ?>
        </div>
        <div class="form-group has-feedback <?php echo e($errors->has('password') ? ' has-error' : '', false); ?>">
            <input id="password" type="password" class="form-control" name="password" <?php if(isset($Cookiepass)): ?>) value="<?php echo e($Cookiepass, false); ?>" <?php endif; ?> required placeholder="<?php echo app('translator')->get('lang_v1.password'); ?>">
            <span class="glyphicon glyphicon-lock form-control-feedback"></span>
            <?php if($errors->has('password')): ?>
            <span class="help-block">
                <strong><?php echo e($errors->first('password'), false); ?></strong>
            </span>
            <?php endif; ?>
        </div>
        <div class="form-group">
            <div class="checkbox icheck">
                <label>
                    <input type="checkbox" name="remember" <?php if(isset($Cookieremember)): ?> <?php if($Cookieremember == true): ?> checked <?php endif; ?> <?php endif; ?>> <?php echo app('translator')->get('lang_v1.remember_me'); ?>
                </label>
            </div>
        </div>
        <br>
        <div class="form-group">
            <button type="submit" class="btn btn-success btn-flat btn-login"><?php echo app('translator')->get('lang_v1.login'); ?></button>
            <?php if(config('app.env') != 'demo'): ?>
            <a href="<?php echo e(route('password.request'), false); ?>" class="pull-right">
                <?php echo app('translator')->get('lang_v1.forgot_your_password'); ?>
            </a>
            <?php endif; ?>
        </div>
    </form>
</div>
<?php if(config('app.env') == 'demo'): ?>
<div class="col-md-12 col-xs-12" style="padding-bottom: 30px;">
    <?php $__env->startComponent('components.widget', ['class' => 'box-primary', 'header' => '<h4 class="text-center">Credencias de demonstração - <small><i> As demonstrações são apenas para fins de exemplo da aplicação.</u></i></small></h4>']); ?>


    <button onclick="demoLogin('slym', '12345')" class="btn bg-red-active btn-app demo-login" data-toggle="tooltip" title="Usuário superadmin" data-admin="<?php echo e($demo_types['superadmin'], false); ?>"><i class="fas fa-university"></i> Login Superadmin Sass</button>


    <button onclick="demoLogin('gestor', '123456')" href="?demo_type=user" class="btn bg-maroon btn-app demo-login" data-toggle="tooltip" title="Usuário comum com todas as permissões" style="color:white !important" data-admin="<?php echo e($demo_types['superadmin'], false); ?>">
        <i class="fas fa-user"></i>
        Login Usuário Admin
    </button>

    <button onclick="demoLogin('caixa', '123456')" href="?demo_type=user_caixa" class="btn bg-info btn-app demo-login" data-toggle="tooltip" title="Usuário acesso caixa" style="color:white !important" data-admin="<?php echo e($demo_types['superadmin'], false); ?>">
        <i class="fas fa-user"></i>
        Login Usuário Caixa
    </button>
    <?php echo $__env->renderComponent(); ?>   
</div>
<?php endif; ?> 
<?php $__env->stopSection(); ?>
<?php $__env->startSection('javascript'); ?>
<script type="text/javascript">
    // $(document).ready(function(){
    //     $('#change_lang').change( function(){
    //         window.location = "<?php echo e(route('login'), false); ?>?lang=" + $(this).val();
    //     });

    //     $('a.demo-login').click( function (e) {
    //      e.preventDefault();
    //      $('#username').val($(this).data('admin'));
    //      $('#password').val("<?php echo e($password, false); ?>");
    //      $('form#login-form').submit();
    //  });
    // })
    function demoLogin(login, pass){

        $('#username').val(login);
        $('#password').val(pass);
        $('form#login-form').submit();
    }
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.auth2', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/sefacilsistemasc/gestor.sefacilsistemas.com.br/resources/views/auth/login.blade.php ENDPATH**/ ?>