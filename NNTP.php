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
// | Authors: Martin Kaltoft   <martin@nitro.dk>                          |
// |          Tomas V.V.Cox    <cox@idecnet.com>                          |
// |          Heino H. Gehlsen <heino@gehlsen.dk>                         |
// +----------------------------------------------------------------------+
//
// $Id$

require_once 'Net/NNTP/Protocol.php';
require_once 'Net/NNTP/Header.php';
require_once 'Net/NNTP/Message.php';


/* NNTP Authentication modes */
define('NET_NNTP_AUTHORIGINAL', 'original');
define('NET_NNTP_AUTHSIMPLE',   'simple');
define('NET_NNTP_AUTHGENERIC',  'generic');

/**
 * The NNTP:: class fetches UseNet news articles acording to the standard
 * based on RFC 977, RFC 1036 and RFC 1980.
 *
 * @version $Revision$
 * @author Martin Kaltoft   <martin@nitro.dk>
 * @author Tomas V.V.Cox    <cox@idecnet.com>
 * @author Heino H. Gehlsen <heino@gehlsen.dk>
 */
class Net_NNTP extends Net_NNTP_Protocol
{
    // {{{ properties

    /**
     * Used for storing information about the currently selected group
     *
     * @var array
     * @access private
     * @since 0.3
     */
    var $_currentGroup = null;

    // }}}
    // {{{ constructor

    /**
     * 
     */
    function Net_NNTP()
    {
	parent::Net_NNTP_Protocol();
    }

    // }}}
    // {{{ connect()

    /**
     * Connect to the newsserver.
     *
     * @param optional string $host The adress of the NNTP-server to connect to.
     * @param optional int $port The port to connect to, defaults to 119.
     *
     * @return mixed (bool) true on success or (object) pear_error on failure
     * @access public
     * @see Net_NNTP::quit()
     * @see Net_NNTP::connectAuthenticated()
     * @see Net_NNTP::authenticate()
     */
    function connect($host = NET_NNTP_PROTOCOL_DEFAULT_HOST,
                     $port = NET_NNTP_PROTOCOL_DEFAULT_PORT)
    {
	return parent::connect($host, $port);
    }

    // }}}
    // {{{ connectAuthenticated()

    /**
     * Connect to the newsserver, and authenticate. If no user/pass is specified, just connect.
     *
     * @param optional string $user The user name to authenticate with
     * @param optional string $pass The password
     * @param optional string $host The adress of the NNTP-server to connect to.
     * @param optional int $port The port to connect to, defaults to 119.
     * @param optional string $authmode The authentication mode
     *
     * @return mixed (bool) true on success or (object) pear_error on failure
     * @access public
     * @since 0.3
     * @see Net_NNTP::connect()
     * @see Net_NNTP::authenticate()
     * @see Net_NNTP::quit()
     */
    function connectAuthenticated($user = null,
            			  $pass = null,
				  $host = NET_NNTP_PROTOCOL_DEFAULT_HOST,
                		  $port = NET_NNTP_PROTOCOL_DEFAULT_PORT,
                		  $authmode = NET_NNTP_AUTHORIGINAL)
    {
	$R = $this->connect($host, $port);
	if (PEAR::isError($R)) {
	    return $R;
	}

	// Authenticate if username is given
	if ($user != null) {
    	    $R = $this->authenticate($user, $pass, $authmode);
    	    if (PEAR::isError($R)) {
    		return $R;
    	    }
	}

        return true;
    }

    // }}}
    // {{{ quit()

    /**
     * Close connection to the newsserver
     *
     * @access public
     * @see Net_NNTP::connect()
     */
    function quit()
    {
        return $this->cmdQuit();
    }

    // }}}
    // {{{ authenticate()

    /**
     * Authenticate
     * 
     * Auth process (not yet standarized but used any way)
     * http://www.mibsoftware.com/userkt/nntpext/index.html
     *
     * @param string $user The user name
     * @param optional string $pass The password if needed
     * @param optional string $mode Authinfo type: original, simple, generic
     *
     * @return mixed (bool) true on success or (object) pear_error on failure
     * @access public
     * @see Net_NNTP::connect()
     */
    function authenticate($user, $pass, $mode = NET_NNTP_AUTHORIGINAL)
    {
        // Username is a must...
        if ($user == null) {
            return $this->throwError('No username supplied', null);
        }

        // Use selected authentication method
        switch ($mode) {
            case NET_NNTP_AUTHORIGINAL:
                return $this->cmdAuthinfo($user, $pass);
                break;
            case NET_NNTP_AUTHSIMPLE:
                return $this->cmdAuthinfoSimple($user, $pass);
                break;
            case NET_NNTP_AUTHGENERIC:
                return $this->cmdAuthinfoGeneric($user, $pass);
                break;
            default:
                return $this->throwError("The auth mode: '$mode' is unknown", null);
        }
    }

    // }}}
    // {{{ isConnected()

    /**
     * Test whether we are connected or not.
     *
     * @return bool true or false
     * @access public
     * @see Net_NNTP::connect()
     * @see Net_NNTP::quit()
     */
    function isConnected()
    {
        return parent::isConnected();
    }

    // }}}
    // {{{ selectGroup()

    /**
     * Selects a news group (issue a GROUP command to the server)
     *
     * @param string $newsgroup The newsgroup name
     *
     * @return mixed (array) Groups info on success or (object) pear_error on failure
     * @access public
     */
    function selectGroup($newsgroup)
    {
        $response_arr = $this->cmdGroup($newsgroup);
    	if (PEAR::isError($response_arr)) {
	    return $response_arr;
	}

	// Store group info in the object
	$this->_currentGroup = $response_arr;

	return $response_arr;
    }

    // }}}
    // {{{ getGroups()

    /**
     * Fetches a list of all avaible newsgroups
     *
     * @return mixed (array) nested array with informations about existing newsgroups on success or (object) pear_error on failure
     * @access public
     */
    function getGroups()
    {
	// Get groups
	$groups = $this->cmdList();
	if (PEAR::isError($groups)) {
	    return $groups;
	}

	// Deprecated / historical
	foreach (array_keys($groups) as $k) {
    	    $groups[$k]['posting_allowed'] =& $groups[$k][3];
	}

	// Get group descriptions
	$descriptions = $this->cmdListNewsgroups();
	if (PEAR::isError($descriptions)) {
	    return $descriptions;
	}
	
	// Set known descriptions for groups
	if (count($descriptions) > 0) {
    	    foreach ($descriptions as $k=>$v) {
		$groups[$k]['desc'] = $v;
	    }
	}

	return $groups;
    }

    // }}}
    // {{{ getOverview()

    /**
     * Fetch message header fields from message number $first to $last
     *
     * The format of the returned array is:
     * $messages[message_id][header_name]
     *
     * @param integer $first first article to fetch
     * @param integer $last  last article to fetch
     *
     * @return mixed (array) nested array of message and there headers on success or (object) pear_error on failure
     * @access public
     */
    function getOverview($first, $last)
    {
	$overview = $this->cmdXOver($first, $last);
	if (PEAR::isError($overview)) {
	    return $overview;
	}
	
	return $overview;
    }

    // }}}
    // {{{ getOverviewFmt()

    /**
     * Returns a list of avaible headers which are send from newsserver to client for every news message
     *
     * @return mixed (array) header names on success or (object) pear_error on failure
     * @access public
     */
    function getOverviewFmt()
    {
	return $this->cmdListOverviewFMT();
    }

    // }}}
    // {{{ getReferencesOverview()

    /**
     * Fetch message header from message number $first to $last
     *
     * The format of the returned array is:
     * $messages[message_id][header_name]
     *
     * @param integer $first first article to fetch
     * @param integer $last  last article to fetch
     *
     * @return mixed (array) nested array of message and there headers on success or (object) pear_error on failure
     * @access public
     */
    function getReferencesOverview($first, $last)
    {
	$overview = $this->cmdXROver($first, $last);
	if (PEAR::isError($overview)) {
	    return $overview;
	}
	
	return $overview;
    }

    // }}}
    // {{{ post()

    /**
     * Post an article to a newsgroup.
     * Among the aditional headers you might think of adding could be:
     * "NNTP-Posting-Host: <ip-of-author>", which should contain the IP-adress
     * of the author of the post, so the message can be traced back to him.
     * Or "Organization: <org>" which contain the name of the organization
     * the post originates from.
     *
     * @param string $subject The subject of the post.
     * @param string $newsgroup The newsgroup to post to.
     * @param string $from Name + email-adress of sender.
     * @param string $body The body of the post itself.
     * @param optional string $aditional Aditional headers to send.
     *
     * @return mixed (string) server response on success or (object) pear_error on failure
     * @access public
     */
    function post($newsgroup, $subject, $body, $from, $aditional = '')
    {
	return $this->cmdPost($newsgroup, $subject, $body, $from, $aditional);
    }

    // }}}
    // {{{ getArticle()

    /**
     * Get an article from the currently open connection.
     *
     * The v0.2 version of the this function (which returned the article as a string) has been renamed to getArticleRaw().
     *
     * @param mixed $article Either the message-id or the message-number on the server of the article to fetch.
     *
     * @return mixed (object) message object on success or (object) pear_error on failure
     * @access public
     */
    function getArticle($article)
    {
        $message = $this->getArticleRaw($article, false);
        if (PEAR::isError($message)) {
	    return $data;
	}
	
	$M = Net_NNTP_Message::create($message);
	
	return $M;
    }

    // }}}
    // {{{ getArticleRaw()

    /**
     * Get an article from the currently open connection.
     *
     * @param mixed $article Either the message-id or the message-number on the server of the article to fetch.
     * @param optional bool  $implode When true the result array is imploded to a string, defaults to true.
     *
     * @return mixed (array/string) The article on success or (object) pear_error on failure
     * @access public
     */
    function getArticleRaw($article, $implode = true)
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
     * Get the header of an article from the currently open connection
     *
     * @param mixed $article Either the (string) message-id or the (int) message-number on the server of the article to fetch.
     *
     * @return mixed (object) header object on success or (object) pear_error on failure
     * @access public
     */
    function getHeader($article)
    {
        $header = $this->getHeaderRaw($article, false);
        if (PEAR::isError($header)) {
	    return $header
;
	}

	$H = Net_NNTP_Header::create($header);

	return $H;
    }

    // }}}
    // {{{ getHeaderRaw()

    /**
     * Get the header of an article from the currently open connection
     *
     * @param mixed $article Either the (string) message-id or the (int) message-number on the server of the article to fetch.
     * @param optional bool $implode When true the result array is imploded to a string, defaults to true.
     *
     * @return mixed (array/string) header fields on success or (object) pear_error on failure
     * @access public
     */
    function getHeaderRaw($article, $implode = true)
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
    // {{{ getBodyRaw()

    /**
     * Get the body of an article from the currently open connection.
     *
     * @param mixed $article Either the message-id or the message-number on the server of the article to fetch.
     * @param optional bool  $implode When true the result array is imploded to a string, defaults to true.
     *
     * @return mixed (array/string) body on success or (object) pear_error on failure
     * @access public
     */
    function getBodyRaw($article, $implode = true)
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
     *
     * @access public
     * @since 0.3
     */
    function getGroupArticles($newsgroup)
    {
        return $this->cmdListgroup($newsgroup);
    }

    // }}}
    // {{{ getNewGroups()

    /**
     *
     * @access public
     * @since 0.3
     */
    function getNewGroups($time)
    {
	switch (gettype($time)) {
	    case 'integer':
		break;
	    case 'string':
		$time = (int) strtotime($time);
		break;
	    default:
	        return $this->throwError('');
	}

	return $this->cmdNewgroups($time);
    }

    // }}}
    // {{{ getNewNews()

    /**
     *
     * @access public
     * @since 0.3
     */
    function getNewNews($time, $newsgroups = '*')
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

	return $this->cmdNewnews($time, $newsgroups);
    }

    // }}}
    // {{{ getDate()

    /**
     * Get the date from the newsserver format of returned date:
     * $date['']  - timestamp
     * $date['y'] - year
     * $date['m'] - month
     * $date['d'] - day
     *
     * @return mixed (array) date on success or (object) pear_error on failure
     * @access public
     * @since 0.3
     */
    function getDate()
    {
        $date = $this->cmdDate();
        if (PEAR::isError($date)) {
	    return $date;
	}
	return array ('y' => substr($date, 0, 4), 'm' => substr($date, 4, 2), 'd' => substr($date, 6, 2));
    }

    // }}}
    // {{{ count()

    /**
     * Number of articles in currently selectd group
     *
     * @return integer count
     * @access public
     * @since 0.3
     * @see Net_NNTP::first()
     * @see Net_NNTP::last()
     */
    function count()
    {
        return $this->_currentGroup['count'];

    }

    // }}}
    // {{{ last()

    /**
     * Maximum article number in current group
     *
     * @return integer maximum
     * @access public
     * @since 0.3
     * @see Net_NNTP::first()
     * @see Net_NNTP::count()
     */
    function last()
    {
	return $this->_currentGroup['last'];
    }

    // }}}
    // {{{ first()

    /**
     * Minimum article number in current group
     *
     * @return integer minimum
     * @access public
     * @since 0.3
     * @see Net_NNTP::last()
     * @see Net_NNTP::count()
     */
    function first()
    {
	return $this->_currentGroup['first'];
    }

    // }}}
    // {{{ group()

    /**
     * Currently selected group
     *
     * @return string group
     * @access public
     * @since 0.3
     */
    function group()
    {
	return $this->_currentGroup['group'];
    }

    // }}}
    // {{{ splitHeaders()

    /**
     * Get the headers of an article from the currently open connection, and parse them into a keyed array.
     *
     * @param mixed $article Either the (string) message-id or the (int) message-number on the server of the article to fetch.
     *
     * @return mixed (array) Assoc array with headers names as key on success or (object) pear_error on failure
     * @access public
     *
     * @deprecated
     */
    function splitHeaders($article)
    {
	// Retrieve header and create header object
	$H = $this->getHeader($article);

	// Return keyed array
	return $H->getFieldsArray();
    }

    // }}}
    // {{{ command()

    /**
     * Issue a command to the NNTP server
     *
     * @param string $cmd The command to launch, ie: "ARTICLE 1004853"
     *
     * @return mixed (int) response code on success or (object) pear_error on failure
     * @access public
     */
    function command($cmd)
    {
        return $this->_sendCommand($cmd);
    }

    // }}}

}
?>
