@extends('layouts.app')
@section('title', __('Editar OS'))

@section('content')
@include('ordem_servico.nav.index')
<!-- Content Header (Page header) -->
<section class="content-header no-print">
    <h1>
        @lang('Editar Ordem de Serviço:') {{$ordem->id}}
    </h1>
</section>
<section class="content">
    @if(!empty($repair_settings))
    @php
    $product_conf = isset($repair_settings['product_configuration']) ? explode(',', $repair_settings['product_configuration']) : [];

    $defects = isset($repair_settings['problem_reported_by_customer']) ? explode(',', $repair_settings['problem_reported_by_customer']) : [];

    $product_cond = isset($repair_settings['product_condition']) ? explode(',', $repair_settings['product_condition']) : [];
    @endphp
    @else
    @php
    $product_conf = [];
    $defects = [];
    $product_cond = [];
    @endphp
    @endif
    {!! Form::open(['url' => action('OrdemServicoController@update', [$ordem->id]), 'method' => 'put', 'id' => '']) !!}

    @includeIf('repair::job_sheet.partials.scurity_modal')
    <div class="box box-solid">
        <div class="box-body">
            <div class="row">
                @if(count($business_locations) == 1)
                @php
                $default_location = current(array_keys($business_locations->toArray()));
                @endphp
                @else
                @php $default_location = null;
                @endphp
                @endif
                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('location_id', 'Localização:*' )!!}
                        {!! Form::select('location_id', $business_locations, $default_location, ['class' => 'form-control', 'placeholder' => __('messages.please_select'), 'required', 'style' => 'width: 100%;']); !!}
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        {{-- {!! Form::label('contact_id', __('contact.customer') . ':*') !!}
                        <div class="input-group">
                            <span class="input-group-addon">
                                <i class="fa fa-user"></i>
                            </span>

                            {!! Form::select('contact_id', [], null, ['class' => 'form-control mousetrap', 'id' => 'customer_id', 'placeholder' => 'Enter Customer name / phone', 'required']); !!}
                            <span class="input-group-btn">
                                <button type="button" class="btn btn-default bg-white btn-flat add_new_customer" data-name=""><i class="fa fa-plus-circle text-primary fa-lg"></i></button>
                            </span>
                        </div> --}}
                        {!! Form::label('cliente_id', __('Cliente') . ':*') !!}
                        <select class="form-control select2 required" name="cliente_id" id="">
                            <option value="">Selecione um cliente</option>
                            @foreach ($clientes as $c)
                            <option @isset($ordem) @if($c->id == $ordem->cliente_id) selected @endif @endif value="{{$c->id}}">{{$c->name}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-5">
                    {!! Form::label('service_type', __('repair::lang.service_type').':*', ['style' => 'margin-left:20px;'])!!}
                    <br>
                    <label class="radio-inline">
                        {!! Form::radio('service_type', 'carry_in', ($ordem->service_type == 'carry_in') ? true : false, [ 'class' => 'input-icheck', 'required']); !!}
                        @lang('repair::lang.carry_in')
                    </label>
                    <label class="radio-inline">
                        {!! Form::radio('service_type', 'pick_up', ($ordem->service_type == 'pick_up') ? true : false, [ 'class' => 'input-icheck']); !!}
                        @lang('repair::lang.pick_up')
                    </label>
                    <label class="radio-inline radio_btns">
                        {!! Form::radio('service_type', 'on_site', ($ordem->service_type == 'on_site') ? true : false, [ 'class' => 'input-icheck']); !!}
                        @lang('repair::lang.on_site')
                    </label>
                </div>
            </div>
            <div class="row pick_up_onsite_addr" style="display: none;">
                <div class="col-md-6">
                    <div class="form-group">
                        {!! Form::label('pick_up_on_site_addr', __('repair::lang.pick_up_on_site_addr') . ':') !!}
                        {!! Form::textarea('pick_up_on_site_addr', $ordem->pick_up_on_site_addr, ['class' => 'form-control ', 'id' => 'pick_up_on_site_addr', 'placeholder' => __(''), 'rows' => 3]); !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="box box-solid">
        <div class="box-body">
            <div class="row">
                <div class="col-sm-4">
                    <div class="form-group">
                        {!! Form::label('funcionario_id', __('Atribuir um Profissional') . ':*') !!}
                        <select class="form-control select2 required" name="funcionario_id" id="">
                            <option value="">Selecione um profissional</option>
                            @foreach ($funcionario as $f)
                            <option @isset($ordem) @if($f->id == $ordem->funcionario_id) selected @endif @endif value="{{$f->id}}">{{$f->nome}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-sm-4">
                    <div class="form-group">
                        {!! Form::label('veiculo_id', __('Selecionar Veículo') . ':*') !!}
                        <select class="form-control select2 required" name="veiculo_id" id="">
                            <option value="">Selecione um Veículo</option>
                            @foreach ($veiculos as $v)
                            <option @isset($ordem) @if($v->id == $ordem->veiculo_id) selected @endif @endif value="{{$v->id}}">{{$v->modelo}} - {{$v->cor}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="form-group">
                        {!! Form::label('comment_by_ss', __('Comentário do profissional') . ':') !!}
                        {!! Form::textarea('comment_by_ss', $ordem->descricao, ['class' => 'form-control ', 'rows' => '3']); !!}
                    </div>
                </div>
                <div class="col-sm-4">
                    <div class="form-group">
                        <label for="status_id">{{__('sale.status') . ':*'}}</label>
                        <select name="status_id" class="form-control status" id="" required>
                            <option value="">Selecionar</option>
                            @foreach($repair_statuses as $repair_status)
                            <option @isset($ordem) @if($repair_status->id == $ordem->status_id) selected @endif @endif value="{{$repair_status->id}}">{{$repair_status->name}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        {!! Form::label('delivery_date', __('repair::lang.expected_delivery_date') . ':') !!}
                        @show_tooltip(__('repair::lang.delivery_date_tooltip'))
                        <div class="input-group">
                            <span class="input-group-addon">
                                <i class="fa fa-calendar"></i>
                            </span>
                            {!! Form::text('delivery_date', \Carbon\Carbon::parse($ordem->data_entrega)->format('d/m/Y H:i:s'), ['class' => 'form-control', 'readonly']); !!}
                            <span class="input-group-addon">
                                <i class="fas fa-times-circle cursor-pointer clear_delivery_date"></i>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="clearfix"></div>
                <hr>
                <div class="clearfix"></div>
                <div class="col-md-12">
                    <div class="form-group">
                        {!! Form::label('observacao', __('Observação') . ':') !!}
                        {!! Form::textarea('observacao', $ordem->observacao, ['class' => 'form-control ', 'rows' => '3']); !!}
                    </div>
                </div>
                <div class="col-sm-12 text-right">
                    {{-- <input type="hidden" name="submit_type" id="submit_type">
                    <button type="submit" class="btn btn-success submit_button" value="save_and_add_parts" id="save_and_add_parts">
                        @lang('Atualizar e Adicionar Peças e Serviços')
                    </button> --}}
                    <button type="submit" class="btn btn-primary submit_button" value="submit" id="save">
                        @lang('Atualizar')
                    </button>
                </div>
            </div>
        </div>
    </div>
    {!! Form::close() !!}
    <div class="modal fade contact_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
        @include('contact.create', ['quick_add' => true])
    </div>
</section>
@stop

@section('javascript')
<script src="{{ asset('js/pos.js?v=' . $asset_v) }}"></script>

<script type="text/javascript">
    $('#delivery_date').datetimepicker({
        format: moment_date_format + ' ' + moment_time_format
        , ignoreReadonly: true
    , });
    
    $(document).on('click', '.clear_delivery_date', function() {
        $('#delivery_date').data("DateTimePicker").clear();
    });

    $('input[type=radio][name=service_type]').on('ifChecked', function() {
        if ($(this).val() == 'pick_up' || $(this).val() == 'on_site') {
            $("div.pick_up_onsite_addr").show();
        } else {
            $("div.pick_up_onsite_addr").hide();
        }
    });

</script>

@endsection
