<body class="login-page">
    <div class="login-box">
        <div class="login-logo">
            <a href="<?php echo base_url(); ?>"><img src="<?php echo base_url() . 'assets/img/logo.png'; ?>"/></a>
        </div>
        <div class="login-box-body" id="user_login_form_div">
            <p class="login-box-msg">Login</p>
            <form id="user_login_form" action="" method="post" role="form">
                <div class="form-group has-feedback">
                    <input name="user_login" id="user_login" type="text" class="form-control" placeholder="Username OR Email"/>
                    <span class="glyphicon glyphicon-user form-control-feedback"></span>
                </div>
                <div class="form-group has-feedback">
                    <input name="user_login_password" id="user_login_password" type="password" class="form-control" placeholder="Password"/>
                    <span class="glyphicon glyphicon-lock form-control-feedback"></span>
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
                        <a href="<?php echo base_url(); ?>recover">I forgot my password</a>
                    </div>
                    <div class="col-xs-4">
                        <button id="user_login_button" type="submit" class="btn btn-primary btn-block btn-flat">Sign In <i class="fa fa-chevron-right" data-loading-text="Please Wait..."></i></button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <div class="row" id="login_success_redirect" style="display: none;">
        <div class="col-md-4 col-lg-offset-4">
            <div class="well background_white">
                <div class="alert alert-success">Login Successful. Please Wait...</div>
            </div>
        </div>
    </div>
    <script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.13.1/jquery.validate.min.js"></script>
    <script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.13.1/additional-methods.min.js"></script>
    <script type="text/javascript" src="<?php echo base_url(); ?>assets/js/plugins/md5.min.js"></script>
    <script type="text/javascript" src="<?php echo base_url(); ?>assets/js/plugins/base64.min.js"></script>
    <script type="text/javascript">
        $(function () {
            $("#user_login_form").validate({
                errorElement: 'span', errorClass: 'help-block',
                rules: {
                    user_login: {
                        required: true,
                        minlength: 5
                    },
                    user_login_password: {
                        required: true,
                        minlength: 5
                    }
                },
                messages: {
                    user_login: {
                        required: "The User ID field is required.",
                        minlength: "The User ID field must be at least {0} characters in length."
                    },
                    user_login_password: {
                        required: "The Password field is required.",
                        minlength: "The Password field must be at least {0} characters in length."
                    }
                },
                invalidHandler: function (event, validator) {
                    show_login_error();
                },
                highlight: function (element) {
                    $(element).closest('.form-group').addClass('has-error');
                },
                unhighlight: function (element) {
                    $(element).closest('.form-group').removeClass('has-error');
                },
                success: function (element) {
                    $(element).closest('.form-group').removeClass('has-error');
                    $(element).closest('.form-group').children('span.help-block').remove();
                },
                errorPlacement: function (error, element) {
                    error.appendTo(element.closest('.form-group'));
                },
                submitHandler: function (form) {
                    $(".alert-danger").remove();
                    $("#user_login_button").button('loading');
                    $.post('', {'user_login': btoa(btoa($.trim($("#user_login").val()))), 'user_login_password': btoa(btoa(md5(md5($.trim($("#user_login_password").val()).toLowerCase())))), 'user_remember': $("#user_remember:checked").val(), 'captcha_image': $("#captcha_image").val()}, function (data) {
                        if (data === '1') {
                            $("#user_login_form_div").hide();
                            $("#login_success_redirect").fadeIn('fast');
                            document.location.href = base_url + 'dashboard';
                        } else if (/^([a-z]([a-z]|\d|\+|-|\.)*):(\/\/(((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:)*@)?((\[(|(v[\da-f]{1,}\.(([a-z]|\d|-|\.|_|~)|[!\$&'\(\)\*\+,;=]|:)+))\])|((\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5]))|(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=])*)(:\d*)?)(\/(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)*)*|(\/((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)+(\/(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)*)*)?)|((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)+(\/(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)*)*)|((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)){0})(\?((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|[\uE000-\uF8FF]|\/|\?)*)?(\#((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|\/|\?)*)?$/i.test(data)) {
                            $("#user_login_form_div").hide();
                            $("#login_success_redirect").fadeIn('fast');
                            document.location.href = data;
                        } else if (data === '-1') {
                            document.location.href = '';
                        } else {
                            show_login_error();
                        }
                        $("#user_login_button").button('reset');
                    });
                }
            });
        });

        function show_login_error() {
            $("p.login-box-msg").after('<div class="alert alert-danger alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>You have entered an invalid username/email or password.</div>');
        }
    </script>