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
$captcha->setAngle(0);
$captcha->useRandomColors = true;
$captcha->useRandomRotation = true;
$captcha->addNoise = true;
$captcha->useRandomColorNoise = false;
$captcha->setNoiseModifier(50);
$captcha->createCaptcha();

echo 'Numbers: ' . $captcha->getNumbers() . '<br/>Image: ' . $captcha->getImage();
