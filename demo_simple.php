<?php
/**
 * Demo of simple captcha class
 * 
 * @author nrekow
 * 
 */
require_once 'classes/spamprotect/captcha/simple.php';

use SpamProtect\Captcha\Simple;

$captcha = new Simple(rand(5, 10));

echo 'Numbers: ' . $captcha->numbers . '<br/>Image: ' . $captcha->image;
