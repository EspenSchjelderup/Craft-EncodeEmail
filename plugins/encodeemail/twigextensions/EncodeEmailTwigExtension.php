<?php
namespace Craft;

use Twig_Extension;
use Twig_Filter_Method;

class EncodeEmailTwigExtension extends Twig_Extension {
	public function getName() {
		return 'Encode Email';
	}

	public function getFilters() {
		return array(
			'css_obfuscate' => new Twig_Filter_Method($this, 'css_obfuscate', array('is_safe' => array('html'))),
			'rot13_obfuscate' => new Twig_Filter_Method($this, 'rot13_obfuscate', array('is_safe' => array('html'))),
		);
	}

	/**
	 * Obfuscate emails using CSS codedirection
	 * 
	 * @param	string	Input ($string) to check for email adresses
	 * @return	mixed	Return a string with all emails obfuscated
	 */
	public function css_obfuscate($string) {
		$pattern1 = '#<a.+?href="mailto:(.*?)".+?</a>#';
		$string = preg_replace( $pattern1, "$1", $string ); // remove hyperlinks from emails that has a hyperlink
		$pattern2 = "/[A-Za-z0-9_-]+@[A-Za-z0-9_-]+\.([A-Za-z0-9_-][A-Za-z0-9_]+)/";
		preg_match_all( $pattern2, $string, $matches ); // finds all email adresses (now without hyperlinks)
		foreach( $matches[0] as $email ) {
			$email_to_replace = "~$email~";
			$string = preg_replace( $email_to_replace, $this->_obfuscateEmailCss($email), $string );
		}
		return $string;
	}

	/**
	 * Obfuscate emails using Rot13 with javascript inserts
	 * 
	 * @param	string	Input ($string) to check for email adresses
	 * @return	mixed	Return a string with all emails obfuscated
	 */
	public function rot13_obfuscate($string) {
		$pattern1 = '#<a.+?href="mailto:(.*?)".+?</a>#'; // search for all "hyperlinked" emails
		$pattern2 = '/[A-Za-z0-9_-]+@[A-Za-z0-9_-]+\.([A-Za-z0-9_-][A-Za-z0-9_]+)/'; // search for all "non-hyperlinked" emails
		preg_match_all( $pattern1, $string, $matches1 );
		preg_match_all( $pattern2, $string, $matches2 );
		foreach( $matches1[0] as $email ) {
			$email_to_replace = "~$email~";
			$string = preg_replace( $email_to_replace, $this->_obfuscateEmailRot13($email), $string ); // obfuscates all "non-hyperlinked" emails
		}
		foreach( $matches2[0] as $email ) {
			$email_to_replace = "~$email~";
			$string = preg_replace( $email_to_replace, $this->_obfuscateEmailRot13($email), $string ); // obfuscates all "hyperlinked" emails
		}
		return $string;
	}

	/**
	 * Returns an email string using CSS codedirection method
	 * http://techblog.tilllate.com/2008/07/20/ten-methods-to-obfuscate-e-mail-addresses-compared/
	 * 
	 * @param	string	$email to be obfuscated
	 * @return	mixed	Obfuscated email wrapped with text-direction-span
	 */
	private function _obfuscateEmailCss($email) {
		$obfuscated_email = '<span class="obf" style="unicode-bidi:bidi-override; direction: rtl;">'.strrev($email).'</span>'; 
		return $obfuscated_email;
	}

	/**
	 * Returns a rot13 encrypted string without javascript decoder
	 * http://snipplr.com/view/6037/
	 * 
	 * @param	string	$email be obfuscated
	 * @return	mixed	Obfuscated email and javascript decoder function
	 */
	private function _obfuscateEmailRot13($email) {
		$obfuscated_email = null;
		$length = strlen( $email );
		for( $i = 0; $i < $length; $i++ ) {
			$obfuscated_email .= str_replace( '"', '\"', str_rot13( $email[$i] ) );
			$return = '<script type="text/javascript">document.write("' . $obfuscated_email . '".replace(/[a-zA-Z]/g, function(c){return String.fromCharCode((c<="Z"?90:122)>=(c=c.charCodeAt(0)+13)?c:c-26);}));</script>';
		}
		return $return;
	} 
}
?>
