<?php

$url = $_GET['u'];
$url = urldecode($url);

// ensure the URL begins with http:// in a case-insensitive way
if (stripos($url, 'http://') === 0) {
    echo '<html>
<head>
<meta http-equiv="REFRESH" content="0; URL='.$url.'">
</head>
<body>
<strong>Du wirst weitergeleitet!</strong><br />
Wenn das nicht klappt, klick hier: <a href="'.$url.'">'.$url.'</a>
</body>
</html>
';
}

?>
