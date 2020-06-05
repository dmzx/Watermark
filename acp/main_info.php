<?php
/**
 *
 * Watermark. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2020, dmzx, https://www.dmzx-web.net
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace dmzx\watermark\acp;

/**
 * Watermark ACP module info.
 */
class main_info
{
	public function module()
	{
		return [
			'filename'	=> '\dmzx\watermark\acp\main_module',
			'title'		=> 'ACP_WATERMARK_TITLE',
			'modes'		=> [
				'settings'	=> [
					'title'	=> 'ACP_WATERMARK',
					'auth'	=> 'ext_dmzx/watermark && acl_a_board',
					'cat'	=> ['ACP_WATERMARK_TITLE']
				],
			],
		];
	}
}
