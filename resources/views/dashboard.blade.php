<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <title>Sapo | Dashboard</title>
  <!-- Tell the browser to be responsive to screen width -->
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport" />
  <!-- Bootstrap 3.3.7 -->
  <link rel="stylesheet" href="_raw/admin-lte/bower_components/bootstrap/dist/css/bootstrap.min.css" />
  <!-- Font Awesome -->
  <link rel="stylesheet" href="_raw/admin-lte/bower_components/font-awesome/css/font-awesome.min.css" />
  <!-- Ionicons -->
  <link rel="stylesheet" href="_raw/admin-lte/bower_components/Ionicons/css/ionicons.min.css" />
  <!-- Theme style -->
  <link rel="stylesheet" href="_raw/admin-lte/dist/css/AdminLTE.min.css" />
  <!--
      AdminLTE Skins. Choose a skin from the css/skins
      folder instead of downloading all of them to reduce the load.
    -->
  <link rel="stylesheet" href="_raw/admin-lte/dist/css/skins/_all-skins.min.css" />

  <link rel="stylesheet" href="_raw/admin-lte/bower_components/datatables.net-bs/css/dataTables.bootstrap.min.css" />

  <!-- Select2 -->
  <link rel="stylesheet" href="_raw/admin-lte/bower_components/select2/dist/css/select2.min.css">

  <!-- animsition -->
  <!-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animsition/4.0.2/css/animsition.css" /> -->

  <!--
      HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries
    -->
  <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
  <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->

  <!-- Google Font -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700,300italic,400italic,600italic" />

  <style>
    #communication-log span.selection > span{
      height: 35px;
    }
    body, .animsition-overlay-slide {
      background-color:black;
    }
  </style>
</head>
<!-- ADD THE CLASS layout-top-nav TO REMOVE THE SIDEBAR. -->

<body class="hold-transition skin-blue layout-top-nav">
  <div class="wrapper animsition-overlay" data-animsition-in-class="fade-in" data-animsition-in-duration="1000"
    data-animsition-out-class="fade-out" data-animsition-out-duration="800">
    <header class="main-header">
      <nav class="navbar navbar-static-top">
        <div class="container">
          <div class="navbar-header">
            <a href="_raw/admin-lte/index2.html" class="navbar-brand"><b>Dev</b> Enviroment</a>
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar-collapse">
              <i class="fa fa-bars"></i>
            </button>
          </div>

          <!--
              Collect the nav links, forms, and other content for toggling
            -->
          <div class="collapse navbar-collapse pull-left" id="navbar-collapse">
            <ul class="nav navbar-nav">
              <li class="active">
                <a href="#">Dashboard <span class="sr-only">(current)</span></a>
              </li>
            </ul>
          </div>
          <!-- /.navbar-collapse -->
          <!-- Navbar Right Menu -->
          <div class="navbar-custom-menu">
            <ul class="nav navbar-nav">
              <!-- /.messages-menu -->

              <!-- Notifications Menu -->
              <li class="dropdown notifications-menu">
                <!-- Menu toggle button -->
                <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                  <i class="fa fa-bell-o"></i>
                  <span class="label label-warning">10</span>
                </a>
                <ul class="dropdown-menu">
                  <li class="header">You have 10 notifications</li>
                  <li>
                    <!-- Inner Menu: contains the notifications -->
                    <ul class="menu">
                      <li>
                        <!-- start notification -->
                        <a href="#">
                          <i class="fa fa-users text-aqua"></i> 5 new members
                          joined today
                        </a>
                      </li>
                      <!-- end notification -->
                    </ul>
                  </li>
                  <li class="footer"><a href="#">View all</a></li>
                </ul>
              </li>
              <li><a href="#">Hi Administrator </a></li>
              <li><a href="#">Log out </a></li>
            </ul>
          </div>
          <!-- /.navbar-custom-menu -->
        </div>
        <!-- /.container-fluid -->
      </nav>
    </header>
    <!-- Full Width Column -->
    <div class="content-wrapper">
      <div class="container-fluid">
        <!-- Content Header (Page header) -->
        <section class="content-header">
          <h1>Statistic Dashboard <small>ver 0.1</small></h1>
          <ol class="breadcrumb">
            <li>
              <a href="#"><i class="fa fa-dashboard"></i> Home</a>
            </li>
            <li><a href="#">Dashboard</a></li>
          </ol>
        </section>

        <!-- Main content -->
        <section class="content">

          {{-- Infobox --}}
          <!-- Info boxes -->
          <div class="row">
            <div class="col-md-3 col-sm-6 col-xs-12">
              <div class="info-box">
                <span class="info-box-icon bg-aqua"><i class="ion ion-ios-gear-outline"></i></span>

                <div class="info-box-content">
                  <span class="info-box-text">CPU Usage </span>
                  <span class="info-box-number"> <span id="cpu-usage"> 0 </span> <small>%</small></span>
                </div>
                <!-- /.info-box-content -->
              </div>
              <!-- /.info-box -->
            </div>
            <!-- /.col -->
            <div class="col-md-3 col-sm-6 col-xs-12">
              <div class="info-box">
                <span class="info-box-icon bg-red"><i class="fa fa-microchip"></i></span>

                <div class="info-box-content">
                  <span class="info-box-text">RAM Usage</span>
                  <span class="info-box-number"><span id="ram-usage"> 0 </span><small>%</small></span>
                </div>
                <!-- /.info-box-content -->
              </div>
              <!-- /.info-box -->
            </div>
            <!-- /.col -->

            <!-- fix for small devices only -->
            <div class="clearfix visible-sm-block"></div>

            <div class="col-md-3 col-sm-6 col-xs-12">
              <div class="info-box">
                <span class="info-box-icon bg-green"><i class="fa fa-clock-o"></i></span>

                <div class="info-box-content">
                  <span class="info-box-text">Up Time</span>
                  <span class="info-box-number"><span id="time"> 0 </span></span>
                </div>
                <!-- /.info-box-content -->
              </div>
              <!-- /.info-box -->
            </div>
            <!-- /.col -->
            <div class="col-md-3 col-sm-6 col-xs-12">
              <div class="info-box">
                <span class="info-box-icon bg-yellow"><i class="fa fa-mixcloud"></i></span>

                <div class="info-box-content">
                  <span class="info-box-text">Connections</span>
                  <span class="info-box-number"><span id="connections"> 0 </span></span>
                </div>
                <!-- /.info-box-content -->
              </div>
              <!-- /.info-box -->
            </div>
            <!-- /.col -->
          </div>


          {{-- Body --}}
          <div class="row">
            <!-- Thermostats -->
            <div class="col-md-6">
              <div class="box">
                <div class="box-header">
                  <h3 class="box-title">Thermostats</h3>
                  <button type="button" class="btn btn-success btn-sm pull-right" data-toggle="modal" data-target="#add-thermostat-modal">
                    + Add thermostats
                  </button>
                </div>
                <!-- /.box-header -->
                <div class="box-body no-padding">
                  <table class="table table-striped">
                    <tr>
                      <th style="width: 10px">#</th>
                      <th>Themostat</th>
                      <th>Ip Address : Port</th>
                      <th>Temperature</th>
                      <th style="width: 140px" class="text-center">Action</th>
                    </tr>
                    <tr>
                      <td>1.</td>
                      <td>PHP Department</td>
                      <td>192.168.100.13 : 85624</td>
                      <td>27 C</td>
                      <td class="text-center">
                        <button type="button" class="btn btn-success btn-sm">
                          +
                        </button>
                        <button type="button" class="btn btn-danger btn-sm">
                          -
                        </button>
                      </td>
                    </tr>
                  </table>
                </div>
                <!-- /.box-body -->
              </div>
              <!-- /.box -->
            </div>

            <!-- Mobile Phones -->
            <div class="col-md-6">
              <div class="box">
                <div class="box-header">
                  <h3 class="box-title">Devices</h3>
                </div>
                <!-- /.box-header -->
                <div class="box-body no-padding">
                  <table class="table table-striped">
                    <tr>
                      <th style="width: 10px">#</th>
                      <th>Device</th>
                      <th>Ip Address</th>
                      <th style="width: 140px">Time</th>
                    </tr>
                    <tr>
                      <td>1.</td>
                      <td>Huawei P10</td>
                      <td>192.168.100.15</td>
                      <td>13:17 | 22 Nov</td>
                    </tr>
                  </table>
                </div>
                <!-- /.box-body -->
              </div>
              <!-- /.box -->
            </div>
          </div>
          <hr />
          <div class="row">
            <!-- Thermostats -->
            <div class="col-md-12">
              <div class="box">
                <div class="box-header">
                  <h3 class="box-title">Communication Log</h3>
                </div>
                <!-- /.box-header -->
                <div class="box-body">
                  <table class="table table-striped" id="communication-log">
                    <thead>
                      <tr>
                        <th style="width: 10px">#</th>
                        <th>Address</th>
                        <th>Data</th>
                        <th>Bytes</th>
                        <th>Message</th>
                        <th style="width: 140px">Date</th>
                        <th style="width: 140px">Action</th>
                      </tr>
                    </thead>
                    <tbody>
                      @foreach ($communication_logs as $communication_log)
                      <tr>
                        <td>{{$communication_log->id}}</td>
                        <td>{{$communication_log->address}}</td>
                        <td>{{$communication_log->data}}</td>
                        <td>{{$communication_log->bytes}}</td>
                        <td>{{$communication_log->message}}</td>
                        <td>{{$communication_log->created_at->format('H:i:s M d Y')}}</td>
                        <td>

                          <div class="btn-group">
                            <button class="btn btn-default show btn-sm" data-id="{{$communication_log->id}}" data-address="{{$communication_log->address}}">
                              <i class="fa fa-file-code-o"></i> View
                            </button>
                            <button class="btn btn-danger delete btn-sm" data-id="{{$communication_log->id}}" data-address="{{$communication_log->address}}">
                                <i class="fa fa-trash-o"></i> Delete
                            </button>

                          </div>
                        </td>
                      </tr>
                      @endforeach
                    </tbody>
                    <tfoot>
                      <tr>
                        <th style="width: 10px">#</th>
                        <th>Address</th>
                        <th>Data</th>
                        <th>Bytes</th>
                        <th>Message</th>
                        <th style="width: 140px">Date</th>
                        <th style="width: 250px">
                          Action
                        </th>
                      </tr>
                    </tfoot>
                  </table>
                </div>
                <!-- /.box-body -->
              </div>
              <!-- /.box -->
            </div>
          </div>
        </section>
        <!-- /.content -->
      </div>
      <!-- /.container -->
    </div>
    <!-- /.content-wrapper -->
    <footer class="main-footer">
      <div class="container">
        <div class="pull-right hidden-xs"><b>Version</b> 0.1.0</div>
        <strong>Copyright &copy; 2018-11
          <a href="https://www.sentice.com">Sentice</a>.</strong>
        All rights reserved.
      </div>
      <!-- /.container -->
    </footer>
  </div>
  <!-- ./wrapper -->

  <!-- modals -->

  <!-- Show modal -->
  <div class="modal modal-default fade" id="show-modal">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span></button>
          <h4 class="modal-title">Packet details</h4>
        </div>
        <div class="modal-body">
          <p>One fine body&hellip;</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline pull-left" data-dismiss="modal">Close</button>
        </div>
      </div>
      <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
  </div>

  <!-- Delete Modal -->
  <div class="modal modal-default fade" id="delete-modal">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span></button>
          <h4 class="modal-title">Delete Packet</h4>
        </div>
        <div class="modal-body">
          <form role="form">

            <!-- radio -->
            <div class="form-group">

              <!-- delete single record -->
              <div class="radio">
                <label>
                  <input type="radio" name="delete-record" id="d-single" value="1" checked>
                  Delete this entry
                </label>
              </div>

              <!-- delete all entry -->
              <div class="radio">
                <label>
                  <input type="radio" name="delete-record" id="d-all" value="2">
                  Delete all entry for this address: <span id="packet-adddress"></span>
                </label>
              </div>

            </div>
          </form>

        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-danger pull-right modal-btn-delete ml-xs">Delete</button>
          <button type="button" class="btn btn-default pull-right" data-dismiss="modal">Close</button>
        </div>
      </div>
      <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
  </div>


  <!-- /modals -->

  <!-- jQuery 3 -->
  <script src="_raw/admin-lte/bower_components/jquery/dist/jquery.min.js"></script>
  <!-- Bootstrap 3.3.7 -->
  <script src="_raw/admin-lte/bower_components/bootstrap/dist/js/bootstrap.min.js"></script>
  <!-- SlimScroll -->
  <script src="_raw/admin-lte/bower_components/jquery-slimscroll/jquery.slimscroll.min.js"></script>
  <!-- FastClick -->
  <script src="_raw/admin-lte/bower_components/fastclick/lib/fastclick.js"></script>
  <!-- AdminLTE App -->
  <script src="_raw/admin-lte/dist/js/adminlte.min.js"></script>

  <!-- DataTables -->
  <script src="_raw/admin-lte/bower_components/datatables.net/js/jquery.dataTables.min.js"></script>
  <script src="_raw/admin-lte/bower_components/datatables.net-bs/js/dataTables.bootstrap.min.js"></script>

  <!-- AdminLTE for demo purposes -->
  <script src="_raw/admin-lte/dist/js/demo.js"></script>

  <!-- Select2 -->
  <script src="_raw/admin-lte/bower_components/select2/dist/js/select2.full.min.js"></script>

  <!-- animsition  -->
  <!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/animsition/4.0.2/js/animsition.js"></script> -->

  <script type='text/javascript'>
    $(document).ready(function () {

      var data = {};

      //Generate Datatable
      $("#communication-log").DataTable({
        "order": [
          [0, "desc"]
        ],
        initComplete: function () {
          this.api().columns().every(function (index) {



            //Skip columns filter
            if (index != 2 && index != 1) {
              return false;
            }

            console.log("[pass]=", index)
            var column = this;
            var select = $(
                '<select class="form-control" ><option value="">Filter by</option></select>'
              )
              .appendTo($(column.footer()).empty())
              .on('change', function () {
                var val = $.fn.dataTable.util.escapeRegex(
                  $(this).val()
                );

                column
                  .search(val ? '^' + val + '$' : '', true, false)
                  .draw();
              });

            column.data().unique().sort().each(function (d, j) {
              select.append('<option value="' + d + '">' + d + '</option>')
            });
          });

          // $('.select2').select2({
          //   width: 'resolve',
          //   dropdownAutoWidth: true,
          // });
        }
      });

      //get system info
      system_info()


      //Show modal
      $(".show").on("click", function () {
        data.id = $(this).data('id');
        data.address = $(this).data('address');

        $("#show-modal").modal();
      })


      $(".delete").on("click", function () {

        data.id = $(this).data('id');
        data.address = $(this).data('address');

        $("#delete-modal").modal();
      })



      //refresh the system info details
      setTimeout(function () {
        system_info();
      }, 3000);


      //not used
      function get_form() {
        var result = {};
        $.each($("#add-thermostat-form").serializeArray(), function () {
          result[this.name] = this.value;
        });

        return result;
      }

      //get system info
      function system_info() {
        $.ajax({
          url: "{{route('sys-info')}}",
          success: function (result) {
            $("#cpu-usage").text(parseFloat(result.info.cpu.usage_percentage).toFixed(1))
            $("#ram-usage").text(parseFloat(result.info.ram.usage_percentage).toFixed(1))
            $("#time").text(parseFloat(result.info.ram.usage_percentage).toFixed(1))
            $("#connections").text(result.num_of_connections)
          }
        });
      }
    });
  </script>

  <!-- modal -->
  <div class="modal fade" id="add-thermostat-modal">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header bg-primary">
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
          <h4 class="modal-title text-center">Add Thermostat</h4>
        </div>
        <div class="modal-body">
          <!-- general form elements -->

          <!-- form start -->

          <form id="add-thermostat-form">
            <div class="box-body">
              <!-- tecnical details -->
              <div class="col-md-6" style="border-right: 1px solid #eee;">
                <h4 class="text-center">
                  <i class="fa fa-wrench"></i> Tecnical Details
                </h4>
                <hr />
                <div class="form-group">
                  <label for="ssid">SSID</label>
                  <input type="text" class="form-control" id="ssid" name="ssid" placeholder="Enter ssid" />
                </div>

                <div class="form-group">
                  <label for="ssid-password">SSID password</label>
                  <input type="text" class="form-control" id="ssid-password" name="ssid-password" placeholder="Enter ssid password" />
                </div>

                <div class="form-group">
                  <label for="socket-server-ip">Socket Server IP</label>
                  <input type="text" class="form-control" id="socket-server-ip" name="socket-server-ip" placeholder="Example:192.168.100.52" />
                </div>

                <div class="form-group">
                  <label for="socket-server-port">Socket server port</label>
                  <input type="text" class="form-control" id="socket-server-port" name="socket-server-port" placeholder="Example:7433" />
                </div>
              </div>

              <!-- room details -->
              <div class="col-md-6">
                <h4 class="text-center">
                  <i class="fa fa-home"></i> Zone Details
                </h4>
                <hr />

                <div class="form-group">
                  <label for="home-id">Home id</label>
                  <input type="text" class="form-control" id="home-id" name="home-id" placeholder="Enter Home Id" />
                </div>

                <div class="form-group">
                  <label for="ssid">Room Name</label>
                  <input type="text" class="form-control" id="room-name" name="room-name" placeholder="Room name" />
                </div>

                <div class="form-group">
                  <label>Sensor mode</label>
                  <select class="form-control">
                    <option value="1">Room Sensor</option>
                    <option value="2">Room S. & Floor Limit</option>
                    <option value="3">Floor Sensor Only</option>
                    <option value="4">Bathroom mode</option>
                  </select>
                </div>

                <div class="form-group">
                  <label>Data type</label>
                  <select class="form-control">
                    <option value="1">Max temp</option>
                    <option value="2">Min temp</option>
                    <option value="3">Offset</option>
                    <option value="4">Temp unit</option>
                    <option value="5">Repaly operation limit</option>
                    <option value="6">Sensitivity</option>
                    <option value="7">Differential</option>
                  </select>
                </div>
              </div>

              <!-- /.box-body -->
            </div>
          </form>
          <div class="modal-footer">
            <button type="button" class="btn bg-olive btn-block" id="add-thermostat">
              Save
            </button>
          </div>
        </div>
        <!-- /.modal-content -->
      </div>
      <!-- /.modal-dialog -->
    </div>
  </div>
</body>

</html>