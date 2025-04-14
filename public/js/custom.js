function thisUser(e) {
    var user_id = $(e).attr('data-id');

    Cookies.set('user_id', user_id);
    Cookies.set('user_name', $(e).attr('data-name'));

    location.reload();

}


function deleteTechnician(e) {

    var id = $(e).attr('data-id');
    var data = Cookies.get('token');
    data = JSON.parse(data);
    var token = data.token.access_token;

    $.ajax({
        type: "post",
        url: "/api/dashboard/technicians/" + id,
        headers: {
            'Authorization': `Bearer ${token}`,
        },
        beforeSend: function () {
            $("#loading-page-technicians").show();
        },
        success: function (data) {
            $("#loading-page-technicians").hide();
            $("#addTechnicians").html('');

            $.each(data, function (key, value) {

                var tbRow = '<tbody><tr>';
                tbRow += '<td>' + value.name + '</td>';
                tbRow += '<td>' + value.email + '</td>';
                tbRow += '<td>' + value.country + '</td>';
                tbRow += '<td>' + value.phone + '</td>';
                tbRow += '<td><button data-id="' + value.id + '" class="btn btn-danger btn-sm deleteTechnician" onclick="deleteTechnician(this)">Delete</button></td>';
                tbRow += '</tr></tbody>';
                $("#addTechnicians").append(tbRow);
            });

        },
        error: function () {

        }
    });

}



$(document).ready(function () {


    var TotalProperties = 0;
    var TotalThermostats = 0;
    var Online = 0;
    var Offline = 0;
    var TotalUsers = 0;

    // hide main page and show login screen
    $('#page').hide();
    $('#login').hide();


    // Check if use has already logged in
    var checkToken = Cookies.get('token');

    if (checkToken) {
        $('#login').remove();
        getSystem(checkToken);
        enterWeb(checkToken);
        getCountry();

        console.log('You have old token!', Cookies.get('token'));

    } else { // get all countries from API


        $.ajax({
            type: "get",
            url: "/countries",
            success: function (data) {
                $('#login').toggle('slide');
                $('#loading-page').hide();
                var selOpts = "";
                for (let i = 0; i < data.length; i++) {
                    var id = data[i]['country'];
                    var val = data[i]['name'];
                    selOpts += "<option value='" + id + "'>" + val + "</option>";
                }

                $('#country').append(selOpts);
            },
            error: function (data) {
                $('#loading-page').hide();
            }
        });

    }

    function setName(name) {
        Cookies.set('name', name);
    }

    function getName() {
        return Cookies.get('name');
    }


    function getCountry() {
        $.ajax({
            type: "get",
            url: "/countries",
            success: function (data) {

                var selOpts = "";
                for (let i = 0; i < data.length; i++) {
                    var id = data[i]['country'];
                    var val = data[i]['name'];
                    selOpts += "<option value='" + id + "'>" + val + "</option>";
                }

                $('#countryTechnician').append(selOpts);
            },
            error: function (data) {
                console.log('error');
            }
        });
    }

    $('#getToken').on('submit', function (e) { // get login form
        e.preventDefault();

        let path;
        let data;
        let login;

        if ($('#showCode').is(":hidden")) { // first go to /register route and get code on your phone
            path = '/register';
            data = {'name': 'Admin', 'phone': $('#phone').val(), 'country': $('#country').val()}
        } else {
            path = '/login'; // showCode will show and after entering the code, send to /login route
            data = {
                'phone': $('#phone').val(),
                'code': $('#code').val(),
                'country': $('#country').val(),
            };
            login = true;
        }
        $('#loading').show();
        $.ajax({
            type: 'post',
            url: path,
            data: data,
            success: function (data) {

                if (login) {

                    //  if path is /login get Bearer token
                    $('#loading').hide();
                    Cookies.set('token', data);
                    enterWeb(Cookies.get('token'));
                } else {
                    // if path is /register show showCode input
                    $('#loading').hide();
                    $('#showCode').toggle('slide');
                    $('#phoneNumber').toggle('slide');
                    $('#countryName').toggle('slide');
                }


            },
            error: function (data) {

                // show errors on login window
                $('#loading').hide();
                $.each(data.responseJSON, function (key, value) {
                    $("#error").html(value);
                });
            }
        });
    });

    $('#addTechnician').on('submit', function (e) {
        e.preventDefault();

        var data = Cookies.get('token');
        data = JSON.parse(data);
        var token = data.token.access_token;

        var sendData = {
            name: $("#nameTechnician").val(),
            email: $("#emailTechnician").val(),
            password: $("#passwordTechnician").val(),
            country: $("#countryTechnician").val(),
            phone: $("#phoneTechnician").val()
        };


        $.ajax({
            type: "POST",
            url: "/api/users",
            data: sendData,
            headers: {
                'Authorization': `Bearer ${token}`,
            },
            beforeSend: function () {
                $('#loading-form-technitian').show();
            },
            success: function (data) {
                $('#loading-form-technitian').hide();

                $("#nameTechnician").val('');
                $("#emailTechnician").val('');
                $("#passwordTechnician").val('');
                $("#phoneTechnician").val('');
                getTechnicians();
                $('#myModal').modal('toggle');
            },
            error: function (data) {

                // show errors on login window
                $('#loading-form-technitian').hide();
                $.each(data.responseJSON, function (key, value) {
                    $("#error-technician").html(value);
                });
            }
        });


    });

    function getTechnicians() {
        var data = Cookies.get('token');
        data = JSON.parse(data);
        var token = data.token.access_token;

        $.ajax({
            type: "get",
            url: "/api/dashboard/technicians",
            headers: {
                'Authorization': `Bearer ${token}`,
            },
            beforeSend: function () {
                $("#loading-page-technicians").show();
            },
            success: function (data) {
                $("#loading-page-technicians").hide();

                $("#addTechnicians").html('');

                $.each(data, function (key, value) {

                    var tbRow = '<tbody><tr>';
                    tbRow += '<td>' + value.name + '</td>';
                    tbRow += '<td>' + value.email + '</td>';
                    tbRow += '<td>' + value.country + '</td>';
                    tbRow += '<td>' + value.phone + '</td>';
                    tbRow += '<td><button data-id="' + value.id + '" class="btn btn-danger btn-sm deleteTechnician" onclick="deleteTechnician(this)">Delete</button></td>';
                    tbRow += '</tr></tbody>';
                    $("#addTechnicians").append(tbRow);

                });

            },
            error: function () {

            }
        });

    }




    function enterWeb(data) {
        getTechnicians();


        var user_id = Cookies.get('user_id');


        // if user is logged in show page and remove login screen
        data = JSON.parse(data);


        if (!getName()) {
            setName(data.user.name);
        }

        $('#login').remove();
        $('#page').show();
        $('.name').html(getName);
        $('title').html('TouchAPP Dashboard');
        // Get all data for DashBoard


        var sendParams;
        if (user_id) {
            sendParams = {user: user_id};
            Cookies.remove('user_id');


            $('#selected-user').show();

            var name = '<a href="/" class="orange">x</a><span class="orange"> ' + Cookies.get('user_name') + '</span>';
            $('#selected-user').html(name);
            Cookies.remove('user_name');
        }


        var token = data.token.access_token;
        $('#loading-page').show();
        $('#loading-page-thermostats').show();
        $('#loading-page-properties').show();
        $('#loading-page-users').show();
        $.ajax({
            type: "get",
            url: "/api/dashboard",
            data: sendParams,
            headers: {
                'Authorization': `Bearer ${token}`,
            },
            success: function (data) {

                $('#loading-page').hide();
                $('#loading-page-thermostats').hide();
                $('#loading-page-properties').hide();
                $('#loading-page-users').hide();
                showMap(data);

            },
            error: function () {
                getNewToken();
            }
        });
    }

    function getSystem(data) {
        data = JSON.parse(data);
        var token = data.token.access_token;


        $('#loading-page-system').show();


        setInterval(function () {
            $('#loading-page-system').show();
            $.ajax({
                type: "get",
                url: "/api/system",
                headers: {
                    'Authorization': `Bearer ${token}`,
                },
                success: function (data) {

                    $('#loading-page-system').hide();
                    $('.discUsage').html(data.discUsage);
                    $('.httpConnections').html(data.httpConnections);
                    $('.kernelVersion').html(data.kernelVersion);
                    $('.memory').html(data.memory);
                    $('.numberProcesses').html(data.numberProcesses);
                    $('.serverMemory').html(data.serverMemory);
                    $('.systemCores').html(data.systemCores);
                    $('.systemLoad').html(data.systemLoad);
                },
                error: function () {
                    getNewToken();
                }
            });
        }, 10000);


    }

    // logout user
    $('#logout').on('click', function () {
        Cookies.remove('token');
        Cookies.remove('name');
        location.reload();
    });

    function showMap(data) {

        TotalProperties = data.totalProperties;
        TotalThermostats = data.totalThermostats;
        Online = data.online;
        Offline = data.offline;
        totalUsers = data.totalUsers;

        $('.totalProperties').html(data.totalProperties);
        $('.totalThermostats').html(data.totalThermostats);
        $('.online').html(data.online);
        $('.offline').html(data.offline);
        $('.totalUsers').html('Total Users: ' + data.totalUsers);

        var mymap = L.map('mapid').setView([51.505, -0.09], 3);

        L.tileLayer('https://api.tiles.mapbox.com/v4/{id}/{z}/{x}/{y}.png?access_token=pk.eyJ1IjoibWFwYm94IiwiYSI6ImNpejY4NXVycTA2emYycXBndHRqcmZ3N3gifQ.rJcFIG214AriISLbB6B5aw', {
            maxZoom: 18,
            attribution: 'Map data &copy; <a href="https://www.openstreetmap.org/">OpenStreetMap</a> contributors, ' +
                '<a href="https://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>, ' +
                'Imagery © <a href="https://www.mapbox.com/">Mapbox</a>',
            id: 'mapbox.streets'
        }).addTo(mymap);
        var markers = {};
        $.each(data.properties, function (key, value) {

            markers[value.id] = L.marker([value.lat, value.lng], {propertyId: value.id}).addTo(mymap).bindPopup(value.name).on('click', onClick).on('popupclose', returnState);
        });
    }


    function returnState(e) {
        $('.property').html('Total Properties: ' + TotalProperties);
        $('.totalThermostats').html(TotalThermostats);
        $('.online').html(Online);
        $('.offline').html(Offline);
        $('.totalUsers').html('Total Users: ' + totalUsers);
        $('.showThermostats').html('');
        $('.thermostat-info').hide();


    }

    function onClick(e) {

        var propertyId = this.options.propertyId;
        var data = Cookies.get('token');
        data = JSON.parse(data);
        var token = data.token.access_token;

        var userId = 0;

        $('#loading-page-thermostats').show();
        $('#loading-page-properties').show();
        $('#loading-page-totalusers').show();
        $.ajax({
            type: "get",
            url: "/api/properties/" + propertyId,
            headers: {
                'Authorization': `Bearer ${token}`,
            },
            success: function (data) {


                userId = data[0].user_id;

                getOwner(userId);
                getUsers(propertyId);
                $('#loading-page-thermostats').hide();
                $('#loading-page-properties').hide();

                var lists = "";
                var online = 0;
                var offline = 0;
                var total = 0;

                var addClass = '';

                $.each(data[0].thermostats, function (key, value) {
                    value.online === 1 ? addClass = 'thermostat-online' : addClass = 'thermostat-offline';
                    var last_online = value.online === 0 ? ' [offline ' + value.last_online + ']' : '';
                    lists += '<li class="' + addClass + '"><button class="btn btn-default orange thermostat" data-id="' + value.id + '">' + value.room_name + ' ' + last_online + '</button></li>';
                    value.online === 1 ? online++ : offline++;

                });


                var address = data[0]['address'];
                var name = data[0]['name'];
                var square_meters = data[0]['square_meters'] + data[0]['square_measure'];

                var show = '<ul>' + '<li>' + name + '</li>' + '<li>' + address + '</li>' + '<li>' + square_meters + '</li>' + '</ul>';

                $('.property').html(show);

                $('.totalThermostats').html(total);
                $('.online').html(online);
                $('.offline').html(offline);
                $('.showThermostats').html('<ul>' + lists + '</ul>');
                thermostatAction();
            },
            error: function () {
                getNewToken();
            }
        });

    }


    $("#search-box").keyup(function () {

        var data = Cookies.get('token');
        data = JSON.parse(data);
        var token = data.token.access_token;

        var sendData = {keyword: $(this).val()};
        $.ajax({
            type: "POST",
            url: "/api/users/search",
            data: sendData,
            headers: {
                'Authorization': `Bearer ${token}`,
            },
            beforeSend: function () {
                $("#search-box").css("background", "#FFF url(/images/gears.gif) no-repeat 165px");
            },
            success: function (data) {

                var lists = '';
                $.each(data, function (key, value) {

                    lists += '<li class="suggestion-box-items"><div><p class="btn btn-default orange" onclick="thisUser(this)" data-name="' + value.name + '" data-id="' + value.id + '">' + value.name + '  (' + value.phone + ')</p></div></li>';


                });
                $("#suggestion-box").show();
                $("#suggestion-box").html('<ul>' + lists + '</ul>');
                $("#search-box").css("background", "#FFF");
            }
        });


        $("#suggestion-box").mouseleave(function () {
            $("#suggestion-box").hide();
        });

    });

    function getOwner(userId) {

        $('#loading-page-owner').show();
        $('.totalUsers').html('');
        var data = Cookies.get('token');
        data = JSON.parse(data);
        var token = data.token.access_token;

        $.ajax({
            type: "get",
            url: "/api/users/" + userId,
            headers: {
                'Authorization': `Bearer ${token}`,
            },
            success: function (data) {

                $('#loading-page-owner').hide();

                let me = '<p class="orange">Owner</p>';
                me += '<p>' + data.name + ' | ' + data.phone + '</p>';

                $('.totalUsers').append(me);


            },
            error: function () {
                getNewToken();
            }
        });
    }

    function getUsers(propertyId) {
        $('#loading-page-users').show();
        $('.totalUsers').html('');
        var data = Cookies.get('token');
        data = JSON.parse(data);
        var token = data.token.access_token;

        $.ajax({
            type: "get",
            url: "/api/properties/" + propertyId + '/users',
            headers: {
                'Authorization': `Bearer ${token}`,
            },
            success: function (data) {

                $('#loading-page-users').hide();

                if (data) {
                    let me = '<p class="orange">Users</p>';
                    $.each(data, function (key, value) {
                        me += '<p>' + value.name + ' | ' + value.phone + '</p>';
                    });

                    $('.totalUsers').append(me);
                }


            },
            error: function () {
                getNewToken();
            }
        });
    }

    function thermostatAction() {

        $('.thermostat').on('click', function (e) {
            $('.thermostat-info').show();
            $('.results').html('');
            let thermostatId = $(e.target).attr("data-id");

            getThermostat(thermostatId);

        });
    }

    function getThermostat(thermostatId) {

        var data = Cookies.get('token');
        data = JSON.parse(data);
        var token = data.token.access_token;


        $('#loading-page-thermostat').show();

        $.ajax({
            type: "get",
            url: "/api/thermostats/" + thermostatId,
            headers: {
                'Authorization': `Bearer ${token}`,
            },
            success: function (data) {

                $('#loading-page-thermostat').hide();
                $.each(data, function (key, value) {
                    $("#" + key + "").val(value);
                });


            },
            error: function () {
                getNewToken();
            }
        });
    }


    $(".slider").knob({
        'fgColor': '#8d78ff',
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
                json = '{"id":' + $('#id').val() + ', "' + attrName + '":' + v + ',"offset_sign":' + offset_sing + '}';

            } else {
                var json = '{"id":' + $('#id').val() + ', "' + attrName + '":' + v + '}';

            }

            var sendData = JSON.parse(json);

            var token = JSON.parse(Cookies.get('token'));

            token = token.token.access_token;

            $.ajax({
                type: 'POST',
                url: '/api/set-commands',
                data: sendData,
                dataType: 'json',
                headers: {
                    'Authorization': `Bearer ${token}`,
                },
                success: function (data) {
                    console.log('Success', data);
                },
                error: function () {
                    getNewToken();
                }
            })
        }
    });


    $('.toggleSelect').change(function (e) {
        e.preventDefault();

        var elem = e.target;
        var elemName = elem.id;
        var elemValue = $(elem).val();


        var json = '{"id":' + $('#id').val() + ', "' + elemName + '":' + elemValue + '}';
        var sendData = JSON.parse(json);


        var token = JSON.parse(Cookies.get('token'));

        token = token.token.access_token;

        $.ajax({
            type: 'POST',
            url: '/api/set-commands',
            data: sendData,
            dataType: 'json',
            headers: {
                'Authorization': `Bearer ${token}`,
            },
            success: function (data) {
                console.log('Success', data);
            },
            error: function () {
                getNewToken();
            }
        })

    });

    $('.toggleElem').change(function (e) {
        e.preventDefault();

        var elem = e.target;
        var elemName = elem.id;
        var elemValue = $(elem).prop('checked') ? 1 : 0;


        var json = '{"id":' + $('#id').val() + ', "' + elemName + '":' + elemValue + '}';
        var sendData = JSON.parse(json);

        var token = JSON.parse(Cookies.get('token'));

        token = token.token.access_token;


        $.ajax({
            type: 'POST',
            url: '/api/set-commands',
            data: sendData,
            dataType: 'json',
            headers: {
                'Authorization': `Bearer ${token}`,
            },
            success: function (data) {
                console.log('Success', data);
            },
            error: function () {
                getNewToken();
            }
        })

    });

    function getNewToken() {


        var data = Cookies.get('token');
        data = JSON.parse(data);
        console.log(data);
        var refresh_token = data.token.refresh_token;
        Cookies.remove('token');

        $('#loading-page-thermostat').show();

        $.ajax({
            type: "post",
            url: "/refresh-token",
            data: {refresh_token: refresh_token},
            success: function (data) {
                Cookies.set('token', {token: data});
                console.log('You have new token!', Cookies.get('token'));
                enterWeb(Cookies.get('token'));
                location.reload();
            },
            error: function (data) {

                console.log(data);
            }
        });
    }

});
