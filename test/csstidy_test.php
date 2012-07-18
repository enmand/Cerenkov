<?php

require_once('../classes/css/CSSTidy.php');

$form_code = <<<HERE
<html>
<head>
	<title>CSS Tidy</title>
</head>

<body>
<h1>CSS Tidy</h1>
<form enctype="multipart/form-data" method="POST" action="$PHP_SELF">
	<input type="hidden" name="MAX_FILE_SIZE" value="2097152" />
	<input type="file" name="cssfile" />
	<input type="submit" name="submit" value="Submit" />
</form>
</body>
</html>
HERE;

if ( $_SERVER['REQUEST_METHOD'] == 'GET' )
{
	// Show form
	echo $form_code;
}
else if ( $_SERVER['REQUEST_METHOD'] == 'POST' )
{
	// Tidy CSS
	$form_file = $_FILES['cssfile']['tmp_name'];
	$css = file_get_contents($form_file);
	unlink($form_file);
	$tidy = new CSSTidy($css);
	header('Content-Type: text/css');
	echo $tidy->tidyCSS();
}
else
{
	die('Unsupported HTTP request.');
}

?>
