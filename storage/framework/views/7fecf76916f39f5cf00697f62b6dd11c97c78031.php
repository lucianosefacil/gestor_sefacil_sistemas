<link rel="stylesheet" href="<?php echo e(asset('css/vendor.css?v='.$asset_v), false); ?>">

<?php if( in_array(session()->get('user.language', config('app.locale')), config('constants.langs_rtl')) ): ?>
<link rel="stylesheet" href="<?php echo e(asset('css/rtl.css?v='.$asset_v), false); ?>">
<?php endif; ?>

<?php echo $__env->yieldContent('css'); ?>

<?php

$images = ["home-bg.jpg", "home-bg1.png", "home-bg2.png", "home-bg3.png"];
$backHome = $images[rand(0, sizeof($images)-1)];
?>
<!-- app css -->
<link rel="stylesheet" href="<?php echo e(asset('css/app.css?v='.$asset_v), false); ?>">

<style type="text/css">
	.left-col {
		background: linear-gradient(0deg,rgba(0, 0, 0, 0.76),rgba(51, 51, 51, 0.32)),url(../img/<?php echo e($backHome, false); ?>); 
		text-align: center;
		background-size: cover;
		background-position: center;
	}
</style>

<?php if(isset($pos_layout) && $pos_layout): ?>
<style type="text/css">
	.content{
		padding-bottom: 0px !important;
	}
</style>
<?php endif; ?>

<?php if(!empty($__system_settings['additional_css'])): ?>
<?php echo $__system_settings['additional_css']; ?>

<?php endif; ?><?php /**PATH /home/gestor/public_html/gestor_sefacil_sistemas/resources/views/layouts/partials/css.blade.php ENDPATH**/ ?>