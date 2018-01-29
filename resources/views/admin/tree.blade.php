<div class="box">
    <div class="box-header">
        <h3 class="box-title"></h3>
        <div class="btn-group">
            <a class="btn btn-primary btn-sm {{ $id }}-tree-tools" data-action="expand" id="expand-{{$id}}">
                <i class="fa fa-plus-square-o"></i>&nbsp;{{ trans('admin.expand') }}
            </a>
            <a class="btn btn-primary btn-sm {{ $id }}-tree-tools" data-action="collapse" id="collapse-{{$id}}">
                <i class="fa fa-minus-square-o"></i>&nbsp;{{ trans('admin.collapse') }}
            </a>
        </div>
        <div class="btn-group">
            <a class="btn btn-warning btn-sm {{ $id }}-refresh" id="refresh-{{$id}}">
                <i class="fa fa-refresh"></i>&nbsp;{{ trans('admin.refresh') }}
            </a>
        </div>
        @if($useCreate)
            <div class="btn-group pull-right">
                <a class="btn btn-success btn-sm" href="{{ $path }}/create"><i class="fa fa-save"></i>&nbsp;{{ trans('admin.new') }}</a>
            </div>
        @endif
    </div>
    <!-- /.box-header -->
    <div class="box-body table-responsive no-padding">
        <table id="{{ $id }}" class="table">
            <thead>
            <tr>
                @if($headerCallback instanceof \Closure)
                @foreach(call_user_func($headerCallback) as $header=>$attr)
                    <th>{{ $header }}</th>
                @endforeach
                @endif
                @if($options && $options['columnopt'])
                <th style="width:100px;">操作</th>
                @endif
            </tr>
            </thead>
            <tbody>
                @each($branchView, $items, 'branch')
            </tbody>
        </table>
    </div>
    <!-- /.box-body -->
</div>