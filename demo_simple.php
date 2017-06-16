<?php
/**
 * Demo of simple captcha class
 * 
 * @author nrekow
 * 
 */
require_once 'classes/spamprotect/captcha/simple/captcha.php';

$captcha = new SpamProtect\Simple\Captcha(rand(5, 10));

echo 'Numbers: ' . $captcha->numbers. '<br/>Image: ' . $captcha->image;
