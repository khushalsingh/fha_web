<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
         <title><?php echo isset($title) ? $title : (($this->router->method === 'index') ? '' : ucwords(str_replace('_', ' ', $this->router->method))) . ' ' . ucwords(str_replace('_', ' ', $this->router->class)); ?></title>
        <meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>
        <link href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
        <link href="//maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css" rel="stylesheet" type="text/css" />
        <link href="//code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css" rel="stylesheet" type="text/css" />
        <link href="//fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700,300italic,400italic,600italic" rel="stylesheet" type="text/css">
        <link href="<?php echo base_url(); ?>assets/css/fandh.css" rel="stylesheet" type="text/css" />
        <link href="<?php echo base_url(); ?>assets/css/skins/_all-skins.css" rel="stylesheet" type="text/css" />
        <?php if (is_file(FCPATH . 'assets/css/fandh/' . $this->router->class . '/' . $this->router->method . '.css')) { ?>
            <link rel="stylesheet" href="<?php echo base_url() . 'assets/css/fandh/' . $this->router->class . '/' . $this->router->method . '.css'; ?>" />
        <?php } ?>
        <script src="//code.jquery.com/jquery.min.js"></script>
        <script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.4/js/bootstrap.min.js" type="text/javascript"></script>
        <!--[if lt IE 9]>
            <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
            <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
        <![endif]-->
        <script type="text/javascript">var base_url = '<?php echo base_url(); ?>';</script>
    </head>
    <body class="skin-green-light layout-top-nav">
        <div class="wrapper">
            <header class="main-header">
                <nav class="navbar navbar-static-top">
                    <div class="navbar-header">
                        <a href="<?php echo base_url(); ?>" class="navbar-brand logo"><span class="logo-mini"><img src="<?php echo base_url(); ?>assets/img/logo-thumb.png" /></span>
                            <span class="logo-lg"><img src="<?php echo base_url(); ?>assets/img/logo-thumb.png" /> Fit + Healthy</span></a>
                        <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar-collapse">
                            <i class="fa fa-bars"></i>
                        </button>
                    </div>
                    <div class="collapse navbar-collapse" id="navbar-collapse">
                        <ul class="nav navbar-nav">
                            <li class="active"><a href="<?php echo base_url(); ?>login">Login</a></li>
                        </ul>
                    </div>
                </nav>
            </header>