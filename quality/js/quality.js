var url_absolute = '../../../admin/quality/';

$(document).ready(function() {

    var incident_id = $('input[name="incident_id[]"]').val();
    
    $('#quality_save').click(function() {
        
        var quality = 1;
        var review = $('#quality_review').val();

        if (review == '') {
            alert('Esrciba algún comentario!');
        } 
        else {
            saveForm(incident_id, quality, review);
        }
    });
    
    $('#quality_complete').click(function() {
        
        var quality = 3;
        var review = $('#quality_review').val();

        if (confirm('Está seguro?')) {
            saveForm(incident_id, quality, review);
        }
    });
    
    $('#quality_email').click(function() {
        
        var email = $(this).data('email');
        var name = $(this).data('name');
        
        if (confirm('Está seguro?')) {
            sendEmail(incident_id, email, name);
        }
    });
    $('#filter_category, #filter_state').change(function() {
        filterReports($(this).attr('id'), $(this).val());
    });
    
});
            
function saveForm(incident_id, quality, review) {            
    $.ajax({
        url: url_absolute + 'save',
        type: 'POST',
        data: { incident_id:incident_id, quality: quality, review:review },
        success: function(data) {
            if (data.success == 1) {
                
                if (review != 3) {
                    $('div#quality_form').hide();
                    $('div#quality_success').show();
                }
                else {
                    $('div#quality_form').hide();
                    $('div#quality_complete').show();
                }
                    
            }
        }
    });
}

function sendEmail(incident_id, email, name) {            
    $.ajax({
        url: url_absolute + 'email',
        type: 'POST',
        data: { incident_id:incident_id, email:email, name:name },
        success: function(data) {
            if (data.success == 1) {

                $('div#quality_email_success').show();
                $('div#quality_success').hide();
                    
            }
        }
    });
}

function filterReports(key, value)
{
    kpv = addURLParameter(document.location.search.substr(1), [[key, value]]);

    document.location.search = kpv; 
}
function getURLParameter(name) {
    return decodeURI(
        (RegExp('[?|&]' + name + '=' + '(.+?)(&|$)').exec(location.search)||[,null])[1]
    );
}

function addURLParameter(url, arr) 
{
    
    var kvp = url.split('&');
    var i= kvp.length; 
    var x;
    
    for (pa in arr){
        i= kvp.length; 
        key = escape(arr[pa][0]); 
        value = escape(arr[pa][1]);
    
        console.log(key);

        while(i--) {
            x = kvp[i].split('=');

            if (x[0] == key) {
                    x[1] = value;
                    kvp[i] = x.join('=');
                    break;
            }
        }

        if(i<0) {kvp[kvp.length] = [key,value].join('=');}
    }

    console.log(kvp);

    return kvp.join('&');
} 
