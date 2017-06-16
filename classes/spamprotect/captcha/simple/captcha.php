<?php
/**
 * Very simple captcha class
 * 
 * Generates a numeric captcha image out of existing single number images.
 * 
 * Usage:
 * 
 * 		$captcha = new SpamProtect\Simple\Captcha($length);
 * 		
 * 		// Numeric representation of captcha
 * 		echo $captcha->numbers
 * 
 * 		// Image representation of captcha
 * 		echo $captcha->numbers
 * 
 * @author: nrekow
 * 
 */

namespace SpamProtect\Simple;

class Captcha {
	public $numbers = '';
	public $image = '';
	
	public function __construct($length) {
		for ($i = 0; $i < $length; $i++) {
			$num = rand(0, 9);
			$this->numbers .= $num;
			$this->image .= str_replace($num, '<img src="img/' . $num . '.gif"/>', $num);
		}
	}
}
?>