<?php
header("HTTP/1.0 500 Internal Server Error: Uncaught Exception");
?>
<!DOCTYPE HTML PUBLIC "-//IETF//DTD HTML 2.0//EN">
<html>
<head>
<title>500 - Exception Occurred</title>
</head>
<body>
<h1>500 Internal Server Error: Uncaught Exception</h1>
<p><?=isset($message) ? "<pre>$message</pre>":'Unknown uncaught exception occured.';?></p>
</body></html>