<div class="{{$viewClass['form-group']}} {!! !$errors->has($column) ?: 'has-error' !!}">

    <label for="{{$id}}" class="{{$viewClass['label']}} control-label">{{$label}}</label>

    <div class="{{$viewClass['field']}}" id="{{$id}}">

        @include('admin::form.error')
        <?php $pivot = $pivot ? $pivot : [];?>
        @foreach($options as $option => $label)
            <div class="checkbox">
                <div class="row">
                    <div class="col-xs-3">
                        <input type="checkbox" data-name="{{$name}}[{{$option}}]" value="{{$option}}" class="{{$class}}" {{ isset($value[$option])?'checked':'' }} {!! $attributes !!} />&nbsp;{{$label}}
                    </div>
                    <div class="col-xs-6">
                    @if(isset($value[$option]) && !empty($value[$option]))
                        @foreach($value[$option] as $k=>$v)
                            <input type="text" name="{{$name}}[{{$option}}][{{$k}}]" data-name="{{$name}}[{{$option}}][{{$k}}]" value="{{$v}}"/>
                        @endforeach
                    @else
                        @foreach($pivot as $k)
                            <input type="text" name=""                               data-name="{{$name}}[{{$option}}][{{$k}}]" value=""/>
                        @endforeach
                    @endif
                    </div>
                </div>
            </div>
        @endforeach

        <input type="hidden" name="{{$name}}[]">

        @include('admin::form.help-block')

    </div>
</div>
<script>
    $(".{{$class}}").on('ifChanged', function () {
        var dataname = $(this).attr('data-name');
        if($(this).is(':checked')){
            $('input[data-name^="'+dataname+'["]').each(function(){
                $(this).attr('name', $(this).attr('data-name'));
            });
        } else {
            $('input[data-name^="'+dataname+'["]').each(function(){
                $(this).attr('name', '');
            });
        }
    });
</script>