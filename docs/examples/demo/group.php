<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4 foldmethod=marker: */
// +-----------------------------------------------------------------------+
// |                                                                       |
// | Copyright © 2003 Heino H. Gehlsen. All Rights Reserved.               |
// |                  http://www.heino.gehlsen.dk/software/license         |
// |                                                                       |
// +-----------------------------------------------------------------------+
// |                                                                       |
// | This work (including software, documents, or other related items) is  |
// | being provided by the copyright holders under the following license.  |
// | By obtaining, using and/or copying this work, you (the licensee)      |
// | agree that you have read, understood, and will comply with the        |
// | following terms and conditions:                                       |
// |                                                                       |
// | Permission to use, copy, modify, and distribute this software and     |
// | its documentation, with or without modification, for any purpose and  |
// | without fee or royalty is hereby granted, provided that you include   |
// | the following on ALL copies of the software and documentation or      |
// | portions thereof, including modifications, that you make:             |
// |                                                                       |
// | 1. The full text of this NOTICE in a location viewable to users of    |
// |    the redistributed or derivative work.                              |
// |                                                                       |
// | 2. Any pre-existing intellectual property disclaimers, notices, or    |
// |    terms and conditions. If none exist, a short notice of the         |
// |    following form (hypertext is preferred, text is permitted) should  |
// |    be used within the body of any redistributed or derivative code:   |
// |    "Copyright © 2003 Heino H. Gehlsen. All Rights Reserved.           |
// |     http://www.heino.gehlsen.dk/software/license"                     |
// |                                                                       |
// | 3. Notice of any changes or modifications to the files, including     |
// |    the date changes were made. (We recommend you provide URIs to      |
// |    the location from which the code is derived.)                      |
// |                                                                       |
// | THIS SOFTWARE AND DOCUMENTATION IS PROVIDED "AS IS," AND COPYRIGHT    |
// | HOLDERS MAKE NO REPRESENTATIONS OR WARRANTIES, EXPRESS OR IMPLIED,    |
// | INCLUDING BUT NOT LIMITED TO, WARRANTIES OF MERCHANTABILITY OR        |
// | FITNESS FOR ANY PARTICULAR PURPOSE OR THAT THE USE OF THE SOFTWARE    |
// | OR DOCUMENTATION WILL NOT INFRINGE ANY THIRD PARTY PATENTS,           |
// | COPYRIGHTS, TRADEMARKS OR OTHER RIGHTS.                               |
// |                                                                       |
// | COPYRIGHT HOLDERS WILL NOT BE LIABLE FOR ANY DIRECT, INDIRECT,        |
// | SPECIAL OR CONSEQUENTIAL DAMAGES ARISING OUT OF ANY USE OF THE        |
// | SOFTWARE OR DOCUMENTATION.                                            |
// |                                                                       |
// | The name and trademarks of copyright holders may NOT be used in       |
// | advertising or publicity pertaining to the software without specific, |
// | written prior permission. Title to copyright in this software and any |
// | associated documentation will at all times remain with copyright      |
// | holders.                                                              |
// |                                                                       |
// +-----------------------------------------------------------------------+
// |                                                                       |
// | This license is based on the "W3C® SOFTWARE NOTICE AND LICENSE".      |
// | No changes have been made to the "W3C® SOFTWARE NOTICE AND LICENSE",  |
// | except for the references to the copyright holder, which has either   |
// | been changes or removed.                                              |
// |                                                                       |
// +-----------------------------------------------------------------------+
// $Id$
?>
<html>

<head>
    <title>PEAR::Net_NNTP Demo</title>
</head>

<body>
<?php

$debug =  isset($_GET['debug']) && !empty($_GET['debug']) ? true : false;

$host = isset($_GET['host']) ? $_GET['host'] : null;
$port = isset($_GET['port']) ? $_GET['port'] : null;

$group = isset($_GET['group']) ? $_GET['group'] : null;

if (empty($host)) {
    die('<font color="cc0000">No host set!</font>');
}

if (empty($port)) {
    die('<font color="cc0000">No port set!</font>');
}

if (empty($port)) {
    die('<font color="cc0000">No newsgroup choosed!</font>');
}

//
require_once "Net/NNTP/Client.php";

//
$nntp = new Net_NNTP_Client();
$nntp->setDebug($debug);

// Connect
$posting = $nntp->connect($host, $port);
if (PEAR::isError($posting)) {
    echo '<font color="#cc0000">No connection to newsserver!</font>';
    die ($posting->getMessage());
}

// Select group
$currentgroup = $nntp->selectGroup($group);
if (PEAR::isError($currentgroup)) {
    echo '<font color="#cc0000">' . $currentgroup->getMessage() . '</font><br>';
}

// select last article
$article = $nntp->selectArticle($nntp->last());
if (PEAR::isError($article)) {
    die('<font color="#cc0000">' . $article->getMessage() . '</font><br>');
}

//
$i = 0;
$articles = array();

// Loop until break inside
while (true) {

    // Break if no more articles
    if ($article === false) {
    	break;
    }

    // Fetch overview for currently selected article
    $overview = $nntp->getOverview();
    if (PEAR::isError($overview)) {
        die('<font color="#cc0000">' . $overview->getMessage() . '</font><br>');
    }

    //
    $articles[] = $overview;

    // Break if max article reached
    if (++$i >= 10) {
    	break;
    }

    // Select previous article
    $article = $nntp->selectPreviousArticle();
    if (PEAR::isError($article)) {
        die('<font color="cc0000">' . $article->getMessage() . '</font>');
    }

}

// Disconnect
$nntp->quit();

//
$articles = array_reverse($articles);

// Output
echo '<h1>Avaible articles in <i>', $group, ' @ ', $host, '</i>:</h1>';
echo $messageCount ;
echo '<hr>';
echo '<h2>Last ', $i, ' out of ', $currentgroup['count'], ' messages:</h2>';

echo '<table border="0" cellpadding="2" cellspacing="3">';
echo '<tr bgcolor="#cccccc"><th>#</th><th>Subject</th><th>Author</th><th>Date</th></tr>';


foreach ($articles as $overview) {

    $link = 'article.php?' . "host=$host&port=$port" . ($debug ? '&debug=true' : '') . '&group=' . urlencode($group) . '&msgnum=' . urlencode($overview['Number']);

    echo '<tr>';
    echo '<td><a href="', $link, '">', $overview['Number'], '</a></td>';
    echo '<td>';
    echo '<a href="article.php?', "host=$host&port=$port", ($debug ? '&debug=true' : ''), '&group=', urlencode($group), '&msgnum=', urlencode($overview['Number']), '"><b>', $overview['Subject'], '</b></a><br>';
    echo '</td>';
    echo '<td>', $overview['From'], '</td>';
    echo '<td>', str_replace(" ", "&nbsp;", strftime("%c", strtotime($overview['Date']))), '</td>';

    echo '</tr>';
}

echo '</table>';
if (count($articles) <= 1) {
    echo 'No articles...';
}

?>
</body>

</html>
