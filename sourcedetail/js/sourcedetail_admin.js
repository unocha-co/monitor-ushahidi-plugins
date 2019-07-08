$(function() { 

    var admins = 'admin';
    var path = window.location.pathname;
    var re = /\d+/;

    var id = re.exec(path)[0];
    var url = path.substr(0,path.indexOf(admins)) + 'sourcedetail/edit/' + id;

    $.ajax({
        url: url,
        success: function(data){
            $(data.sources).each(function(s){
                
                var n = 1 + s;

                src = data.sources[s];

                if (s > 0) {
                    addSDGroup();
                }
                
                $('#sd_source_id_' + n).val(src.source_id);
                $('#sd_date_' + n).val(src.source_date);
                $('#sd_desc_' + n).html(src.source_desc);
                $('#sd_reference_' + n).html(src.source_reference);

            })
        }
    });
    
    // Remueve campos de enlace a fuentes
    $('#divNews').remove();
});
