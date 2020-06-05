<?php
/**
 *
 * Watermark. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2020, dmzx, https://www.dmzx-web.net
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace dmzx\watermark\migrations;

class install_watermark extends \phpbb\db\migration\migration
{
	public static function depends_on()
	{
		return [
			'\phpbb\db\migration\data\v330\v330'
		];
	}

	public function update_data()
	{
		return [
			['config.add', ['watermark_img_folder', 'watermark']],
			['config.add', ['watermark_file', '']],
			['config.add', ['watermark_scale', 2]],
			['config.add', ['watermark_enable', 0]],
			['config.add', ['watermark_version', '1.0.0']],

			['module.add', [
				'acp',
				'ACP_CAT_DOT_MODS',
				'ACP_WATERMARK_TITLE'
			]],
			['module.add', [
				'acp',
				'ACP_WATERMARK_TITLE',
				[
					'module_basename'	=> '\dmzx\watermark\acp\main_module',
					'modes'				=> ['settings'],
				],
			]],
		];
	}
}
