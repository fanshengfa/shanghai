<?php

namespace App\Fcore;

use Closure;
use App\Fcore\Layout\Content;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Support\Facades\Auth;
use InvalidArgumentException;

/**
 * Class Fast.
 */
class Fast
{
    /**
     * @param $model
     * @param Closure $callable
     *
     * @return \Encore\Admin\Grid
     */
    public function grid($model, Closure $callable)
    {
        return new Grid($this->getModel($model), $callable);
    }

    /**
     * @param $model
     * @param Closure $callable
     *
     * @return \Encore\Admin\Form
     */
    public function form($model, Closure $callable)
    {
        return new Form($this->getModel($model), $callable);
    }

    /**
     * Build a tree.
     *
     * @param $model
     *
     * @return \Encore\Admin\Tree
     */
    public function tree($model, Closure $callable = null)
    {
        return new Tree($this->getModel($model), $callable);
    }

    /**
     * @param Closure $callable
     *
     * @return \Encore\Admin\Layout\Content
     */
    public function content(Closure $callable = null)
    {
        return new Content($callable);
    }

    /**
     * @param $model
     *
     * @return mixed
     */
    public function getModel($model)
    {
        if ($model instanceof EloquentModel) {
            return $model;
        }

        if (is_string($model) && class_exists($model)) {
            return $this->getModel(new $model());
        }

        throw new InvalidArgumentException("$model is not a valid model");
    }

    /**
     * Get current login user.
     *
     * @return mixed
     */
    public function user()
    {
        return Auth::guard('admin')->user();
    }

}
