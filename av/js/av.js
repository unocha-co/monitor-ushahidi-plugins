var cat_tx = actor_tx = ub_tx = date_tx = '';

var months = ['', 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];

var tree_select_mode = 3;

$(function() {
    
    // Tabs
    $('#av').tabs();

    // Checkbox of actors div
    $('#div_actor_content input:checkbox').click(function() {
        $(this).parent('div').toggleClass('selected');
    });
    
    // Description readonly
    $('#incident_description').attr('readonly', 'readonly');

    // Date to fill description
    dateToDesc();
    $('#incident_date').change(function(){ dateToDesc(); updateDesc(); });

    // Location to fill description
    $('#location_name').change(function() {
        ub_tx = $('#location_name').val();
        updateDesc();
    });

    $('#select_city').change(function() {
        ub_tx = $(this).find('option:selected').text() + ', ' + $('#select_state option:selected').text();
        updateDesc();
    });

    // Populate victim category dropdown with categories selected
    $categories = $('#categories, .report_category');  // Selector para frontend y backend

    $categories.find('input:checkbox').click(function() {
        addActorVictim($(this));
    });

    $('#av_category_id').change(function(){
        $('.av_category_div').hide();
        $('#actor_category_div_' + $(this).val()).show();
        $('#victim_category_div_' + $(this).val()).show();
    });
    
    $('.numbersOnly').keyup(function () { 
            this.value = this.value.replace(/[^0-9\.]/g,'');
    });
    
    $('#reportForm').submit(function () {
        // Set number of victim groups per category
        var v_x_c = '';
        $('.victim_category_div:not(:first)').each(function(i){
            
            var num = $(this).find('.victim_group').length;
            
            if (i > 0)  v_x_c += '|';
            //else    num -= 1;

            v_x_c += num;
        });

        $('#victim_category_hidden').attr('value',v_x_c);
        
        // Delete first victim group in each category
        $('#victim_group_0').remove();
    });
});

function addActorVictim($obj) {
    var v_c = $('#av_category_id');
    var avs = ['actor', 'victim'];

    // Delete cats dropdown options
    v_c.children('option').remove();

    if ($categories.find('input:checked').length == 0) {
        $('#av_plugin').hide();
    }
    else {
        $('#av_plugin').show();
    }

    for (_a in avs) {
        
        _av = avs[_a];

        if (!$obj.attr('checked')){
            $('#' + _av + '_category_div_' + $obj.val()).remove();
            $('#' + _av + '_category_hidden_' + $obj.val()).remove();
        }

        cats = [];
        $categories.find('input:checked').each(function(i,obj){
            var val = obj.value;
            var id_new = _av + '_category_div_' + val;
            var id_hidden_new = _av + '_category_hidden_' + val;
            var tx = ($(this).next().size()) ? $(this).next().html() : $.trim($(this).parent().text());
            var num = $('#div_'+_av).find('.'+_av+'_group').length;
            
            // Cats title to fill description 
            cats.push(tx);
    
            if (_a == 0) {
                v_c.append('<option value="' + val + '">' + tx + '</option>');
            }
            
            // Change id for first div
            /*
            if (i == 0 && $('#' +_av + '_category_div_0').length > 0){
                $('#' + _av + '_category_div_0').attr('id',id_new);
                $('#id_actor_0').attr('name', 'id_actor_' + val);
                $('#id_actor_0').attr('id', 'id_actor_' + val);
                
                // Clone first actor list and change id
                $('#' + _av + '_group_0').clone().attr('id', _av + '_group_1').removeClass('hide').appendTo($('#' + id_new));
            }
            */

            // Add Div Actor&Victim to each category
            if ($('#' + _av + '_category_div_' + val).length == 0){
                
                var hide = (i > 0) ? 'hide' : '';
                var _div = '<div class="av_category_div ' + _av + '_category_div ' + hide + '" id="' + id_new + '">';
                
                if (_av == 'actor') {
                    _div += '<input type="hidden" id="id_actor_'+val+'" name="id_actor_'+val+'"></div>';
                }
                
                $('#div_' + _av + '_content').append(_div);
                $('#' + _av + '_group_0').clone().attr('id', _av + '_group_' + num).removeClass('hide').appendTo($('#' + id_new));
                
                // Show first div ' + _av + '
                $('.' + _av + '_category_div:eq(0)').show();
                
            }

            // Show div to first selected category
            $('.av_category_div' + ':first').show();
                
            // Tree for actors
            if (_av == 'actor') {
                $('#actor_group_' + num).dynatree({ 
                                        checkbox : true, 
                                        minExpandLevel: 1,
                                        selectMode: tree_select_mode,
                                        debugLevel: 0,
                                        onLazyRead: function(node){
                                            // Si edit, envia incident_id
                                            var incident_id = 0;
                                            if ($('input[name="incident_id[]"]').length > 0) {
                                                incident_id = $('input[name="incident_id[]"]').val();
                                            }

                                            node.appendAjax({url: '/av/getActores/' + node.data.ida + '/' + val + '/' + incident_id,
                                                data: {"key": node.data.key, // Optional url arguments
                                                    "mode": "all"
                                                }
                                            });
                                        },
                                        onSelect: function(select, node) {
                                                // Display list of selected nodes
                                                var selNodes = node.tree.getSelectedNodes();
                                                
                                                var selKeys = $.map(selNodes, function(node){
                                                    return node.data.ida;
                                                });
                                                
                                                var partsel = new Array();
                                                var acs = [];
                                                $('#' + id_new).find(".dynatree-partsel:not(.dynatree-selected)").each(function () {
                                                    var node = $.ui.dynatree.getNode(this);
                                                    partsel.push(node.data.ida);
                                                    
                                                    // ParSelected parents title
                                                    if ($(this).hasClass('dynatree-ico-ef')) {
                                                        acs.push(node.data.title);
                                                    }
                                                });
                                                
                                                // Selected parents title to fill incident title
                                                var selTit = [];
                                                $('#' + id_new).find(".dynatree-selected").each(function () {
                                                    var node = $.ui.dynatree.getNode(this);
                                                    if (node.getLevel() == 1) {
                                                        selTit.push(node.data.title);
                                                    }
                                                });
                                                
                                                // Text for fill description, only root of each node
                                                selTit = selTit.concat(acs);
                                                actor_tx = selTit.join(' Vs ');
                                                updateDesc();

                                                selKeys = selKeys.concat(partsel);
                                                $('#id_actor_' + val).val(selKeys.join(','));
                                            }
                                    });
            }
        });

        // Show first victim group
        $('#div_' + _av + '_content').show();
    
        // Update description
        cat_tx = cats.join(' - ');
        updateDesc();
    }
}

function addVictimGroup(){

    //var idx = $('#av_category_id').attr('selectedIndex');
    var idx = $('#av_category_id').val();
    var num = $('#div_victim').find('.victim_group').length;

    // Clone victim group
    //$('#victim_group_0').clone().appendTo($('.victim_category_div:eq(' + idx + ')'));
    $('#victim_group_0').clone().attr('id','victim_group_' + num).removeClass('hide').appendTo($('#victim_category_div_' + idx));
}

function deleteVictimGroup(e){
    if (confirm('Está seguro de borrar este grupo?')){
        //$('#' + e.target.id).closest('div.victim_group').remove();
        $(e.target).closest('div.victim_group').remove();
    }
}
function filterList(event, cancel) {

    if (cancel) {
        $(event.target).parents('div.av_category_div').find('input.actor_search_input').val('');
    }
    else {
        texto = $(event.target).val();
        keyNum = event.keyCode;
            
        if (keyNum == 8){  //Backspace
            texto = texto.slice(0, -1);  //Borra el ultimo caracter
        }
        else{
            keyChar = String.fromCharCode(keyNum);
            texto +=  keyChar;
        }
        
        var re = new RegExp(texto, 'i');
    }
	
    if (cancel || texto.length >= 3) {
        $(event.target).parents('div.av_category_div').find('li').each(
            function () {
                $(this).show();
                if (!cancel && !$(this).html().match(re)) {
                    $(this).hide();
                }
            });
    }
}

function updateDesc(tx) {
    
    d_tx = cat_tx;

    /*
    if (actor_tx != '') {
        d_tx += '. ' + actor_tx;
    }
    */

    if (ub_tx != '') {
        d_tx += '. ' + ub_tx;
    }
    
    d_tx += '. ' + date_tx;

    $('#incident_description').val(d_tx);
}

function dateToDesc() {    
    var _dt = $('#incident_date').val().split('/');
    date_tx = _dt[1] + ' de ' + months[_dt[0]*1] + ' de ' + _dt[2];
}
