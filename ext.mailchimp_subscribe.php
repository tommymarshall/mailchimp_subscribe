<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once PATH_THIRD.'mailchimp_subscribe/includes/MailChimp.class.php';

/**
 * vl_recommend Extension Control Panel File
 *
 * @category	Extension
 * @author		Tommy Marshall
 * @link		http://viget.com
 */

class Vl_subscribe_ext {

	public $name             = 'Mailchimp Subscribe';
	public $version          = '1.0.0';
	public $description      = 'Subscribes newly created user to Mailchimp specified newsletter.';
	public $docs_url         = '';
	public $settings_exist   = 'y';
	public $package          = 'mailchimp_subscribe';
	public $settings         = array();
	public $settings_default = array(
		'mailchimp_subscribe_api_key' => '',
		'mailchimp_subscribe_list_id' => '',
	);

	protected $_ee;
	protected $_mailchimp;

	public function __construct($settings = array())
	{
		$this->_ee      =& get_instance();
		$this->_ee->load->library('logger');
		$this->settings = $this->assign_default_settings($settings);
	}

	public function assign_default_settings($settings = array())
	{
		$config_items = array();

		if ($this->_ee->config->item('mailchimp_subscribe_api_key')) {
			$config_items['mailchimp_subscribe_api_key'] = $this->_ee->config->item('mailchimp_subscribe_api_key');
		}

		if ($this->_ee->config->item('mailchimp_subscribe_list_id')) {
			$config_items['mailchimp_subscribe_list_id'] = $this->_ee->config->item('mailchimp_subscribe_list_id');
		}

		return array_merge($settings, $config_items);
	}

	public function settings()
	{
		return array(
			'mailchimp_subscribe_api_key' => $this->settings_default['mailchimp_subscribe_api_key'],
			'mailchimp_subscribe_list_id' => $this->settings_default['mailchimp_subscribe_list_id'],
		);
	}

	public function activate_extension()
	{
		$hooks = array(
			'cartthrob_on_authorize',
			'cp_members_member_create',
			'cp_members_validate_members',
			'freeform_module_insert_end',
			'member_member_register',
			'member_register_validate_members',
			'membrr_subscribe',
			'user_edit_end',
			'user_register_end',
			'zoo_visitor_cp_register_end',
			'zoo_visitor_cp_update_end',
			'zoo_visitor_register_end',
			'zoo_visitor_update_end',
		);

		$this->register_hooks($hooks);
	}

	public function register_hooks($hooks = array())
	{
		foreach ($hooks as $hook)
		{
			$this->_ee->db->insert(
				'extensions',
				array(
					'class'    => __CLASS__,
					'enabled'  => 'y',
					'hook'     => $hook,
					'settings' => serialize($this->settings()),
					'method'   => 'begin_register',
					'priority' => 10,
					'version'  => $this->version,
				)
			);
		}
	}

	public function begin_register()
	{
		if ( $this->userWantsUpdates() && $this->userCanSubscribe() )
		{

			$data = array(
				'email'      => $this->_ee->input->get_post('email'),
				'first_name' => $this->_ee->input->get_post('first_name'),
				'last_name'  => $this->_ee->input->get_post('last_name'),
			);

			$this->subscribe_user_to_list($data);
		}

	}

	public function userWantsUpdates()
	{
		return $this->_ee->input->get_post('email_updates') == 'yes';
	}

	public function userCanSubscribe()
	{
		if (strlen($this->settings['mailchimp_subscribe_api_key']) === 0)
		{
			$errors[] = "There is no API API key.";
		}

		if (strlen($this->settings['mailchimp_subscribe_list_id']) === 0)
		{
			$errors[] = "Error adding email {{$user_data['email']}} to list ID {{$this->settings['mailchimp_subscribe_list_id']}} using {{$this->settings['mailchimp_subscribe_api_key']}} as an API key.";
		}

		if ($errors)
		{
			foreach ($errors as $error) {
				$this->_ee->logger->developer($error);
			}

			return false;
		}

		return true;
	}

	public function subscribe_user_to_list($user_data = array())
	{
		$this->_mailchimp = new MailChimp($this->settings['mailchimp_subscribe_api_key']);
		$result = $this->_mailchimp->call('lists/subscribe',
			array(
				'id'                => $this->settings['mailchimp_subscribe_list_id'],
				'email'             => array(
					'email' => $user_data['email'],
				),
				'merge_vars'        => array(
					'FNAME' => $user_data['first_name'],
					'LNAME' => $user_data['last_name'],
				),
				'double_optin'      => false,
				'update_existing'   => true,
				'replace_interests' => false,
				'send_welcome'      => false,
			)
		);

		if ($result === FALSE)
		{
			$this->_ee->logger->developer("Error adding email {{$user_data['email']}} to list ID {{$this->settings['mailchimp_subscribe_list_id']}} using {{$this->settings['mailchimp_subscribe_api_key']}} as an API key.");

			return false;
		}

		return true;
	}

	public function disable_extension()
	{
		$this->_ee->db->where('class', __CLASS__);
		$this->_ee->db->delete('extensions');
	}
}
