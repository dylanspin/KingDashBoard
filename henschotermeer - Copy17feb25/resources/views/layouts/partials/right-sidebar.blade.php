<div class="right-sidebar">
    <div class="slimscrollright">
        <div class="rpanel-title"> @lang('right-sidebar.actions') <span><i class="icon-close right-side-toggler"></i></span></div>
        @if(auth()->check() && !auth()->user()->hasRole('promo'))
        <div class="r-panel-body pb-0 hidden-lg hidden-md">
            <ul class="layouts">
                <li>
                    <a 
                        href="{{route('dashboard')}}" 
                        class="btn btn-primary{{ Request::is('dashboard')? ' device_heading': '' }}" 
                        style="padding:6px 32px;">Vehicle Dashboard</a>
                </li>
                <li>
                    <a 
                        href="{{route('person_dashboard')}}" 
                        class="btn btn-primary{{ Request::is('dashboard/person')? ' device_heading': '' }}" 
                        style="padding:6px 32px;">Person Dashboard</a>
                </li>
            </ul>
        </div>
        @endif
        <div class="r-panel-body pb-0">
            <ul class="layouts">
                <li class="dropdown">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                        <!--<i class="fa fa-language"></i>-->
                        <img src="{{asset('/plugins/images/icons/language.png')}}" 
                             alt="user-img"
                             class="img"
                             style="max-width:35px;padding:0px;"> {{strtoupper(\App::getLocale())}} <span class="caret"></span>
                    </a>
                    <ul class="dropdown-menu">
                        <li>
                            <a class="pt-0 pb-0" href="{{asset('change-language/en')}}">EN</a>
                        </li>
                        <li>
                            <a class="pt-0 pb-0" href="{{asset('change-language/nl')}}">NL</a>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
        @if(auth()->check())
        <div class="r-panel-body pt-0">
            <ul class="layouts">
                <!--<li><a href="{{url('profile')}}"><i class="fa fa-user"></i> Profile</a></li>-->
                <li>
                    <a href="{{'account-settings'}}">
                        <!--<i class="fa fa-cog"></i>--> 
                        <img src="{{asset('/plugins/images/icons/setting.png')}}" 
                             alt="user-img"
                             class="img"
                             style="max-width:35px;padding:0px;"> @lang('right-sidebar.account_settings')
                    </a>
                </li>
                <li><a href="{{route('logout')}}"><i class="fa fa-power-off"></i> @lang('right-sidebar.logout')</a></li>
            </ul>
        </div>
        <!--        <div class="text-center">
                    <a class="btn btn-primary m-t-10" href="{{route('logout')}}">Logout</a>
                </div>-->
        @else
        
        @endif
    </div>
</div>