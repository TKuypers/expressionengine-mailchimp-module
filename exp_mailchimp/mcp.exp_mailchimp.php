<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
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
use EllisLab\ExpressionEngine\Library\CP\Table;

class Exp_mailchimp_mcp { 
    
	
	private $right_nav;
	
	
	public function __construct()
    {
		//Load
		ee()->load->model('exp_mailchimp_model');
		ee()->load->helper('array');
    }
	
	
	
	public function index($message = '')
	{
		// final view variables we need to render the form
		$url = ee('CP/URL', 'addons/settings/exp_mailchimp');
		$vars = array
		(
			'base_url' 		=> ee('CP/URL', 'addons/settings/exp_mailchimp/'),
			'cp_page_title' => lang('lists'),
		);	

		// check if we are connected to mailchimp
		$has_error = (ee()->exp_mailchimp_model->error != NULL) ? TRUE : FALSE;
		if(!$has_error)
		{
			// check if we have settings
			$has_settings = ee()->exp_mailchimp_model->has_settings();
			if(!$has_settings)
				ee()->functions->redirect(ee('CP/URL', 'addons/settings/exp_mailchimp/settings')->compile());

			// the form
			$data  = array();

			// loop trough the list
			$lists = ee()->exp_mailchimp_model->get_lists();
			foreach($lists['lists'] as $list)
			{
				$data[] = array
				(
					'list' 		 => $list['name'],
					'id'         => $list['id'],
					'subcribers' => $list['stats']['member_count']
				);
			}

			// set the data
			$vars['data'] = $data;
		}
		else
		{
			$vars['error'] = ee()->exp_mailchimp_model->error;
		}

		// set the template
		return array
		(
		  	'body'       => ee('View')->make('exp_mailchimp:list')->render($vars),
			'heading'    => lang('list_settings'),
		);

	}
	
	
	
	
    
	public function settings()
	{
		// check if we have a result
		$rules = array
		(
		 	'api_key' => 'required|minLength[10]',
		);
		$result = ee('Validation')->make($rules)->validate($_POST);
		if($result->isValid())
		{
			$configuration_id = 1;
			$api_key          = ee()->input->post('api_key');

			// save the values
			$save = ee()->exp_mailchimp_model->save_settings($configuration_id, $api_key);
			if($save !== FALSE)
			{
				ee('CP/Alert')->makeBanner('success-message')
					->asSuccess()
					->withTitle(lang('saved'))
					->addToBody(lang('form_saved'))
					->defer();

				ee()->functions->redirect(ee('CP/URL', 'addons/settings/exp_mailchimp/')->compile());
				exit;
			}
		}
		else
		{
			$api_key = '';

			//Get the settings
			$settings = ee()->exp_mailchimp_model->get_settings();
			if($settings != NULL)
			{
				$api_key = $settings->api_key;
			}

			$form = array
			(
				array(
					array(
						'title'  => 'api_key',
						'desc'   => 'api_key_desc',
						'fields' => array(
							'api_key' => array(
								'type'     => 'text',
								'value'    => $api_key,
								'required' => TRUE
							)
						),
					),
				)
			);

			// final view variables we need to render the form
			$vars = array('sections' => $form);
			$vars += array
			(
				'base_url' 			    => ee('CP/URL', 'addons/settings/exp_mailchimp/settings'),
				'cp_page_title' 		=> lang('api_settings'),
				'save_btn_text' 		=> 'btn_save_form',
				'save_btn_text_working' => 'btn_saving'
			);	

			// add the error to the form
			if($_POST)
				$vars['errors'] = $result;

			ee()->cp->add_js_script(array('file' => array('cp/form_group')));

			return array
			(
			  	'body'       => ee('View')->make('exp_mailchimp:form')->render($vars),
			  	'breadcrumb' => array(ee('CP/URL', 'addons/settings/exp_mailchimp/')->compile() => lang('exp_mailchimp_module_name')),
				'heading'    => lang('mailchimp_settings')
			);
		}
	}	
}
