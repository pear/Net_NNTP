<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4 foldmethod=marker: */

/**
 * 
 * 
 * PHP versions 4 and 5
 *
 * +-----------------------------------------------------------------------+
 * |                                                                       |
 * | W3C® SOFTWARE NOTICE AND LICENSE                                      |
 * | http://www.w3.org/Consortium/Legal/2002/copyright-software-20021231   |
 * |                                                                       |
 * | This work (and included software, documentation such as READMEs,      |
 * | or other related items) is being provided by the copyright holders    |
 * | under the following license. By obtaining, using and/or copying       |
 * | this work, you (the licensee) agree that you have read, understood,   |
 * | and will comply with the following terms and conditions.              |
 * |                                                                       |
 * | Permission to copy, modify, and distribute this software and its      |
 * | documentation, with or without modification, for any purpose and      |
 * | without fee or royalty is hereby granted, provided that you include   |
 * | the following on ALL copies of the software and documentation or      |
 * | portions thereof, including modifications:                            |
 * |                                                                       |
 * | 1. The full text of this NOTICE in a location viewable to users       |
 * |    of the redistributed or derivative work.                           |
 * |                                                                       |
 * | 2. Any pre-existing intellectual property disclaimers, notices,       |
 * |    or terms and conditions. If none exist, the W3C Software Short     |
 * |    Notice should be included (hypertext is preferred, text is         |
 * |    permitted) within the body of any redistributed or derivative      |
 * |    code.                                                              |
 * |                                                                       |
 * | 3. Notice of any changes or modifications to the files, including     |
 * |    the date changes were made. (We recommend you provide URIs to      |
 * |    the location from which the code is derived.)                      |
 * |                                                                       |
 * | THIS SOFTWARE AND DOCUMENTATION IS PROVIDED "AS IS," AND COPYRIGHT    |
 * | HOLDERS MAKE NO REPRESENTATIONS OR WARRANTIES, EXPRESS OR IMPLIED,    |
 * | INCLUDING BUT NOT LIMITED TO, WARRANTIES OF MERCHANTABILITY OR        |
 * | FITNESS FOR ANY PARTICULAR PURPOSE OR THAT THE USE OF THE SOFTWARE    |
 * | OR DOCUMENTATION WILL NOT INFRINGE ANY THIRD PARTY PATENTS,           |
 * | COPYRIGHTS, TRADEMARKS OR OTHER RIGHTS.                               |
 * |                                                                       |
 * | COPYRIGHT HOLDERS WILL NOT BE LIABLE FOR ANY DIRECT, INDIRECT,        |
 * | SPECIAL OR CONSEQUENTIAL DAMAGES ARISING OUT OF ANY USE OF THE        |
 * | SOFTWARE OR DOCUMENTATION.                                            |
 * |                                                                       |
 * | The name and trademarks of copyright holders may NOT be used in       |
 * | advertising or publicity pertaining to the software without           |
 * | specific, written prior permission. Title to copyright in this        |
 * | software and any associated documentation will at all times           |
 * | remain with copyright holders.                                        |
 * |                                                                       |
 * +-----------------------------------------------------------------------+
 *
 * @category   Net
 * @package    Net_NNTP
 * @author     Heino H. Gehlsen <heino@gehlsen.dk>
 * @copyright  2002-2005 Heino H. Gehlsen <heino@gehlsen.dk>. All Rights Reserved.
 * @license    http://www.w3.org/Consortium/Legal/2002/copyright-software-20021231 W3C® SOFTWARE NOTICE AND LICENSE
 * @version    CVS: $Id$
 * @link       http://pear.php.net/package/Net_NNTP
 * @see        
 * @since      File available since release 1.3.0
 */

?>
<html>

<head>
    <title>PEAR::Net_NNTP Demo</title>
</head>

<body>
<?php

$loglevel = isset($_GET['loglevel']) && !empty($_GET['loglevel']) ? $_GET['loglevel'] : PEAR_LOG_NOTICE;

//
require_once "Log.php";

//
$logger = &Log::factory('display', '', 'Net_NNTP Demo',
                        array('linebreak' => "<br>\r\n",
                              'error_prepend' => '',
                              'error_append' => ''
                             ),
			$loglevel);

// Register common input
$transport = isset($_GET['encryption']) && !empty($_GET['encryption']) ? $_GET['encryption'] : null;
$host = isset($_GET['host']) && !empty($_GET['host']) ? $_GET['host'] : null;
$port = isset($_GET['port']) && !empty($_GET['port']) ? $_GET['port'] : null;

// Register local input
$group = isset($_GET['group']) && !empty($_GET['group']) ? $_GET['group'] : null;
$from = isset($_GET['from']) && !empty($_GET['from']) ? $_GET['from'] : null;
$next = isset($_GET['next']) && !empty($_GET['next']) ? true : false;

// Validate local input
if (empty($group)) {
    die('<font color="cc0000">No newsgroup choosed!</font>');
}

//
require_once "Net/NNTP/Client.php";

//
$nntp = new Net_NNTP_Client();
$nntp->setLogger($logger);

// Connect
$posting = $nntp->connect($host, $transport, $port);
if (PEAR::isError($posting)) {
    die('<b><font color="#cc0000">' . $posting->getMessage() . '</font></b>');    
}

// Select group
$currentgroup = $nntp->selectGroup($group);
if (PEAR::isError($currentgroup)) {
    die('<b><font color="cc0000">' . $currentgroup->getMessage() . '</font></b>');
}

if ($from) {
    // select last article
    $article = $nntp->selectArticle($from);
    if (PEAR::isError($article)) {
        die('<b><font color="#cc0000">' . $article->getMessage() . '</font></b>');
    }

    if ($next) {
        $article = $nntp->selectNextArticle();
    } else {
        $article = $nntp->selectPreviousArticle();
    }
    if (PEAR::isError($article)) {
        die('<b><font color="#cc0000">' . $article->getMessage() . '</font></b>');
    }
} else {
    // select last article
    $article = $nntp->selectArticle($nntp->last());
    if (PEAR::isError($article)) {
        die('<b><font color="#cc0000">' . $article->getMessage() . '</font></b>');
    }
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
    if ($next) {
        $article = $nntp->selectNextArticle();
    } else {
        $article = $nntp->selectPreviousArticle();
    }
    if (PEAR::isError($article)) {
        die('<font color="cc0000">' . $article->getMessage() . '</font>');
    }

}

// Disconnect
$nntp->quit();

//
if (!$next) {
    $articles = array_reverse($articles);
}

// get the first and the last article number from returned articles
$first = reset($articles);
$last = end($articles);

// Output
echo '<h1>Avaible articles in <i>', $group, ' @ ', $host, '</i>:</h1>';
echo $messageCount ;
echo '<hr>';
echo '<h2>Article ', $first['Number'], '-', $last['Number'], ' out of ', $currentgroup['last'], ' messages (', $currentgroup['count'], ' on server) :</h2>';

// Previous/Next # links
if ($first['Number'] > $nntp->first()) {
    echo '<a href="group.php?', "host=$host&port=$port&group=", urlencode($group), '&from=', $first['Number'], "&loglevel=$loglevel", '">Previous 10</a> ';
}
if ($last['Number'] < $nntp->last()) {
    echo '<a href="group.php?', "host=$host&port=$port&group=", urlencode($group), '&from=', $last['Number'], '&next=true', "&loglevel=$loglevel", '">Next 10</a> ';
}

echo '<table border="0" cellpadding="2" cellspacing="3">';
echo '<tr bgcolor="#cccccc"><th>#</th><th>Subject</th><th>Author</th><th>Date</th></tr>';
foreach ($articles as $overview) {


    $number = $overview['Number'];
    $subject = $overview['Subject'];
    $from = $overview['From'];
    $date = $overview['Date'];

    $link = 'article.php?' . "host=$host&port=$port&group=" . urlencode($group) . '&msgnum=' . urlencode($number) . "&loglevel=$loglevel";

    // Decode subject and from header fields, if mime-extension is loaded...
    if (function_exists('imap_mime_header_decode')) {
    	$decoded = imap_mime_header_decode($subject);
	$subject = '';
    	foreach ($decoded as $element) {
    	    $subject .= $element->text;
    	}

    	$decoded = imap_mime_header_decode($from);
	$from = '';
    	foreach ($decoded as $element) {
    	    $from .= $element->text;
    	}
    }

    echo '<tr>';
    echo '<td><a href="', $link, '">', $number, '</a></td>';
    echo '<td>';
    echo '<a href="', $link, '"><b>', $subject, '</b></a><br>';
    echo '</td>';
    echo '<td>', $from, '</td>';
    echo '<td>', str_replace(" ", "&nbsp;", strftime("%c", strtotime($date))), '</td>';

    echo '</tr>';
}

echo '</table>';
if (count($articles) <= 1) {
    echo 'No articles...';
}

?>
</body>

</html>
