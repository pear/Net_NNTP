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

require_once 'PEAR.php';
require_once 'Net/Socket.php';
require_once 'Net/NNTP/Protocol/Responsecode.php';

// {{{ constants

/**
 * Default host
 *
 * @access     public
 */
define('NET_NNTP_PROTOCOL_CLIENT_DEFAULT_HOST', 'localhost');

/**
 * Default port
 *
 * @access     public
 */
define('NET_NNTP_PROTOCOL_CLIENT_DEFAULT_PORT', '119');

// }}}
// {{{ Net_NNTP_Protocol_Client

/**
 * Low level NNTP Client
 *
 * Implements the client part of the NNTP standard acording to:
 *  - RFC 977,
 *  - RFC 2980,
 *  - RFC 850/1036, and
 *  - RFC 822/2822
 *
 * Each NNTP command is represented by a method: cmd*()
 *
 * WARNING: The Net_NNTP_Protocol_Client class is considered an internal class
 *          (and should therefore currently not be extended directly outside of
 *          the Net_NNTP package). Therefore its API is NOT required to be fully
 *          stable, for as long as such changes doesn't affect the public API of
 *          the Net_NNTP_Client class, which is considered stable.
 *
 * TODO:	cmdListActive()
 *      	cmdListActiveTimes()
 *      	cmdDistribPats()
 *      	cmdHeaders()
 *
 * @category   Net
 * @package    Net_NNTP
 * @author     Heino H. Gehlsen <heino@gehlsen.dk>
 * @version    $Id$
 * @access     private
 * @see        Net_NNTP_Client
 * @since      Class available since Release 0.11.0
 */
class Net_NNTP_Protocol_Client
{
    // {{{ properties

    /**
     * The socket resource being used to connect to the NNTP server.
     *
     * @var resource
     * @access private
     */
    var $_socket = null;

    /**
     * Contains the last recieved status response code and text
     *
     * @var array
     * @access private
     */
    var $_currentStatusResponse = null;

    /**
     * Whether to enable internal debug messages.
     *
     * @var     bool
     * @access  private
     */
    var $_debug = false;

    // }}}
    // {{{ constructor
	    
    /**
     * Constructor
     *
     * @access public
     */
    function Net_NNTP_Protocol_Client() {
    	$this->_socket = new Net_Socket();
    }

    // }}}
    // {{{ Connect()

    /**
     * Connect to a NNTP server
     *
     * @param optional string $host The address of the NNTP-server to connect to, defaults to 'localhost'.
     * @param optional int $port The port number to connect to, defaults to 119.
     *
     * @return mixed (bool) on success (true when posting allowed, otherwise false) or (object) pear_error on failure
     * @access protected
     */
    function connect($host = NET_NNTP_PROTOCOL_CLIENT_DEFAULT_HOST, $port = NET_NNTP_PROTOCOL_CLIENT_DEFAULT_PORT)
    {
        if ($this->isConnected() ) {
    	    return PEAR::throwError('Already connected, disconnect first!', null);
    	}

    	// Open Connection
    	$R = @$this->_socket->connect($host, $port, false, 15);
    	if (PEAR::isError($R)) {
    	    return PEAR::throwError('Could not connect to the server', null, $R->getMessage());
    	}

    	// Retrive the server's initial response.
    	$response = $this->_getStatusResponse();
    	if (PEAR::isError($response)) {
    	    return $response;
        }

        switch ($response) {
    	    case NET_NNTP_PROTOCOL_RESPONSECODE_READY_POSTING_ALLOWED: // 200, Posting allowed
    	    	// TODO: Set some variable
    	        return true;
    	        break;
    	    case NET_NNTP_PROTOCOL_RESPONSECODE_READY_POSTING_PROHIBITED: // 201, Posting NOT allowed
    	    	// TODO: Set some variable
    	        return false;
    	        break;
    	    case 400:
    	    	return PEAR::throwError('Server refused connection', $response, $this->_currentStatusResponse());
    	    	break;
    	    case NET_NNTP_PROTOCOL_RESPONSECODE_NOT_PERMITTED: // 502, 'access restriction or permission denied' / service permanently unavailable
    	    	return PEAR::throwError('Server refused connection', $response, $this->_currentStatusResponse());
    	    	break;
    	    default:
    	    	return $this->_handleUnexpectedResponse($response);
    	}
    }

    // }}}
    // {{{ disconnect()

    /**
     * alias for cmdQuit()
     *
     * @access protected
     */
    function disconnect()
    {
    	return $this->cmdQuit();
    }

    // }}}
    // {{{ cmdQuit()

    /**
     * Disconnect from the NNTP server
     *
     * @return mixed (bool) true on success or (object) pear_error on failure 
     * @access protected
     */
    function cmdQuit()
    {
    	// Tell the server to close the connection
    	$response = $this->_sendCommand('QUIT');
        if (PEAR::isError($response)) {
            return $response;
    	}
	
        switch ($response) {
    	    case 205: // RFC977: 'closing connection - goodbye!'
    	    	// If socket is still open, close it.
    	    	if ($this->isConnected()) {
    	    	    $this->_socket->disconnect();
    	    	}
    	    	return true;
    	    	break;
    	    default:
    	    	return $this->_handleUnexpectedResponse($response);
    	}
    }

    // }}}

    /**
     * The authentication process i not yet standarized but used any way
     * (http://www.mibsoftware.com/userkt/nntpext/index.html).
     */
     
    // {{{ cmdAuthinfo()

    /**
     * Authenticate using 'original' method
     *
     * @param string $user The username to authenticate as.
     * @param string $pass The password to authenticate with.
     *
     * @return mixed (bool) true on success or (object) pear_error on failure 
     * @access protected
     */
    function cmdAuthinfo($user, $pass)
    {
    	// Send the username
        $response = $this->_sendCommand('AUTHINFO user '.$user);
        if (PEAR::isError($response)) {
            return $response;
    	}

    	// Send the password, if the server asks
    	if (($response == 381) && ($pass !== null)) {
    	    // Send the password
            $response = $this->_sendCommand('AUTHINFO pass '.$pass);
    	    if (PEAR::isError($response)) {
    	    	return $response;
    	    }
    	}

        switch ($response) {
    	    case 281: // RFC2980: 'Authentication accepted'
    	        return true;
    	        break;
    	    case 381: // RFC2980: 'More authentication information required'
    	        return PEAR::throwError('Authentication uncompleted', $response, $this->_currentStatusResponse());
    	        break;
    	    case 482: // RFC2980: 'Authentication rejected'
    	    	return PEAR::throwError('Authentication rejected', $response, $this->_currentStatusResponse());
    	    	break;
    	    case 502: // RFC2980: 'No permission'
    	    	return PEAR::throwError('Authentication rejected', $response, $this->_currentStatusResponse());
    	    	break;
//    	    case 500:
//    	    case 501:
//    	    	return PEAR::throwError('Authentication failed', $response, $this->_currentStatusResponse());
//    	    	break;
    	    default:
    	    	return $this->_handleUnexpectedResponse($response);
    	}
    }
	
    // }}}
    // {{{ cmdAuthinfoSimple()

    /**
     * Authenticate using 'simple' method
     *
     * @param string $user The username to authenticate as.
     * @param string $pass The password to authenticate with.
     *
     * @return mixed (bool) true on success or (object) pear_error on failure 
     * @access protected
     */
    function cmdAuthinfoSimple($user, $pass)
    {
        return PEAR::throwError("The auth mode: 'simple' is has not been implemented yet", null);
    }
	
    // }}}
    // {{{ cmdAuthinfoGeneric()

    /**
     * Authenticate using 'generic' method
     *
     * @param string $user The username to authenticate as.
     * @param string $pass The password to authenticate with.
     *
     * @return mixed (bool) true on success or (object) pear_error on failure 
     * @access protected
     */
    function cmdAuthinfoGeneric($user, $pass)
    {
        return PEAR::throwError("The auth mode: 'generic' is has not been implemented yet", null);
    }
	
    // }}}
    // {{{ cmdHelp()

    /**
     *
     *
     * @return mixed (array) help text on success or (object) pear_error on failure 
     * @access protected
     */
    function cmdHelp()
    {
        // tell the newsserver we want an article
        $response = $this->_sendCommand('HELP');
        if (PEAR::isError($response)) {
            return $response;
        }
	
    	switch ($response) {
            case NET_NNTP_PROTOCOL_RESPONSECODE_HELP_FOLLOWS: // 100
    	    	$data = $this->_getTextResponse();
    	    	if (PEAR::isError($data)) {
    	    	    return $data;
    	    	}
    	    	return $data;
    	        break;
    	    default:
    	    	return $this->_handleUnexpectedResponse($response);
    	}
    }

    // }}}
    // {{{ cmdCapabilities()

    /**
     *
     *
     * @return mixed (array) list of capabilities on success or (object) pear_error on failure 
     * @access protected
     */
    function cmdCapabilities()
    {
        // tell the newsserver we want an article
        $response = $this->_sendCommand('CAPABILITIES');
        if (PEAR::isError($response)) {
            return $response;
        }
	
    	switch ($response) {
            case NET_NNTP_PROTOCOL_RESPONSECODE_CAPABILITIES_FOLLOW: // 101, Draft: 'Capability list follows'
    	    	$data = $this->_getTextResponse();
    	    	if (PEAR::isError($data)) {
    	    	    return $data;
    	    	}
    	    	return $data;
    	        break;
    	    default:
    	    	return $this->_handleUnexpectedResponse($response);
    	}
    }

    // }}}
    // {{{ cmdModeReader()

    /**
     *
     *
     * @return mixed (bool) true when posting allowed, false when postind disallowed or (object) pear_error on failure 
     * @access protected
     */
    function cmdModeReader()
    {
        // tell the newsserver we want an article
        $response = $this->_sendCommand('MODE READER');
        if (PEAR::isError($response)) {
            return $response;
        }
	
    	switch ($response) {
            case NET_NNTP_PROTOCOL_RESPONSECODE_READY_POSTING_ALLOWED: // 200, RFC2980: 'Hello, you can post'
    	    	return true;
    	        break;
    	    case NET_NNTP_PROTOCOL_RESPONSECODE_READY_POSTING_PROHIBITED: // 201, RFC2980: 'Hello, you can't post'
    	    	return false;
    	    	break;
    	    case NET_NNTP_PROTOCOL_RESPONSECODE_NOT_PERMITTED: // 502, 'access restriction or permission denied' / service permanently unavailable
    	    	return PEAR::throwError('Connection being closed, since service so permanently unavailable', $response, $this->_currentStatusResponse());
    	    	break;
    	    default:
    	    	return $this->_handleUnexpectedResponse($response);
    	}
    }

    // }}}
    // {{{ cmdNext()

    /**
     * 
     *
     * @return
     * @access protected
     */
    function cmdNext($ret = -1)
    {
        // 
        $response = $this->_sendCommand('NEXT');
        if (PEAR::isError($response)) {
            return $response;
        }

    	switch ($response) {
    	    case NET_NNTP_PROTOCOL_RESPONSECODE_ARTICLE_SELECTED: // 223, RFC977: 'n a article retrieved - request text separately (n = article number, a = unique article id)'
    	    	$response_arr = split(' ', trim($this->_currentStatusResponse()));

    	    	switch ($ret) {
    	    	    case -1:
    	    	    	return array('number' => (int) $response_arr[0], 'id' =>  (string) $response_arr[1]);
    	    	    	break;
    	    	    case 0:
    	    	        return (int) $response_arr[0];
    	    	    	break;
    	    	    case 1:
    	    	        return (string) $response_arr[1];
    	    	    	break;
    	    	}
    	    	break;
    	    case NET_NNTP_PROTOCOL_RESPONSECODE_NO_GROUP_SELECTED: // 412, RFC977: 'no newsgroup selected'
    	    	return PEAR::throwError('No newsgroup has been selected', $response, $this->_currentStatusResponse());
    	    	break;
    	    case NET_NNTP_PROTOCOL_RESPONSECODE_NO_ARTICLE_SELECTED: // 420, RFC977: 'no current article has been selected'
    	    	return PEAR::throwError('No current article has been selected', $response, $this->_currentStatusResponse());
    	    	break;
    	    case NET_NNTP_PROTOCOL_RESPONSECODE_NO_NEXT_ARTICLE: // 421, RFC977: 'no next article in this group'
    	    	return PEAR::throwError('No next article in this group', $response, $this->_currentStatusResponse());
    	    	break;
    	    default:
    	    	return $this->_handleUnexpectedResponse($response);
    	}
    }

    // }}}
    // {{{ cmdLast()

    /**
     * 
     *
     * @return
     * @access protected
     */
    function cmdLast($ret = -1)
    {
        // 
        $response = $this->_sendCommand('LAST');
        if (PEAR::isError($response)) {
            return $response;
        }

    	switch ($response) {
    	    case NET_NNTP_PROTOCOL_RESPONSECODE_ARTICLE_SELECTED: // 223, RFC977: 'n a article retrieved - request text separately (n = article number, a = unique article id)'
    	    	$response_arr = split(' ', trim($this->_currentStatusResponse()));

    	    	switch ($ret) {
    	    	    case -1:
    	    	    	return array('number' => (int) $response_arr[0], 'id' =>  (string) $response_arr[1]);
    	    	    	break;
    	    	    case 0:
    	    	        return (int) $response_arr[0];
    	    	    	break;
    	    	    case 1:
    	    	        return (string) $response_arr[1];
    	    	    	break;
    	    	}
    	    	break;
    	    case NET_NNTP_PROTOCOL_RESPONSECODE_NO_GROUP_SELECTED: // 412, RFC977: 'no newsgroup selected'
    	    	return PEAR::throwError('No newsgroup has been selected', $response, $this->_currentStatusResponse());
    	    	break;
    	    case NET_NNTP_PROTOCOL_RESPONSECODE_NO_ARTICLE_SELECTED: // 420, RFC977: 'no current article has been selected'
    	    	return PEAR::throwError('No current article has been selected', $response, $this->_currentStatusResponse());
    	    	break;
    	    case NET_NNTP_PROTOCOL_RESPONSECODE_NO_PREVIOUS_ARTICLE: // 422, RFC977: 'no previous article in this group'
    	    	return PEAR::throwError('No previous article in this group', $response, $this->_currentStatusResponse());
    	    	break;
    	    default:
    	    	return $this->_handleUnexpectedResponse($response);
    	}
    }

    // }}}
    // {{{ cmdStat

    /**
     * 
     *
     * @param mixed $article 
     *
     * @return mixed (???) ??? on success or (object) pear_error on failure 
     * @access protected
     */
    function cmdStat($article, $ret = -1)
    {
        // tell the newsserver we want an article
        $response = $this->_sendCommand('STAT '.$article);
        if (PEAR::isError($response)) {
            return $response;
        }

    	switch ($response) {
    	    case NET_NNTP_PROTOCOL_RESPONSECODE_ARTICLE_SELECTED: // 223, RFC977: 'n <a> article retrieved - request text separately' (actually not documented, but copied from the ARTICLE command)
    	    	$response_arr = split(' ', trim($this->_currentStatusResponse()));

    	    	switch ($ret) {
    	    	    case -1:
    	    	    	return array('number' => (int) $response_arr[0], 'id' =>  (string) $response_arr[1]);
    	    	    	break;
    	    	    case 0:
    	    	        return (int) $response_arr[0];
    	    	    	break;
    	    	    case 1:
    	    	        return (string) $response_arr[1];
    	    	    	break;
    	    	}
		
    	    	break;
    	    case NET_NNTP_PROTOCOL_RESPONSECODE_NO_GROUP_SELECTED: // 412, RFC977: 'no newsgroup has been selected' (actually not documented, but copied from the ARTICLE command)
    	    	return PEAR::throwError('No newsgroup has been selected', $response, $this->_currentStatusResponse());
    	    	break;
    	    case NET_NNTP_PROTOCOL_RESPONSECODE_NO_SUCH_ARTICLE_NUMBER: // 423, RFC977: 'no such article number in this group' (actually not documented, but copied from the ARTICLE command)
    	    	return PEAR::throwError('No such article number in this group', $response, $this->_currentStatusResponse());
    	    	break;
    	    case NET_NNTP_PROTOCOL_RESPONSECODE_NO_SUCH_ARTICLE_ID: // 430, RFC977: 'no such article found' (actually not documented, but copied from the ARTICLE command)
    	    	return PEAR::throwError('No such article found', $response, $this->_currentStatusResponse());
    	    	break;
    	    default:
    	    	return $this->_handleUnexpectedResponse($response);
    	}
    }

    // }}}
    // {{{ cmdArticle()

    /**
     * Get an article from the currently open connection.
     *
     * @param mixed $article Either a message-id or a message-number of the article to fetch. If null or '', then use current article.
     *
     * @return mixed (array) article on success or (object) pear_error on failure 
     * @access protected
     */
    function cmdArticle($article)
    {
        // tell the newsserver we want an article
        $response = $this->_sendCommand('ARTICLE '.$article);
        if (PEAR::isError($response)) {
            return $response;
        }
	
    	switch ($response) {
    	    case NET_NNTP_PROTOCOL_RESPONSECODE_ARTICLE_FOLLOWS:  // 220, RFC977: 'n <a> article retrieved - head and body follow (n = article number, <a> = message-id)'
    	    	$data = $this->_getTextResponse();
    	    	if (PEAR::isError($data)) {
    	    	    return $data;
    	    	}
    	    	return $data;
    	    	break;
    	    case NET_NNTP_PROTOCOL_RESPONSECODE_NO_GROUP_SELECTED: // 412, RFC977: 'no newsgroup has been selected'
    	    	return PEAR::throwError('No newsgroup has been selected', $response, $this->_currentStatusResponse());
    	    	break;
    	    case NET_NNTP_PROTOCOL_RESPONSECODE_NO_ARTICLE_SELECTED: // 420, RFC977: 'no current article has been selected'
    	    	return PEAR::throwError('No current article has been selected', $response, $this->_currentStatusResponse());
    	    	break;
    	    case NET_NNTP_PROTOCOL_RESPONSECODE_NO_SUCH_ARTICLE_NUMBER: // 423, RFC977: 'no such article number in this group'
    	    	return PEAR::throwError('No such article number in this group', $response, $this->_currentStatusResponse());
    	    	break;
    	    case NET_NNTP_PROTOCOL_RESPONSECODE_NO_SUCH_ARTICLE_ID: // 430, RFC977: 'no such article found'
    	    	return PEAR::throwError('No such article found', $response, $this->_currentStatusResponse());
    	    	break;
    	    default:
    	    	return $this->_handleUnexpectedResponse($response);
    	}
    }

    // }}}
    // {{{ cmdHead()

    /**
     * Get the headers of an article from the currently open connection.
     *
     * @param mixed $article Either a message-id or a message-number of the article to fetch the headers from. If null or '', then use current article.
     *
     * @return mixed (array) headers on success or (object) pear_error on failure 
     * @access protected
     */
    function cmdHead($article)
    {
        // tell the newsserver we want the header of an article
        $response = $this->_sendCommand('HEAD '.$article);
        if (PEAR::isError($response)) {
            return $response;
        }

    	switch ($response) {
    	    case NET_NNTP_PROTOCOL_RESPONSECODE_HEAD_FOLLOWS:     // 221, RFC977: 'n <a> article retrieved - head follows'
    	    	$data = $this->_getTextResponse();
    	    	if (PEAR::isError($data)) {
    	    	    return $data;
	    	}
    	        return $data;
    	    	break;
    	    case NET_NNTP_PROTOCOL_RESPONSECODE_NO_GROUP_SELECTED: // 412, RFC977: 'no newsgroup has been selected'
    	    	return PEAR::throwError('No newsgroup has been selected', $response, $this->_currentStatusResponse());
    	    	break;
    	    case NET_NNTP_PROTOCOL_RESPONSECODE_NO_ARTICLE_SELECTED: // 420, RFC977: 'no current article has been selected'
    	    	return PEAR::throwError('No current article has been selected', $response, $this->_currentStatusResponse());
    	    	break;
    	    case NET_NNTP_PROTOCOL_RESPONSECODE_NO_SUCH_ARTICLE_NUMBER: // 423, RFC977: 'no such article number in this group'
    	    	return PEAR::throwError('No such article number in this group', $response, $this->_currentStatusResponse());
    	    	break;
    	    case NET_NNTP_PROTOCOL_RESPONSECODE_NO_SUCH_ARTICLE_ID: // 430, RFC977: 'no such article found'
    	    	return PEAR::throwError('No such article found', $response, $this->_currentStatusResponse());
    	    	break;
    	    default:
    	    	return $this->_handleUnexpectedResponse($response);
    	}
    }

    // }}}
    // {{{ cmdBody()

    /**
     * Get the body of an article from the currently open connection.
     *
     * @param mixed $article Either a message-id or a message-number of the article to fetch the body from. If null or '', then use current article.
     *
     * @return mixed (array) body on success or (object) pear_error on failure 
     * @access protected
     */
    function cmdBody($article)
    {
        // tell the newsserver we want the body of an article
        $response = $this->_sendCommand('BODY '.$article);
        if (PEAR::isError($response)) {
            return $response;
        }

    	switch ($response) {
    	    case NET_NNTP_PROTOCOL_RESPONSECODE_BODY_FOLLOWS:     // 222, RFC977: 'n <a> article retrieved - body follows'
    	    	$data = $this->_getTextResponse();
    	    	if (PEAR::isError($data)) {
    	    	    return $data;
    	    	}
    	        return $data;
    	    	break;
    	    case NET_NNTP_PROTOCOL_RESPONSECODE_NO_GROUP_SELECTED: // 412, RFC977: 'no newsgroup has been selected'
    	    	return PEAR::throwError('No newsgroup has been selected', $response, $this->_currentStatusResponse());
    	    	break;
    	    case NET_NNTP_PROTOCOL_RESPONSECODE_NO_ARTICLE_SELECTED: // 420, RFC977: 'no current article has been selected'
    	    	return PEAR::throwError('No current article has been selected', $response, $this->_currentStatusResponse());
    	    	break;
    	    case NET_NNTP_PROTOCOL_RESPONSECODE_NO_SUCH_ARTICLE_NUMBER: // 423, RFC977: 'no such article number in this group'
    	    	return PEAR::throwError('No such article number in this group', $response, $this->_currentStatusResponse());
    	    	break;
    	    case NET_NNTP_PROTOCOL_RESPONSECODE_NO_SUCH_ARTICLE_ID: // 430, RFC977: 'no such article found'
    	    	return PEAR::throwError('No such article found', $response, $this->_currentStatusResponse());
    	    	break;
    	    default:
    	    	return $this->_handleUnexpectedResponse($response);
    	}
    }

    // }}}
    // {{{ cmdPost()

    /**
     * Post an article to a newsgroup.
     *
     * Among the aditional headers you might think of adding could be:
     * "NNTP-Posting-Host: <ip-of-author>", which should contain the IP-adress
     * of the author of the post, so the message can be traced back to him.
     * "Organization: <org>" which contain the name of the organization
     * the post originates from.
     *
     * @param string $newsgroup The newsgroup to post to.
     * @param string $subject The subject of the post.
     * @param string $body The body of the post itself.
     * @param string $from Name + email-adress of sender.
     * @param optional mixed $headers Aditional headers to send.
     *
     * @return mixed (bool) true on success or (object) pear_error on failure
     * @access protected
     */
    function cmdPost($newsgroup, $subject, $body, $from, $headers = null)
    {
	// Only accept $headers is null, array or string
    	if (!is_null($headers) && !is_array($headers) && !is_string($headers)) {
    	    return PEAR::throwError('Ups', null, 0);
	}

        // tell the newsserver we want to post an article
    	$response = $this->_sendCommand('POST');
    	if (PEAR::isError($response)) {
    	    return $response;
        }

    	switch ($response) {
    	    case NET_NNTP_PROTOCOL_RESPONSECODE_POSTING_SEND: // 340, RFC977: 'send article to be posted. End with <CR-LF>.<CR-LF>'
    	    	// continue...
    	    	break;
    	    case NET_NNTP_PROTOCOL_RESPONSECODE_POSTING_PROHIBITED: // 440, RFC977: 'posting not allowed'
    	    	return PEAR::throwError('Posting not allowed', $response, $this->_currentStatusResponse());
    	    	break;
    	    default:
    	    	return $this->_handleUnexpectedResponse($response);
    	}

    	/* should be presented in the format specified by RFC850 */
	    
    	// Send standard headers and x-poster header
        $this->_socket->write("Newsgroups: $newsgroup\r\nSubject: $subject\r\nFrom: $from\r\n");
        $this->_socket->write("X-poster: PEAR::Net_NNTP\r\n");

    	// Send additional headers, if any
    	switch (true) {
    	    case is_null($headers):
		break;
    	    case is_array($headers):
    		foreach ($headers as $header=>$value) {
    	    	    switch (true) {
    	    		case is_string($header):
    	    	    	    echo $this->_socket->write("$header: $value\r\n");
		    	    break;
    	    		case is_int($header) && strpos($value, ':', 1):
    	    	    	    echo $this->_socket->write("$value\r\n");
    	    	    	    break;
    	    		default:
    	    	    	    // Ignore header...
    	    	    }
		}
    	    	break;
    	    case is_string($headers):
    	    	$this->_socket->write("$headers\r\n");
    	    	break;
	}
	
    	// Send body
        $this->_socket->write("\r\n");
        $this->_socket->write($body);
        $this->_socket->write("\r\n.\r\n");

    	// Retrive server's response.
    	$response = $this->_getStatusResponse();
    	if (PEAR::isError($response)) {
    	    return $response;
    	}

    	switch ($response) {
    	    case NET_NNTP_PROTOCOL_RESPONSECODE_POSTING_SUCCESS: // 240, RFC977: 'article posted ok'
    	    	return true;
    	    	break;
    	    case NET_NNTP_PROTOCOL_RESPONSECODE_POSTING_FAILURE: // 441, RFC977: 'posting failed'
    	    	return PEAR::throwError('Posting failed', $response, $this->_currentStatusResponse());
    	    	break;
    	    default:
    	    	return $this->_handleUnexpectedResponse($response);
    	}
    }

    // }}}
    // {{{ cmdIhave()

    /**
     *
     *
     * @param string $id
     * @param mixed $message (string/array)
     *
     * @return mixed (bool) true on success or (object) pear_error on failure
     * @access protected
     */
    function cmdIhave($id, $message)
    {
        // tell the newsserver we want to post an article
    	$response = $this->_sendCommand('IHAVE ' . $id);
    	if (PEAR::isError($response)) {
    	    return $response;
        }

    	switch ($response) {
    	    case NET_NNTP_PROTOCOL_RESPONSECODE_TRANSFER_SEND: // 335
    	    	// continue...
    	    	break;
    	    case NET_NNTP_PROTOCOL_RESPONSECODE_TRANSFER_UNWANTED: // 435
    	    	return PEAR::throwError('Article not wanted', $response, $this->_currentStatusResponse());
    	    	break;
    	    case NET_NNTP_PROTOCOL_RESPONSECODE_TRANSFER_FAILURE: // 436
    	    	return PEAR::throwError('Transfer not possible; try again later', $response, $this->_currentStatusResponse());
    	    	break;
    	    default:
    	    	return $this->_handleUnexpectedResponse($response);
    	}

    	/* should be presented in the format specified by RFC850 */
	    
    	// Send standard headers and x-poster header
        $this->_socket->write($message);
        $this->_socket->write("\r\n.\r\n");

    	// Retrive server's response.
    	$response = $this->_getStatusResponse();
    	if (PEAR::isError($response)) {
    	    return $response;
    	}

    	switch ($response) {
    	    case NET_NNTP_PROTOCOL_RESPONSECODE_TRANSFER_SUCCESS: // 235
    	    	return true;
    	    	break;
    	    case NET_NNTP_PROTOCOL_RESPONSECODE_TRANSFER_FAILURE: // 436
    	    	return PEAR::throwError('Transfer not possible; try again later', $response, $this->_currentStatusResponse());
    	    	break;
    	    case NET_NNTP_PROTOCOL_RESPONSECODE_TRANSFER_REJECTED: // 437
    	    	return PEAR::throwError('Transfer rejected; do not retry', $response, $this->_currentStatusResponse());
    	    	break;
    	    default:
    	    	return $this->_handleUnexpectedResponse($response);
    	}
    }

    // }}}
    // {{{ cmdGroup()

    /**
     * Selects a news group (issue a GROUP command to the server)
     *
     * @param string $newsgroup The newsgroup name
     *
     * @return mixed (array) groupinfo on success or (object) pear_error on failure
     * @access protected
     */
    function cmdGroup($newsgroup)
    {
        $response = $this->_sendCommand('GROUP '.$newsgroup);
        if (PEAR::isError($response)) {
            return $response;
        }

    	switch ($response) {
    	    case NET_NNTP_PROTOCOL_RESPONSECODE_GROUP_SELECTED: // 211, RFC977: 'n f l s group selected'
    	    	$response_arr = split(' ', trim($this->_currentStatusResponse()));

    	    	return array('count' => $response_arr[0],
    	                     'first' => $response_arr[1],
    	    	             'last'  => $response_arr[2],
    	    	             'group' => $response_arr[3]);
    	    	break;
    	    case NET_NNTP_PROTOCOL_RESPONSECODE_NO_SUCH_GROUP: // 411, RFC977: 'no such news group'
    	    	return PEAR::throwError('No such news group', $response, $this->_currentStatusResponse());
    	    	break;
    	    default:
    	    	return $this->_handleUnexpectedResponse($response);
    	}
    }

    // }}}
    // {{{ cmdList()

    /**
     * Fetches a list of all avaible newsgroups
     *
     * @return mixed (array) nested array with informations about existing newsgroups on success or (object) pear_error on failure
     * @access protected
     */
    function cmdList()
    {
        $response = $this->_sendCommand('LIST');
        if (PEAR::isError($response)){
            return $response;
        }

    	switch ($response) {
    	    case NET_NNTP_PROTOCOL_RESPONSECODE_GROUPS_FOLLOW: // 215, RFC977: 'list of newsgroups follows'
    	    	$data = $this->_getTextResponse();
    	    	if (PEAR::isError($data)) {
    	    	    return $data;
    	    	}

    	    	$groups = array();
    	    	foreach($data as $line) {
    	    	    $arr = explode(' ', trim($line));

    	    	    $group = array('group'   => $arr[0],
    	    	                   'last'    => $arr[1],
    	    	                   'first'   => $arr[2],
    	    	                   'posting' => $arr[3]);

    	    	    $groups[$group['group']] = $group;
    	    	}
    	        return $groups;
    	    	break;
    	    default:
    	    	return $this->_handleUnexpectedResponse($response);
    	}
    }

    // }}}
    // {{{ cmdListNewsgroups()

    /**
     * Fetches a list of (all) avaible newsgroup descriptions.
     *
     * @param string $wildmat Wildmat of the groups, that is to be listed, defaults to null;
     *
     * @return mixed (array) nested array with description of existing newsgroups on success or (object) pear_error on failure
     * @access protected
     */
    function cmdListNewsgroups($wildmat = null)
    {
        if (is_null($wildmat)) {
    	    $command = 'LIST NEWSGROUPS';
    	} else {
            $command = 'LIST NEWSGROUPS ' . $wildmat;
        }

        $response = $this->_sendCommand($command);
        if (PEAR::isError($response)){
            return $response;
        }

    	switch ($response) {
    	    case NET_NNTP_PROTOCOL_RESPONSECODE_GROUPS_FOLLOW: // 215, RFC2980: 'information follows'
    	    	$data = $this->_getTextResponse();
    	        if (PEAR::isError($data)) {
    	            return $data;
    	        }

    	    	$groups = array();

    	        foreach($data as $line) {
    	            preg_match("/^(.*?)\s(.*?$)/", trim($line), $matches);
    	            $groups[$matches[1]] = (string) $matches[2];
    	        }

    	        return $groups;
    		break;
    	    case 503: // RFC2980: 'program error, function not performed'
    	    	return PEAR::throwError('Internal server error, function not performed', $response, $this->_currentStatusResponse());
    	    	break;
    	    default:
    	    	return $this->_handleUnexpectedResponse($response);
    	}
    }

    // }}}
    /**
     * Fetches a list of (all) avaible newsgroup descriptions.
     * Depresated as of RFC2980.
     *
     * @param string $wildmat Wildmat of the groups, that is to be listed, defaults to '*';
     *
     * @return mixed (array) nested array with description of existing newsgroups on success or (object) pear_error on failure
     * @access protected
     */
    function cmdXGTitle($wildmat = '*')
    {
        $response = $this->_sendCommand('XGTITLE '.$wildmat);
        if (PEAR::isError($response)){
            return $response;
        }

    	switch ($response) {
    	    case 282: // RFC2980: 'list of groups and descriptions follows'
    	    	$data = $this->_getTextResponse();
    	        if (PEAR::isError($data)) {
    	            return $data;
    	        }

    	    	$groups = array();

    	        foreach($data as $line) {
    	            preg_match("/^(.*?)\s(.*?$)/", trim($line), $matches);
    	            $groups[$matches[1]] = (string) $matches[2];
    	        }

    	        return $groups;
    	    	break;
		  
    	    case 481: // RFC2980: 'Groups and descriptions unavailable'
    	    	return PEAR::throwError('Groups and descriptions unavailable', $response, $this->_currentStatusResponse());
    	    	break;
    	    default:
    	    	return $this->_handleUnexpectedResponse($response);
    	}
    }

    // }}}
    // {{{ cmdNewgroups()

    /**
     * Fetches a list of all newsgroups created since a specified date.
     *
     * @param int $time Last time you checked for groups (timestamp).
     * @param optional string $distributions (deprecaded in rfc draft)
     *
     * @return mixed (array) nested array with informations about existing newsgroups on success or (object) pear_error on failure
     * @access protected
     */
    function cmdNewgroups($time, $distributions = null)
    {
	$date = date('ymd His', $time);

        if (is_null($distributions)) {
    	    $command = 'NEWGROUPS ' . $date . ' GMT';
    	} else {
    	    $command = 'NEWGROUPS ' . $date . ' GMT <' . $distributions . '>';
        }

        $response = $this->_sendCommand($command);
        if (PEAR::isError($response)){
            return $response;
        }

    	switch ($response) {
    	    case NET_NNTP_PROTOCOL_RESPONSECODE_NEW_GROUPS_FOLLOW: // 231, REF977: 'list of new newsgroups follows'
    	    	$groups = array();
    	        foreach($this->_getTextResponse() as $line) {
    	    	    $arr = explode(' ', $line);
    	    	    $groups[$arr[0]] = array('group'   => $arr[0],
    	    	                             'last'    => $arr[1],
    	    	                             'first'   => $arr[2],
    	    	                             'posting' => $arr[3]);

    	    	}
    	    	return $groups;
    	    	break;
    	    default:
    	    	return $this->_handleUnexpectedResponse($response);
    	}
    }

    // }}}
    // {{{ cmdListOverviewFmt()

    /**
     * Returns a list of avaible headers which are send from newsserver to client for every news message
     *
     * @return mixed (array) of header names on success or (object) pear_error on failure
     * @access protected
     */
    function cmdListOverviewFmt()
    {
    	$response = $this->_sendCommand('LIST OVERVIEW.FMT');
        if (PEAR::isError($response)){
            return $response;
        }

    	switch ($response) {
    	    case NET_NNTP_PROTOCOL_RESPONSECODE_GROUPS_FOLLOW: // 215, RFC2980: 'information follows'
    	    	$data = $this->_getTextResponse();
    	        if (PEAR::isError($data)) {
    	            return $data;
    	        }

    	        $format = array('number');
    	        // XXX Use the splitHeaders() algorithm for supporting
    	        //     multiline headers?
    	        foreach ($data as $line) {
    	            $line = explode(':', trim($line));
    	            $format[] = $line[0];
    	        }
    	        return $format;
    	    	break;
    	    case 503: // RFC2980: 'program error, function not performed'
    	    	return PEAR::throwError('Internal server error, function not performed', $response, $this->_currentStatusResponse());
    	    	break;
    	    default:
    	    	return $this->_handleUnexpectedResponse($response);
    	}
    }

    // }}}
    // {{{ cmdXOver()

    /**
     * Fetch message header from message number $first until $last
     *
     * The format of the returned array is:
     * $messages[message_id][header_name]
     *
     * @param optional string $range articles to fetch
     *
     * @return mixed (array) nested array of message and there headers on success or (object) pear_error on failure
     * @access protected
     */
    function cmdXOver($range = null)
    {
	// deprecated API (the code _is_ still in alpha state)
    	if (func_num_args() > 1 ) {
    	    die('The second parameter in cmdXOver() has been deprecated! Use x-y instead...');
    	}

        $format = $this->cmdListOverviewFmt();
        if (PEAR::isError($format)){
            return $formt;
        }

        if (is_null($range)) {
	    $command = 'XOVER';
    	} else {
    	    $command = 'XOVER ' . $range;
        }

        $response = $this->_sendCommand($command);
        if (PEAR::isError($response)){
            return $response;
        }

    	switch ($response) {
    	    case NET_NNTP_PROTOCOL_RESPONSECODE_OVERVIEW_FOLLOWS: // 224, RFC2980: 'Overview information follows'
    	    	$data = $this->_getTextResponse();
    	        if (PEAR::isError($data)) {
    	            return $data;
    	        }
    	    	$messages = array();
    	        foreach($data as $line) {
    	            $i=0;
    	            foreach(explode("\t", trim($line)) as $line) {
    	                $message[$format[$i++]] = $line;
    	            }
    	            $messages[$message['Message-ID']] = $message;
    	        }
    	    	return $messages;
    	    	break;
    	    case NET_NNTP_PROTOCOL_RESPONSECODE_NO_GROUP_SELECTED: // 412, RFC2980: 'No news group current selected'
    	    	return PEAR::throwError('No news group current selected', $response, $this->_currentStatusResponse());
    	    	break;
    	    case NET_NNTP_PROTOCOL_RESPONSECODE_NO_ARTICLE_SELECTED: // 420, RFC2980: 'No article(s) selected'
    	    	return PEAR::throwError('No article(s) selected', $response, $this->_currentStatusResponse());
    	    	break;
    	    case 502: // RFC2980: 'no permission'
    	    	return PEAR::throwError('No permission', $response, $this->_currentStatusResponse());
    	    	break;
    	    default:
    	    	return $this->_handleUnexpectedResponse($response);
    	}
    }
    
    // }}}
    // {{{ cmdXROver()

    /**
     * Fetch message references from message number $first to $last
     *
     * @param optional string $range articles to fetch
     *
     * @return mixed (array) assoc. array of message references on success or (object) pear_error on failure
     * @access protected
     */
    function cmdXROver($range = null)
    {
	// Warn about deprecated API (the code _is_ still in alpha state)
    	if (func_num_args() > 1 ) {
    	    die('The second parameter in cmdXROver() has been deprecated! Use x-y instead...');
    	}

        if (! is_null($range)) {
	    $command = 'XROVER';
    	} else {
    	    $command = 'XROVER ' . $range;
        }

        $response = $this->_sendCommand($command);
        if (PEAR::isError($response)){
            return $response;
        }

    	switch ($response) {
    	    case NET_NNTP_PROTOCOL_RESPONSECODE_OVERVIEW_FOLLOWS: // 224, RFC2980: 'Overview information follows'
    	    	$data = $this->_getTextResponse();
    	        if (PEAR::isError($data)) {
    	            return $data;
    	        }

    	        foreach($data as $line) {

    	            $references = preg_split("/ +/", trim($line), -1, PREG_SPLIT_NO_EMPTY);

    	            $id = array_shift($references);

    	            $messages[$id] = $references;
    	        }
    	    	return $messages;
    	    	break;
    	    case NET_NNTP_PROTOCOL_RESPONSECODE_NO_GROUP_SELECTED: // 412, RFC2980: 'No news group current selected'
    	    	return PEAR::throwError('No news group current selected', $response, $this->_currentStatusResponse());
    	    	break;
    	    case NET_NNTP_PROTOCOL_RESPONSECODE_NO_ARTICLE_SELECTED: // 420, RFC2980: 'No article(s) selected'
    	    	return PEAR::throwError('No article(s) selected', $response, $this->_currentStatusResponse());
    	    	break;
    	    case 502: // RFC2980: 'no permission'
    	    	return PEAR::throwError('No permission', $response, $this->_currentStatusResponse());
    	    	break;
    	    default:
    	    	return $this->_handleUnexpectedResponse($response);
    	}
    }

    // }}}
    // {{{ cmdListgroup()

    /**
     *
     *
     * @param optional string $newsgroup 
     * @param optional mixed $range 
     *
     * @return optional mixed (array) on success or (object) pear_error on failure
     * @access protected
     */
    function cmdListgroup($newsgroup = null, $range = null)
    {
        if (is_null($newsgroup)) {
    	    $command = 'LISTGROUP';
    	} else {
    	    if (is_null($range)) {
    	        $command = 'LISTGROUP ' . $newsgroup;
    	    } else {
    	        $command = 'LISTGROUP ' . $newsgroup . ' ' . $range;
    	    }
        }

        $response = $this->_sendCommand($command);
        if (PEAR::isError($response)){
            return $response;
        }

    	switch ($response) {
    	    case NET_NNTP_PROTOCOL_RESPONSECODE_GROUP_SELECTED: // 211, RFC2980: 'list of article numbers follow'

    	    	$articles = $this->_getTextResponse();
    	        if (PEAR::isError($articles)) {
    	            return $articles;
    	        }
		
    	        $response_arr = split(' ', trim($this->_currentStatusResponse()));

    	    	return array('count'    => $response_arr[0],
    	                     'first'    => $response_arr[1],
    	    	             'last'     => $response_arr[2],
    	    	             'group'    => $response_arr[3],
    	    	             'articles' => $articles);
    	    	break;
    	    case NET_NNTP_PROTOCOL_RESPONSECODE_NO_GROUP_SELECTED: // 412, RFC2980: 'Not currently in newsgroup'
    	    	return PEAR::throwError('Not currently in newsgroup', $response, $this->_currentStatusResponse());
    	    	break;
    	    case 502: // RFC2980: 'no permission'
    	    	return PEAR::throwError('No permission', $response, $this->_currentStatusResponse());
    	    	break;
    	    default:
    	    	return $this->_handleUnexpectedResponse($response);
    	}
    }

    // }}}
    // {{{ cmdNewnews()

    /**
     *
     *
     * @param timestamp $time
     * @param mixed $newsgroups (string or array of strings)
     * @param mixed $distribution (string or array of strings)
     *
     * @return mixed 
     * @access protected
     */
    function cmdNewnews($time, $newsgroups, $distribution = null)
    {
        $date = date('ymd His', $time);

    	if (is_array()) {
    	    $newsgroups = implode(',', $newsgroups);
    	}
	

        if (is_null($distribution)) {
    	    $command = 'NEWNEWS ' . $newsgroups . ' ' . $date . ' GMT';
    	} else {
    	    if (is_array()) {
    		$distribution = implode(',', $distribution);
    	    }

    	    $command = 'NEWNEWS ' . $newsgroups . ' ' . $date . ' GMT <' . $distribution . '>';
        }

	// TODO: the lenght of the request string may not exceed 510 chars
	
        $response = $this->_sendCommand($command);
        if (PEAR::isError($response)){
            return $response;
        }

    	switch ($response) {
    	    case NET_NNTP_PROTOCOL_RESPONSECODE_NEW_ARTICLES_FOLLOW: // 230, RFC977: 'list of new articles by message-id follows'
    	    	$messages = array();
    	    	foreach($this->_getTextResponse() as $line) {
    	    	    $messages[] = $line;
    	    	}
    	    	return $messages;
    	    	break;
    	    default:
    	    	return $this->_handleUnexpectedResponse($response);
    	}
    }

    // }}}
    // {{{ cmdDate()

    /**
     * Get the date from the newsserver format of returned date
     *
     * @param bool $timestap when false function returns string, and when true function returns int/timestamp.
     *
     * @return mixed (string) 'YYYYMMDDhhmmss' / (int) timestamp on success or (object) pear_error on failure
     * @access protected
     */
    function cmdDate($timestamp = false)
    {
        $response = $this->_sendCommand('DATE');
        if (PEAR::isError($response)){
            return $response;
        }

    	switch ($response) {
    	    case NET_NNTP_PROTOCOL_RESPONSECODE_SERVER_DATE: // 111, RFC2980: 'YYYYMMDDhhmmss'
    	        $d = $this->_currentStatusResponse();
    	    	if ($timestamp === false) {
    	    	    return (string) $d;	    
    	    	} else {
    	    	    return (int) strtotime(substr($d, 0, 8).' '.$d[8].$d[9].':'.$d[10].$d[11].':'.$d[12].$d[13]);
    	    	}
    	    	break;
    	    default:
    	    	return $this->_handleUnexpectedResponse($response);
    	}
    }
    // }}}
    // {{{ isConnected()

    /**
     * Test whether we are connected or not.
     *
     * @return bool true or false
     * @access protected
     */
    function isConnected()
    {
    	return (is_resource($this->_socket->fp) && (!$this->_socket->eof()));
    }

    // }}}
    // {{{ setDebug()

    /**
     * Sets the debuging information on or off
     *
     * @param boolean $debug True or false 
     *
     * @return bool previos state
     * @access protected
     */
    function setDebug($debug = true)
    {
        $tmp = $this->_debug;
        $this->_debug = $debug;
        return $tmp;
    }

    // }}}
    // {{{ _handleUnexpectedResponse()

    /**
     *
     *
     * @param int $code Status code number
     * @param string $text Status text
     *
     * @return mixed
     * @access private
     */
    function _handleUnexpectedResponse($code = null, $text = null)
    {
    	if ($code === null) {
    	    $code = $this->_currentStatusResponse[0];
	}

    	if ($text === null) {
    	    $text = $this->_currentStatusResponse();
	}

    	return PEAR::throwError('Unexpected response', $code, $text);
    }

    // }}}
    // {{{ _getStatusResponse()

    /**
     * Get servers status response after a command.
     *
     * @return mixed (int) statuscode on success or (object) pear_error on failure
     * @access private
     */
    function _getStatusResponse()
    {
    	// Retrieve a line (terminated by "\r\n") from the server.
    	$response = $this->_socket->gets(256);
        if (PEAR::isError($response) ) {
    	    return PEAR::throwError('Failed to read from socket!', null, $response->getMessage());
        }

        if ($this->_debug) {
            echo "S: $response\r\n";
        }

    	// Trim the start of the response in case of misplased whitespace (should not be needen!!!)
    	$response = ltrim($response);

        $this->_currentStatusResponse = array(
    	    	    	    	    	      (int) substr($response, 0, 3),
    	                                      (string) rtrim(substr($response, 4))
    	    	    	    	    	     );

    	return $this->_currentStatusResponse[0];
    }
    
    // }}}
    // {{{ __currentStatusResponse()

    /**
     *
     *
     * @return string status text
     * @access private
     */
    function _currentStatusResponse()
    {
    	return $this->_currentStatusResponse[1];
    }
    
    // }}}
    // {{{ _getTextResponse()

    /**
     * Retrieve textural data
     *
     * Get data until a line with only a '.' in it is read and return data.
     *
     * @return mixed (array) text response on success or (object) pear_error on failure
     * @access private
     */
    function _getTextResponse()
    {
        $data = array();
        $line = '';
	
        // Continue until connection is lost
        while(!$this->_socket->eof()) {

            // Retrieve and append up to 1024 characters from the server.
            $line .= $this->_socket->gets(1024); 
            if (PEAR::isError($line) ) {
                return PEAR::throwError( 'Failed to read from socket!', null, $line->getMessage());
    	    }
	    
            // Continue if the line is not terminated by CRLF
            if (substr($line, -2) != "\r\n" || strlen($line) < 2) {
                continue;
            }

            // Validate recieved line
            if (false) {
                // Lines should/may not be longer than 998+2 chars (RFC2822 2.3)
                if (strlen($line) > 1000) {
                    return PEAR::throwError('Invalid line recieved!', null);
                }
            }

            // Remove CRLF from the end of the line
            $line = substr($line, 0, -2);

            // Check if the line terminates the textresponse
            if ($line == '.') {
                // return all previous lines
                return $data;
                break;
            }

            // If 1st char is '.' it's doubled (NNTP/RFC977 2.4.1)
            if (substr($line, 0, 2) == '..') {
                $line = substr($line, 1);
            }
            
            // Add the line to the array of lines
            $data[] = $line;

            // Reset/empty $line
            $line = '';
        }

    	return PEAR::throwError('Data stream not terminated with period', null);
    }

    // }}}
    // {{{ _sendCommand()

    /**
     * Send command
     *
     * Send a command to the server. A carriage return / linefeed (CRLF) sequence
     * will be appended to each command string before it is sent to the IMAP server.
     *
     * @param string $cmd The command to launch, ie: "ARTICLE 1004853"
     *
     * @return mixed (int) response code on success or (object) pear_error on failure
     * @access private
     */
    function _sendCommand($cmd)
    {
        // NNTP/RFC977 only allows command up to 512 (-2) chars.
        if (!strlen($cmd) > 510) {
            return PEAR::throwError('Failed to write to socket! (Command to long - max 510 chars)');
        }

    	// Check if connected
    	if (!$this->isConnected()) {
            return PEAR::throwError('Failed to write to socket! (connection lost!)');
        }

    	// Send the command
    	$R = $this->_socket->writeLine($cmd);
        if ( PEAR::isError($R) ) {
            return PEAR::throwError('Failed to write to socket!', null, $R->getMessage());
        }
	
        if ($this->_debug) {
            echo "C: $cmd\r\n";
        }

    	return $this->_getStatusResponse();
    }
    
    // }}}

}

// }}}

?>
