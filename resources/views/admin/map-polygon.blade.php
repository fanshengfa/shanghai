<div class="{{$viewClass['form-group']}} {!! !$errors->has($errorKey) ? '' : 'has-error' !!}">

    <label for="{{$id['lat']}}" class="{{$viewClass['label']}} control-label">{{$label}}</label>




    <div class="{{$viewClass['field']}}">

        @include('admin::form.error')
        <div class="row" style="margin-top: 10px;">
            <div class="col-sm-8">
                <div id="map_{{$id['polygon']}}" style="width: 100%;height: 400px;max-height: 400px; margin-top: 10px;" {!! $attributes !!}></div>
                <input type="hidden" id="{{$id['polygon']}}" name="{{$name['polygon']}}" value="{{ old($column['polygon'], $value['polygon']) }}" {!! $attributes !!} />
            </div>
            <div class="col-sm-4">

                <div id="addr_list_{{$id['polygon']}}" style="width: 100%;height: 400px;max-height: 400px; margin-top: 10px;" {!! $attributes !!}></div>
            </div>
        </div>
        @include('admin::form.help-block')

    </div>
</div>
