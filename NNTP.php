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
// |          Tomas V.V.Cox  <cox@idecnet.com>                            |
// |                                                                      |
// +----------------------------------------------------------------------+
//
// $Id$

require_once 'PEAR.php';

define('NNTP_ALL',   0);
define('NNTP_NAMES', 1);
define('NNTP_LIST',  2);
/**
 * The NNTP:: class fetches UseNet news articles acording to the standard
 * based on RFC 1036.
 *
 * @version 0.1
 * @author Martin Kaltoft <martin@nitro.dk>
 * @author Tomas V.V.Cox  <cox@idecnet.com>
 */

class Net_Nntp extends PEAR
{

    var $max = '';
    var $min = '';
    var $user = null;
    var $pass = null;
    var $authmode = null;

    /** File pointer of the nntp-connection */
    var $fp = null;

    /**
    * Output or not debug information
    * @see Net_Nntp::set_debug()
    */
    var $_debug = false;

    /**
     * Connect to the newsserver
     *
     * @param string $nntpserver The adress of the NNTP-server to connect to.
     * @param int $port (optional) the port-number to connect to, defaults to 119.
     * @param string $user (optional) The user name to authenticate with
     * @param string $pass (optional) The password
     * @param string $authmode (optional) The authentication mode
     * @return mixed True on success or Pear Error object on failure
     * @see Net_Nntp::authenticate()
     * @access public
     */
    function connect($nntpserver,
                     $port = 119,
                     $user = null,
                     $pass = null,
                     $authmode = 'original')
    {
        $fp = @fsockopen($nntpserver, $port, $errno, $errstr, 15);
        if (!is_resource($fp)) {
            return $this->raiseError("Could not connect to NNTP-server $nntpserver");
        }
        socket_set_blocking($fp, true);
        if (!$fp) {
            return $this->raiseError('Not connected');
        }
        $response = fgets($fp, 128);
        if ($this->_debug) {
            print "<< $response\n";
        }
        $this->fp   = $fp;
        $this->user = $user;
        $this->pass = $pass;
        $this->authmode = $authmode;

        return true;
    }

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
     * @param string $user (optional) The user name to authenticate with
     * @param string $pass (optional) The password
     * @param string $authmode (optional) The authentication mode
     * @return mixed True on success or Pear Error object on failure
     * @see Net_Nntp::authenticate()
     * @access public
     * @deprecated Use connect() instead
     */
    function prepare_connection($nntpserver,
                                $port = 119,
                                $newsgroup,
                                $user = null,
                                $pass = null,
                                $authmode = 'original')
    {
        /* connect to the server */
        $err = $this->connect($nntpserver, $port, $user, $pass, $authmode);
        if (PEAR::isError($err)) {
            return $err;
        }

        /* issue a GROUP command */
        $r = $this->command("GROUP $newsgroup");

        if (PEAR::isError($r) || $this->responseCode($r) > 299) {
            return $this->raiseError($r);
        }
        $response_arr = split(' ', $r);
        $this->max = $response_arr[3];
        $this->min = $response_arr[2];

        return true;
    }

    /**
    * Auth process (not yet standarized but used any way)
    * http://www.mibsoftware.com/userkt/nntpext/index.html
    *
    * @param string $user The user name
    * @param string $pass (optional) The password if needed
    * @param string $mode Authinfo form: original, simple, generic
    * @return mixed (bool) true on success or Pear Error obj on fail
    */
    function authenticate($user = null, $pass = null, $mode = 'original')
    {
        if ($user === null) {
            return $this->raiseError('Authentication required but no user supplied');
        }
        switch ($mode) {
            case 'original':
                /*
                    281 Authentication accepted
                    381 More authentication information required
                    480 Authentication required
                    482 Authentication rejected
                    502 No permission
                */
                $response = $this->command("AUTHINFO user $user", false);
                if ($this->responseCode($response) != 281) {
                    if ($this->responseCode($response) == 381 && $pass !== null) {
                        $response = $this->command("AUTHINFO pass $pass", false);
                    }
                }
                if ($this->responseCode($response) != 281) {
                    return $this->raiseError("Authentication failed: $response");
                }
                return true;
                break;
            case 'simple':
            case 'generic':
            default:
                $this->raiseError("The auth mode: $mode isn't implemented");
        }
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
        /* tell the newsserver we want an article */
        $r = $this->command("ARTICLE $article");
        if (PEAR::isError($r) || $this->responseCode($r) > 299) {
            return $this->raiseError($r);
        }
        $post = null;
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
            return $this->raiseError('Not connected');
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
        /* tell the newsserver we want an article */
        $r = $this->command("HEAD $article");
        if (PEAR::isError($r) || $this->responseCode($r) > 299) {
            return $this->raiseError($r);
        }

        $headers = '';
        while(!feof($this->fp)) {
            $line = trim(fgets($this->fp, 256));

            if ($line == '.') {
                break;
            } else {
                $headers .= $line . "\n";
            }
        }
        return $headers;
    }


    /**
    * Returns the headers of a given article in the form of
    * an associative array. Ex:
    * array(
    *   'From'      => 'foo@bar.com (Foo Smith)',
    *   'Subject'   => 'Re: Using NNTP class',
    *   ....
    *   );
    *
    * @param $article string Article number or id
    * @return array Assoc array with headers names as key or Pear obj error
    */
    function split_headers($article)
    {
        $headers = $this->get_headers($article);
        if (PEAR::isError($headers)) {
            return $headers;
        }

        $lines = explode("\n", $headers);
        foreach ($lines as $line) {
            $line = trim($line);
            if (($pos = strpos($line, ':')) !== false) {
                $head = substr($line, 0, $pos);
                $ret[$head] = ltrim(substr($line, $pos+1));
            // if the field was longer than 256 chars, look also in the next line
            // XXX a better way to discover that than strpos?
            } else {
                $ret[$head] .= $line;
            }
        }
        if (isset($ret['References'])) {
            $ret['References'] = explode (' ', $ret['References']);
        }
        return $ret;
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
        /* tell the newsserver we want an article */
        $r = $this->command("BODY $article");
        if (PEAR::isError($r) || $this->responseCode($r) > 299) {
            return $this->raiseError($r);
        }

        $body = null;
        while(!feof($this->fp)) {
            $line = trim(fgets($this->fp, 256));

            if ($line == '.') {
                break;
            } else {
                $body .= $line ."\n";
            }
        }
        return $body;
    }

    /**
     * Get data until a line with only a '.' in it is read and return data.
     *
     * @author Morgan Christiansson <mog@linux.nu>
     */
    function get_data()
    {
        $body = array();
        while(!feof($this->fp)) {
            $line = trim(fgets($this->fp, 256));
            if ($line == '.') {
                break;
            } else {
                $body[] = $line;
            }
        }
        return $body;
    }

    /**
    * Selects a news group (issue a GROUP command to the server)
    * @param string $newsgroup The newsgroup name
    * @return mixed True on success or Pear Error object on failure
    */
    function select_group($newsgroup)
    {
        $r = $this->command("GROUP $newsgroup");
        if (PEAR::isError($r) || $this->responseCode($r) > 299) {
            return $this->raiseError($r);
        }
        $response_arr = split(' ', $r);
        $this->max = $response_arr[3];
        $this->min = $response_arr[2];

        return array(
                     "first" => $response_arr[2],
                     "last"  => $response_arr[3]
                    );
    }

    /**
     *
     * @param int $fetch NNTP_ALL NNTP_NAMES NNTP_LIST
     * @author Morgan Christiansson <mog@linux.nu>
     */
    function get_groups($fetch = true)
    {
        $this->command("LIST");
        foreach($this->get_data() as $line) {
            $arr = explode(" ",$line);
            $groups[$arr[0]]["group"] = $arr[0];
            $groups[$arr[0]]["last"] = $arr[1];
            $groups[$arr[0]]["first"] = $arr[2];
            $groups[$arr[0]]["posting_allowed"] = $arr[3];
        }

        $this->command("LIST NEWSGROUPS");
        foreach($this->get_data() as $line) {
            preg_match("/^(.*?)\s(.*?$)/",$line,$matches);
            $groups[$matches[1]]["desc"] = $matches[2];
        }
        return $groups;
    }

    function get_overview_fmt()
    {
        $this->command("LIST OVERVIEW.FMT");
        $format = array("number");
        foreach($body = $this->get_data() as $line) {
            $line = current(explode(":",$line));
            $format[] = $line;
        }
        return $format;
    }

    function get_overview($first,$last) {
        $format = $this->get_overview_fmt();

        $this->command("XOVER $first-$last");
        foreach($this->get_data() as $line) {
            $i=0;
            foreach(explode("\t",$line) as $line) {
                $message[$format[$i++]] = $line;
            }
            $messages[$message["Message-ID"]] = $message;
        }

        $this->command("XROVER $first-$last");
        foreach($this->get_data() as $line) {
            $i=0;
            foreach(explode("\t",$line) as $line) {
                $message[$format[$i++]] = $line;
            }
            $messages[$message["Message-ID"]] = $message;
        }
        return $messages;
    }

    /**
     * Get the date from the newsserver
     *
     * @access public
     */
    function date()
    {
        $r = $this->command('DATE');
        if (PEAR::isError($r) || $this->responseCode($r) > 299) {
            return $this->raiseError($r);
        }
        return true;
    }

    /**
     * Maximum article number in current group
     *
     * @access public
     */
    function max()
    {
        if (!@is_resource($this->fp)) {
            return $this->raiseError('Not connected');
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
            return $this->raiseError('Not connected');
        }
        return $this->min;
    }

    /**
     * Test whether we are connected or not.
     *
     * @return bool true or false
     * @access public
     */
    function is_connected()
    {
        if (@is_resource($this->fp)) {
            return true;
        }
        return false;
    }

    /**
     * Close connection to the newsserver
     *
     * @access public
     */
    function quit()
    {
        $this->command("QUIT");
        fclose($this->fp);
    }

    function responseCode($response)
    {
        $parts = explode(' ', ltrim($response));
        return (int) $parts[0];
    }

    function set_debug($on = true)
    {
        $this->_debug = $on;
    }

    /**
    * Issue a command to the NNTP server
    * @param string $cmd The command to launch, ie: "ARTICLE 1004853"
    * @param bool $testauth Test or not the auth
    * @return mixed True on success or Pear Error object on failure
    */
    function command($cmd, $testauth = true)
    {
        if (!@is_resource($this->fp)) {
            return $this->raiseError('Not connected');
        }
        fputs($this->fp, "$cmd\r\n");
        if ($this->_debug) {
            print ">> $cmd\n";
        }
        $response = fgets($this->fp, 128);
        if ($this->_debug) {
            print "<< $response\n";
        }
        // From the spec: "In all cases, clients must provide
        // this information when requested by the server. Servers are
        // not required to accept authentication information that is
        // volunteered by the client"
        $code = $this->responseCode($response);
        if ($testauth && ($code == 450 || $code == 480)) {
            $error = $this->authenticate($this->user, $this->pass, $this->authmode);
            if (PEAR::isError($error)) {
                return $error;
            }
            /* re-issue the command */
            $response = $this->command($cmd, false);
        }
        return $response;
    }
}
?>