<?php
/**
 * Captcha class
 *
 * Generates a graphical captcha out of random numbers.
 *
 * @author: nrekow
 *
 * Usage:
 *
 * 		$captcha = new SpamProtect\Complex\Captcha($length);
 *
 * 		// Numeric representation of captcha
 * 		echo $captcha->numbers
 *
 * 		// Image representation of captcha
 * 		echo $captcha->numbers
 *
 *
 */

namespace SpamProtect\Complex;

class Captcha {
	private $_numbers = null;
	private $_image = null;
	
	/**
	 * Constructor
	 * 
	 * Generates a string of defined length with random numbers and converts it into an image
	 * 
	 * @param integer $length
	 * 
	 */
	public function __construct($length) {
		for ($i = 0; $i < $length; $i++) {
			$num = rand(0, 9);
			$this->_numbers .= $num;
		}

		// Convert the generated string into an image.
		$this->_image = $this->_text2img($this->_numbers, $length);
	}
	
	public function getImage() {
		return $this->_image;
	}
	
	public function getNumbers() {
		return $this->_numbers;
	}
	
	/**
	 * Creates image from plain text, writes it into PHP's output buffer
	 * and returns an HTML image element with a Base64 encoded inline
	 * representation of the image.
	 * 
	 * For best rotation results use square angles (e.g. 0, 90, 180, 270).
	 * 
	 * @param string $text
	 * @param integer $separate_line_after_chars
	 * @param string $font
	 * @param integer $size
	 * @param integer $rotate
	 * @param integer $padding
	 * @param boolean $transparent
	 * @param boolean $use_random_colors
	 * @param array $color
	 * @param array $bg_color
	 * @return string
	 */
	private function _text2img(
			$text,
			$length,
			$font='./myfont.ttf',
			$size = 24,
			$angle = 0,
			$padding = 2,
			$transparent = true,
			$use_random_colors = true,
			$use_random_rotation = true,
			$fg_color = array('R' => 0, 'G' => 0, 'B' => 0),
			$bg_color = array('R' => 255, 'G' => 255, 'B' => 255)
	) {
		$width = $height = $offset_x = $offset_y = 0;
		
		// Sets modifiers of horizontal offset between chars and height,
		// because rotated chars require a larger image, because the offset of rotation is not the center of the char.
		$modifier = 4;
		$height_modifier = 0;
		$width_modifier = 0;
		if ($use_random_rotation) {
			$modifier = 10;
			$height_modifier = $modifier;
			$width_modifier = $length * 3;
		}
		
		if (!is_file($font)) {
			// If the configured font does not exist locally, try to fetch an external fallback font.
			$fallback_font = 'https://github.com/edx/edx-certificates/raw/master/template_data/fonts/OpenSans-Regular.ttf';
			
			// If fetching the external fallback font fails, throw error and cancel further processing.
			if (!file_put_contents($font, file_get_contents($fallback_font))) {
				trigger_error('Configured font (' . $font . ') is missing and fallback font (' . $fallback_font . ') could not be fetched.', E_USER_ERROR);
				die();
			}
		}
		
		// Rotation is always 0, because we first create an image and rotate it afterwards. That's much easier, then creating a rotated one.
		// Get the font height.
		$bounds = ImageTTFBBox($size, 0, $font, "W");
		$font_height = abs($bounds[7] - $bounds[1]);
		
		// Determine bounding box. Again, rotation is always 0.
		$bounds = ImageTTFBBox($size, 0, $font, $text);
		$width = abs($bounds[4] - $bounds[6]);
		$height = abs($bounds[7] - $bounds[1]);
		$offset_y = $font_height;
		$offset_x = 0;
		
		
		// Adjust padding if random rotation is used.
		if ($use_random_rotation) {
			$padding = $size * 1.1;
		}
		
		// Create image resource.
		$image = imagecreate($width + ($padding * 2) + 1 + $width_modifier, $height + ($padding) + 1 - $height_modifier);
		
		// Set image colors. The order is important. Background first, then foreground. Otherwise you'll get strange results.
		$background = imagecolorallocate($image, $bg_color['R'], $bg_color['G'], $bg_color['B']);
		$foreground = imagecolorallocate($image, $fg_color['R'], $fg_color['G'], $fg_color['B']);
		
		if ($transparent) {
			// Define a color as transparent
			imagecolortransparent($image, $background);
		
			// Enable alpha-blending and save that information
			imagealphablending($image, true);
			imagesavealpha($image, true);
		}
		
		// Enable interlacing
		imageinterlace($image, true);
		
		// Render the image
		if ($use_random_colors) {
			foreach (str_split($text) as $ch) {
				// Generate random color code.
				$fg_color['R'] = rand(0, 255);
				$fg_color['G'] = rand(0, 255);
				$fg_color['B'] = rand(0, 255);
				
				// Set generated color code
				$foreground = imagecolorallocate($image, $fg_color['R'], $fg_color['G'], $fg_color['B']);

				// Generate random rotation angle and set vertical offset.
				if ($use_random_rotation) {
					// Define two ranges of angles and select one random angle of each.
					$rotate1 = rand(0, 60);
					$rotate2 = rand(300, 360);
					
					// Choose which random angle to use.
					if (rand(0, 1)) {
						$rotate = $rotate1;
					} else {
						$rotate = $rotate2;
					}
					
					// Adjust vertical offset when rotating in relation to angle.
					$offset_y = abs($rotate / ($rotate - 100));
				} else {
					// Default to 0 if no rotation is used.
					$rotate = 0;
				}
				
				// Add char to image
				imagettftext($image, $size, $rotate, $offset_x + $padding, $offset_y + $padding, $foreground, $font, $ch);

				// Increase horizontal offset, so the next char won't overwrite the previous one.
				$offset_x += round($size - ($size / $modifier));
			}
		} else {
			// Simple generate a single color image represantation of the text.
			imagettftext($image, $size, $angle, $offset_x + $padding, $offset_y + $padding, $foreground, $font, $text);
		}	
		
		// Init PHP's output buffer ...
		ob_start();
		
		// Rotate generated image
		if ($angle != 0) {
			$rotated = imagerotate($image, $angle, $background);
			
			$background = imagecolorallocate($rotated, $bg_color['R'], $bg_color['G'], $bg_color['B']);
			
			if ($transparent) {
				// Define a color as transparent
				imagecolortransparent($rotated, $background);
				
				// Enable alpha-blending and save that information
				imagealphablending($rotated, true);
				imagesavealpha($rotated, true);
			}
			
			// Write rotated image to output buffer.
			imagepng($rotated);
			
			// Destroy rotated image resource.
			imagedestroy($rotated);
		} else {
			// Write normal image to output buffer.
			imagepng($image);
		}

		// ... and destroy the image resource afterwards.
		imagedestroy($image);
		
		// Fetch the binary represantation of the image in the output buffer.
		$contents = ob_get_clean();

		// Generate HTML image tag using inline base64 encoded image data and return the result.
		return '<img src="data:image/png;base64,' . base64_encode($contents) . '"/>';
	}// END: _text2img()
}