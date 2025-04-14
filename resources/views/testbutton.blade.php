<!doctype html>
<html>
<head>
    <title>Test Button</title>
    <link rel="stylesheet"
          href="https://unpkg.com/bootstrap-material-design@4.1.1/dist/css/bootstrap-material-design.min.css"
          integrity="sha384-wXznGJNEXNG1NFsbm0ugrLFMQPWswR3lds2VeinahP8N0zJw9VWSopbjv2x7WCvX" crossorigin="anonymous">

</head>
<body>
<form>
    <input type="text" id="set_temp" value="1">
<button type="submit" id="klikni">SET COMMAND</button>
</form>
<script src="https://code.jquery.com/jquery-3.1.1.min.js"></script>

<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js"
        integrity="sha384-ChfqqxuZUCnJSK3+MXmPNIyE6ZbWh2IMqE241rYiqJxyMiZ6OW/JmZQ5stwEULTy"
        crossorigin="anonymous"></script>


<script>

    var delay = ( function() {
        var timer = 0;
        return function(callback, ms) {
            clearTimeout (timer);
            timer = setTimeout(callback, ms);
        };
    })();

    var lastt, tdiff;
    var i = 0;
    $( "form" ).click(function( event ) {

        event.preventDefault();

        if (lastt) {
        tdiff = event.timeStamp - lastt;
        console.log( "Time since last event: " + tdiff);
    }
           lastt = event.timeStamp;

        var set_temp = $('#set_temp').val();

        $("#set_temp").val(parseInt(set_temp) + 1);
        if(tdiff > 500 && i === 0)
        {
            sendCommand();
        }
        else {
            delay(function(){
                sendCommand();
            }, 500 ); // end


        }
        i++;
    });


    function sendCommand()
    {
        var data =  {set_temp: parseInt($('#set_temp').val()), id: 31};
        console.log(data);
    }





</script>
</body>
</html>