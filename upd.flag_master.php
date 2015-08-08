<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

 /**
 * mithra62 - Flag Master
 *
 * @package		mithra62:Flag_master
 * @author		Eric Lamb
 * @copyright	Copyright (c) 2012, mithra62, Eric Lamb.
 * @link		http://mithra62.com/projects/view/flag-master/
 * @version		1.0
 * @filesource 	./system/expressionengine/third_party/flag_master/
 */
 
 /**
 * Flag Master - Upd Class
 *
 * Updater class
 *
 * @package 	mithra62:Flag_master
 * @author		Eric Lamb
 * @filesource 	./system/expressionengine/third_party/flag_master/upd.flag_master.php
 */
class Flag_master_upd 
{     
    public $class = '';
    
    public $settings_table = '';

    public $profiles_table = '';
    
    public $profile_options_table = '';
    
    public $flags_table = '';
     
    public function __construct() 
    { 
		// Make a local reference to the ExpressionEngine super object
		$this->EE =& get_instance();
		
		$path = dirname(realpath(__FILE__));
		include $path.'/config'.EXT;
		$this->class = $config['class_name'];
		$this->settings_table = $config['settings_table'];
		$this->profiles_table = $config['profiles_table'];
		$this->profile_options_table = $config['profile_options_table'];
		$this->flags_table = $config['flags_table'];
		$this->version = $config['version'];	
		$this->ext_class_name = $config['ext_class_name'];	
    } 
    
	public function install() 
	{
		$this->EE->load->dbforge();
	
		$data = array(
			'module_name' => $this->class,
			'module_version' => $this->version,
			'has_cp_backend' => 'y',
			'has_publish_fields' => 'n'
		);
	
		$this->EE->db->insert('modules', $data);
		
		$db_prefix = $this->EE->db->dbprefix;
		$sql = "INSERT INTO ".$db_prefix."actions (class, method) VALUES ('".$this->class."', 'proc_flag')";
		$this->EE->db->query($sql);
		
		$this->add_settings_table();
		$this->add_profiles_table();
		$this->add_profile_options_table();
		$this->add_flags_table();
		
		$this->activate_extension();
		
		return TRUE;
	} 
	
	public function activate_extension()
	{
		$data = array();
		$data[] = array(
					'class'      => $this->ext_class_name,
					'method'    => 'comment_entries_tagdata',
					'hook'  => 'comment_entries_tagdata',
				
					'settings'    => '',
					'priority'    => 10,
					'version'    => $this->version,
					'enabled'    => 'y'
		);
		
		$data[] = array(
				'class'      => $this->ext_class_name,
				'method'    => 'channel_entries_tagdata',
				'hook'  => 'channel_entries_tagdata',
		
				'settings'    => '',
				'priority'    => 10,
				'version'    => $this->version,
				'enabled'    => 'y'
		);		
	
		foreach($data AS $ex)
		{
			$this->EE->db->insert('extensions', $ex);	
		}		
	}

	public function uninstall()
	{
		$this->EE->load->dbforge();
	
		$this->EE->db->select('module_id');
		$query = $this->EE->db->get_where('modules', array('module_name' => $this->class));
	
		$this->EE->db->where('module_id', $query->row('module_id'));
		$this->EE->db->delete('module_member_groups');
	
		$this->EE->db->where('module_name', $this->class);
		$this->EE->db->delete('modules');
	
		$this->EE->db->where('class', $this->class);
		$this->EE->db->delete('actions');
		
		$this->EE->dbforge->drop_table($this->settings_table);
		$this->EE->dbforge->drop_table($this->profiles_table);
		$this->EE->dbforge->drop_table($this->profile_options_table);
		$this->EE->dbforge->drop_table($this->flags_table);
		
		$this->disable_extension();
	
		return TRUE;
	}
	
	public function disable_extension()
	{
		$this->EE->db->where('class', $this->ext_class_name);
		$this->EE->db->delete('extensions');
	}

	public function update($current = '')
	{
		
		if ($current == $this->version)
		{
			return FALSE;
		}
		
		$db_prefix = $this->EE->db->dbprefix;
		if(version_compare($current, '1.1', '<'))
		{
			$sql = "
				ALTER TABLE `".$db_prefix."flag_master_profiles` ADD `notify_email_subject` VARCHAR( 255 ) NOT NULL AFTER `notify_emails` ,
				ADD `notify_email_message` TEXT NOT NULL AFTER `notify_email_subject` ,
				ADD `notify_email_mailtype` VARCHAR( 10 ) NOT NULL AFTER `notify_email_message` ,
				ADD `notify_email_multiplier` INT( 3 ) NOT NULL AFTER `notify_email_mailtype` ";	
			$this->EE->db->query($sql);
		}
		
		if(version_compare($current, '1.2', '<'))
		{
			$this->activate_extension(); //first install of extension
		}		
		
		
	}	
	
	private function add_settings_table()
	{
		$this->EE->load->dbforge();
		$fields = array(
			'id' => array(
				'type'			=> 'int',
				'constraint'	=> 10,
				'unsigned'		=> TRUE,
				'null'			=> FALSE,
				'auto_increment'=> TRUE
			),
			'setting_key' => array(
				'type' 			=> 'varchar',
				'constraint'	=> '30',
				'null'			=> FALSE,
				'default'		=> ''
			),
			'setting_value' => array(
				'type' 			=> 'text',
				'null'			=> TRUE
			),
			'serialized' => array(
				'type' => 'int',
				'constraint' => 1,
				'null' => TRUE,
				'default' => '0'
			)										
		);

		$this->EE->dbforge->add_field($fields);
		$this->EE->dbforge->add_key('id', TRUE);
		$this->EE->dbforge->create_table($this->settings_table, TRUE);		
	}
	
	private function add_profiles_table()
	{
		$this->EE->load->dbforge();
		$fields = array(
			'id'	=> array(
					'type'			=> 'int',
					'constraint'	=> 10,
					'unsigned'		=> TRUE,
					'null'			=> FALSE,
					'auto_increment'=> TRUE
			),
			'name'	=> array(
					'type' 			=> 'varchar',
					'constraint'	=> '100',
					'null'			=> FALSE,
					'default'		=> ''
			),
			'active' => array(
					'type' => 'tinyint',
					'constraint' => 1,
					'null' => TRUE,
					'default' => '0'
			),
			'type'	=> array(
					'type' 			=> 'varchar',
					'constraint'	=> '100',
					'null'			=> FALSE,
					'default'		=> ''
			),
			'created_by'	=> array(
					'type' 			=> 'int',
					'constraint'	=> 10,
					'null'			=> FALSE,
					'default'		=> '0'
			),
			'total_flags'	=> array(
					'type' 			=> 'int',
					'constraint'	=> 10,
					'null'			=> FALSE,
					'default'		=> '0'
			),
			'notify_emails'  => array(
					'type' 			=> 'text',
					'null'			=> FALSE
			),
			'notify_email_subject'	=> array(
					'type' 			=> 'varchar',
					'constraint'	=> '255',
					'null'			=> FALSE,
					'default'		=> ''
			),	
			'notify_email_message'  => array(
					'type' 			=> 'text',
					'null'			=> FALSE
			),
			'notify_email_mailtype'	=> array(
					'type' 			=> 'varchar',
					'constraint'	=> '10',
					'null'			=> FALSE,
					'default'		=> ''
			),		
			'notify_email_multiplier'	=> array(
					'type' 			=> 'int',
					'constraint'	=> 3,
					'null'			=> FALSE,
					'default'		=> '0'
			),
			'auto_close_threshold'	=> array(
					'type' 			=> 'int',
					'constraint'	=> 10,
					'null'			=> FALSE,
					'default'		=> '0'
			),
			'last_modified'	=> array(
					'type' 			=> 'datetime'
			),
			'created_date'	=> array(
					'type' 			=> 'datetime'
			)
		);
	
		$this->EE->dbforge->add_field($fields);
		$this->EE->dbforge->add_key('id', TRUE);
		$this->EE->dbforge->create_table($this->profiles_table, TRUE);
	}	
	
	private function add_profile_options_table()
	{
		$this->EE->load->dbforge();
		$fields = array(
				'id'	=> array(
						'type'			=> 'int',
						'constraint'	=> 10,
						'unsigned'		=> TRUE,
						'null'			=> FALSE,
						'auto_increment'=> TRUE
				),
				'title'	=> array(
						'type' 			=> 'varchar',
						'constraint'	=> '100',
						'null'			=> FALSE,
						'default'		=> ''
				),
				'description'  => array(
						'type' 			=> 'text',
						'null'			=> FALSE,
						'default'		=> ''
				),
				'profile_id'	=> array(
						'type' 			=> 'int',
						'constraint'	=> 10,
						'null'			=> FALSE,
						'default'		=> '0'
				),	
				'parent_id'	=> array(
						'type' 			=> 'int',
						'constraint'	=> 10,
						'null'			=> FALSE,
						'default'		=> '0'
				),	
				'created_by'	=> array(
						'type' 			=> 'int',
						'constraint'	=> 10,
						'null'			=> FALSE,
						'default'		=> '0'
				),
				'total_flags'	=> array(
						'type' 			=> 'int',
						'constraint'	=> 10,
						'null'			=> FALSE,
						'default'		=> '0'
				),										
				'user_defined' => array(
						'type' => 'tinyint',
						'constraint' => 1,
						'null' => TRUE,
						'default' => '0'
				),
				'member_only' => array(
						'type' => 'tinyint',
						'constraint' => 1,
						'null' => TRUE,
						'default' => '0'
				),
				'sort_order'	=> array(
						'type' 			=> 'int',
						'constraint'	=> 10,
						'null'			=> FALSE,
						'default'		=> '0'
				),				
				'last_modified'	=> array(
						'type' 			=> 'datetime'
				),
				'created_date'	=> array(
						'type' 			=> 'datetime'
				)
		);
	
		$this->EE->dbforge->add_field($fields);
		$this->EE->dbforge->add_key('id', TRUE);
		$this->EE->dbforge->create_table($this->profile_options_table, TRUE);
	}

	private function add_flags_table()
	{
		$this->EE->load->dbforge();
		$fields = array(
				'id'	=> array(
						'type'			=> 'int',
						'constraint'	=> 10,
						'unsigned'		=> TRUE,
						'null'			=> FALSE,
						'auto_increment'=> TRUE
				),
				'profile_id'	=> array(
						'type' 			=> 'int',
						'constraint'	=> 10,
						'null'			=> FALSE,
						'default'		=> '0'
				),
				'option_id'	=> array(
						'type' 			=> 'int',
						'constraint'	=> 10,
						'null'			=> FALSE,
						'default'		=> '0'
				),
				'member_id'	=> array(
						'type' 			=> 'int',
						'constraint'	=> 10,
						'null'			=> FALSE,
						'default'		=> '0'
				),
				'entry_id'	=> array(
						'type' 			=> 'int',
						'constraint'	=> 10,
						'null'			=> FALSE,
						'default'		=> '0'
				),				
				'user_defined'  => array(
						'type' 			=> 'text',
						'null'			=> FALSE,
						'default'		=> ''
				),
				'ip_address'	=> array(
						'type' 			=> 'varchar',
						'constraint'	=> '30',
						'null'			=> FALSE,
						'default'		=> ''
				),	
				'user_agent'	=> array(
						'type' 			=> 'varchar',
						'constraint'	=> '100',
						'null'			=> FALSE,
						'default'		=> ''
				),
				'last_modified'	=> array(
						'type' 			=> 'datetime'
				),
				'created_date'	=> array(
						'type' 			=> 'datetime'
				)
		);
	
		$this->EE->dbforge->add_field($fields);
		$this->EE->dbforge->add_key('id', TRUE);
		$this->EE->dbforge->create_table($this->flags_table, TRUE);
	}	
}