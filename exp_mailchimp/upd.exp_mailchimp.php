<?php if(!defined('BASEPATH')) exit('No direct script access allowed');
/**
 * ExpressionEngine 3.x MailChimp subscription module
 *
 * @package     ExpressionEngine
 * @module      Expertees MailChimp
 * @author      Ties Kuypers
 * @copyright   Copyright (c) 2017 - Ties Kuypers
 * @link        http://expertees.nl/expressionengine-mailchimp-module
 * @license 
 *
 * Copyright (c) 2017, Expertees webdevelopment
 * All rights reserved.
 *
 * This source is commercial software. Use of this software requires a
 * site license for each domain it is used on. Use of this software or any
 * of its source code without express written permission in the form of
 * a purchased commercial or other license is prohibited.
 *
 * THIS CODE AND INFORMATION ARE PROVIDED "AS IS" WITHOUT WARRANTY OF ANY
 * KIND, EITHER EXPRESSED OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND/OR FITNESS FOR A
 * PARTICULAR PURPOSE.
 *
 * As part of the license agreement for this software, all modifications
 * to this source must be submitted to the original author for review and
 * possible inclusion in future releases. No compensation will be provided
 * for patches, although where possible we will attribute each contribution
 * in file revision notes. Submitting such modifications constitutes
 * assignment of copyright to the original author (for such modifications. If you do not wish to assign
 * copyright to the original author, your license to  use and modify this
 * source is null and void. Use of this software constitutes your agreement
 * to this clause.
 */
class Exp_mailchimp_upd 
{ 
    var $version      = '1.0.0'; 
    var $name         = 'Exp_mailchimp';
	var $config_table = 'Exp_mailchimp';
	 
	 
    public function __construct()
    {
		ee()->load->dbforge();
    }
	
	// --------------------------------------------------------------------

	/**
	* Module Installer
	*
	* @access	public
	* @param    none
	* @return	bool
    */
	
	//@todo: add the tables
	function install() 
	{
		//Install the module
		$fields  = array('module_name'        => $this->name,
						 'module_version'     => $this->version,
						 'has_cp_backend'     => 'y',
						 'has_publish_fields' => 'n');
	
		// insert the module in database
		ee()->db->insert('modules', $fields); 
		
		// add the tables
		ee()->db->query(
			"CREATE TABLE IF NOT EXISTS `".ee()->db->dbprefix('mailchimp_configuration')."` (
			  `configuration_id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
			  `site_id` int(11) NOT NULL,
			  `api_key` varchar(255) DEFAULT NULL
			) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=0;");
		

		// insert the e-mail action
		$data = array
		(
			'class'     => 'Exp_mailchimp',
			'method'    => 'do_subscribe'
		);
		ee()->db->insert('actions', $data);

		return TRUE;

	}
    
    
	// --------------------------------------------------------------------
	/**
	* Module Updater
	*
	* @access	public
	* @param    none
	* @return	bool
    */
	function update($current = '')
	{
		//Check the current version
		if($current < $this->version)
		{
			//Handle updates
		}
		return TRUE; 
    }

	
	// --------------------------------------------------------------------
	/**
	* Module Uninstaller
	*
	* @access	public
	* @param    none
	* @return	bool
    */
	function uninstall()
	{ 
		//Load the class
		ee()->load->dbforge();
	
		//Remove the tables
    	ee()->dbforge->drop_table('exp_mailchimp_configuration');
	
		//Remove the module from the table
		ee()->db->query("DELETE FROM exp_modules WHERE module_name='$this->name'");

		return TRUE;
    }

}