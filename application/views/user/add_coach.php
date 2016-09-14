<div class="content-wrapper">
    <section class="content-header">
        <h1>Add Coach User <small>create a new coach</small></h1>
        <ol class="breadcrumb">
            <li><a href="<?php echo base_url(); ?>dashboard"><i class="fa fa-dashboard"></i> Dashboard</a></li>
            <li class="active">Add Coach User</li>
        </ol>
    </section>
    <section class="content">
        <div class="row">
            <div class="col-md-6 col-md-offset-3">
                <div class="box box-primary">
                    <div class="box-header">
                        <h3 class="box-title">Add Coach User</h3>
                    </div>
                    <form id="add_coach_form" role="form" method="post" action="">
                        <div class="box-body">
                            <div class="form-group">
                                <label for="user_first_name">First Name</label>
                                <input name="user_first_name" type="text" class="form-control" id="user_first_name" placeholder="Enter First Name">
                            </div>
                            <div class="form-group">
                                <label for="user_last_name">Last Name</label>
                                <input name="user_last_name" type="text" class="form-control" id="user_last_name" placeholder="Enter Last Name">
                            </div>
                            <div class="form-group">
                                <label for="">Gender </label>
                                <label class="radio-inline">
                                    <input type="radio" name="user_gender" value="1" checked="checked"> Male
                                </label>
                                <label class="radio-inline">
                                    <input type="radio" name="user_gender" value="0"> Female
                                </label>
                            </div>
                            <div class="form-group">
                                <label for="user_primary_contact">Contact</label>
                                <input name="user_primary_contact" type="text" class="form-control" id="user_primary_contact" placeholder="Enter Contact">
                            </div>
                            <div class="form-group">
                                <label for="user_email">Email</label>
                                <input name="user_email" type="text" class="form-control" id="user_email" placeholder="Enter Email">
                            </div>
                            <div class="form-group">
                                <label for="user_description">Description</label>
                                <textarea name="user_description" class="form-control" id="user_description" placeholder="Description"></textarea>
                            </div>
                            <div class="form-group">
                                <label for="user_login_password">Password</label>
                                <input name="user_login_password" type="text" class="form-control" id="user_login_password" placeholder="Enter Password">
                            </div>
                        </div>
                        <div class="box-footer text-right">
                            <button id="add_coach_button" type="button" class="btn btn-primary" data-loading-text="Please Wait...">Add Coach <i class="fa fa-chevron-right"></i></button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
</div>
<script type="text/javascript">
    $("#add_coach_button").click(function() {
        $("#add_coach_button").button('loading');
        $.post('', $("#add_coach_form").serialize(), function(data) {
            if (data === '1') {
                bootbox.confirm("Coach Added Successfully !!!", function(result) {
                    document.location.href = base_url + 'user/coach';
                });
            } else if(data === '0'){
                bootbox.alert("Error Saving Data !!!");
            } else {
                bootbox.alert(data);
            }
            $("#add_coach_button").button('reset');
        });
    });
</script>