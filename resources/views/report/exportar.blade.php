@extends('layouts.app')
@section('title', 'Exportar para Excel')

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>
        <small>Exportar Informações</small>
    </h1>
    
</section>

<!-- Main content -->
<section class="content">

    @component('components.widget', ['class' => 'box-primary', 'title' => 'Lista de Documentos que pode exportar'])
    @can('user.create')
    @slot('tool')

    @endslot
    @endcan
    @can('user.view')

    

    <section class="content">
        <div class="row no-print">
            <div class="col-md-12">
                <div class="col-md-6">
                    <div class="form-group">
                        <div class="col-md-4">
                            <h4>Produtos</h4>
                        </div>
                        <a style="margin-left: 5px; margin-top: 5px;" href="/exportar/produtos" class="btn btn-md btn-primary">
                        <i class="fa fa-arrow-down"></i> Exportar Excel</a>
                    </div>
                </div>
            </div>
            
            <div class="col-md-12">
                <div class="col-md-6">
                    <div class="form-group">
                        <div class="col-md-4">
                            <h4>Clientes</h4>
                        </div>
                        <a style="margin-left: 5px; margin-top: 5px;" href="/exportar/clientes" class="btn btn-md btn-primary">
                        <i class="fa fa-arrow-down"></i> Exportar Excel</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    @endcan
    @endcomponent

    <!-- <div class="modal fade user_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
    </div> -->

</section>
<!-- /.content -->
@stop
@section('javascript')

<!-- <script type="text/javascript">
    //Roles table
    $(document).ready( function(){
        var users_table = $('#users_table').DataTable({
            processing: true,
            serverSide: true,
            ajax: '/reports/produtos-ncm',
            columnDefs: [ {
                "targets": [2],
                "orderable": false,
                "searchable": false
            } ],
            "columns":[
                { data: 'name', name: 'name' },
                { data: 'ncm', name: 'ncm' },
            ]
        });
    });
    
</script> -->
@endsection