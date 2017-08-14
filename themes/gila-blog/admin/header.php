<!DOCTYPE html>
<html lang="en">

<head>
    <base href="<?=gila::config('base')?>">

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, shrink-to-fit=no, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>Gila CMS - Administration</title>

    <!-- Bootstrap Core CSS -->
    <link href="lib/bootstrap/bootstrap.min.css" rel="stylesheet">
    <link href="lib/font-awesome/css/font-awesome.min.css" rel="stylesheet" async>

    <!-- Custom CSS -->
    <link href="src/core/assets/simple-sidebar.css" rel="stylesheet" async>
    <link href="lib/gila.min.css" rel="stylesheet">
    <style>
    #sidebar-wrapper .g-nav li{ color:#fff; }
    #sidebar-wrapper .g-nav li a{ color:#aaa; }
    #sidebar-wrapper .g-nav li ul li a{ color:#444; }
    #sidebar-wrapper .g-nav li a:hover{ background:var(--main-dark-color);color:white }
    .g-nav li ul{min-width: 200px}
    /*.dark-orange li ul{ background-color: #fff; }
    .dark-orange li ul li{ color: var(--main-color); }
    .dark-orange li ul li a{ color: var(--main-color); }
    .dark-orange li ul li a:hover{ color:white; }*/
    </style>

    <script src="lib/jquery/jquery-2.2.4.min.js"></script>
    <!--script src="lib/bootstrap/bootstrap.min.js"></script-->
    <script src="lib/gila.min.js"></script>


</head>

<body style="background:#f5f5f5">

    <div class="g-group fullwidth" style="vertical-align:baseline; background:white ">
        <span class="g-group bordered">
            <a href="#menu-toggle" class="btn btn-white g-group-item" id="menu-toggle" title="Toggle Menu"><i class='fa fa-bars'></i></a>
            <a href="<?=gila::config('base')?>" class="btn btn-white g-group-item" title="Homepage" target="_blank"><i class='fa fa-home'></i></a>
        </span>
        <span class="g-group g-group-item fullscreen text-align-right fa pad">
            <?=session::key('user_name')?> <a href="<?=gila::config('base')?>admin/logout">Logout</a>
        </span>
    </div>


    <div id="wrapper">

        <!-- Sidebar g-nav vertical -->
        <div id="sidebar-wrapper" style="top:43px">
            <div class="g-group-item pad">
                <img style="width:40px" src="assets/gila-logo.svg">
            </div>
            <ul class="g-nav vertical">
                <?php
                    foreach (gila::$amenu as $key => $value) {
                        echo "<li><a href='{$value[1]}'><i class='fa fa-{$value['icon']}'></i> {$value[0]}</a>";
                        if(isset($value['children'])) {
                            echo "<ul class=\"dropdown\">";
                            foreach ($value['children'] as $subkey => $subvalue) {
                                echo "<li><a href='{$subvalue[1]}'><i class='fa fa-{$subvalue['icon']}'></i> {$subvalue[0]}</a></li>";
                            }
                            echo "</ul>";
                        }
                        echo "</li>";
                    }
                ?>
            </ul>
        </div>
        <!-- /#sidebar-wrapper -->

        <!-- Page Content -->
        <div class="col-md-12">
            <div style="background:#ddd; padding:6px" class="row caption">
                <div style="font-size:22px; padding-left: 15px;">
                    <?php
                    $cn = router::controller();
                    $c = new $cn();
                    if (isset($c->icons)) {
                        if (isset($c->icons[router::action()]))
                            echo "<i class='fa fa-{$c->icons[router::action()]}'></i> ";
                    }
                     ?>
                    <?=ucwords(router::action())?>
                </div>
            </div>
            <div class="wrapper" id='main-wrapper'>