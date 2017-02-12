<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
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
require PATH_THIRD.'exp_mailchimp/libraries/MailChimp.php';

use \DrewM\MailChimp\MailChimp;

class Exp_mailchimp_model extends CI_Model {
    
	
	private $mailchimp = NULL;
	private $settings  = NULL;
	
	public $error      = '';//NULL;

	public function __construct()
	{
		//Load the settings
		$this->settings = $this->get_settings();

		// check if we have settings
		if($this->settings != NULL && $this->mailchimp == NULL)
		{
			//Start the API
			$api_key = $this->settings->api_key;
			try
			{
				// set the api key
				$this->mailchimp = new MailChimp($api_key);	

				// check for localhost
				if(isset($_SERVER['HTTP_HOST']))
				{
					if(stripos($_SERVER['HTTP_HOST'], '.dev') !== FALSE)
						$this->mailchimp->verify_ssl = FALSE;
				}
			} 
			catch(Exception $e)
			{
				$this->error = $e->getMessage();
			}	
		}
	}
	
	
	
	public function has_settings()
	{
		$site_id = ee()->config->item('site_id');
		
		$query  = ee()->db->select('COUNT(configuration_id) AS total')->get('mailchimp_configuration');
		$result = $query->row(); 
		
		if($result->total == 1)
		{
			return TRUE;
		}
		
		return FALSE;
	}



	public function get_lists()
	{
		$lists = $this->mailchimp->get('lists');	
		if($lists)
		{
			return $lists;
		}

		return NULL;
	}
	
	
	public function subscribe($list_id, $name, $email)
	{
		$name = $this->_slice_name($name);
		$data = array
		(
			'email_address' => $email,
			'merge_fields'  => ['FNAME' => $name['fname'], 'LNAME' => $name['lname']],
            'status'        => 'subscribed',
		);

		$subscribe = $this->mailchimp->post("lists/$list_id/members", $data);
		switch($subscribe['status'])
		{
			case  'subscribed':
				return ['success' => TRUE, 'message' => 'successfully_subscribed'];
			  break;

			default:
				$message = ($subscribe['title'] == 'Member Exists') ? 'already_subscribed' : 'could_not_subscribe';
				return ['success' => FALSE, 'message' => $message];
			  break;
		}
	}
	
	

	private function _slice_name($name)
	{
		$parts = explode(' ', $name);
		$fname = reset(($parts));
		$lname = implode(' ', array_slice($parts, 1));

		return ['fname' => $fname, 'lname' => $lname];
	}	
	
	
	public function get_settings()
	{
		$site_id = ee()->config->item('site_id');
		$query   = ee()->db->where('site_id', $site_id)->get('mailchimp_configuration');
		
		if($query->num_rows() > 0)
		{
			$result = $query->row();
			
			return $result;	
		}
		
		return NULL;
	}
	
	
	
	
	public function save_settings($configuration_id = 0, $api_key)
	{
		//Set the data
		$site_id = ee()->config->item('site_id');
		$data = array
		(
			'site_id'		   => $site_id,
			'configuration_id' => $configuration_id,
			'api_key'          => $api_key,
		);
		
		//Go find a configuration row
		$query = ee()->db->select('configuration_id')->where('site_id', $site_id)->get('mailchimp_configuration');

		//Check if we want to update or save
		if($query->num_rows() == 0) //Insert
		{
			//Insert the row
			ee()->db->insert('mailchimp_configuration', $data);
			
			return ee()->db->insert_id();
		}
		else //Update
		{
			//Where
			$configuration_id = $query->row()->configuration_id;
			$where            = array('site_id' => $site_id, 'configuration_id' => $configuration_id);
			
			//Save into the database
			if(ee()->db->where($where)->update('mailchimp_configuration', $data))
			{
				return $configuration_id;	
			}
		}
		
		return FALSE;
	}			
}