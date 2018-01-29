
<div class="row">
    <div class="{{$viewClass['label']}}"><h4 class="pull-right">{{ $label }}</h4></div>
    <div class="{{$viewClass['field']}}"></div>
</div>

<hr style="margin-top: 0px;">

<div id="has-many-{{$column}}" class="has-many-{{$column}}">

    <div class="has-many-{{$column}}-forms">

        @foreach($forms as $pk => $form)

            <div class="has-many-{{$column}}-form fields-group">

                @foreach($form->fields() as $field)
                    {!! $field->render() !!}
                @endforeach

                @if(isset($options['remove']) && $options['remove']==false)
                @else
                <div class="form-group">
                    <label class="{{$viewClass['label']}} control-label"></label>
                    <div class="{{$viewClass['field']}}">
                        <div class="remove btn btn-warning btn-sm pull-right" style="margin-left: 5px;"><i class="fa fa-trash">&nbsp;</i>{{ trans('admin.remove') }}</div>
                        <div class="movedown btn btn-primary btn-sm pull-right" style="margin-left: 5px;"><i class="fa fa-long-arrow-down loc-place-down"></i>&nbsp;下移</div>
                        <div class="moveup   btn btn-primary btn-sm pull-right" style="margin-left: 5px;"><i class="fa fa-long-arrow-up loc-place-up"></i>&nbsp;上移</div>
                    </div>
                </div>
                @endif
                <hr>
            </div>

        @endforeach
    </div>

    @if(isset($options['add']) && $options['add']==false)
    @else
    <template class="{{$column}}-tpl">
        <div class="has-many-{{$column}}-form fields-group">

            {!! $template !!}

            <div class="form-group">
                <label class="{{$viewClass['label']}} control-label"></label>
                <div class="{{$viewClass['field']}}">
                    <div class="remove   btn btn-warning btn-sm pull-right" style="margin-left: 5px;"><i class="fa fa-trash"></i>&nbsp;{{ trans('admin.remove') }}</div>
                    <div class="movedown btn btn-primary btn-sm pull-right" style="margin-left: 5px;"><i class="fa fa-long-arrow-down loc-place-down"></i>&nbsp;下移</div>
                    <div class="moveup   btn btn-primary btn-sm pull-right" style="margin-left: 5px;"><i class="fa fa-long-arrow-up loc-place-up"></i>&nbsp;上移</div>
                </div>
            </div>
            <hr>
        </div>
    </template>

    <div class="form-group">
        <label class="{{$viewClass['label']}} control-label"></label>
        <div class="{{$viewClass['field']}}">
            <div class="add btn btn-success btn-sm"><i class="fa fa-save"></i>&nbsp;{{ trans('admin.new') }}</div>
        </div>
    </div>
    @endif

</div>