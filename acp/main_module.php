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
 * Watermark ACP module.
 */
class main_module
{
	public $page_title;
	public $tpl_name;
	public $u_action;

	/**
	 * Main ACP module
	 *
	 * @param int	$id	The module ID
	 * @param string $mode The module mode (for example: manage or settings)
	 * @throws \Exception
	 */
	public function main($id, $mode)
	{
		global $phpbb_container, $request;

		/** @var \dmzx\watermark\controller\acp_controller $acp_controller */
		$acp_controller = $phpbb_container->get('dmzx.watermark.controller.acp');

		// Requests
		$action = $request->variable('action', '');

		/** @var \phpbb\language\language $language */
		$language = $phpbb_container->get('language');

		// Load a template from adm/style for our ACP page
		$this->tpl_name = 'acp_watermark_body';

		// Set the page title for our ACP page
		$this->page_title = $language->lang('ACP_WATERMARK_TITLE');

		// Make the $u_action url available in our ACP controller
		$acp_controller->set_page_url($this->u_action);

		if ($request->is_set_post('watermarkimgfolder'))
		{
			$acp_controller->rename_folder();
		}

		// Load the display options handle in our ACP controller
		$acp_controller->display_options();
	}
}
