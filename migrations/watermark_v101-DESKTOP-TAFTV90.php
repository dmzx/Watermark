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

class watermark_v101 extends \phpbb\db\migration\migration
{
	public static function depends_on()
	{
		return [
			'\dmzx\watermark\migrations\install_watermark'
		];
	}

	public function update_data()
	{
		return [
			['config.update', ['watermark_version', '1.0.1']],
			['config.add', ['watermark_location', '0', true]],
			['config.add', ['watermark_level', 100]],
		];
	}
}
