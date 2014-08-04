<?php
 /**
  * Framework Core
  *
  * @package Framework\Core
  * @author Daniel Farrelly <daniel@jellystyle.com>
  * @copyright 2014 Daniel Farrelly <daniel@jellystyle.com>
  * @license FreeBSD
  */

 /**
  * Convert a string of words (like a sentence) into title case.
  * This differs from ucwords because certain words will remain lowercase.
  *
  * For example: "This is a STRING" becomes "this_is_a_string", or "camelCaseString" becomes "camel_case_string".
  *
  * @param string $string The string to convert to title case.
  * @return string The title case string.
  */

	function title_case( $string ) {
		$string = strtolower( $string );
		$string = ucwords( $string );
		$regex = '/\b(a|an|the|at|by|for|in|of|on|to|up|and|as|but|is|or|nor)\b/i';
		$string = preg_replace_callback( $regex, create_function( '$matches', 'return strtolower($matches[0]);' ), $string );
		$string = ucfirst( $string );
		$regex = '/\bi(cloud|pad|phone|pod|device|mac)\b/i';
		$string = preg_replace_callback( $regex, create_function( '$matches', 'return "i".ucfirst(strtolower($matches[1]));' ), $string );
		return $string;
	}

 /**
  * Convert a string to an camelcase string, i.e. 'camelCaseString'.
  *
  * @param array $string The string to convert.
  * @return string The converted string.
  */

	function camel_case( $string ) {
		// Convert non-alphanumeric characters
		$string = preg_replace( '/([^\w\d]+)/', ' ', $string );
		// Trim the word
		$string = trim( $string );
		// Convert the case
		$string = strtolower( $string );
		$string = ucwords( $string );
		$string = lcfirst( $string );
		// Convert the spaces to the given character
		$string = preg_replace( '/(\s+)/', $character, $string );
		// Return
		return $string;
	}

 /**
  * Convert a string to an lowercase, underscore seperated string. Words are considered to be strings of alphanumaric
  * characters, divided either by spaces and symbols, or by capitalisation (for camel case strings).
  *
  * For example: "This is a STRING" becomes "this_is_a_string", or "camelCaseString" becomes "camel_case_string".
  *
  * @param string $string The string to convert.
  * @param string $character The character to use as a word seperator. Defaults to underscore (_).
  * @return string The converted string.
  */

	function underscore( $string, $character='_' ) {
		// Seperate camelcase
		$string = preg_replace( '/([A-Z0-9]+)/', ' $0', $string );
		// Convert non-alphanumeric characters
		$string = preg_replace( '/([^A-Z0-9]+)/i', ' ', $string );
		// Trim the string
		$string = trim( $string );
		// Convert the spaces to the given character
		$string = preg_replace( '/(\s+)/', $character, $string );
		// Convert the case
		$string = strtolower( $string );
		// Return
		return $string;
	}

 /**
  * Remove unwanted slashes (and whitespace!) from the start and end of the given string.
  * Removes both forward and back slashes.
  *
  * @param array $string The string to trim slashes from.
  * @return string The altered string.
  */

	function trim_slashes( $string ) {
		return trim( $string, "\/\\ \t\n\r\0\x0B" );
	}

 /**
  * Remove unwanted slashes from a string.
  *
  * @param array $string The string to remove slashes from.
  * @return string The altered string.
  */

	function remove_slashes( $string ) {
		return preg_replace( "/(\\\\|&#92;){2,}([\'\"\\\\])/", '$2', $string );
	}

 /**
  * Combine an array of strings into a single string with comma seperation and an alternative final token.
  *
  * @param array $pieces An array of strings.
  * @param string $glue The main string to place between the array strings. Defaults to a comma (,).
  * @param string $final_glue An alternate string to place between the final two array strings. Defaults to 'and'.
  * @return string The combined string.
  */

	function comma_and( $pieces, $glue=', ', $final_glue=' and ' ) {
		$token = '__AND__';
		// Combine the names into a string
		$string = implode( $token, $pieces );
		// If we have more than one host, format
		if( ( $pos = str_rpos( $string, $token ) ) !== false ) {
			$string = substr_replace( $string, $final_glue, $pos, strlen( $token ) );
			$string = str_replace( $token, $glue, $string);
		}
		// Return
		return $string;
	}

 /**
  * Indent a multiline string by prefixing each line with a given string.
  * @param $string string The string to indent.
  * @param $indentString string The string to prefix each line with.
  * @return The indented string.
  */

	function string_indent( $string, $indentString="\t" ) {
		return $indentString.str_replace( "\n", "\n".$indentString, $string );
	}

 /**
  * Get the given numer of paragraphs of a string.
  *
  * @param string $string The string to truncate.
  * @param int $paragraphs The number of paragraphs to reduce to.
  * @return string The string of paragraphs.
  */

	function paragraphs( $string, $paragraphs=1 ) {
		// Invalid Arguments
		if( ! is_int( $paragraphs ) || $paragraphs <= 0 ) {
			throw new \InvalidArgumentException;
		}
		// Prepare for launch!
		$string = trim( $string );
		// Find the end of the desired paragraph
		$limit = 0;
		for( $i=1; $i<=$paragraphs; $i++ ) {
			// Get the position
			$pos = str_pos( $string, array( "\n\n", "\r\r", "\r\n\r\n", "\0\0" ), ( $i >= 2 ) ? $limit+2 : $limit );
			// Update the limit
			if( $pos !== false )
				$limit = $pos;
			elseif( $i >= 2 )
				$limit = 0;
		}
		// Trim to a single paragraph
		if( $limit != 0 ) {
			$string = str_truncate( $string, $limit, '', false, false );
		}
		// Return the string
		return $string;
	}


 /**
  * Truncate a string
  *
  * @param string $string The string to truncate.
  * @param int $limit The number of characters to allow in the string.
  * @param bool $words If the truncation should occur on word boundaries. Setting this as true will make the method truncate on the closest word boundary.
  * @return string The truncated string.
  */

	function str_truncate( $string, $limit, $words=true ) {
		// If we're using word boundaries, we find the nearest boundary
		if( $word_boundaries == true ) {
			// If we can find a space character
			$break_on = array( " ", "\t", "\n", "\r", "\0", "\x0B", );
			if( ( $pos = str_rpos( $string, $break_on, $limit ) ) !== false ) {
				$limit = $pos;
			}
		}
		// Now truncate
		return substr( $string, 0, $limit );
	}

 /**
  * Encode the non-standard character entities within a string
  *
  * @return string The encoded string.
  */

	function encode_entities( $string, $use_char_codes=false, $reencode=false ) {
		// Encode all unencoded entities
		$string = htmlentities( $string, ENT_QUOTES, 'UTF-8', false );
		// If we're using numerical character codes
		if( $use_char_codes == true ) {
			// Prepare a table of entities
			static $_entities = array(
				'&#039;' => '&#39;', '&AElig;' => '&#198;', '&Aacute;' => '&#193;', '&Acirc;' => '&#194;', '&Agrave;' => '&#192;',
				'&Alpha;' => '&#913;', '&Aring;' => '&#197;', '&Atilde;' => '&#195;', '&Auml;' => '&#196;', '&Beta;' => '&#914;',
				'&Ccedil;' => '&#199;', '&Chi;' => '&#935;', '&Dagger;' => '&#8225;', '&Delta;' => '&#916;', '&ETH;' => '&#208;',
				'&Eacute;' => '&#201;', '&Ecirc;' => '&#202;', '&Egrave;' => '&#200;', '&Epsilon;' => '&#917;', '&Eta;' => '&#919;',
				'&Euml;' => '&#203;', '&Gamma;' => '&#915;', '&Iacute;' => '&#205;', '&Icirc;' => '&#206;', '&Igrave;' => '&#204;',
				'&Iota;' => '&#921;', '&Iuml;' => '&#207;', '&Kappa;' => '&#922;', '&Lambda;' => '&#923;', '&Mu;' => '&#924;',
				'&Ntilde;' => '&#209;', '&Nu;' => '&#925;', '&OElig;' => '&#338;', '&Oacute;' => '&#211;', '&Ocirc;' => '&#212;',
				'&Ograve;' => '&#210;', '&Omega;' => '&#937;', '&Omicron;' => '&#927;', '&Oslash;' => '&#216;', '&Otilde;' => '&#213;',
				'&Ouml;' => '&#214;', '&Phi;' => '&#934;', '&Pi;' => '&#928;', '&Prime;' => '&#8243;', '&Psi;' => '&#936;',
				'&Rho;' => '&#929;', '&Scaron;' => '&#352;', '&Sigma;' => '&#931;', '&THORN;' => '&#222;', '&Tau;' => '&#932;',
				'&Theta;' => '&#920;', '&Uacute;' => '&#218;', '&Ucirc;' => '&#219;', '&Ugrave;' => '&#217;', '&Upsilon;' => '&#933;',
				'&Uuml;' => '&#220;', '&Xi;' => '&#926;', '&Yacute;' => '&#221;', '&Yuml;' => '&#376;', '&Zeta;' => '&#918;',
				'&aacute;' => '&#225;', '&acirc;' => '&#226;', '&acute;' => '&#180;', '&aelig;' => '&#230;', '&agrave;' => '&#224;',
				'&alefsym;' => '&#8501;', '&alpha;' => '&#945;', '&amp;' => '&#38;', '&and;' => '&#8743;', '&ang;' => '&#8736;',
				'&apos;' => '&#39;', '&aring;' => '&#229;', '&asymp;' => '&#8776;', '&atilde;' => '&#227;', '&auml;' => '&#228;',
				'&bdquo;' => '&#8222;', '&beta;' => '&#946;', '&brvbar;' => '&#166;', '&bull;' => '&#8226;', '&cap;' => '&#8745;',
				'&ccedil;' => '&#231;', '&cedil;' => '&#184;', '&cent;' => '&#162;', '&chi;' => '&#967;', '&circ;' => '&#710;',
				'&clubs;' => '&#9827;', '&cong;' => '&#8773;', '&copy;' => '&#169;', '&crarr;' => '&#8629;', '&cup;' => '&#8746;',
				'&curren;' => '&#164;', '&dArr;' => '&#8659;', '&dagger;' => '&#8224;', '&darr;' => '&#8595;', '&deg;' => '&#176;',
				'&delta;' => '&#948;', '&diams;' => '&#9830;', '&divide;' => '&#247;', '&eacute;' => '&#233;', '&ecirc;' => '&#234;',
				'&egrave;' => '&#232;', '&empty;' => '&#8709;', '&emsp;' => '&#8195;', '&ensp;' => '&#8194;', '&epsilon;' => '&#949;',
				'&equiv;' => '&#8801;', '&eta;' => '&#951;', '&eth;' => '&#240;', '&euml;' => '&#235;', '&euro;' => '&#8364;',
				'&exist;' => '&#8707;', '&fnof;' => '&#402;', '&forall;' => '&#8704;', '&frac12;' => '&#189;', '&frac14;' => '&#188;',
				'&frac34;' => '&#190;', '&frasl;' => '&#8260;', '&gamma;' => '&#947;', '&ge;' => '&#8805;', '&gt;' => '&#62;',
				'&hArr;' => '&#8660;', '&harr;' => '&#8596;', '&hearts;' => '&#9829;', '&hellip;' => '&#8230;', '&iacute;' => '&#237;',
				'&icirc;' => '&#238;', '&iexcl;' => '&#161;', '&igrave;' => '&#236;', '&image;' => '&#8465;', '&infin;' => '&#8734;',
				'&int;' => '&#8747;', '&iota;' => '&#953;', '&iquest;' => '&#191;', '&isin;' => '&#8712;', '&iuml;' => '&#239;',
				'&kappa;' => '&#954;', '&lArr;' => '&#8656;', '&lambda;' => '&#955;', '&lang;' => '&#9001;', '&laquo;' => '&#171;',
				'&larr;' => '&#8592;', '&lceil;' => '&#8968;', '&ldquo;' => '&#8220;', '&le;' => '&#8804;', '&lfloor;' => '&#8970;',
				'&lowast;' => '&#8727;', '&loz;' => '&#9674;', '&lrm;' => '&#8206;', '&lsaquo;' => '&#8249;', '&lsquo;' => '&#8216;',
				'&lt;' => '&#60;', '&macr;' => '&#175;', '&mdash;' => '&#8212;', '&micro;' => '&#181;', '&middot;' => '&#183;',
				'&minus;' => '&#8722;', '&mu;' => '&#956;', '&nabla;' => '&#8711;', '&nbsp;' => '&#160;', '&ndash;' => '&#8211;',
				'&ne;' => '&#8800;', '&ni;' => '&#8715;', '&not;' => '&#172;', '&notin;' => '&#8713;', '&nsub;' => '&#8836;',
				'&ntilde;' => '&#241;', '&nu;' => '&#957;', '&oacute;' => '&#243;', '&ocirc;' => '&#244;', '&oelig;' => '&#339;',
				'&ograve;' => '&#242;', '&oline;' => '&#8254;', '&omega;' => '&#969;', '&omicron;' => '&#959;', '&oplus;' => '&#8853;',
				'&or;' => '&#8744;', '&ordf;' => '&#170;', '&ordm;' => '&#186;', '&oslash;' => '&#248;', '&otilde;' => '&#245;',
				'&otimes;' => '&#8855;', '&ouml;' => '&#246;', '&para;' => '&#182;', '&part;' => '&#8706;', '&permil;' => '&#8240;',
				'&perp;' => '&#8869;', '&phi;' => '&#966;', '&pi;' => '&#960;', '&piv;' => '&#982;', '&plusmn;' => '&#177;',
				'&pound;' => '&#163;', '&prime;' => '&#8242;', '&prod;' => '&#8719;', '&prop;' => '&#8733;', '&psi;' => '&#968;',
				'&quot;' => '&#34;', '&rArr;' => '&#8658;', '&radic;' => '&#8730;', '&rang;' => '&#9002;', '&raquo;' => '&#187;',
				'&rarr;' => '&#8594;', '&rceil;' => '&#8969;', '&rdquo;' => '&#8221;', '&real;' => '&#8476;', '&reg;' => '&#174;',
				'&rfloor;' => '&#8971;', '&rho;' => '&#961;', '&rlm;' => '&#8207;', '&rsaquo;' => '&#8250;', '&rsquo;' => '&#8217;',
				'&sbquo;' => '&#8218;', '&scaron;' => '&#353;', '&sdot;' => '&#8901;', '&sect;' => '&#167;', '&shy;' => '&#173;',
				'&sigma;' => '&#963;', '&sigmaf;' => '&#962;', '&sim;' => '&#8764;', '&spades;' => '&#9824;', '&sub;' => '&#8834;',
				'&sube;' => '&#8838;', '&sum;' => '&#8721;', '&sup1;' => '&#185;', '&sup2;' => '&#178;', '&sup3;' => '&#179;',
				'&sup;' => '&#8835;', '&supe;' => '&#8839;', '&szlig;' => '&#223;', '&tau;' => '&#964;', '&there4;' => '&#8756;',
				'&theta;' => '&#952;', '&thetasym;' => '&#977;', '&thinsp;' => '&#8201;', '&thorn;' => '&#254;', '&tilde;' => '&#732;',
				'&times;' => '&#215;', '&trade;' => '&#8482;', '&uArr;' => '&#8657;', '&uacute;' => '&#250;', '&uarr;' => '&#8593;',
				'&ucirc;' => '&#251;', '&ugrave;' => '&#249;', '&uml;' => '&#168;', '&upsih;' => '&#978;', '&upsilon;' => '&#965;',
				'&uuml;' => '&#252;', '&weierp;' => '&#8472;', '&xi;' => '&#958;', '&yacute;' => '&#253;', '&yen;' => '&#165;',
				'&yuml;' => '&#255;', '&zeta;' => '&#950;', '&zwj;' => '&#8205;', '&zwnj;' => '&#8204;'
			);
			// Do a straight replace
			$html_entities = array_keys( $_entities );
			$xml_entities = array_values( $_entities );
			$string = str_replace( $html_entities, $xml_entities, $string );
		}
		// If we're reencoding entities
		if( $reencode == true ) {
			$string = htmlentities( $string, ENT_QUOTES, 'UTF-8' );
		}
		// Return the string
		return $string;
	}

 /**
  * Find the first position of an array of substrings within a given string.
  *
  * @param string $string The string to find the position of the substrings in.
  * @param array|string $substrings The substrings to find the position of.
  * @param array $offset Start the search this number of characters counted from the beginning of the string.
  * @return int The position of the last possible match, false if no matches were found.
  */

	function str_pos( $string, $substrings, $offset=0 ) {
		// We only accept arrays or strings
		if( is_string( $substrings ) ) {
			return strpos( $string, $substrings, $offset );
		}
		else if( ! is_array( $substrings ) ) {
			throw new \InvalidArgumentException;
		}
		// Go through the substrings and collect the positions
		$positions = array();
		foreach( $substrings as $substring ) {
			if( ( $pos = strpos( $string, $substring, $offset ) ) !== false ) {
				$positions[] = $pos;
			}
		}
		// Return the last possible position
		return count( $positions ) ? max( $positions ) : false;
	}

 /**
  * Find the last possible position of an array of substrings within a given string.
  *
  * @param string $string The string to find the position of the substrings in.
  * @param array|string $substrings The substrings to find the position of.
  * @param int $limit A mandatory maximum length to impose on the primary string.
  * @return int The position of the last possible match, false if no matches were found.
  */

	function str_rpos( $string, $substrings, $limit=null ) {
		// If a limit is set, we truncate the primary string
		if( $limit !== null && is_numeric( $limit ) && $limit < strlen( $string ) ) {
			$string = substr( $string, 0, $limit );
		}
		// We only accept arrays or strings
		if( is_string( $substrings ) ) {
			return strrpos( $string, $substrings );
		}
		else if( ! is_array( $substrings ) ) {
			throw new \InvalidArgumentException;
		}
		// Go through the substrings and collect the positions
		$positions = array();
		foreach( $substrings as $substring ) {
			if( ( $pos = strrpos( $string, $substring ) ) !== false ) {
				$positions[] = $pos;
			}
		}
		// Return the last possible position
		return count( $positions ) ? max( $positions ) : false;
	}

 /**
  * Gets the plural version of the given word
  *
  * @param string $word The word to pluralize.
  * @return string The plural version of the given word.
  */

	function plural_of( $word ) {
		$result = strval( $word );
		$uncountable = array( 'money', 'rice', 'series', 'fish', 'species', 'information', 'meta', 'equipment' );

		if( in_array( $result, $uncountable ) ) {
			return $result;
		}

		$plural_rules = array(
			'/^(ox)$/i' => '\1\2en', // ox
			'/([m|l])ouse$/i' => '\1ice', // mouse, louse
			'/(matr|vert|ind)ix|ex$/i' => '\1ices', // matrix, vertex, index
			'/(x|ch|ss|sh)$/i' => '\1es', // search, switch, fix, box, process, address
			'/([^aeiouy]|qu)y$/i' => '\1ies', // query, ability, agency
			'/(hive)$/i' => '\1s', // archive, hive
			'/(?:([^f])fe|([lr])f)$/i' => '\1\2ves', // half, safe, wife
			'/sis$/i' => 'ses', // basis, diagnosis
			'/([ti])um$/i' => '\1a', // datum, medium
			'/(p)erson$/i' => '\1eople', // person, salesperson
			'/(m)an$/i' => '\1en', // man, woman, spokesman
			'/(c)hild$/i' => '\1hildren', // child
			'/(buffal|tomat)o$/i' => '\1\2oes', // buffalo, tomato
			'/(bu|campu)s$/i' => '\1\2ses', // bus, campus
			'/(alias|status|virus)$/i' => '\1es', // alias
			'/(octop)us$/i' => '\1i', // octopus
			'/(ax|cris|test)is$/i' => '\1es', // axis, crisis
			'/s$/' => 's', // no change (compatibility)
			'/$/' => 's',
		);

		foreach( $plural_rules as $rule => $replacement ) {
			if( preg_match( $rule, $result ) ) {
				$result = preg_replace($rule, $replacement, $result);
				break;
			}
		}

		return $result;
	}

 /**
  * Gets the singular version of the given word
  *
  * @param string $word The word to singularize.
  * @return string The singular version of the given word.
  */

	function singular_of( $word ) {
		$result = strval( $word );
		$uncountable = array( 'money', 'rice', 'series', 'fish', 'species', 'information', 'meta', 'equipment' );

		if( in_array( $result, $uncountable ) ) {
			return $result;
		}

		$singular_rules = array(
			'/(matr)ices$/i' => '\1ix',
			'/(vert|ind)ices$/i' => '\1ex',
			'/^(ox)en/i' => '\1',
			'/(alias)es$/i' => '\1',
			'/([octop|vir])i$/i' => '\1us',
			'/(cris|ax|test)es$/i' => '\1is',
			'/(shoe)s$/i' => '\1',
			'/(o)es$/i' => '\1',
			'/(bus|campus)es$/i' => '\1',
			'/([m|l])ice$/i' => '\1ouse',
			'/(x|ch|ss|sh)es$/i' => '\1',
			'/(m)ovies$/i' => '\1\2ovie',
			'/(s)eries$/i' => '\1\2eries',
			'/([^aeiouy]|qu)ies$/i' => '\1y',
			'/([lr])ves$/i' => '\1f',
			'/(tive)s$/i' => '\1',
			'/(hive)s$/i' => '\1',
			'/([^f])ves$/i' => '\1fe',
			'/(^analy)ses$/i' => '\1sis',
			'/((a)naly|(b)a|(d)iagno|(p)arenthe|(p)rogno|(s)ynop|(t)he)ses$/i' => '\1\2sis',
			'/([ti])a$/i' => '\1um',
			'/(p)eople$/i' => '\1\2erson',
			'/(m)en$/i' => '\1an',
			'/(s)tatuses$/i' => '\1\2tatus',
			'/(c)hildren$/i' => '\1\2hild',
			'/(n)ews$/i' => '\1\2ews',
			'/([^us])s$/i' => '\1',
		);

		foreach( $singular_rules as $rule => $replacement ) {
			if( preg_match( $rule, $result ) ) {
				$result = preg_replace($rule, $replacement, $result);
				break;
			}
		}

		return $result;
	}
