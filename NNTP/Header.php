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

/**
 * The Net_NNTP_Header class
 *
 * @version 0.0.1
 * @author Heino H. Gehlsen <heino@gehlsen.dk>
 */

class Net_NNTP_Header extends PEAR
{
    // {{{ properties

    /**
     * Container for the header fields
     *
     * @var    array
     * @access public
     */
    var $fields;

    // }}}
    // {{{ constructor

    /**
     * Constructor
     *
     * @access public
     * @since 0.1
     */
    function Net_NNTP_Header()
    {
	parent::PEAR();
	
	// Reset object
	$this->reset();

	// Set default values;
	$this->_modifyHeaderNameCase = true;
	$this->_unfoldOnParse = true;
	$this->_decodeOnParse = true;
	$this->_encodeOnRegenerate = false;
	$this->_foldOnRegenerate = false;
    }

    // }}}
    // {{{ reset()
    
    /**
     * Reset the field container
     * 
     * @access public
     * @since 0.1
     */
    function reset()
    {
	$this->fields = array();
    }

    // }}}
    // {{{ add()
    
    /**
     * Add a new line to the header
     * 
     * @param string $tag
     * @param string $value
     * @param optional int $index
     * 
     * @access public
     * @since 0.1
     */
    function add($tag, $value, $index = null)
    {
	// Add header to $return array
    	if (isset($this->fields[$tag]) && is_array($this->fields[$tag])) {
	    // The header name has already been used at least two times.
            $this->fields[$tag][] = $value;
        } elseif (isset($this->fields[$tag])) {
	    // The header name has already been used one time -> change to nedted values.
            $this->fields[$tag] = array($this->fields[$tag], $value);
        } else {
	    // The header name has not used until now.
	    $this->fields[$tag] = $value;
        }
    }

    // }}}
    // {{{ replace()
    
    /**
     * Replace a line in the header
     * 
     * @param string $tag
     * @param string $value
     * @param optional int $index
     * 
     * @access public
     * @since 0.1
     */
    function replace($tag, $value, $index = null)
    {
	if (isset($this->fields[$tag])) {
	    if ($index === null) {
		$this->fields[$tag] = $value;
	    } else {
		if (is_array($this->fields[$tag])) {
		    $this->fields[$tag][$index] = $value;
		} else {
//TODO: Currently ignores $index, and just replaces the value
		    $this->fields[$tag] = $value;
		}
	    }
	} else {
	    $this->fields[$tag] = $value;
	}
    }

    // }}}
    // {{{ delete()
    
    /**
     * Delete a tag from the header
     * 
     * @param string $tag
     * @param optional int $index
     * 
     * @access public
     * @since 0.1
     */
    function delete($tag, $index = null)
    {
	if (isset($this->fields[$tag])) {
	    if ($index == null) {
		unset($this->fields[$tag]);
	    } else {
		if (is_array($this->fields[$tag])) {
		    unset($this->fields[$tag][$index]);
		} else {
		    unset($this->fields[$tag]);
		}
	    }
	} else {
	    // Do nothing...
	}
    }

    // }}}
    // {{{ count()
    
    /**
     * Returns the number of times the given tag appears in the header.
     * 
     * @param string $tag
     * 
     * @return int
     * @access public
     * @since 0.1
     */
    function count($tag)
    {
	if (isset($this->fields[$tag])) {
	    if (is_array($this->fields[$tag])) {
		return count($this->fields[$tag]);
	    } else {
		return 1;
	    }
	} else {
	    return false;
	}
    }

    // }}}
    // {{{ tags()
    
    /**
     * Retruns an array of all the tags that exist in the header.
     * Each tag will only appear in the list once.
     * 
     * @return array
     * @access public
     * @since 0.1
     */
    function tags()
    {
	return array_keys($this->fields);
    }

    // }}}
    // {{{ clean()
    
    /**
     * Remove any header line that, other than the tag, only contains whitespace.
     * 
     * @access public
     * @since 0.1
     */
    function clean()
    {
// TODO:
    }

    // }}}
    // {{{ setFields()
    
    /**
     * Import RFC2822 style header lines given in $string into the object
     * 
     * @param mixed $input RFC2822 style header lines as string (CRLF included) or array (CRLF not included)
     * 
     * @access public
     * @since 0.1
     */
    function setFields($input)
    {
	switch (gettype($input)) {
	    case 'string':
		$this->fields =& $this->parseString(&$input);
		break;
	    case 'array':
		$this->fields =& $this->parseArray(&$input);
		break;
	}
    }

    // }}}
    // {{{ getFields()

    /**
     *
     * 
     * @return array
     * @access public
     * @since 0.1
     */
    function getFields()
    {
	return $this->fields;
    }

    // }}}
    // {{{ getFieldsString()

    /**
     * Export a string of RFC2822 style header style lines from the object.
     * 
     * @return string RFC2822 style header lines (CRLF included)
     * @access public
     * @since 0.1
     */
    function getFieldsString()
    {
	return $this->regenerateString(
&$this->fields);
    }

    // }}}
    // {{{ getFieldsArray()

    /**
     * Export an array of RFC2822 style header style lines from the object.
     *
     * @return array RFC2822 style header lines (CRLF not included)
     * @access public
     * @since 0.1
     */
    function getFieldsArray()
    {
	return $this->regenerateArray(&$this->fields
);
    }

    // }}}
    // {{{ parseString()
    
    /**
     * Parse a string of RFC2822 style header lines into a 'header array' with the header names as keys.
     * When header names a'pear more the once, the resulting array will have the values nested in the order of a'pear'ence.
     * 
     * @param string $string RFC2822 style header lines (CRLF included)
     * 
     * @return array 'header array' with the header names as keys, values may be nested.
     * @access public
     * @since 0.1
     */
    function parseString($string)
    {
	// Convert to array
	$array =& explode("\r\n", &$string);

	// Forward to parseArray()
	return $this->parseArray(&$array);
    }

    // }}}
    // {{{ parseArray()

    /**
     * Parse an array of RFC2822 style header lines into a 'header array' with the header names as keys.
     * When header names a'pear more the once, the resulting array will have the values nested in the order of a'pear'ence.
     * 
     * @param array $array RFC2822 style header lines (CRLF not included)
     *
     * @return array 'header array' with the header names as keys, values may be nested.
     * @access public
     * @since 0.1
     */
    function parseArray($array)
    {
	// Duplicate to prevent any modification af the original array, if user parses by reference
	$headers = $array;
    	// Unfold the headers
	if ($this->_unfoldOnParse == true) {
	    $headers =& $this->unfoldArray(&$headers);
	}

	// Init return variable
	$return = array();

	// Loop through all headers
        foreach ($headers as $header) {
	    // Separate header name and value
            $name = substr($header, 0, $pos = strpos($header, ':'));
            $value = substr($header, $pos + 1);

	    // Change header name to lower case
	    if ($this->_modifyHeaderNameCase == true) {
 		$name = strtolower($name);
	    }
	    
    	    // Remove commonly used space between colon and value
	    if ($value[0] == ' ')
 {
                $value = substr($value, 1);
	    }

	    // Decode header value acording to RFC 2047
	    if ($this->_decodeOnParse == true) {
		$value
 =& $this->_decodeString(&$value);
	    }
	    
	    // Add header to $return array
    	    if (isset($return[$name]) AND is_array($return[$name])) {
		// The header name has already been used at least two times.
            	$return[$name][] = $value;
            } elseif (isset($return[$name])) {
		// The header name has already been used one time -> change to nedted values.
            	$return[$name] = array($return[$name], $value);
            } else {
		// The header name has not used until now.
        	$return[$name] = $value;
            }
        }

        return $return;
    }

    // }}}
    // {{{ regenerateString()
    
    /**
     * Generate a string of RFC2822 style header lines from the 'header array' given in $array.
     * 
     * @param array 'headers array'
     *
     * @return string RFC822 style header lines (CRLF included).
     * @access public
     * @since 0.1
     */
    function regenerateString($array)
    {
	// ( Forward to parseArray() and then convert to string )
	return implode("\r\n", $this->regenerateArray(&$array));
    }

    // }}}
    // {{{ regenerateArray()

    /**
     * Generate an array of RFC2822 style header lines from the array given in $array.
     *
     * @param array 'headers array'
     *
     * @return array RFC822 style header lines (CRLF not included).
     * @access public
     * @since 0.1
     */
    function regenerateArray($array)
    {
	// Duplicate to prevent any modification af the original array, if user parses by reference
	$header = $array;

	// Encode header values acording to RFC 2047
        if ($this->_encodeOnParse == true) {
	    $header =& $this->encode(&$header);
	}

	// Init return variable
	$return = array();

	// Loop through headers
        foreach ($header as $name => $value) {
	    if (is_array($value)) {
    		foreach ($value as $sub_value) {
        	    $return[] = $name.': '.$sub_value;
		}
	    } else {
        	$return[] = $name.': '.$value;
	    }
        }

	// Fold headers
	if ($this->_foldOnParse == true) {
	    $return =& $this->foldArray(&$return);
	}

        return $return;
    }

    // }}}
    // {{{ unfoldString()

    /**
     * Do the (RFC822 3.1.1) header unfolding to a string of RFC2822 header lines.
     * 
     * @param string $string RFC2822 header lines to unfolded (CRLF included)
     *
     * @return string Unfolded RFC2822 header lines (CRLF included)
     * @access public
     * @since 0.1
     */
    function unfoldString($string)
    {
	// Correct \r to \r\n
    	$string = preg_replace("/\r?\n/", "\r\n", $string);

	// Unfold multiline headers
    	$string = preg_replace("/\r\n(\t| )+/", ' ', $string);

	return $string;
    }

    // }}}
    // {{{ unfoldArray()

    /**
     * Do the (RFC822 3.1.1) header unfolding to an array of RFC2822 header lines.
     *
     * @param array $array RFC2822 header lines to unfolded (CRLF not included)
     *
     * @return array Unfolded RFC2822 header lines (CRLF not included)
     * @access public
     * @since 0.1
     */
    function unfoldArray($array)
    {
	// Unfold multiline headers
	for ($i = count($array)-1; $i>0; $i--) {

	    // Check for leading whitespace
	    if (preg_match('/^(\x09|\x20)/', $array[$i])) {
		    
	        // Remove folding \r\n
    	        if (substr($array[$i-1], -2) == "\r\n") {
	            $array[$i-1] = substr($array[$i-1], 0, -2);
    	        }
			
	        // Append folded line to prev line
	        $array[$i-1] = $array[$i-1].' '.ltrim($array[$i], " \t");
			
	        // Remove folded line
		array_splice($array, $i, 1);
	    }
	}

	return $array;
    }

    // }}}
    // {{{ foldArray()

    /**
     *
 !!! DONT USE, function not properly implemented !!!
     *
     * @param array $array
     *
     * @return array 
     * @access public
     * @since 0.1
     */
    function foldArray($array = null)
    {
	return $array;	

	if ($array == null) {
	    $header = $this->header;
	} else {
	    $header = $array;
	}
	
        foreach (array_keys($header) as $name) {

	    if (!is_array($header[$name])) {
        	$header[$name] = $this->_foldString($header[$name], $name);
	    } else { // It's an array
		foreach (array_keys($header[$name]) as $number) {
    			$header[$name][$number] = $this->_foldString($header[$name][$number], $name);
		}
	    }
        }
	
	return $header;	
    }

    // }}}
    // {{{ foldString()

    /**
     *
 !!! DONT USE, function not properly implemented !!!
     *
     * DUMMY / NOT IMPLEMENTED
     */
    function _foldString($string)
    {
	//TODO: fold to prefered 78-whitespace chars & max 998 chars
	return $string;
    }

    // }}}
    // {{{ decodeArray()

    /**
     * Decodes the values in a 'header array' as per RFC2047
     * 
     * @param array $array The 'header array' to encode
, if null use objects internal
     *
     * @return array Encoded version of $array
     * @access public
     * @since 0.1
     */
    function decode($array = null)
    {
	if ($array == null) {
	    $array =& $this->header;
	}

        foreach ($array as $hdr_name => $hdr_value) {

	    if (!is_array($hdr_value)) {
        	$array[$hdr_name] = $this->_decodeString($hdr_value);
	    } else { // Array
    		foreach ($hdr_value as $hdr2_name => $hdr2_value) {
        	    $array[$hdr_name][$hdr2_name] = $this->_decodeString($hdr2_value);
		}
	    }

        }
        
        return $array;
    }

    // }}}
    // {{{ _decodeString()

    /**
     * Given a header/string, this function will decode it
 according to RFC2047.
     * Probably not *exactly*
 conformant, but it does pass all the given
     * examples (in RFC2047).
     * 
     * @param string $input Input header value to decode
     *
     * @return string Decoded header value
     * @access private
     * @since 0.1
     */
    function _decodeString($input)
    {
        // Remove white space between encoded-words
        $input = preg_replace('/(=\?[^?]+\?(q|b)\?[^?]*\?=)(\s)+=\?/i', '\1=?', $input);

        // For each encoded-word...
        while (preg_match('/(=\?([^?]+)\?(q|b)\?([^?]*)\?=)/i', $input, $matches)) {

            $encoded  = $matches[1];
            $charset  = $matches[2];
            $encoding = $matches[3];
            $text     = $matches[4];

            switch (strtolower($encoding)) {
                case 'b':
 // RFC2047 4.1
                    $text = base64_decode($text);
                    break;

                case 'q': // RFC2047 4.2
                    $text = str_replace('_', ' ', $text);
                    preg_match_all('/=([a-f0-9]{2})/i', $text, $matches);
                    foreach($matches[1] as $value)
                        $text = str_replace('='.$value, chr(hexdec($value)), $text);
                    break;
            }

            $input = str_replace($encoded, $text, $header);
        }

        return $input;
    }

    // }}}
    // {{{ encode()

    /**
     * Encodes the values in a 'header array' as per RFC2047
     *
     * @param array $array The 'header array' to decode
, if null use objects internal
     *
     * @return array Decoded version of $array
     * @access public
     * @since 0.1
     */
    function encode($array = null)
    {
	if ($array == null) {
	    $array =& $this->header;
	}

        foreach ($array as $hdr_name => $hdr_value) {

	    if (!is_array($hdr_value)) {
        	$array[$hdr_name] = $this->_encodeString($hdr_value);
	    } else { // Array
    		foreach ($hdr_value as $hdr2_name => $hdr2_value) {
        	    $array[$hdr_name][$hdr2_name] = $this->_encodeString($hdr2_value);
		}
	    }

        }
        
        return $array;
    }

    // }}}
    // {{{ _encodeString()

    /**
     * Encodes the string given in $string as per RFC2047
     * 
     * @param string $string The string to encode
     *
     * @return string Encoded string
     * @access private
     * @since 0.1
     */
    function _encodeString($string)
    {
	// TODO: could be better! (Look into CPAN's Encode::MIME::Header)
	
	$charset = 'iso-8859-1';
	
        preg_match_all('/(\w*[\x80-\xFF]+\w*)/', $string, $matches);
	foreach ($matches[1] as $value) {
            $replacement = preg_replace('/([\x80-\xFF])/e', '"=" . strtoupper(dechex(ord("\1")))', $value);
            $string = str_replace($value, '=?' . $charset . '?Q?' . $replacement . '?=', $string);
        }
            
        return $string;
    }

    // }}}
    // {{{ cleanString()

    /**
     * Removes CRLF and misplaced empty lines before and after actual headerlines.
     * 
     * @param string $string.
     *
     * @return string 
     * @access public
     * @since 0.1
     */
    function cleanString($string)
    {
	// Correct missing CR's before LF's
        $string = &preg_replace("!\r?\n!", "\r\n", &$string);

	// Remove empty lines from start and end.
	// TODO: This should be done better...
	$string =& trim(&$string, "\r\n");

        return $string
;
    }

    // }}}
    // {{{ cleanArray()

    /**
     * Removes CRLF and misplaced empty lines before and after actual headerlines.
     * 
     * @param array $input 
     *
     * @return array
     * @access public
     * @since 0.1
     */
    function cleanArray($input)
    {
	// Remove empty lines from the start
	while (reset($input) == "\r\n") {
	    array_shift($input);
	}
	// Remove empty lines from the end
	while (end($input) == "\r\n") {
	    array_pop($input);
	}

	// Run backwards through all lines
	for ($i = count($input)-1; $i > 0; $i--) {

	    // Remove \r\n from the end
	    $input = preg_replace("/\r?\n$/", '', $input);
	}

        return $input;
    }

    // }}}

}

?>
