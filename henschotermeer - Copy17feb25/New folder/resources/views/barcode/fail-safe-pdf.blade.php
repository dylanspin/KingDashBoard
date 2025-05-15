<!DOCTYPE html>
<html>
    <head>
        <title>Ticket</title>
        <!--<link href="{{ URL::asset('css/home.css') }}"  rel="stylesheet"/>-->
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
        <!--        <link href="https://fonts.googleapis.com/css?family=Lato" rel="stylesheet">-->
        <style type="text/css">

            body {
                position: relative;
                font-family: 'Helvetica', sans-serif;
            }
            .title{
                padding-top:0px;
                width:100%;
                background-color: #456bb3;
                clear:both;
            }
            .title .name{
                height:40px;
                border-right: 1px solid white;
                font-family: 'Helvetica', sans-serif;
                color: white; 
                background-color: #456bb3;
                padding: 20px 0px 10px 0px; 
                width: 50%; 
                float: left;
                text-align: center
            }
            .title .message{
                background-color: #456bb3; 
                font-family: 'Helvetica', sans-serif;
                height:40px;
                color: white;
                padding: 20px 0px 10px 0px;
                width: 50%; 
                float: left; 
                text-align: center
            }
            .detail_section{
                display: flex;
                width:100%;
                padding-top: 20px;
                clear:both;
            }
            .detail_section_con{
                font-size:14px;
                height:260px;
                width: 50%;
                border-right:2px dotted #CDCDCD;
                float: left;
                margin-right:0px; 
            }
            .detail_section_con div{
                clear:both;
                height:20px;
                padding-left:20px;
                margin-bottom: 10px;
            }
            .location_title{
                text-align:center; 
                width: 100%; 
                clear:both; 
                margin-bottom: 15px; 
                font-family: 'Helvetica', sans-serif;
            }
            .qr_code_con{
                text-align: center;
                width: 50%;
                float: right;
                clear: both;
            }
            .qr_code_con .qr_code_img{
                margin-top: 30px;
                width: 100%;
                margin-left: auto;
                margin-right: auto;
                float: left;
                clear: both;
            }
            .qr_code_con .qr_code_img img{
                /*margin-left: 25%;*/
                vertical-align: middle;
            }
            .qr_code_con .folding_instruction{
                width: 100%;
                margin-left: auto;
                margin-right: auto;
                float: left;
                margin-top: 100px;
                clear: both;
            }
            .qr_code_con .folding_instruction img{
                /*margin-left: 30%;*/
                vertical-align: middle;
                height: 70px;
                width: 165px;
            }
            .description_sec{
                clear:both;
                width: 100%;
                font-family: 'Helvetica', sans-serif;
            }
            .description_sec .left_sec{
                padding: 5px 0px; 
                width: 60%; 
                float: left;
                margin-bottom: 20px;
            }
            .description_sec .right_sec{
                padding: 5px 0px;
                width: 40%;
                float: right;
            }
            .location_image_con{
                width:100%;
                margin-left: auto;
                margin-right: auto;
                padding-top:20px;
            }
            .location_image_con img{
                margin-bottom:20px;
                height:170px; 
                float:right;
            }
            .location_map_con{
                width:100%;
                margin-left: auto;
                margin-right: auto;
                padding-top:200px;
            }
            .location_map_con img{
                margin-bottom:20px; 
                float:right;
            }
            .parking_shop_con{
                width:100%;
                margin-left: auto;
                margin-right: auto;
                padding-top:400px;
            }
            .parking_shop_con img{
                width:170px;
                margin-bottom:20px; 
                float:right;
            }
            .font-size-12{
                font-size:12px;
                font-family: 'Helvetica', sans-serif;
            }
            .footer{
                width:100%;
                clear:both;
            }
            .footer .footer_text{
                font-size:14px;
                font-family: 'Helvetica', sans-serif;
                width: 60%;
                float: left;
            }
            .footer .footer_text span,.footer .footer_text h3{
                font-family: 'Helvetica', sans-serif;
            }
        </style>
    </head>
    <body style="width: 86mm;height: 55mm;margin: 0px;padding: 0px;">

        <div  style="text-align:left;margin: 0px;padding: 0px;width: 80%">
            <img 
                src="data:image/png;base64,{{ \Milon\Barcode\DNS1D::getBarcodePNG($barcode_number, 'C128')}}" 
                alt="barcode" />
        </div>
        <div  style="width:100%;float: left; text-align: left;margin: 0px;padding: 0px;">
            <p style="margin-top:10px!important;margin-bottom: 0px;font-size:12px;">Vanaf: {{ date('d/m/Y 00:00:00') }}</p>
            <p style="margin-top:5px;margin-bottom: 0px;font-size:12px;">Tot: {{ date('d/m/Y 23:59:59') }}</p>
        </div>
    </body>
</html>