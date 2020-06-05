<?php
/**
 *
 * Watermark. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2020, dmzx, https://www.dmzx-web.net
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

if (!defined('IN_PHPBB'))
{
	exit;
}

if (empty($lang) || !is_array($lang))
{
	$lang = [];
}

// DEVELOPERS PLEASE NOTE
//
// All language files should use UTF-8 as their encoding and the files must not contain a BOM.
//
// Placeholders can now contain order information, e.g. instead of
// 'Page %s of %s' you can (and should) write 'Page %1$s of %2$s', this allows
// translators to re-order the output of data while ensuring it remains correct
//
// You do not need this where single placeholders are used, e.g. 'Message %d' is fine
// equally where a string contains only two placeholders which are used to wrap text
// in a url you again do not need to specify an order e.g., 'Click %sHERE%s' is fine
//
// Some characters you may want to copy&paste:
// ’ » “ ” …
//

$lang = array_merge($lang, [
	'ACP_WATERMARK_SETTING_SAVED'			=> 'Settings have been saved successfully!',
	'ACP_WATERMARK_ERROR'					=> 'The file %s failed to upload.',
	'ACP_WATERMARK_FILE_ERROR'				=> 'The watermark file %s failed.',
	'ACP_WATERMARK_ENABLE'					=> 'Enable watermark',
	'ACP_WATERMARK_ENABLE_EXPLAIN'			=> 'When enabled and image uploaded watermark will be automatic added to uploaded images.',
	'ACP_WATERMARK_LOGO_UPLOAD'				=> 'Watermark image upload',
	'ACP_WATERMARK_LOGO_UPLOAD_EXPLAIN'		=> 'Upload a watermark image. The "Watermark file uploaded" below will automatically be filled in when an image is uploaded.<br>Accepted format: png only.',
	'ACP_WATERMARK_FILE'					=> 'Watermark file uploaded',
	'ACP_WATERMARK_FILE_EXPLAIN'			=> 'This will be filled in automatically when you upload a file.<br>Delete and submit to remove watermark. Image will not be removed from server.',
	'ACP_WATERMARK_SCALE'					=> 'Watermark scale factor',
	'ACP_WATERMARK_SCALE_EXPLAIN'			=> 'Set scale of watermark image.<br>Default value is 2.',
	'ACP_WATERMARK_IMG_FOLDER'				=> 'Rename watermark image folder',
	'ACP_WATERMARK_IMG_FOLDER_EXPLAIN'		=> 'Rename watermark image folder in the root folder images.<br>Minimum length 3 characters.',
]);
