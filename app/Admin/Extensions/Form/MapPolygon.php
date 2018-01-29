<?php

namespace App\Admin\Extensions\Form;

use Encore\Admin\Form\Field;

class MapPolygon extends Field
{
    protected $view = 'admin.map-polygon';

    /**
     * Column name.
     *
     * @var array
     */
    protected $column = [];

    /**
     * Get assets required by this field.
     *
     * @return array
     */
    public static function getAssets()
    {
        $assets = [
            'css'=>[
                'http://cache.amap.com/lbs/static/main1119.css'
            ],
            'js'=>[
                '//cache.amap.com/lbs/static/es5.min.js',
                //'http://webapi.amap.com/maps?v=1.4.0&key=4b0d69421ad2598d617730c720111db7',
                '//webapi.amap.com/maps?v=1.3&key=4b0d69421ad2598d617730c720111db7',
                '/assets/js/map-polygon-amap.js',
            ]
        ];
        return $assets;
    }

    public function __construct($column, $arguments)
    {
        $this->column['polygon'] = $column;

        //array_shift($arguments);

        $this->label = $this->formatLabel([end($arguments)]);
        $this->id = $this->formatId($this->column);
    }

    public function useAmapMap() {
        $map = $this->attributes['data-tk'] ? "div[id=map_{$this->id['polygon']}][data-tk={$this->attributes['data-tk']}]" : "#map_{$this->id['polygon']}";
        $polygon = $this->attributes['data-tk'] ? "input[id={$this->id['polygon']}][data-tk={$this->attributes['data-tk']}]" : "#{$this->id['polygon']}";
        $addr_list = $this->attributes['data-tk'] ? "div[id=addr_list_{$this->id['polygon']}][data-tk={$this->attributes['data-tk']}]" : "#addr_list_{$this->id['polygon']}";
        $options = \json_encode($this->options);
        $this->script = <<<EOT
        
        mappolygonAmapMap('{$map}', '{$polygon}', '{$addr_list}', $options);
EOT;

    }

    public function render()
    {
        $this->useAmapMap();
        return parent::render();
    }
}
