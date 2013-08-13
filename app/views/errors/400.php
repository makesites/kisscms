<!DOCTYPE HTML PUBLIC "-//IETF//DTD HTML 2.0//EN">
<html>
<head>
<title>400 Bad Request</title>
</head>
<body>
	<h1>400 Bad Request: <?=$type?></h1>
	<hr />
	<p>Fatal error on line <?=$errline?> in file <?=$file?>, PHP <?=PHP_VERSION ?> (<?=PHP_OS ?>)</p>
	<p style="font-weight:bold;color:#F00"><?=$message?></p>
	<p>Aborting...</p>
	<p>Click <a href="javascript: history.back(1)">here</a> to go back to where you were.</p>
</body>
</html>