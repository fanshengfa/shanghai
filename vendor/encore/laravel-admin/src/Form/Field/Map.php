<?php

namespace Encore\Admin\Form\Field;

use Encore\Admin\Form\Field;

class Map extends Field
{
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
        if (config('app.locale') == 'zh-CN') {
            //$js = '//map.qq.com/api/js?v=2.exp';
            $assets = [
                'css'=>[
                    'http://cache.amap.com/lbs/static/main1119.css'
                ],
                'js'=>[
                    '//cache.amap.com/lbs/static/es5.min.js',
                    //'http://webapi.amap.com/maps?v=1.4.0&key=4b0d69421ad2598d617730c720111db7',
                    '//webapi.amap.com/maps?v=1.3&key=4b0d69421ad2598d617730c720111db7',
                    '/assets/js/location-selector-amap.js',
                ]
            ];
            return $assets;
        } else {
            $js = ['//maps.googleapis.com/maps/api/js?v=3.exp&sensor=false&key='.env('GOOGLE_API_KEY')];
        }
        return compact('js');
    }

    public function __construct($column, $arguments)
    {
        $this->column['lat'] = $column;
        $this->column['lng'] = $arguments[0];

        if($arguments[1]) {
            $this->column['province'] = $arguments[1];
        }
        if($arguments[2]) {
            $this->column['city']     = $arguments[2];
        }
        if($arguments[3]) {
            $this->column['region']   = $arguments[3];
        }
        if($arguments[4]) {
            $this->column['address']   = $arguments[4];
        }
        if($arguments[5]) {
            $this->column['map_province'] = $arguments[5];
        }
        if($arguments[6]) {
            $this->column['map_city']     = $arguments[6];
        }
        if($arguments[7]) {
            $this->column['map_region']   = $arguments[7];
        }
        if($arguments[8]) {
            $this->column['map_address']   = $arguments[8];
        }
        if($arguments[9]) {
            $this->column['polygon']   = $arguments[9];
        }

        //array_shift($arguments);

        $this->label = $this->formatLabel([end($arguments)]);
        $this->id = $this->formatId($this->column);

        /*
         * Google map is blocked in mainland China
         * people in China can use Tencent map instead(;
         */
        if (config('app.locale') == 'zh-CN') {
            //$this->useTencentMap();
        } else {
            //$this->useGoogleMap();
        }
    }

    public function useGoogleMap()
    {
        $this->script = <<<EOT
        function initGoogleMap(name) {
            var lat = $('#{$this->id['lat']}');
            var lng = $('#{$this->id['lng']}');

            var LatLng = new google.maps.LatLng(lat.val(), lng.val());

            var options = {
                zoom: 13,
                center: LatLng,
                panControl: false,
                zoomControl: true,
                scaleControl: true,
                mapTypeId: google.maps.MapTypeId.ROADMAP
            }

            var container = document.getElementById("map_"+name);
            var map = new google.maps.Map(container, options);

            var marker = new google.maps.Marker({
                position: LatLng,
                map: map,
                title: 'Drag Me!',
                draggable: true
            });

            google.maps.event.addListener(marker, 'dragend', function (event) {
                lat.val(event.latLng.lat());
                lng.val(event.latLng.lng());
            });
        }

        initGoogleMap('{$this->id['lat']}{$this->id['lng']}');
EOT;
    }

    public function useTencentMap()
    {
        $lat = $this->attributes['data-tk'] ? "input[id={$this->id['lat']}][data-tk={$this->attributes['data-tk']}]" : "#{$this->id['lat']}";
        $lng = $this->attributes['data-tk'] ? "input[id={$this->id['lng']}][data-tk={$this->attributes['data-tk']}]" : "#{$this->id['lng']}";
        $map = $this->attributes['data-tk'] ? "div[id=map_{$this->id['lat']}{$this->id['lng']}][data-tk={$this->attributes['data-tk']}]" : "#map_{$this->id['lat']}{$this->id['lng']}";
        $province = '';
        $city = '';
        $region = '';
        if($this->id['province']) {
            $province = $this->attributes['data-tk'] ? "input[id={$this->id['province']}][data-tk={$this->attributes['data-tk']}]" : "#{$this->id['province']}";
        }
        if($this->id['city']) {
            $city = $this->attributes['data-tk'] ? "input[id={$this->id['city']}][data-tk={$this->attributes['data-tk']}]" : "#{$this->id['city']}";
        }
        if($this->id['region']) {
            $region = $this->attributes['data-tk'] ? "input[id={$this->id['region']}][data-tk={$this->attributes['data-tk']}]" : "#{$this->id['region']}";
        }
        $this->script = <<<EOT
        function initTencentMap(lat, lng, map, province, city, region) {
            var lat = $(lat);
            var lng = $(lng);

            var center = new qq.maps.LatLng(lat.val(), lng.val());

            var container = $(map)[0];
            var map = new qq.maps.Map(container, {
                center: center,
                zoom: 13
            });

            var marker = new qq.maps.Marker({
                position: center,
                draggable: true,
                map: map
            });

            if( ! lat.val() || ! lng.val()) {
                var citylocation = new qq.maps.CityService({
                    complete : function(result){
                        map.setCenter(result.detail.latLng);
                        marker.setPosition(result.detail.latLng);
                    }
                });

                citylocation.searchLocalCity();
            }

            qq.maps.event.addListener(map, 'click', function(event) {
                marker.setPosition(event.latLng);
            });

            qq.maps.event.addListener(marker, 'position_changed', function(event) {
                var position = marker.getPosition();
                lat.val(position.getLat());
                lng.val(position.getLng());
                console.log(position);
                var geocoder = new qq.maps.Geocoder({
                    complete: function(result){
                        if(province) {
                            $(province).val();
                        }
                        console.log(result);
                    }
                });
                var latLng = new qq.maps.LatLng(position.getLat(), position.getLng());
                console.log(geocoder.getAddress(latLng));
            });
        }

        initTencentMap('{$lat}', '{$lng}', '{$map}', '{$province}', '{$city}', '{$region}');
EOT;
    }

    public function useAmapMap() {
        $lat = $this->attributes['data-tk'] ? "input[id={$this->id['lat']}][data-tk={$this->attributes['data-tk']}]" : "#{$this->id['lat']}";
        $lng = $this->attributes['data-tk'] ? "input[id={$this->id['lng']}][data-tk={$this->attributes['data-tk']}]" : "#{$this->id['lng']}";
        $map = $this->attributes['data-tk'] ? "div[id=map_{$this->id['lat']}{$this->id['lng']}][data-tk={$this->attributes['data-tk']}]" : "#map_{$this->id['lat']}{$this->id['lng']}";
        $province = '';
        $city = '';
        $region = '';
        $address = '';
        $map_province = '';
        $map_city = '';
        $map_region = '';
        $map_address = '';
        $polygon = '';
        if($this->id['province']) {
            $province = $this->attributes['data-tk'] ? "select[id={$this->id['province']}][data-tk={$this->attributes['data-tk']}]" : "#{$this->id['province']}";
        }
        if($this->id['city']) {
            $city = $this->attributes['data-tk'] ? "select[id={$this->id['city']}][data-tk={$this->attributes['data-tk']}]" : "#{$this->id['city']}";
        }
        if($this->id['region']) {
            $region = $this->attributes['data-tk'] ? "select[id={$this->id['region']}][data-tk={$this->attributes['data-tk']}]" : "#{$this->id['region']}";
        }
        if($this->id['address']) {
            $address = $this->attributes['data-tk'] ? "input[id={$this->id['address']}][data-tk={$this->attributes['data-tk']}]" : "#{$this->id['address']}";
        }

        if($this->id['map_province']) {
            $map_province = $this->attributes['data-tk'] ? "input[id={$this->id['map_province']}][data-tk={$this->attributes['data-tk']}]" : "#{$this->id['map_province']}";
        }
        if($this->id['map_city']) {
            $map_city = $this->attributes['data-tk'] ? "input[id={$this->id['map_city']}][data-tk={$this->attributes['data-tk']}]" : "#{$this->id['map_city']}";
        }
        if($this->id['map_region']) {
            $map_region = $this->attributes['data-tk'] ? "input[id={$this->id['map_region']}][data-tk={$this->attributes['data-tk']}]" : "#{$this->id['map_region']}";
        }
        if($this->id['map_address']) {
            $map_address = $this->attributes['data-tk'] ? "input[id={$this->id['map_address']}][data-tk={$this->attributes['data-tk']}]" : "#{$this->id['map_address']}";
        }

        if($this->id['polygon']) {
            $polygon = $this->attributes['data-tk'] ? "input[id={$this->id['polygon']}][data-tk={$this->attributes['data-tk']}]" : "#{$this->id['polygon']}";
        }


        $addr_list = $this->attributes['data-tk'] ? "div[id=addr_list_{$this->id['lat']}{$this->id['lng']}][data-tk={$this->attributes['data-tk']}]" : "#addr_list_{$this->id['lat']}{$this->id['lng']}";

        $this->script = <<<EOT
        
        initAmapMap('{$lat}', '{$lng}', '{$map}', '{$province}', '{$city}', '{$region}', '{$address}', '{$map_province}', '{$map_city}', '{$map_region}', '{$map_address}', '{$polygon}', '{$addr_list}');
EOT;

    }

    public function render()
    {
        if (config('app.locale') == 'zh-CN') {
            $this->useAmapMap();
            //$this->useTencentMap();
        } else {
            $this->useGoogleMap();
        }
        return parent::render();//->with(['name'=>$this->formatName($this->column)]);
    }
}
