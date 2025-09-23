<?php $__env->startSection('title', __('lang_v1.'.$type.'s')); ?>
<?php
    $api_key = env('GOOGLE_MAP_API_KEY');
?>
<?php if(!empty($api_key)): ?>
    <?php $__env->startSection('css'); ?>
        <?php echo $__env->make('contact.partials.google_map_styles', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    <?php $__env->stopSection(); ?>
<?php endif; ?>
<?php $__env->startSection('content'); ?>
<!-- Content Header (Page header) -->
<section class="content-header">
    <h1> <?php echo app('translator')->get('lang_v1.'.$type.'s'); ?>
        <small><?php echo app('translator')->get( 'contact.manage_your_contact', ['contacts' =>  __('lang_v1.'.$type.'s') ]); ?></small>
    </h1>
    <!-- <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Level</a></li>
        <li class="active">Here</li>
    </ol> -->
</section>

<!-- Main content -->
<section class="content">

    <?php if( $type == 'customer'): ?>

    <?php $__env->startComponent('components.filters', ['title' => __('report.filters')]); ?>
    <div class="col-md-3">
        <div class="form-group">
            <?php echo Form::label('cpf_cnpj',  __('Pesquisar por CPF') . ':'); ?>

            <?php echo Form::text('cpf_cnpj', null, ['class' => 'form-control', 'id' => 'cpf_cnpj', 'placeholder' => __('CPF')]); ?>

        </div>
    </div>
    <?php echo $__env->renderComponent(); ?>
    <?php endif; ?>

    <input type="hidden" value="<?php echo e($type, false); ?>" id="contact_type">
    <?php $__env->startComponent('components.widget', ['class' => 'box-primary', 'title' => __( 'contact.all_your_contact', ['contacts' => __('lang_v1.'.$type.'s') ])]); ?>
        <?php if(auth()->user()->can('supplier.create') || auth()->user()->can('customer.create')): ?>
            <?php $__env->slot('tool'); ?>
                <div class="box-tools">
                    <button type="button" class="btn btn-block btn-primary btn-modal" 
                    data-href="<?php echo e(action('ContactController@create', ['type' => $type]), false); ?>" 
                    data-container=".contact_modal">
                    <i class="fa fa-plus"></i> <?php echo app('translator')->get('messages.add'); ?></button>
                </div>
            <?php $__env->endSlot(); ?>
        <?php endif; ?>
        <?php if(auth()->user()->can('supplier.view') || auth()->user()->can('customer.view')): ?>
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="contact_table">
                    <thead>
                        <tr>
                            <th><?php echo app('translator')->get('messages.action'); ?></th>
                            <th><?php echo app('translator')->get('lang_v1.contact_id'); ?></th>
                            <?php if($type == 'supplier'): ?> 
                                <th><?php echo app('translator')->get('business.business_name'); ?></th>
                                <th><?php echo app('translator')->get('contact.name'); ?></th>
                                <th><?php echo app('translator')->get('business.email'); ?></th>

                                <th><?php echo app('translator')->get('account.opening_balance'); ?></th>
                                <th><?php echo app('translator')->get('lang_v1.added_on'); ?></th>
                                <th>Telefone</th>
                                <th><?php echo app('translator')->get('contact.total_purchase_due'); ?></th>
                                <th><?php echo app('translator')->get('lang_v1.total_purchase_return_due'); ?></th>
                            <?php elseif( $type == 'customer'): ?>
                                <th><?php echo app('translator')->get('user.name'); ?></th>
                                <th><?php echo app('translator')->get('business.email'); ?></th>
                                <th><?php echo app('translator')->get('lang_v1.added_on'); ?></th>
                                <?php if($reward_enabled): ?>
                                    <th id="rp_col"><?php echo e(session('business.rp_name'), false); ?></th>
                                <?php endif; ?>

                                <th></th>
                                <th><?php echo app('translator')->get('business.address'); ?></th>
                                <th>Telefone</th>
                                <th><?php echo app('translator')->get('contact.total_sale_due'); ?></th>
                                <th><?php echo app('translator')->get('lang_v1.total_sell_return_due'); ?></th>

                            <?php endif; ?>
                            <?php
                                $custom_labels = json_decode(session('business.custom_labels'), true);
                            ?>
                           
                        </tr>
                    </thead>
                    <tfoot>
                        <tr class="bg-gray font-17 text-center footer-total">

                            <td
                                <?php if($type == 'supplier'): ?>
                                    colspan="8"
                                <?php elseif( $type == 'customer'): ?>
                                    <?php if($reward_enabled): ?>
                                        colspan="9"
                                    <?php else: ?>
                                        colspan="8"
                                    <?php endif; ?>
                                <?php endif; ?>>
                                    <strong>
                                        <?php echo app('translator')->get('sale.total'); ?>:
                                    </strong>
                            </td>
                            <td><span class="display_currency" id="footer_contact_due" data-currency_symbol ="true"></span></td>
                            <td><span class="display_currency" id="footer_contact_return_due" data-currency_symbol ="true"></span></td>
                            <!-- <td></td>
                            <td></td>
                            <td></td>
                            <td></td> -->
                        </tr>
                    </tfoot>
                </table>
            </div>
        <?php endif; ?>
    <?php echo $__env->renderComponent(); ?>

    <div class="modal fade contact_modal" tabindex="-1" role="dialog" 
    	aria-labelledby="gridSystemModalLabel">
    </div>
    <div class="modal fade pay_contact_due_modal" tabindex="-1" role="dialog" 
        aria-labelledby="gridSystemModalLabel">
    </div>

</section>
<!-- /.content -->
<?php $__env->stopSection(); ?>
<?php $__env->startSection('javascript'); ?>
<?php if(!empty($api_key)): ?>
<script>
  // This example adds a search box to a map, using the Google Place Autocomplete
  // feature. People can enter geographical searches. The search box will return a
  // pick list containing a mix of places and predicted search terms.

  // This example requires the Places library. Include the libraries=places
  // parameter when you first load the API. For example:
  // <script src="https://maps.googleapis.com/maps/api/js?key=YOUR_API_KEY&libraries=places">

  function initAutocomplete() {
    var map = new google.maps.Map(document.getElementById('map'), {
      center: {lat: -33.8688, lng: 151.2195},
      zoom: 10,
      mapTypeId: 'roadmap'
    });

    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function (position) {
            initialLocation = new google.maps.LatLng(position.coords.latitude, position.coords.longitude);
            map.setCenter(initialLocation);
        });
    }


    // Create the search box and link it to the UI element.
    var input = document.getElementById('shipping_address');
    var searchBox = new google.maps.places.SearchBox(input);
    map.controls[google.maps.ControlPosition.TOP_LEFT].push(input);

    // Bias the SearchBox results towards current map's viewport.
    map.addListener('bounds_changed', function() {
      searchBox.setBounds(map.getBounds());
    });

    var markers = [];
    // Listen for the event fired when the user selects a prediction and retrieve
    // more details for that place.
    searchBox.addListener('places_changed', function() {
      var places = searchBox.getPlaces();

      if (places.length == 0) {
        return;
      }

      // Clear out the old markers.
      markers.forEach(function(marker) {
        marker.setMap(null);
      });
      markers = [];

      // For each place, get the icon, name and location.
      var bounds = new google.maps.LatLngBounds();
      places.forEach(function(place) {
        if (!place.geometry) {
          console.log("Returned place contains no geometry");
          return;
        }
        var icon = {
          url: place.icon,
          size: new google.maps.Size(71, 71),
          origin: new google.maps.Point(0, 0),
          anchor: new google.maps.Point(17, 34),
          scaledSize: new google.maps.Size(25, 25)
        };

        // Create a marker for each place.
        markers.push(new google.maps.Marker({
          map: map,
          icon: icon,
          title: place.name,
          position: place.geometry.location
        }));

        //set position field value
        var lat_long = [place.geometry.location.lat(), place.geometry.location.lng()]
        $('#position').val(lat_long);

        if (place.geometry.viewport) {
          // Only geocodes have viewport.
          bounds.union(place.geometry.viewport);
        } else {
          bounds.extend(place.geometry.location);
        }
      });
      map.fitBounds(bounds);
    });
  }

</script>
<script src="https://maps.googleapis.com/maps/api/js?key=<?php echo e($api_key, false); ?>&libraries=places" async defer></script>
<script type="text/javascript">
    $(document).on('shown.bs.modal', '.contact_modal', function(e) {
        initAutocomplete();
    });
</script>
<?php endif; ?>

<script>

   $(document).ready(function() {

    if ( $.fn.DataTable.isDataTable('#contact_table') ) {
        $('#contact_table').DataTable().destroy();
    }

    var table = $('#contact_table').DataTable({
    processing: true,
    serverSide: true,
    ajax: {
        url: '/contacts?type=customer',
        data: function (d) {
            d.cpf_cnpj = $('#cpf_cnpj').val();
        }
    },
    columns: [
        { data: 'action', name: 'action', orderable: false, searchable: false },
        { data: 'contact_id', name: 'contacts.contact_id' },
        { data: 'name', name: 'contacts.name' },
        { data: 'email', name: 'contacts.email' },
        { data: 'created_at', name: 'contacts.created_at' },
        { data: null, defaultContent: '', orderable: false, searchable: false }, // coluna vazia
        { data: 'address', name: 'address', orderable: false, searchable: false }, // <--- address NÃO existe no banco
        { data: 'mobile', name: 'contacts.mobile' },
        { data: 'due', name: 'due', orderable: false, searchable: false }, // <--- calculado
        { data: 'return_due', name: 'return_due', orderable: false, searchable: false } // <--- calculado
    ]
});


    $('#cpf_cnpj').on('keyup change', function() {
        table.ajax.reload();
    });
});
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/sefacilsistemasc/gestor.sefacilsistemas.com.br/resources/views/contact/index.blade.php ENDPATH**/ ?>