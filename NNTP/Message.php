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
* @author  Heino H. Gehlsen <heino@gehlsen.dk>
* @version $Revision$
* @package 
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

/*-------------------------------------------------------------------------------------------------*/

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

	$this->header = $header;
	$this->body   = $body;
	
	if ($this->header == null) {
	    $this->header = new Net_NNTP_Header();
	}
    }

    // }}}

/*-------------------------------------------------------------------------------------------------*/

    // {{{ setHeader()

    /**
     * 
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
		    $this->header->importString($string);
		    break;

		case 'array':
		    $array = $this->header->cleanString($input);
		    $this->header->importArray($array);
		    break;

		default:
// TODO: Fail
	    }
	}
    }

    // }}}
    // {{{ getHeader()

    /**
     * 
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
     * 
     *
     * @param string $body
     *
     * @access public
     */
    function setBody($body)
    {
	$this->body =& $body;
    }

    // }}}
    // {{{ getBody()

    /**
     * 
     *
     * @return string
     * @access public
     */
    function getBody()
    {
	return $this->body;
    }

    // }}}
    // {{{ setMessage()

    /**
     * 
     *
     * @param string $message
     *
     * @access public
     */
    function setMessage($message)
    {
	$array =& $this->splitMessage(&$message);
	$this->setHeader($array['header']);
	$this->setBody($array['body']);	
    }

    // }}}
    // {{{ getMessage()

    /**
     * 
     *
     * @return string
     * @access public
     */
    function getMessage()
    {
	return $this->header->exportString()."\r\n".implode("\r\n", $this->body);
    }

    // }}}
    // {{{ splitMessage()

    /**
     * Given a string containing a header and body
     * section, this function will split them (at the first
     * blank line) and return them.
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
