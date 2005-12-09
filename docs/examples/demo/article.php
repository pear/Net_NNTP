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
$messageNum = isset($_GET['msgnum']) && !empty($_GET['msgnum'])? $_GET['msgnum'] : null;
$messageID = isset($_GET['msgid']) && !empty($_GET['msgid']) ? $_GET['msgid'] : null;

// Validate local input
if (is_null($group) and is_null($messageID)) {
    die('<b><font color="#cc0000">Error: Nither group nor message-id provided!</font></b>');
}

if (!is_null($group) and is_null($messageNum)) {
    die('<b><font color="#cc0000">Error: Group needs message number!</font></b>');
}

if (!is_null($messageID) and !is_null($messageNum)) {
    die('<b><font color="#cc0000">Error: Both message-id AND message number provided!</font></b>');
}

//
require_once 'Net/NNTP/Client.php';

//
$nntp = new Net_NNTP_Client();
$nntp->setLogger($logger);

// Connect
$posting = $nntp->connect($host, $transport, $port);
if (PEAR::isError($posting)) {
    echo '<font color="#cc0000">No connection to newsserver: ', $posting->getMessage(), '</font>';
}

// If asked for a article in a group, select group, then article
if ($group !== null && $messageNum !== null) {
    $currentgroup = $nntp->selectGroup($group);
    if (PEAR::isError($currentgroup)) {
        die('<b><font color="cc0000">' . $currentgroup->getMessage() . '</font></b>');
    }

    $currentmessage = $nntp->selectArticle($messageNum);
    if (PEAR::isError($currentmessage)) {
        die('<b><font color="cc0000">' . $currentmessage->getMessage() . '</font></b>');
    }
}


// Fetch 'Subject' header field
$subject = $nntp->getHeaderField('Subject', $messageID);
if (PEAR::isError($subject)) {
    $logger->warning('Error: Error fetching \'Subject\' header field (Server response: ' . $subject->getMessage() . ')');
    $subject = null;
}

// Fetch 'From' header field
$from = $nntp->getHeaderField('From', $messageID);
if (PEAR::isError($from)) {
    $logger->warning('Error: Error fetching \'From\' header field (Server response: ' . $from->getMessage() . ')');
    $from = null;
}

// Fetch 'From' header field
$groups = $nntp->getHeaderField('Newsgroups', $messageID);
if (PEAR::isError($groups)) {
    $logger->warning('Error: Error fetching \'Newsgroups\' header field (Server response: ' . $groups->getMessage() . ')');
    $groups = null;
}
// Fetch list of references
$references = $nntp->getReferences($messageID);
if (PEAR::isError($references)) {
    $logger->warning('Error: Error fetching \'References\' header field (Server response: ' . $references->getMessage() . ')');
    $references = array();
}

// Fetch Raw header and body
$header = $nntp->getHeader($messageID, true);
$body = $nntp->getBody($messageID, true);



if (empty($subject)) {
    $logger->info('Empty \'Subject\' header.');
    $subject = '[unknown]';
}
if (empty($from)) {
    $logger->info('Empty \'From\' header.');
    $from = '[unknown]';
}
if (empty($groups)) {
    $logger->info('Empty \'Newsgroups\' header.');
    $groups = '[unknown]';
}



// Close connection
$nntp->quit();

// Output
echo '<h1>Subject: ', $subject , '</h1>';
echo 'by: ', $from , '<br>';
echo 'in: ', $groups, '<br>';
echo '<hr>';
echo '<b>References:</b>';
if (!PEAR::isError($references)) {
    foreach ($references as $reference) {
	echo ' <a href="article.php?', "host=$host&port=$port&msgid=$reference&loglevel=$loglevel", '">#', ++$i, '</a>';
    }
}
echo '<hr>';
echo '<h2>Header</h2>';
echo '<pre>';
echo preg_replace("/\r\n(\t| )+/", ' ', $header);
echo '</pre>';
echo '<hr>';
echo '<h2>Body</h2>';
echo '<form><textarea wrap="off" cols="79", rows="25">', $body, '</textarea></form>';

?>
</body>

</html>
