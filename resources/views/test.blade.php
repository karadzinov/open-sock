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

    <title>Thermostat!</title>
</head>
<body>

<style>
    html, body {
        height: 100%;
    }

    .container, .row.justify-content-center.align-items-center {
        height: 100%;
        min-height: 100%;
        text-align: center;
    }

    select {
        width: 400px;
        text-align-last: center;
    }

    #set-temp-data {
        font-size: 25px;
    }

    hr {
        margin-bottom: 50px;
    }
</style>
<div class="container">
    <div class="row justify-content-center align-items-center">
        <div class="col-12">
            <div class="center-block">
                <h6 id="set_temp">Set Temperature: {{ $set_temp }}</h6>
                <h6 id="room_temp">Room Temperature: {{ $room_temp }}</h6>
                <hr/>



                <p>Set Temperature</p>
                <input type="text" value="{{ $set_temp }}" class="dial">

                <div id="success"></div>

            </div>
        </div>
    </div>
</div>

<!-- Optional JavaScript -->
<script src="https://js.pusher.com/4.3/pusher.min.js"></script>


<script>

    // Enable pusher logging - don't include this in production
    Pusher.logToConsole = true;

    var pusher = new Pusher('d66ca498d980a3900469', {
        cluster: 'mt1',
        forceTLS: true
    });

    var newThermostat = pusher.subscribe('new-thermostat');


    var channel = pusher.subscribe('my-channel');
    channel.bind('set_temp', function(data) {
        $('.dial').val(data.set_temp).trigger('change');
    });

    channel.bind('property', function(data) {
        console.log(data);
    });
    channel.bind('thermostat-{{ $id }}', function(data) {
        $('#room_temp').html('Room Temperature: ' + data.thermostat.room_temp);// first set the value
        $('#set_temp').html('Set Temperature: ' + data.thermostat.set_temp);// first set the value
        $('.dial').val(data.thermostat.set_temp);




    });

</script>


<!-- jQuery first, then Popper.js, then Bootstrap JS -->
<script src="https://code.jquery.com/jquery-3.1.1.min.js"></script>
<script src="js/knob.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js"
        integrity="sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49"
        crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js"
        integrity="sha384-ChfqqxuZUCnJSK3+MXmPNIyE6ZbWh2IMqE241rYiqJxyMiZ6OW/JmZQ5stwEULTy"
        crossorigin="anonymous"></script>

<script src="https://unpkg.com/bootstrap-material-design@4.1.1/dist/js/bootstrap-material-design.js"
        integrity="sha384-CauSuKpEqAFajSpkdjv3z9t8E7RlpJ1UP0lKM/+NdtSarroVKu069AlsRPKkFBz9"
        crossorigin="anonymous"></script>





<script>
    $(document).ready(function () {

        var token = 'Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImp0aSI6IjNhN2U4MjlmOTU4OTY5MmY0YmNiMTEyZWU0YTcyYTE0MmExYzcwOThjMDVlMTgwOGUxOTJkODBlMDVhZTJjNDY1ZjQzODY3ZDcxYWE4ZTZlIn0.eyJhdWQiOiIyIiwianRpIjoiM2E3ZTgyOWY5NTg5NjkyZjRiY2IxMTJlZTRhNzJhMTQyYTFjNzA5OGMwNWUxODA4ZTE5MmQ4MGUwNWFlMmM0NjVmNDM4NjdkNzFhYThlNmUiLCJpYXQiOjE1NTk5MTk0NzEsIm5iZiI6MTU1OTkxOTQ3MSwiZXhwIjoxNTkxNTQxODcxLCJzdWIiOiI0Iiwic2NvcGVzIjpbInNhcG8iXX0.VnYCCblASNomdZUKxGF361de__SvbgW00bpwjmTIW7Zhi1tVVuQun1OMQ_4V6sKQA21lOlNEOIL3jtejd7WNWq4qRnz99Ocmim5LRlaz9-GzsFHNoYcsIahcvmPCL5Ob12QP0kLW05AT9dplVQ3aSPwB0mlvIYrEebF3MCiCaXR9No2eSiptJ6RcIeTU4C6w7-s7q8upvGsecJIMvWSR6IVg0OB-Z7E4zZKJJfPEPja3VL-voWKgzuzsdoRHkuTMPoNYJXoNqFCjStd55_Be2Z0YavNgtQg5oP6Htl7z67Nh33kG3eWjv75aOGelrulJK7shqOkyqoXVnMKpQmXKZ9ph_AV3yfzUU8m1ZxFR21Wdp9e4WAmHIN6NjR9vF-4L7UF1JnDiPN2-6U-etPXyYFYxN068iec3L6kipe0UbfUmKwOz_MFTlocn__USW3jGOcxw9NxQeZfTuscuujKOBef8hnFw2VphkPvN6XWG_5NZAwFU3UbEweDcaEFDuTl452jKVH82usZq0HJp2l9M3oi3M93LYWBcpDiCVBt79DyaWiUWa5men0VTUAIGd2ZWulQ6aD__JORivO19ubgzMnlUB9rspdJ4uXd5CBRcyETrQedFh2m14I2kVq_dOMFj-LnBAixaicMdmP7-LbzSM5Q4cQPuS-119brufKDc-C0';


        $('#test').on('change', function (e) {
            e.preventDefault();
            var elemId = e.target;
            console.log(elemId.id);
            console.log($(elemId).val());
        });


        $(function() {
            $(".dial").knob({
                'min': 0,
                'max':50,
                'fgColor':'#FF0000',
                'skin':'tron',
                'release' : function (v) {
                    let data = {"id": {{ $id }}, "set_temp": v};

                    $.ajax({
                        type: 'POST',
                        url: '/api/set-commands',
                        data: data,
                        dataType: 'json',
                        beforeSend: function (xhr) {
                            xhr.setRequestHeader('Authorization', token);
                        },
                        success: function (data) {
                            console.log('Success', data);
                        },
                        error: function (data) {
                            console.log(data);
                            $("#success").html('<p class="text-danger">' + data.responseJSON[0] + '</p>');
                        }
                    })
                }
            });
        });


        let set_temp = {{ $set_temp }}

        $(function () {
            var $select = $("#set-temp-data");
            for (i = 1; i <= 50; i++) {
                $select.append($('<option></option>').val(i).html(i));
            }
            $('#set-temp-data').val(set_temp).attr('selected', 'selected');
        });




        $('#set-temp-data').on('change', function (e) {
            e.preventDefault();
            let data = {"id": {{ $id }}, "set_temp": $('#set-temp-data').val()};
            $.ajax({
                type: 'POST',
                url: 'api/set-commands',
                data: data,
                dataType: 'json',
                beforeSend: function (xhr) {
                    xhr.setRequestHeader('Authorization', token);
                },
                success: function (data) {
                    console.log('Success', data);
                    $("#success").html('<p class="text-success">Success!</p>');
                },
                error: function (data) {
                    console.log(data);
                    $("#success").html('<p class="text-danger">' + data.responseJSON[0] + '</p>');
                }
            })
        });

    });

</script>


</body>
</html>
