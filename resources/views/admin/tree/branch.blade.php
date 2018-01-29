<tr data-tt-id="{{ $branch[$options['parentids']] }}" data-tt-parent-id="{{ preg_replace("/(,{$branch[$keyName]}$)|(^{$branch[$keyName]}$)/", '', $branch[$options['parentids']]) }}" data-id="{{ $branch[$keyName] }}">
    <?php $tdata=$branchCallback($branch); $fkey=key($tdata); ?>
    @foreach($tdata as $key=>$data)
        @if($fkey == $key)
            <td>
                @if(isset($branch['children']))
                    <span class='folder'>{!! $data !!}</span>
                @else
                    <span class='file'>{!! $data !!}</span>
                @endif
            </td>
        @else
            <td>
                {!! $data !!}
            </td>
        @endif
    @endforeach
    @if($options && $options['columnopt'])
        <td>
            {!! $actionCallback($branch, "<a href='{$path}/{$branch[$keyName]}/edit'><i class='fa fa-edit'></i></a>", "<a data-id='{$branch[$keyName]}' class='tree_branch_delete'><i class='fa fa-trash'></i></a>") !!}
        </td>
    @endif
</tr>
@if(isset($branch['children']))
    @foreach($branch['children'] as $branch)
        @include($branchView, $branch)
    @endforeach
@endif