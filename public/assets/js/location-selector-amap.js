function initAmapMap(lat, lng, map, province, city, region, address, map_province, map_city, map_region, map_address, polygon, addr_list) {
    console.log(polygon);
    //地区码
    var amapAdcode = {};
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
        amapAdcode._district = new AMap.DistrictSearch({//高德行政区划查询插件实例
            subdistrict: 1   //返回下一级行政区
        });

        //构建地区下拉菜单
        amapAdcode.createSelectList = function($selectId, list) {//生成下拉列表
            var html = "<option value=''>--请选择--</option>";
            for (var i = 0, l = list.length, option; i < l; i++) {
                html += "<option data-code='"+list[i].adcode+"' data-text='"+list[i].name+"' value='"+list[i].name+"'>"+list[i].name+"</option>";
            }
            $selectId.html(html);
        };

        //搜索并将选择地点做为中心显示
        amapAdcode.search = function(adcodeLevel, keyword, $selectId, callback) {//查询行政区划列表并生成相应的下拉列表
            var me = this;
            //第三级时查询边界点
            if (adcodeLevel == 'district' || adcodeLevel == 'city') {
                this._district.setExtensions('all');
            } else {
                this._district.setExtensions('base');
            }
            //行政区级别
            this._district.setLevel(adcodeLevel);

            //注意，api返回的格式不统一，在下面用三个条件分别处理
            this._district.search(keyword, function(status, result) {
                if (typeof(result.districtList) == "undefined") {
                    return;
                }
                var districtData = result.districtList[0];
                if (typeof($selectId) != "undefined") {
                    if (districtData.districtList) {
                        me.createSelectList($selectId, districtData.districtList);
                    } else if (districtData.districts) {
                        me.createSelectList($selectId, districtData.districts);
                    } else {
                        $($selectId).html("");
                    }
                }
                if(adcodeLevel == 'country') {
                    level = 6;
                } else if (adcodeLevel == 'province') {
                    level = 8;
                } else if(adcodeLevel == 'city') {
                    level = 10;
                } else if(adcodeLevel == 'district') {
                    level = 12;
                }
                map.setZoomAndCenter(level, districtData.center);
                callback && callback();
            });
        };

        // 清空下拉列表
        amapAdcode.clear = function($selectId) {
            $selectId.html("");
        };

        // 创建省列表
        amapAdcode.createProvince = function(callback) {
            $(province).html("");
            this.search('country', '中国', $(province), callback);
        };

        // 创建市列表
        amapAdcode.createCity = function(provinceAdcode, callback) {
            this.search('province', provinceAdcode, $(city), callback);
        };

        // 创建区域
        amapAdcode.createDistrict = function(cityAdcode, callback) {//创建区县列表
            this.search('city', cityAdcode, $(region), callback);
        }

        amapAdcode.createProvince(function(){
            $(province).find('option[data-text="'+$(province).attr('data-value')+'"]').attr("selected", "selected");
            if($(province).find("option:selected").text()==$(province).attr('data-value')) {
                amapAdcode.createCity($(province).find("option:selected").val(), function(){
                    $(city).find('option[data-text="'+$(city).attr('data-value')+'"]').attr("selected", "selected");
                    if($(city).find("option:selected").text()==$(city).attr('data-value')) {
                        amapAdcode.createDistrict($(city).find("option:selected").val(), function(){
                            $(region).find('option[data-text="'+$(region).attr('data-value')+'"]').attr("selected", "selected");
                            search($(city).find("option:selected").attr('data-code'), $(region).find("option:selected").attr('data-text'), $(address).val());
                        });
                    }
                });
            }
        });
    });

    var search = function(city, region, address) {
        // 搜索地点
        var placeSearch = new AMap.PlaceSearch({
            pageSize: 3,
            pageIndex: 1,
            citylimit: true,
            city: city, //城市
            map: map,
            panel: $(addr_list)[0]
        });
        // 查询详细地址
        placeSearch.search(region + ' ' + address, function(status, result) {
            // 绑定地址列表点击事件
            $(addr_list+" .poibox").unbind("click").bind("click", function (e) {
                var index = parseInt($(this).find(".amap_lib_placeSearch_poi").text())-1;
                var selectedPoi;

                selectedPoi = result.poiList.pois[index];
                $(lat).val(selectedPoi.location.lat);
                $(lng).val(selectedPoi.location.lng);
                $(map_province).val(selectedPoi.pname);
                $(map_city).val(selectedPoi.cityname);
                $(map_region).val(selectedPoi.adname);
                $(map_address).val(selectedPoi.name);
                console.log(selectedPoi);
            });
        });
    }
    // 选择省份
    $(province).change(function () {
        amapAdcode.clear($(city));
        if ($(this).val() != "") {
            amapAdcode.createCity($(this).val());
            amapAdcode.clear($(region));
        }
    });

    // 选择城市
    $(city).change(function () {
        amapAdcode.createDistrict($(this).val());
        amapAdcode.search('city', $(this).val());
    });

    // 选择区
    $(region).change(function () {
        amapAdcode.search('district', $(this).val());
    });

    $('button[id='+$(address).attr('id')+'-search'+'][data-tk='+$(address).attr('data-tk')+']').click(function(){
        $(addr_list).html();
        search($(city).find("option:selected").attr('data-code'), $(region).find("option:selected").attr('data-text'), $(address).val());
    });

    /*
    setTimeout(function(){
        // 详细位置查询
        if($(city).find("option:selected").attr('data-code') && $(region).find("option:selected").attr('data-text')) {
            search($(city).find("option:selected").attr('data-code'), $(region).find("option:selected").attr('data-text'), $(address).val());
        } else {
            //search('北京', '西城区', '新街口');
        }
    }, 500000);
    */

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
            //AMap.event.removeListener(self.mapClickListener);
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
        var strify = JSON.stringify(res.target.getPath());
        $(polygon).val(strify);
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
    draw.init();
}