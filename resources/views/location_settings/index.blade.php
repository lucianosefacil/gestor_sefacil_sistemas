@extends('layouts.app')
@section('title', __('messages.business_location_settings'))

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>@lang( 'messages.business_location_settings' ) - {{$location->name}}</h1>
    <!-- <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Level</a></li>
        <li class="active">Here</li>
    </ol> -->
</section>

<!-- Main content -->
<section class="content">
    <div class="row">
        <div class="col-md-12">
          <!-- Custom Tabs -->
          <div class="nav-tabs-custom">
            <ul class="nav nav-tabs">
                <li class="active"><a href="#tab_1" data-toggle="tab" aria-expanded="true">@lang('receipt.receipt_settings')</a></li>
                <li><a href="#tab_2" data-toggle="tab" aria-expanded="true">Certificado</a></li>
                <li><a href="#tab_3" data-toggle="tab" aria-expanded="false">TEF</a></li>
            </ul>
            <div class="tab-content">
                <div class="tab-pane active" id="tab_1">
                    <div class="row">
                        <div class="col-md-12">
                            <h4>@lang('receipt.receipt_settings')
                                <small>@lang( 'receipt.receipt_settings_mgs')</small>
                            </h4>
                        </div>
                    </div>
                    <br>
                    <div class="row">
                        <div class="col-md-12">
                            {!! Form::open(['url' => route('location.settings_update', [$location->id]), 'method' => 'post', 'id' => 'bl_receipt_setting_form']) !!}

                            <div class="col-sm-4">
                                <div class="form-group">
                                    {!! Form::label('print_receipt_on_invoice', 'Impressão automática após a conclusão' . ':') !!}
                                    @show_tooltip(__('tooltip.print_receipt_on_invoice'))
                                    <div class="input-group">
                                        <span class="input-group-addon">
                                            <i class="fa fa-file-alt"></i>
                                        </span>
                                        {!! Form::select('print_receipt_on_invoice', $printReceiptOnInvoice, $location->print_receipt_on_invoice, ['class' => 'form-control select2', 'required']); !!}
                                    </div>
                                </div>
                            </div>

                            <div class="col-sm-4">
                                <div class="form-group">
                                    {!! Form::label('receipt_printer_type', __('receipt.receipt_printer_type') . ':*') !!} @show_tooltip(__('tooltip.receipt_printer_type'))
                                    <div class="input-group">
                                        <span class="input-group-addon">
                                            <i class="fa fa-print"></i>
                                        </span>
                                        {!! Form::select('receipt_printer_type', $receiptPrinterType, $location->receipt_printer_type, ['class' => 'form-control select2', 'required']); !!}
                                    </div>
                                    @if(config('app.env') == 'demo')
                                    <span class="help-block">Only Browser based option is enabled in demo.</span>
                                    @endif

                                </div>
                            </div>

                            <div class="col-sm-4" id="location_printer_div">
                                <div class="form-group">
                                    {!! Form::label('printer_id', 'Impressoras de recibos' . ':*') !!}
                                    <div class="input-group">
                                        <span class="input-group-addon">
                                            <i class="fa fa-share-alt"></i>
                                        </span>
                                        {!! Form::select('printer_id', $printers, $location->printer_id, ['class' => 'form-control select2']); !!}
                                    </div>
                                </div>
                            </div>
                            <div class="clearfix"></div>
                            <br/>

                            <div class="col-sm-3">
                                <div class="form-group">
                                    {!! Form::label('invoice_layout_id', __('invoice.invoice_layout') . ':*') !!} @show_tooltip(__('tooltip.invoice_layout'))
                                    <div class="input-group">
                                        <span class="input-group-addon">
                                            <i class="fa fa-info"></i>
                                        </span>
                                        {!! Form::select('invoice_layout_id', $invoice_layouts, $location->invoice_layout_id, ['class' => 'form-control select2', 'required']); !!}
                                    </div>
                                </div>
                            </div>

                            <div class="col-sm-3">
                                <div class="form-group">
                                    {!! Form::label('invoice_scheme_id', __('invoice.invoice_scheme') . ':*') !!} @show_tooltip(__('tooltip.invoice_scheme'))
                                    <div class="input-group">
                                        <span class="input-group-addon">
                                            <i class="fa fa-info"></i>
                                        </span>
                                        {!! Form::select('invoice_scheme_id', $invoice_schemes, $location->invoice_scheme_id, ['class' => 'form-control select2', 'required']); !!}
                                    </div>
                                </div>
                            </div>


                            <div class="col-sm-12">
                                <div class="form-group">
                                    {!! Form::label('invoice_scheme_id', 'Informação complementar' . ':*') !!} @show_tooltip('Informação complementar para NFe')
                                    {!! Form::textarea('info_complementar', $location->info_complementar, ['class' => 'form-control', 'rows' => 3]); !!}

                                </div>
                            </div>




                            <div class="row">
                                <div class="col-sm-12">
                                    <button class="btn btn-primary pull-right" type="submit">@lang('messages.update')</button>
                                </div>
                            </div>
                            {!! Form::close() !!}
                        </div>
                    </div>
                </div>

                <div class="tab-pane" id="tab_2">
                    <div class="row">
                        <div class="col-md-12">
                            <h4>Configuração de Certificado
                            </h4>
                        </div>
                    </div>
                    <br>
                    <div class="row">
                        <div class="col-md-12">

                            {!! Form::open(['url' => route('location.settings_update_certificado', [$location->id]), 'method' => 'post', 'id' => 'bl_receipt_setting_form', 'files' => true ]) !!}


                            <div class="row">
                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label for="certificado">Certificado:</label>
                                        <input name="certificado" type="file" id="certificado">
                                    </div>
                                </div>

                                <div class="col-sm-3">
                                    <div class="form-group">
                                        {!! Form::label('senha_certificado', 'Senha' . ':') !!}
                                        {!! Form::text('senha_certificado', '', ['class' => 'form-control',
                                        'placeholder' => 'Senha']); !!}
                                    </div>
                                </div>

                                @if($infoCertificado != null && $infoCertificado != -1)
                                <h5>Serial: <strong>{{$infoCertificado['serial']}}</strong></h5>
                                <h5>Expiração: <strong>{{$infoCertificado['expiracao']}}</strong></h5>
                                <h5>ID: <strong>{{$infoCertificado['id']}}</strong></h5>
                                @endif

                                @if($infoCertificado == -1)
                                <h5 style="color: red">Erro na leitura do certificado, verifique a senha e outros dados, e realize o upload novamente!!</h5>
                                @endif
                            </div>

                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-12">
                            <button class="btn btn-primary pull-right" type="submit">Salvar</button>
                        </div>
                    </div>
                    {!! Form::close() !!}

                </div>
                <!-- /.tab_2 -->

                <!-- Tab 3: TEF -->
                <div class="tab-pane" id="tab_3">
                    <div class="row">
                        <div class="col-md-12">
                            <h4>Configuração TEF (GetCard)
                                <small>Configure o código de certificação TEF para esta localização</small>
                            </h4>
                        </div>
                    </div>
                    <br>
                    <div class="row">
                        <div class="col-md-12">
                            {!! Form::open(['url' => route('location.settings_update', [$location->id]), 'method' => 'post', 'id' => 'bl_tef_setting_form']) !!}

                            <div class="col-sm-6">
                                <div class="form-group">
                                    {!! Form::label('tef_registro_certificacao', 'Código de Certificação TEF-GP (GetCard):') !!}
                                    <small class="text-muted">Código fornecido pela GetCard para certificação</small>
                                    <div class="input-group">
                                        <span class="input-group-addon">
                                            <i class="fa fa-key"></i>
                                        </span>
                                        {!! Form::text('tef_registro_certificacao', $location->tef_registro_certificacao, [
                                            'class' => 'form-control',
                                            'placeholder' => 'Ex: G45J35G3JH45B435',
                                            'maxlength' => 100
                                        ]); !!}
                                    </div>
                                    <small class="help-block">
                                        <i class="fa fa-info-circle"></i> 
                                        Este código é específico para cada localização e é usado nas transações TEF.
                                    </small>
                                </div>
                            </div>

                            <div class="clearfix"></div>

                            <div class="col-sm-12">
                                <div class="alert alert-info">
                                    <i class="fa fa-info-circle"></i> 
                                    <strong>Importante:</strong>
                                    <ul>
                                        <li>O código de certificação é fornecido pela GetCard após a certificação do estabelecimento</li>
                                        <li>Cada localização pode ter seu próprio código de certificação</li>
                                    </ul>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-sm-12">
                                    <button class="btn btn-primary pull-right" type="submit">
                                        <i class="fa fa-save"></i> @lang('messages.update')
                                    </button>
                                </div>
                            </div>
                            {!! Form::close() !!}
                        </div>
                    </div>
                </div>
                <!-- /.tab_3 -->

            </div>
            <!-- /.tab-content -->
        </div>
    </div>
    <!-- /.tab-content -->
</div>
<!-- nav-tabs-custom -->
</div>
</div>

<div class="modal fade invoice_modal" tabindex="-1" role="dialog" 
aria-labelledby="gridSystemModalLabel">
</div>
<div class="modal fade invoice_edit_modal" tabindex="-1" role="dialog" 
aria-labelledby="gridSystemModalLabel">
</div>

</section>
<!-- /.content -->

@endsection
