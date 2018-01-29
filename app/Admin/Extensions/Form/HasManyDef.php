<?php

namespace App\Admin\Extensions\Form;

use Encore\Admin\Admin;
use Encore\Admin\Form;
use Encore\Admin\Form\Field;
use Encore\Admin\Form\Field\HasMany;
use Encore\Admin\Form\NestedForm;
use Illuminate\Database\Eloquent\Relations\HasMany as Relation;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

/**
 * Class HasMany.
 */
class HasManyDef extends HasMany
{
    protected $views = [
        'default'   => 'admin.hasmany-def',
        'tab'       => 'admin::form.hasmanytab',
    ];
    /**
     * Create a new HasMany field instance.
     *
     * @param $relationName
     * @param array $arguments
     */
    public function __construct($relationName, $arguments = [])
    {
        parent::__construct($relationName, $arguments);
    }

    /**
     * Build Nested form for related data.
     *
     * @throws \Exception
     *
     * @return array
     */
    protected function buildRelatedForms()
    {
        if (is_null($this->form)) {
            return [];
        }

        $model = $this->form->model();

        $relation = call_user_func([$model, $this->relationName]);

        if (!$relation instanceof Relation && !$relation instanceof MorphMany) {
            throw new \Exception('hasMany field must be a HasMany or MorphMany relation.');
        }

        $forms = [];

        /*
         * If redirect from `exception` or `validation error` page.
         *
         * Then get form data from session flash.
         *
         * Else get data from database.
         */
        if ($values = old($this->column)) {
            foreach ($values as $key => $data) {
                if ($data[NestedForm::REMOVE_FLAG_NAME] == 1) {
                    continue;
                }

                $forms[$key] = $this->buildNestedForm($this->column, $this->builder, $key)
                    ->fill($data);
            }
        } elseif($this->value) {
            foreach ($this->value as $data) {
                $key = array_get($data, $relation->getRelated()->getKeyName());

                $forms[$key] = $this->buildNestedForm($this->column, $this->builder, $key)
                    ->fill($data);
            }
        } elseif($this->getDefault()) {
            foreach ($this->getDefault() as $data) {
                $key = array_get($data, $relation->getRelated()->getKeyName());

                $forms[$key] = $this->buildNestedForm($this->column, $this->builder, $key)
                    ->fill($data);
            }
        }

        return $forms;
    }

    /**
     * Setup default template script.
     *
     * @param string $templateScript
     *
     * @return void
     */
    protected function setupScriptForDefaultView($templateScript)
    {
        parent::setupScriptForDefaultView($templateScript);
        $script = <<<EOT
$('#has-many-{$this->column}').on('click', '.moveup', function () {
    var hasManyColumnForm = $(this).closest('.has-many-{$this->column}-form');
    hasManyColumnForm.prev().before(hasManyColumnForm);
});

$('#has-many-{$this->column}').on('click', '.movedown', function () {
    var hasManyColumnForm = $(this).closest('.has-many-{$this->column}-form');
    hasManyColumnForm.next().after(hasManyColumnForm);
});
EOT;

        Admin::script($script);
    }

    public function render()
    {
        return parent::render()->with([
            'options' => $this->options
        ]);
    }
}
