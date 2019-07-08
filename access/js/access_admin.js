var form_response;

$(function() {
   
    getValues();

    $('#form_id').change(function(){ 
        setTimeout(setValues, '2000');
    });
                        
});


function getValues() {
    var admins = 'admin';
    var path = window.location.pathname;
    var re = /\d+/;

    var id = re.exec(path)[0];
    var url = path.substr(0,path.indexOf(admins)) + 'access/edit/' + id;


    $.ajax({
        url: url,
        success: function(json){ 
            
            form_response = json.acceso;

            setValues();
        }
                
    });

}
            
function setValues() {
    for (f in form_response) {
        var $i = $('input[value="' + form_response[f] + '"]');

        $i.prop('checked', true);
        $i.closest('td').css('background-color', '#FFF68A');
    }
}
