<div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
        @php
            $form_id = 'contact_add_form';
            if (isset($quick_add)) {
                $form_id = 'quick_add_contact';
            }

            if (isset($store_action)) {
                $url = $store_action;
                $type = 'lead';
                $customer_groups = [];
            } else {
                $url = action('ContactController@store');
                $type = '';
                $sources = [];
                $life_stages = [];
                $users = [];
            }
        @endphp
        {!! Form::open(['url' => $url, 'method' => 'post', 'id' => $form_id]) !!}

        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                    aria-hidden="true">&times;</span></button>
            <h4 class="modal-title">Novo {{ $tipo == 'customer' ? 'Cliente' : 'Contato' }}</h4>
        </div>

        <!-- DESABILITADO TIPO DE CLIENTE -->
        <div class="modal-body" style="background-color: #fbfbfb">
            <div class="row">

                <!--div class="col-md-2">
          <div class="form-group">
            {!! Form::label('tipo', 'Tipo' . ':') !!}
            <div class="input-group" style="width: 100%;">

              {!! Form::select('tipo', ['j' => 'Juridica', 'f' => 'Fisica'], '', ['class' => 'form-control']) !!}
            </div>
          </div>
        </div-->


                <div class="col-md-3">


                    <label for="product_custom_field2">CNPJ/CPF*:</label>

                    <input class="form-control featured-field warning-input" onchange="BuscaCNPJ(this.value);" required
                        placeholder="CPF/CNPJ" name="cpf_cnpj" type="text" id="cpf_cnpj">

                </div>



                <div class="col-md-3">
                    <div class="form-group">
                        <label id="label_ie" for="product_custom_field2">INS.ESTADUAL:</label>
                        <input class="form-control featured-field warning-input" placeholder="I.E/RG" name="ie_rg"
                            id="ie_rg">
                    </div>
                </div>



                <div class="col-md-6">
                    <div class="form-group">
                        {!! Form::label('name', 'Razão social/Nome' . ':*') !!}
                        <div class="input-group">
                            <span class="input-group-addon">
                                <i class="fa fa-user"></i>
                            </span>
                            {!! Form::text('name', null, [
                                'id' => 'name',
                                'class' => 'form-control featured-field warning-input',
                                'placeholder' => 'Razão social',
                                'required',
                            ]) !!}
                        </div>
                    </div>
                </div>



                <div class="col-md-4">
                    <div class="form-group">
                        <label id="label_fantasia">Fantasia:</label>
                        <div class="input-group" style="width: 100%;">


                            {!! Form::text('supplier_business_name', null, [
                                'id' => 'nome_fantasia',
                                'class' => 'form-control warning-input',
                                'required',
                                'placeholder' => __('business.business_name'),
                            ]) !!}
                        </div>
                    </div>
                </div>



                <div class="col-md-2">
                    <div class="form-group">

                        <div class="input-group" style="width: 100%;">


                            <label>UF:</label>
                            <select id="uf2" class="form-control select2 featured-field warning-input"
                                name="uf2">

                                <option value=""></option>
                                <option value="AC">AC</option>
                                <option value"AL">AL</option>
                                <option value"AP">AP</option>
                                <option value"AM">AM</option>
                                <option value"BA">BA</option>
                                <option value"CE">CE</option>
                                <option value"ES">ES</option>
                                <option value="GO">GO</option>
                                <option value"MA">MA</option>
                                <option value"MT">MT</option>
                                <option value"MS">MS</option>
                                <option value"MG">MG</option>
                                <option value"PA">PA</option>
                                <option value"PB">PB</option>
                                <option value="PR">PR</option>
                                <option value"PE">PE</option>
                                <option value"PI">PI</option>
                                <option value"RJ">RJ</option>
                                <option value"RN">RN</option>
                                <option value"RS">RS</option>
                                <option value"RO">RO</option>
                                <option value"RR">RR</option>
                                <option value"SC">SC</option>
                                <option value"SP">SP</option>
                                <option value"SE">SE</option>
                                <option value"TO">TO</option>
                                <option value"DF">DF</option>

                            </select>


                        </div>
                    </div>
                </div>



                <div class="col-md-6 contact_type_div">
                    <div class="form-group">

                        {!! Form::label('type', __('contact.contact_type') . ':*') !!}
                        <div class="input-group">
                            <span class="input-group-addon">
                                <i class="fa fa-user"></i>
                            </span>
                            {!! Form::select('type', $types, $tipo, [
                                'class' => 'form-control',
                                'id' => 'contact_type',
                                'placeholder' => __('messages.please_select'),
                                'required',
                            ]) !!}
                        </div>
                    </div>
                </div>

                <div class="col-md-4 customer_fields">
                    <div class="form-group">

                        {!! Form::label('Consumidor Final' . ':') !!}
                        {!! Form::select('consumidor_final', ['1' => 'Sim', '0' => 'Não'], '', [
                            'id' => 'consumidor_final',
                            'class' => 'form-control select2 featured-field warning-input',
                            'required',
                        ]) !!}
                    </div>
                </div>

                <div class="col-md-4 customer_fields">
                    <div class="form-group">

                        {!! Form::label('contribuinte', 'Contribuinte' . ':') !!}
                        {!! Form::select('contribuinte', ['1' => 'Sim', '0' => 'Não'], '', [
                            'id' => 'contribuinte',
                            'class' => 'form-control select2 featured-field',
                            'required',
                        ]) !!}
                    </div>
                </div>


                <div class="col-md-4 customer_fields">
                    <div class="form-group">
                        <label>Data do Cadastro:</label>

                        <input readonly="readonly" class="form-control" type="text" value="<?php echo date('d/m/Y'); ?>"
                            name="data_cadastro" />

                    </div>
                </div>

                <div class="col-md-8">
                    <div class="form-group">
                        {!! Form::label('vendedor_id', 'Vendedor (opcional)' . ':') !!}
                        {!! Form::select('vendedor_id', $usuario, '', [
                            'id' => 'vendedor',
                            'class' => 'form-control select2 warning-input featured-field',
                            'placeholder' => __('messages.please_select'),
                        ]) !!}
                    </div>
                </div>

                <div class="col-md-12">
                    <hr />
                </div>

                <div class="col-md-2 ">
                    <div class="form-group">
                        <label for="product_custom_field2">CEP*:</label>
                        <input class="form-control warning-input featured-field" onchange="buscacep(this.value, '')"
                            required placeholder="CEP" name="cep" data-mask="00000-000" type="text"
                            id="cep">
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group">
                        <label for="product_custom_field2">Rua*:</label>
                        <input class="form-control warning-input featured-field" required placeholder="Rua"
                            name="rua" type="text" id="rua">
                    </div>
                </div>
                <div class="col-md-2 ">
                    <div class="form-group">
                        <label for="product_custom_field2">Nº*:</label>
                        <input class="form-control warning-input featured-field" required placeholder="Nº"
                            name="numero" type="text" id="numero">
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group">
                        {!! Form::label('complement', 'Complemento:') !!}
                        <div class="input-group">
                            <span class="input-group-addon">
                                <i class="fa fa-map-marker"></i>
                            </span>
                            {!! Form::text('complement', null, ['class' => 'form-control', 'placeholder' => 'Complemento']) !!}
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group">
                        <label for="product_custom_field2">Bairro*:</label>
                        <input class="form-control warning-input featured-field" required placeholder="Bairro"
                            name="bairro" type="text" id="bairro">
                    </div>
                </div>

                <div class="col-md-5">
                    <div class="form-group">
                        {!! Form::label('city_id', 'Cidade:*') !!}
                        {!! Form::select('city_id', $cities, '', [
                            'id' => 'cidade',
                            'class' => 'form-control select2 warning-input featured-field',
                            'required',
                        ]) !!}
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        {!! Form::label('email', __('business.email') . ':') !!}
                        <div class="input-group">
                            <span class="input-group-addon">
                                <i class="fa fa-envelope"></i>
                            </span>
                            {!! Form::text('email', null, ['class' => 'form-control', 'placeholder' => __('business.email')]) !!}
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        {!! Form::label('landmark', __('business.landmark') . ':') !!}
                        <div class="input-group">
                            <span class="input-group-addon">
                                <i class="fa fa-map-marker"></i>
                            </span>
                            {!! Form::text('landmark', null, ['class' => 'form-control', 'placeholder' => __('business.landmark')]) !!}
                        </div>
                    </div>
                </div>

                <div class="clearfix"></div>

                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('landline', 'Fixo:') !!}
                        <div class="input-group">
                            <span class="input-group-addon">
                                <i class="fa fa-phone"></i>
                            </span>
                            {!! Form::text('landline', null, ['class' => 'form-control', 'placeholder' => 'Telefone Fixo']) !!}
                        </div>
                    </div>
                </div>


                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('alternate_number', 'Telefone alternativo' . ':') !!}
                        <div class="input-group">
                            <span class="input-group-addon">
                                <i class="fa fa-phone"></i>
                            </span>
                            {!! Form::text('alternate_number', null, [
                                'class' => 'form-control',
                                'placeholder' => __('contact.alternate_contact_number'),
                            ]) !!}
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('mobile', 'Celular' . ':') !!}
                        <div class="input-group">
                            <span class="input-group-addon">
                                <i class="fa fa-mobile"></i>
                            </span>
                            {!! Form::text('mobile', null, ['class' => 'form-control', 'placeholder' => 'Celular']) !!}
                        </div>
                    </div>
                </div>

                <div class="clearfix"></div>
                <div class="col-md-12">
                    <hr />
                </div>

                <!-- <div class="col-md-8" >
          <strong>{{ __('lang_v1.shipping_address') }}</strong><br>
          {!! Form::text('shipping_address', null, [
              'class' => 'form-control',
              'placeholder' => 'Endeço de entrega',
              'id' => 'shipping_address',
          ]) !!}
          <div id="map"></div>
        </div> -->


                <div class="col-md-12">
                    <h5>Endereço de entrega</h5>
                </div>

                <div class="col-md-2">
                    <div class="form-group">
                        <label for="product_custom_field2">CEP:</label>
                        <input class="form-control  featured-field" placeholder="CEP" name="cep_entrega"
                            data-mask="00000-000" type="text" id="cep_entrega">
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group">
                        <label for="product_custom_field2">Rua:</label>
                        <input class="form-control featured-field" placeholder="Rua" name="rua_entrega"
                            type="text" id="rua_entrega">
                    </div>
                </div>

                <div class="col-md-2">
                    <div class="form-group">
                        <label for="product_custom_field2">Nº:</label>
                        <input class="form-control featured-field" placeholder="Nº" name="numero_entrega"
                            type="text" id="numero_entrega">
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="form-group">
                        <label for="product_custom_field2">Bairro:</label>
                        <input class="form-control featured-field" placeholder="Bairro" name="bairro_entrega"
                            type="text" id="bairro_entrega">
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group">
                        {!! Form::label('city_id_entrega', 'Cidade:') !!}
                        {!! Form::select('city_id_entrega', $cities, '', [
                            'id' => 'cidade_entrega',
                            'class' => 'form-control select2 featured-field',
                        ]) !!}
                    </div>
                </div>

                <div class="clearfix"></div>
                <div class="col-md-12">
                    <hr />
                </div>

                <div class="col-md-4 customer_fields">
                    <div class="form-group">
                        {!! Form::label('credit_limit', __('lang_v1.credit_limit') . ':') !!}
                        <div class="input-group">
                            <span class="input-group-addon">
                                <i class="fas fa-money-bill-alt"></i>
                            </span>
                            {!! Form::text('credit_limit', null, ['class' => 'form-control input_number']) !!}
                        </div>
                        <p class="help-block">@lang('lang_v1.credit_limit_help')</p>
                    </div>
                </div>

                <div class="col-md-4 opening_balance">
                    <div class="form-group">
                        {!! Form::label('opening_balance', __('lang_v1.opening_balance') . ':') !!}
                        <div class="input-group">
                            <span class="input-group-addon">
                                <i class="fas fa-money-bill-alt"></i>
                            </span>
                            {!! Form::text('opening_balance', 0, ['class' => 'form-control input_number']) !!}
                        </div>
                    </div>
                </div>

                <div class="col-md-4 pay_term">
                    <div class="form-group">
                        <div class="multi-input">
                            {!! Form::label('pay_term_number', __('contact.pay_term') . ':') !!} @show_tooltip(__('tooltip.pay_term'))
                            <br />
                            {!! Form::number('pay_term_number', null, [
                                'class' => 'form-control width-40 pull-left',
                                'placeholder' => __('contact.pay_term'),
                            ]) !!}

                            {!! Form::select('pay_term_type', ['months' => __('lang_v1.months'), 'days' => __('lang_v1.days')], '', [
                                'class' => 'form-control width-60 pull-left',
                                'placeholder' => __('messages.please_select'),
                            ]) !!}
                        </div>
                    </div>
                </div>

                <div class="col-md-4 lead_additional_div">
                    <div class="form-group">
                        {!! Form::label('crm_life_stage', __('lang_v1.life_stage') . ':') !!}
                        <div class="input-group">
                            <span class="input-group-addon">
                                <i class="fas fa fa-life-ring"></i>
                            </span>
                            {!! Form::select('crm_life_stage', $life_stages, null, [
                                'class' => 'form-control',
                                'id' => 'crm_life_stage',
                                'placeholder' => __('messages.please_select'),
                            ]) !!}
                        </div>
                    </div>
                </div>

                <div class="clearfix"></div>

                <div class="col-md-4">
                    <div class="form-group">
                        {!! Form::label('tax_number', __('contact.tax_no') . ':') !!}
                        <div class="input-group">
                            <span class="input-group-addon">
                                <i class="fa fa-info"></i>
                            </span>
                            {!! Form::text('tax_number', null, ['class' => 'form-control', 'placeholder' => __('contact.tax_no')]) !!}
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group">
                        {!! Form::label('contact_id', __('lang_v1.contact_id') . ':') !!}
                        <div class="input-group">
                            <span class="input-group-addon">
                                <i class="fa fa-id-badge"></i>
                            </span>
                            {!! Form::text('contact_id', null, ['class' => 'form-control', 'placeholder' => __('lang_v1.contact_id')]) !!}
                        </div>
                    </div>
                </div>


                <!-- lead additional field -->
                <div class="col-md-4 lead_additional_div">
                    <div class="form-group">
                        {!! Form::label('crm_source', __('lang_v1.source') . ':') !!}
                        <div class="input-group">
                            <span class="input-group-addon">
                                <i class="fas fa fa-search"></i>
                            </span>
                            {!! Form::select('crm_source', $sources, null, [
                                'class' => 'form-control',
                                'id' => 'crm_source',
                                'placeholder' => __('messages.please_select'),
                            ]) !!}
                        </div>
                    </div>
                </div>


                <!-- AQUI O POSSIVEL CAMPO ASSIGNED TO QUE IMPOOSSIBILITA A EMPRESA SALVAR O CADASTRO DO CLIENTE -->

                <div class="col-md-6 lead_additional_div">
                    <div class="form-group">
                        {!! Form::label('user_id', __('lang_v1.assigned_to') . ':*') !!}
                        <div class="input-group">
                            <span class="input-group-addon">
                                <i class="fa fa-user"></i>
                            </span>
                            {!! Form::select('user_id[]', $users, null, [
                                'class' => 'form-control select2',
                                'id' => 'user_id',
                                'multiple',
                                'style' => 'width: 100%;',
                            ]) !!}
                        </div>
                    </div>
                </div>

                <!-- FIM DO POSSIVEL CAMPO ASSIGNED TO -->



                <div class="clearfix">
                    </ <div class="clearfix">
                </div>


                <div class="col-md-12">
                    <hr />
                </div>


                @php
                    $custom_labels = json_decode(session('business.custom_labels'), true);
                    $contact_custom_field1 = !empty($custom_labels['contact']['custom_field_1'])
                        ? $custom_labels['contact']['custom_field_1']
                        : __('lang_v1.contact_custom_field1');
                    $contact_custom_field2 = !empty($custom_labels['contact']['custom_field_2'])
                        ? $custom_labels['contact']['custom_field_2']
                        : __('lang_v1.contact_custom_field2');
                    $contact_custom_field3 = !empty($custom_labels['contact']['custom_field_3'])
                        ? $custom_labels['contact']['custom_field_3']
                        : __('lang_v1.contact_custom_field3');
                    $contact_custom_field4 = !empty($custom_labels['contact']['custom_field_4'])
                        ? $custom_labels['contact']['custom_field_4']
                        : __('lang_v1.contact_custom_field4');
                @endphp
                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('custom_field1', $contact_custom_field1 . ':') !!}
                        {!! Form::text('custom_field1', null, [
                            'class' => 'form-control',
                            'placeholder' => __('lang_v1.contact_custom_field1'),
                        ]) !!}
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('custom_field2', $contact_custom_field2 . ':') !!}
                        {!! Form::text('custom_field2', null, ['class' => 'form-control', 'placeholder' => $contact_custom_field2]) !!}
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('custom_field3', $contact_custom_field3 . ':') !!}
                        {!! Form::text('custom_field3', null, ['class' => 'form-control', 'placeholder' => $contact_custom_field3]) !!}
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('custom_field4', $contact_custom_field4 . ':') !!}
                        {!! Form::text('custom_field4', null, ['class' => 'form-control', 'placeholder' => $contact_custom_field4]) !!}
                    </div>
                </div>
                {!! Form::hidden('position', null, ['id' => 'position']) !!}

            </div>
        </div>



        <div class="col-md-3" style="display: none">
            <div class="form-group">
                {!! Form::label('city', __('business.city') . ':') !!}
                <div class="input-group">
                    <span class="input-group-addon">
                        <i class="fa fa-map-marker"></i>
                    </span>
                    {!! Form::text('city', null, ['class' => 'form-control', 'placeholder' => __('business.city')]) !!}
                </div>
            </div>
        </div>
        <div class="col-md-3" style="display: none">
            <div class="form-group">
                {!! Form::label('state', __('business.state') . ':') !!}
                <div class="input-group">
                    <span class="input-group-addon">
                        <i class="fa fa-map-marker"></i>
                    </span>
                    {!! Form::text('state', null, ['class' => 'form-control', 'placeholder' => __('business.state')]) !!}
                </div>
            </div>
        </div>

        <div class="col-md-3" style="display: none">
            <div class="form-group">
                {!! Form::label('country', __('business.country') . ':') !!}
                <div class="input-group">
                    <span class="input-group-addon">
                        <i class="fa fa-globe"></i>
                    </span>
                    {!! Form::text('country', null, ['class' => 'form-control', 'placeholder' => __('business.country')]) !!}
                </div>
            </div>
        </div>



        <div class="modal-footer">
            <div class="row">
                <div class="col-md-12 text-center">
                    <button style="min-width: 30vw" type="submit" class="btn btn-success"><i
                            class="fa fa-save"></i> Salvar Cadastro</button>
                    <button type="button" class="btn btn-danger" data-dismiss="modal"><i class="fa fa-times"></i>
                        Cancelar</button>
                </div>
            </div>
        </div>

        {!! Form::close() !!}



    </div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->


<script type="text/javascript">
    setTimeout(function() {


    }, 7000);


    //$('#cpf_cnpj').mask('00.000.000/0000-00')
    $('#cep').mask('00000-000')
    $('#tipo').change((val) => {
        var tipo = $('#tipo').val()

        if (tipo == 'j') {
            // $('#cpf_cnpj').mask('00.000.000/0000-00')

        } else {

            //$('#cpf_cnpj').mask('000.000.000-00')
            $('#nome_fantasia').removeAttr('required')

        }
    })

    function BuscaCNPJ(cpf_cnpj) {
       $.get('{{ route("contacts.valida-cnpj") }}', {
                doc: cpf_cnpj
            })
            .done((success) => {
                console.log(success)
            })
            .fail((err) => {
                console.log(err)
                if (err.status == 401) {
                    swal("Alerta", "Já existe um cadastro com este documento " + err.responseJSON.name, "warning")
                    $('#cpf_cnpj').val('')
                } else {
                    swal("Erro", "Algo errado ao consultar", "error")
                }
        })

        //var cnpj = $('#cpf_cnpj').val();
        //var uf = $('#uf2').val();  
        //alert(cpf_cnpj); 

        //AQUI VEJO SE É UM CPF, SE FOR COLOCA NAO CONTRIBUINTE E SOME O NOME FANTASIA
        var verifica_pj_pf = $("#cpf_cnpj").val();
        if (verifica_pj_pf.length < 14) {
            document.getElementById("nome_fantasia").style.display = "none";
            document.getElementById("label_fantasia").style.display = "none";
            $("#consumidor_final").val(1).change(); //SIM
            $("#contribuinte").val(0).change(); // NÃO
        } else {
            //SE FOR CNPJ VOLTA NOME FANTASIA E COLOCA CONTRIBUINTE IGUAL A SIM       
            document.getElementById("nome_fantasia").style.display = "block";
            document.getElementById("label_fantasia").style.display = "block";
            $("#consumidor_final").val(0).change(); //NAO
            $("#contribuinte").val(1).change(); //SIM
        }
        cpf_cnpj = cpf_cnpj.replace(/[^0-9]/g, '')
        $.ajax({
            type: 'GET',
            url: 'https://publica.cnpj.ws/cnpj/' + cpf_cnpj,

            dataType: 'json',
            success: function(data) {
                if (data != null) {
                    $('#ie_rg').val(data.estabelecimento.inscricoes_estaduais[0].inscricao_estadual)
                    $('#name').val(data.razao_social)
                    $('#nome_fantasia').val(data.estabelecimento.nome_fantasia)
                    $("#rua").val(data.estabelecimento.tipo_logradouro + " " + data.estabelecimento
                        .logradouro)
                    $("#rua_entrega").val(data.estabelecimento.tipo_logradouro + " " + data.estabelecimento
                        .logradouro)
                    $('#numero').val(data.estabelecimento.numero)
                    $('#numero_entrega').val(data.estabelecimento.numero)
                    $("#bairro").val(data.estabelecimento.bairro);
                    $("#bairro_entrega").val(data.estabelecimento.bairro);
                    //VERIFICA SE A ISCRICAO ESTÁ BAIXADA NA RECEITA
                    if (data.estabelecimento.inscricoes_estaduais[0].ativo == false) {
                        swal({
                            icon: 'warning',
                            title: 'ALERTA!',
                            text: 'Inscrição Estadual não habilitada ou irregular para NFe.',

                        })
                    }
                    //FIM VERIFICAÇÃO
                    //O REPLACE REMOVE OS ESPAÇOS QUE VEM LA DA API NO COMPLEMENTO
                    $('#complement').val(data.estabelecimento.complemento ? data.estabelecimento.complemento
                        .replace(/( )+/g, " ") : "");

                    $('#email').val(data.estabelecimento.email)



                    var cep = data.estabelecimento.cep.replace(/[^\d]+/g, '');

                    //aqui reomve o ponto do cep 
                    $('#cep').val(cep.substring(0, 5) + '-' + cep.substring(5, 9))
                    $('#cep_entrega').val(cep.substring(0, 5) + '-' + cep.substring(5, 9))



                    findCidade(data.estabelecimento.cidade.nome, (cidade) => {

                        if (cidade) {

                            var $option = $("<option selected></option>").val(data.estabelecimento
                                .cidade.id).text(data.estabelecimento.cidade.nome + " (" + data
                                .estabelecimento.estado.sigla + ")");

                            $('#cidade').append($option).trigger('change');
                            $('#cidade').val(data.estabelecimento.cidade.id).change();

                            $('#cidade_entrega').val(data.estabelecimento.cidade.id).change();
                            $('#cidade_entrega').append($option).trigger('change');



                            //PUXAR A UF APOS O CAMPO CNPJ
                            var $optionUF = $("<option selected></option>").val(data.estabelecimento
                                .cidade.id).text(data.estabelecimento.estado.sigla);

                            $('#uf2').append($optionUF).trigger('change');
                            $('#uf2').val(data.estabelecimento.estado.sigla).change();
                            //FIM PUXAR UF APOS O CNPJ
                        }
                    })

                } else {
                    swal('Algo deu errado', data.status, 'warning')
                }
            },
            error: function(data) {
                // console.log("err", data.status)
                swal('Algo deu errado', data.status, 'warning')

            }
        });
    }


    function findCidade(nomeCidade, call) {
        var path = window.location.protocol + '//' + window.location.host

        $.get('{{ route('nfe.findCidade') }}', {
                nome: nomeCidade
            })
            .done((success) => {
                call(success)
            })
            .fail((err) => {
                call(err)
            })
    }




    //Busca os dados do CEP na API
    // function buscacep(cep) {
    //     cep = cep.replace("-", "")
    //     $.get('https://ws.apicep.com/cep.json/', {
    //             code: cep
    //         })
    //         .done((response) => {
    //             $('#bairro').val(response.district);
    //             $('#bairro_entrega').val(response.district);
    //             $('#rua').val(response.address);
    //             $('#rua_entrega').val(response.address);
    //             // $('#uf2').val(response.state);
    //             //$('#uf2').select2.val(response.state);
    //             findCidade(response.city, (res) => {
    //                 console.log(res)
    //                 if (res) {
    //                     var $option = $("<option selected></option>").val(res.id).change()
    //                     var $option = $("<option selected></option>").val(res.id).text(res.nome + " (" + res
    //                         .uf + ")");
    //                     $('#cidade').append($option).trigger('change');
    //                     $('#cidade').val(res.id).change()
    //                     $('#cidade_entrega').val(res.id).change()
    //                 }
    //             });
    //         })
    // }
    // $('#cep').keyup((event) => {
    //     buscacep(event.target.value);
    // });


     function buscacep(cep) {
        cep = cep.replace("-", "").replace(/\D/g, '');

        $.get('/buscar-cep', { cep: cep })
            .done((response) => {
                $('#bairro').val(response.bairro);
                $('#bairro_entrega').val(response.bairro);
                $('#rua').val(response.logradouro);
                $('#rua_entrega').val(response.logradouro);

                findCidade(response.localidade, (res) => {
                    if (res) {
                        var $option = $("<option selected></option>").val(res.id).text(res.nome + " (" + res.uf + ")");
                        $('#cidade').append($option).trigger('change');
                        $('#cidade').val(res.id).change()
                        $('#cidade_entrega').val(res.id).change()
                    }
                });
            })
            .fail(() => {
                alert("Erro ao buscar o CEP.");
            });
    }

    $('#cep').on('blur', function () {
        buscacep(this.value);
    });

    // <!--VERIFICA SE EXISTE JA UM CADASTRO -->   

    // $('#cpf_cnpj').blur((event) => {
    //   let doc = $('#cpf_cnpj').val()
    //   $.get('{{ route('contacts.valida-cnpj') }}', {doc: doc} )
    //   .done((success) => {
    //     console.log(success)
    //   })
    //   .fail((err) => {
    //     console.log(err)
    //     if(err.status == 401){
    //       swal("Alerta", "Já existe um cadastro com este documento " + err.responseJSON.name, "warning")
    //       $('#cpf_cnpj').val('')
    //     }else{
    //       swal("Erro", "Algo errado ao consultar", "error")
    //     }
    //   })
    // });

    // <!--FIM VERIFICAÇÃO -->
</script>
