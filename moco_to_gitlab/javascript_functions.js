
// creates datatable
function load_datatable(){
    $(document).ready( function (){
    $('#table_id').DataTable();
    });
    var myTable = $('#table_id').DataTable( {
        "searching": false,
        paging: false,
        "info": false,
    });
    myTable.responsive.recalc();
    myTable.columns.adjust();
}

// loading circle 
function loadingFunction_send_frame() {
    document.getElementById("loadingContainer").innerHTML = "<img src='Preloader_3.gif' alt='laden'>";
}

// // loading circle when clicking "send Tickets" and when offers are beeing loaded into dataTable
function loadingFunction_main_frame() {
    document.getElementById("table_id").innerHTML = "<img src='Preloader_3.gif' alt='laden'>";
}

// create new GitLab token via Alertify ///////////////////////////////////////////////////////////////////////////
var inpOneVal;
var inpTwoVal
function show_key()
{
    var dlgContentHTML = $('#dlgContent').html();
    $('#dlgContent').html(""); 
    /* This is important : strip the HTML of dlgContent to ensure no conflict of IDs for input boxes arrises" */

    /* Now instead of making a prompt Dialog , use a Confirm Dialog */
    alertify.confirm(dlgContentHTML).set('onok', function(closeevent, value) { 
        inpOneVal = $('#inpOne').val();
        inpTwoVal = $('#inpTwo').val();
        send_gitlab_token();	
    }).set('title',"GitLab-Token hinzufügen")
    .set({onshow:null, onclose:function(){ location.reload()}});
}

function send_gitlab_token()
{
    // to DO noch mehr Plausibilität
    if (inpOneVal != "" || inpOneVal != ""){
        $.post("set_new_key.php", { mail: inpOneVal,  token: inpTwoVal} ).done(function() { alertify.success('Daten wurden gespeichert'); console.log('Done', data)});
    }
    else{
        alert('Eingabefelder müssen ausgefüllt werden');
    }
    // always reload the page, otherwise Alertify doesn't wor a second time!
    location.reload();
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////

