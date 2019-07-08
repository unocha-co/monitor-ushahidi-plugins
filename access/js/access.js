var url_absolute = '/admin/access/';

$(document).ready(function() {

    // Actualiza los eventos que han sido preclasificados
    // por los alimentadores y les coloca la columna access=-2
    // solo en tan No clasificados
    if ($('ul.tabset > li:first > a').hasClass('active')) {
        $('.access_form').each(function(){ 
            var access = 0;

            if ($(this).find(':checked').length > 0 ) { 
                var $btn = $(this).find('.btn_save');
                operationIncident($btn.data('incident'), this, 'pre');
            };
        });
    } 

    $('.tr_access_row').hover(
        function(){ 
            $(this).find('.btn_classify').show(); 
        },
        function(){ 
            $(this).find('.btn_classify').hide(); 
        }
    );

    $('.btn_classify').click(function() {
        $(this).closest('div').siblings('.access_form').toggle();
    });
    
    $('.btn_save, .btn_pre').click(function() {
        
        var chks = $(this).closest('div.access_form').find('input:checked');
        var form = $(this).closest('form');
        var that = this

        if (chks.length == 0) {
            alert('Seleccione alguna opcion para poder guardar!');
        } 
        else {
            saveForm(form, that, $(this).data('access'));
        }
    });
    
    $('.btn_delete').click(function() {
        
        var form = $(this).closest('form');
        var that = this
        
        if (confirm('Esta seguro?')) {
            $(this).parent().siblings('.not_access').val(0);
            saveForm(form, that, 0);
        }
    });
    
    $('.btn_discard').click(function() {
        
        //var form = $(this).closest('form');
        var that = this
        
        if (confirm('Esta seguro?')) {
            //discardIncident(form, that);
            operationIncident($(this).data('incident'), that, 'discard');
        }
    });
});
            
function saveForm(form, that, access) {            

    $('#access_' + $(that).data('incident')).val(access);

    $.ajax({
        url: url_absolute + 'save',
        type: 'POST',
        data: form.serialize(),
        success: function(data) {
            if (data.success == 1) {
                
                if (data.edit == 0) {
                    var target = $(that).closest('tr.tr_access_row');
                    target.hide('slow', function(){ target.remove(); });

                    // Update total
                    $('#num_total').text($('#num_total').text()*1 - 1);
                }
                else {
                    var target = $(that).closest('div.access_form');
                    
                    target.hide('slow');
                    $(that).closest('td').find('.message').show();
                    setTimeout(function(){ $(that).closest('td').find('.message').hide(); }, 3000);
                }
            }
        }
    });
}

function operationIncident(incident_id, that, v) {
    
    var op = [];
    op['discard'] = -1,
    op['pre'] = -2,

    $.ajax({
        url: url_absolute + 'operation/' + incident_id + '/' + op[v],
        type: 'POST',
        //data: form.serialize(),
        success: function(data) {
            if (data.success == 1) {
                
                var target = $(that).closest('tr.tr_access_row');
                target.hide('slow', function(){ target.remove(); });

                // Update total
                $('#num_total').text($('#num_total').text()*1 - 1);
            }
        }
    });
}
