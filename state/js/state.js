var layers_veredales = '';

$(document).ready(function() {
    $('#select_state').change(function() {
        $('#select_city').html('<option>Loading....</option>');

        var _u = document.URL;

        var _relat = '';

        if (_u.indexOf('admin') > 0) {
            _relat = '../../../';
        }
        else if (_u.indexOf('submit') > 0) {
            _relat = '../';
        }

        var _path = _relat + 'state/cities/';
        
        var _url = _path + $(this).val();
        
        $.ajax(
        {
            url: _url,
            success: function(data) {
                var cities = data;
                $('#select_city').html('<option value="">Selecciona la ciudad</option>');
                $.each(cities, function(i, v){
                    $('#select_city').append('<option value="'+v.id+'" lonlat="'+v.lonlat+'">'+$.trim(v.n)+'</option>');
                });
            }
        });
        
        // Primero trae el listado de capas veredales
        if (layers_veredales == '') {
            $.ajax({
                url: _relat + 'geonode_layers_veredales.json',
                dataType: 'json',
                success: function(json){ 
                    layers_veredales = json;
                }
            });
        }
    });
    
    $('#select_city').change(function() {
        // create a new lat/lon object
        var ll = $(this).find('option:selected').attr('lonlat').split(',');
        var lon = ll[0];
        var lat = ll[1];
						
        var report_form = (document.URL.indexOf('submit') > 0 || document.URL.indexOf('edit') > 0) ? true : false;
        
        // Get map instance
        var map_instance = (report_form) ? map : radiusMap;
        
        var myPoint = new OpenLayers.LonLat(lon, lat);
        myPoint.transform(proj_4326, map_instance.getProjectionObject());

        // get markers layer
        var layer_name = (report_form) ? 'Editable' : 'Markers';
        var layer_markers = map_instance.getLayersByName(layer_name)[0];
        
        if (report_form) {
            layer_markers.removeFeatures(layer_markers.features);
        
            var point = new OpenLayers.Geometry.Point(lon, lat);
            OpenLayers.Projection.transform(point, proj_4326,proj_900913);
        
            f = new OpenLayers.Feature.Vector(point);
            vlayer.addFeatures(f);
        } 
        else { // En reportes frontend
            layer_markers.clearMarkers();
            var marker = new OpenLayers.Marker(myPoint);

            markers.addMarker(marker);
					
            drawCircle(radiusMap, lat, lon);
						
            currRadius = $("#alert_radius option:selected").val();
            radius = currRadius * 1000

            // Store the radius and start locations
            urlParameters["radius"] = currRadius;
            urlParameters["start_loc"] = lat + "," + lon;
        }

        // display the map centered on a latitude and longitude
        map_instance.setCenter(myPoint);
						
        // Update form values
        $("#latitude").attr("value", lat);
        $("#longitude").attr("value", lon);

        var ciudad = $(this).find('option:selected').text();
        $("#location_name").attr("value", ciudad + ', ' + $('#select_state option:selected').text());
        $("#country_name").attr("value", 'Colombia');
    
        // Muestra capa veredal del municipio cuando existe en geonode
        // El listdo de capas veredales viene de un json en monitor que es creado
        // cada dia con el cron que genera el listado de capas
        var u = 'http://geonode.salahumanitaria.co/geoserver/wms';
        
        // Para geojson
        var wfs = 'http://geonode.salahumanitaria.co/geoserver/wfs?outputFormat=json&version=1.0.0&request=GetFeature&service=WFS&format_options=callback:addFeatures&propertyName=NOMBRE&typename=';

        // Busca el municipio
        for (var i in layers_veredales) {
            var re = new RegExp(' ' + ciudad, 'i'); // Espacio al comienzo para que no retorne casos como buscar: tame, nombre: departamento, coinice tame

            var n = layers_veredales[i]['nombre'];
            var l = layers_veredales[i]['wms'];

            var match = re.exec(n);
            
            if (match !== null) {
                
                // WFS para dropdown de veredas
                $.ajax({
                    url: wfs + l,
                    dataType: 'jsonp',
                });

                // WMS
                ly = new OpenLayers.Layer.WMS(n, 
                                  u,
                                  {
                                  layers: l,
                                  transparent: true,
                                  },
                                  {
                                    opacity: 1,
                                    visibility: true
                                  }
                              );

                map.addLayer(ly);

                map.zoomTo(8);

            }
        }
    });

    $(document).on('change', '#select_vereda', function(){ 
        var vereda = $(this).find('option:selected').text();
        $("#location_name").attr("value", 'Vereda ' + vereda + ', ' + $('#select_city option:selected').text() + ', ' + $('#select_state option:selected').text());
    });

    // Validar Submit
    $('input[name="submit"]:not(.header_nav_login_btn),a.btn_save,a.btn_save_close,a.btn_save_add_new').click(function(){
        // Valida forma
        var _error = true;
        var error = '';
        
        if ($('#incident_title').val() == '') {
            error += '- Titulo <br />';
        }
        
        if ($('#incident_description').val() == '') {
            error += '- Descripción<br />';
        }
        
        $('input[name="incident_category[]"]').each(function(){ 
            if ($(this).attr('checked')) {
                _error = false;
            }
        });

        if (_error) {
            error += '- Categoria<br />';
        }

        if ($('#select_state').val() == '') {
            error += '- Departamento<br />';
        }
        
        if ($('#select_city').val() == '') {
            error += '- Ciudad<br />';
        }
        
        if ($('#location_name').val() == '') {
            error += '- Ubicación precisa<br />';
        }
        
        if ($('#person_first').val() == '') {
            error += '- Su nombre<br />';
        }
        
        if ($('#person_last').val() == '') {
            error += '- Su apellido<br />';
        }
        
        if ($('#person_email').val() == '') {
            error += '- Su email<br />';
        }
        
        if (error != '') {
            mdialog('Los siguientes campos son obligatorios: <br /><br />' + error + '<br />');
            return false;
        }
        else if (error == '' && ($(this).hasClass('btn_save') || $(this).hasClass('btn_save_close') || $(this).hasClass('btn_save_add_new'))) {
            $(this).parents("form").submit();
        } 
        
    });



});
    
function addFeatures(geojson) {
    // Popula dropdown
    var html = '<select id="select_vereda" class="select"><option>Seleccione vereda</option>';
    
    for (var l in geojson['features']) {
        html += '<option value="1">' + geojson['features'][l]['properties']['NOMBRE'] + '</option>';
    }

    html += '</select>';

    // Borra combo ya creado
    if ($('#select_vereda').length > 0) {
        $('#select_vereda').remove();
    }
    $('#select_state').closest('div.report_row').append(html);

    // Ordena options
    $dd = $('#select_vereda');

    var foption = $dd.find('option:first');

    $dd.append($dd.find('option:not(:first)').sort(function(a, b) { 
        return a.text == b.text ? 0 : a.text < b.text ? -1 : 1
    }));

    $dd.prepend(foption);

}

function mdialog(h) {
    if ($('#ddialog').length > 0) {
        $('#ddialog').html(h);
        $('#ddialog').dialog('open');
    }
    else {
        var $dialog = $('<div id="ddialog"></div>')
                .html(h)
                .dialog({
                    autoOpen: false,
                    title: 'Campos obligatorios',
                    modal: true,
                    width: 400
                });
        $dialog.dialog('open');
    }
            
}
