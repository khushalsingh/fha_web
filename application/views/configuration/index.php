<div class="content-wrapper">
    <section class="content-header">
        <h1>Configuration <small>global configurations</small></h1>
        <ol class="breadcrumb">
            <li><a href="javascript:;"><i class="fa fa-dashboard"></i> Dashboard</a></li>
            <li class="active">Configuration</li>
        </ol>
    </section>
    <section class="content">
        <div class="box">
            <form action="" method="post" id="configurations_form" name="configurations_form">
                <div class="box-body">
                    <table class="table table-bordered table-hover">
                        <tr>
                            <th>#</th>
                            <th style="width: 30%">Configuration Name</th>
                            <th>Configuration Value</th>
                        </tr>
                        <?php foreach ($configurations_array as $configuration) { ?>
                            <tr>
                                <td><?php echo $configuration['configuration_id']; ?></td>
                                <td><?php echo $configuration['configuration_name']; ?></td>
                                <td>
                                    <?php if ($configuration['configuration_id'] > 7) { ?>
                                        <?php echo $configuration['configuration_value']; ?>
                                        <?php
                                    } else {
                                        if ($configuration['configuration_type'] === '1') {
                                            ?>
                                            <input class="form-control" type="text" name="<?php echo $configuration['configuration_key']; ?>" value="<?php echo $configuration['configuration_value']; ?>" />
                                        <?php } else { ?>
                                            <textarea rows="5" class="form-control" name="<?php echo $configuration['configuration_key']; ?>"><?php echo $configuration['configuration_value']; ?></textarea>
                                            <?php
                                        }
                                    }
                                    ?>
                                </td>
                            </tr>
                        <?php } ?>
                    </table>
                </div>
                <div class="box-footer text-right">
                    <button id="configurations_form_submit" type="button" class="btn btn-primary" data-loading-text="Please Wait...">Save Changes <i class="fa fa-chevron-right"></i></button>
                </div>
            </form>
        </div>
    </section>
</div>
<script type="text/javascript">
    $(function(){
        $("#configurations_form_submit").click(function(){
            $("#configurations_form_submit").button('loading');
            $.post('',$("#configurations_form").serialize(),function(data){
                if (data === '1') {
                    bootbox.alert("Configuration Saved Successfully !!!", function() {
                        document.location.href = '';
                    });
                } else if(data === '0'){
                    bootbox.alert("Error Saving Data !!!");
                } else {
                    bootbox.alert(data);
                }
                $("#configurations_form_submit").button('reset');
            });
        });
    });
</script>