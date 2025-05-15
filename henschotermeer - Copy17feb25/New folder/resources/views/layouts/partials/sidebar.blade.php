<aside class="sidebar">
    <div class="scroll-sidebar">
	
        @if(auth()->check())
        @if(session()->get('theme-layout') != 'fix-header')
        <div class="user-profile">
            <div class="dropdown user-pro-body ">
                <div class="profile-image">
                    @if(auth()->user()->profile->pic == null)
                    <img src="{{asset('/plugins/images/icons/User_Active.png')}}" 
                         alt="user-img"
                         class="img filter"
                         style="padding:0px;">
                    @else
                    <img src="{{asset('/uploads/users/'.auth()->user()->profile->pic)}}"
                         alt="user-img" 
                         class="img img-circle"
                         style="padding:0px;">
                    @endif
                    <a href="javascript:void(0);" class="dropdown-toggle u-dropdown text-blue"
                       data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
                        <span class="badge badge-danger">
                            <i class="fa fa-angle-down"></i>
                        </span>
                    </a>
                    <ul class="dropdown-menu animated flipInY">
                        <li><a href="{{'account-settings'}}"><i class="fa fa-cog"></i> @lang('sidebar.account_settings')</a></li>
                        <li role="separator" class="divider"></li>
                        <li><a href="{{route('logout')}}"><i class="fa fa-power-off"></i> @lang('sidebar.logout')</a></li>
                    </ul>
                </div>
                <p class="profile-text m-t-15 font-16"><a
                        href="javascript:void(0);"> {{auth()->user()->name}}</a></p>
            </div>
        </div>
        @endif
        <nav class="sidebar-nav">
            <ul id="side-menu">
                @if(auth()->user()->isAdmin() == true)
					
                <li class="two-column">
                    <a class="waves-effect" href="javascript:void(0);" aria-expanded="false">
                        <!--<i class="fa fa-list-ol fa-fw"></i>--> 
                        <img src="{{asset('/plugins/images/icons/List.png')}}" 
                             alt="user-img"
                             class="img filter"
                             style="max-width:45px;margin-right:5px;padding:0px;">
                        <span class="hide-menu"> @lang('sidebar.white_list')</span>
                    </a>
                    <ul aria-expanded="false" class="collapse">
                        <li><a href="{{asset('white-list')}}">@lang('sidebar.manage_user')</a></li>
                        <li><a href="{{asset('white-list/create')}}">@lang('sidebar.add_new_user')</a></li>

                    </ul>
                </li>
                <li class="two-column">
                    <a class="waves-effect" href="javascript:void(0);" aria-expanded="false">
                        <img src="{{asset('/plugins/images/icons/User_List_1.png')}}" 
                             alt="user-img"
                             class="img filter"
                             style="max-width:45px;margin-right:5px;padding:0px;">
                        <span class="hide-menu"> @lang('sidebar.user_list')</span>
                    </a>
                    <ul aria-expanded="false" class="collapse">
                        <li><a href="{{asset('user-list')}}">@lang('sidebar.manage_user')</a></li>
                        <li><a href="{{asset('user-list/create')}}">@lang('sidebar.add_new_user')</a></li>

                    </ul>
                </li>
                <li class="two-column">
                    <a class="waves-effect" href="javascript:void(0);" aria-expanded="false">
                        <!--<i class="fa fa-barcode fa-fw"></i>--> 
                        <img src="{{asset('/plugins/images/icons/barcode.png')}}" 
                             alt="user-img"
                             class="img filter"
                             style="max-width:45px;margin-right:5px;padding:0px;">
                        <span class="hide-menu"> @lang('sidebar.barcodes')</span>
                    </a>
                    <ul aria-expanded="false" class="collapse">
                        <li><a href="{{asset('barcode')}}">@lang('sidebar.manage_barcodes_parking')</a></li>
                        <li><a href="{{asset('barcode/create')}}">@lang('sidebar.add_new_barcode_parking')</a></li>
                        <li><a href="{{asset('person/barcode')}}">@lang('sidebar.manage_barcodes_person')</a></li>
                        <li><a href="{{asset('person/barcode/create')}}">@lang('sidebar.add_new_barcode_person')</a></li>
                        <li><a href="{{asset('barcode/fail-safe/person')}}">@lang('sidebar.fail_safe_person')</a></li>
                        <li><a href="{{asset('barcode/fail-safe/parking')}}">@lang('sidebar.fail_safe_parking')</a></li>
                    </ul>
                </li>
                <li class="two-column">
                    <a class="waves-effect" href="javascript:void(0);" aria-expanded="false">
                        <!--<i class="fa fa-book fa-fw"></i>--> 
                        <img src="{{asset('/plugins/images/icons/Reservation_03.png')}}" 
                             alt="user-img"
                             class="img filter"
                             style="max-width:45px;margin-right:5px;padding:0px;">
                        <span class="hide-menu"> @lang('sidebar.tommy_reservation')</span>
                    </a>
                    <ul aria-expanded="false" class="collapse">
                        <li><a href="{{asset('tommy-reservations')}}">@lang('sidebar.manage_reservations')</a></li>
                        <li><a href="{{asset('tommy-reservations/import')}}">@lang('sidebar.import_reservation')</a></li>
                        <li><a href="{{asset('tommy-reservations/bookings')}}">@lang('sidebar.bookings')</a></li>
						<li><a href="{{asset('manual-reservation')}}">@lang('sidebar.add_reservation')</a></li>
                        <li><a href="{{asset('person-manual-reservation')}}">@lang('sidebar.manual_reservation_person')</a></li>
                    </ul>
                </li>
                <li>
                    <a class="waves-effect" href="{{ url('/send-ticket') }}">
                        <!--<i class="fa fa-ticket fa-fw"></i>-->
                        <img src="{{asset('/plugins/images/icons/invite_send.png')}}" 
                             alt="user-img"
                             class="img filter"
                             style="max-width:45px;margin-right:5px;padding:0px;">
                        <span class="hide-menu"> @lang('sidebar.send_ticket')</span>
                    </a>
                </li>
                <li>
                    <a class="waves-effect" href="{{ url('location/attendants') }}">
                        <!--<i class="fa fa-list-ol fa-fw"></i>-->
                        <img src="{{asset('/plugins/images/icons/arrivals.png')}}" 
                             alt="user-img"
                             class="img filter"
                             style="max-width:45px;margin-right:5px;padding:0px;">
                        <span class="hide-menu"> @lang('sidebar.attendants')</span>
                    </a>
                </li>
                <li>
                    <a class="waves-effect" href="{{ url('/transaction_details') }}">
                        <!--<i class="fa fa-list-ol fa-fw"></i>-->
                        <img src="{{asset('/plugins/images/icons/transactions.png')}}" 
                             alt="user-img"
                             class="img filter"
                             style="max-width:45px;margin-right:5px;padding:0px;">
                        <span class="hide-menu"> @lang('sidebar.all_transactions')</span>
                    </a>
                </li>
                <li>
                    <a class="waves-effect" href="{{ url('location/reviews') }}">
                        <!--<i class="fa fa-clipboard fa-fw"></i>-->
                        <img src="{{asset('/plugins/images/icons/reviews.png')}}" 
                             alt="user-img"
                             class="img filter"
                             style="max-width:45px;margin-right:5px;padding:0px;">
                        <span class="hide-menu"> @lang('sidebar.reviews')</span>
                    </a>
                </li>
                <li class="two-column">
                    <a class="waves-effect" href="javascript:void(0);" aria-expanded="false">
                        <!--<i class="fa fa-product-hunt fa-fw"></i>--> 
                        <img src="{{asset('/plugins/images/icons/shopping_cart_1.png')}}" 
                             alt="user-img"
                             class="img filter"
                             style="max-width:45px;margin-right:5px;padding:0px;">
                        <span class="hide-menu"> @lang('sidebar.product')</span>
                    </a>
                    <ul aria-expanded="false" class="collapse">
                        <li><a href="{{asset('products/person_ticket')}}">@lang('sidebar.person_ticket')</a></li>
                        <li><a href="{{asset('products/day_ticket')}}">@lang('sidebar.day_ticket')</a></li>
                    </ul>
                </li>
                <li class="two-column">
                    <a class="waves-effect" href="javascript:void(0);" aria-expanded="false">
                        <!--<i class="fa fa-product-hunt fa-fw"></i>--> 
                        <img src="{{asset('/plugins/images/icons/tag.png')}}" 
                             alt="user-img"
                             class="img filter"
                             style="max-width:45px;margin-right:5px;padding:0px;">
                        <span class="hide-menu"> @lang('sidebar.promos')</span>
                    </a>
                    <ul aria-expanded="false" class="collapse">
                        <li><a href="{{asset('promo')}}">@lang('sidebar.promos_view')</a></li>
                        <li><a href="{{asset('promo/create')}}">@lang('sidebar.promos_add')</a></li>
                    </ul>
                </li>
                <li class="two-column">
                    <a class="waves-effect" href="javascript:void(0);" aria-expanded="false">
                        <!--<i class="fa fa-list-ol fa-fw"></i>--> 
                        <img src="{{asset('/plugins/images/icons/List.png')}}" 
                             alt="user-img"
                             class="img filter"
                             style="max-width:45px;margin-right:5px;padding:0px;">
                        <span class="hide-menu"> @lang('sidebar.seasonal')</span>
                    </a>
                    <ul aria-expanded="false" class="collapse">
                        <li><a href="{{asset('seasonal/person')}}">@lang('sidebar.seasonal_person')</a></li>
                        <li><a href="{{asset('seasonal/parking')}}">@lang('sidebar.seasonal_parking')</a></li>
                    </ul>
                </li>
                <li class="two-column">
                    <a class="waves-effect" href="javascript:void(0);" aria-expanded="false">
                        <!--<i class="fa fa-list-ol fa-fw"></i>--> 
                        <img src="{{asset('/plugins/images/icons/User_List_1.png')}}" 
                             alt="user-img"
                             class="img filter"
                             style="max-width:45px;margin-right:5px;padding:0px;">
                        <span class="hide-menu"> @lang('sidebar.day')</span>
                    </a>
                    <ul aria-expanded="false" class="collapse">
                        <li><a href="{{asset('day/person')}}">@lang('sidebar.seasonal_person')</a></li>
                        <li><a href="{{asset('day/parking')}}">@lang('sidebar.seasonal_parking')</a></li>
                    </ul>
                </li>
                <li>
                    <a class="waves-effect" href="{{ url('location/payments') }}">
                        <!--<i class="fa fa-credit-card-alt fa-fw"></i>-->
                        <img src="{{asset('/plugins/images/icons/payment_card.png')}}" 
                             alt="user-img"
                             class="img filter"
                             style="max-width:45px;margin-right:5px;padding:0px;">
                        <span class="hide-menu"> @lang('sidebar.payments')</span>
                    </a>
                </li>
                <li>
                    <a class="waves-effect" href="{{ url('location/future/bookings') }}">
                        <!--<i class="fa fa-credit-card-alt fa-fw"></i>-->
                        <img src="{{asset('/plugins/images/icons/Reservation_03.png')}}" 
                             alt="user-img"
                             class="img filter"
                             style="max-width:45px;margin-right:5px;padding:0px;">
                        <span class="hide-menu"> @lang('sidebar.future_bookings')</span>
                    </a>
                </li>
                <li>
                    <a class="waves-effect" href="{{ url('/location/edit') }}">
                        <!--<i class="fa fa-pencil-square-o fa-fw"></i>-->
                        <img src="{{asset('/plugins/images/icons/parking.png')}}" 
                             alt="user-img"
                             class="img filter"
                             style="max-width:45px;margin-right:5px;padding:0px;">
                        <span class="hide-menu"> @lang('sidebar.loc_setting')</span>
                    </a>
                </li>
                <li class="two-column">
                    <a class="waves-effect" href="javascript:void(0);" aria-expanded="false">
                        <!--<i class="fa fa-qrcode fa-fw"></i>--> 
                        <img src="{{asset('/plugins/images/icons/Qr_Code_1.png')}}" 
                             alt="user-img"
                             class="img filter"
                             style="max-width:45px;margin-right:5px;padding:0px;">
                        <span class="hide-menu"> @lang('sidebar.devices')</span>
                    </a>
                    <ul aria-expanded="false" class="collapse">
                        <li><a href="{{asset('devices')}}">@lang('sidebar.manage_devices')</a></li>
                        <li><a href="{{asset('devices/sort')}}">@lang('sidebar.sort_devices')</a></li>
                        <li><a href="{{asset('devices/create')}}">@lang('sidebar.add_new_device')</a></li>

                    </ul>
                </li>
                <li>
                    <a class="waves-effect" href="{{ url('/messages') }}">
                        <!--<i class="fa fa-envelope fa-fw"></i>-->
                        <img src="{{asset('/plugins/images/icons/messages.png')}}" 
                             alt="user-img"
                             class="img filter"
                             style="max-width:45px;margin-right:5px;padding:0px;">
                        <span class="hide-menu"> @lang('sidebar.messages')</span>
                    </a>
                </li>
                <li>
                    <a class="waves-effect" href="{{ url('/account-settings') }}">
                        <!--<i class="fa fa-gear fa-fw"></i>-->
                        <img src="{{asset('/plugins/images/icons/setting.png')}}" 
                             alt="user-img"
                             class="img filter"
                             style="max-width:45px;margin-right:5px;padding:0px;">
                        <span class="hide-menu"> @lang('sidebar.account_settings')</span>
                    </a>
                </li>
                <li class="two-column">
                    <a class="waves-effect" href="javascript:void(0);" aria-expanded="false">
                        <!--<i class="fa fa-user fa-fw"></i>--> 
                        <img src="{{asset('/plugins/images/icons/User_List_1.png')}}" 
                             alt="user-img"
                             class="img filter"
                             style="max-width:45px;margin-right:5px;padding:0px;">
                        <span class="hide-menu"> @lang('sidebar.employees')</span>
                    </a>
                    <ul aria-expanded="false" class="collapse">
                        <li><a href="{{asset('users')}}">@lang('sidebar.view_employees')</a></li>
                        <li><a href="{{asset('user/create')}}">@lang('sidebar.add_new_employee')</a></li>
                    </ul>
                </li>
                <li class="two-column">
                    <a class="waves-effect" href="javascript:void(0);" aria-expanded="false">
                        <!--<i class="fa fa-group fa-fw"></i>--> 
                        <img src="{{asset('/plugins/images/icons/group_1.png')}}" 
                             alt="user-img"
                             class="img filter"
                             style="max-width:45px;margin-right:5px;padding:0px;">
                        <span class="hide-menu"> @lang('sidebar.group')</span>
                    </a>
                    <ul aria-expanded="false" class="collapse">
                        <li><a href="{{asset('group')}}">@lang('sidebar.manage_group')</a></li>
                        <li><a href="{{asset('group/create')}}">@lang('sidebar.add_new_group')</a></li>
                    </ul>
                </li>
                <li>
                    <a class="waves-effect" href="{{ url('/email/bounces') }}">
                        <!--<i class="fa fa-envelope fa-fw"></i>-->
                        <img src="{{asset('/plugins/images/icons/invite_send.png')}}" 
                             alt="user-img"
                             class="img filter"
                             style="max-width:45px;margin-right:5px;padding:0px;">
                        <span class="hide-menu"> @lang('sidebar.bounces_email')</span>
                    </a>
                </li>
                @endif
                @if(auth()->user()->hasRole('service'))
                <li class="two-column">
                    <a class="waves-effect" href="javascript:void(0);" aria-expanded="false">
                        <!--<i class="fa fa-list-ol fa-fw"></i>--> 
                        <img src="{{asset('/plugins/images/icons/List.png')}}" 
                             alt="user-img"
                             class="img filter"
                             style="max-width:45px;margin-right:5px;padding:0px;">
                        <span class="hide-menu"> @lang('sidebar.white_list')</span>
                    </a>
                    <ul aria-expanded="false" class="collapse">
                        <li><a href="{{asset('white-list')}}">@lang('sidebar.manage_user')</a></li>
                        <li><a href="{{asset('white-list/create')}}">@lang('sidebar.add_new_user')</a></li>

                    </ul>
                </li>
                <li class="two-column">
                    <a class="waves-effect" href="javascript:void(0);" aria-expanded="false">
                        <img src="{{asset('/plugins/images/icons/User_List_1.png')}}" 
                             alt="user-img"
                             class="img filter"
                             style="max-width:45px;margin-right:5px;padding:0px;">
                        <span class="hide-menu"> @lang('sidebar.user_list')</span>
                    </a>
                    <ul aria-expanded="false" class="collapse">
                        <li><a href="{{asset('user-list')}}">@lang('sidebar.manage_user')</a></li>
                        <li><a href="{{asset('user-list/create')}}">@lang('sidebar.add_new_user')</a></li>

                    </ul>
                </li>
                <li class="two-column">
                    <a class="waves-effect" href="javascript:void(0);" aria-expanded="false">
                        <!--<i class="fa fa-barcode fa-fw"></i>--> 
                        <img src="{{asset('/plugins/images/icons/barcode.png')}}" 
                             alt="user-img"
                             class="img filter"
                             style="max-width:45px;margin-right:5px;padding:0px;">
                        <span class="hide-menu"> @lang('sidebar.barcodes')</span>
                    </a>
                    <ul aria-expanded="false" class="collapse">
                        <li><a href="{{asset('barcode')}}">@lang('sidebar.manage_barcodes_parking')</a></li>
                        <li><a href="{{asset('barcode/create')}}">@lang('sidebar.add_new_barcode_parking')</a></li>
                        <li><a href="{{asset('person/barcode')}}">@lang('sidebar.manage_barcodes_person')</a></li>
                        <li><a href="{{asset('person/barcode/create')}}">@lang('sidebar.add_new_barcode_person')</a></li>
                        <li><a href="{{asset('barcode/fail-safe/person')}}">@lang('sidebar.fail_safe_person')</a></li>
                        <li><a href="{{asset('barcode/fail-safe/parking')}}">@lang('sidebar.fail_safe_parking')</a></li>
                    </ul>
                </li>
                <li class="two-column">
                    <a class="waves-effect" href="javascript:void(0);" aria-expanded="false">
                        <!--<i class="fa fa-book fa-fw"></i>--> 
                        <img src="{{asset('/plugins/images/icons/Reservation_03.png')}}" 
                             alt="user-img"
                             class="img filter"
                             style="max-width:45px;margin-right:5px;padding:0px;">
                        <span class="hide-menu"> @lang('sidebar.tommy_reservation')</span>
                    </a>
                    <ul aria-expanded="false" class="collapse">
                        <li><a href="{{asset('tommy-reservations')}}">@lang('sidebar.manage_reservations')</a></li>
                        <li><a href="{{asset('tommy-reservations/import')}}">@lang('sidebar.import_reservation')</a></li>
                        <li><a href="{{asset('tommy-reservations/bookings')}}">@lang('sidebar.bookings')</a></li>
						 <li><a href="{{asset('manual-reservation')}}">@lang('sidebar.add_reservation')</a></li>
                    </ul>
                </li>
                <li>
                    <a class="waves-effect" href="{{ url('/send-ticket') }}">
                        <!--<i class="fa fa-ticket fa-fw"></i>-->
                        <img src="{{asset('/plugins/images/icons/invite_send.png')}}" 
                             alt="user-img"
                             class="img filter"
                             style="max-width:45px;margin-right:5px;padding:0px;">
                        <span class="hide-menu"> @lang('sidebar.send_ticket')</span>
                    </a>
                </li>
                <li>
                    <a class="waves-effect" href="{{ url('location/attendants') }}">
                        <!--<i class="fa fa-list-ol fa-fw"></i>-->
                        <img src="{{asset('/plugins/images/icons/arrivals.png')}}" 
                             alt="user-img"
                             class="img filter"
                             style="max-width:45px;margin-right:5px;padding:0px;">
                        <span class="hide-menu"> @lang('sidebar.attendants')</span>
                    </a>
                </li>
                <li>
                    <a class="waves-effect" href="{{ url('/transaction_details') }}">
                        <!--<i class="fa fa-list-ol fa-fw"></i>-->
                        <img src="{{asset('/plugins/images/icons/transactions.png')}}" 
                             alt="user-img"
                             class="img filter"
                             style="max-width:45px;margin-right:5px;padding:0px;">
                        <span class="hide-menu"> @lang('sidebar.all_transactions')</span>
                    </a>
                </li>
                <li>
                    <a class="waves-effect" href="{{ url('location/reviews') }}">
                        <!--<i class="fa fa-clipboard fa-fw"></i>-->
                        <img src="{{asset('/plugins/images/icons/reviews.png')}}" 
                             alt="user-img"
                             class="img filter"
                             style="max-width:45px;margin-right:5px;padding:0px;">
                        <span class="hide-menu"> @lang('sidebar.reviews')</span>
                    </a>
                </li>
                <li class="two-column">
                    <a class="waves-effect" href="javascript:void(0);" aria-expanded="false">
                        <!--<i class="fa fa-product-hunt fa-fw"></i>--> 
                        <img src="{{asset('/plugins/images/icons/shopping_cart_1.png')}}" 
                             alt="user-img"
                             class="img filter"
                             style="max-width:45px;margin-right:5px;padding:0px;">
                        <span class="hide-menu"> @lang('sidebar.product')</span>
                    </a>
                    <ul aria-expanded="false" class="collapse">
                        <li><a href="{{asset('products/person_ticket')}}">@lang('sidebar.person_ticket')</a></li>
                        <li><a href="{{asset('products/day_ticket')}}">@lang('sidebar.day_ticket')</a></li>
                    </ul>
                </li>
                <li class="two-column">
                    <a class="waves-effect" href="javascript:void(0);" aria-expanded="false">
                        <!--<i class="fa fa-list-ol fa-fw"></i>--> 
                        <img src="{{asset('/plugins/images/icons/List.png')}}" 
                             alt="user-img"
                             class="img filter"
                             style="max-width:45px;margin-right:5px;padding:0px;">
                        <span class="hide-menu"> @lang('sidebar.seasonal')</span>
                    </a>
                    <ul aria-expanded="false" class="collapse">
                        <li><a href="{{asset('seasonal/person')}}">@lang('sidebar.seasonal_person')</a></li>
                        <li><a href="{{asset('seasonal/parking')}}">@lang('sidebar.seasonal_parking')</a></li>

                    </ul>
                </li>
                <li class="two-column">
                    <a class="waves-effect" href="javascript:void(0);" aria-expanded="false">
                        <!--<i class="fa fa-list-ol fa-fw"></i>--> 
                        <img src="{{asset('/plugins/images/icons/User_List_1.png')}}" 
                             alt="user-img"
                             class="img filter"
                             style="max-width:45px;margin-right:5px;padding:0px;">
                        <span class="hide-menu"> @lang('sidebar.day')</span>
                    </a>
                    <ul aria-expanded="false" class="collapse">
                        <li><a href="{{asset('day/person')}}">@lang('sidebar.seasonal_person')</a></li>
                        <li><a href="{{asset('day/parking')}}">@lang('sidebar.seasonal_parking')</a></li>
                    </ul>
                </li>
                <li>
                    <a class="waves-effect" href="{{ url('location/payments') }}">
                        <!--<i class="fa fa-credit-card-alt fa-fw"></i>-->
                        <img src="{{asset('/plugins/images/icons/payment_card.png')}}" 
                             alt="user-img"
                             class="img filter"
                             style="max-width:45px;margin-right:5px;padding:0px;">
                        <span class="hide-menu"> @lang('sidebar.payments')</span>
                    </a>
                </li>
                <li>
                    <a class="waves-effect" href="{{ url('/location/edit') }}">
                        <!--<i class="fa fa-pencil-square-o fa-fw"></i>-->
                        <img src="{{asset('/plugins/images/icons/parking.png')}}" 
                             alt="user-img"
                             class="img filter"
                             style="max-width:45px;margin-right:5px;padding:0px;">
                        <span class="hide-menu"> @lang('sidebar.loc_setting')</span>
                    </a>
                </li>
                <li class="two-column">
                    <a class="waves-effect" href="javascript:void(0);" aria-expanded="false">
                        <!--<i class="fa fa-qrcode fa-fw"></i>--> 
                        <img src="{{asset('/plugins/images/icons/Qr_Code_1.png')}}" 
                             alt="user-img"
                             class="img filter"
                             style="max-width:45px;margin-right:5px;padding:0px;">
                        <span class="hide-menu"> @lang('sidebar.devices')</span>
                    </a>
                    <ul aria-expanded="false" class="collapse">
                        <li><a href="{{asset('devices')}}">@lang('sidebar.manage_devices')</a></li>
                        <li><a href="{{asset('devices/sort')}}">@lang('sidebar.sort_devices')</a></li>
                        <li><a href="{{asset('devices/create')}}">@lang('sidebar.add_new_device')</a></li>

                    </ul>
                </li>
                <li>
                    <a class="waves-effect" href="{{ url('/messages') }}">
                        <!--<i class="fa fa-envelope fa-fw"></i>-->
                        <img src="{{asset('/plugins/images/icons/messages.png')}}" 
                             alt="user-img"
                             class="img filter"
                             style="max-width:45px;margin-right:5px;padding:0px;">
                        <span class="hide-menu"> @lang('sidebar.messages')</span>
                    </a>
                </li>
                <li>
                    <a class="waves-effect" href="{{ url('/account-settings') }}">
                        <!--<i class="fa fa-gear fa-fw"></i>-->
                        <img src="{{asset('/plugins/images/icons/setting.png')}}" 
                             alt="user-img"
                             class="img filter"
                             style="max-width:45px;margin-right:5px;padding:0px;">
                        <span class="hide-menu"> @lang('sidebar.account_settings')</span>
                    </a>
                </li>
                <li class="two-column">
                    <a class="waves-effect" href="javascript:void(0);" aria-expanded="false">
                        <!--<i class="fa fa-user fa-fw"></i>--> 
                        <img src="{{asset('/plugins/images/icons/User_List_1.png')}}" 
                             alt="user-img"
                             class="img filter"
                             style="max-width:45px;margin-right:5px;padding:0px;">
                        <span class="hide-menu"> @lang('sidebar.employees')</span>
                    </a>
                    <ul aria-expanded="false" class="collapse">
                        <li><a href="{{asset('users')}}">@lang('sidebar.view_employees')</a></li>
                        <li><a href="{{asset('user/create')}}">@lang('sidebar.add_new_employee')</a></li>
                    </ul>
                </li>
                <li class="two-column">
                    <a class="waves-effect" href="javascript:void(0);" aria-expanded="false">
                        <!--<i class="fa fa-group fa-fw"></i>--> 
                        <img src="{{asset('/plugins/images/icons/group_1.png')}}" 
                             alt="user-img"
                             class="img filter"
                             style="max-width:45px;margin-right:5px;padding:0px;">
                        <span class="hide-menu"> @lang('sidebar.group')</span>
                    </a>
                    <ul aria-expanded="false" class="collapse">
                        <li><a href="{{asset('group')}}">@lang('sidebar.manage_group')</a></li>
                        <li><a href="{{asset('group/create')}}">@lang('sidebar.add_new_group')</a></li>
                    </ul>
                </li>
                @endif
                @if(auth()->user()->hasRole('manager'))
					
                <li class="two-column">
                    <a class="waves-effect" href="javascript:void(0);" aria-expanded="false">
                        <!--<i class="fa fa-list-ol fa-fw"></i>--> 
                        <img src="{{asset('/plugins/images/icons/List.png')}}" 
                             alt="user-img"
                             class="img filter"
                             style="max-width:45px;margin-right:5px;padding:0px;">
                        <span class="hide-menu"> @lang('sidebar.white_list')</span>
                    </a>
                    <ul aria-expanded="false" class="collapse">
                        <li><a href="{{asset('white-list')}}">@lang('sidebar.manage_user')</a></li>
                        <li><a href="{{asset('white-list/create')}}">@lang('sidebar.add_new_user')</a></li>

                    </ul>
                </li>
                <li class="two-column">
                    <a class="waves-effect" href="javascript:void(0);" aria-expanded="false">
                        <img src="{{asset('/plugins/images/icons/User_List_1.png')}}" 
                             alt="user-img"
                             class="img filter"
                             style="max-width:45px;margin-right:5px;padding:0px;">
                        <span class="hide-menu"> @lang('sidebar.user_list')</span>
                    </a>
                    <ul aria-expanded="false" class="collapse">
                        <li><a href="{{asset('user-list')}}">@lang('sidebar.manage_user')</a></li>
                        <li><a href="{{asset('user-list/create')}}">@lang('sidebar.add_new_user')</a></li>

                    </ul>
                </li>
                <li class="two-column">
                    <a class="waves-effect" href="javascript:void(0);" aria-expanded="false">
                        <!--<i class="fa fa-barcode fa-fw"></i>--> 
                        <img src="{{asset('/plugins/images/icons/barcode.png')}}" 
                             alt="user-img"
                             class="img filter"
                             style="max-width:45px;margin-right:5px;padding:0px;">
                        <span class="hide-menu"> @lang('sidebar.barcodes')</span>
                    </a>
                    <ul aria-expanded="false" class="collapse">
                        <li><a href="{{asset('barcode')}}">@lang('sidebar.manage_barcodes_parking')</a></li>
                        <li><a href="{{asset('barcode/create')}}">@lang('sidebar.add_new_barcode_parking')</a></li>
                        <li><a href="{{asset('person/barcode')}}">@lang('sidebar.manage_barcodes_person')</a></li>
                        <li><a href="{{asset('person/barcode/create')}}">@lang('sidebar.add_new_barcode_person')</a></li>
                        <li><a href="{{asset('barcode/fail-safe/person')}}">@lang('sidebar.fail_safe_person')</a></li>
                        <li><a href="{{asset('barcode/fail-safe/parking')}}">@lang('sidebar.fail_safe_parking')</a></li>
                    </ul>
                </li>
                <li class="two-column">
                    <a class="waves-effect" href="javascript:void(0);" aria-expanded="false">
                        <!--<i class="fa fa-book fa-fw"></i>--> 
                        <img src="{{asset('/plugins/images/icons/Reservation_03.png')}}" 
                             alt="user-img"
                             class="img filter"
                             style="max-width:45px;margin-right:5px;padding:0px;">
                        <span class="hide-menu"> @lang('sidebar.tommy_reservation')</span>
                    </a>
                    <ul aria-expanded="false" class="collapse">
                        <li><a href="{{asset('tommy-reservations')}}">@lang('sidebar.manage_reservations')</a></li>
                        <li><a href="{{asset('tommy-reservations/import')}}">@lang('sidebar.import_reservation')</a></li>
                        <li><a href="{{asset('tommy-reservations/bookings')}}">@lang('sidebar.bookings')</a></li>
						 <li><a href="{{asset('manual-reservation')}}">@lang('sidebar.add_reservation')</a></li>
                    </ul>
                </li>
                <li>
                    <a class="waves-effect" href="{{ url('/send-ticket') }}">
                        <!--<i class="fa fa-ticket fa-fw"></i>-->
                        <img src="{{asset('/plugins/images/icons/invite_send.png')}}" 
                             alt="user-img"
                             class="img filter"
                             style="max-width:45px;margin-right:5px;padding:0px;">
                        <span class="hide-menu"> @lang('sidebar.send_ticket')</span>
                    </a>
                </li>
                <li>
                    <a class="waves-effect" href="{{ url('location/attendants') }}">
                        <!--<i class="fa fa-list-ol fa-fw"></i>-->
                        <img src="{{asset('/plugins/images/icons/arrivals.png')}}" 
                             alt="user-img"
                             class="img filter"
                             style="max-width:45px;margin-right:5px;padding:0px;">
                        <span class="hide-menu"> @lang('sidebar.attendants')</span>
                    </a>
                </li>
                <li>
                    <a class="waves-effect" href="{{ url('/transaction_details') }}">
                        <!--<i class="fa fa-list-ol fa-fw"></i>-->
                        <img src="{{asset('/plugins/images/icons/transactions.png')}}" 
                             alt="user-img"
                             class="img filter"
                             style="max-width:45px;margin-right:5px;padding:0px;">
                        <span class="hide-menu"> @lang('sidebar.all_transactions')</span>
                    </a>
                </li>
                <li>
                    <a class="waves-effect" href="{{ url('location/reviews') }}">
                        <!--<i class="fa fa-clipboard fa-fw"></i>-->
                        <img src="{{asset('/plugins/images/icons/reviews.png')}}" 
                             alt="user-img"
                             class="img filter"
                             style="max-width:45px;margin-right:5px;padding:0px;">
                        <span class="hide-menu"> @lang('sidebar.reviews')</span>
                    </a>
                </li>
                <li class="two-column">
                    <a class="waves-effect" href="javascript:void(0);" aria-expanded="false">
                        <!--<i class="fa fa-product-hunt fa-fw"></i>--> 
                        <img src="{{asset('/plugins/images/icons/shopping_cart_1.png')}}" 
                             alt="user-img"
                             class="img filter"
                             style="max-width:45px;margin-right:5px;padding:0px;">
                        <span class="hide-menu"> @lang('sidebar.product')</span>
                    </a>
                    <ul aria-expanded="false" class="collapse">
                        <li><a href="{{asset('products/person_ticket')}}">@lang('sidebar.person_ticket')</a></li>
                        <li><a href="{{asset('products/day_ticket')}}">@lang('sidebar.day_ticket')</a></li>
                    </ul>
                </li>
                <li>
                    <a class="waves-effect" href="{{ url('location/payments') }}">
                        <!--<i class="fa fa-credit-card-alt fa-fw"></i>-->
                        <img src="{{asset('/plugins/images/icons/payment_card.png')}}" 
                             alt="user-img"
                             class="img filter"
                             style="max-width:45px;margin-right:5px;padding:0px;">
                        <span class="hide-menu"> @lang('sidebar.payments')</span>
                    </a>
                </li>
                <li>
                    <a class="waves-effect" href="{{ url('/location/edit') }}">
                        <!--<i class="fa fa-pencil-square-o fa-fw"></i>-->
                        <img src="{{asset('/plugins/images/icons/parking.png')}}" 
                             alt="user-img"
                             class="img filter"
                             style="max-width:45px;margin-right:5px;padding:0px;">
                        <span class="hide-menu"> @lang('sidebar.loc_setting')</span>
                    </a>
                </li>
                <li>
                    <a class="waves-effect" href="{{ url('/messages') }}">
                        <!--<i class="fa fa-envelope fa-fw"></i>-->
                        <img src="{{asset('/plugins/images/icons/messages.png')}}" 
                             alt="user-img"
                             class="img filter"
                             style="max-width:45px;margin-right:5px;padding:0px;">
                        <span class="hide-menu"> @lang('sidebar.messages')</span>
                    </a>
                </li>
                <li>
                    <a class="waves-effect" href="{{ url('/account-settings') }}">
                        <!--<i class="fa fa-gear fa-fw"></i>-->
                        <img src="{{asset('/plugins/images/icons/setting.png')}}" 
                             alt="user-img"
                             class="img filter"
                             style="max-width:45px;margin-right:5px;padding:0px;">
                        <span class="hide-menu"> @lang('sidebar.account_settings')</span>
                    </a>
                </li>
                <li class="two-column">
                    <a class="waves-effect" href="javascript:void(0);" aria-expanded="false">
                        <!--<i class="fa fa-group fa-fw"></i>--> 
                        <img src="{{asset('/plugins/images/icons/group_1.png')}}" 
                             alt="user-img"
                             class="img filter"
                             style="max-width:45px;margin-right:5px;padding:0px;">
                        <span class="hide-menu"> @lang('sidebar.group')</span>
                    </a>
                    <ul aria-expanded="false" class="collapse">
                        <li><a href="{{asset('group')}}">@lang('sidebar.manage_group')</a></li>
                        <li><a href="{{asset('group/create')}}">@lang('sidebar.add_new_group')</a></li>
                    </ul>
                </li>
                @endif
                @if(auth()->user()->hasRole('operator'))
                <li class="two-column">
                    <a class="waves-effect" href="javascript:void(0);" aria-expanded="false">
                        <img src="{{asset('/plugins/images/icons/User_List_1.png')}}" 
                             alt="user-img"
                             class="img filter"
                             style="max-width:45px;margin-right:5px;padding:0px;">
                        <span class="hide-menu"> @lang('sidebar.user_list')</span>
                    </a>
                    <ul aria-expanded="false" class="collapse">
                        <li><a href="{{asset('user-list')}}">@lang('sidebar.manage_user')</a></li>
                        <li><a href="{{asset('user-list/create')}}">@lang('sidebar.add_new_user')</a></li>

                    </ul>
                </li>
                <li class="two-column">
                    <a class="waves-effect" href="javascript:void(0);" aria-expanded="false">
                        <!--<i class="fa fa-barcode fa-fw"></i>--> 
                        <img src="{{asset('/plugins/images/icons/barcode.png')}}" 
                             alt="user-img"
                             class="img filter"
                             style="max-width:45px;margin-right:5px;padding:0px;">
                        <span class="hide-menu"> @lang('sidebar.barcodes')</span>
                    </a>
                    <ul aria-expanded="false" class="collapse">
                        <li><a href="{{asset('barcode')}}">@lang('sidebar.manage_barcodes_parking')</a></li>
                        <li><a href="{{asset('barcode/create')}}">@lang('sidebar.add_new_barcode_parking')</a></li>
                        <li><a href="{{asset('person/barcode')}}">@lang('sidebar.manage_barcodes_person')</a></li>
                        <li><a href="{{asset('person/barcode/create')}}">@lang('sidebar.add_new_barcode_person')</a></li>
                        <li><a href="{{asset('barcode/fail-safe/person')}}">@lang('sidebar.fail_safe_person')</a></li>
                        <li><a href="{{asset('barcode/fail-safe/parking')}}">@lang('sidebar.fail_safe_parking')</a></li>
                    </ul>
                </li>
                <li class="two-column">
                    <a class="waves-effect" href="javascript:void(0);" aria-expanded="false">
                        <!--<i class="fa fa-book fa-fw"></i>--> 
                        <img src="{{asset('/plugins/images/icons/Reservation_03.png')}}" 
                             alt="user-img"
                             class="img filter"
                             style="max-width:45px;margin-right:5px;padding:0px;">
                        <span class="hide-menu"> @lang('sidebar.tommy_reservation')</span>
                    </a>
                    <ul aria-expanded="false" class="collapse">
                        <li><a href="{{asset('tommy-reservations')}}">@lang('sidebar.manage_reservations')</a></li>
                        <li><a href="{{asset('tommy-reservations/import')}}">@lang('sidebar.import_reservation')</a></li>
                        <li><a href="{{asset('tommy-reservations/bookings')}}">@lang('sidebar.bookings')</a></li>
						 <li><a href="{{asset('manual-reservation')}}">@lang('sidebar.add_reservation')</a></li>
						 
                    </ul>
                </li>
                <li class="two-column">
                    <a class="waves-effect" href="javascript:void(0);" aria-expanded="false">
                        <!--<i class="fa fa-qrcode fa-fw"></i>--> 
                        <img src="{{asset('/plugins/images/icons/Qr_Code_1.png')}}" 
                             alt="user-img"
                             class="img filter"
                             style="max-width:45px;margin-right:5px;padding:0px;">
                        <span class="hide-menu"> @lang('sidebar.devices')</span>
                    </a>
                    <ul aria-expanded="false" class="collapse">
                        <li><a href="{{asset('devices')}}">@lang('sidebar.manage_devices')</a></li>
                        <li><a href="{{asset('devices/sort')}}">@lang('sidebar.sort_devices')</a></li>
                        <li><a href="{{asset('devices/create')}}">@lang('sidebar.add_new_device')</a></li>

                    </ul>
                </li>
                <li>
                    <a class="waves-effect" href="{{ url('location/attendants') }}">
                        <!--<i class="fa fa-list-ol fa-fw"></i>-->
                        <img src="{{asset('/plugins/images/icons/arrivals.png')}}" 
                             alt="user-img"
                             class="img filter"
                             style="max-width:45px;margin-right:5px;padding:0px;">
                        <span class="hide-menu"> @lang('sidebar.attendants')</span>
                    </a>
                </li>
                <li class="two-column">
                    <a class="waves-effect" href="javascript:void(0);" aria-expanded="false">
                        <!--<i class="fa fa-list-ol fa-fw"></i>--> 
                        <img src="{{asset('/plugins/images/icons/List.png')}}" 
                             alt="user-img"
                             class="img filter"
                             style="max-width:45px;margin-right:5px;padding:0px;">
                        <span class="hide-menu"> @lang('sidebar.seasonal')</span>
                    </a>
                    <ul aria-expanded="false" class="collapse">
                        <li><a href="{{asset('seasonal/person')}}">@lang('sidebar.seasonal_person')</a></li>
                        <li><a href="{{asset('seasonal/parking')}}">@lang('sidebar.seasonal_parking')</a></li>
                    </ul>
                </li>
                <li class="two-column">
                    <a class="waves-effect" href="javascript:void(0);" aria-expanded="false">
                        <!--<i class="fa fa-list-ol fa-fw"></i>--> 
                        <img src="{{asset('/plugins/images/icons/User_List_1.png')}}" 
                             alt="user-img"
                             class="img filter"
                             style="max-width:45px;margin-right:5px;padding:0px;">
                        <span class="hide-menu"> @lang('sidebar.day')</span>
                    </a>
                    <ul aria-expanded="false" class="collapse">
                        <li><a href="{{asset('day/person')}}">@lang('sidebar.seasonal_person')</a></li>
                        <li><a href="{{asset('day/parking')}}">@lang('sidebar.seasonal_parking')</a></li>
                    </ul>
                </li>
				<li>
                    <a class="waves-effect" href="{{ url('location/future/bookings') }}">
                        <!--<i class="fa fa-credit-card-alt fa-fw"></i>-->
                        <img src="{{asset('/plugins/images/icons/Reservation_03.png')}}" 
                             alt="user-img"
                             class="img filter"
                             style="max-width:45px;margin-right:5px;padding:0px;">
                        <span class="hide-menu"> @lang('sidebar.future_bookings')</span>
                    </a>
                </li>
                <li>
                    <a class="waves-effect" href="{{ url('/location/edit') }}">
                        <!--<i class="fa fa-pencil-square-o fa-fw"></i>-->
                        <img src="{{asset('/plugins/images/icons/parking.png')}}" 
                             alt="user-img"
                             class="img filter"
                             style="max-width:45px;margin-right:5px;padding:0px;">
                        <span class="hide-menu"> @lang('sidebar.loc_setting')</span>
                    </a>
                </li>
                @endif
                @if(auth()->user()->hasRole('operator_basic'))
                <li class="two-column">
                    <a class="waves-effect" href="javascript:void(0);" aria-expanded="false">
                        <img src="{{asset('/plugins/images/icons/User_List_1.png')}}" 
                             alt="user-img"
                             class="img filter"
                             style="max-width:45px;margin-right:5px;padding:0px;">
                        <span class="hide-menu"> @lang('sidebar.user_list')</span>
                    </a>
                    <ul aria-expanded="false" class="collapse">
                        <li><a href="{{asset('user-list')}}">@lang('sidebar.manage_user')</a></li>
                        <li><a href="{{asset('user-list/create')}}">@lang('sidebar.add_new_user')</a></li>

                    </ul>
                </li>
                <li>
                    <a class="waves-effect" href="{{ url('location/attendants') }}">
                        <!--<i class="fa fa-list-ol fa-fw"></i>-->
                        <img src="{{asset('/plugins/images/icons/arrivals.png')}}" 
                             alt="user-img"
                             class="img filter"
                             style="max-width:45px;margin-right:5px;padding:0px;">
                        <span class="hide-menu"> @lang('sidebar.attendants')</span>
                    </a>
                </li>
                <li>
                    <a class="waves-effect" href="{{ url('/account-settings') }}">
                        <!--<i class="fa fa-gear fa-fw"></i>-->
                        <img src="{{asset('/plugins/images/icons/setting.png')}}" 
                             alt="user-img"
                             class="img filter"
                             style="max-width:45px;margin-right:5px;padding:0px;">
                        <span class="hide-menu"> @lang('sidebar.account_settings')</span>
                    </a>
                </li>
                @endif
                @if(auth()->user()->hasRole('promo'))
                <li class="two-column">
                    <a class="waves-effect" href="javascript:void(0);" aria-expanded="false">
                        <!--<i class="fa fa-product-hunt fa-fw"></i>--> 
                        <img src="{{asset('/plugins/images/icons/tag.png')}}" 
                             alt="user-img"
                             class="img filter"
                             style="max-width:45px;margin-right:5px;padding:0px;">
                        <span class="hide-menu"> @lang('sidebar.promos')</span>
                    </a>
                    <ul aria-expanded="false" class="collapse">
                        <li><a href="{{asset('promo')}}">@lang('sidebar.promos_view')</a></li>
                        <li><a href="{{asset('promo/create')}}">@lang('sidebar.promos_add')</a></li>
                    </ul>
                </li>
                <li>
                    <a class="waves-effect" href="{{ url('/account-settings') }}">
                        <!--<i class="fa fa-gear fa-fw"></i>-->
                        <img src="{{asset('/plugins/images/icons/setting.png')}}" 
                             alt="user-img"
                             class="img filter"
                             style="max-width:45px;margin-right:5px;padding:0px;">
                        <span class="hide-menu"> @lang('sidebar.account_settings')</span>
                    </a>
                </li>
                @endif
            </ul>
        </nav>
        @endif
    </div>
</aside>