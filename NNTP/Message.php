<?php
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
require_once 'Net/NNTP/Header.php';

/**
 *
  The Net_NNTP_Message class
 *
 * @version $Revision$
 * @package Net_NNTP
 *
 * @author  Heino H. Gehlsen <heino@gehlsen.dk>
 */

class Net_NNTP_Message // extends PEAR
{
    // {{{ properties

    /*
     * Contains the parsed headers of the message
     *
     * @var    object
     * @access public
     */
    var $header;
				 
    /**
     * Contains the body part of the message
     *
     * @var    string
     * @access public
     */
    var $body;

    // }}}
    // {{{ constructor

    /**
     * Constructor.
     *
     * @param optional object $header
     * @param optional array $body
     *
     * @access public
     */
    function Net_NNTP_Message($header = null, $body = '')
    {
//	parent::Pear();

	if ($this->header != null) {
    	    $this->header = $header;
	} else {
	    $this->header = new Net_NNTP_Header();
	}

	$this->body   = $body;
    }

    // }}}
    // {{{ setMessage()

    /**
     * Sets the header and body grom the given $message
     *
     * @param string $message
     *
     * @access public
     */
    function setMessage($message)
    {
	if (is_a($input, 'net_nntp_message')) {
	    $this =& $message;
	} else {
	    switch (gettype($message)) {
		case 'array':
		case 'string':
    		    $array =& $this->splitMessage(&$message);
	    	    $this->setHeader(&$array['header']);
		    $this->setBody(&$array['body']);
		    break;
		
		default:
		    return PEAR::throwError('Unsupported type: '.gettype($message), null);
	    }
	}
    }

    // }}}
    // {{{ getMessageString()

    /**
     * Get the complete transport-ready message as a string
     *
     * @return string
     * @access public
     */
    function getMessageString()
    {
	return $this->header->getFieldsString()."\r\n\r\n".$this->body;
    }

    // }}}
    // {{{ getMessageArray()

    /**
     * Get the complete transport-ready message as an array
     *
     * @return string
     * @access public
     */
    function getMessageArray()
    {
	$header = $this->header->getFieldsArray();
	$header[] = '';
	return array_merge($header, explode("\r\n", $this->body));
    }

    // }}}
    // {{{ setHeader()

    /**
     * Sets the header's fields from the given $input
     *
     * @param mixed $input
     *
     * @access public
     */
    function setHeader($input)
    {
	if (is_a($input, 'net_nntp_header')) {

	    $this->header = $input;

	} else {

	    switch (strtolower(gettype($input))) {
		case 'string':
		    $string = $this->header->cleanString($input);
		    $this->header->setFields($string);
		    break;

		case 'array':
		    $array = $this->header->cleanArray($input);
		    $this->header->setFields($array);
		    break;

		default:
		    return PEAR::throwError('Unsupported type: '. gettype($input), null);
	    }
	}
    }

    // }}}
    // {{{ getHeader()

    /**
     * Gets the header object
     *
     * @return object
     * @access public
     */
    function getHeader()
    {
	return $this->header;
    }

    // }}}
    // {{{ setBody()

    /**
     * Sets the body
     *
     * @param string $body
     *
     * @access public
     */
    function setBody($body)
    {
	if (is_array($body)) {
	    $this->body =& implode("\r\n", &$body);
	} else {
	    $this->body =& $body;
	}
    }

    // }}}
    // {{{ getBody()

    /**
     * Gets the body
     *
     * @return string
     * @access public
     */
    function getBody()
    {
	return $this->body;
    }

    // }}}
    // {{{ splitMessage()

    /**
     * Splits the header and body as given in $input apart (at the first
     * blank line) and return them in the same type as $input.
     *
     * @param mixed $input Message in form of eiter string or array
     *
     * @return array Contains separated header and body section
s in same type as $input
     * @access public
     */
    function splitMessage($input)
    {
	switch (gettype($input)) {
	    case 'string':
    		if (preg_match("/^(.*?)\r?\n\r?\n(.*)/s", $input, $matches)) {
    		    return array('header' => &$matches[1], 'body' => &$matches[2]);
    		}
 else {
	    	    return PEAR::throwError('Could not split header and body');
		}
		break;
		
	    case 'array':
		$header = array();
		while (($line = array_shift($input)) != '') {
		    $header[] = $line;
		}
    		return array('header' => &$header, 'body' => $input);
		break;
		
	    default:
	        return PEAR::throwError('Unsupported type: '.gettype($input));
	}
    }

    // }}}

}

?>
