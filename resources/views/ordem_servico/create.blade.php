@extends('layouts.app')
@section('title', __('repair::lang.add_job_sheet'))

@section('content')
@include('ordem_servico.nav.index')
<!-- Content Header (Page header) -->
<section class="content-header no-print">
    <h1>
        @lang('Nova Ordem de Serviço')
        <small>@lang('repair::lang.create')</small>
    </h1>
</section>
<section class="content">

    <input type="hidden" id="amount_rounding_method" value="{{$pos_settings['amount_rounding_method'] ?? ''}}">

    @if(!empty($pos_settings['allow_overselling']))
    <input type="hidden" id="is_overselling_allowed">
    @endif
    @if(session('business.enable_rp') == 1)
    <input type="hidden" id="reward_point_enabled">
    @endif

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
    {!! Form::open(['action' => 'OrdemServicoController@store', 'id' => '', 'method' => 'post', 'files' => true]) !!}
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
                        {!! Form::label('contact_id', __('contact.customer') . ':*') !!}
                        <div class="input-group">
                            {{-- <input type="hidden" id="default_customer_id" value="{{ $walk_in_customer['id'] ?? ''}}">
                            <input type="hidden" id="default_customer_name" value="{{ $walk_in_customer['name'] ?? ''}}"> --}}

                            <input type="hidden" id="default_customer_id" value="{{ $walk_in_customer['id']}}">
                            <input type="hidden" id="default_customer_name" value="{{ $walk_in_customer['name']}}">

                            {!! Form::select('contact_id', [], null, ['class' => 'form-control mousetrap', 'id' => 'customer_id', 'placeholder' => 'Entre com nome do cliente', 'required']); !!}

                            <span class="input-group-btn">
                                <button type="button" class="btn btn-default bg-white btn-flat add_new_customer" data-name=""><i class="fa fa-plus-circle text-primary fa-lg"></i></button>
                            </span>
                        </div>
                    </div>
                </div>

                <div class="col-md-5">
                    {!! Form::label('service_type', __('repair::lang.service_type').':*', ['style' => 'margin-left:20px;'])!!}
                    <br>
                    <label class="radio-inline">
                        {!! Form::radio('service_type', 'carry_in', true, [ 'class' => 'input-icheck', 'required']); !!}
                        @lang('repair::lang.carry_in')
                    </label>
                    <label class="radio-inline">
                        {!! Form::radio('service_type', 'pick_up', false, [ 'class' => 'input-icheck']); !!}
                        @lang('repair::lang.pick_up')
                    </label>
                    <label class="radio-inline radio_btns">
                        {!! Form::radio('service_type', 'on_site', false, [ 'class' => 'input-icheck']); !!}
                        @lang('repair::lang.on_site')
                    </label>
                </div>
            </div>
            <div class="row pick_up_onsite_addr" style="display: none;">
                <div class="col-md-6">
                    <div class="form-group">
                        {!! Form::label('pick_up_on_site_addr', __('repair::lang.pick_up_on_site_addr') . ':') !!}
                        {!! Form::textarea('pick_up_on_site_addr',null, ['class' => 'form-control ', 'id' => 'pick_up_on_site_addr', 'placeholder' => __(''), 'rows' => 3]); !!}
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
                        <select class="form-control required" name="funcionario_id" id="">
                            <option value="">Selecione um profissional</option>
                            @foreach ($funcionario as $f)
                            <option value="{{$f->id}}">{{$f->nome}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="col-sm-4">
                    <div class="form-group">
                        {!! Form::label('veiculo_id', 'Veiculo' . ':*') !!}
                        <div class="input-group">
                            {!! Form::select('veiculo_id', $veiculos, '', ['class' => 'form-control select2', 'id' => 'veiculo_id', 'required', 'placeholder' => 'Veiculo']); !!}
                            <span class="input-group-btn">
                                <button type="button" class="btn btn-default bg-white btn-flat add_new_veiculo" data-name=""><i class="fa fa-plus-circle text-primary fa-lg"></i></button>
                            </span>
                        </div>
                    </div>
                </div>

                <div class="col-md-12">
                    <div class="form-group">
                        {!! Form::label('comment_by_ss', __('Comentário do profissional') . ':') !!}
                        {!! Form::textarea('comment_by_ss', null, ['class' => 'form-control ', 'rows' => '3']); !!}
                    </div>
                </div>
                <div class="col-sm-4">
                    <div class="form-group">
                        <label for="status_id">{{__('sale.status') . ':*'}}</label>
                        <select name="status_id" class="form-control status" id="status_id">
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
                            {!! Form::text('delivery_date', null, ['class' => 'form-control', 'readonly']); !!}
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
                        {!! Form::textarea('observacao', null, ['class' => 'form-control ', 'rows' => '3']); !!}
                    </div>
                </div>

                {{-- colocado essa parte para testar o js, porem essa parte nao usa na abertura da OS  --}}
                <div class="input-group">
                    {{-- <div class="input-group-btn">
                        <button type="button" class="btn btn-default bg-white btn-flat" data-toggle="modal" data-target="#configure_search_modal" title="{{__('lang_v1.configure_product_search')}}"><i class="fa fa-barcode"></i></button>
                    </div> --}}
                    {!! Form::hidden('search_product', null, ['class' => 'form-control mousetrap', 'id' => 'search_product', 'placeholder' => __('lang_v1.search_product_placeholder'),
                    'disabled' => is_null($default_location)? true : false,
                    'autofocus' => is_null($default_location)? false : true,
                    ]); !!}
                    {{-- <span class="input-group-btn">
                        <button type="button" class="btn btn-default bg-white btn-flat pos_add_quick_product" data-href="{{action('ProductController@quickAdd')}}" data-container=".quick_add_product_modal"><i class="fa fa-plus-circle text-primary fa-lg"></i></button>
                    </span> --}}
                </div>
                {{-- fim --}}

                <div class="col-sm-12 text-right">
                    <input type="hidden" name="submit_type" id="submit_type">
                    <button type="submit" class="btn btn-success submit_button" value="save_and_add_parts" id="save_and_add_parts">
                        @lang('Salvar e Adicionar Peças e Serviços')
                    </button>
                    <button type="submit" class="btn btn-primary submit_button" value="submit" id="save">
                        @lang('messages.save')
                    </button>
                </div>
            </div>
        </div>
    </div>
    {!! Form::close() !!}
    <!-- /form close -->

</section>

<div class="modal fade contact_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
    @include('contact.create', ['quick_add' => true])
</div>

<div class="modal fade veiculo_os" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
    @include('veiculo_os.create', ['quick_add' => true])
</div>

@stop
@section('css')
{{-- @include('repair::job_sheet.tagify_css') --}}
@stop


@section('javascript')

<script src="{{ asset('js/pos.js?v=' . $asset_v) }}"></script>

<script type="text/javascript">

    $(document).on('click', '.add_new_veiculo', function() {
        $('.veiculo_os').modal('show');
    });

    $('.veiculo_os').on('hidden.bs.modal', function() {
        $('form#add_veiculo_os')
            .find('button[type="submit"]')
            .removeAttr('disabled');
        $('form#add_veiculo_os')[0].reset();
    });


    $(document).on("focus", ".placa", function() {
        $(this).mask("AAA-AAAA", {
            reverse: true
        })
    });

    $(document).on("focus", ".cpf", function() {
        $(this).mask("000.000.000-00", {
            reverse: true
        })
    });

    $(document).on("focus", ".ano", function() {
        $(this).mask("0000", {
            reverse: true
        })
    });

    $(document).ready( function() {
        $('.submit_button').click( function(){
            $('#submit_type').val($(this).attr('value'));
        });
        $('form#job_sheet_form').validate({
            errorPlacement: function(error, element) {
                if (element.parent('.iradio_square-blue').length) {
                    error.insertAfter($(".radio_btns"));
                } else if (element.hasClass('status')) {
                    error.insertAfter(element.parent());
                } else {
                    error.insertAfter(element);
                }
            },
            submitHandler: function(form) {
                form.submit();
            }
        });

        var data = [{
          id: "",
          text: '@lang("messages.please_select")',
          html: '@lang("messages.please_select")',
          is_complete : '0',
        }, 
        @foreach($repair_statuses as $repair_status)
            {
            id: {{$repair_status->id}},
            is_complete : '{{$repair_status->is_completed_status}}',
            @if(!empty($repair_status->color))
                text: '<i class="fa fa-circle" aria-hidden="true" style="color: {{$repair_status->color}};"></i> {{$repair_status->name}}',
                title: '{{$repair_status->name}}'
            @else
                text: "{{$repair_status->name}}"
            @endif
            },
        @endforeach
        ];

        $("select#status_id").select2({
            data: data,
            escapeMarkup: function(markup) {
                return markup;
            },
            templateSelection: function (data, container) {
                $(data.element).attr('data-is_complete', data.is_complete);
                return data.text;
            }
        });

        @if(!empty($default_status))
            $("select#status_id").val({{$default_status}}).change();
        @endif

        $('#delivery_date').datetimepicker({
            format: moment_date_format + ' ' + moment_time_format,
            ignoreReadonly: true,
        });

        $(document).on('click', '.clear_delivery_date', function() {
            $('#delivery_date').data("DateTimePicker").clear();
        });

        var lock = new PatternLock("#pattern_container", {
            onDraw:function(pattern){
                $('input#security_pattern').val(pattern);
            },
            enableSetPattern: true
        });

        $('input[type=radio][name=service_type]').on('ifChecked', function(){
          if ($(this).val() == 'pick_up' || $(this).val() == 'on_site') {
            $("div.pick_up_onsite_addr").show();
          } else {
            $("div.pick_up_onsite_addr").hide();
          }
        });
    });

</script>
@endsection
