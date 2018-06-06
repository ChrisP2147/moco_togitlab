
// creates datatable
function load_datatable(){
    $(document).ready( function (){
    $('#table_id').DataTable();
    });
    var myTable = $('#table_id').DataTable( {
        "pageLength": 25,
        // paging: false,
        // "info": false,
        // searching: false,
        scrollY: 400
    });
    myTable.columns.adjust();
    myTable.responsive.recalc();
}

// loading circle (not in use)
function loadingFunction() {
    document.getElementById("loadingContainer").innerHTML = "<img src='Preloader_3.gif' alt='laden'>";
}
