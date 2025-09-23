@extends('layouts.app')
@section('title', __('Veículos'))
@section('content')
@include('ordem_servico.nav.index')
<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>
        @lang('Veículos')
    </h1>
</section>

<!-- Main content -->
<section class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="nav-tabs-custom">
                <div class="tab-content">
                    <div class="tab-pane active" id="repair_status_tab">
                        <button type="button" class="btn btn-sm btn-primary btn-modal pull-right" data-href="{{action('VeiculoOsController@create')}}" data-container=".view_modal">
                            <i class="fa fa-plus"></i>
                            @lang( 'messages.add' )
                        </button>
                        <br><br>
                        <table class="table table-bordered table-striped" id="users_table" style="width: 100%">
                            <thead>
                                <tr>
                                    <th>Placa</th>
                                    <th>Modelo</th>
                                    <th>Cor</th>
                                    <th>Cliente</th>
                                    <th>Ação</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade modal_veiculos" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
    </div>

</section>
@stop

@section('javascript')

<script type="text/javascript">
    //Roles table
    $(document).ready(function() {
        var users_table = $('#users_table').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: '/veiculo-os',
                    columnDefs: [ {
                        "targets": [3],
                        "orderable": false,
                        "searchable": false
                    } ],
                    "columns":[
                        {"data":"placa"},
                        {"data":"modelo"},
                        {"data":"cor"},
                        {"data":"cliente"},
                        {"data":"action"}
                    ]
                });

        $(document).on('click', 'btn-delete', function() {
            swal({
                title: LANG.sure
                , text: LANG.confirm_delete_user
                , icon: "warning"
                , buttons: true
                , dangerMode: true
            , }).then((willDelete) => {
                if (willDelete) {
                    var href = $(this).data('href');
                    var data = $(this).serialize();
                    $.ajax({
                        method: "DELETE"
                        , url: href
                        , dataType: "json"
                        , data: data
                        , success: function(result) {
                            if (result.success == true) {
                                toastr.success(result.msg);
                                users_table.ajax.reload();
                            } else {
                                toastr.error(result.msg);
                            }
                        }
                    });
                }
            });
        });

        $(document).on('click', '.edit_veiculos', function(e) {
            e.preventDefault();
            $('div.modal_veiculos').load($(this).attr('href'), function() {
                $(this).modal('show');
            });
        });

    });

</script>
@endsection
