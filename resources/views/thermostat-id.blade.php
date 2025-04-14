<!doctype html>
<html lang="en">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet"
          href="https://unpkg.com/bootstrap-material-design@4.1.1/dist/css/bootstrap-material-design.min.css"
          integrity="sha384-wXznGJNEXNG1NFsbm0ugrLFMQPWswR3lds2VeinahP8N0zJw9VWSopbjv2x7WCvX" crossorigin="anonymous">
    <!-- Material Design Bootstrap -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/mdbootstrap/4.7.3/css/mdb.min.css" rel="stylesheet">

    <link href="https://gitcdn.github.io/bootstrap-toggle/2.2.2/css/bootstrap-toggle.min.css" rel="stylesheet">

    <title>Thermostats</title>
</head>

<style>
    /* *, *:before, *:after {
    box-sizing: border-box;
    }

    body {
    font-family: sans-serif;
    padding: 60px 20px;
    }
    @media (min-width: 600px) {
    body {
        padding: 60px;
    }
    } */

    .range-slider {
        margin: 5px 0 0 0%;
    }

    .range-slider {
        width: 100%;
    }

    .range-slider__range {
        -webkit-appearance: none;
        width: calc(100% - (73px));
        height: 10px;
        border-radius: 5px;
        background: #d7dcdf;
        outline: none;
        padding: 0;
        margin: 0;
    }

    .range-slider__range::-webkit-slider-thumb {
        appearance: none;
        width: 20px;
        height: 20px;
        border-radius: 50%;
        background: #2c3e50;
        cursor: pointer;
        transition: background 0.15s ease-in-out;
    }

    .range-slider__range::-webkit-slider-thumb:hover {
        background: #1abc9c;
    }

    .range-slider__range:active::-webkit-slider-thumb {
        background: #1abc9c;
    }

    .range-slider__range::-moz-range-thumb {
        width: 20px;
        height: 20px;
        border: 0;
        border-radius: 50%;
        background: #2c3e50;
        cursor: pointer;
        transition: background 0.15s ease-in-out;
    }

    .range-slider__range::-moz-range-thumb:hover {
        background: #1abc9c;
    }

    .range-slider__range:active::-moz-range-thumb {
        background: #1abc9c;
    }

    .range-slider__range:focus::-webkit-slider-thumb {
        box-shadow: 0 0 0 3px #fff, 0 0 0 6px #1abc9c;
    }

    .range-slider__value {
        display: inline-block;
        position: relative;
        width: 60px;
        color: #fff;
        line-height: 20px;
        text-align: center;
        border-radius: 3px;
        background: #2c3e50;
        padding: 5px 10px;
        margin-left: 8px;
    }

    .range-slider__value:after {
        position: absolute;
        top: 8px;
        left: -7px;
        width: 0;
        height: 0;
        border-top: 7px solid transparent;
        border-right: 7px solid #2c3e50;
        border-bottom: 7px solid transparent;
        content: "";
    }

    ::-moz-range-track {
        background: #d7dcdf;
        border: 0;
    }

    input::-moz-focus-inner,
    input::-moz-focus-outer {
        border: 0;
    }
</style>

<body>

<div class="container">

    <div id="success"></div>
    <div id="error"></div>

    <form id="form_term" class="mt-5">
        <div class="form-group row">
            <input type="hidden" value="{{$thermostat->id}}" name="id">

            <div class="col-md-6 text-center p-3">
                <label for="room_name" class="font-weight-bold">Room name</label>
                <input type="text" class="form-control" value="{{$thermostat->room_name}}" disabled>
            </div>
            <div class="col-md-6 text-center p-3">
                <label for="set_temp" class="font-weight-bold">Set Temperature</label><br>
                <input class="slider" value="{{$thermostat->set_temp}}" data-min="0" data-max="50" id="set_temp"
                       name="set_temp">
            </div>
        </div>
        <div class="form-group row">
            <div class="col-md-6 text-center p-3">
                <label for="sched_temp" class="font-weight-bold">Scedule Temperature</label><br>
                <input class="slider" value="{{$thermostat->sched_temp}}" data-min="0" data-max="50" id="sched_temp"
                       name="sched_temp">
            </div>
            <div class="col-md-6 text-center p-3">
                <label for="max_temp" class="font-weight-bold">Max Temperature</label><br>
                <input class="slider" value="{{$thermostat->max_temp}}" data-min="22" data-max="50" id="max_temp"
                       name="max_temp">
            </div>
        </div>
        <div class="form-group row">
            <div class="col-md-6 text-center p-3">
                <label for="min_temp" class="font-weight-bold">Min Temperature</label><br>
                <input class="slider" value="{{$thermostat->min_temp}}" data-min="5" data-max="21" id="min_temp"
                       name="min_temp">
            </div>
            <div class="col-md-6 text-center p-3">
                <label for="offset_sign" class="font-weight-bold">offset_sign</label><br>

                {{-- <input class="switch" type="checkbox" id="offset_sign" name="offset_sign"> --}}

                <input id="offset_sign" name="offset_sign" type="checkbox"
                       @if($thermostat->offset_sign == 0) @elseif($thermostat->offset_sign == 1) checked @endif
                       data-toggle="toggle" data-on="True" data-off="False" data-onstyle="success"
                       data-offstyle="danger">

                {{-- <select id="offset_sign" name="offset_sign" class="form-control">
                    <option value="0" @if($thermostat->offset_sign == 0) selected @endif>False</option>
                    <option value="1" @if($thermostat->offset_sign == 1) selected @endif>True</option>
                </select> --}}
            </div>


        </div>
        <div class="form-group row">
            <div class="col-md-6 text-center p-3">
                <label for="offset_temp" class="font-weight-bold">Offset Temperature</label><br>
                <input class="slider" value="{{$thermostat->offset_temp}}" data-min="0" data-max="50" id="offset_temp"
                       name="offset_temp">
            </div>
            <div class="col-md-6 text-center p-3">
                <label for="temp_limiter" class="font-weight-bold">Temp. Limiter</label><br>
                <input class="slider" value="{{$thermostat->temp_limiter}}" data-min="0" data-max="50" id="temp_limiter"
                       name="temp_limiter">
            </div>
        </div>
        <div class="form-group row">
            <div class="col-md-6 text-center p-3">
                <label for="mode" class="font-weight-bold">Mode</label><br>
                <select id="mode" name="mode" class="form-control toggleSelect">
                    <option value="0" @if($thermostat->mode == 0) selected @endif>0</option>
                    <option value="1" @if($thermostat->mode == 1) selected @endif>1</option>
                    <option value="2" @if($thermostat->mode == 2) selected @endif>2</option>
                    <option value="3" @if($thermostat->mode == 3) selected @endif>3</option>
                    <option value="4" @if($thermostat->mode == 4) selected @endif>4</option>
                </select>
            </div>
            <div class="col-md-6 text-center p-3">
                <label for="sensors_mode" class="font-weight-bold">Sensors mode</label><br>
                <select id="sensors_mode" name="sensors_mode" class="form-control toggleSelect">
                    <option value="0" @if($thermostat->sensors_mode == 0) selected @endif>0</option>
                    <option value="1" @if($thermostat->sensors_mode == 1) selected @endif>1</option>
                    <option value="2" @if($thermostat->sensors_mode == 2) selected @endif>2</option>
                    <option value="3" @if($thermostat->sensors_mode == 3) selected @endif>3</option>
                </select>
            </div>
        </div>
        <div class="form-group row">
            <div class="col-md-6 text-center p-3">
                <label for="temp_measurement" class="font-weight-bold">Temp. measurement</label><br>


                <input id="temp_measurement" name="temp_measurement" type="checkbox" class="toggleElem"
                       @if($thermostat->temp_measurement == 0) @elseif($thermostat->temp_measurement == 1) checked @endif
                       data-toggle="toggle" data-on="True" data-off="False" data-onstyle="success"
                       data-offstyle="danger">

            </div>
            <div class="col-md-6 text-center p-3">
                <label for="relay_opera" class="font-weight-bold">Relay operation</label><br>

                <input id="relay_opera" name="relay_opera" type="checkbox" class="toggleElem"
                       @if($thermostat->relay_opera == 0) @elseif($thermostat->relay_opera == 1) checked @endif
                       data-toggle="toggle" data-on="True" data-off="False" data-onstyle="success"
                       data-offstyle="danger">
            </div>
        </div>
        <div class="form-group row">
            <div class="col-md-6 text-center p-3">
                <label for="relay_limit" class="font-weight-bold">Relay Limit</label><br>
                <input class="slider" value="{{$thermostat->relay_limit}}" data-min="0" data-max="10" id="relay_limit"
                       name="relay_limit">
            </div>
            <div class="col-md-6 text-center p-3">
                <label for="sensitivity" class="font-weight-bold">Sensitivity</label><br>
                <input class="slider" value="{{$thermostat->sensitivity}}" data-min="0" data-max="3" data-step="0.01"
                       id="sensitivity" name="sensitivity">
            </div>
        </div>
        <div class="form-group row">
            <div class="col-md-6 text-center p-3">
                <label for="differential" class="font-weight-bold">Differential</label><br>
                <input class="slider" value="{{$thermostat->differential}}" data-min="0" data-max="3" id="differential"
                       name="differential">
            </div>
            <div class="col-md-6 text-center p-3">
                <label for="cool_heat_mode" class="font-weight-bold">cool_heat_mode</label><br>
                <input class="slider" value="{{$thermostat->cool_heat_mode}}" data-min="0" data-max="2"
                       id="cool_heat_mode"
                       name="cool_heat_mode">
            </div>
        </div>
        <div class="form-group row">
            <div class="col-md-6 text-center p-3">
                <label for="boiler_duration" class="font-weight-bold">Boiler Duration</label><br>
                <input class="slider" value="{{$thermostat->boiler_duration}}" data-min="30" data-max="210"
                       id="boiler_duration"
                       name="boiler_duration">
            </div>
            <div class="col-md-6 text-center p-3">
                <label for="home_router_mac_address" class="font-weight-bold">Home Router MAC Address</label><br>
                <input type="text" class="form-control" value="{{$thermostat->home_router_mac_address}}"
                       id="home_router_mac_address"
                       name="home_router_mac_address" placeholder="home_router_mac_address">
            </div>
        </div>
        <div class="form-group row">
            <div class="col-md-6 text-center p-3">
                <label for="bathroom_on_low_heat" class="font-weight-bold">bathroom_on_low_heat</label><br>
                <input class="slider" value="{{$thermostat->bathroom_on_low_heat}}" data-min="1" data-max="30"
                       id="bathroom_on_low_heat"
                       name="bathroom_on_low_heat">
            </div>
            <div class="col-md-6 text-center p-3">
                <label for="bathroom_delay_stby_low_heat">bathroom_delay_stby_low_heat</label><br>
                <input class="slider" value="{{$thermostat->bathroom_delay_stby_low_heat}}" data-min="1" data-max="30"
                       id="bathroom_delay_stby_low_heat" name="bathroom_delay_stby_low_heat">
            </div>
        </div>
        <div class="form-group row">
            <div class="col-md-6 text-center p-3">
                <label for="bathroom_on_med_heat" class="font-weight-bold">bathroom_on_med_heat</label><br>
                <input class="slider" value="{{$thermostat->bathroom_on_med_heat}}" data-min="1" data-max="30"
                       id="bathroom_on_med_heat"
                       name="bathroom_on_med_heat">
            </div>
            <div class="col-md-6 text-center p-3">
                <label for="bathroom_delay_stby_med_heat"
                       class="font-weight-bold">bathroom_delay_stby_med_heat</label><br>
                <input class="slider" value="{{$thermostat->bathroom_delay_stby_med_heat}}" data-min="1" data-max="30"
                       id="bathroom_delay_stby_med_heat" name="bathroom_delay_stby_med_heat">
            </div>
        </div>
        <div class="form-group row">
            <div class="col-md-6 text-center p-3">
                <label for="bathroom_on_high_heat" class="font-weight-bold">bathroom_on_high_heat</label><br>
                <input class="slider" value="{{$thermostat->bathroom_on_high_heat}}" data-min="1" data-max="30"
                       id="bathroom_on_high_heat"
                       name="bathroom_on_high_heat">
            </div>
            <div class="col-md-6 text-center p-3">
                <label for="bathroom_delay_stby_high_heat"
                       class="font-weight-bold">bathroom_delay_stby_high_heat</label><br>
                <input class="slider" value="{{$thermostat->bathroom_delay_stby_high_heat}}" data-min="1" data-max="30"
                       id="bathroom_delay_stby_high_heat" name="bathroom_delay_stby_high_heat">
            </div>
        </div>
        <div class="form-group row">
            <div class="col-md-6 text-center p-3">
                <label for="cool_room_check" class="font-weight-bold">cool_room_check</label><br>
                <input class="slider" value="{{$thermostat->cool_room_check}}" data-min="0" data-max="120"
                       id="cool_room_check"
                       name="cool_room_check">
            </div>
            <div class="col-md-6 text-center p-3">
                <label for="boost" class="font-weight-bold">Boost</label><br>
                <input class="slider" value="{{$thermostat->boost}}" data-min="0" data-max="240" id="boost"
                       name="boost">
            </div>
        </div>
        <div class="form-group row">
            <div class="col-md-6 text-center p-3">
                <label for="ligth_intensity" class="font-weight-bold">Light Intensity</label><br>
                <input class="slider" value="{{$thermostat->ligth_intensity}}" data-min="0" data-max="2"
                       id="ligth_intensity"
                       name="ligth_intensity">
            </div>
            <div class="col-md-6 text-center p-3">
                <label for="enab_vibration" class="font-weight-bold">enab_vibration</label><br>


                <input id="enab_vibration" name="enab_vibration" type="checkbox" class="toggleElem"
                       @if($thermostat->enab_vibration == 0) @elseif($thermostat->enab_vibration == 1) checked @endif
                       data-toggle="toggle" data-on="True" data-off="False" data-onstyle="success"
                       data-offstyle="danger">
            </div>
        </div>
        <div class="form-group row">
            <div class="col-md-6 text-center p-3">
                <label for="enab_matrix" class="font-weight-bold">enab_matrix</label><br>

                <input id="enab_matrix" name="enab_matrix" type="checkbox" class="toggleElem"
                       @if($thermostat->enab_matrix == 0) @elseif($thermostat->enab_matrix == 1) checked @endif
                       data-toggle="toggle" data-on="True" data-off="False" data-onstyle="success"
                       data-offstyle="danger">

            </div>
            <div class="col-md-6 text-center p-3">
                <label for="enab_hart_beep_led" class="font-weight-bold">enab_hart_beep_led</label><br>

                <input id="enab_hart_beep_led" name="enab_hart_beep_led" type="checkbox" class="toggleElem"
                       @if($thermostat->enab_hart_beep_led == 0) @elseif($thermostat->enab_hart_beep_led == 1) checked @endif
                       data-toggle="toggle" data-on="True" data-off="False" data-onstyle="success"
                       data-offstyle="danger">
            </div>
        </div>
        <div class="form-group row">
            <div class="col-md-6 text-center p-3">
                <label for="enab_lock" class="font-weight-bold">enab_lock</label><br>


                <input id="enab_lock" name="enab_lock" type="checkbox" class="toggleElem"
                       @if($thermostat->enab_lock == 0) @elseif($thermostat->enab_lock == 1) checked @endif
                       data-toggle="toggle" data-on="True" data-off="False" data-onstyle="success"
                       data-offstyle="danger">

            </div>
            <div class="col-md-6 text-center p-3">
                <label for="last_operation" class="font-weight-bold">Last Operation</label><br>
                <input class="slider" value="{{$thermostat->last_operation}}" data-min="0" data-max="15"
                       id="last_operation"
                       name="last_operation">
            </div>
        </div>
        <div class="form-group row">
            <div class="col-md-6 text-center p-3">
                <label for="last_operation_value" class="font-weight-bold">Last operation Value</label><br>
                <input class="slider" value="{{$thermostat->last_operation_value}}" data-min="0" data-max="50"
                       id="last_operation_value"
                       name="last_operation_value">
            </div>
            <div class="col-md-6 text-center p-3">
                <label for="restore_default" class="font-weight-bold">restore_default</label><br>

                <input id="restore_default" name="restore_default" type="checkbox" class="toggleElem"
                       @if($thermostat->restore_default == 0) @elseif($thermostat->restore_default == 1) checked @endif
                       data-toggle="toggle" data-on="True" data-off="False" data-onstyle="success"
                       data-offstyle="danger">
            </div>
        </div>
        <div class="form-group row">
            <div class="col-md-6 text-center p-3">
                <label for="encription_code" class="font-weight-bold">Encription Code</label><br>
                <input class="slider" value="{{$thermostat->encription_code}}" data-min="0" data-max="990"
                       id="encription_code"
                       name="encription_code">
            </div>
            <div class="col-md-6 text-center p-3">
            </div>
        </div>

    </form>
</div>

<!-- Optional JavaScript -->
<script src="https://js.pusher.com/4.3/pusher.min.js"></script>


<script>
    // Enable pusher logging - don't include this in production
    Pusher.logToConsole = true;

    var pusher = new Pusher('{{ env('PUSHER_APP_KEY') }}', {
        cluster: '{{ env('PUSHER_CLUSTER') }}',
        forceTLS: true
    });
    var channel = pusher.subscribe('channel-{{ $property }}');
    /*
            channel.bind('set_temp', function(data) {
                $('.dial').val(data.set_temp).trigger('change');
            });

            */

    channel.bind('thermostat-{{ $thermostat->id }}', function (data) {
        console.log(data);

        $('#set_temp').val(data.thermostat.set_temp); // first set the value
    });


    let property = pusher.subscribe('property-{{ $property }}');

    property.bind('property', function (data) {
        console.log(data);
    });

</script>

<!-- jQuery first, then Popper.js, then Bootstrap JS -->
<script src="https://code.jquery.com/jquery-3.1.1.min.js"></script>
<script src="/js/knob.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js"
        integrity="sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49"
        crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js"
        integrity="sha384-ChfqqxuZUCnJSK3+MXmPNIyE6ZbWh2IMqE241rYiqJxyMiZ6OW/JmZQ5stwEULTy"
        crossorigin="anonymous"></script>

<script src="https://unpkg.com/bootstrap-material-design@4.1.1/dist/js/bootstrap-material-design.js"
        integrity="sha384-CauSuKpEqAFajSpkdjv3z9t8E7RlpJ1UP0lKM/+NdtSarroVKu069AlsRPKkFBz9"
        crossorigin="anonymous"></script>

<script src="https://gitcdn.github.io/bootstrap-toggle/2.2.2/js/bootstrap-toggle.min.js"></script>

<script>
    $(document).ready(function () {


        let token = 'Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImp0aSI6IjgwYmM3MWNhZTQ1ZWJlNzczMzZiZGZlNDJmMTFjYjUzNGJlMDE5MDczMDc4NDJmNmMzOGFiOTU0MTk2NGFhM2QwM2RiNGFkYjc1MmIzZjVkIn0.eyJhdWQiOiIyIiwianRpIjoiODBiYzcxY2FlNDVlYmU3NzMzNmJkZmU0MmYxMWNiNTM0YmUwMTkwNzMwNzg0MmY2YzM4YWI5NTQxOTY0YWEzZDAzZGI0YWRiNzUyYjNmNWQiLCJpYXQiOjE1NzY1ODA2ODIsIm5iZiI6MTU3NjU4MDY4MiwiZXhwIjoxNjA4MTE2NjgyLCJzdWIiOiIyOCIsInNjb3BlcyI6WyJzYXBvIl19.V2h5YVm0sEGWcYdVGzChgAwS69bNvZG6my03Ng9gUYg1GI3mrwnSzb3u0dV_aYch1UcZVZHvDI4LHxQqeM-bKI4JU9aRVXDNMiPEW81zxeCbXC__P8HpZ9JBNAOFM_mpLJagxS_L3nhEuPZo1ePIu3XlwNry9vNiH_PRvKEb5Bt2ZXH1Am7ArdcYREQ9iFULIGGH-kzYrULHGGCo2PITbswbpStEpVRKeuH9qXo9uA5XhwKBbnfJl0CoZfKbRFDJCJXpqdJiclLzXJNyLZ7i-Co0gmwD81Lie8OnYzURfyWV648syx7l8Tc9jWG7TxPEkD99z0Mes8PLaVVJsu62NNrC7GzcxM4HDCeiN-dZZ6axIagPNnRVKSC9Rg5yp6nx_Qq0XZ_HU6u3VTtMH9kwn3y9uvT2_Uze8jeJeDIDofkCSOvVAlntWUIcHyL7NGT7PpvsLIutUJS6FmGbk2sHjMS4qPcgXiwInh_nkHmPEuwrdLtfUF-WGcKp1IEf1pq84fc72P-1-ke52NVE327L0KsrFb3Tahzl4-7s9tVQiybAN9m7QcbsfdjDBdgO772UfENWkvwhEyVVQuqMlr7i6wOn7NNnyS3iN0W5DLltsOt5vySuD5XKWrOPREr_bRE_PnHCJ9PCqZ4ZYZ3TUz31WNNT84zXgVDtrmcK1mSbrQA';
        $(function () {

            $(".slider").knob({
                'fgColor': '#FF0000',
                'skin': 'tron',
                'release': function (v) {
                    let data = {
                        "id": 9,
                        "set_temp": v
                    };
                    let elem = $(this.$);
                    let attrName = $(elem).attr('name');

                    if (attrName === 'offset_temp') {
                        v > 0 ? $('#offset_sign').bootstrapToggle('on') : $('#offset_sign').bootstrapToggle('off').prop('disabled');

                        var offset_sing = $('#offset_sign').prop('checked') ? 1 : 0;
                        json = '{"id":' + '{{ $thermostat->id }}' + ', "' + attrName + '":' + v + ',"offset_sign":' + offset_sing + '}';
                        console.log(json);
                    }
                    else {
                        var json = '{"id":' + '{{ $thermostat->id }}' + ', "' + attrName + '":' + v + '}';
                        console.log(json);
                    }

                    var sendData = JSON.parse(json);

                    $.ajax({
                        type: 'POST',
                        url: '/api/set-commands',
                        data: sendData,
                        dataType: 'json',
                        beforeSend: function (xhr) {
                            xhr.setRequestHeader('Authorization',
                               token
                            );
                        },
                        success: function (data) {
                            console.log('Success', data);
                        },
                        error: function (data) {
                            console.log(data);
                            $("#success").html('<p class="text-danger">' +
                                data.responseJSON[0] + '</p>');
                        }
                    })
                }
            });
        });


        $('.toggleSelect').change(function (e) {
            e.preventDefault();

            var elem = e.target;
            var elemName = elem.id;
            var elemValue = $(elem).val();
            console.log('Elementot se vika: ', elemName);
            console.log('Elementot e: ', elemValue);

            var json = '{"id":' + '{{ $thermostat->id }}' + ', "' + elemName + '":' + elemValue + '}';
            var sendData = JSON.parse(json);

            $.ajax({
                type: 'POST',
                url: '/api/set-commands',
                data: sendData,
                dataType: 'json',
                beforeSend: function (xhr) {
                    xhr.setRequestHeader('Authorization',
                      token
                    );
                },
                success: function (data) {
                    console.log('Success', data);
                },
                error: function (data) {
                    console.log(data);
                    $("#success").html('<p class="text-danger">' +
                        data.responseJSON[0] + '</p>');
                }
            })

        });

        $('.toggleElem').change(function (e) {
            e.preventDefault();

            var elem = e.target;
            var elemName = elem.id;
            var elemValue = $(elem).prop('checked') ? 1 : 0;
            console.log('Elementot se vika: ', elemName);
            console.log('Elementot e: ', elemValue);

            var json = '{"id":' + '{{ $thermostat->id }}' + ', "' + elemName + '":' + elemValue + '}';
            var sendData = JSON.parse(json);

            $.ajax({
                type: 'POST',
                url: '/api/set-commands',
                data: sendData,
                dataType: 'json',
                beforeSend: function (xhr) {
                    xhr.setRequestHeader('Authorization',
                      token
                    );
                },
                success: function (data) {
                    console.log('Success', data);
                },
                error: function (data) {
                    console.log(data);
                    $("#success").html('<p class="text-danger">' +
                        data.responseJSON[0] + '</p>');
                }
            })

        });

    });
</script>

</body>

</html>