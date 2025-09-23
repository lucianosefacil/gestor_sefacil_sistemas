<div class="table-responsive">
    <table class="table table-bordered table-striped ajax_view" id="purchase_table">
        <thead>
            <tr>
                <th><?php echo app('translator')->get('messages.action'); ?></th>
                <th><?php echo app('translator')->get('messages.date'); ?></th>
                <th><?php echo app('translator')->get('purchase.ref_no'); ?></th>
                <th><?php echo app('translator')->get('purchase.location'); ?></th>
                <th><?php echo app('translator')->get('purchase.supplier'); ?></th>
                <th><?php echo app('translator')->get('purchase.purchase_status'); ?></th>
                <th><?php echo app('translator')->get('purchase.payment_status'); ?></th>
                <th>Total</th>
                <th></th>
                <th><?php echo app('translator')->get('lang_v1.added_by'); ?></th>
                <th>Notas Fiscais</th>
            </tr>
        </thead>
        
 
        <tfoot>
            <tr class="bg-gray font-17 text-center footer-total">
               
                <!--<td colspan="5"><strong><?php echo app('translator')->get('sale.total'); ?>:</strong></td>-->
                <!--<td id="footer_status_count"></td>-->
                <!--<td id="footer_payment_status_count"></td>-->
                <!--<td><span class="display_currency" id="footer_purchase_total" data-currency_symbol ="true"></span></td>-->
                <!--<td class="text-left"><small>Pagamento - <span class="display_currency" id="footer_total_due" data-currency_symbol ="true"></span><br>-->
                <!--<?php echo app('translator')->get('lang_v1.purchase_return'); ?> - <span class="display_currency" id="footer_total_purchase_return_due" data-currency_symbol ="true"></span>-->
                <!--</small></td>-->
                <!--<td></td>-->
                

            </tr>
        </tfoot>
    </table>
</div><?php /**PATH /home/sefacilsistemasc/gestor.sefacilsistemas.com.br/resources/views/purchase/partials/purchase_table.blade.php ENDPATH**/ ?>