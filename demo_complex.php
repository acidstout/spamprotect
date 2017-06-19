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
$captcha->addArcs = true;
$captcha->addBlots = true;
$captcha->addLines = true;
$captcha->addNoise = false;
$captcha->useRandomColors = true;
$captcha->useRandomRotation = true;
$captcha->useRandomColorArcs = false;
$captcha->useRandomColorBlots = false;
$captcha->useRandomColorLines = false;
$captcha->setArcsModifier(10);
$captcha->setLinesModifier(5);
$captcha->createCaptcha();

echo 'Numbers: ' . $captcha->getNumbers() . '<br/>Image: ' . $captcha->getImage();
