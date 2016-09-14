$.extend($.fn.DataTable.defaults, {
    "aLengthMenu": [
    [10, 20, 50, 100, 200, 500, -1],
    [10, 20, 50, 100, 200, 500, "All"]
    ],
    "sDom": "<'row'<'col-md-4 col-sm-12'l><'col-md-4 col-sm-12 text-center'T><'col-md-4 col-sm-12'f>r><'table-scrollable't><'row'<'col-md-5 col-sm-12'i><'col-md-7 col-sm-12'p>>",
    "iDisplayLength": 10,
    "bProcessing": true,
    "bServerSide": true,
    "bDeferRender": true,
    "sServerMethod": "POST"
});