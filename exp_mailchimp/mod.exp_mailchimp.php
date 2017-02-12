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

class Exp_mailchimp { 


	public function __construct()
	{
		ee()->load->library('template');
		ee()->load->model('exp_mailchimp_model');
		ee()->lang->loadfile('exp_mailchimp');
	}
	
	public function subscribe()
	{
		// load
		ee()->load->helper('form');

		// check for errors
		$mailchimp_error = ee()->exp_mailchimp_model->error;
		if($mailchimp_error != NULL)
			return ''.$mailchimp_error.'';


		// check for a message
		$response      =  $this->_get_msg();
		$show_response = ee()->session->flashdata('mailchimp_show_response');
		if($response != NULL)
			return ($show_response == 'yes') ? $response : '';

		// vars
		$params          = ee()->TMPL->tagparams;
		$id     		 = ee()->TMPL->fetch_param('list_id', NULL);
		$inline_response = ee()->TMPL->fetch_param('inline_response', 'yes'); 
		$return 		 = ee()->TMPL->fetch_param('return', $_SERVER['REQUEST_URI']);

		unset($params['id']);
		unset($params['inline_response']);
		unset($params['return']);

		// check if we have an id
		if($id == NULL)
			return lang('missing_list_id');

		// encrypt the id for safety
		$id = ee('Encrypt')->encode($id);
		
		// get the form data
		$protocol         = (isset($_SERVER['HTTPS'])) ? 'https' : 'http';
		$action_id        =  ee()->functions->fetch_action_id('Exp_mailchimp', 'do_subscribe');
		$params['action'] = $protocol.'://'.$_SERVER['HTTP_HOST'].'/?ACT='.$action_id.''; 
		
		// set the form
		$form  = ee()->functions->form_declaration($params);
		$form .= form_hidden('list', $id);
		$form .= form_hidden('return', $return);
		$form .= form_hidden('inline_response', $inline_response);
		$form .= ee()->TMPL->tagdata;
		$form .= form_close();
		
		return $form;	
	}


	private function _get_msg($slug = 'no')
	{
		// message
		$msg = NULL;

		// error
		$error   = ee()->session->flashdata('mailchimp_error');
		if($error != NULL)
			$msg = $error;

		// success
		$success = ee()->session->flashdata('mailchimp_success');
		if($success != NULL)
			$msg = $success;

		// check the message
		if($msg != NULL)
			return ($slug == 'no') ? lang($msg) : $msg; 

		return NULL;
	}



	public function response()
	{
		$slug    = ee()->TMPL->fetch_param('slug', 'no');
		$tagdata = ee()->TMPL->tagdata;
		$msg     = $this->_get_msg($slug);

		if(!empty($msg))
			return ee()->TMPL->parse_variables($tagdata, [['result' => $msg]]);

		return '';
	}



	// the actions
	public function do_subscribe()
	{	
		// get the return url
		$return = (!empty($_POST['return'])) ? $_POST['return'] : '/';
		
		// validate
		$rules = array
		(
			'list'   => 'required|minLength[6]',
		 	'name'   => 'required',
		 	'email'  => 'required|email',
		);		
		$result = ee('Validation')->make($rules)->validate($_POST);
		if($result->isValid())
		{
			// get the fields
			$id    = ee('Encrypt')->decode($_POST['list']);
			$name  = $_POST['name'];
			$email = $_POST['email'];

			// try to subscribe
			$subscribed = ee()->exp_mailchimp_model->subscribe($id, $name, $email);
			if($subscribed['success'])
			{
				ee()->session->set_flashdata('mailchimp_success', $subscribed['message']);
			}
			else
			{
				ee()->session->set_flashdata('mailchimp_error', $subscribed['message']);
			}
		}
		else
		{
			// set the error
			ee()->session->set_flashdata('mailchimp_error', 'no_valid_form_data');
		}

		// set the response
		ee()->session->set_flashdata('mailchimp_show_response', $_POST['inline_response']);

		// redirect to a specified page
		ee()->functions->redirect($return);
	}	









	
}