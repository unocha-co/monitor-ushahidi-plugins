
$(function() {
    
    $('#av_plugin').show();

    var admins = 'admin';
    var path = window.location.pathname;
    var re = /\d+/;

    var id = re.exec(path)[0];
    var url = path.substr(0,path.indexOf(admins)) + 'av/edit/' + id;

    $('.report_category').find('input:checked').each(function(i,obj){

        var num = i + 1;

        var cat_id = $(this).val();

        addActorVictim($(this));

        $.ajax({
            url: url + '/' + cat_id,
            success: function(data){ 
                var actores = data.actores;
                
                $obj = $('#actor_category_div_' + cat_id).find('.actor_group')
            
                $obj.dynatree('option', 'selectMode', 2);
                $tree = $obj.dynatree('getTree');
                
                $tree.visit(function(node){ 

                    if (actores.length > 0) {
                        for (a in actores) {
                            
                            var ida = actores[a];

                            if (node.data.ida == ida) {
                                node.select(true);
                                break;
                            }
                        }
                    
                    } 
                });
                
                $obj.dynatree('option', 'selectMode', tree_select_mode);
                
                $('#id_actor_' + cat_id).val(actores.join(','));

                var victimas = data.victimas;
                var $div = $('#victim_category_div_' + cat_id);

                $(victimas).each(function(v){ 
                    
                    var nv = 1 + v;

                    if (nv > 1) {
                        // Selecciona la catgoria en el dropdown de categorias de AV
                        $('#av_category_id').val(cat_id);
                        addVictimGroup();
                    }
                    
                    var num_victim_global = $('#div_victim').find('.victim_group').length - 1;


                    var $group = $div.find('.victim_group:eq(' + v + ')');
                    
                    var _v = victimas[v];

                    var cant = _v.cant;
                    var gender_id = _v.gender_id;
                    var occupation_id = _v.occupation_id;
                    var status_id = _v.status_id;
                    var condition_id = _v.condition_id;
                    var sub_condition_id = _v.sub_condition_id;
                    var age_group_id = _v.age_group_id;
                    var age_id = _v.age_id;
                    var ethnic_group_id = _v.ethnic_group_id;
                    var sub_ethnic_group_id = _v.sub_ethnic_group_id;

                    $group.find('input[name="victim_cant[]"]').val(cant);
                    $group.find('select[name="victim_gender_id[]"]').val(gender_id);
                    $group.find('select[name="victim_sub_ethnic_group_id[]"]').val(sub_ethnic_group_id);
                    $group.find('select[name="victim_ethnic_group_id[]"]').val(ethnic_group_id);
                    $group.find('select[name="victim_age_group_id[]"]').val(age_group_id);
                    $group.find('select[name="victim_age_id[]"]').val(age_id);
                    $group.find('select[name="victim_sub_condition_id[]"]').val(sub_condition_id);
                    $group.find('select[name="victim_condition_id[]"]').val(condition_id);
                    $group.find('select[name="victim_status_id[]"]').val(status_id);
                    $group.find('select[name="victim_occupation_id[]"]').val(occupation_id);

                });

                // Selecciona la primera categori
                $('#av_category_id').val($('#av_category_id').find('option:first').val());
            }
        });
    });
                        
});
