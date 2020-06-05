<?php
/**
 *
 * Watermark. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2020, dmzx, https://www.dmzx-web.net
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace dmzx\watermark\controller;

/**
 * Watermark ACP controller.
 */
class acp_controller
{
	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\language\language */
	protected $language;

	/** @var \phpbb\log\log */
	protected $log;

	/** @var \phpbb\request\request */
	protected $request;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\user */
	protected $user;

	/** @var \phpbb\path_helper */
	protected $path_helper;

	/** @var string phpBB root path */
	protected $root_path;

	/** @var string Custom form action */
	protected $u_action;

	/**
	 * Constructor.
	 *
	 * @param \phpbb\config\config		$config		Config object
	 * @param \phpbb\language\language	$language	Language object
	 * @param \phpbb\log\log			$log		Log object
	 * @param \phpbb\request\request	$request	Request object
	 * @param \phpbb\template\template	$template	Template object
	 * @param \phpbb\user				$user		User object
	 * @param \phpbb\path_helper 		$path_helper phpBB path helper
	 * @param string					$root_path	phpBB root path
	 */
	public function __construct(
		\phpbb\config\config $config,
		 \phpbb\language\language $language,
		\phpbb\log\log $log,
		\phpbb\request\request $request,
		\phpbb\template\template $template,
		\phpbb\user $user,
		\phpbb\path_helper $path_helper,
		 string $root_path
	)
	{
		$this->config		= $config;
		$this->language		= $language;
		$this->log			= $log;
		$this->request		= $request;
		$this->template		= $template;
		$this->user			= $user;
		$this->path_helper 	= $path_helper;
		$this->root_path 	= $root_path;

	}

	/**
	 * Display the options a user can configure for this extension.
	 *
	 * @return void
	 */
	public function display_options()
	{
		// Add our common language file
		$this->language->add_lang('common', 'dmzx/watermark');

		// Create a form key for preventing CSRF attacks
		add_form_key('dmzx_watermark_acp');

		// Create an array to collect errors that will be output to the user
		$errors = [];

		// Determine board url - we may need it later
		$board_url = generate_board_url() . '/';
		$corrected_path = $this->path_helper->get_web_root_path();
		$image_path = ((defined('PHPBB_USE_BOARD_URL_PATH') && PHPBB_USE_BOARD_URL_PATH) ? $board_url : $corrected_path) . 'images/' . $this->config['watermark_img_folder'] . '/';

		if (!is_dir($image_path))
		{
			$this->recursive_mkdir($image_path, 0775);
		}

		// Is the form being submitted to us?
		if ($this->request->is_set_post('submit'))
		{
			// Test if the submitted form is valid
			if (!check_form_key('dmzx_watermark_acp'))
			{
				$errors[] = $this->language->lang('FORM_INVALID');
			}

			// If no errors, process the form data
			if (empty($errors))
			{
				// Set the options the user configured
				$this->config->set('watermark_enable', $this->request->variable('watermark_enable', ''));
				$this->config->set('watermark_file', $this->request->variable('watermark_file', ''));
				$this->config->set('watermark_scale', $this->request->variable('watermark_scale', 0));

				$file = $this->request->file('watermark_logo_upload');

				if ($file['error'] == UPLOAD_ERR_OK)
				{
					$destination = $image_path;
					if (!$this->upload($file, $destination))
					{
						trigger_error($this->language->lang('ACP_WATERMARK_ERROR', $file['name']) . adm_back_link($this->u_action), E_USER_WARNING);
					}
					$this->config->set('watermark_file', $file['name']);
				}
				else if ($file['error'] != UPLOAD_ERR_NO_FILE)
				{
					trigger_error($this->language->lang('ACP_WATERMARK_FILE_ERROR', $file['name']) . adm_back_link($this->u_action), E_USER_WARNING);
				}

				// Add option settings change action to the admin log
				$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_ACP_WATERMARK_SETTINGS');

				// Option settings have been updated and logged
				// Confirm this to the user and provide link back to previous page
				trigger_error($this->language->lang('ACP_WATERMARK_SETTING_SAVED') . adm_back_link($this->u_action));
			}
		}

		$s_errors = !empty($errors);

		// Set output variables for display in the template
		$this->template->assign_vars([
			'S_ERROR'						=> $s_errors,
			'ERROR_MSG'						=> $s_errors ? implode('<br />', $errors) : '',
			'WATERMARK_ENABLE'				=> $this->config['watermark_enable'],
			'WATERMARK_IMG_FOLDER'			=> $this->config['watermark_img_folder'],
			'WATERMARK_FILE'				=> $this->config['watermark_file'],
			'WATERMARK_SCALE'				=> $this->config['watermark_scale'],
			'WATERMARK_VERSION'				=> $this->config['watermark_version'],
			'U_ACTION'						=> $this->u_action,
		]);
	}

	public function rename_folder()
	{
		$watermark_img_folder = $this->request->variable('watermark_img_folder', 'watermark');

		if (strpbrk($watermark_img_folder, "\\/?%*:|\"<>") === false)
		{
			rename ($this->root_path . 'images/' . $this->config['watermark_img_folder'], $this->root_path . 'images/' . $watermark_img_folder);
			chmod($this->root_path . 'images/' . $watermark_img_folder, 0775);
			$this->config->set('watermark_img_folder', $watermark_img_folder);
		}
	}

	protected function recursive_mkdir($path, $mode = false)
	{
		if (!$mode)
		{
			$mode = 0755;
		}

		$dirs = explode('/', $path);
		$count = sizeof($dirs);
		$path = '.';
		for ($i = 0; $i < $count; $i++)
		{
			$path .= '/' . $dirs[$i];

			if (!is_dir($path))
			{
				@mkdir($path, $mode);
				@chmod($path, $mode);

				if (!is_dir($path))
				{
					return false;
				}
			}
		}
		return true;
	}

	protected function upload($fp, $location)
	{
		if ($this->allowedExtension($fp['name']) && $this->allowedSize($fp['size']))
		{
			$destination = $location . basename($fp['name']);
			if (move_uploaded_file($fp['tmp_name'], $destination))
			{
				return true;
			}
		}
		return false;
	}

	protected function allowedExtension($filename)
	{
		return in_array($this->getExtension($filename), ['png'], true);
	}

	protected function allowedSize($filesize)
	{
		return ($filesize < ((int) ini_get('upload_max_filesize')) * 1000000);
	}

	protected function getExtension($filename)
	{
		if (strpos($filename, '.') === false)
		{
			return '';
		}

		$parts = explode('.', $filename);
		return strtolower(array_pop($parts));
	}

	public function set_page_url($u_action)
	{
		$this->u_action = $u_action;
	}
}
