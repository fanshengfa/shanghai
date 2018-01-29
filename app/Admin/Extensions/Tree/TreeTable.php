<?php

namespace App\Admin\Extensions\Tree;

use Encore\Admin\Tree;
use Encore\Admin\Facades\Admin;

class TreeTable extends Tree
{
    /**
     * Options for specify elements.
     *
     * @var array
     */
    protected $options = [
        'columnopt' => true,
        'parentids' => 'parent_ids'
    ];

    /**
     * @var null
     */
    protected $actionCallback = null;

    /**
     * @var null
     */
    protected $headerCallback = null;

    /**
     * View of tree to render.
     *
     * @var string
     */
    protected $view = [
        'tree'      => 'admin.tree',
        'branch'    => 'admin.tree.branch',
    ];

    /**
     * Get assets required by this field.
     *
     * @return array
     */
    public static function getAssets()
    {
        $assets = [
            'css'=>[
                '/assets/css/treetable/screen.css',
                '/assets/css/treetable/jquery.treetable.css',
                '/assets/css/treetable/jquery.treetable.theme.default.css'
            ],
            'js'=>[
                '/assets/js/jquery.treetable.js',
            ]
        ];
        return $assets;
    }

    /**
     * Build tree grid scripts.
     *
     * @return string
     */
    protected function script()
    {
        $deleteConfirm = trans('admin.delete_confirm');
        $saveSucceeded = trans('admin.save_succeeded');
        $refreshSucceeded = trans('admin.refresh_succeeded');
        $deleteSucceeded = trans('admin.delete_succeeded');
        $confirm = trans('admin.confirm');
        $cancel = trans('admin.cancel');
        return <<<SCRIPT
        $("#{$this->elementId}").treetable({ expandable: true, initialState:'expand' });
        $("#expand-{$this->elementId}").click(function(){
            $("#{$this->elementId}").treetable('expandAll');
        });
        $("#collapse-{$this->elementId}").click(function(){
            $("#{$this->elementId}").treetable('collapseAll');
        });

        $('#{$this->elementId} .tree_branch_delete').click(function() {
            var id = $(this).data('id');
            swal({
              title: "$deleteConfirm",
              type: "warning",
              showCancelButton: true,
              confirmButtonColor: "#DD6B55",
              confirmButtonText: "$confirm",
              closeOnConfirm: false,
              cancelButtonText: "$cancel"
            },
            function(){
                $.ajax({
                    method: 'post',
                    url: '{$this->path}/' + id,
                    data: {
                        _method:'delete',
                        _token:LA.token,
                    },
                    success: function (data) {
                        $.pjax.reload('#pjax-container');

                        if (typeof data === 'object') {
                            if (data.status) {
                                swal(data.message, '', 'success');
                            } else {
                                swal(data.message, '', 'error');
                            }
                        }
                    }
                });
            });
        });

        $('#refresh-{$this->elementId}').click(function () {
            $.pjax.reload('#pjax-container');
            toastr.success('{$refreshSucceeded}');
        });
SCRIPT;
    }

    /**
     * Render a tree.
     *
     * @return \Illuminate\Http\JsonResponse|string
     */
    public function render()
    {
        Admin::script($this->script());

        view()->share([
            'path'           => $this->path,
            'keyName'        => $this->model->getKeyName(),
            'branchView'     => $this->view['branch'],
            'branchCallback' => $this->branchCallback,
            'headerCallback' => $this->headerCallback,
            'actionCallback' => $this->actionCallback,
            'options'        => $this->options,
        ]);

        return view($this->view['tree'], $this->variables())->render();
    }

    /**
     * Set branch callback.
     *
     * @param \Closure $headerCallback
     *
     * @return $this
     */
    public function header(\Closure $headerCallback)
    {
        $this->headerCallback = $headerCallback;

        return $this;
    }

    /**
     * Set branch callback.
     *
     * @param \Closure $actionCallback
     *
     * @return $this
     */
    public function action(\Closure $actionCallback)
    {
        $this->actionCallback = $actionCallback;

        return $this;
    }

    /**
     * Set the field options.
     *
     * @param array $options
     *
     * @return $this
     */
    public function options($options = [])
    {
        if ($options instanceof Arrayable) {
            $options = $options->toArray();
        }

        $this->options = array_merge($this->options, $options);

        return $this;
    }

    public function __call($name, $arguments) {
        if(isset($this->options[$name]) && isset($arguments[0])) {
            $this->options([$name=>$arguments[0]]);
        }
        return $this;
    }
}