<?php
/**
 * Captcha class
 *
 * Generates a graphical captcha out of random numbers and adds some noise.
 *
 * @author: nrekow
 *
 *
 * TODO: Add arcs, lines and noise relative to image size. 
 *
 */

namespace SpamProtect\Captcha;

interface CaptchaInterface {
	public function createCaptcha();
	public function getImage();
	public function getNumbers();
	public function getAngle();
	public function getPadding();
	public function getSize();
	public function setForegroundColor($r, $g, $b);
	public function setBackgroundColor($r, $g, $b);
	public function setAngle($angle);
	public function setFont($font);
	public function setPadding($padding);
	public function setSize($size);
	public function getArcsModifier();
	public function getLinesModifier();
	public function getNoiseModifier();
}

class Complex implements CaptchaInterface {
	public $useTransparency = true;
	public $useRandomColors = true;
	public $useRandomRotation = true;
	public $addArcs = true;
	public $addLines = true;
	public $addNoise = true;
	public $useRandomColorArcs = true;
	public $useRandomColorLines = true;
	public $useRandomColorNoise = true;
	
	private $_numbers = null;
	private $_image = null;
	private $_resource = null;
	private $_font = './OpenSans-Regular.ttf';
	private $_length = 10;
	private $_size = 24;
	private $_angle = 0;
	private $_padding = 2;
	private $_fg_color = array('R' => 0, 'G' => 0, 'B' => 0);
	private $_bg_color = array('R' => 255, 'G' => 255, 'B' => 255);
	private $_arcsModifier = 50;
	private $_linesModifier = 5;
	private $_noiseModifier = 1000;
	
	/**
	 * Constructor
	 * 
	 * Generates a string of defined length with random numbers and converts it into an image
	 * 
	 * @param integer $length
	 * 
	 */
	public function __construct($length) {
		$this->_length = $length;
		
		for ($i = 0; $i < $this->_length; $i++) {
			$num = rand(0, 9);
			$this->_numbers .= $num;
		}
		
		// Explicitly set a font.
		$this->setFont($this->_font);
	}
	

	/**
	 * Convert the generated string into an image.
	 */
	public function createCaptcha() {
		$this->_image = $this->_text2img($this->_numbers, $this->_length);
	}
	
	
	
	/////////////////////////
	// Getter methods
	
	public function getImage() {
		return $this->_image;
	}
	
	public function getNumbers() {
		return $this->_numbers;
	}
	
	public function getAngle() {
		return $this->_angle;
	}

	public function getPadding() {
		return $this->_padding;
	}
	
	public function getSize() {
		return $this->_size;
	}
	
	public function getArcsModifier() {
		return $this->_arcsModifier;
	}
	
	public function getLinesModifier() {
		return $this->_linesModifier;
	}
	
	public function getNoiseModifier() {
		return $this->_noiseModifier;
	} 
	
	
	
	/////////////////////////
	// Setter methods
	
	public function setForegroundColor($r, $g, $b) {
		$r = $this->_checkRange($r, 0, 255);
		$b = $this->_checkRange($b, 0, 255);
		$g = $this->_checkRange($g, 0, 255);
		
		$this->_fg_color = array('R' => $r, 'G' => $g, 'B' => $b);
	}
	
	
	public function setBackgroundColor($r, $g, $b) {
		$r = $this->_checkRange($r, 0, 255);
		$b = $this->_checkRange($b, 0, 255);
		$g = $this->_checkRange($g, 0, 255);
		
		$this->_bg_color = array('R' => $r, 'G' => $g, 'B' => $b);
	}
	
	
	public function setAngle($angle) {
		($angle >= 0 && $angle <= 360) ? $this->_angle = $angle : $this->_angle = 0;
	}
	
	
	public function setFont($font) {
		if (!is_file($font)) {
			// If the configured font does not exist locally, try to fetch an external fallback font.
			$fallback_font = 'https://github.com/edx/edx-certificates/raw/master/template_data/fonts/OpenSans-Regular.ttf';
			
			// If fetching the external fallback font fails, throw error and cancel further processing.
			if (!file_put_contents($font, file_get_contents($fallback_font))) {
				trigger_error('Configured font (' . $font. ') is missing and fallback font (' . $fallback_font . ') could not be fetched.', E_USER_ERROR);
				die();
			}
		}
		
		$this->_font = $font;
	}
	
	
	public function setPadding($padding) {
		$this->_padding = abs($padding);
	}
	
	
	public function setSize($size) {
		$this->_size = abs($size);
	}
	
	public function setArcsModifier($arcs) {
		$this->_arcsModifier = abs($arcs);
	}
	
	public function setLinesModifier($lines) {
		$this->_linesModifier = abs($lines);
	}
	
	public function setNoiseModifier($noise) {
		$this->_noiseModifier = abs($noise);
	}
	
	/////////////////////////
	// Private functions
	
	/**
	 * Check if a number is in the defined range.
	 * If number is not in range, return 0.
	 * 
	 * @param integer $x
	 * @param integer $a
	 * @param integer $b
	 * @return integer
	 */
	private function _checkRange($x, $a, $b) {
		return ($x >= $a && $x <= $b) ? $x : 0;
	}
	
	
	/**
	 * Define two ranges of angles and select one random angle of each.
	 * @return number
	 */
	private function _getRandomAngle() {
		$rotate1 = rand(0, 60);
		$rotate2 = rand(300, 360);
		
		// Choose which random angle to use.
		if (rand(0, 1)) {
			$rotate = $rotate1;
		} else {
			$rotate = $rotate2;
		}
		
		return $rotate;
	}
	

	/**
	 * Set color which is used as transparency
	 *  
	 * @param array $color
	 */
	private function _setTransparency($color) {
		// Define a color as transparent
		imagecolortransparent($this->_resource, $color);
		
		// Enable alpha-blending and save that information
		imagealphablending($this->_resource, true);
		imagesavealpha($this->_resource, true);
	}
	

	/**
	 * Sets random color and returns color identifier
	 * 
	 * @param array $colorIdentifier
	 * @return integer
	 */
	private function _setRandomColor() {
		// Generate random color code.
		$color = array('R' => rand(0, 255), 'G' => rand(0, 255), 'B' => rand(0, 255));
		
		// Set generated color code.
		return imagecolorallocate($this->_resource, $color['R'], $color['G'], $color['B']);
	}
	

	/**
	 * Adds random arcs to the image
	 */
	private function _addArcs() {
		// Set colors. Use default as defined above.
		$background = imagecolorallocate($this->_resource, $this->_bg_color['R'], $this->_bg_color['G'], $this->_bg_color['B']);
		$foreground = imagecolorallocate($this->_resource, $this->_fg_color['R'], $this->_fg_color['G'], $this->_fg_color['B']);
		
		// The higher the noise modifier, the more noise is added to the image.
		for ($i = 0; $i < abs($this->_arcsModifier); $i++) {
			// Set random foreground color.
			if ($this->useRandomColorArcs) {
				$foreground = $this->_setRandomColor();
			}
			
			// Add filled rectangle at a random position.
			imagefilledrectangle($this->_resource, rand(0, 5) + $i, rand(0, 5) + $i, rand(0, 5) + $i, rand(0, 5) + $i, (rand(0, 1) ? $foreground: $background));
			
			// Set random line thickness.
			imagesetthickness($this->_resource, rand(1, 5));
			
			// Draw random arcs.
			imagearc(
					$this->_resource,
					rand(1, 300), // x-coordinate of the center.
					rand(1, 300), // y-coordinate of the center.
					rand(1, 300), // The arc width.
					rand(1, 300), // The arc height.
					rand(1, 300), // The arc start angle, in degrees.
					rand(1, 300), // The arc end angle, in degrees.
					(rand(0, 1) ? $foreground: $background) // A color identifier.
			);
		}
	}
	
	/**
	 * Add random lines to an image
	 * 
	 * @param integer $lines
	 */
	private function _addLines() {
		// Reset line thickness to one pixel.
		imagesetthickness($this->_resource, 1);
		
		// Reset foreground color to default. 
		$foreground = imagecolorallocate($this->_resource, $this->_fg_color['R'], $this->_fg_color['G'], $this->_fg_color['B']);
		
		// Add some randomly colored lines.
		for ($i = 0; $i < abs($this->_linesModifier); $i++) {
			// Set random foreground color if desired.
			if ($this->useRandomColorLines) {
				$foreground = $this->_setRandomColor();
			}
			
			imageline($this->_resource, 0, rand() % 50, 200, rand() % 50, $foreground);
		}
	}
	
	
	/**
	 * Adds some random noise to the image
	 */
	private function _addNoise() {
		// Reset foreground color to default.
		$foreground = imagecolorallocate($this->_resource, $this->_fg_color['R'], $this->_fg_color['G'], $this->_fg_color['B']);
		
		for ($i = 0; $i < abs($this->_noiseModifier); $i++) {
			// Set random foreground color if desired.
			if ($this->useRandomColorNoise) {
				$foreground = $this->_setRandomColor();
			}
			imagesetpixel($this->_resource, rand() % 200, rand() % 50, $foreground);
		}
	}
	
	
	/**
	 * Creates image from plain text, writes it into PHP's output buffer
	 * and returns an HTML image element with a Base64 encoded inline
	 * representation of the image.
	 * 
	 * For best rotation results use square angles (e.g. 0, 90, 180, 270).
	 * 
	 * @param string $text
	 * @param boolean $use_random_colors
	 * @param array $color
	 * @param array $bg_color
	 * @return string
	 */
	private function _text2img($text, $length) {
		$width = $height = $offset_x = $offset_y = 0;
		
		// Sets modifiers of horizontal offset between chars and height,
		// because rotated chars require a larger image, because the offset of rotation is not the center of the char.
		$modifier = 4;
		$height_modifier = $width_modifier = 0;
		if ($this->useRandomRotation) {
			$modifier = 10;
			$height_modifier = $modifier;
			$width_modifier = $length * 3;
		}
				
		// Rotation is always 0, because we first create an image and rotate it afterwards. That's much easier, then creating a rotated one.
		// Get the font height.
		$bounds = ImageTTFBBox($this->_size, 0, $this->_font, "W");
		$font_height = abs($bounds[7] - $bounds[1]);
		
		// Determine bounding box. Again, rotation is always 0.
		$bounds = ImageTTFBBox($this->_size, 0, $this->_font, $text);
		$width = abs($bounds[4] - $bounds[6]);
		$height = abs($bounds[7] - $bounds[1]);
		$offset_y = $font_height;
		$offset_x = 0;
		
		
		// Adjust padding if random rotation is used.
		if ($this->useRandomRotation) {
			$this->_padding = $this->_size* 1.1;
		}
		
		// Create image resource.
		$this->_resource = imagecreate($width + ($this->_padding* 2) + 1 + $width_modifier, $height + ($this->_padding) + 1 - $height_modifier);
		
		// Set image colors. The order is important. Background first, then foreground. Otherwise you'll get strange results.
		$background = imagecolorallocate($this->_resource, $this->_bg_color['R'], $this->_bg_color['G'], $this->_bg_color['B']);
		$foreground = imagecolorallocate($this->_resource, $this->_fg_color['R'], $this->_fg_color['G'], $this->_fg_color['B']);
		
		$this->_setTransparency($background);

		// Enable interlacing
		imageinterlace($this->_resource, true);

		
		foreach (str_split($text) as $ch) {
			if ($this->useRandomColors) {
				$foreground = $this->_setRandomColor();
			}
			
			if ($this->useRandomRotation) {
				// Get random rotation angle for each char.
				$rotate = $this->_getRandomAngle();
				
				// Adjust vertical offset when rotating in relation to angle.
				$offset_y = abs($rotate / ($rotate - 100));
			} else {
				// Default to 0 if no rotation is used.
				$rotate = 0;
			}
			
			// Add char to image
			imagettftext($this->_resource, $this->_size, $rotate, $offset_x + $this->_padding, $offset_y + $this->_padding, $foreground, $this->_font, $ch);
			
			// Increase horizontal offset, so the next char won't overwrite the previous one.
			$offset_x += round($this->_size- ($this->_size/ $modifier));
		}
		
		// Init PHP's output buffer ...
		ob_start();
		
		// Add some random arcs
		if ($this->addArcs) {
			$this->_addArcs();
		}

		// Add some random lines
		if ($this->addLines) {
			$this->_addLines();
		}

		// Add some noise to the image if desired.
		if ($this->addNoise) {
			$this->_addNoise();
		}

		// ... and finally rotate the generated image
		if ($this->_angle != 0) {
			$this->_resource= imagerotate($this->_resource, $this->_angle, $background);
			
			if ($this->useTransparency) {
				$this->_setTransparency($background);
			}
		}
		
		// Write image to output buffer ...
		imagepng($this->_resource);
		// ... and destroy the image resource afterwards.
		imagedestroy($this->_resource);
		
		// Fetch the binary represantation of the image in the output buffer.
		$contents = ob_get_clean();

		// Generate HTML image tag using inline base64 encoded image data and return the result.
		return '<img src="data:image/png;base64,' . base64_encode($contents) . '"/>';
	}// END: _text2img()
}