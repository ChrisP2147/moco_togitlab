
// creates datatable
function load_datatable(){
    $(document).ready( function (){
        $('#table_id').DataTable();
    });
    $('#table_id').DataTable( {
        scrollY: 400
    } );
}

// loading circle (not in use)
function loadingFunction() {
    document.getElementById("loadingContainer").innerHTML = "<img src='Preloader_3.gif' alt='laden'>";
}