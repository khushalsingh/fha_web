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
        <script src="//code.jquery.com/jquery.min.js"></script>
        <script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.4/js/bootstrap.min.js" type="text/javascript"></script>
        <!--[if lt IE 9]>
            <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
            <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
        <![endif]-->
        <script type="text/javascript">var base_url = '<?php echo base_url(); ?>';</script>
    </head>