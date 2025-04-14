<!DOCTYPE html>
<html>
<head>
    <title>Login Page</title>

    <!--Bootsrap 4 CDN-->
    <!--Fontawesome CDN-->
    <link href="/css/all.css" rel="stylesheet">

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.5.1/dist/leaflet.css"
          integrity="sha512-xwE/Az9zrjBIphAcBb3F6JVqxf46+CDLwfLMHloNu6KEQCAWi6HcDUbeOfBIptF7tcCzusKFjFw2yuvEpDL9wQ=="
          crossorigin=""/>
    <link rel="stylesheet" href="/css/bootstrap.min.css">
    <link rel="stylesheet" href="/css/bootstrap-toggle.min.css">
    <!--Custom styles-->
    <link rel="stylesheet" type="text/css" href="/css/login.css">

    <link rel="icon" href="/images/favicon.ico">

</head>
<body>
<div class="container-fluid">
    <div id="page" style="display: none">

        <div class="row">
            <div class="col-lg-12">
                <div class="card-left">
                    <div class="card-header height">
                        <div class="float-left">
                            <form id="search-form" class="inline-form">
                                <div class="md-form mt-0">
                                    <input class="form-control" type="text" id="search-box" placeholder="Search" aria-label="Search"
                                           autocomplete="off">
                                    <div id="suggestion-box"></div>
                                </div>

                            </form>

                        </div>
                        <div class="float-left">
                            <div id="selected-user" style="display: none"></div>
                        </div>
                        <div class="float-right">
                            <h6>Welcome <span class="name"> </span>
                                <button type="button" id="logout" class="btn btn-warning btn-sm">
                                    <i class="fas fa-sign-out-alt"></i> Log out
                                </button>
                            </h6>
                        </div>


                    </div>
                    <div class="card-body">
                        <div class="center-block">

                        </div>
                    </div>

                </div>
            </div>
        </div>


        <div class="row">
            <div class="col-md-12">
                <div class="card">


                    <div class="card-header height">
                        <div class="float-left">
                            <h6>
                                <button type="button" class="btn btn-warning btn-sm" data-toggle="modal" data-target="#myModal">Add Technician
                                </button>
                            </h6>

                        </div>


                    </div>
                    <div class="card-body">
                        <div class="center-block">
                            <div class="sys_info">Technicians: <span class="totalTechnicians"></span></div>

                            <img src="/images/gears.gif" id="loading-page-technicians" style="display:none; width: 55px; height: 55px;">


                            <div id="technicians">
                                <table class="table"  id="addTechnicians">
                                    <tr>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Country</th>
                                        <th>Phone</th>
                                        <th>Actions</th>
                                    </tr>

                                </table>

                            </div>

                        </div>

                    </div>

                    <!-- Trigger the modal with a button -->


                    <!-- Modal -->
                    <div id="myModal" class="modal fade" role="dialog">
                        <div class="modal-dialog">

                            <!-- Modal content-->
                            <div class="modal-content">
                                <div class="modal-header">

                                    <h4 class="modal-title orange">Add Technician</h4>
                                    <button type="button" class="btn btn-default" data-dismiss="modal">x</button>
                                </div>
                                <div class="modal-body">
                                    <form id="addTechnician">
                                        <div class="form-group">
                                            <label for="name">Name:</label>
                                            <input type="text" class="form-control" id="nameTechnician">
                                        </div>
                                        <div class="form-group">
                                            <label for="email">Email address:</label>
                                            <input type="email" class="form-control" id="emailTechnician">
                                        </div>
                                        <div class="form-group">
                                            <label for="name">Password:</label>
                                            <input type="password" class="form-control" id="passwordTechnician">
                                        </div>
                                        <div class="form-group">
                                            <label for="name">Phone:</label>
                                            <input type="text" class="form-control" id="phoneTechnician">
                                        </div>
                                        <div class="input-group mb-3" id="countryNameTechnician">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text"><i class="fas fa-flag"></i></span>
                                            </div>
                                            <select class="custom-select" placeholder="country" id="countryTechnician">

                                            </select>
                                        </div>


                                        <img src="/images/gears.gif" id="loading-form-technitian" style="display:none">
                                        <div id="error-technician"></div>
                                        <button type="submit" class="btn btn-success">Submit</button>
                                    </form>
                                </div>

                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="push-down"></div>
        <div class="row">
            <div class="col-lg-3">

                <div class="card">
                    <div class="card-header height">
                        <div class="float-left">
                            <h6>Users</h6>
                        </div>


                    </div>
                    <div class="card-body">
                        <div class="center-block">
                            <div class="sys_info totalUsers">Total users: <span class="totalUsers"></span></div>

                            <img src="/images/gears.gif" id="loading-page-owner" style="display:none">
                            <img src="/images/gears.gif" id="loading-page-users" style="display:none">
                        </div>

                    </div>

                </div>

            </div>


            <div class="col-lg-3">
                <div class="card">
                    <div class="card-header height">
                        <div class="float-left">
                            <h6>Properties</h6>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="center-block">

                            <div class="sys_info property">Total Properties: <span class="totalProperties"></span></div>
                            <img src="/images/gears.gif" id="loading-page-properties"
                                 style="display:none">
                        </div>
                    </div>

                </div>
            </div>

            <div class="col-lg-6">

                <div class="card-map">
                    <div class="card-header height">
                        <div class="float-left">
                            <h6>Properties</h6>
                        </div>


                    </div>
                    <div class="card-body">
                        <div class="center-block">
                            <img src="/images/gears.gif" id="loading-page"
                                 style="display:none">

                            <div id="mapid"></div>

                        </div>


                    </div>

                </div>

            </div>


        </div>
        <div class="push-down"></div>
        <div class="row">
            <div class="col-lg-3">
                <div class="card">
                    <div class="card-header height">
                        <div class="float-left">
                            <h6>System information</h6>
                        </div>


                    </div>
                    <div class="card-body">
                        <div class="center-block">


                            <div class="sys_info">Disc Usage: <span class="discUsage"></span></div>
                            <div class="sys_info">Http Connections: <span class="httpConnections"></span></div>
                            <div class="sys_info">Kernel Version: <span class="kernelVersion"></span></div>
                            <div class="sys_info">Web Memory: <span class="memory"></span></div>
                            <div class="sys_info">Number of process: <span class="numberProcesses"></span></div>
                            <div class="sys_info">Server Memory: <span class="serverMemory"></span></div>
                            <div class="sys_info">System Cores: <span class="systemCores"></span></div>
                            <div class="sys_info">System Load: <span class="systemLoad"></span></div>
                            <img src="/images/gears.gif" id="loading-page-system"
                                 style="display:none">
                        </div>


                    </div>

                </div>
            </div>
            <div class="col-lg-3">
                <div class="card">
                    <div class="card-header height">
                        <div class="float-left">
                            <h6>Thermostats</h6>
                        </div>


                    </div>
                    <div class="card-body">
                        <div class="center-block">


                            <div class="sys_info">Total Thermostats: <span class="totalThermostats"></span></div>
                            <div class="sys_info">Online: <span class="online"></span></div>
                            <div class="sys_info">Offline: <span class="offline"></span></div>

                            <div class="sys_info showThermostats"></div>
                            <img src="/images/gears.gif" id="loading-page-thermostats"
                                 style="display:none">

                        </div>


                    </div>

                </div>
            </div>


            <div class="col-lg-6">
                <div class="thermostat-info" style="display:none">
                    <div class="card-map">
                        <div class="card-header height">
                            <div class="float-left">
                                <h6>Thermostat</h6>
                            </div>


                        </div>
                        <div class="card-body">
                            <div class="center-block">
                                <img src="/images/gears.gif" id="loading-page-thermostat"
                                     style="display:none">


                                <form id="form_term" class="mt-5">
                                    <div class="form-group row">
                                        <input type="hidden" name="id" id="id">

                                        <div class="col-md-6 text-center p-3">
                                            <label for="room_name" class="font-weight-bold">Room name</label>
                                            <input type="text" id="room_name" class="form-control" disabled>
                                        </div>
                                        <div class="col-md-6 text-center p-3">
                                            <label for="set_temp" class="font-weight-bold">Set Temperature</label><br>
                                            <input class="slider" data-min="0" data-max="50" id="set_temp"
                                                   name="set_temp" value="1">
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <div class="col-md-6 text-center p-3">
                                            <label for="sched_temp" class="font-weight-bold">Scedule Temperature</label><br>
                                            <input class="slider" data-min="0" data-max="50" id="sched_temp"
                                                   name="sched_temp">
                                        </div>
                                        <div class="col-md-6 text-center p-3">
                                            <label for="max_temp" class="font-weight-bold">Max Temperature</label><br>
                                            <input class="slider" data-min="22" data-max="50" id="max_temp"
                                                   name="max_temp">
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <div class="col-md-6 text-center p-3">
                                            <label for="min_temp" class="font-weight-bold">Min Temperature</label><br>
                                            <input class="slider" data-min="5" data-max="21" id="min_temp"
                                                   name="min_temp">
                                        </div>
                                        <div class="col-md-6 text-center p-3">
                                            <label for="offset_sign" class="font-weight-bold">offset_sign</label><br>


                                            <input id="offset_sign" name="offset_sign" type="checkbox"
                                                   data-toggle="toggle" data-on="True" data-off="False"
                                                   data-onstyle="success"
                                                   data-offstyle="danger">


                                        </div>


                                    </div>
                                    <div class="form-group row">
                                        <div class="col-md-6 text-center p-3">
                                            <label for="offset_temp" class="font-weight-bold">Offset Temperature</label><br>
                                            <input class="slider" data-min="0" data-max="50" id="offset_temp"
                                                   name="offset_temp">
                                        </div>
                                        <div class="col-md-6 text-center p-3">
                                            <label for="temp_limiter" class="font-weight-bold">Temp. Limiter</label><br>
                                            <input class="slider" data-min="0" data-max="50" id="temp_limiter"
                                                   name="temp_limiter">
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <div class="col-md-6 text-center p-3">
                                            <label for="mode" class="font-weight-bold">Mode</label><br>
                                            <select id="mode" name="mode" class="form-control toggleSelect">
                                                <option value="0">0</option>
                                                <option value="1">1</option>
                                                <option value="2">2</option>
                                                <option value="3">3</option>
                                                <option value="4">4</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6 text-center p-3">
                                            <label for="sensors_mode" class="font-weight-bold">Sensors mode</label><br>
                                            <select id="sensors_mode" name="sensors_mode"
                                                    class="form-control toggleSelect">
                                                <option value="0">0</option>
                                                <option value="1">1</option>
                                                <option value="2">2</option>
                                                <option value="3">3</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <div class="col-md-6 text-center p-3">
                                            <label for="temp_measurement" class="font-weight-bold">Temp.
                                                measurement</label><br>


                                            <input id="temp_measurement" name="temp_measurement" type="checkbox"
                                                   class="toggleElem"
                                                   data-toggle="toggle" data-on="True" data-off="False"
                                                   data-onstyle="success"
                                                   data-offstyle="danger">

                                        </div>
                                        <div class="col-md-6 text-center p-3">
                                            <label for="relay_opera" class="font-weight-bold">Relay
                                                operation</label><br>

                                            <input id="relay_opera" name="relay_opera" type="checkbox"
                                                   class="toggleElem"
                                                   data-toggle="toggle" data-on="True" data-off="False"
                                                   data-onstyle="success"
                                                   data-offstyle="danger">
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <div class="col-md-6 text-center p-3">
                                            <label for="relay_limit" class="font-weight-bold">Relay Limit</label><br>
                                            <input class="slider" data-min="0" data-max="10" id="relay_limit"
                                                   name="relay_limit">
                                        </div>
                                        <div class="col-md-6 text-center p-3">
                                            <label for="sensitivity" class="font-weight-bold">Sensitivity</label><br>
                                            <input class="slider" data-min="0" data-max="3" data-step="0.01"
                                                   id="sensitivity" name="sensitivity">
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <div class="col-md-6 text-center p-3">
                                            <label for="differential" class="font-weight-bold">Differential</label><br>
                                            <input class="slider" data-min="0" data-max="3" id="differential"
                                                   name="differential">
                                        </div>
                                        <div class="col-md-6 text-center p-3">
                                            <label for="cool_heat_mode"
                                                   class="font-weight-bold">cool_heat_mode</label><br>
                                            <input class="slider" data-min="0" data-max="2"
                                                   id="cool_heat_mode"
                                                   name="cool_heat_mode">
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <div class="col-md-6 text-center p-3">
                                            <label for="boiler_duration" class="font-weight-bold">Boiler
                                                Duration</label><br>
                                            <input class="slider" data-min="30" data-max="210"
                                                   id="boiler_duration"
                                                   name="boiler_duration">
                                        </div>
                                        <div class="col-md-6 text-center p-3">
                                            <label for="home_router_mac_address" class="font-weight-bold">Home Router
                                                MAC Address</label><br>
                                            <input type="text" class="form-control"
                                                   id="home_router_mac_address"
                                                   name="home_router_mac_address" placeholder="home_router_mac_address">
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <div class="col-md-6 text-center p-3">
                                            <label for="bathroom_on_low_heat" class="font-weight-bold">bathroom_on_low_heat</label><br>
                                            <input class="slider" data-min="1" data-max="30"
                                                   id="bathroom_on_low_heat"
                                                   name="bathroom_on_low_heat">
                                        </div>
                                        <div class="col-md-6 text-center p-3">
                                            <label for="bathroom_delay_stby_low_heat">bathroom_delay_stby_low_heat</label><br>
                                            <input class="slider" data-min="1" data-max="30"
                                                   id="bathroom_delay_stby_low_heat"
                                                   name="bathroom_delay_stby_low_heat">
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <div class="col-md-6 text-center p-3">
                                            <label for="bathroom_on_med_heat" class="font-weight-bold">bathroom_on_med_heat</label><br>
                                            <input class="slider" data-min="1" data-max="30"
                                                   id="bathroom_on_med_heat"
                                                   name="bathroom_on_med_heat">
                                        </div>
                                        <div class="col-md-6 text-center p-3">
                                            <label for="bathroom_delay_stby_med_heat"
                                                   class="font-weight-bold">bathroom_delay_stby_med_heat</label><br>
                                            <input class="slider" data-min="1" data-max="30"
                                                   id="bathroom_delay_stby_med_heat"
                                                   name="bathroom_delay_stby_med_heat">
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <div class="col-md-6 text-center p-3">
                                            <label for="bathroom_on_high_heat" class="font-weight-bold">bathroom_on_high_heat</label><br>
                                            <input class="slider" data-min="1" data-max="30"
                                                   id="bathroom_on_high_heat"
                                                   name="bathroom_on_high_heat">
                                        </div>
                                        <div class="col-md-6 text-center p-3">
                                            <label for="bathroom_delay_stby_high_heat"
                                                   class="font-weight-bold">bathroom_delay_stby_high_heat</label><br>
                                            <input class="slider" data-min="1" data-max="30"
                                                   id="bathroom_delay_stby_high_heat"
                                                   name="bathroom_delay_stby_high_heat">
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <div class="col-md-6 text-center p-3">
                                            <label for="cool_room_check"
                                                   class="font-weight-bold">cool_room_check</label><br>
                                            <input class="slider" data-min="0" data-max="120"
                                                   id="cool_room_check"
                                                   name="cool_room_check">
                                        </div>
                                        <div class="col-md-6 text-center p-3">
                                            <label for="boost" class="font-weight-bold">Boost</label><br>
                                            <input class="slider" data-min="0" data-max="240" id="boost"
                                                   name="boost">
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <div class="col-md-6 text-center p-3">
                                            <label for="ligth_intensity" class="font-weight-bold">Light
                                                Intensity</label><br>
                                            <input class="slider" data-min="0" data-max="2"
                                                   id="ligth_intensity"
                                                   name="ligth_intensity">
                                        </div>
                                        <div class="col-md-6 text-center p-3">
                                            <label for="enab_vibration"
                                                   class="font-weight-bold">enab_vibration</label><br>


                                            <input id="enab_vibration" name="enab_vibration" type="checkbox"
                                                   class="toggleElem"
                                                   data-toggle="toggle" data-on="True" data-off="False"
                                                   data-onstyle="success"
                                                   data-offstyle="danger">
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <div class="col-md-6 text-center p-3">
                                            <label for="enab_matrix" class="font-weight-bold">enab_matrix</label><br>

                                            <input id="enab_matrix" name="enab_matrix" type="checkbox"
                                                   class="toggleElem"
                                                   data-toggle="toggle" data-on="True" data-off="False"
                                                   data-onstyle="success"
                                                   data-offstyle="danger">

                                        </div>
                                        <div class="col-md-6 text-center p-3">
                                            <label for="enab_hart_beep_led"
                                                   class="font-weight-bold">enab_hart_beep_led</label><br>

                                            <input id="enab_hart_beep_led" name="enab_hart_beep_led" type="checkbox"
                                                   class="toggleElem"
                                                   data-toggle="toggle" data-on="True" data-off="False"
                                                   data-onstyle="success"
                                                   data-offstyle="danger">
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <div class="col-md-6 text-center p-3">
                                            <label for="enab_lock" class="font-weight-bold">enab_lock</label><br>


                                            <input id="enab_lock" name="enab_lock" type="checkbox" class="toggleElem"
                                                   data-toggle="toggle" data-on="True" data-off="False"
                                                   data-onstyle="success"
                                                   data-offstyle="danger">

                                        </div>
                                        <div class="col-md-6 text-center p-3">
                                            <label for="last_operation" class="font-weight-bold">Last
                                                Operation</label><br>
                                            <input class="slider" data-min="0" data-max="15"
                                                   id="last_operation"
                                                   name="last_operation">
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <div class="col-md-6 text-center p-3">
                                            <label for="last_operation_value" class="font-weight-bold">Last operation
                                                Value</label><br>
                                            <input class="slider" data-min="0" data-max="50"
                                                   id="last_operation_value"
                                                   name="last_operation_value">
                                        </div>
                                        <div class="col-md-6 text-center p-3">
                                            <label for="restore_default"
                                                   class="font-weight-bold">restore_default</label><br>

                                            <input id="restore_default" name="restore_default" type="checkbox"
                                                   class="toggleElem"
                                                   data-toggle="toggle" data-on="True" data-off="False"
                                                   data-onstyle="success"
                                                   data-offstyle="danger">
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <div class="col-md-6 text-center p-3">
                                            <label for="encription_code" class="font-weight-bold">Encription
                                                Code</label><br>
                                            <input class="slider" data-min="0" data-max="990"
                                                   id="encription_code"
                                                   name="encription_code">
                                        </div>
                                        <div class="col-md-6 text-center p-3">
                                        </div>
                                    </div>

                                </form>
                            </div>


                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container" id="login">
    <div class="d-flex justify-content-center h-100">
        <div class="card">
            <div class="card-header">
                <h3>Sign In</h3>

                <div id="error" class="d-flex justify-content-center links"></div>
            </div>
            <div class="card-body">
                <form id="getToken">
                    <div class="input-group form-group" id="phoneNumber">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-phone"></i></span>
                        </div>
                        <input type="text" class="form-control" id="phone" placeholder="Phone">

                    </div>
                    <div class="input-group form-group" id="showCode" style="display:none">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fa fa-lock"></i></span>
                        </div>
                        <input type="text" class="form-control" id="code" placeholder="Code">
                    </div>

                    <div class="input-group mb-3" id="countryName">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-flag"></i></span>
                        </div>
                        <select class="custom-select" placeholder="country" id="country">

                        </select>
                    </div>

                    <div class="form-group">
                        <img src="/images/gears.gif" id="loading"
                             style="display:none">
                        <input type="submit" value="Login" class="btn float-right login_btn">
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>


<script src="/js/jquery.min.js"></script>
<script src="/js/bootstrap.min.js"></script>
<script src="/js/js.cookie.min.js"></script>
<script src="/js/jquery.session.js"></script>
<script src="/js/knob.js"></script>
<script src="/js/leaflet.js"></script>
<script src="/js/bootstrap-toggle.min.js"></script>
<script src="/js/custom.js"></script>

</body>
</html>