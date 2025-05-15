<div style="text-align:center;width:100%;clear:both;">
    <img style="width:200px;"src="{{url('/images/logo.png')}}">
</div>
<div style="display:flex;padding:10px;background-color:#456bb3;width:100%;clear:both;">
    <div style="color:white;width:100%;text-align:center;">
        @lang('email.forget_password_email_subject'),
    </div>
</div>
<div style="margin-top:15px;text-align:left;width:100%;clear:both;">
    <p>@lang('email.forget_password_p1')</p>
    
    <p><a href="{{$reset_link}}" class="btn btn-info btn-lg">@lang('email.forget_password_btn_text')</a></p>
    
    <p>@lang('email.forget_password_p2')</p>
    
    <p>@lang('email.forget_password_p3') {{$reset_link}}</p>
    
    <p><b>@lang('email.what') is Parkingshop?</b></p>
    
    <p>@lang('email.parkingshop_description')</p>
    
</div>
<div style="display:flex;margin-bottom:30px;position:relative;width:100%;clear:both;">
    <div style="padding: 10px 0px 10px 0px;  width: 60%; float: left;">
        <h3>@lang('email.regards')</h3>
        <span><?php echo $location_title ?></span><br>
        <span><?php echo $location_address ?></span><br>
        <span><?php echo $location_phone ?></span><br>        
    </div>
</div>

