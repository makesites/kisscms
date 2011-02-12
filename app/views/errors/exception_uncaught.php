<?php
header("HTTP/1.0 500 Internal Server Error: Uncaught Exception");
?>
<!DOCTYPE HTML PUBLIC "-//IETF//DTD HTML 2.0//EN">
<html>
<head>
<title>Exception Occurred</title>
</head>
<body>
<h1>Exception Occurred</h1>
<p><?=isset($message) ? "<pre>$message</pre>":'Unknown uncaught exception occured.';?></p>
<hr />
<p>Powered By: <a href="http://kissmvc.com">KISSMVC</a></p>
</body></html>