<?php
/**
 *
 * Watermark. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2020, dmzx, https://www.dmzx-web.net
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace dmzx\watermark\event;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Watermark Event listener.
 */
class main_listener implements EventSubscriberInterface
{
	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\path_helper */
	protected $path_helper;

	/** @var string phpBB root path */
	protected $root_path;

	/**
	 * Constructor
	 *
	 * @param \phpbb\config\config		$config		Config object
	 * @param \phpbb\path_helper 		$path_helper phpBB path helper
	 * @param string					$root_path	phpBB root path
	 */
	public function __construct(
		\phpbb\config\config $config,
		\phpbb\path_helper $path_helper,
		string $root_path
	)
	{
		$this->config = $config;
		$this->path_helper = $path_helper;
		$this->root_path = $root_path;
	}

	public static function getSubscribedEvents()
	{
		return [
			'core.modify_uploaded_file'		=> 'modify_uploaded_file',
		];
	}

	public function modify_uploaded_file($event)
	{
		if ($this->config['watermark_enable'])
		{
			$board_url = generate_board_url() . '/';
			$corrected_path = $this->path_helper->get_web_root_path();
			$image_path = ((defined('PHPBB_USE_BOARD_URL_PATH') && PHPBB_USE_BOARD_URL_PATH) ? $board_url : $corrected_path) . 'images/' . $this->config['watermark_img_folder'] . '/';

			if (!$event['is_image'] || !$event['filedata']['post_attach'] || !$this->supportCheck($event['filedata']['mimetype']))
			{
				return;
			}

			if (!@extension_loaded('gd'))
			{
				return;
			}

			if (empty($this->config['watermark_file']))
			{
				return;
			}

			if (!file_exists($image_path . $this->config['watermark_file']))
			{
				return;
			}

			$watermark = imagecreatefrompng($image_path . $this->config['watermark_file']);
			$transColor = imagecolorallocatealpha($watermark, 255, 255, 255, 127);
			$watermark = imagerotate($watermark, $this->config['watermark_orientation'], $transColor);
			$image = $this->image_get($event);
			$orig_watermark_x = imagesx($watermark);
			$orig_watermark_y = imagesy($watermark);
			$im_x = imagesx($image);
			$im_y = imagesy($image);
			$cof = $im_x / ($orig_watermark_x * $this->config['watermark_scale']);
			$w = intval($orig_watermark_x * $cof);
			$h = intval($orig_watermark_y * $cof);

			$watermark_mini = imagecreatetruecolor($w, $h);
			imagealphablending($watermark_mini, false);
			imagesavealpha($watermark_mini, true);
			imagecopyresampled($watermark_mini, $watermark, 0, 0, 0, 0, $w, $h, $orig_watermark_x, $orig_watermark_y);

			$dest_x = $im_x - $w;
			$dest_y = $im_y - $h;
			$location = $this->config['watermark_location'];
			$level = $this->config['watermark_level'];

			if ($location == 0)
			{
				// top-left
				$this->imagecopymerge_watermark($image, $watermark_mini, 0, 0, 0, 0, $w, $h, $level);
			}
			else if ($location == 1)
			{
				// top-right
				$this->imagecopymerge_watermark($image, $watermark_mini, $dest_x + 0, 0, 0, 0, $w, $h, $level);
			}
			else if ($location == 2)
			{
				// bottom-left
				$this->imagecopymerge_watermark($image, $watermark_mini, 0, $dest_y + 5, 0, 0, $w, $h, $level);
			}
			else if ($location == 3)
			{
				// bottom-right
				$this->imagecopymerge_watermark($image, $watermark_mini, $dest_x, $dest_y + 5, 0, 0, $w, $h, $level);
			}
			else
			{
				// center
				$this->imagecopymerge_watermark($image, $watermark_mini, ($dest_x)/2, ($dest_y)/2, 0, 0, $w, $h, $level);
			}

			$this->image_write($event, $image);

			imagedestroy($watermark);

			$filedata = $event['filedata'];

			clearstatcache();

			$filedata['filesize'] = @filesize($this->root_path . $this->config['upload_path'] . '/' . $filedata['physical_filename']);
			$event['filedata'] = $filedata;
		}
	}

	private function imagecopymerge_watermark($image, $watermark_mini, $dest_x, $dest_y, $src_x, $src_y, $w, $h, $level)
	{
		$level /= 100;
		$minalpha = 127;

		for ($x = 0; $x < $w; $x++)
		{
			for ($y = 0; $y < $h; $y++)
			{
				$alpha = (imagecolorat($watermark_mini, $x, $y) >> 24) & 0xFF;

				if ($alpha < $minalpha)
				{
					$minalpha = $alpha;
				}
			}
		}

		for ($x = 0; $x < $w; $x++)
		{
			for ($y = 0; $y < $h; $y++)
			{
				$colorxy = imagecolorat($watermark_mini, $x, $y);
				$alpha = ($colorxy >> 24) & 0xFF;

				if ($minalpha !== 127)
				{
					$alpha = 127 + 127 * $level * ($alpha - 127) / (127 - $minalpha);
				}
				else
				{
					$alpha += 127 * $level;
				}

				$alphacolorxy = imagecolorallocatealpha($watermark_mini, ($colorxy >> 16) & 0xFF, ($colorxy >> 8) & 0xFF, $colorxy & 0xFF, $alpha);

				if (!imagesetpixel($watermark_mini, $x, $y, $alphacolorxy))
				{
					return false;
				}
			}
		}

		imagecopy($image, $watermark_mini, $dest_x, $dest_y, $src_x, $src_y, $w, $h);
		return true;
	}

	private function image_get($event)
	{
		switch ($event['filedata']['mimetype'])
		{
			case 'image/png':
				$image = imagecreatefrompng($this->root_path . $this->config['upload_path'] . '/' . $event['filedata']['physical_filename']);
			break;

			case 'image/jpeg':
			case 'image/jpg':
				$image = imagecreatefromjpeg($this->root_path . $this->config['upload_path'] . '/' . $event['filedata']['physical_filename']);
			break;

			case 'image/gif':
				$image = imagecreatefromgif($this->root_path . $this->config['upload_path'] . '/' . $event['filedata']['physical_filename']);
			break;

			case 'image/webp':
				$image = imagecreatefromwebp($this->root_path . $this->config['upload_path'] . '/' . $event['filedata']['physical_filename']);
			break;

			default:
			break;
		}
		return $image;
	}

	private function image_write($event, $image)
	{
		switch ($event['filedata']['mimetype'])
		{
			case 'image/png':
				imagepng($image, $this->root_path . $this->config['upload_path'] . '/' . $event['filedata']['physical_filename']);
			break;

			case 'image/jpeg':
			case 'image/jpg':
				imagejpeg($image, $this->root_path . $this->config['upload_path'] . '/' . $event['filedata']['physical_filename']);
			break;

			case 'image/gif':
				imagegif($image, $this->root_path . $this->config['upload_path'] . '/' . $event['filedata']['physical_filename']);
			break;

			case 'image/webp':
				imagewebp($image, $this->root_path . $this->config['upload_path'] . '/' . $event['filedata']['physical_filename']);
			break;

			default:
			break;
		}
		imagedestroy($image);
	}

	private function supportCheck($mimetype)
	{
		switch ($mimetype)
		{
			case 'image/png':
			case 'image/jpeg':
			case 'image/gif':
			case 'image/webp':
				return true;
				break;

			default:
				return false;
				break;
		}
	}
}
