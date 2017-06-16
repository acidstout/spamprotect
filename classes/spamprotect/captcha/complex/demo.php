<?php
/**
 * Demo of captcha class
 * 
 * @author nrekow
 * 
 */
require_once 'captcha.php';

$captcha = new SpamProtect\Complex\Captcha(rand(5, 10));

echo 'Numbers: ' . $captcha->getNumbers() . '<br/>Image: ' . $captcha->getImage();
