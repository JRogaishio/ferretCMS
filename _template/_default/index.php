<!DOCTYPE html>

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<title><?php echo $page->title; ?></title>
	<link rel="stylesheet" type="text/css" href="<?php echo SITE_ROOT . TEMPLATE_PATH . "/" . $page->templatePath . "/main.css";?>" />
	
</head>

<body>
	<div id="main">
	<?php $cms->load_navigation(array("home","blog","archive")); ?>
	
	<h1><?php echo $page->title; ?></h1>
	<?php

	
	$page->display_posts(5,true);
	

	?>
	</div>
</body>

</html>

