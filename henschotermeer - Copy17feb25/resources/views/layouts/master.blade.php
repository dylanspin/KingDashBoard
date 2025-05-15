<!DOCTYPE html>
<html lang="en">

    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="keywords" content="">
        <meta name="description" content="">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="author" content="">
        <link rel="icon" type="image/png" sizes="16x16" href="{{asset('plugins/images/favicon.ico')}}">
        <title>ParkingShop</title>
        <!-- ===== Bootstrap CSS ===== -->
        <link href="{{asset('bootstrap/dist/css/bootstrap.min.css')}}" rel="stylesheet">
        <!-- ===== Plugin CSS ===== -->
        <link href="{{asset('plugins/components/chartist-js/dist/chartist.min.css')}}" rel="stylesheet">
        <link href="{{asset('plugins/components/chartist-plugin-tooltip-master/dist/chartist-plugin-tooltip.css')}}"
              rel="stylesheet">
        <link href="{{asset('plugins/components/toast-master/css/jquery.toast.css')}}" rel="stylesheet">
        <link href="{{asset('plugins/components/bootstrap-datepicker/bootstrap-datepicker.min.css')}}">
        <link href="{{asset('plugins/components/bootstrap-daterangepicker/daterangepicker.css')}}" rel="stylesheet">

        <!-- ===== Animation CSS ===== -->
        <link href="{{asset('css/animate.css')}}" rel="stylesheet">
        <!-- ===== Custom CSS ===== -->
        <link href="{{asset('css/common.css')}}" rel="stylesheet">
        <!--====== Dynamic theme changing =====-->

        @if(session()->get('theme-layout') == 'fix-header')
        <link href="{{asset('css/style-fix-header.css')}}" rel="stylesheet">
        <link href="{{asset('css/colors/default-dark.css')}}" id="theme" rel="stylesheet">

        @elseif(session()->get('theme-layout') == 'mini-sidebar')
        <link href="{{asset('css/style-mini-sidebar.css')}}" rel="stylesheet">
        <link href="{{asset('css/colors/default-dark.css')}}" id="theme" rel="stylesheet">
        @else
        <link href="{{asset('css/style-normal.css')}}" rel="stylesheet">
        <link href="{{asset('css/colors/default-dark.css')}}" id="theme" rel="stylesheet">
        @endif

        @stack('css')

        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-iconpicker/1.9.0/css/bootstrap-iconpicker.min.css"/>


        <!-- ===== Color CSS ===== -->
        <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
        <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
        <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
        <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
        <![endif]-->
        <style>
            tbody > tr > th{
                min-width: 0px!important;
                width: 0px!important;
                padding: 0px!important;
            }
            .sidebar-nav ul#side-menu li a{
                padding: 10px 15px 10px 15px;
            }
            .sidebar-nav ul#side-menu li ul{
                padding-left: 75px;
            }
            .sidebar-nav ul#side-menu li ul li a{
                padding: 8px 5px 8px 5px;
            }
            tr.footable-filtering div.footable-filtering-search button.dropdown-toggle{
                display: none;
            }
            .filter {
                -webkit-filter: brightness(0) invert(1);
                filter: brightness(0) invert(1);
            }
            .dataTables_wrapper .dataTables_filter input {
                border: 1px solid gainsboro;
            }
            table.dataTable thead .sorting:after {
                content: none;
            }
            table.dataTable thead .sorting_asc:after {
                content: none;
            }
            table.dataTable thead .sorting_desc:after {
                content: none;
            }
            table > tbody > tr > td > span.footable-toggle {
                cursor: pointer;
            }
            .location_server_text{
                color: white;
                font-size: 18px;
                margin-top: 17px;
                width: auto; 
                float: left;
            }
            @media (min-width: 768px) {
                .extra.collapse li a span.hide-menu {
                    display: block !important;
                }

                .extra.collapse.in li a.waves-effect span.hide-menu {
                    display: block !important;
                }

                .extra.collapse li.active a.active span.hide-menu {
                    display: block !important;
                }

                ul.side-menu li:hover + .extra.collapse.in li.active a.active span.hide-menu {
                    display: block !important;
                }

                .mini-sidebar .sidebar-nav #side-menu>li>a {
                    padding: 5px;
                }
            }
            .has-sub ul li a{
                padding: 2px 0px !important;
            }
        </style>
        <link href="{{asset('css/utility.css')}}" rel="stylesheet">
    </head>
    <body class="@if(session()->get('theme-layout')) {{session()->get('theme-layout')}} @endif">
        <!-- ===== Main-Wrapper ===== -->
        <div id="wrapper">
            <div class="preloader" style="opacity:0.6">
                <div class="cssload-speeding-wheel"></div>
            </div>
            <!-- ===== Top-Navigation ===== -->
            @include('layouts.partials.navbar')
            <!-- ===== Top-Navigation-End ===== -->

            <!-- ===== Left-Sidebar ===== -->
            @include('layouts.partials.sidebar')
            @include('layouts.partials.right-sidebar')

            <!-- ===== Left-Sidebar-End ===== -->
            <!-- ===== Page-Content ===== -->
            <div class="page-wrapper">
                @yield('content')
                <footer class="footer t-a-c">
                    &copy; <?php echo date('Y') ?> Parkingshop
                </footer>
            </div>
            <!-- ===== Page-Content-End ===== -->
                        </div>
        <!-- ===== Main-Wrapper-End ===== -->
        <!-- ==============================
            Required JS Files
        =============================== -->
        <!-- ===== jQuery ===== -->
        <script src="{{asset('plugins/components/jquery/dist/jquery.min.js')}}"></script>
        <!-- ===== Bootstrap JavaScript ===== -->
        <script src="{{asset('bootstrap/dist/js/bootstrap.min.js')}}"></script>
        <!-- ===== Slimscroll JavaScript ===== -->
        <script src="{{asset('js/jquery.slimscroll.js')}}"></script>
        <!-- ===== Wave Effects JavaScript ===== -->
        <script src="{{asset('js/waves.js')}}"></script>
        <!-- ===== Menu Plugin JavaScript ===== -->
        <script src="{{asset('js/sidebarmenu.js')}}"></script>
        <!-- ===== Custom JavaScript ===== -->

        @if(session()->get('theme-layout') == 'fix-header')
        <script src="{{asset('js/custom-fix-header.js')}}"></script>
        @elseif(session()->get('theme-layout') == 'mini-sidebar')
        <script src="{{asset('js/custom-mini-sidebar.js')}}"></script>
        @else
        <script src="{{asset('js/custom-normal.js')}}"></script>
        @endif

<!--{{--<script src="{{asset('js/custom.js')}}"></script>--}}-->
        <!-- ===== Plugin JS ===== -->
        <script src="{{asset('plugins/components/chartist-js/dist/chartist.min.js')}}"></script>
        <script src="{{asset('plugins/components/chartist-plugin-tooltip-master/dist/chartist-plugin-tooltip.min.js')}}"></script>
        <script src="{{asset('plugins/components/sparkline/jquery.sparkline.min.js')}}"></script>
        <script src="{{asset('plugins/components/sparkline/jquery.charts-sparkline.js')}}"></script>
        <script src="{{asset('plugins/components/knob/jquery.knob.js')}}"></script>
        <script src="{{asset('plugins/components/moment/moment.js')}}"></script>
        <script src="{{asset('plugins/components/easypiechart/dist/jquery.easypiechart.min.js')}}"></script>
        <!-- ===== Style Switcher JS ===== -->
        <script src="{{asset('plugins/components/styleswitcher/jQuery.style.switcher.js')}}"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-iconpicker/1.9.0/js/bootstrap-iconpicker-iconset-all.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-iconpicker/1.9.0/js/bootstrap-iconpicker.min.js"></script>
        <script src="{{asset('plugins/components/bootstrap-daterangepicker/daterangepicker.js')}}"></script>
        <script src="{{asset('plugins/components/bootstrap-datepicker/bootstrap-datepicker.min.js')}}"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.7.14/js/bootstrap-datetimepicker.min.js"></script>
        <script src="{{ asset('/js/default.js') }}"></script>
		<script>
        $(document).ready(function(){
           
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $(".ReservationButton").click(function(){
                
                var id = $("#invisible").val();
                var name = $("#firstName").val();
                var plate = $("#LicensePlate").val();
                var check_in = $("#datetime1").val();
                var check_out = $("#datetime2").val();
                // alert(data);
                $.ajax({
                    /* the route pointing to the post function */
                    url: 'store_reservation_info',
                    type: 'POST',
                    /* send the csrf-token and the input to the controller */
                    data:{id:id, name:name, plate:plate, check_in:check_in,check_out:check_out},
                    /* remind that 'data' is the response of the AjaxController */
                    success: function(data) {
                        
                        console.log(data);
                        if(!data.errors){
                            $('.sucess').removeClass('hidden');
                             $('.sucess').append(data.success);
                             location.reload();
                        }else{
                            printErrorMsg(data.errors);
                        }
                    }   
                }); 
                $(".alert-success").fadeTo(2000, 500).slideUp(500, function(){
                    $(".alert-success").slideUp(500);
                });
                
            });
            function printErrorMsg (msg) {
                    $(".print-error-msg").find("ul").html('');
                    $(".print-error-msg").css('display','block');
                    $.each( msg, function( key, value ) {
                        $(".print-error-msg").find("ul").append('<li>'+value+'</li>');
                    });
                }
       });   



 
    </script>
<script>


$(document).ready(function(){
          
			
           $.ajaxSetup({
               headers: {
                   'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
               }
           });
           $(".EditButton").click(function(){
            var booking_id = $(this).data('id');
               
               $.ajax({
                   /* the route pointing to the post function */
                   url: '{{ url('editreservation')}}/'+booking_id,
                   type: 'GET',
                   /* send the csrf-token and the input to the controller */
                   /* remind that 'data' is the response of the AjaxController */
                   success: function (data) {
                       //console.log(data);
                       $('#invisible').val(data.id);
                       $('#firstName').val(data.first_name);
                       $('#LicensePlate').val(data.vehicle_num);
					    var formattedDate = new Date(data.checkin_time);
						var d = formattedDate.getDate();
						var m =  formattedDate.getMonth();
						m += 1;  // JavaScript months are 0-11
						var Y = formattedDate.getFullYear();
						
                       $('#datetime1').val(d + "-" + m + "-" + Y);
					   var formattedDatee = new Date(data.checkout_time);
						var d1 = formattedDatee.getDate();
						var m1 =  formattedDatee.getMonth();
						m1 += 1;  // JavaScript months are 0-11
						var Y1 = formattedDate.getFullYear();
					    
                       $('#datetime2').val(d1 + "-" + m1 + "-" + Y1);
                    //    $('.sucess').removeClass("hidden"); 
                    //    $('.sucess').html(data.success);  
                   }
               }); 
			   
           });
      }); 

    </script>
				<script type="text/javascript">
         $(function () {

    $('#datetimepicker1').datetimepicker({
                 format: 'DD-MM-YYYY'
           });
		    
       $('#datetimepicker2').datetimepicker({
		   format: 'DD-MM-YYYY',
   useCurrent: false //Important! See issue #1075
   });
       $("#datetimepicker1").on("dp.change", function (e) {
           $('#datetimepicker2').data("DateTimePicker").minDate(e.date);
       });
       $("#datetimepicker2").on("dp.change", function (e) {
           $('#datetimepicker1').data("DateTimePicker").maxDate(e.date);
       });
	  
 });




      </script>
        @include('layouts.partials.script-lang')
        @if(\Illuminate\Support\Facades\Session::has('nav-scroll-session'))
        <script type="text/javascript">
            var timer_toggle_dashboard = null;

            var beeps_count = 1;
            $(document).ready(function () {
                timer_toggle_dashboard = setInterval(function () {
                    $('.sidebartoggler').click();
                    clearInterval(timer_toggle_dashboard);
                }, 50);
            });
        </script>
        @endif
        <script type="text/javascript">
            var operator_sound_timer = null;
            function playAudio() {
                var audio = new Audio();
                audio.src = '/plugins/beep.mp3';
                // when the sound has been loaded, execute your code
                audio.oncanplaythrough = (event) => {
                    var playedPromise = audio.play();
                    if (playedPromise) {
                        playedPromise.catch((e) => {
                            console.log(e)
                            if (e.name === 'NotAllowedError' || e.name === 'NotSupportedError') {
                                console.log(e.name);
                                beeps_count--;
                            }
                        }).then(() => {
                            beeps_count++;
                        });
                    }
                }

            }

            
			
			
			



           
			
			
        </script>

        @stack('js')
    </body>

</html>