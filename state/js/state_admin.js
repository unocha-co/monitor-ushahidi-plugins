$(document).ready(function() {
    var admins = 'admin';
    var path = window.location.pathname;
    var re = /\d+/;

    var id = re.exec(path);

    if (id != undefined) {
        var id = id[0];
        var url_root = path.substr(0,path.indexOf(admins));
        var url = url_root + 'state/edit/' + id;
        
        $.ajax({
            url: url,
            success: function(data){ 
                $('#select_city').html('<option>Loading....</option>');
                
                var state_id = data.state_id;
                var city_id = data.city_id;

                var _url =  url_root + 'state/cities/' + state_id;

                $.ajax(
                {
                    url: _url,
                    success: function(data) {
                        var cities = data;
                        $('#select_city').html('<option value="">Selecciona la ciudad</option>');
                        $.each(cities, function(i, v){
                            $('#select_city').append('<option value="'+v.id+'" lonlat="'+v.lonlat+'">'+v.n+'</option>');
                        });

                        // Selecciona valores
                        $('#select_state').val(state_id*1);
                        $('#select_city').val(city_id*1);
                    }
                });
            }
        });
    }
});
