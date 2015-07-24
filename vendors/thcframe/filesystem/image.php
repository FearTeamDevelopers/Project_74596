<?php

namespace THCFrame\Filesystem;

use THCFrame\Core\Base as Base;
use THCFrame\Filesystem\Exception as Exception;
use THCFrame\Core\StringMethods as StringMethods;

/**
 * 
 */
class Image extends Base
{

    /**
     * @readwrite
     */
    protected $_quality = 75;

    /**
     * @readwrite
     */
    protected $_image;

    /**
     * @readwrite
     */
    protected $_filename;
    
    /**
     * @readwrite
     */
    protected $_thumbname = '';

    /**
     * @readwrite
     */
    protected $_originalInfo;

    /**
     * @readwrite
     */
    protected $_width;

    /**
     * @readwrite
     */
    protected $_height;
    
    /**
     * @readwrite
     */
    protected $_size;
    
    /**
     * @readwrite
     */
    protected $_format;
    
    /**
     * @readwrite
     */
    protected $_imagestring;

    /**
     * Create instance and load an image, or create an image from scratch
     *
     * @param null|string	$filename	Path to image file (may be omitted to create image from scratch)
     * @param int		$width		Image width (is used for creating image from scratch)
     * @param int|null		$height		If omitted - assumed equal to $width (is used for creating image from scratch)
     * @param null|string	$color		Hex color string, array(red, green, blue) or array(red, green, blue, alpha).
     *                                              Where red, green, blue - integers 0-255, alpha - integer 0-127<br>
     *                                              (is used for creating image from scratch)
     *
     * @return Image
     */
    public function __construct($filename = null, $base64 = null, $width = null, $height = null, $color = null)
    {
        parent::__construct();

        if ($filename) {
            $this->load($filename);
        }elseif ($base64){
            $this->loadBase64($base64);
        } elseif ($width) {
            $this->create($width, $height, $color);
        }

        return $this;
    }

    /**
     * Destroy image resource
     */
    public function __destruct()
    {
        if ($this->_image) {
            imagedestroy($this->_image);
        }
    }

    /**
     * Get the current orientation
     *
     * @return string	portrait|landscape|square
     */
    public function getOrientation()
    {
        if (imagesx($this->_image) > imagesy($this->_image)) {
            return 'landscape';
        }

        if (imagesx($this->_image) < imagesy($this->_image)) {
            return 'portrait';
        }

        return 'square';
    }

    /**
     * Rotates and/or flips an image automatically so the orientation will be correct (based on exif 'Orientation')
     *
     * @return Image
     *
     */
    public function autoOrient()
    {
        $info = $this->getOriginalInfo();
        switch ($info['exif']['Orientation']) {
            case 1:
                // Do nothing
                break;
            case 2:
                // Flip horizontal
                $this->flip('x');
                break;
            case 3:
                // Rotate 180 counterclockwise
                $this->rotate(-180);
                break;
            case 4:
                // vertical flip
                $this->flip('y');
                break;
            case 5:
                // Rotate 90 clockwise and flip vertically
                $this->flip('y');
                $this->rotate(90);
                break;
            case 6:
                // Rotate 90 clockwise
                $this->rotate(90);
                break;
            case 7:
                // Rotate 90 clockwise and flip horizontally
                $this->flip('x');
                $this->rotate(90);
                break;
            case 8:
                // Rotate 90 counterclockwise
                $this->rotate(-90);
                break;
        }

        return $this;
    }

    /**
     * Best fit (proportionally resize to fit in specified width/height)
     * Shrink the image proportionally to fit inside a $width x $height box
     *
     * @param int	$max_width
     * @param int	$max_height
     *
     * @return	Image
     */
    public function bestFit($max_width, $max_height)
    {
        // If it already fits, there's nothing to do
        if ($this->_width <= $max_width && $this->_height <= $max_height) {
            return $this;
        }

        // Determine aspect ratio
        $aspect_ratio = $this->_height / $this->_width;

        // Make width fit into new dimensions
        if ($this->_width > $max_width) {
            $width = $max_width;
            $height = $width * $aspect_ratio;
        } else {
            $width = $this->_width;
            $height = $this->_height;
        }

        // Make height fit into new dimensions
        if ($height > $max_height) {
            $height = $max_height;
            $width = $height / $aspect_ratio;
        }

        return $this->resize($width, $height);
    }

    /**
     * Brightness
     *
     * @param int	$level	Darkest = -255, lightest = 255
     * @return Image
     */
    public function brightness($level)
    {
        imagefilter($this->_image, IMG_FILTER_BRIGHTNESS, $this->_inRange($level, -255, 255));
        return $this;
    }

    /**
     * Contrast
     *
     * @param int	$level	Min = -100, max = 100
     * @return Image
     */
    public function contrast($level)
    {
        imagefilter($this->_image, IMG_FILTER_CONTRAST, $this->_inRange($level, -100, 100));
        return $this;
    }

    /**
     * Colorize
     *
     * @param string		$color		Hex color string, array(red, green, blue) or array(red, green, blue, alpha).
     * 									Where red, green, blue - integers 0-255, alpha - integer 0-127
     * @param float|int		$opacity	0-1
     * @return Image
     */
    public function colorize($color, $opacity)
    {
        $rgba = $this->_normalizeColor($color);
        $alpha = $this->_inRange(127 - (127 * $opacity), 0, 127);
        imagefilter($this->_image, IMG_FILTER_COLORIZE, $this->_inRange($rgba['r'], 0, 255), $this->_inRange($rgba['g'], 0, 255), $this->_inRange($rgba['b'], 0, 255), $alpha);
        return $this;
    }

    /**
     * Blur
     *
     * @param string	$type	selective|gaussian
     * @param int	$passes	Number of times to apply the filter
     *
     * @return Image
     */
    public function blur($type = 'selective', $passes = 1)
    {
        switch (strtolower($type)) {
            case 'gaussian':
                $type = IMG_FILTER_GAUSSIAN_BLUR;
                break;
            default:
                $type = IMG_FILTER_SELECTIVE_BLUR;
                break;
        }
        for ($i = 0; $i < $passes; $i++) {
            imagefilter($this->_image, $type);
        }
        return $this;
    }

    /**
     * Desaturate (grayscale)
     *
     * @return Image
     */
    public function desaturate()
    {
        imagefilter($this->_image, IMG_FILTER_GRAYSCALE);
        return $this;
    }

    /**
     * Edge Detect
     *
     * @return Image
     */
    public function edges()
    {
        imagefilter($this->_image, IMG_FILTER_EDGEDETECT);
        return $this;
    }

    /**
     * Emboss
     *
     * @return Image
     */
    public function emboss()
    {
        imagefilter($this->_image, IMG_FILTER_EMBOSS);
        return $this;
    }

    /**
     * Invert
     *
     * @return Image
     */
    public function invert()
    {
        imagefilter($this->_image, IMG_FILTER_NEGATE);
        return $this;
    }

    /**
     * Mean Remove
     *
     * @return Image
     */
    public function meanRemove()
    {
        imagefilter($this->_image, IMG_FILTER_MEAN_REMOVAL);
        return $this;
    }

    /**
     * Sepia
     *
     * @return Image
     */
    public function sepia()
    {
        imagefilter($this->_image, IMG_FILTER_GRAYSCALE);
        imagefilter($this->_image, IMG_FILTER_COLORIZE, 100, 50, 0);
        return $this;
    }

    /**
     * Sketch
     *
     * @return Image
     */
    public function sketch()
    {
        imagefilter($this->_image, IMG_FILTER_MEAN_REMOVAL);
        return $this;
    }

    /**
     * Smooth
     *
     * @param int			$level	Min = -10, max = 10
     * @return Image
     */
    public function smooth($level)
    {
        imagefilter($this->_image, IMG_FILTER_SMOOTH, $this->_inRange($level, -10, 10));
        return $this;
    }

    /**
     * Pixelate
     *
     * @param int	$block_size	Size in pixels of each resulting block
     * @return Image
     *
     */
    public function pixelate($block_size = 10)
    {
        imagefilter($this->_image, IMG_FILTER_PIXELATE, $block_size, true);
        return $this;
    }

    /**
     * Fill image with color
     *
     * @param string		$color	Hex color string, array(red, green, blue) or array(red, green, blue, alpha).
     * 								Where red, green, blue - integers 0-255, alpha - integer 0-127
     * @return Image
     */
    public function fill($color = '#000000')
    {
        $rgba = $this->_normalizeColor($color);
        $fill_color = imagecolorallocatealpha($this->_image, $rgba['r'], $rgba['g'], $rgba['b'], $rgba['a']);
        imagealphablending($this->_image, false);
        imagesavealpha($this->_image, true);
        imagefilledrectangle($this->_image, 0, 0, $this->_width, $this->_height, $fill_color);

        return $this;
    }

    /**
     * Fit to height (proportionally resize to specified height)
     *
     * @param int	$height
     * @return Image
     */
    public function resizeToHeight($height)
    {
        $aspect_ratio = $this->_height / $this->_width;
        $width = $height / $aspect_ratio;

        return $this->resize($width, $height);
    }

    /**
     * Fit to width (proportionally resize to specified width)
     *
     * @param int	$width
     * @return Image
     */
    public function resizeToWidth($width)
    {
        $aspect_ratio = $this->_height / $this->_width;
        $height = $width * $aspect_ratio;

        return $this->resize($width, $height);
    }

    /**
     * Flip an image horizontally or vertically
     *
     * @param string	$direction	x|y
     * @return Image
     *
     */
    public function flip($direction)
    {
        $new = imagecreatetruecolor($this->_width, $this->_height);
        imagealphablending($new, false);
        imagesavealpha($new, true);

        if (strtolower($direction) == 'y') {
            for ($y = 0; $y < $this->_height; $y++) {
                imagecopy($new, $this->_image, 0, $y, 0, $this->_height - $y - 1, $this->_width, 1);
            }
        } else {
            for ($x = 0; $x < $this->_width; $x++) {
                imagecopy($new, $this->_image, $x, 0, $this->_width - $x - 1, 0, 1, $this->_height);
            }
        }

        $this->_image = $new;

        return $this;
    }

    /**
     * Changes the opacity level of the image
     *
     * @param float|int		$opacity	0-1
     */
    public function opacity($opacity)
    {
        // Determine opacity
        $opacity = $this->_inRange($opacity, 0, 1) * 100;

        // Make a copy of the image
        $copy = imagecreatetruecolor($this->_width, $this->_height);
        imagealphablending($copy, false);
        imagesavealpha($copy, true);
        imagecopy($copy, $this->_image, 0, 0, 0, 0, $this->_width, $this->_height);

        // Create transparent layer
        $this->create($this->_width, $this->_height, array(0, 0, 0, 127));

        // Merge with specified opacity
        $this->_imagecopymergealpha($this->_image, $copy, 0, 0, 0, 0, $this->_width, $this->_height, $opacity);
        imagedestroy($copy);

        return $this;
    }

    /**
     * Resize an image to the specified dimensions
     *
     * @param int	$width
     * @param int	$height
     * @return Image
     */
    public function resize($width, $height)
    {
        // Generate new GD image
        $new = imagecreatetruecolor($width, $height);
        $info = $this->getOriginalInfo();

        if ($info['format'] === 'gif') {
            // Preserve transparency in GIFs
            $transparent_index = imagecolortransparent($this->_image);
            if ($transparent_index >= 0) {
                $transparent_color = imagecolorsforindex($this->_image, $transparent_index);
                $transparent_index = imagecolorallocate($new, $transparent_color['red'], $transparent_color['green'], $transparent_color['blue']);
                imagefill($new, 0, 0, $transparent_index);
                imagecolortransparent($new, $transparent_index);
            }
        } else {
            // Preserve transparency in PNGs (benign for JPEGs)
            imagealphablending($new, false);
            imagesavealpha($new, true);
        }

        $width = round($width);
        $height = round($height);
        // Resize
        imagecopyresampled($new, $this->_image, 0, 0, 0, 0, $width, $height, $this->_width, $this->_height);

        // Update meta data
        $this->_width = $width;
        $this->_height = $height;
        $this->_image = $new;

        return $this;
    }

    /**
     * Rotate an image
     *
     * @param int	$angle		0-360
     * @param string	$bg_color	Hex color string, array(red, green, blue) or array(red, green, blue, alpha).
     * 									Where red, green, blue - integers 0-255, alpha - integer 0-127
     * @return Image
     */
    public function rotate($angle, $bg_color = '#000000')
    {
        // Perform the rotation
        $rgba = $this->_normalizeColor($bg_color);
        $bg_color = imagecolorallocatealpha($this->_image, $rgba['r'], $rgba['g'], $rgba['b'], $rgba['a']);
        $new = imagerotate($this->_image, -($this->_inRange($angle, -360, 360)), $bg_color);
        imagesavealpha($new, true);
        imagealphablending($new, true);

        // Update meta data
        $this->_width = imagesx($new);
        $this->_height = imagesy($new);
        $this->_image = $new;

        return $this;
    }

    /**
     * Create an image from scratch
     *
     * @param int		$width	Image width
     * @param int|null		$height	If omitted - assumed equal to $width
     * @param null|string	$color	Hex color string, array(red, green, blue) or array(red, green, blue, alpha).
     * 								Where red, green, blue - integers 0-255, alpha - integer 0-127
     * @return Image
     */
    public function create($width, $height = null, $color = null)
    {
        $height = $height ? : $width;
        $this->_width = $width;
        $this->_height = $height;
        $this->_image = imagecreatetruecolor($width, $height);
        $this->_originalInfo = array(
            'width' => $width,
            'height' => $height,
            'orientation' => $this->getOrientation(),
            'exif' => null,
            'format' => 'png',
            'mime' => 'image/png'
        );

        if ($color) {
            $this->fill($color);
        }

        return $this;
    }

    /**
     * Crop an image
     *
     * @param int			$x1	Left
     * @param int			$y1	Top
     * @param int			$x2	Right
     * @param int			$y2	Bottom
     *
     * @return Image
     */
    public function crop($x1, $y1, $x2, $y2)
    {
        // Determine crop size
        if ($x2 < $x1) {
            list($x1, $x2) = array($x2, $x1);
        }
        if ($y2 < $y1) {
            list($y1, $y2) = array($y2, $y1);
        }
        $crop_width = $x2 - $x1;
        $crop_height = $y2 - $y1;

        // Perform crop
        $new = imagecreatetruecolor($crop_width, $crop_height);
        imagealphablending($new, false);
        imagesavealpha($new, true);
        imagecopyresampled($new, $this->_image, 0, 0, $x1, $y1, $crop_width, $crop_height, $crop_width, $crop_height);

        // Update meta data
        $this->_width = $crop_width;
        $this->_height = $crop_height;
        $this->_image = $new;

        return $this;
    }

    /**
     * Load an image
     *
     * @param string        $filename	Path to image file
     * @return Image
     * @throws Exception
     */
    public function load($filename)
    {
        // Require GD library
        if (!extension_loaded('gd')) {
            throw new Exception\Version('Required extension GD is not loaded.');
        }

        $this->_filename = $filename;
        return $this->_getMetaData();
    }

    /**
     * Load a base64 string as image
     *
     * @param string	$filename   base64 string
     * @return Image
     * 
     */
    public function loadBase64($base64string)
    {
        if (!extension_loaded('gd')) {
            throw new Exception\Version('Required extension GD is not loaded.');
        }

        //remove data URI scheme and spaces from base64 string then decode it
        $this->_imagestring = base64_decode(str_replace(' ', '+', preg_replace('#^data:image/[^;]+;base64,#', '', $base64string)));
        $this->_image = imagecreatefromstring($this->_imagestring);
        return $this->_getMetaData();
    }

    /**
     * Outputs image without saving
     *
     * @param null|string	$format		If omitted or null - format of original file will be used, may be gif|jpg|png
     * @param int|null		$quality	Output image quality in percents 0-100
     * @throws Exception
     */
    public function output($format = null, $quality = null)
    {
        // Determine quality
        $quality = $quality ? : $this->_quality;

        // Determine mimetype
        switch (strtolower($format)) {
            case 'gif':
                $mimetype = 'image/gif';
                break;
            case 'jpeg':
            case 'jpg':
                imageinterlace($this->_image, true);
                $mimetype = 'image/jpeg';
                break;
            case 'png':
                $mimetype = 'image/png';
                break;
            default:
                $info = (empty($this->_imagestring)) ? getimagesize($this->_filename) : getimagesizefromstring($this->_imagestring);
                $mimetype = $info['mime'];
                unset($info);
                break;
        }

        // Output the image
        header('Content-Type: ' . $mimetype);
        switch ($mimetype) {
            case 'image/gif':
                imagegif($this->_image);
                break;
            case 'image/jpeg':
                imagejpeg($this->_image, null, round($quality));
                break;
            case 'image/png':
                imagepng($this->_image, null, round(9 * $quality / 100));
                break;
            default:
                throw new Exception\Type(sprintf('Unsupported image format: %s', $this->_filename));
                break;
        }

        $this->__destruct();
    }

    /**
     * Outputs image as data base64 to use as img src
     *
     * @param null|string	$format		If omitted or null - format of original file will be used, may be gif|jpg|png
     * @param int|null		$quality	Output image quality in percents 0-100
     * @return string
     * @throws Exception
     */
    public function outputBase64($format = null, $quality = null)
    {
        // Determine quality
        $quality = $quality ? : $this->_quality;

        // Determine mimetype
        switch (strtolower($format)) {
            case 'gif':
                $mimetype = 'image/gif';
                break;
            case 'jpeg':
            case 'jpg':
                imageinterlace($this->_image, true);
                $mimetype = 'image/jpeg';
                break;
            case 'png':
                $mimetype = 'image/png';
                break;
            default:
                $info = getimagesize($this->_filename);
                $mimetype = $info['mime'];
                unset($info);
                break;
        }

        // Output the image
        ob_start();
        switch ($mimetype) {
            case 'image/gif':
                imagegif($this->_image);
                break;
            case 'image/jpeg':
                imagejpeg($this->_image, null, round($quality));
                break;
            case 'image/png':
                imagepng($this->_image, null, round(9 * $quality / 100));
                break;
            default:
                throw new Exception\Type(sprintf('Unsupported image format: %s', $this->_filename));
                break;
        }
        $image_data = ob_get_contents();
        ob_end_clean();

        // Returns formatted string for img src
        return 'data:' . $mimetype . ';base64,' . base64_encode($image_data);
    }

    /**
     * Save an image
     * The resulting format will be determined by the file extension.
     *
     * @param null|string	$filename	If omitted - original file will be overwritten
     * @param null|int		$quality	Output image quality in percents 0-100
     * @return Image
     * @throws Exception
     */
    public function save($filename = null, $quality = null)
    {
        // Determine quality, filename, and format
        $quality = $quality ? : $this->_quality;
        $filename = $filename ? StringMethods::removeDiacriticalMarks(str_replace(' ', '_', $filename)) : $this->_filename;
        $info = $this->getOriginalInfo();
        $format = $this->_fileExt($filename) ? : $info['format'];

        // Create the image
        switch (strtolower($format)) {
            case 'gif':
                $result = imagegif($this->_image, $filename);
                break;
            case 'jpg':
            case 'jpeg':
                imageinterlace($this->_image, true);
                $result = imagejpeg($this->_image, $filename, round($quality));
                break;
            case 'png':
                $result = imagepng($this->_image, $filename, round(9 * $quality / 100));
                break;
            default:
                throw new Exception\Type('Unsupported format');
        }

        if (!$result) {
            throw new Exception\IO(sprintf('Unable to save image: %s', $filename));
        }
        $this->_filename = $filename;
        return $this;
    }

    /**
     * Overlay
     * Overlay an image on top of another, works with 24-bit PNG alpha-transparency
     *
     * @param string		$overlay	An image filename or a Image object
     * @param string		$position	center|top|left|bottom|right|top left|top right|bottom left|bottom right
     * @param float|int		$opacity	Overlay opacity 0-1
     * @param int		$x_offset	Horizontal offset in pixels
     * @param int		$y_offset	Vertical offset in pixels
     *
     * @return Image
     */
    public function overlay($overlay, $position = 'center', $opacity = 1, $x_offset = 0, $y_offset = 0)
    {
        // Load overlay image
        if (!($overlay instanceof Image)) {
            $overlay = new Image($overlay);
        }

        // Convert opacity
        $opacity = $opacity * 100;

        // Determine position
        switch (strtolower($position)) {
            case 'top left':
                $x = 0 + $x_offset;
                $y = 0 + $y_offset;
                break;
            case 'top right':
                $x = $this->_width - $overlay->width + $x_offset;
                $y = 0 + $y_offset;
                break;
            case 'top':
                $x = ($this->_width / 2) - ($overlay->width / 2) + $x_offset;
                $y = 0 + $y_offset;
                break;
            case 'bottom left':
                $x = 0 + $x_offset;
                $y = $this->_height - $overlay->height + $y_offset;
                break;
            case 'bottom right':
                $x = $this->_width - $overlay->width + $x_offset;
                $y = $this->_height - $overlay->height + $y_offset;
                break;
            case 'bottom':
                $x = ($this->_width / 2) - ($overlay->width / 2) + $x_offset;
                $y = $this->_height - $overlay->height + $y_offset;
                break;
            case 'left':
                $x = 0 + $x_offset;
                $y = ($this->_height / 2) - ($overlay->height / 2) + $y_offset;
                break;
            case 'right':
                $x = $this->_width - $overlay->width + $x_offset;
                $y = ($this->_height / 2) - ($overlay->height / 2) + $y_offset;
                break;
            case 'center':
            default:
                $x = ($this->_width / 2) - ($overlay->width / 2) + $x_offset;
                $y = ($this->_height / 2) - ($overlay->height / 2) + $y_offset;
                break;
        }

        // Perform the overlay
        $this->_imagecopymergealpha($this->_image, $overlay->image, $x, $y, 0, 0, $overlay->width, $overlay->height, $opacity);

        return $this;
    }

    /**
     * Add text to an image
     *
     * @param string		$text
     * @param string		$font_file
     * @param float|int		$font_size
     * @param string		$color
     * @param string		$position
     * @param int		$x_offset
     * @param int		$y_offset
     * @return Image
     * @throws Exception
     */
    public function text($text, $font_file, $font_size = 12, $color = '#000000', $position = 'center', $x_offset = 0, $y_offset = 0)
    {
        $angle = 0;

        // Determine text color
        $rgba = $this->_normalizeColor($color);
        $color = imagecolorallocatealpha($this->_image, $rgba['r'], $rgba['g'], $rgba['b'], $rgba['a']);

        // Determine textbox size
        $box = imagettfbbox($font_size, $angle, $font_file, $text);
        if (!$box) {
            throw new Exception\IO(sprintf('Unable to load font: %s', $font_file));
        }
        $box_width = abs($box[6] - $box[2]);
        $box_height = abs($box[7] - $box[1]);

        // Determine position
        switch (strtolower($position)) {
            case 'top left':
                $x = 0 + $x_offset;
                $y = 0 + $y_offset + $box_height;
                break;
            case 'top right':
                $x = $this->_width - $box_width + $x_offset;
                $y = 0 + $y_offset + $box_height;
                break;
            case 'top':
                $x = ($this->_width / 2) - ($box_width / 2) + $x_offset;
                $y = 0 + $y_offset + $box_height;
                break;
            case 'bottom left':
                $x = 0 + $x_offset;
                $y = $this->_height - $box_height + $y_offset + $box_height;
                break;
            case 'bottom right':
                $x = $this->_width - $box_width + $x_offset;
                $y = $this->_height - $box_height + $y_offset + $box_height;
                break;
            case 'bottom':
                $x = ($this->_width / 2) - ($box_width / 2) + $x_offset;
                $y = $this->_height - $box_height + $y_offset + $box_height;
                break;
            case 'left':
                $x = 0 + $x_offset;
                $y = ($this->_height / 2) - (($box_height / 2) - $box_height) + $y_offset;
                break;
            case 'right';
                $x = $this->_width - $box_width + $x_offset;
                $y = ($this->_height / 2) - (($box_height / 2) - $box_height) + $y_offset;
                break;
            case 'center':
            default:
                $x = ($this->_width / 2) - ($box_width / 2) + $x_offset;
                $y = ($this->_height / 2) - (($box_height / 2) - $box_height) + $y_offset;
                break;
        }

        // Add the text
        imagettftext($this->_image, $font_size, $angle, $x, $y, $color, $font_file, $text);

        return $this;
    }

    /**
     * Thumbnail
     * This function attempts to get the image to as close to the provided dimensions as possible, and then crops the
     * remaining overflow (from the center) to get the image to be the size specified. Useful for generating thumbnails.
     *
     * @param int	$width
     * @param int|null	$height     If omitted - assumed equal to $width
     * @return Image
     */
    public function thumbnail($width, $height = null)
    {
        // Determine height
        $height = $height ? : $width;

        // Determine aspect ratios
        $current_aspect_ratio = $this->_height / $this->_width;
        $new_aspect_ratio = $height / $width;

        // Fit to height/width
        if ($new_aspect_ratio > $current_aspect_ratio) {
            $this->resizeToHeight($height);
        } else {
            $this->resizeToWidth($width);
        }
        $left = floor(($this->_width / 2) - ($width / 2));
        $top = floor(($this->_height / 2) - ($height / 2));

        // Return trimmed image
        return $this->crop($left, $top, $width + $left, $height + $top);
    }

    /**
     * Returns the file extension of the specified file
     *
     * @param string	$filename
     * @return string
     */
    protected function _fileExt($filename)
    {
        return pathinfo($filename, PATHINFO_EXTENSION);
    }

    /**
     * Get meta data of image or base64 string
     *
     * @param string|null	$imagestring	If omitted treat as a normal image
     * @return Image
     * @throws Exception
     */
    protected function _getMetaData()
    {
        //gather meta data
        if (empty($this->_imagestring)) {
            $info = getimagesize($this->_filename);

            switch ($info['mime']) {
                case 'image/gif':
                    $this->_image = imagecreatefromgif($this->_filename);
                    break;
                case 'image/jpeg':
                    $this->_image = imagecreatefromjpeg($this->_filename);
                    break;
                case 'image/png':
                    $this->_image = imagecreatefrompng($this->_filename);
                    break;
                default:
                    throw new Exception\IO(sprintf('Invalid image: %s', $this->_filename));
                    break;
            }
        } elseif (function_exists('getimagesizefromstring')) {
            $info = getimagesizefromstring($this->_imagestring);
        } else {
            throw new Exception\Version('PHP 5.4 is required to use method getimagesizefromstring');
        }

        $this->_originalInfo = array(
            'width' => $info[0],
            'height' => $info[1],
            'orientation' => $this->getOrientation(),
            'exif' => function_exists('exif_read_data') && $info['mime'] === 'image/jpeg' && $this->_imagestring === null ? $this->exif = @exif_read_data($this->_filename) : null,
            'format' => preg_replace('/^image\//', '', $info['mime']),
            'mime' => $info['mime']
        );

        $this->_width = $info[0];
        $this->_height = $info[1];
        $this->_format = $this->_originalInfo['format'];

        imagesavealpha($this->_image, true);
        imagealphablending($this->_image, true);

        return $this;
    }

    /**
     * Same as PHP's imagecopymerge() function, except preserves alpha-transparency in 24-bit PNGs
     *
     * @param $dst_im
     * @param $src_im
     * @param $dst_x
     * @param $dst_y
     * @param $src_x
     * @param $src_y
     * @param $src_w
     * @param $src_h
     * @param $pct
     */
    protected function _imagecopymergealpha($dst_im, $src_im, $dst_x, $dst_y, $src_x, $src_y, $src_w, $src_h, $pct)
    {
        // Get image width and height and percentage
        $pct /= 100;
        $w = imagesx($src_im);
        $h = imagesy($src_im);

        // Turn alpha blending off
        imagealphablending($src_im, false);

        // Find the most opaque pixel in the image (the one with the smallest alpha value)
        $minalpha = 127;
        for ($x = 0; $x < $w; $x++) {
            for ($y = 0; $y < $h; $y++) {
                $alpha = (imagecolorat($src_im, $x, $y) >> 24) & 0xFF;
                if ($alpha < $minalpha) {
                    $minalpha = $alpha;
                }
            }
        }

        // Loop through image pixels and modify alpha for each
        for ($x = 0; $x < $w; $x++) {
            for ($y = 0; $y < $h; $y++) {
                // Get current alpha value (represents the TANSPARENCY!)
                $colorxy = imagecolorat($src_im, $x, $y);
                $alpha = ($colorxy >> 24) & 0xFF;
                // Calculate new alpha
                if ($minalpha !== 127) {
                    $alpha = 127 + 127 * $pct * ($alpha - 127) / (127 - $minalpha);
                } else {
                    $alpha += 127 * $pct;
                }
                // Get the color index with new alpha
                $alphacolorxy = imagecolorallocatealpha($src_im, ($colorxy >> 16) & 0xFF, ($colorxy >> 8) & 0xFF, $colorxy & 0xFF, $alpha);
                // Set pixel with the new color + opacity
                if (!imagesetpixel($src_im, $x, $y, $alphacolorxy)) {
                    return;
                }
            }
        }

        // Copy it
        imagesavealpha($dst_im, true);
        imagealphablending($dst_im, true);
        imagesavealpha($src_im, true);
        imagealphablending($src_im, true);
        imagecopy($dst_im, $src_im, $dst_x, $dst_y, $src_x, $src_y, $src_w, $src_h);
    }

    /**
     * Ensures $value is always within $min and $max range.
     * If lower, $min is returned. If higher, $max is returned.
     *
     * @param int|float		$value
     * @param int|float		$min
     * @param int|float		$max
     * @return int|float
     */
    protected function _inRange($value, $min, $max)
    {

        if ($value < $min) {
            return $min;
        }

        if ($value > $max) {
            return $max;
        }

        return $value;
    }

    /**
     * Converts a hex color value to its RGB equivalent
     *
     * @param string	$color  	Hex color string, array(red, green, blue) or array(red, green, blue, alpha).
     *                                  	Where red, green, blue - integers 0-255, alpha - integer 0-127
     * @return array|bool
     */
    protected function _normalizeColor($color)
    {
        if (is_string($color)) {

            $color = trim($color, '#');

            if (mb_strlen($color) == 6) {
                list($r, $g, $b) = array(
                    $color[0] . $color[1],
                    $color[2] . $color[3],
                    $color[4] . $color[5]
                );
            } elseif (mb_strlen($color) == 3) {
                list($r, $g, $b) = array(
                    $color[0] . $color[0],
                    $color[1] . $color[1],
                    $color[2] . $color[2]
                );
            } else {
                return false;
            }
            return array(
                'r' => hexdec($r),
                'g' => hexdec($g),
                'b' => hexdec($b),
                'a' => 0
            );
        } elseif (is_array($color) && (count($color) == 3 || count($color) == 4)) {

            if (isset($color['r'], $color['g'], $color['b'])) {
                return array(
                    'r' => $this->_inRange($color['r'], 0, 255),
                    'g' => $this->_inRange($color['g'], 0, 255),
                    'b' => $this->_inRange($color['b'], 0, 255),
                    'a' => $this->_inRange(isset($color['a']) ? $color['a'] : 0, 0, 127)
                );
            } elseif (isset($color[0], $color[1], $color[2])) {
                return array(
                    'r' => $this->_inRange($color[0], 0, 255),
                    'g' => $this->_inRange($color[1], 0, 255),
                    'b' => $this->_inRange($color[2], 0, 255),
                    'a' => $this->_inRange(isset($color[3]) ? $color[3] : 0, 0, 127)
                );
            }
        }
        return false;
    }

}
