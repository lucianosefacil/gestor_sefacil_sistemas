<div class="pos-tab-content">
     <div class="row">
        <div class="col-sm-4">
            <div class="form-group">
                <?php echo Form::label('stock_expiry_alert_days', 'Veja o alerta de expiração da estoque para' . ':*'); ?>

                <div class="input-group">
                <span class="input-group-addon">
                    <i class="fas fa-calendar-times"></i>
                </span>
                <?php echo Form::number('stock_expiry_alert_days', $business->stock_expiry_alert_days, ['class' => 'form-control','required']); ?>

                <span class="input-group-addon">
                    Dias
                </span>
                </div>
            </div>
        </div>
    </div>
</div><?php /**PATH /home/gestor/public_html/gestor_sefacil_sistemas/resources/views/business/partials/settings_dashboard.blade.php ENDPATH**/ ?>