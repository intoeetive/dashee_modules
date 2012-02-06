<?php

/*
=====================================================
 Addons widget
-----------------------------------------------------
 http://www.intoeetive.com/
-----------------------------------------------------
 Copyright (c) 2012 Yuri Salimovskiy
=====================================================
 This software is based upon and derived from
 ExpressionEngine software protected under
 copyright dated 2004 - 2012. Please see
 http://expressionengine.com/docs/license.html
=====================================================
 File: wgt.dashee_modules.php
-----------------------------------------------------
 Purpose: Lists all modules that have Control Panel
=====================================================
*/


class Wgt_dashee_modules
{
	public $title;		// title displayed at top of widget
	public $settings;	// array of widget settings (required for dynamic widgets only)
	public $wclass;		// class name for additional styling capabilities
	
	private $_EE;
	
	/**
	 * Constructor
	 */
	public function __construct()
	{	
		$this->_EE 		=& get_instance();
		$this->_EE->lang->loadfile('modules');
		$this->_EE->lang->loadfile('dashee_modules');  
		
		$this->title  	= lang('wgt_dashee_modules_name');
		$this->wclass 	= 'contentMenu';	
		
		// define default widget settings
		$this->settings = array();
		
	}
	
	// ----------------------------------------------------------------
	
	/**
	 * Permissions Function
	 * Defines permissions needed for user to be able to add widget.
	 *
	 * @return 	bool
	 */
	public function permissions()
	{
		// add any additional custom permission checking here and 
		// return FALSE if user doesn't have permission
	
		return TRUE;
	}

	/**
	 * Index Function
	 *
	 * @return 	string
	 */
	public function index()
	{
		$this->_EE->load->library('addons');
		$can_admin = ( ! $this->_EE->cp->allowed_group('can_admin_modules')) ? FALSE : TRUE;
		
		//  Fetch all module names from "modules" folder
		$modules = $this->_EE->addons->get_files();

		foreach($modules as $module => $info)
		{
			$this->_EE->lang->loadfile($module);
		}
		
		$installed_modules = $this->_EE->addons->get_installed();
	
		// Fetch allowed Modules for a particular user
		$this->_EE->db->select('modules.module_name');
		$this->_EE->db->from('modules, module_member_groups');
		$this->_EE->db->where('module_member_groups.group_id', $this->_EE->session->userdata('group_id'));
		$this->_EE->db->where('modules.module_id = '.$this->_EE->db->dbprefix('module_member_groups').'.module_id', NULL, FALSE);
		$this->_EE->db->order_by('module_name');
		
		$query = $this->_EE->db->get();

		$allowed_mods = array();

		if ($query->num_rows() == 0 AND ! $can_admin)
		{
			return lang('module_no_access');
		}

		foreach ($query->result_array() as $row)
		{
			$allowed_mods[] = strtolower($row['module_name']);
		}
		
		$display = '';
		ksort($modules);
		foreach ($modules as $module => $module_info)
		{
			if ( ! $can_admin)
			{
				if ( ! in_array($module, $allowed_mods))
				{
					continue;
				}
			}
			
			// Module Name
			$name = (lang(strtolower($module).'_module_name') != FALSE) ? lang(strtolower($module).'_module_name') : $module_info['name'];
			
			if (isset($installed_modules[$module]) AND $installed_modules[$module]['has_cp_backend'] == 'y')
			{
				$display .= '
				<tr class="'.alternator('odd','even').'">
					<td><a href="'.BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module='.strtolower($module).'"><strong>'.$name.'</strong></a></td>
				</tr>';
			}
		}
		
		return '
			<table>
				<tbody>'.$display.'</tbody>
			</table>
		';
	}
	

}
/* End of file wgt.biolerplate.php */
/* Location: /system/expressionengine/third_party/dashee/widgets/wgt.biolerplate.php */