<?php
/**
 *
 * Watermark. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2024, dmzx, https://www.dmzx-web.net
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace dmzx\watermark\migrations;

class watermark_v104 extends \phpbb\db\migration\migration
{
	public static function depends_on()
	{
		return [
			'\dmzx\watermark\migrations\watermark_v103'
		];
	}

	public function update_data()
	{
		return [
			['config.update', ['watermark_version', '1.0.4']],
			['config.add', ['watermark_forum_excluded', 0]],
		];
	}
}
