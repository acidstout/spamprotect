<?php
/**
 * Very simple captcha class
 * 
 * Generates a numeric captcha image out of existing single number images.
 * 
 * @author: nrekow
 * 
 */

namespace SpamProtect\Captcha;

class Simple {
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