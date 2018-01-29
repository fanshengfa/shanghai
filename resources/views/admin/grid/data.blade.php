<!-- /.box-header -->
<div class="box-body table-responsive no-padding">
    <table class="table table-hover">
        <?php $columns = $grid->columns();?>
        @foreach($grid->rows() as $row)
            @foreach($grid->columnNames as $key=>$name)
                <tr {!! $row->getRowAttributes() !!}>
                    <td style="text-align:left;">
                        {{$columns[$key]->getLabel()}}
                    </td>
                    <td {!! $row->getColumnAttributes($name) !!} style="text-align:right;">
                        {!! $row->column($name) !!}
                    </td>
                </tr>
            @endforeach
        @endforeach
    </table>
</div>
<!-- /.box-body -->