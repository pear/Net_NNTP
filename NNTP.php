<?php
//
// +----------------------------------------------------------------------+
// | PHP version 4.0                                                      |
// +----------------------------------------------------------------------+
// | Copyright (c) 1997-2001 The PHP Group                                |
// +----------------------------------------------------------------------+
// | This source file is subject to version 2.0 of the PHP license,       |
// | that is bundled with this package in the file LICENSE, and is        |
// | available at through the world-wide-web at                           |
// | http://www.php.net/license/2_02.txt.                                 |
// | If you did not receive a copy of the PHP license and are unable to   |
// | obtain it through the world-wide-web, please send a note to          |
// | license@php.net so we can mail you a copy immediately.               |
// +----------------------------------------------------------------------+
// | Authors: Martin Kaltoft <martin@nitro.dk>                            |
// +----------------------------------------------------------------------+

require_once 'PEAR.php';

/**
 * The NNTP:: class fetches UseNet news articles acording to the standard
 * based on RFC 1036.                                              
 * To parse the articles into its appropriate entities, use the NNTP
 * Parser class.                                                    
 * 
 * @version 0.1
 * @author Martin Kaltoft <martin@nitro.dk>                         
 */

class Net_Nntp extends PEAR {

    var $max = "";
    var $min = "";

    /** File pointer of the nntp-connection */
    var $fp = null;


    /**
     * Connect to the newsserver, and issue a GROUP command
     * Once connection is prepared, we can only fetch articles from one group 
     * at a time, to fetch from another group, a new connection has to be made.
     *
     * This is to avoid the GROUP command for every article, as it is very 
     * ressource intensive on the newsserver especially when used for 
     * groups with many articles.
     *
     * @param string $nntpserver The adress of the NNTP-server to connect to.
     * @param int $port (optional) the port-number to connect to, defaults to 119.
     * @param string $newsgroup The name of the newsgroup to use.
     * @access public
     */
    function prepare_connection($nntpserver, $port = 119, $newsgroup)
    {
	/* connect to the server */
	$fp = @fsockopen($nntpserver, $port, $errno, $errstr, 15);
	if (!$fp) {
	    return new PEAR_Error("Could not connect to NNTP-server");
	}

    	set_socket_blocking($fp, true);

	if (!$fp) {
		return new PEAR_Error("Not connected");
	}

	$response = fgets($fp, 128);

	/* issue a GROUP command */
	fputs($fp, "GROUP $newsgroup\n");
	$response = fgets($fp, 128);

	$response_arr = split(" ", $response);
	$this->max = $response_arr[3];
	$this->min = $response_arr[2];
	$this->fp = $fp;
    }

    /**
     * Get an article from the currently open connection.
     * To get articles from another newsgroup a new prepare_connection() - 
     * call has to be made with apropriate parameters
     *
     * @param mixed $article Either the message-id or the message-number on the server of the article to fetch
     * @access public
     */
    function get_article($article)
    {
	if (!@is_resource($this->fp)) {
	    return new PEAR_Error("Not connected");
	}

	/* tell the newsserver we want an article */
	fputs($this->fp, "ARTICLE $article\n");

	/* The servers' response */
	$response = trim(fgets($this->fp, 128));

	while(!feof($this->fp)) {
	    $line = trim(fgets($this->fp, 256));

	    if ($line == ".") {
		break;
	    } else {
		$post .= $line ."\n";
	    }
	}
	return $post;
    }

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
     * @param string $aditionak (optional) Aditional headers to send.
     * @access public
     */
    function post($subject, $newsgroup, $from, $body, $aditional = "")
    {
	if (!@is_resource($this->fp)) {
	    return new PEAR_Error("Not connected");
	}

	/* tell the newsserver we want to post an article */
	fputs($this->fp, "POST\n");

	/* The servers' response */
	$response = trim(fgets($this->fp, 128));

	fputs($this->fp, "From: $from\n");
	fputs($this->fp, "Newsgroups: $newsgroup\n");
	fputs($this->fp, "Subject: $subject\n");
	fputs($this->fp, "X-poster: nntp_fetcher (0.1) by Martin Kaltoft\n");
	fputs($this->fp, "$aditional\n");
	fputs($this->fp, "\n$body\n.\n");

	/* The servers' response */
	$response = trim(fgets($this->fp, 128));

	return $response;
    }


    /**
     * Get the headers of an article from the currently open connection
     * To get the headers of an article from another newsgroup, a new 
     * prepare_connection()-call has to be made with apropriate parameters
     *
     * @param string $article Either a message-id or a message-number of the article to fetch the headers from.
     * @access public
     */
    function get_headers($article)
    {
	if (!@is_resource($this->fp)) {
	    return new PEAR_Error("Not connected");
	}

	/* tell the newsserver we want an article */
	fputs($this->fp, "HEAD $article\n");

	/* The servers' response */
	$response = trim(fgets($this->fp, 128));

	while(!feof($this->fp)) {
	    $line = trim(fgets($this->fp, 256));

	    if ($line == ".") {
		break;
	    } else {
		$headers .= $line ."\n";
	    }
	}
	return $headers;
    }

    /**
     * Get the body of an article from the currently open connection.
     * To get the body of an article from another newsgroup, a new 
     * prepare_connection()-call has to be made with apropriate parameters
     *
     * @param string $article Either a message-id or a message-number of the article to fetch the headers from.
     * @access public
     */
    function get_body($article)
    {
	if (!@is_resource($this->fp)) {
	    return new PEAR_Error("Not connected");
	}

	/* tell the newsserver we want an article */
	fputs($this->fp, "BODY $article\n");

	/* The servers' response */
	$response = trim(fgets($this->fp, 128));

	while(!feof($this->fp)) {
	    $line = trim(fgets($this->fp, 256));

	    if ($line == ".") {
		break;
	    } else {
		$body .= $line ."\n";
	    }
	}
	return $body;
    }

    /**
     * Get the date from the newsserver
     *
     * @access public
     */
    function date()
    {
	if (!@is_resource($this->fp)) {
	    return new PEAR_Error("Not connected");
	}

	fputs($this->fp, "DATE\n");
	$response = trim(fgets($this->fp, 128));

	return $response;
    }

    /**
     * Maximum article number in current group
     *
     * @access public
     */
    function max()
    {
	if (!@is_resource($this->fp)) {
	    return new PEAR_Error("Not connected");
	}

	return $this->max;
    }

    /**
     * Minimum article number in current group
     *
     * @access public
     */
    function min()
    {
	if (!@is_resource($this->fp)) {
	    return new PEAR_Error("Not connected");
	}

	return $this->min;
    }


    /**
     * Test whether we are connected or not.
     *
     * @access public
     */
    function is_connected()
    {
	if (!@is_resource($this->fp)) {
	    $conn = 0;
	} elseif (@is_resource($this->fp)) {
	    $conn = 1;
	} else {
	    $conn = 0;
	}
	return $conn;
    }

    /**
     * Close connection to the newsserver
     *
     * @access public
     */
    function quit()
    {
	if (!@is_resource($this->fp)) {
	    return new PEAR_Error("Not connected");
	}

	fputs($this->fp, "QUIT\n");
    }
}
