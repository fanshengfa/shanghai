<div {!! $attributes !!}>
    <div class="box-header with-border">
        <h3 class="box-title">{{ $title }}</h3>
        <div class="box-tools pull-right">
            @foreach($tools as $tool)
                {!! $tool !!}
            @endforeach
        </div><!-- /.box-tools -->
    </div><!-- /.box-header -->
    <div class="box-body" style="display: block;">
        <div id="location-map" style="width:100%;height:300px;"></div>
    </div><!-- /.box-body -->
</div>
<script>
$(document).ready(function(){
    var transform = {
        earthR: 6378137.0,

        outOfChina: function(lng, lat) {
            if ((lng < 72.004) || (lng > 137.8347)) {
                return true;
            }
            if ((lat < 0.8293) || (lat > 55.8271)) {
                return true;
            }
            return false;
        },

        transform: function(x, y) {
            var xy = x * y;
            var absX = Math.sqrt(Math.abs(x));
            var xPi = x * Math.PI;
            var yPi = y * Math.PI;
            var d = 20.0*Math.sin(6.0*xPi) + 20.0*Math.sin(2.0*xPi);

            var lat = d;
            var lng = d;

            lat += 20.0*Math.sin(yPi) + 40.0*Math.sin(yPi/3.0);
            lng += 20.0*Math.sin(xPi) + 40.0*Math.sin(xPi/3.0);

            lat += 160.0*Math.sin(yPi/12.0) + 320*Math.sin(yPi/30.0);
            lng += 150.0*Math.sin(xPi/12.0) + 300.0*Math.sin(xPi/30.0);

            lat *= 2.0 / 3.0;
            lng *= 2.0 / 3.0;

            lat += -100.0 + 2.0*x + 3.0*y + 0.2*y*y + 0.1*xy + 0.2*absX;
            lng += 300.0 + x + 2.0*y + 0.1*x*x + 0.1*xy + 0.1*absX;

            return {lat: lat, lng: lng}
        },

        delta: function(lng, lat) {
            var ee = 0.00669342162296594323;
            var d = this.transform(lng-105.0, lat-35.0);
            var radLat = lat / 180.0 * Math.PI;
            var magic = Math.sin(radLat);
            magic = 1 - ee*magic*magic;
            var sqrtMagic = Math.sqrt(magic);
            d.lat = (d.lat * 180.0) / ((this.earthR * (1 - ee)) / (magic * sqrtMagic) * Math.PI);
            d.lng = (d.lng * 180.0) / (this.earthR / sqrtMagic * Math.cos(radLat) * Math.PI);
            return d;
        },

        WGSToGCJ: function(wgsLng, wgsLat) {
            if (this.outOfChina(wgsLng, wgsLat)) {
                return [wgsLng, wgsLat];
            }
            var d = this.delta(wgsLng, wgsLat);
            return [wgsLng + d.lng, wgsLat + d.lat];
        }

    };

    var points = $.parseJSON('{!! $content !!}');
    AMap.service(["AMap.ToolBar"], function () {
        var map = new AMap.Map("location-map", {
            resizeEnable: true,
            scrollWheel: false,
            zoom:11
            //center: ['.$order->auto->lng.', '.$order->auto->lat.']
        });
        map.addControl(new AMap.ToolBar({ visible: true }));
        for(var i in points) {
            points[i][0] = parseFloat(points[i][0]);
            points[i][1] = parseFloat(points[i][1]);
        }
        var lineArr = points;
        var maxLng = minLng = parseFloat(lineArr[0][0]);
        var maxLat = minLat = parseFloat(lineArr[0][1]);
        var avgLng = avgLat = diffLng = diffLat = 0;
        var zoom = 13;
        for(k in lineArr) {
            lineArr[k] = transform.WGSToGCJ(lineArr[k][0], lineArr[k][1]);
            if(lineArr[k][0]>maxLng) {
                maxLng = lineArr[k][0];
            }
            if(lineArr[k][1]>maxLat) {
                maxLat = lineArr[k][1];
            }
            if(lineArr[k][0]<minLng) {
                minLng = lineArr[k][0];
            }
            if(lineArr[k][1]<minLat) {
                minLat = lineArr[k][1];
            }
        }
        avgLng = (maxLng+minLng)/2;
        avgLat = (maxLat+minLat)/2;
        diffLng = maxLng - minLng;
        diffLat = maxLat - minLat;
        if(diffLng>5 || diffLat>5) {
            zoom = 4
        } else if(diffLng>4 || diffLat>4) {
            zoom = 7
        }  else if(diffLng>3 || diffLat>3) {
            zoom = 10
        }  else if(diffLng>2 || diffLat>2) {
            zoom = 13
        }

        map.setZoomAndCenter(zoom, [avgLng, avgLat]);

        var polyline = new AMap.Polyline({
            path: lineArr,          //设置线覆盖物路径
            strokeColor: "#FF0000", //线颜色
            strokeOpacity: 1,       //线透明度
            strokeWeight: 2,        //线宽
            strokeStyle: "solid",   //线样式
            strokeDasharray: [10, 5] //补充线样式
        });
        polyline.setMap(map);

        var marker = new AMap.Marker({});
        marker.setIcon("/assets/image/truck-map-marker.png");
        marker.setPosition([lineArr[0].lng, lineArr[0].lat]);
        marker.setLabel({
            content: '起点',
        });
        marker.setMap(map);

        var marker = new AMap.Marker({});
        marker.setIcon("/assets/image/truck-map-marker.png");
        marker.setPosition([lineArr[lineArr.length-1].lng, lineArr[lineArr.length-1].lat]);
        marker.setLabel({
            content: '终点',
        });
        marker.setMap(map);
    });
    /*
    if (resp.state == 1 && resp.points.length>0) {
        var lineArr = resp.points;
        var maxLng = minLng = lineArr[0][0];
        var maxLat = minLat = lineArr[0][1];
        var avgLng = avgLat = diffLng = diffLat = 0;
        var zoom = 13;
        for(k in lineArr) {
            lineArr[k] = transform.WGSToGCJ(lineArr[k][0], lineArr[k][1]);
            if(lineArr[k][0]>maxLng) {
                maxLng = lineArr[k][0];
            }
            if(lineArr[k][1]>maxLat) {
                maxLat = lineArr[k][1];
            }
            if(lineArr[k][0]<minLng) {
                minLng = lineArr[k][0];
            }
            if(lineArr[k][1]<minLat) {
                minLat = lineArr[k][1];
            }
        }
        avgLng = (maxLng+minLng)/2;
        avgLat = (maxLat+minLat)/2;
        diffLng = maxLng - minLng;
        diffLat = maxLat - minLat;
        if(diffLng>5 || diffLat>5) {
            zoom = 4
        } else if(diffLng>4 || diffLat>4) {
            zoom = 7
        }  else if(diffLng>3 || diffLat>3) {
            zoom = 10
        }  else if(diffLng>2 || diffLat>2) {
            zoom = 13
        }

        map.setZoomAndCenter(zoom, [avgLng, avgLat]);

        var polyline = new AMap.Polyline({
            path: lineArr,          //设置线覆盖物路径
            strokeColor: "#FF0000", //线颜色
            strokeOpacity: 1,       //线透明度
            strokeWeight: 2,        //线宽
            strokeStyle: "solid",   //线样式
            strokeDasharray: [10, 5] //补充线样式
        });
        polyline.setMap(map);

        marker.setPosition([lineArr[0].lng, lineArr[0].lat]);
        marker.setLabel({
            content: '起点',
        });
        marker.setMap(map);

        marker.setPosition([lineArr[lineArr.length-1].lng, lineArr[lineArr.length-1].lat]);
        marker.setLabel({
            content: '终点',
        });
        marker.setMap(map);

        // 显示位置上报时间
        $("#loc-report-time").text("位置上报时间：" + resp.loc_report_time);
    } else if (resp.state == 1 && resp.auto.lat && resp.auto.lng) {

        // 显示标注点
        var zoom = map.getZoom();

        var lat = resp.auto.lat,
            lng = resp.auto.lng;

        map.remove(marker);
        marker.setIcon('/assets/static/truck-map-marker.png')
        marker.setPosition([lng, lat]);
        map.setZoomAndCenter(zoom > 14 ? zoom : 14, [lng, lat]);
        marker.setMap(map);

        // 显示位置上报时间
        $("#loc-report-time").text("位置上报时间：" + resp.auto.loc_report_time_format);
        getAutoLocation(map, marker, id, orderId);
    }
    */
});
</script>