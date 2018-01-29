function mappolygonAmapMap(map, polygon, addr_list, options) {
    console.log(polygon);
    console.log(options);
    //地区码
    var geoCoder = {};
    var container = $(map)[0];
    // 打开选择框的元素
    var opener;
    // 当前位置经纬度, 查询出的省份、城市、地区、详细地址
    var currLat, currLng, currProvince, currCity, currRegion, currAddress;
    var map = new AMap.Map(container, {
        resizeEnable: true,
        scrollWheel: false,
        zoom: 3
    });
    // 行政区划插件
    AMap.service(["AMap.ToolBar", "AMap.DistrictSearch", "AMap.PlaceSearch", "AMap.PolyEditor"], function () {
        //添加工具条
        map.addControl(new AMap.ToolBar({ visible: true }));
    });

    geoCoder.search = function(name, citycode, callback) {
        citycode = citycode || '';
        map.plugin(["AMap.Geocoder"], function() {     //加载地理编码插件
            if(citycode) {
                var MGeocoder = new AMap.Geocoder({
                    city: citycode,
                    radius:200 //范围，默认：500
                });
            } else {
                var MGeocoder = new AMap.Geocoder({
                    radius:200 //范围，默认：500
                });
            }
            AMap.event.addListener(MGeocoder, "complete", function(data){
                var geocode = data.geocodes;
                if(geocode.length == 0) {
                    return;
                }
                var lngX = geocode[0].location.getLng();
                var latY = geocode[0].location.getLat();
                map.setZoomAndCenter(12, new AMap.LngLat(lngX, latY));
                callback && callback(lngX, latY, geocode[0].addressComponent.citycode);
            });
            MGeocoder.getLocation(name);
        });
    };

    var draw = {
        map: '',
        markPoints : [],
        marks : [],
        mapClickListener : '',
        polygon: '',
        polygonEditor : '',
        polygonEditorOpen: false
    };
    draw.init = function() {
        var self = this;
        self.map = map;
        self.markPoints = [];
        self.marks = [];
        self.polygonEditor = '';
        self.mapClickListener = AMap.event.addListener(map, "click", self.mapOnClick.bind(self));
        self.markPoints = self.json2arr($(polygon).val());
        self.createEditor();
        $(container).after(function () {
            return $('<div class="button-group"></div>').append(function(){
                if(self.markPoints.length>=3) {
                    var value = '编辑完成';
                    var dataopt = 'edit';
                } else {
                    var value = '开始编辑';
                    var dataopt = 'start';
                }
                return $('<input type="button" class="button" value="'+value+'" data-opt="'+dataopt+'"/>').click(function(){
                    if($(this).attr('data-opt')=='edit') {
                        $(this).attr('data-opt', 'start');
                        $(this).val('开始编辑');
                        self.polygonEditor && self.polygonEditor.close();
                    } else if($(this).attr('data-opt')=='start') {
                        $(this).attr('data-opt', 'edit');
                        $(this).val('编辑完成');
                        self.polygonEditor && self.polygonEditor.open();
                    }
                });
            }).append(function(){
                return '&nbsp;&nbsp;'
            }).append(function(){
                return $('<input type="button" class="button" value="重置"/>').click(function(){
                    self.markPoints = [];
                    self.clearMarks();
                    self.polygonEditor && self.polygonEditor.close();
                    self.map.remove(self.polygon);
                    self.polygonEditor = null;
                    self.polygon = null;
                });
            });
        });
    };
    draw.mapOnClick = function (e) {
        // document.getElementById("lnglat").value = e.lnglat.getLng() + ',' + e.lnglat.getLat()
        var self = this;
        if(self.markPoints.length>=3) {
            return;
        }
        var marker = new AMap.Marker({
            icon: "http://webapi.amap.com/theme/v1.3/markers/n/mark_b.png",
            position: e.lnglat
        });
        marker.setMap(map);
        self.marks.push(marker);
        self.markPoints.push(e.lnglat);
        if (self.markPoints.length == 3) {
            self.createEditor();
            self.clearMarks();
        }
    };
    draw.createEditor = function() {
        var self = this;
        var points = self.markPoints.length ? self.markPoints : [];
        if(points.length<3 || self.polygonEditor) {
            return;
        }
        self.polygon = new AMap.Polygon({
            map: map,
            path: points,
            strokeColor: "#0000ff",
            strokeOpacity: 1,
            strokeWeight: 3,
            fillColor: "#f5deb3",
            fillOpacity: 0.35
        });
        self.polygonEditor = new AMap.PolyEditor(map, self.polygon);
        self.polygonEditor.open();
        AMap.event.addListener(self.polygonEditor, 'end', self.polygonEnd);
    };
    draw.polygonEnd = function(res) {
        var data = [];
        var path = res.target.getPath();
        for(var k in path) {
            data[k] = {lat:path[k].lat, lng:path[k].lng};
        }
        $(polygon).val(JSON.stringify(data));
    };
    draw.clearMarks = function() {
        var self = this;
        map.remove(self.marks);
        self.marks = [];
    };
    draw.json2arr = function(json) {
        try {
            var arr = JSON.parse(json);
        } catch (e) {
            var arr = [];
        }
        var res = [];
        if(arr && arr.length>0) {
            for (var i = 0; i < arr.length; i++) {
                var line = [];
                line.push(arr[i].lng);
                line.push(arr[i].lat);
                res.push(line);
            }
        }
        return res;
    };


    $(document).ready(function(){
        draw.init();
        if(options.province && options.city && options.region) {
            var name = $(options.province).find("option:selected").text()+$(options.city).find("option:selected").text()+$(options.region).find("option:selected").text();
            geoCoder.search(name, false, function (lng, lat, citycode) {
                map.setZoomAndCenter(12, new AMap.LngLat(lng, lat));
            });
        } else if(options.province && options.city) {
            var name = $(options.province).find("option:selected").text() + $(options.city).find("option:selected").text();
            geoCoder.search(name, false, function (lng, lat, citycode) {
                map.setZoomAndCenter(10, new AMap.LngLat(lng, lat));
            });
        } else if(options.province) {
            var name = $(options.province).find("option:selected").text();
            geoCoder.search(name, false, function (lng, lat, citycode) {
                map.setZoomAndCenter(8, new AMap.LngLat(lng, lat));
            });
        }
        if(options.address && options.address_search) {
            var placeSearch;
            $(options.address_search).click(function(){
                var name = $(options.province).find("option:selected").text()+$(options.city).find("option:selected").text()+$(options.region).find("option:selected").text();
                geoCoder.search(name, false, function (lng, lat, citycode) {
                    map.setZoomAndCenter(12, new AMap.LngLat(lng, lat));
                    if(placeSearch) {
                        placeSearch.clear();
                    }
                    placeSearch = new AMap.PlaceSearch({
                        pageSize: 3,
                        pageIndex: 1,
                        citylimit: true,
                        city: citycode,
                        map: map,
                        panel: $(addr_list)[0]
                    });
                    // 查询详细地址
                    placeSearch.search($(options.address).val(), function(status, result) {
                        // 绑定地址列表点击事件
                        $(addr_list+" .poibox").unbind("click").bind("click", function (e) {
                            var index = parseInt($(this).find(".amap_lib_placeSearch_poi").text())-1;
                            var selectedPoi = result.poiList.pois[index];
                            console.log(selectedPoi);
                        });
                    });
                    $(addr_list).html("");
                });
            });
        }
        // 选择省份
        if(options.province) {
            $(options.province).change(function () {
                var self = this;
                geoCoder.search($(self).find("option:selected").text(), false, function (lng, lat, citycode) {
                    map.setZoomAndCenter(8, new AMap.LngLat(lng, lat));
                });
            });
        }
        // 选择城市
        if(options.city) {
            $(options.city).change(function () {
                var self = this;
                geoCoder.search($(self).find("option:selected").text(), $(options.province).find("option:selected").text(), function (lng, lat, citycode) {
                    map.setZoomAndCenter(10, new AMap.LngLat(lng, lat));
                });
            });
        }
        // 选择区县
        if(options.region) {
            $(options.region).change(function () {
                var self = this;
                var citycode = $(options.province).find("option:selected").text()+$(options.city).find("option:selected").text();
                geoCoder.search($(self).find("option:selected").text(), citycode, function (lng, lat, citycode) {
                    map.setZoomAndCenter(12, new AMap.LngLat(lng, lat));
                });
            });
        }
    });
}