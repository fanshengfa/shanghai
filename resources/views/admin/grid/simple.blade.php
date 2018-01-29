<div>
    <!-- /.box-header -->
    <div class="box-body table-responsive no-padding">
        <table class="table table-hover">
            <?php
            $columns = $grid->columns()->filter(function ($column) {
                return !empty($column->getLabel());
            });
            ?>
            @if(!$columns->isEmpty())
            <tr>
                @foreach($columns as $column)
                    <th>{{$column->getLabel()}}{!! $column->sorter() !!}</th>
                @endforeach
            </tr>
            @endif
            @foreach($grid->rows() as $row)
            <tr {!! $row->getRowAttributes() !!}>
                @foreach($grid->columnNames as $name)
                    <td {!! $row->getColumnAttributes($name) !!}>
                        {!! $row->column($name) !!}
                    </td>
                @endforeach
            </tr>
            @endforeach

            {!! $grid->renderFooter() !!}

        </table>
    </div>
    @if($grid->option('usePagination'))
    <div class="box-footer clearfix">
        {!! $grid->paginator() !!}
    </div>
    @endif
    <!-- /.box-body -->
</div>
