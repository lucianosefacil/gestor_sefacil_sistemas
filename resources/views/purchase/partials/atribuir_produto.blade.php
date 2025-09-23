<div class="modal fade" id="atribuir_produto" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">@lang('Atribuir Produto')</h4>
            </div>

            @if (count($business_locations) == 1)
                @php
                    $default_location = current(array_keys($business_locations->toArray()));
                    $search_disable = false;
                @endphp
            @else
                @php
                    $default_location = null;
                    $search_disable = true;
                @endphp
            @endif

            <div class="modal-body">
                <label for="search_product" class="control-label">Buscar Produto:</label>
                    <div class="input-group">
                        <span class="input-group-addon">
                            <i class="fa fa-search"></i>
                        </span>
                        {!! Form::text('search_product', null, [
                            'class' => 'form-control mousetrap',
                            'id' => 'search_product',
                            'placeholder' => __('lang_v1.search_product_placeholder'),
                            'disabled' => $search_disable,
                        ]) !!}
                    </div>
            </div>

            <div class="form-group row m-2">
                <div class="col-sm-4">
                    <label for="" class="control-label">Valor de Custo:</label>
                    <input type="text" class="form-control" id="default_purchase_price" name="default_purchase_price" >
                </div>
        
                <div class="col-sm-4">
                    <label for="" class="control-label">Margem de Lucro (%):</label>
                    <input type="text" class="form-control" id="profit_percent" name="profit_percent" >
                </div>
        
                <div class="col-sm-4">
                    <label for="" class="control-label">Valor de Venda:</label>
                    <input type="text" class="form-control" id="default_price" name="default_price" >
                </div>
            </div>



            <div class="modal-footer">
                <button type="button" id="btn-ok" class="btn btn-primary">@lang('messages.save')</button>
                <button type="button" class="btn btn-default" data-dismiss="modal">@lang('messages.close')</button>
            </div>
        </div>
    </div>
</div>

{{-- <div class="modal fade" id="atribuir_produto" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalLabel">Atribuir Produto</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="search_product" class="control-label">Buscar Produto:</label>
                    <div class="input-group">
                        <span class="input-group-addon">
                            <i class="fa fa-search"></i>
                        </span>
                        <input type="text" id="search_product" class="form-control" 
                               placeholder="Digite o nome ou código do produto" 
                               autocomplete="off">
                    </div>
                </div>
                <div id="variation_values">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" id="btn-ok" class="btn btn-primary">Salvar</button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div> --}}
