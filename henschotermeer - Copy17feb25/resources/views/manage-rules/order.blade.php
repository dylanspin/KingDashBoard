@extends('layouts.master')

@push('css')
<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
<style type="text/css">
    .sortable { list-style-type: none; margin: 0; padding: 0; width: 100%; }
    .sortable li { margin: 0 5px 5px 5px; padding: 10px; font-size: 1.2em; }
    .ui-state-highlight { height: 3.5em; line-height: 1.2em; background-color: lightgray; }

    .drop-placeholder {
        background-color: lightgray;
        height: 3.5em;
        padding-top: 12px;
        padding-bottom: 12px;
        line-height: 1.2em;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- .row -->
    <div class="row">
        <div class="col-lg-12 col-sm-12 col-xs-12">
            <div class="white-box">
                <h3 class="box-title pull-left">@lang('access-rules.access_rule')</h3>
                <div class="clearfix"></div>
                <hr>
                <!-- Nav tabs -->
                <ul class="nav nav-tabs" role="tablist">
                    <li 
                        role="presentation" 
                        class="active">
                        <a href="#vehicle" 
                           aria-controls="vehicle" 
                           role="tab"                            
                           data-toggle="tab" 
                           aria-expanded="true">
                            <span 
                                class=""> Rules
                            </span>
                        </a>
                    </li>
                </ul>
                <!-- Tab panes -->
                <div id="rule_ordering" class="tab-content">
                    <div class="col-sm-12">
                        <div class="alert alert-success alert-dismissible print-success-msg" style="display:none">
                            <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                            <ul></ul>
                        </div>
                        <div class="alert alert-danger alert-dismissible print-error-msg" style="display:none">
                            <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                            <ul></ul>
                        </div>
                    </div>
                    <div role="tabpanel" class="tab-pane active" id="vehicle">
                        <div class="col-md-12">
                            <ul id="rule-sortable" class="sortable list-group list-unstyled">
                                @foreach($rules as $key=>$rules)
                                <li 
                                    id="{{$rules->id}}"
                                    class="ui-state-default">{{$rules->name}}</li>
                                @endforeach
                            </ul>
                        </div>
                        <div class="clearfix"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('js')
<script src="https://code.jquery.com/jquery-1.12.4.js"></script>
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
<script src="{{ asset('/js/manage-rules.js') }}"></script>
<script>
$(function () {
});
</script>
@endpush