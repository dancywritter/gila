
<head>
	<link type="text/css" rel="stylesheet" href="../lib/gila.min.css"/>
	<title>Install Gila CMS</title>
</head>
<body class="bg-lightgrey">
<div class="gm-6 centered row" style="">
    <div class="gm-12 wrapper text-align-center">
        <h1 class="margin-0">Gila CMS Installation</h1>
    </div>
<?php
$ext = ['mysqli','zip','mysqlnd','json'];

foreach($ext as $k=>$v) if(!extension_loaded($v))
		echo "<span class='alert fullwidth'>Extension $v in not loaded.</span>";
?>
<form method="post" class="row gap-16px bordered box-shadow g-form bg-white">
	<div class="gl-6">
	<label class="gs-12">Hostname</label>
	<input name="db_host" value="localhost" required>
	<label class="gs-12">Database</label>
	<input name="db_name" required>
	<label class="gs-12">DB Username</label>
	<input name="db_user" required>
	<label class="gs-12">DB Password</label>
	<input name="db_pass" type="password">
	</div>
	<div class="gl-6">
	<label class="gs-12">Admin Username</label>
	<input name="adm_user" required>
	<label class="gs-12">Admin Email</label>
	<input name="adm_email" type="email" required>
	<label class="gs-12">Admin Password</label>
	<input name="adm_pass" type="password" required>
	<label class="gs-12">Base URL</label>
		<input name="base_url" value="" required>
	</div>
	<div class="gl-12"><input class="btn success" type="submit"></div>
</form>

</div>
