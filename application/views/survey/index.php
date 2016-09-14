<div class="content-wrapper">
    <section class="content-header">
        <h1>Survey <small>listing of all surveys</small></h1>
        <ol class="breadcrumb">
            <li><a href="<?php echo base_url(); ?>dashboard"><i class="fa fa-dashboard"></i> Dashboard</a></li>
            <li class="active">User</li>
        </ol>
    </section>
    <section class="content">
        <div class="box">
            <div class="box-body">
                <table id="coach_datatable" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Type</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Contact</th>
                            <th>Gender</th>
                            <th>Survey Remarks</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </section>
</div>
<link rel="stylesheet" type="text/css" href="//cdn.datatables.net/1.9.4/css/jquery.dataTables.css" />
<link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>assets/js/plugins/datatables/DT_bootstrap.css" />
<script type="text/javascript" src="//cdn.datatables.net/1.9.4/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/js/plugins/datatables/tabletools/js/dataTables.tableTools.min.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/js/plugins/datatables/DT_bootstrap.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/js/plugins/datatables/jquery.dataTables.delay.min.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/js/plugins/datatables/DT_custom.js"></script>
<script type="text/javascript">
    $(function () {
        $('#coach_datatable').dataTable({
            "aaSorting": [['0', 'asc']],
            "sAjaxSource": base_url + "survey/datatable",
            "oTableTools": {
                "sSwfPath": base_url + "assets/js/plugins/datatables/tabletools/swf/copy_csv_xls_pdf.swf",
                "aButtons": [{
                        "sExtends": "pdf",
                        "sButtonText": "<i class='fa fa-save'></i> PDF",
                        "sPdfOrientation": "landscape",
                        "sPdfSize": "tabloid",
                        "mColumns": [1, 2, 3, 4]
                    }, {
                        "sExtends": "csv",
                        "sButtonText": "<i class='fa fa-save'></i> CSV",
                        "mColumns": [1, 2, 3, 4]
                    }]
            },
            "aoColumnDefs": [
                {
                    "aTargets": [0],
                    "bVisible": true,
                    "bSearchable": false
                },
                {
                    "aTargets": [1],
                    "bSearchable": false,
                    "mRender": function (data, type, full) {
                        switch (data) {
                            case '2':
                                return '<i class="fa fa-user-md"></i> Coach';
                                break;
                            case '3':
                                return '<i class="fa fa-user"></i> User';
                                break;
                            default:
                                return '<i class="fa fa-circle-thin"></i> Unknown';
                                break;
                            }
                        }
                    },
                    {
                        "aTargets": [5],
                        "bSearchable": false,
                        "mRender": function (data, type, full) {
                            switch (data) {
                                case '1':
                                    return '<i class="fa fa-male"></i> Male';
                                    break;
                                case '0':
                                    return '<i class="fa fa-female"></i> Female';
                                    break;
                                default:
                                    return '<i class="fa fa-genderless"></i> Unknown';
                                    break;
                                }
                            }
                        }]
                }).fnSetFilteringDelay(700);
            });
</script>