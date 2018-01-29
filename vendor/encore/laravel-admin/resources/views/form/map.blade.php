<div class="{{$viewClass['form-group']}} {!! !$errors->has($errorKey) ? '' : 'has-error' !!}">

    <label for="{{$id['lat']}}" class="{{$viewClass['label']}} control-label">{{$label}}</label>




    <div class="{{$viewClass['field']}}">

        @include('admin::form.error')
        <div class="row" style="margin-top: 10px;">
            <div class="col-sm-8">
                <div>
                    <select id="{{$id['province']}}" name="{{$name['province']}}" data-value="{{ old($column['province'], $value['province']) }}" {!! $attributes !!} class="form-control" style="width:120px; float:left;"><option value="">--请选择省份--</option></select>
                    <select id="{{$id['city']}}" name="{{$name['city']}}" data-value="{{ old($column['city'], $value['city']) }}" {!! $attributes !!} class="form-control" style="width:180px; float: left; margin-left: 5px;"><option value="">--请选择城市--</option></select>
                    <select id="{{$id['region']}}" name="{{$name['region']}}" data-value="{{ old($column['region'], $value['region']) }}" {!! $attributes !!} class="form-control" style="width:180px; float:left; margin-left: 5px;"><option value="">--请选择地区--</option></select>
                </div>
                <div id="map_{{$id['lat'].$id['lng']}}" style="width: 100%;height: 400px;max-height: 400px; margin-top: 10px;" {!! $attributes !!}></div>
                <input type="hidden" id="{{$id['lat']}}" name="{{$name['lat']}}" value="{{ old($column['lat'], $value['lat']) }}" {!! $attributes !!} />
                <input type="hidden" id="{{$id['lng']}}" name="{{$name['lng']}}" value="{{ old($column['lng'], $value['lng']) }}" {!! $attributes !!} />
                @if($id['map_province'])
                    <input type="hidden" id="{{$id['map_province']}}" name="{{$name['map_province']}}" value="{{ old($column['map_province'], $value['map_province']) }}" {!! $attributes !!} />
                @endif
                @if($id['map_city'])
                    <input type="hidden" id="{{$id['map_city']}}" name="{{$name['map_city']}}" value="{{ old($column['map_city'], $value['map_city']) }}" {!! $attributes !!} />
                @endif
                @if($id['map_region'])
                    <input type="hidden" id="{{$id['map_region']}}" name="{{$name['map_region']}}" value="{{ old($column['map_region'], $value['map_region']) }}" {!! $attributes !!} />
                @endif
                @if($id['map_address'])
                    <input type="hidden" id="{{$id['map_address']}}" name="{{$name['map_address']}}" value="{{ old($column['map_address'], $value['map_address']) }}" {!! $attributes !!} />
                @endif
                @if($id['polygon'])
                    <input type="hidden" id="{{$id['polygon']}}" name="{{$name['polygon']}}" value="{{ old($column['polygon'], $value['polygon']) }}" {!! $attributes !!} />
                @endif
            </div>
            <div class="col-sm-4">

                <div class="input-group">
                    <input type="text" id="{{$id['address']}}" name="{{$name['address']}}" value="{{ old($column['address'], $value['address']) }}" {!! $attributes !!} class="form-control" style="width:220px;margin-left: 5px;" placeholder="输入地址查询" />
                    <span class="input-group-btn" style="float: left;">
                        <button id="{{$id['address']}}-search" {!! $attributes !!} type="button" class="btn btn-info btn-flat">查询</button>
                    </span>
                </div>

                <div id="addr_list_{{$id['lat'].$id['lng']}}" style="width: 100%;height: 400px;max-height: 400px; margin-top: 10px;" {!! $attributes !!}></div>
            </div>
        </div>
        @include('admin::form.help-block')

    </div>
</div>
