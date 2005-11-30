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

require_once 'Net/NNTP/Protocol/Client.php';


// {{{ constants

/* NNTP Authentication modes */
define('NET_NNTP_CLIENT_AUTH_ORIGINAL', 'original');
define('NET_NNTP_CLIENT_AUTH_SIMPLE',   'simple');
define('NET_NNTP_CLIENT_AUTH_GENERIC',  'generic');

// }}}
// {{{ Net_NNTP_Client

/**
 * Implementation of the client side of NNTP (Network News Transfer Protocol)
 *
 * The Net_NNTP_Client class is a frontend class to the Net_NNTP_Protocol_Client class.
 *
 * @category   Net
 * @package    Net_NNTP
 * @author     Heino H. Gehlsen <heino@gehlsen.dk>
 * @version    $Id$
 * @access     public
 * @see        Net_NNTP_Protocol_Client
 * @since      Class available since Release 0.11.0
 */
class Net_NNTP_Client extends Net_NNTP_Protocol_Client
{
    // {{{ properties

    /**
     * Information summary about the currently selected group.
     *
     * @var array
     * @access private
     * @since 0.3
     */
    var $_currentGroupSummary = null;

    // }}}
    // {{{ constructor

    /**
     * Constructor
     *
     * @access public
     */
    function Net_NNTP_Client()
    {
    	parent::Net_NNTP_Protocol_Client();
    }

    // }}}
    // {{{ connect()

    /**
     * Connect to a server.
     *
     * @param string	$host	(optional) The hostname og IP-address of the NNTP-server to connect to, defaults to localhost.
     * @param int	$port	(optional) The port number to connect to, defaults to 119.
     *
     * @return mixed (bool)	True when posting allowed, otherwise false
     *               (object)	Pear_Error on failure
     * @access public
     * @see Net_NNTP_Client::quit()
     * @see Net_NNTP_Client::authenticate()
     */
    function connect($host = NET_NNTP_PROTOCOL_CLIENT_DEFAULT_HOST,
                     $port = NET_NNTP_PROTOCOL_CLIENT_DEFAULT_PORT)
    {
    	return parent::connect($host, $port);
    }

    // }}}
    // {{{ quit()

    /**
     * Disconnect from server.
     *
     * @access public
     * @see Net_NNTP_Client::connect()
     */
    function quit()
    {
        return $this->cmdQuit();
    }

    // }}}
    // {{{ authenticate()

    /**
     * Authenticate.
     * 
     * Experimental: This method uses non-standard commands, which is not part
     *               of the original RFC977, but has been formalized in RFC2890.
     *
     * @param string	$user	The username
     * @param string	$pass	The password
     * @param string	$mode	(optional) The authentication mode:
     *                                     - NET_NNTP_CLIENT_AUTH_ORIGINAL
     *                                     - NET_NNTP_CLIENT_AUTH_SIMPLE
     *                                     - NET_NNTP_CLIENT_AUTH_GENERIC
     *
     * @return mixed (bool)	True on successful authentification, otherwise false
     *               (object)	Pear_Error on failure
     * @access public
     * @see Net_NNTP_Client::connect()
     */
    function authenticate($user, $pass, $mode = NET_NNTP_CLIENT_AUTH_ORIGINAL)
    {
        // Username is a must...
        if ($user == null) {
            return PEAR::throwError('No username supplied', null);
        }

        // Use selected authentication method
        switch ($mode) {
            case NET_NNTP_CLIENT_AUTH_ORIGINAL:
                return $this->cmdAuthinfo($user, $pass);
                break;

            case NET_NNTP_CLIENT_AUTH_SIMPLE:
                return $this->cmdAuthinfoSimple($user, $pass);
                break;

            case NET_NNTP_CLIENT_AUTH_GENERIC:
                return $this->cmdAuthinfoGeneric($user, $pass);
                break;

            default:
                return PEAR::throwError("The auth mode: '$mode' is unknown", null);
        }
    }

    // }}}
    // {{{ isConnected()

    /**
     * Test whether a connection is currently open or closed.
     *
     * @return bool	True if connected, otherwise false
     * @access public
     * @see Net_NNTP_Client::connect()
     * @see Net_NNTP_Client::quit()
     */
    function isConnected()
    {
        return parent::isConnected();
    }

    // }}}
    // {{{ selectGroup()

    /**
     * Selects a group.
     * 
     * Moves the servers 'currently selected group' pointer to the group 
     * a new group, and returns summary information about it.
     *
     * @param string	$group	Name of the group to select
     *
     * @return mixed (array)	Summary about the selected group 
     *               (object)	Pear_Error on failure
     * @access public
     * @see Net_NNTP_Client::getGroups()
     * @see Net_NNTP_Client::group()
     * @see Net_NNTP_Client::first()
     * @see Net_NNTP_Client::last()
     * @see Net_NNTP_Client::count()
     */
    function selectGroup($group)
    {
        $summary = $this->cmdGroup($group);
    	if (PEAR::isError($summary)) {
    	    return $summary;
    	}

    	// Store group info in the object
    	$this->_currentGroupSummary = $summary;

    	return $summary;
    }

    // }}}
    // {{{ getGroups()

    /**
     * Fetch valid groups.
     *
     * Returns a list of valid groups (that the client is permitted to select)
     * and associated information.
     *
     * @return mixed (array)	Nested array with information about every valid group
     *               (object)	Pear_Error on failure
     * @access public
     * @see Net_NNTP_Client::getDescriptions()
     * @see Net_NNTP_Client::selectGroup()
     */
    function getGroups()
    {
    	// Get groups
    	$groups = $this->cmdList();
    	if (PEAR::isError($groups)) {
    	    return $groups;
    	}

    	return $groups;
    }

    // }}}
    // {{{ getDescriptions()

    /**
     * Fetch all known group descriptions.
     *
     * Fetches a list of known group descriptions - including groups which
     * the client is not permitted to select.
     *
     * Experimental: This method uses non-standard commands, which is not part
     *               of the original RFC977, but has been formalized in RFC2890.
     *
     * @return mixed (array)	Associated array with descriptions of known groups
     *               (object)	Pear_Error on failure
     * @access public
     * @see Net_NNTP_Client::getGroups()
     */
    function getDescriptions()
    {
    	// Get group descriptions
    	$descriptions = $this->cmdListNewsgroups();
    	if (PEAR::isError($descriptions)) {
    	    return $descriptions;
    	}
	
    	return $descriptions;
    }

    // }}}
    // {{{ getOverview()

    /**
     * Fetch an overview of article(s) in the currently selected group.
     *
     * Returns the contents of all the fields in the database for a number
     * of articles specified by either article-numnber range, a message-id,
     * or nothing (indicating currently selected article).
     *
     * The first 8 fields per article is always as follows:
     *     'Number' - '0' or the article number of the currently selected group.
     *     'Subject' - header content.
     *     'From' - header content.
     *     'Date' - header content.
     *     'Message-ID' - header content.
     *     'References' - header content.
     *     ':bytes' - metadata item.
     *     ':lines' - metadata item.
     *
     * The server may send more fields form it's database...
     *
     * Experimental: This method uses non-standard commands, which is not part
     *               of the original RFC977, but has been formalized in RFC2890.
     *
     * @param integer	$first	First article to fetch
     * @param integer	$last	Last article to fetch
     *
     * @return mixed (array)	Nested array of article overview data
     *               (object)	Pear_Error on failure
     * @access public
     * @see Net_NNTP_Client::getOverviewFormat()
     * @see Net_NNTP_Client::getReferences()
     */
    function getOverview($first, $last)
    {
    	$range = $first . '-' . $last;

    	$overview = $this->cmdXOver($range);
    	if (PEAR::isError($overview)) {
    	    return $overview;
    	}
	
    	return $overview;
    }

    // }}}
    // {{{ getOverviewFormat()

    /**
     * Fetch names of fields in overview database
     *
     * Returns a description of the fields in the database for which it is consistent.
     *
     * Experimental: This method uses non-standard commands, which is not part
     *               of the original RFC977, but has been formalized in RFC2890.
     *
     * @return mixed (array)	Overview field names
     *               (object)	Pear_Error on failure
     * @access public
     * @see Net_NNTP_Client::getOverview()
     */
    function getOverviewFormat()
    {
        $format = $this->cmdListOverviewFmt();
    	if (PEAR::isError($format)) {
    	    return $format;
    	}

    	if (true) {
    	    return array_keys($format);
    	} else {
    	    return $format;
    	}
    }

    // }}}
    // {{{ getReferences()

    /**
     * Fetch reference header field of message(s).
     *
     * Retrieves the content of the references header field of messages via
     * either the XHDR ord the XROVER command.
     *
     * Identical to getHeaderField('References').
     *
     * @param integer	$first	First article to fetch
     * @param integer	$last	Last article to fetch
     *
     * Experimental: This method uses non-standard commands, which is not part
     *               of the original RFC977, but has been formalized in RFC2890.
     *
     * @return mixed (array)	Nested array of references
     *               (object)	Pear_Error on failure
     * @access public
     * @see Net_NNTP_Client::getHeaderField()
     */
    function getReferences($first, $last)
    {
    	$range = $first . '-' . $last;

    	$references = $this->cmdXHdr('References', $range);
    	if (PEAR::isError($references)) {
    	    if ($references->getCode() != 500) {
    	    	return $references;
    	    }

    	    $references = $this->cmdXROver($range);
    	    if (PEAR::isError($references)) {
    	        return $references;
    	    }
    	}

    	foreach ($references as $key => $val) {
    	    $references[$key] = preg_split("/ +/", trim($val), -1, PREG_SPLIT_NO_EMPTY);
    	}

    	return $references;
    }

    // }}}
    // {{{ getReferencesOverview()

    /**
     * Deprecated alias for getReferences()
     *
     * @deprecated
     */
    function getReferencesOverview($first, $last)
    {
    	return $this->getReferences($first, $last);
    }

    // }}}
    // {{{ getHeaderField()

    /**
     * Fetch content of a header field from message(s).
     *
     * Retreives the content of specific header field from a number of messages.
     *
     * Experimental: This method uses non-standard commands, which is not part
     *               of the original RFC977, but has been formalized in RFC2890.
     *
     * @param stringr $field	The name of the header field to retreive
     * @param integer $first	First article to fetch
     * @param integer $last	Last article to fetch
     *
     * @return mixed (array)	Nested array of 
     *               (object)	Pear_Error on failure
     * @access public
     * @see Net_NNTP_Client::getOverview()
     * @see Net_NNTP_Client::getReferencesOverview()
     */
    function getHeaderField($field, $first, $last)
    {
    	$range = $first . '-' . $last;

    	$fields = $this->cmdXHdr($field, $range);
    	if (PEAR::isError($fields)) {
    	    return $fields;
    	}

    	return $fields;
    }

    // }}}
    // {{{ post()

    /**
     * Post an article to a number of groups.
     *
     * (Among the aditional headers you might think of adding could be:
     * "NNTP-Posting-Host: <ip-of-author>", which should contain the IP-address
     * of the author of the post, so the message can be traced back to him.
     * Or "Organization: <org>" which contain the name of the organization
     * the post originates from)
     *
     * @param string	$groups	The groups to post to.
     * @param string	$subject	The subject of the article.
     * @param string	$body	The body of the article.
     * @param string	$from	Sender's email address.
     * @param mixed	$aditional	(optional) Aditional header fields to send.
     *
     * @return mixed (string)	Server response
     *               (object)	Pear_Error on failure
     * @access public
     */
    function post($groups, $subject, $body, $from, $aditional = null)
    {
    	return $this->cmdPost($groups, $subject, $body, $from, $aditional);
    }

    // }}}
    // {{{ selectArticle()

    /**
     * Selects an article by article message-number.
     *
     * @param mixed	$article	The message-number (on the server) of
     *                                  the article to select as current article.
     *
     * @return mixed (int)	Article number
     *               (bool)	False if article doesn't exists
     *               (object)	Pear_Error on failure
     * @access public
     * @see Net_NNTP_Client::selectNextArticle()
     * @see Net_NNTP_Client::selectPreviousArticle()
     */
    function selectArticle($article = null)
    {
        $response_arr = $this->cmdStat($article, 0);

    	if (PEAR::isError($response_arr)) {
    	    switch ($response_arr->getCode()) {
    	    	case NET_NNTP_PROTOCOL_RESPONSECODE_NO_SUCH_ARTICLE_NUMBER: // 423
    	    	    return false;
    	    	    break;

    	    	default:
    	    	    return $response_arr;
    	    }
	}

    	return $response_arr;
    }

    // }}}
    // {{{ selectNextArticle()

    /**
     * Select the next article.
     *
     * Select the next article in current group.
     *
     * @param int	$ret	(optional) 
     *
     * @return mixed (int)	Article number, if $ret=0 (default)
     *               (string)	Message-id, if $ret=1
     *               (array)	Both article number and message-id, if $ret=-1
     *               (bool)	False if no further articles exist
     *               (object)	Pear_Error on unexpected failure
     * @access public
     * @see Net_NNTP_Client::selectArticle()
     * @see Net_NNTP_Client::selectPreviousArticle()
     */
    function selectNextArticle($ret = 0)
    {
        $response = $this->cmdNext($ret);

    	if (PEAR::isError($response)) {
    	    switch ($response->getCode()) {
    	    	case NET_NNTP_PROTOCOL_RESPONSECODE_NO_NEXT_ARTICLE: // 421
    	    	    return false;
    	    	    break;

    	    	default:
    	    	    return $response;
    	    }
	}

    	return $response;
    }

    // }}}
    // {{{ selectPreviousArticle()

    /**
     * Select the previous article.
     *
     * Select the previous article in current group.
     *
     * @param int	$ret	(optional) 
     *
     * @return mixed (int)	Article number, if $ret=0 (default)
     *               (string)	Message-id, if $ret=1
     *               (array)	Both article number and message-id, if $ret=-1
     *               (bool)	False if no prevoius article exists
     *               (object)	Pear_Error on failure
     * @access public
     * @see Net_NNTP_Client::selectArticle()
     * @see Net_NNTP_Client::selectNextArticle()
     */
    function selectPreviousArticle($ret = 0)
    {
        $response = $this->cmdLast($ret);

    	if (PEAR::isError($response)) {
    	    switch ($response->getCode()) {
    	    	case NET_NNTP_PROTOCOL_RESPONSECODE_NO_PREVIOUS_ARTICLE: // 422
    	    	    return false;
    	    	    break;

    	    	default:
    	    	    return $response;
    	    }
    	}

    	return $response;
    }

    // }}}
    // {{{ getArticle()

    /**
     * Fetch article into transfer object.
     *
     * Select an article based on the arguments, and return the entire
     * article (raw data).
     *
     * @param mixed	$article	(optional) Either the message-id or the
     *                                  message-number on the server of the
     *                                  article to fetch.
     * @param string	$class	
     * @param bool	$implode	(optional) When true the result array
     *                                  is imploded to a string, defaults to
     *                                  false.
     *
     * @return mixed (object)	Message object specified by $class
     *               (object)	Pear_Error on failure
     * @access public
     * @see Net_NNTP_Client::getArticleRaw()
     * @see Net_NNTP_Client::getHeader()
     * @see Net_NNTP_Client::getBody()
     */
    function getArticle($article = null, $class, $implode = false)
    {
    	if (!is_string($class)) {
    	    return PEAR::throwError('UPS...');
    	}

    	if (!class_exists($class)) {
    	    return PEAR::throwError("Class '$class' does not exist!");
	}

        $message = $this->getArticleRaw($article, $implode);
        if (PEAR::isError($message)) {
    	    return $data;
    	}

	$M = new $class($message);

    	return $M;
    }

    // }}}
    // {{{ getArticleRaw()

    /**
     * Fetch article.
     *
     * Select an article based on the arguments, and return the entire
     * article (raw data)
     *
     * @param mixed	$article	(optional) Either the message-id or the
     *                                  message-number on the server of the
     *                                  article to fetch.
     * @param bool	$implode	(optional) When true the result array
     *                                  is imploded to a string, defaults to
     *                                  false.
     *
     * @return mixed (array)	Complete article (when $implode is false)
     *               (string)	Complete article (when $implode is true)
     *               (object)	Pear_Error on failure
     * @access public
     * @see Net_NNTP_Client::getArticle()
     * @see Net_NNTP_Client::getHeaderRaw()
     * @see Net_NNTP_Client::getBodyRaw()
     */
    function getArticleRaw($article, $implode = false)
    {
        $data = $this->cmdArticle($article);
        if (PEAR::isError($data)) {
    	    return $data;
    	}

    	if ($implode == true) {
    	    $data = implode("\r\n", $data);
    	}

    	return $data;
    }

    // }}}
    // {{{ getHeader()

    /**
     * Fetch article header into transfer object.
     *
     * Select an article based on the arguments, and return the article header.
     *
     * @param mixed	$article	(optional) Either message-id or message
     *                                  number of the article to fetch.
     * @param string	$class	
     * @param bool	$implode	(optional) When true the result array
     *                                  is imploded to a string, defaults to
     *                                  false.
     *
     * @return mixed (object)	Header object specified by $class
     *               (object)	Pear_Error on failure
     * @access public
     * @see Net_NNTP_Client::getHeaderRaw()
     * @see Net_NNTP_Client::getArticle()
     * @see Net_NNTP_Client::getBody()
     */
    function getHeader($article = null, $class, $implode = false)
    {
    	if (!is_string($class)) {
    	    return PEAR::throwError('UPS...');
    	}

    	if (!class_exists($class)) {
    	    return PEAR::throwError("Class '$class' does not exist!");
	}

        $header = $this->getHeaderRaw($article, $implode);
        if (PEAR::isError($header)) {
    	    return $header;
    	}

	$H = new $class($header);

    	return $H;
    }

    // }}}
    // {{{ getHeaderRaw()

    /**
     * Fetch article header.
     *
     * Select an article based on the arguments, and return the article
     * header (raw data).
     *
     * @param mixed	$article	(optional) Either the message-id or the
     *                                  message-number on the server of the
     *                                  article to fetch.
     * @param bool	$implode	(optional) When true the result array
     *                                  is imploded to a string, defaults to
     *                                  false.
     *
     * @return mixed (array)	Header fields (when $implode is false)
     *               (string)	Header fields (when $implode is true)
     *               (object)	Pear_Error on failure
     * @access public
     * @see Net_NNTP_Client::getHeader()
     * @see Net_NNTP_Client::getArticleRaw()
     * @see Net_NNTP_Client::getBodyRaw()
     */
    function getHeaderRaw($article = null, $implode = false)
    {
        $data = $this->cmdHead($article);
        if (PEAR::isError($data)) {
    	    return $data;
    	}

    	if ($implode == true) {
    	    $data = implode("\r\n", $data);
    	}

    	return $data;
    }

    // }}}
    // {{{ getBody()

    /**
     * Fetch article body into transfer object.
     *
     * Select an article based on the arguments, and return the article body.
     *
     * @param mixed	$article	(optional) Either the message-id or the
     *                                  message-number on the server of the
     *                                  article to fetch.
     * @param string	$class	
     * @param bool	$implode	(optional) When true the result array
     *                                  is imploded to a string, defaults to
     *                                  false.
     *
     * @return mixed (object)	Body object specified by $class
     *               (object)	Pear_Error on failure
     * @access public
     * @see Net_NNTP_Client::getHeader()
     * @see Net_NNTP_Client::getArticle()
     * @see Net_NNTP_Client::getBodyRaw()
     */
    function getBody($article = null, $class, $implode = false)
    {
    	if (!is_string($class)) {
    	    return PEAR::throwError('UPS...');
    	}

    	if (!class_exists($class)) {
    	    return PEAR::throwError("Class '$class' does not exist!");
	}

        $body = $this->getBodyRaw($article, $implode);
        if (PEAR::isError($body)) {
    	    return $body;
    	}

	$B = new $class($body);

    	return $B;
    }

    // }}}
    // {{{ getBodyRaw()

    /**
     * Fetch article body.
     *
     * Select an article based on the arguments, and return the article
     * body (raw data).
     *
     * @param mixed	$article	(optional) Either the message-id or the
     *                                  message-number on the server of the
     *                                  article to fetch.
     * @param bool	$implode	(optional) When true the result array
     *                                  is imploded to a string, defaults to
     *                                  false.
     *
     * @return mixed (array)	Message body (when $implode is false)
     *               (string)	Message body (when $implode is true)
     *               (object)	Pear_Error on failure
     * @access public
     * @see Net_NNTP_Client::getBody()
     * @see Net_NNTP_Client::getHeaderRaw()
     * @see Net_NNTP_Client::getArticleRaw()
     */
    function getBodyRaw($article = null, $implode = false)
    {
        $data = $this->cmdBody($article);
        if (PEAR::isError($data)) {
    	    return $data;
    	}
	
    	if ($implode == true) {
    	    $data = implode("\r\n", $data);
    	}
	
    	return $data;
    }

    // }}}
    // {{{ getGroupArticles()

    /**
     * Select a group, and return list of its article numbers.
     *
     * Selects a group in the same manner as selectGroup(), but returns a list
     * of article numbers within the group.
     *
     * Experimental: This method uses non-standard commands, which is not part
     *               of the original RFC977, but has been formalized in RFC2890.
     *
     * @param string	$group	
     *
     * @return mixed (array)	
     *               (object)	Pear_Error on failure
     * @access public
     * @since 0.3
     */
    function getGroupArticles($group)
    {
        $summary = $this->cmdListgroup($group);

	$articles = $summary['articles'];
	unset($summary['articles']);

    	$this->_currentGroupSummary = $summary;

	return $articles;
    }

    // }}}
    // {{{ getNewGroups()

    /**
     * Get new groups since a date.
     *
     * Returns a list of groups created on the server since the specified date
     * and time.
     *
     * @param mixed	$time	
     * @param string	$distributions	(optional) 
     *
     * @return mixed (array)	
     *               (object)	Pear_Error on failure
     * @access public
     * @since 0.3
     */
    function getNewGroups($time, $distributions = null)
    {
    	switch (gettype($time)) {
    	    case 'integer':
    	    	break;
    	    case 'string':
    	    	$time = (int) strtotime($time);
    	    	break;
    	    default:
    	        return PEAR::throwError('UPS...');
    	}

    	return $this->cmdNewgroups($time, $distributions);
    }

    // }}}
    // {{{ getNewArticles()

    /**
     * Get new articles since a date.
     *
     * Returns a list of message-ids of new articles (since the specified date
     * and time) in the groups whose names match the wildmat
     *
     * @param mixed	$time	
     * @param string	$groups	(optional) 
     * @param string	$distributions	(optional) 
     *
     * @return mixed (array)	
     *               (object)	Pear_Error on failure
     * @access public
     * @since 0.3
     */
    function getNewArticles($time, $groups = '*', $distribution = null)
    {
    	switch (true) {
    	    case is_integer($time):
    	    	break;

    	    case is_string($time):
    	    	$time = (int) strtotime($time);
    	    	break;

    	    default:
    	        return PEAR::throwError('UPS...');
    	}

    	return $this->cmdNewnews($time, $groups, $distribution);
    }

    // }}}
    // {{{ getNewNews()

    /**
     * Deprecated alias for getNewArticles()
     *
     * @deprecated
     */
    function getNewNews($time, $groups = '*', $distribution = null)
    {
    	return $this->getNewArticles($time, $groups, $distribution);
    }

    // }}}
    // {{{ getDate()

    /**
     * Get the server's internal date
     *
     * Experimental: This method uses non-standard commands, which is not part
     *               of the original RFC977, but has been formalized in RFC2890.
     *
     * @param int	$format	(optional) Determines the format of returned date:
     *                           - 0: return a integer
     *                           - 1: return an array('y'=>year, 'm'=>month,'d'=>day)
     *
     * @return mixed (mixed)	
     *               (object)	Pear_Error on failure
     * @access public
     * @since 0.3
     */
    function getDate($format = 1)
    {
        $date = $this->cmdDate();
        if (PEAR::isError($date)) {
    	    return $date;
    	}

    	switch ($format) {
    	    case 1:
    	        return array('y' => substr($date, 0, 4),
    	                     'm' => substr($date, 4, 2),
    	                     'd' => substr($date, 6, 2));
    	        break;

    	    case 0:
    	    default:
    	        return $date;
    	        break;
    	}
    }

    // }}}
    // {{{ count()

    /**
     * Number of articles in currently selected group
     *
     * @return integer number of article in group
     * @access public
     * @since 0.3
     * @see Net_NNTP_Client::group()
     * @see Net_NNTP_Client::first()
     * @see Net_NNTP_Client::last()
     * @see Net_NNTP_Client::selectGroup()
     */
    function count()
    {
        return $this->_currentGroupSummary['count'];
    }

    // }}}
    // {{{ last()

    /**
     * Maximum article number in currently selected group
     *
     * @return integer number of last article
     * @access public
     * @since 0.3
     * @see Net_NNTP_Client::first()
     * @see Net_NNTP_Client::group()
     * @see Net_NNTP_Client::count()
     * @see Net_NNTP_Client::selectGroup()
     */
    function last()
    {
    	return $this->_currentGroupSummary['last'];
    }

    // }}}
    // {{{ first()

    /**
     * Minimum article number in currently selected group
     *
     * @return integer number of first article
     * @access public
     * @since 0.3
     * @see Net_NNTP_Client::last()
     * @see Net_NNTP_Client::group()
     * @see Net_NNTP_Client::count()
     * @see Net_NNTP_Client::selectGroup()
     */
    function first()
    {
    	return $this->_currentGroupSummary['first'];
    }

    // }}}
    // {{{ group()

    /**
      * Currently selected group
     *
     * @return string group name
     * @access public
     * @since 0.3
     * @see Net_NNTP_Client::first()
     * @see Net_NNTP_Client::last()
     * @see Net_NNTP_Client::count()
     * @see Net_NNTP_Client::selectGroup()
     */
    function group()
    {
    	return $this->_currentGroupSummary['group'];
    }

    // }}}
}

// }}}

?>
