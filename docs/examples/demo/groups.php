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
$wildmat = isset($_GET['wildmat']) && !empty($_GET['wildmat']) ? $_GET['wildmat'] : null;

//
require_once "Net/NNTP/Client.php";
require_once "Net/NNTP/Message.php";
require_once "Net/NNTP/Header.php";

//
$nntp = new Net_NNTP_Client();
$nntp->setLogger($logger);

// Connect
$posting = $nntp->connect($host, $transport, $port);
//$posting = $nntp->connect();
if (PEAR::isError($posting)) {
    die('<b><font color="#cc0000">' . $posting->getMessage() . '</font></b>');
}

// Fetch list of groups
$groups = $nntp->getGroups($wildmat);
if (PEAR::isError($groups)) {
    die('<b><font color="cc0000">' . $groups->getMessage() . '</font></b>');
}

// Fetch known (to the server) group descriptions
$descriptions = $nntp->getDescriptions($wildmat);
if (PEAR::isError($descriptions)) {
    $logger->notice(' (' . $descriptions->getMessage() . ')');
    $descriptions = array();
}

// Close connection
$nntp->quit();

// Output
echo '<h1>Avaible groups on <i>', $host, ':', $port, '</i></h1><hr>';

echo '<table border="0" cellpadding="2" cellspacing="3">';
echo '<tr bgcolor="#cccccc"><th>Group</th><th>Articles</th><th>Description</th><th>Posting</th></tr>';
foreach($groups as $group) {

    switch ($group['posting']) {
        case 'y':
	    $color = '#006600';
	    $posting = 'yes';
	    break;
        case 'n':
	    $color = '#660000';
	    $posting = 'no';
	    break;
        case 'm':
	    $color = '#666600';
	    $posting = 'moderated';
	    break;
        default:
	    $color = '#666666';
	    $posting = 'unknown';
    }

    echo '<tr>';
    echo '<td>';
    echo '<b><a href="group.php?', "host=$host&port=$port&group=", urlencode($group['group']), "&loglevel=$loglevel", '">', '<font color="', $color , '">', $group['group'], '</font>', '</a></b>';
    echo '</td>';
    echo '<td align="center">', ($group['last'] - $group['first'] + 1), '</td>';
    echo '<td>';
    if (!empty($descriptions[$group['group']])) {
    	echo $descriptions[$group['group']];
    }
    echo '</td>';
    echo '<td>', $posting, '</td>';
    echo '</tr>';
}

echo '</table>';

?>
</body>

</html>
