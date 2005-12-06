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

if (!isset($_GET['group'])) {
    die('<font color="red">No newsgroup choosed!</font><br>');
}

$group = $_GET['group'];

require_once "Net/NNTP/Client.php";

$nntp = new Net_NNTP_Client();
$nntp->setDebug($debug);

$posting = $nntp->connect($host);
if (PEAR::isError($posting)) {
    echo '<font color="red">No connection to newsserver!</font><br>';
    die ($posting->getMessage());
}

$currentgroup = $nntp->selectGroup($group);
if (PEAR::isError($currentgroup)) {
    echo '<font color="red">' . $currentgroup->getMessage() . '</font><br>';
}

$messageCount = $currentgroup['last'] - $currentgroup['first'];

echo '<h1>', $group, '</h1>';
echo '<b>Message count:</b>&nbsp;', $messageCount;
echo '<br><b>Posting: </b>&nbsp;';
switch ($_GET['writable']) {
    case 'y' :
        echo 'Allowed';
        break;
    case 'n' :
        echo 'Disallowed';
        break;
    case 'm' :
        echo 'Moderated';
        break;         
    default:
        echo 'Unknown';
}
echo '<hr>';
echo '<h2>last 10 messages</h2>';
                
$overview = $nntp->getOverview( $messageCount-9, $messageCount);
if (PEAR::isError($overview)) {
    die('<font color="red">' . $overview->getMessage() . '</font><br>');
}

foreach ($overview as $message) {
    $messageNumber = $message['Number'];
    $messageID = $message['Message-ID'];

    echo '<a href="read.php?msgid=', urlencode($messageID), '&group=', urlencode($_GET['group']), '"><b>', $message['Subject'], '</b></a><br>';
    echo 'from:&nbsp;', $message['From'], '<br>';
    echo $message['Date'], '<br><br>';
}

$nntp->quit();

?>
</body>

</html>
