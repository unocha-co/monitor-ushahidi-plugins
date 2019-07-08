$(document).ready(function() {

    addSDGroup();

    // Hide media link field
    $('#divNews, #news_id').hide();
    
    $('.numbersOnly').keyup(function () { 
            this.value = this.value.replace(/[^0-9\.]/g,'');
    });

    // Bind events to fill description field
    $('#categories input:checkbox').click(function() {
        $('#categories input:checked').each(function(i,obj){
            var tx = ($(this).next().size()) ? $(this).next().html() : $.trim($(this).parent().text());

        });


    });
    
    // Validate source detail fields
    $('#reportForm').submit(function () {
        var error = '';
        $('.sd_dd:not(:first)').each(function(i){
            var _g = i + 1;
            var _ix = $(this).attr('name').match(/[0-9]/);

            if ($(this).val() != '') {
                
                var _e = ['date','desc','reference'];
                var _el = ['Fecha','Descripción','Enlacen a la fuente, nombre del articulo, etc'];

                for (_j in _e) {
                    if ($('#sd_' + _e[_j] + '_' + _ix).val() == '') {
                        error += '- ' + _el[_j] + ', para la fuente # ' + (_g) + '<br />';
                    }
                }
            }
        
        });
        
        if (error != '') {
            sd_mdialog('Los siguientes campos son obligatorios para cada fuente: <br /><br />' + error + '<br />');
            return false;
        }

        // Delete first group
        $('#sd_group_0').remove();

    });
});

function sd_mdialog(h) {
    if ($('#ddialog').length > 0) {
        $('#ddialog').html(h);
        $('#ddialog').dialog('open');
    }
    else {
        var $dialog = $('<div id="ddialog"></div>')
                .html(h)
                .dialog({
                    autoOpen: false,
                    title: 'Campos obligatorios detalle de fuentes',
                    modal: true,
                    width: 500
                });
        $dialog.dialog('open');
    }
            
}

function addSDGroup(){
    //var idx = $('#av_category_id').attr('selectedIndex');
    var idx = $('.sd_group').length;
    var _g = $('#sd_group_0').clone().attr('id','sd_group_' + idx).removeClass('hide');
    
    _g.html(_g.html().replace(/_[0-9]/g,'_' + idx));
    _g.appendTo($('#sourcedetail'));
    $('#sd_date_' + idx).datepicker();

    $('#sd_num_' + idx).val(idx);
}

function deleteSDGroup(e){
    if (confirm('Está seguro de borrar este grupo?')){
        //$('#' + e.target.id).closest('div.victim_group').remove();
        $(e.target).closest('div.sd_group').remove();
    }
    
    $('#sourcedetail_num').val(parseInt($('#sourcedetail_num').val()) - 1);
}
