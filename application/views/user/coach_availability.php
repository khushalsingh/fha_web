<div class="content-wrapper">
    <section class="content-header">
        <h1>Coach Availability <small>coach users availability</small></h1>
        <ol class="breadcrumb">
            <li><a href="<?php echo base_url(); ?>dashboard"><i class="fa fa-dashboard"></i> Dashboard</a></li>
            <li class="active">Coach Availability</li>
        </ol>
    </section>
    <section class="content">
        <div class="box">
            <div class="box-body">
                <table id="coach_datatable" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Coach ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Contact</th>
                            <th>For</th>
                            <th>Topic</th>
                            <th>From</th>
                            <th>To</th>
                            <th>Created</th>
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
            "sAjaxSource": base_url + "user/coach_availability_datatable",
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
                    "aTargets": [4],
                    "bSearchable": false,
                    "mRender": function (data, type, full) {
                        switch (data) {
                            case '1':
                                return '<i class="fa fa-user"></i> <i class="fa fa-comment"></i>';
                                break;
                            case '2':
                                return '<i class="fa fa-user"></i> <i class="fa fa-video-camera"></i>';
                                break;
                            case '3':
                                return '<i class="fa fa-users"></i> <i class="fa fa-video-camera"></i>';
                                break;
                            default:
                                return '<i class="fa fa-circle-thin"></i>';
                                break;
                            }
                        }
                    }]
            }).fnSetFilteringDelay(700);
        });
        function user_status(user_id){
            $.post(base_url+"user/change_status",{user_id:user_id,user_status:$("#id_"+user_id).is(":checked")},function(data){
                if(data === '1'){
                    bootbox.alert("User Status Changed Successfully");
                } else if(data === '0'){
                    bootbox.alert("Error Updating User Status !!!");
                } else {
                    bootbox.alert(data);
                }
            });
        }
        function confirm_delete(users_id){
            bootbox.confirm("Are you sure you want to proceed ?",function(result){
                if(result){
                    $.post(base_url+ 'user/delete',{users_id:users_id},function(data){
                        if(data === '1'){
                            document.location.href = '';
                        } else {
                            bootbox.alert(data);
                        }
                    });
                }
            });
        }
</script>