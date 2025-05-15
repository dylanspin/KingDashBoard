<?php session()->put('theme-layout', 'mini-sidebar'); ?> 
<script>
    var lang_locale = '<?php echo \App::getLocale(); ?>';
</script>
<nav class="navbar navbar-default navbar-static-top m-b-0  background-parkingshop">
    <div class="navbar-header background-parkingshop">
        <a class="navbar-toggle font-20   background-parkingshop  hidden-sm hidden-md hidden-lg " href="javascript:void(0)" data-toggle="collapse"
           data-target=".navbar-collapse">
            <i class="fa fa-bars"></i>
        </a>
        <div class="top-left-part  background-parkingshop">
            @if(auth()->check())
            <a class="logo" href="{{'/dashboard'}}">
                <b>
                    <img src="{{asset('plugins/images/logo_2.png')}}" alt="home"/>
                </b>
                <span>
                    PARKINGSHOP
                </span>
            </a>
            @else
            <a class="logo" href="{{'/'}}">
                <b>
                    <img src="{{asset('plugins/images/logo_2.png')}}" alt="home"/>
                </b>
                <span>
                    PARKINGSHOP
                </span>
            </a>
            @endif

        </div>
        <ul class="nav navbar-top-links navbar-left hidden-xs">
            @if(session()->get('theme-layout') != 'fix-header' && auth()->check())
            <li class="sidebar-toggle">
                <a href="javascript:void(0)" class="sidebartoggler font-20 waves-effect waves-light"><i class="icon-arrow-left-circle"></i></a>
            </li>
            @endif

            <!--            <li>
                            <form role="search" class="app-search hidden-xs">
                                <i class="icon-magnifier"></i>
                                <input type="text" placeholder="Search..." class="form-control">
                            </form>
                        </li>-->
        </ul>
        
        <div class="location_server_text">LOCATION SERVER</div>

        <ul class="nav navbar-top-links navbar-right pull-right">
            @if(auth()->check())
            @if(!auth()->user()->hasRole('promo'))
            <li class="hidden-sm hidden-xs">
                <a  class="btn btn-primary{{ Request::is('dashboard')? ' device_heading': '' }}" href="{{route('dashboard')}}" >
                    Vehicle Dashboard
                </a>
            </li>
            <li class="hidden-sm hidden-xs">
                <a  class="btn btn-primary{{ Request::is('dashboard/person')? ' device_heading': '' }}" href="{{route('person_dashboard')}}" >
                    Person Dashboard
                </a>
            </li>
            @endif
            <li class="right-side-toggle">
                <a class="right-side-toggler waves-effect waves-light b-r-0 font-20" href="javascript:void(0)">
                    <i class="icon-settings"></i>
                </a>
            </li>
            @else
            <li class="">
                <a  class="waves-effect waves-light b-r-0 font-20" href="{{'/login'}}" >
                    <i class="icon-user"></i>
                </a>
            </li>
            @endif

        </ul>
    </div>
</nav>
