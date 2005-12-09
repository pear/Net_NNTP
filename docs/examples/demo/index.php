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
<h1>PEAR::Net_NNTP demo</h1><hr>

<form action="groups.php" method="GET">
<table border="0" cellspacing="2" cellpadding="2">
<tr><td>Host:</td><td><input type="text" name="host" value="news.php.net"></td><td>(If empty, use default 'localhost')</td></tr>
<tr><td>Port:</td><td><input type="text" name="port" value=""></td><td>(If empty, use default: '119' on non-encrypted connections, and '563' on encrypted connections)</td></tr>
<tr><td>Windmat:</td><td><input type="text" name="wildmat" value=""></td><td>(Group wildmat)</td></tr>
<tr><td colspan="3"><hr></td></tr>
<tr><td valign="top">Encryption:</td><td><input type="radio" name="encryption" value="" checked="checked">none<br><input type="radio" name="encryption" value="tls">TLS<br><input type="radio" name="encryption" value="ssl">SSL</td><td>(Requires a running NNTPS server)</td></tr>
<tr><td colspan="3"><hr></td></tr>
<tr><td valign="top">Loglevel:</td><td><input type="radio" name="loglevel" value="4" checked="checked">warning<br><input type="radio" name="loglevel" value="5" checked="checked">notice<br><input type="radio" name="loglevel" value="6">info<br><input type="radio" name="loglevel" value="7">debug</td><td>(Application logging level)</td></tr>
<tr><td colspan="3"><hr></td></tr>
<tr><td></td><td><input type="submit" value="View newsgroups"></td></tr>
</table>
</form>

</body>

</html>
