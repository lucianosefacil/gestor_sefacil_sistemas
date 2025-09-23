<section class="no-print">
    <nav class="navbar navbar-default bg-white m-4">
        <div class="container-fluid">
            <!-- Brand and toggle get grouped for better mobile display -->
            <div class="navbar-header">
                <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
            </div>

            <!-- Collect the nav links, forms, and other content for toggling -->
            <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
                <ul class="nav navbar-nav">

                    <li @if(request()->segment(2) == 'job-sheet' && empty(request()->segment(3))) class="active" @endif>
                        <a href="{{action('OrdemServicoController@index')}}">
                            @lang('Lista de OS')
                        </a>
                    </li>

                    <li @if(request()->segment(2) == 'servicos' && empty(request()->segment(3))) class="active" @endif>
                        <a href="{{action('ServicosController@index')}}">@lang('Serviços')</a></li>

                    <li @if(request()->segment(2) == 'veiculos' && empty(request()->segment(3))) class="active" @endif>
                        <a href="{{action('VeiculoOsController@index')}}">@lang('Veículos')</a></li>

                    <li @if(request()->segment(2) == 'profissional' && empty(request()->segment(3))) class="active" @endif>
                        <a href="{{action('FuncionarioController@index')}}">@lang('Profissional')</a></li>

                    @if (auth()->user()->can('edit_repair_settings'))
                    <li @if(request()->segment(1) == 'repair' && request()->segment(2) == 'repair-settings') class="active" @endif>
                        <a href="{{action('\Modules\Repair\Http\Controllers\RepairSettingsController@index')}}">@lang('messages.settings')</a></li>
                    @endif
                </ul>

            </div><!-- /.navbar-collapse -->
        </div><!-- /.container-fluid -->
    </nav>
</section>
