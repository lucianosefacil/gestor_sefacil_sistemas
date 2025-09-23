@extends('layouts.app')

@section('title', 'Alterar Data Registro')

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>Alterar Data Registro</h1>
</section>

<!-- Main content -->
<section class="content">

    <div class="row">
        <div class="col-md-12">
            @component('components.widget')

            {!! Form::open(['url' => action('CteController@salvarAlteracaoData'), 'method' => 'post', 'id' => 'cte_add_form' ]) !!}
            <input type="hidden" id="token" value="{{csrf_token()}}" name="">

            <input type="hidden" id="id" value="{{$cte->id}}" name="cte_id">
            <div class="col-md-12">

                <h4>Data Registro Atual: <strong>{{$cte->data_registro}}</strong></h4>
            </div>

            <div class="clearfix"></div>

            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('nova_data_regitro', 'Nova Data Registro' . ':*') !!}
                    <div class="input-group">
                        <span class="input-group-addon">
                            <i class="fa fa-calendar"></i>
                        </span>
                        <input type="date" id="currentDate" name="nova_data">
                        <input type="time" id="currentTime" name="nova_hora">
                        {{-- {!! Form::text('nova_data_regitro', '', ['class' => 'form-control', 'readonly', 'required', 'id' => 'vencimento']); !!} --}}
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <button id="" type="submit" class="btn btn-info pull-right">@lang( 'messages.save' ) Data</button>
                </div>
            </div>

            @endcomponent
        </div>

    </div>


    {!! Form::close() !!}

    <br>
    <div class="row" id="action" style="display: none">
        <div class="col-md-12">
            @component('components.widget')
            <div class="info-box-content">
                <div class="col-md-4 col-md-offset-4">

                    <span class="info-box-number total_purchase">
                        <strong id="acao"></strong>
                        <i class="fas fa-spinner fa-pulse fa-spin fa-fw margin-bottom"></i></span>
                </div>
            </div>
            @endcomponent

        </div>
    </div>

    @stop

    @section('javascript')
    <script type="text/javascript">
        const getTwoDigits = (value) => value < 10 ? `0${value}` : value;

        const formatDate = (date) => {
            const day = getTwoDigits(date.getDate());
            const month = getTwoDigits(date.getMonth() + 1); // add 1 since getMonth returns 0-11 for the months
            const year = date.getFullYear();

            return `${year}-${month}-${day}`;
        }

        const formatTime = (date) => {
            const hours = getTwoDigits(date.getHours());
            const mins = getTwoDigits(date.getMinutes());

            return `${hours}:${mins}`;
        }

        const date = new Date();
        document.getElementById('currentDate').value = formatDate(date);
        document.getElementById('currentTime').value = formatTime(date);

        // swal("Good job!", "You clicked the button!", "success");
        var path = window.location.protocol + '//' + window.location.host

    </script>
    @endsection
