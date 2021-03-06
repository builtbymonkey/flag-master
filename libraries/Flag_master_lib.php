<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

 /**
 * mithra62 - Flag Master
 *
 * @package		mithra62:Flag_master
 * @author		Eric Lamb
 * @copyright	Copyright (c) 2012, mithra62, Eric Lamb.
 * @link		http://blah.com
 * @version		1.0
 * @filesource 	./system/expressionengine/third_party/flag_master/
 */

 /**
 * Flag Master - Generic methods
 *
 * Library Class
 *
 * @package 	mithra62:Flag_master
 * @author		Eric Lamb
 * @filesource 	./system/expressionengine/third_party/flag_master/libraries/flag_master_lib.php
 */
class Flag_master_lib
{
	/**
	 * The default URL to use within the CP
	 */
	private $url_base = FALSE;
	
	public function __construct()
	{
		$this->EE =& get_instance();
		$this->EE->load->model('Flag_master_settings_model', 'flag_master_settings');
		$this->settings = $this->get_settings();
	}
	
	/**
	 * Returns the array needed for the CP menu
	 */
	public function get_right_menu()
	{
		return array(
			'dashboard'		=> $this->url_base.'index',
			'profiles'		=> $this->url_base.'profiles',
			'settings'	=> $this->url_base.'settings'
		);
	}	
	
	/**
	 * Wrapper to handle CP URL creation
	 * @param string $method
	 */
	public function _create_url($method)
	{
		return $this->url_base.$method;
	}

	/**
	 * Creates the value for $url_base
	 * @param string $url_base
	 */
	public function set_url_base($url_base)
	{
		$this->url_base = $url_base;
	}
	
	public function get_url_base()
	{
		return $this->url_base;
	}
	
	public function perpage_select_options()
	{
		return array(
			   '10' => '10 '.lang('results'),
			   '25' => '25 '.lang('results'),
			   '75' => '75 '.lang('results'),
			   '100' => '100 '.lang('results'),
			   '150' => '150 '.lang('results')
		);		
	}
	
	public function date_select_options()
	{
		return array(
			   '' => lang('date_range'),
			   '1' => lang('past_day'),
			   '7' => lang('past_week'),
			   '31' => lang('past_month'),
			   '182' => lang('past_six_months'),
			   '365' => lang('past_year'),
			   'custom_date' => lang('any_date')
		);				
	}
	
	/**
	 * Wrapper that runs all the tests to ensure system stability
	 * @return array;
	 */
	public function error_check()
	{
		$errors = array();
		if($this->settings['license_number'] == '')
		{
			$errors['license_number'] = 'missing_license_number';
		}
		else 
		{
			if(!$this->valid_license($this->settings['license_number']))
			{
				$errors['license_number'] = 'invalid_license_number';
			}
			elseif($this->settings['license_status'] != '1')
			{
				$errors['license_number'] = 'invalid_license_number';
			}
		}
		
		return $errors;
	}

	/**
	 * Checks a given email is valid
	 * @param string $email
	 * @return mixed
	 */
	public function check_email($email)
	{
		if(function_exists('filter_var'))
		{
			return filter_var($email, FILTER_VALIDATE_EMAIL);
		}
		else
		{
			$this->EE->load->helper('email');
			return valid_email($email);
		}
	}
	
	/**
	 * Returns an array for configuring the EE pagination mechanism 
	 * @param string $method
	 * @param int 	$total_rows
	 * @param int	 $perpage
	 */
	public function pagination_config($method, $total_rows, $perpage)
	{
		// Pass the relevant data to the paginate class
		$config['base_url'] = $this->_create_url($method);
		$config['total_rows'] = $total_rows;
		$config['per_page'] = $perpage;
		$config['page_query_string'] = TRUE;
		$config['query_string_segment'] = 'rownum';
		$config['full_tag_open'] = '<p id="paginationLinks">';
		$config['full_tag_close'] = '</p>';
		$config['prev_link'] = '<img src="'.$this->EE->cp->cp_theme_url.'images/pagination_prev_button.gif" width="13" height="13" alt="&lt;" />';
		$config['next_link'] = '<img src="'.$this->EE->cp->cp_theme_url.'images/pagination_next_button.gif" width="13" height="13" alt="&gt;" />';
		$config['first_link'] = '<img src="'.$this->EE->cp->cp_theme_url.'images/pagination_first_button.gif" width="13" height="13" alt="&lt; &lt;" />';
		$config['last_link'] = '<img src="'.$this->EE->cp->cp_theme_url.'images/pagination_last_button.gif" width="13" height="13" alt="&gt; &gt;" />';

		return $config;
	}

	/**
	 * Half ass attempt at license verification.
	 * @param string $license
	 */
	public function valid_license($license)
	{
		//return TRUE; //if you want to disable the check uncomment this line. You should pay me though eric@mithra62.com :) 
		return preg_match("/^([a-z0-9]{8})-([a-z0-9]{4})-([a-z0-9]{4})-([a-z0-9]{4})-([a-z0-9]{12})$/", $license);
	}
	
	/**
	 * Verify license is valid
	 * @param string $force
	 */
	public function l($force = false)
	{
		if( $this->settings['license_number'] )
		{
			$license_check = $this->settings['license_check'];
			$next_notified = mktime(date('G', $license_check)+24, date('i', $license_check), 0, date('n', $license_check), date('j', $license_check), date('Y', $license_check));
	
			if(time() > $next_notified || $force)
			{
				//license_check
				$get = array(
						'ip' => (ee()->input->ip_address()),
						'key' => ($this->settings['license_number']),
						'site_url' => (ee()->config->config['site_url']),
						'webmaster_email' => (ee()->config->config['webmaster_email']),
						'add_on' => ('flag-master'),
						'version' => ('1.2.2')
				);
	
				$url = 'https://mithra62.com/license-check/'.base64_encode(json_encode($get));
				$ch = curl_init($url);
				curl_setopt($ch, CURLOPT_HEADER, 0);
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.1; rv:1.7.3) Gecko/20041001 Firefox/0.10.1");
				$response = urldecode(curl_exec($ch));
	
				$json = json_decode($response, true);
				if($json && isset($json['valid']))
				{
					ee()->flag_master_settings->update_setting('license_status', $json['valid']);
				}
				else 
				{
					ee()->flag_master_settings->update_setting('license_status', '0');
				}
			}
	
			ee()->flag_master_settings->update_setting('license_check', time());
		}
	}		
	
	/**
	 * Returns the setting array and caches it if none exists
	 */
	public function get_settings()
	{
		if ( ! isset($this->EE->session->cache[__CLASS__]['settings']))
		{
			$this->EE->session->cache[__CLASS__]['settings'] = $this->EE->flag_master_settings->get_settings();
		}
	
		return $this->EE->session->cache[__CLASS__]['settings'];
	}	
	
	/**
	 * Forces an array to download as a csv file
	 * @param array $arr
	 * @param bool $keys_as_headers
	 * @param bool $file_name
	 */
	public function downloadArray(array $arr, $keys_as_headers = TRUE, $file_name = FALSE)
	{
		header("Content-type: application/octet-stream");
		header("Content-Disposition: attachment; filename=\"$file_name\"");
		$cols = '';
		$rows = '';			
		if(is_array($arr) && count($arr) >= 1)
		{
			$rows = array();
			$cols = array_keys($arr['0']);
			foreach($arr AS $key => $value)
			{
				foreach($value AS $k => $v)
				{
					$value[$k] = $this->escape_csv_value($v, "\t");
				}
								
				$rows[] = implode("\t", $value);
			}
						
			echo implode("\t", $cols)."\n";
			echo implode("\n", $rows);
		}
		
		
		exit;

	}	
	
	public function escape_csv_value($value, $delim = ',') 
	{
		$value = str_replace('"', '""', $value);
		if(preg_match('/'.$delim.'/', $value) or preg_match("/\n/", $value) or preg_match('/"/', $value))
		{ 
			return '"'.$value.'"'; 
		} 
		else 
		{
			return $value; 
		}
	}	
	
	public function is_installed_module($module_name)
	{
		$data = $this->EE->db->select('module_name')->from('modules')->like('module_name', $module_name)->get();
		if($data->num_rows == '1')
		{
			return TRUE;
		}
	}
	
	public function get_template_options()
	{
		if ( ! isset($this->EE->session->cache[__CLASS__]['template_options']))
		{
			$query = $this->EE->template_model->get_templates();
			$this->EE->session->cache[__CLASS__]['template_options'][] = '';
			
			if ($query->num_rows() > 0)
			{
				foreach ($query->result() as $template)
				{
					$this->EE->session->cache[__CLASS__]['template_options'][$template->template_id] = $template->group_name.'/'.$template->template_name;
				}
			}			
		}
		
		return $this->EE->session->cache[__CLASS__]['template_options'];
	}
	
	public function flatten_array(array $data, $delim = '_', $depth = 1)
	{
		$return = array();
		foreach($data AS $key => $value)
		{
			if(is_array($value))
			{
				foreach($value AS $k => $v)
				{
					if(!is_numeric($k))
					{
						$return[$key.$delim.$k] = $v;
					}
					else
					{
						$return[$key] = $value;
						break;
					}
				}
			}
			else
			{
				$return[$key] = $value;
			}
			//$return[$delim.$key]
		}
		return $return;
	}	
}