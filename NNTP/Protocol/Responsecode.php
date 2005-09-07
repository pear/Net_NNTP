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


// {{{ constants

// Connection

define('NET_NNTP_PROTOCOL_RESPONSECODE_READY_POSTING_ALLOWED', 200);
define('NET_NNTP_PROTOCOL_RESPONSECODE_READY_POSTING_NOT_ALLOWED', 201);
define('NET_NNTP_PROTOCOL_RESPONSECODE_CLOSING_CONNECTION', 205);

define('NET_NNTP_PROTOCOL_RESPONSECODE_SERVICE_DISCONTINUED', 400);

define('NET_NNTP_PROTOCOL_RESPONSECODE_SLAVE_STATUS', 202);

// Common failures

define('NET_NNTP_PROTOCOL_RESPONSECODE_COMMAND_NOT_RECOGNIZED', 500);
define('NET_NNTP_PROTOCOL_RESPONSECODE_COMMAND_SYNTAX_ERROR', 501);

define('NET_NNTP_PROTOCOL_RESPONSECODE_NO_PERMISSION', 502);
define('NET_NNTP_PROTOCOL_RESPONSECODE_NOT_PERFORMED', 503);

// Common request failures

define('NET_NNTP_PROTOCOL_RESPONSECODE_NO_SUCH_GROUP', 411);

define('NET_NNTP_PROTOCOL_RESPONSECODE_NO_GROUP_SELECTED', 412);
define('NET_NNTP_PROTOCOL_RESPONSECODE_NO_ARTICLE_SELECTED', 420);

define('NET_NNTP_PROTOCOL_RESPONSECODE_NO_NEXT_ARTICLE', 421);
define('NET_NNTP_PROTOCOL_RESPONSECODE_NO_PREVIOUS_ARTICLE', 422);

define('NET_NNTP_PROTOCOL_RESPONSECODE_NO_SUCH_ARTICLE_NUMBER', 423);
define('NET_NNTP_PROTOCOL_RESPONSECODE_NO_SUCH_MESSAGE_ID', 430);

// Groups

define('NET_NNTP_PROTOCOL_RESPONSECODE_GROUP_SELECTED', 211);
define('NET_NNTP_PROTOCOL_RESPONSECODE_GROUPS_FOLLOWS', 215);

define('NET_NNTP_PROTOCOL_RESPONSECODE_NEW_GROUPS_FOLLOWS', 231);

// Articles

define('NET_NNTP_PROTOCOL_RESPONSECODE_ARTICLE_FOLLOWS', 220);
define('NET_NNTP_PROTOCOL_RESPONSECODE_HEAD_FOLLOWS', 221);
define('NET_NNTP_PROTOCOL_RESPONSECODE_BODY_FOLLOWS', 222);
define('NET_NNTP_PROTOCOL_RESPONSECODE_ARTICLE_SELECTED', 223);

// Transferring

define('NET_NNTP_PROTOCOL_RESPONSECODE_TRANSFER_NOT_WANTED', 435);
define('NET_NNTP_PROTOCOL_RESPONSECODE_TRANSFER_START', 335);
define('NET_NNTP_PROTOCOL_RESPONSECODE_TRANSFER_SUCCESS', 235);
define('NET_NNTP_PROTOCOL_RESPONSECODE_TRANSFER_FAILURE', 436);
define('NET_NNTP_PROTOCOL_RESPONSECODE_TRANSFER_REJECTED', 437);

// Posting

define('NET_NNTP_PROTOCOL_RESPONSECODE_POSTING_NOT_ALLOWED', 440);
define('NET_NNTP_PROTOCOL_RESPONSECODE_POSTING_START', 340);
define('NET_NNTP_PROTOCOL_RESPONSECODE_POSTING_SUCCESS', 240);
define('NET_NNTP_PROTOCOL_RESPONSECODE_POSTING_FAILURE', 441);

// Authorization

define('NET_NNTP_PROTOCOL_RESPONSECODE_AUTHORIZATION_REQUIRED', 450);
define('NET_NNTP_PROTOCOL_RESPONSECODE_AUTHORIZATION_CONTINUE', 350);
define('NET_NNTP_PROTOCOL_RESPONSECODE_AUTHORIZATION_ACCEPTED', 250);
define('NET_NNTP_PROTOCOL_RESPONSECODE_AUTHORIZATION_REJECTED', 452);

// Authentication

define('NET_NNTP_PROTOCOL_RESPONSECODE_AUTHENTICATION_REQUIRED', 480);
define('NET_NNTP_PROTOCOL_RESPONSECODE_AUTHENTICATION_CONTINUE', 381);
define('NET_NNTP_PROTOCOL_RESPONSECODE_AUTHENTICATION_ACCEPTED', 281);
define('NET_NNTP_PROTOCOL_RESPONSECODE_AUTHENTICATION_REJECTED', 482);


// }}}

?>
