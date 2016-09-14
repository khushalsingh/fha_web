<body class="login-page">
    <div class="login-box">
        <div class="login-logo">
            <a href="<?php echo base_url(); ?>"><img src="<?php echo base_url() . 'assets/img/logo.png'; ?>"/></a>
        </div>
        <div class="login-box-body" id="user_login_form_div">
            <p class="login-box-msg">Forgot Password</p>
            <?php if (isset($success)) { ?>
                <div class="alert alert-success alert-dismissible" role="alert">
                    <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                    <?php echo $success; ?></div>
            <?php } else if (isset($error)) { ?>
                <div class="alert alert-danger alert-dismissible" role="alert">
                    <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                    <?php echo $error; ?></div>
            <?php } ?>
            <form id="user_recovery_form" action="" method="post" role="form">
                <div class="form-group has-feedback">
                    <input name="email_address" id="email_address" type="text" class="form-control" placeholder="Username OR Email"/>
                    <span class="glyphicon glyphicon-user form-control-feedback"></span>
                </div>
                <?php if (isset($captcha_image)) { ?>
                    <div class="form-group text-center">
                        <?php echo $captcha_image; ?>
                    </div>
                    <div class="form-group has-feedback">
                        <input type="text" autocomplete="off" class="form-control" placeholder="Enter Image Text" name="captcha_image" id="captcha_image" maxlength="6">
                        <span class="form-control-feedback"><i class="fa fa-bullseye"></i></span>
                    </div>
                <?php } ?>
                <div class="row">
                    <div class="col-xs-8">
                        <a href="<?php echo base_url(); ?>login" id="user_recover_button" type="button" class="btn btn-default btn-flat"><i class="fa fa-chevron-left"></i> Back to Login</a>
                    </div>
                    <div class="col-xs-4">
                        <button id="user_recover_button" type="submit" class="btn btn-primary btn-block btn-flat">Submit <i class="fa fa-chevron-right"></i></button>
                    </div>
                </div>
            </form>
        </div>
    </div>