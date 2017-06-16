<?php
/**
 * Demo of captcha class
 * 
 * @author nrekow
 * 
 */
require_once 'classes/spamprotect/captcha/complex.php';

use SpamProtect\Captcha\Complex;

$captcha = new Complex(rand(5, 10));
$captcha->setSize(24);
$captcha->useRandomColors = false;
$captcha->useRandomRotation = false;
$captcha->createCaptcha();

echo 'Numbers: ' . $captcha->getNumbers() . '<br/>Image: ' . $captcha->getImage();
