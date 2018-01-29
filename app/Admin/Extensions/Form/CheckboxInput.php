<?php

namespace App\Admin\Extensions\Form;

use Illuminate\Contracts\Support\Arrayable;
use Encore\Admin\Form\Field\MultipleSelect;
use Encore\Admin\Form\Field\Checkbox;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class CheckboxInput extends Checkbox
{
    protected $view = 'admin.checkbox-input';
    protected $foreignPivotKey = '';
    protected $pivot = [];
    /**
     * Get other key for this many-to-many relation.
     *
     * @throws \Exception
     *
     * @return string
     */
    protected function getforeignPivotKey()
    {
        if ($this->foreignPivotKey) {
            return $this->foreignPivotKey;
        }

        if (is_callable([$this->form->model(), $this->column]) &&
            ($relation = $this->form->model()->{$this->column}()) instanceof BelongsToMany
        ) {
            /* @var BelongsToMany $relation */
            $fullKey = $relation->getQualifiedForeignPivotKeyName();
            return $this->foreignPivotKey = substr($fullKey, strpos($fullKey, '.') + 1);
        }

        throw new \Exception('Column of this field must be a `BelongsToMany` relation.');
    }

    public function fill($data)
    {
        $relations = array_get($data, $this->column);

        if (is_string($relations)) {
            $this->value = explode(',', $relations);
        }

        if (is_array($relations)) {
            if (is_string(current($relations))) {
                $this->value = $relations;
            } else {
                $otherKey = $this->getOtherKey();
                $foreignPivotKey = $this->getforeignPivotKey();
                foreach ($relations as $relation) {
                    $otherKeyVal = array_get($relation, "pivot.{$otherKey}");
                    unset($relation["pivot"][$otherKey]);
                    unset($relation["pivot"][$foreignPivotKey]);
                    $this->value[$otherKeyVal] = array_get($relation, "pivot");
                }
            }
        }
    }
    public function options($options = [])
    {
        if ($options instanceof Arrayable) {
            $options = $options->toArray();
        }

        $this->options = (array) $options;

        return $this;
    }
    public function withPivot($pivot = []) {
        /*
        $relation = $this->form->model()->{$this->column};
        $this->form->model()->with([$relation=>function($query){
            $query->withPivot(['value']);
        }]);
        */
        $this->pivot = $pivot;
        return $this;
    }
    public function render()
    {
        return parent::render()->with('pivot', $this->pivot);
    }
}

