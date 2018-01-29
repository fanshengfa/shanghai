<?php

namespace App\Model;

use DB;
use Encore\Admin\Traits\AdminBuilder;
use Encore\Admin\Traits\ModelTree;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use ModelTree, AdminBuilder;
    protected $table='company';
    protected $primaryKey='id';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'parent_id',
        'parent_ids',
        'level',
        'name',
        'order',
        'contact_name',
        'contact_tel',
    ];

    public $timestamps=true;

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [

    ];

    public function __construct(array $attributes = [])
    {
        $connection = config('admin.database.connection') ?: config('database.default');

        $this->setConnection($connection);

        $this->setTable('company');
        $this->titleColumn = 'name';
        $this->orderColumn = 'order';
        $this->parentColumn = 'parent_id';
        parent::__construct($attributes);
    }

    public function scopeSelectOptions($query)
    {
        $orderColumn = DB::getQueryGrammar()->wrap($this->orderColumn);
        $byOrder = $orderColumn.' = 0,'.$orderColumn;

        $nodes = $query->orderByRaw($byOrder)->get()->toArray();

        $options = $this->buildSelectOptions($nodes, $this->parent_id ?: 0);

        return collect($options)->prepend('', 0)->all();
    }

    public function toTree()
    {
        return $this->buildNestedArray([], $this->parent_id ?: '0');
    }
}


