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


/* NNTP Authentication modes */
define('PEAR_NNTP_AUTHORIGINAL', 'original');
define('PEAR_NNTP_AUTHSIMPLE',   'simple');
define('PEAR_NNTP_AUTHGENERIC',  'generic');


/**
 * The NNTP:: class fetches UseNet news articles acording to the standard
 * based on RFC 977, RFC 1036 and RFC 1980.
 *
 * @version 0.3
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
     * The function currently allows automatic authentication via the three last parameters, 
     * but this feature is to be considered depresated (use connectAuthenticated instead)
     *
     * In the future, this function will just be inherrited from the parent,
     * and thus the last three parameters will no longer be used to authenticate.
     *
     * @param optional string $host The adress of the NNTP-server to connect to.
     * @param optional int $port The port to connect to, defaults to 119.
     * @param optional string $user Depresated!
     * @param optional string $pass Depresated!
     * @param optional string $authmode Depresated!
     *
     * @return mixed (bool) true on success or (object) pear_error on failure
     * @access public
     * @see Net_Nntp::quit()
     * @see Net_Nntp::connectAuthenticated()
     * @see Net_Nntp::authenticate()
     */
    function connect($host = 'localhost',
                     $port = 119,
                     $user = null,
                     $pass = null,
                     $authmode = PEAR_NNTP_AUTHORIGINAL)
    {
	// Currently this function just 'forwards' to connectAuthenticated().
	return $this->connectAuthenticated($host, $port, $user, $pass, $authmode);
    }

    // }}}
    /**
     * Connect to the newsserver, and authenticate. If no user/pass is specified, just connect.
     *
     * @param optional string $host The adress of the NNTP-server to connect to.
     * @param optional int $port The port to connect to, defaults to 119.
     * @param optional string $user The user name to authenticate with
     * @param optional string $pass The password
     * @param optional string $authmode The authentication mode
     *
     * @return mixed (bool) true on success or (object) pear_error on failure
     * @access public
     * @since 0.3
     * @see Net_Nntp::connect()
     * @see Net_Nntp::authenticate()
     * @see Net_Nntp::quit()
     */
    function connectAuthenticated($host = 'localhost',
                     $port = 119,
                     $user = null,
                     $pass = null,
                     $authmode = PEAR_NNTP_AUTHORIGINAL)
    {
	// Until connect() is changed, connect() is called directly from the parent...
	$R = parent::connect($host, $port);
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
     * @see Net_Nntp::connect()
     */
    function quit()
    {
        return $this->cmdQuit();
    }

    // }}}
    // {{{ prepareConnection()

    /**
     * Connect to the newsserver, and issue a GROUP command
     * Once connection is prepared, we can only fetch articles from one group
     * at a time, to fetch from another group, a new connection has to be made.
     *
     * This is to avoid the GROUP command for every article, as it is very
     * ressource intensive on the newsserver especially when used for
     * groups with many articles.
     *
     * @param string $host The adress of the NNTP-server to connect to.
     * @param optional int $port the port-number to connect to, defaults to 119.
     * @param string $newsgroup The name of the newsgroup to use.
     * @param optional string $user The user name to authenticate with
     * @param optional string $pass The password
     * @param optional string $authmode The authentication mode
     *
     * @return mixed (bool) true on success or (object) pear_error on failure
     * @access public
     *
     * @deprecated Use Connect() instead
     */
    function prepareConnection($host,
                                $port = 119,
                                $newsgroup,
                                $user = null,
                                $pass = null,
                                $authmode = PEAR_NNTP_AUTHORIGINAL)
    {
        /* connect to the server */
        $R = $this->connect($host, $port, $user, $pass, $authmode);
        if ($this->isError($R)) {
            return $R;
        }

        /* issue a GROUP command */
        $R = $this->selectGroup($newsgroup);
        if ($this->isError($R)) {
            return $R;
        }

        return true;
    }

    // }}}
    // {{{ prepare_connection()

    /**
     * @param string $host The adress of the NNTP-server to connect to.
     * @param optional int $port the port-number to connect to, defaults to 119.
     * @param string $newsgroup The name of the newsgroup to use.
     * @param optional string $user The user name to authenticate with
     * @param optional string $pass The password
     * @param optional string $authmode The authentication mode
     *
     * @return mixed (bool) true on success or (object) pear_error on failure
     * @access public
     *
     * @deprecated Use prepareConnection() instead
     */
    function prepare_connection($host,
                                $port = 119,
                                $newsgroup,
                                $user = null,
                                $pass = null,
                                $authmode = PEAR_NNTP_AUTHORIGINAL)
    {
        return $this->prepareConnection($host, $port, $newsgroup, $user, $pass, $authmode);
    }

    // }}}
    // {{{ authenticate()

    /**
     * Auth process (not yet standarized but used any way)
     * http://www.mibsoftware.com/userkt/nntpext/index.html
     *
     * @param string $user The user name
     * @param optional string $pass The password if needed
     * @param optional string $mode Authinfo type: original, simple, generic
     *
     * @return mixed (bool) true on success or (object) pear_error on failure
     * @access public
     * @see Net_Nntp::connect()
     */
    function authenticate($user, $pass, $mode = PEAR_NNTP_AUTHORIGINAL)
    {
	return $this->cmdAuthinfo($user, $pass, $mode);
    }

    // }}}
    // {{{ isConnected()

    /**
     * Test whether we are connected or not.
     *
     * @return bool true or false
     * @access public
     * @see Net_Nntp::connect()
     * @see Net_Nntp::quit()
     */
    function isConnected()
    {
        return parent::isConnected();
    }

    // }}}
    // {{{ is_connected()

    /**
     * @return bool true or false
     * @access public
     *
     * @deprecated Use isConnected() instead
     */
    function is_connected()
    {
        return $this->isConnected();
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

	$this->_currentGroup = $response_arr;

	// Deprisated / historical				  	
	$response_arr['min'] =& $response_arr['first'];
	$response_arr['max'] =& $response_arr['last'];

	return $response_arr;
    }

    // }}}
    // {{{ select_group()

    /**
     * @param string $newsgroup The newsgroup name
     *
     * @return mixed (array) Groups info on success or (object) pear_error on failure
     * @access public
     *
     * @deprecated Use selectGroup() instead
     */
    function select_group($newsgroup)
    {
        return $this->selectGroup($newsgroup);
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

	// Deprisated / historical
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
    // {{{ get_groups()

    /**
     * @return mixed (array) nested array with informations about existing newsgroups on success or (object) pear_error on failure
     * @access public
     *
     * @deprecated Use getGroups() instead
     */
    function get_groups()
    {
        return $this->getGroups();
    }

    // }}}
    // {{{ getOverview()

    /**
     * Fetch message header from message number $first until $last
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
	$XOver = $this->cmdXOver($first, $last);
	if (PEAR::isError($XOver)) {
	    return $XOver;
	}

	$result = $XOver;
	
	$XROver = $this->cmdXROver($first, $last);
	if (!PEAR::isError($XROver)) {
	    $result = array_merge($result, $XROver);
	}
	
	return $result;
    }

    // }}}
    // {{{ get_overview()

    /**
     * @param integer $first first article to fetch
     * @param integer $last  last article to fetch
     *
     * @return mixed (array) nested array of message and there headers on success or (object) pear_error on failure
     * @access public
     *
     * @deprecated Use getOverview() instead
     */
    function get_overview($first, $last)
    {
        return $this->getOverview($first, $last);
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
    // {{{ get_overview_fmt()

    /**
     * @return mixed (array) header names on success or (object) pear_error on failure
     * @access public
     *
     * @deprecated Use getOverviewFmt() instead
     */
    function get_overview_fmt()
    {
        return $this->getOverviewFmt();
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
    function post($subject, $newsgroup, $from, $body, $aditional = '')
    {
	return $this->cmdPost($newsgroup, $subject, $body, $from, $aditional);
    }

    // }}}
    // {{{ getArticle()

    /**
     * Get an article from the currently open connection.
     *
     * @param mixed $article Either the message-id or the message-number on the server of the article to fetch.
     * @param bool  $implode When true the result array is imploded to a string, defaults to true.
     *
     * @return mixed (array/string) The headers on success or (object) pear_error on failure
     * @access public
     */
    function getArticle($article, $implode = true)
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
    // {{{ get_article()

    /**
     * @param mixed $article Either the message-id or the message-number on the server of the article to fetch.
     *
     * @return mixed (string) The headers on success or (object) pear_error on failure
     * @access public
     *
     * @deprecated Use getArticle() instead
     */
    function get_article($article)
    {
        return $this->getArticle($article, true);
    }

    // }}}
    // {{{ getHeaders()

    /**
     * Get the headers of an article from the currently open connection
     *
     * @param mixed $article Either the (string) message-id or the (int) message-number on the server of the article to fetch.
     * @param bool  $implode When true the result array is imploded to a string, defaults to true.
     *
     * @return mixed (array/string) headers on success or (object) pear_error on failure
     * @access public
     */
    function getHeaders($article, $implode = true)
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
    // {{{ get_headers()

    /**
     * @param mixed $article Either the (string) message-id or the (int) message-number on the server of the article to fetch.
     *
     * @return mixed (string) headers on success or (object) pear_error on failure
     * @access public
     *
     * @deprecated Use getHeaders() instead
     */
    function get_headers($article)
    {
        return $this->getHeaders($article, true);
    }

    // }}}
    // {{{ getHeadersParsed()

    /**
     * Get the headers of an article from the currently open connection, and parse them into a keyed array.
     *
     * @param mixed $article Either the (string) message-id or the (int) message-number on the server of the article to fetch.
     *
     * @return mixed (array) Assoc array with headers names as key on success or (object) pear_error on failure
     * @access public
     * @since 0.3
     */
    function getHeadersParsed($article)
    {
	// Retrieve headers
        $headers = $this->getHeaders($article, false);
        if (PEAR::isError($headers)) {
            return $this->throwError($headers);
        }
	
	// Parse headers
	$res = $this->parseHeaders($headers);

	return $res;
    }

    // }}}
    // {{{ getBody()

    /**
     * Get the body of an article from the currently open connection.
     *
     * @param mixed $article Either the message-id or the message-number on the server of the article to fetch.
     * @param bool  $implode When true the result array is imploded to a string, defaults to true.
     *
     * @return mixed (array/string) headers on success or (object) pear_error on failure
     * @access public
     */
    function getBody($article, $implode = true)
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
    // {{{ get_body()
    
    /**
     * @param mixed $article Either the message-id or the message-number on the server of the article to fetch.
     *
     * @return mixed (string) headers on success or (object) pear_error on failure
     * @access public
     *
     * @deprecated Use getBody() instead
     */
    function get_body($article)
    {
        return $this->getBody($article, true);
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
	        return $this->throwError('UPS...');
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
    // {{{ date()

    /**
     * @return mixed (array) date on success or (object) pear_error on failure
     * @access public
     *
     * @deprecated Use getDate() instead
     */
    function date()
    {
        return $this->getDate();
    }

    // }}}
    // {{{ count()

    /**
     * Number of articles in currently selectd group
     *
     * @return integer count
     * @access public
     * @since 0.3
     */
    function count()
    {
        if (!$this->isConnected()) {
            return $this->throwError('Not connected');
        }
        return $this->currentGroup['count'];

    }

    // }}}
    // {{{ last()

    /**
     * Maximum article number in current group
     *
     * @return integer maximum
     * @access public
     * @since 0.3
     * @see Net_Nntp::first()
     */
    function last()
    {
        if (!$this->isConnected()) {
            return $this->throwError('Not connected');
        }
	return $this->currentGroup['last'];
    }

    // }}}
    // {{{ max()

    /**
     * @return integer maximum
     * @access public
     *
     * @deprecated Use last() instead
     */
    function max()
    {
        return $this->last();
    }

    // }}}
    // {{{ first()

    /**
     * Minimum article number in current group
     *
     * @return integer minimum
     * @access public
     * @since 0.3
     * @see Net_Nntp::last()
     */
    function first()
    {
        if (!$this->isConnected()) {
            return $this->throwError('Not connected');
        }
	return $this->currentGroup['first'];
    }

    // }}}
    // {{{ min()

    /**
     * @return integer minimum
     * @access public
     *
     * @deprecated Use first() instead
     */
    function min()
    {
        return $this->first();
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
        if (!$this->isConnected()) {
            return $this->throwError('Not connected');
        }
	return $this->currentGroup['group'];
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
     * @deprecated Use getHeadersParsed() instead
     */
    function splitHeaders($article)
    {
        return $this->getHeadersParsed($article);
    }

    // }}}
    // {{{ split_headers()

    /**
     * Get the headers of an article from the currently open connection, and parse them into a keyed array.
     *
     * @param mixed $article Either the (string) message-id or the (int) message-number on the server of the article to fetch.
     *
     * @return mixed (array) Assoc array with headers names as key on success or (object) pear_error on failure
     * @access public
     *
     * @deprecated Use splitHeaders() instead
     */
    function split_headers($article)
    {
        return $this->splitHeaders($article);
    }

    // }}}
    // {{{ parseHeaders()

    /**
     * Returns the headers of a given article in the form of
     * an associative array. Ex:
     * array(
     *   'From'      => 'foo@bar.com (Foo Smith)',
     *   'Subject'   => 'Re: Using NNTP class',
     *   ....
     *   );
     *
     * @param array/string $article Article number or id
     *
     * @return mixed (array) Assoc array with headers names as key on success or (object) pear_error on failure
     * @access public
     * @see Net_Nntp::getHeadersParsed()
     */
    function parseHeaders($headers)
    {
	require_once('Net/NNTP/Header.php');
	$H = new Net_Mime_Header();
	$headers = $H->cleanArray($headers);
	$headers = $H->parseArray($headers);

	return $headers;
    }

    // }}}
    // {{{ responseCode()

    /**
     * returns the response code of a newsserver command
     *
     * @param string $response newsserver answer
     *
     * @return integer response code
     * @access public
     *
     * @deprecated
     */
    function responseCode($response)
    {
        $parts = explode(' ', ltrim($response), 2);
        return (int) $parts[0];
    }

    // }}}
    // {{{ _getData()

    /**
     * Get data until a line with only a '.' in it is read and return data.
     *
     * @return mixed (string) data on success or (object) pear_error on failure
     * @access private
     *
     * @deprecated Use _getTextResponse() instead
     */
    function _getData()
    {
	return $this->_getTextResponse();
    }

    // }}}
    // {{{ get_data()

    /**
     * @return mixed (string) data on success or (object) pear_error on failure
     * @access private
     *
     * @deprecated Use _getData() instead
     */
    function get_data()
    {
        return $this->_getData();
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
