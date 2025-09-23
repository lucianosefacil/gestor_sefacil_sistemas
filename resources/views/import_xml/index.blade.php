@extends('layouts.app')
@section('title', 'Importação de XML')

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>Importação de XML</h1>
</section>

<!-- Main content -->
<section class="content">
    @if (session('notification') || !empty($notification))
    <div class="row">
        <div class="col-sm-12">
            <div class="alert alert-danger alert-dismissible">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                @if(!empty($notification['msg']))
                {{$notification['msg']}}
                @elseif(session('notification.msg'))
                {{ session('notification.msg') }}
                @endif
            </div>
        </div>  
    </div>     
    @endif
    <div class="row">
        <div class="col-md-12">
            @component('components.widget')
            {!! Form::open(['url' => action('ImportXmlController@preview'), 'method' => 'post', 'enctype' => 'multipart/form-data' ]) !!}

            <div class="row">
                <div class="col-sm-12">
                    <div class="col-sm-3">
                        <div class="form-group">
                            {!! Form::label('file', __( 'product.file_to_import' ) . ':') !!}
                            {!! Form::file('file', ['required' => 'required', 'accept' => '.zip']); !!}
                        </div>
                    </div>

                    <div class="col-sm-2">
                        <div class="form-group">
                            {!! Form::label('type', 'Tipo:') !!}
                            {!! Form::select('type', ['' => 'Selecione', 'nfe' => 'NFe', 'nfce' => 'NFCe'], null, ['class' => 'form-control', 'required']); !!}
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            {!! Form::label('location_id', __('business.business_location') . ':*') !!}
                            {!! Form::select('location_id', $business_locations, null, ['class' => 'form-control', 'required', 'placeholder' => __('messages.please_select')]); !!}
                        </div>
                    </div>
                    <div class="col-sm-3">
                        <br>
                        <button type="submit" class="btn btn-primary">@lang('lang_v1.upload_and_review')</button>
                    </div>
                </div>
            </div>

            {!! Form::close() !!}
            @endcomponent
        </div>
    </div>
    
</section>
@stop
@section('javascript')
<script type="text/javascript">
    $(document).on('click', 'a.revert_import', function(e){
        e.preventDefault();
        swal({
            title: LANG.sure,
            icon: 'warning',
            buttons: true,
            dangerMode: true,
        }).then(willDelete => {
            if (willDelete) {
                window.location = $(this).attr('href');
            } else {
                return false;
            }
        });
    });
</script>
@endsection