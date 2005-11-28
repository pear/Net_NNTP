<?php
//
// +----------------------------------------------------------------------+
// | PHP Version 4                                                        |
// +----------------------------------------------------------------------+
// | Copyright (c) 1997-2003 The PHP Group                                |
// +----------------------------------------------------------------------+
// | This source file is subject to version 2.0 of the PHP license,       |
// | that is bundled with this package in the file LICENSE, and is        |
// | available at through the world-wide-web at                           |
// | http://www.php.net/license/2_02.txt.                                 |
// | If you did not receive a copy of the PHP license and are unable to   |
// | obtain it through the world-wide-web, please send a note to          |
// | license@php.net so we can mail you a copy immediately.               |
// +----------------------------------------------------------------------+
// | Authors: Alexander Merz <alexmerz@php.net>                           |
// |          Heino H. Gehlsen <heino@gehlsen.dk>                         |
// +----------------------------------------------------------------------+
//
// $Id$

$host = 'news.php.net';
$debug = false;

?>
<html>

<head>
    <title>NNTP <?php echo $host; ?></title>
</head>

<body>
<?php

require_once "Net/NNTP/Client.php";

$nntp = new Net_NNTP_Client();
$nntp->setDebug($debug);

$posting = $nntp->connect($host);
if (PEAR::isError($posting)) {
    echo '<font color="red">No connection to newsserver!</font><br>';
    die($posting->getMessage());
}

$groups = $nntp->getGroups();
if (PEAR::isError($groups)) {
    die('<font color="red">' . $groups->getMessage() . '</font><br>');
}


$descriptions = $nntp->getDescriptions();
if (PEAR::isError($descriptions)) {
    echo '<font color="red">' . $descriptions->getMessage() . '</font><br>';
}

echo "<h1>Avaible groups</h1>";

foreach($groups as $group) {
    echo '<a href="group.php?group=', urlencode($group['group']), '&writable=', urlencode($group['posting']), '">', $group['group'], '</a> ';
    echo '(', ($group['last'] - $group['first']), ' messages)<br>';
    echo $descriptions[$group['group']], '<br><br>';
}

$nntp->quit();

?>
</body>

</html>
