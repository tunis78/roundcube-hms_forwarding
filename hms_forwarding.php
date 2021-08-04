<?php

/**
 * hMailServer Forwarding Plugin for Roundcube
 *
 * @version 1.2
 * @author Andreas Tunberg <andreas@tunberg.com>
 *
 * Copyright (C) 2018, Andreas Tunberg
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see http://www.gnu.org/licenses/.
 */

define('HMS_ERROR', 1);
define('HMS_CONNECT_ERROR', 2);
define('HMS_SUCCESS', 0);

/**
 * Change hMailServer forwarding plugin
 *
 * Plugin that adds functionality to change hMailServer forwarding messages.
 * It provides common functionality and user interface and supports
 * several backends to finally update the forwarding.
 *
 * For installation and configuration instructions please read the README file.
 *
 * @author Andreas Tunberg
 */
 
class hms_forwarding extends rcube_plugin
{
    public $task    = "settings";
    public $noframe = true;
    public $noajax  = true;
    private $rc;
    private $driver;

    function init()
    {
        
        $this->add_texts('localization/');
        $this->include_stylesheet($this->local_skin_path() . '/hms_forwarding.css');

        $this->add_hook('settings_actions', array($this, 'settings_actions'));

        $this->register_action('plugin.forwarding', array($this, 'forwarding'));
        $this->register_action('plugin.forwarding-save', array($this, 'forwarding_save'));
    }

    function settings_actions($args)
    {
        $args['actions'][] = array(
            'action' => 'plugin.forwarding',
            'class'  => 'forwarding',
            'label'  => 'forwarding',
            'title'  => 'editforwarding',
            'domain' => 'hms_forwarding'
        );

        return $args;
    }
    
    function forwarding_init()
    {
        $this->rc = rcube::get_instance();
        $this->load_config();
        $this->rc->output->set_pagetitle($this->gettext('editforwarding'));
    }

    function forwarding()
    {
        $this->forwarding_init();
        
        $this->register_handler('plugin.body', array($this, 'forwarding_form'));

        $this->rc->output->send('plugin');
    }

    function forwarding_save()
    {
        $this->forwarding_init();

        $dataToSave = array(
            'action'       => 'forwarding_save',
            'enabled'      => rcube_utils::get_input_value('_enabled', rcube_utils::INPUT_POST),
            'address'      => rcube_utils::get_input_value('_address', rcube_utils::INPUT_POST),
            'keeporiginal' => rcube_utils::get_input_value('_keeporiginal', rcube_utils::INPUT_POST),
        );

		// if disabled save all current data
		$currentData = $this->_load(array('action' => 'forwarding_load'));
		if(!$dataToSave['enabled'] && !$dataToSave['address'] && !$dataToSave['keeporiginal'])
		{
			$dataToSave['enabled'] = $currentData['enabled'];
			$dataToSave['address'] = $currentData['address'];
			$dataToSave['keeporiginal'] = $currentData['keeporiginal'];
		}

        if(!$dataToSave['address'])
            $dataToSave['enabled'] = null;

		if ($dataToSave['address'] && !rcube_utils::check_email($dataToSave['address']))
			$this->rc->output->command('display_message', $this->gettext('novalidemailaddress'), 'error');
		else {
        if (!($result = $this->_save($dataToSave))) {
            $this->rc->output->command('display_message', $this->gettext('successfullyupdated'), 'confirmation');
        }
        else {
            $this->rc->output->command('display_message', $result, 'error');
        }
		}		

        $this->register_handler('plugin.body', array($this, 'forwarding_form'));

        $this->rc->overwrite_action('plugin.forwarding');
        $this->rc->output->send('plugin');
    }

    function forwarding_form()
    {
		$config = rcmail::get_instance()->config;
		
		// check for excluded domains and addresses
		$disabled = false;
		$username = $this->rc->get_user_name();
		$domain = explode('@', $username);
		if (in_array($username, $config->get('hms_forwarding_excluded_addresses'))) {
			$disabled = true;
		}
		elseif (in_array($domain[1], $config->get('hms_forwarding_excluded_domains'))) {
			$disabled = true;
		}	
		
        $currentData = $this->_load(array('action' => 'forwarding_load'));

        if (!is_array($currentData)) {
            if ($currentData == HMS_CONNECT_ERROR) {
                $error = $this->gettext('loadconnecterror');
            }
            else {
                $error = $this->gettext('loaderror');
            }

            $this->rc->output->command('display_message', $error, 'error');
            return;
        }

		// check if forward address is distributionlist
		if ($config->get('hms_forwarding_exclude_distributionlists') && in_array($currentData['address'], $currentData['exludedaddresses'])) {
			$disabled = true;
		}

        $table = new html_table(array('cols' => 2, 'class' => 'propform'));

        $field_id = 'enabled';
        $input_enabled = new html_checkbox(array (
                'name'  => '_enabled',
				'disabled' => $disabled ? 'disabled' : '',
                'id'    => $field_id,
                'value' => 1
        ));

        $table->add('title', html::label($field_id, rcube::Q($this->gettext('enabled'))));
        $table->add(null, $input_enabled->show($currentData['enabled']));

        $field_id = 'address';
        $input_address = new html_inputfield(array (
				'type'      => 'email',
				'disabled' => $disabled ? 'disabled' : '',
                'name'      => '_address',
                'id'        => $field_id,
                'maxlength' => 192
        ));

        $table->add('title', html::label($field_id, rcube::Q($this->gettext('address'))));
        $table->add(null, $input_address->show($currentData['address']));

        $field_id = 'keeporiginal';
        $input_keeporiginal = new html_checkbox(array (
                'name'  => '_keeporiginal',
				'disabled' => $disabled ? 'disabled' : '',
                'id'    => $field_id,
                'value' => 1
        ));            
        $table->add('title', html::label($field_id, rcube::Q($this->gettext('keeporiginalmessage'))));
        $table->add(null, $input_keeporiginal->show($currentData['keeporiginal']));

        $submit_button = $this->rc->output->button(array(
                'command' => 'plugin.forwarding-save',
                'class'   => 'button mainaction submit',
                'label'   => 'save'
        ));

        $form = $this->rc->output->form_tag(array(
            'id'     => 'forwarding-form',
            'name'   => 'forwarding-form',
            'class'  => 'propform',
            'method' => 'post',
            'action' => './?_task=settings&_action=plugin.forwarding-save',
        ), $table->show());

        $out = html::div(array('id' => 'prefs-title', 'class' => 'boxtitle'), $this->gettext('editforwarding'))
            . html::div(array('class' => 'hms box formcontainer scroller'),
                html::div(array('class' => 'boxcontent formcontent'), $form)
            . html::div(array('class' => 'footerleft formbuttons'), $submit_button));

        $this->rc->output->add_gui_object('forwardingform', 'forwarding-form');
        $this->rc->output->add_label('hms_forwarding.novalidemailaddress');

        $this->include_script('hms_forwarding.js');

        return $out;
    }

    private function _load($data)
    {
        if (is_object($this->driver)) {
            $result = $this->driver->load($data);
        }
        elseif (!($result = $this->load_driver())){
            $result = $this->driver->load($data);
        }
        return $result;
    }

    private function _save($data, $response = false)
    {
        if (is_object($this->driver)) {
            $result = $this->driver->save($data);
        }
        elseif (!($result = $this->load_driver())){
            $result = $this->driver->save($data);
        }
        
        if ($response) return $result;

        switch ($result) {
            case HMS_SUCCESS:
                return;
            case HMS_CONNECT_ERROR:
                $reason = $this->gettext('updateconnecterror');
                break;
            case HMS_ERROR:
            default:
                $reason = $this->gettext('updateerror');
        }

        return $reason;
    }

    private function load_driver()
    {
        $config = rcmail::get_instance()->config;
        $driver = $config->get('hms_forwarding_driver', 'hmail');
        $class  = "rcube_{$driver}_forwarding";
        $file   = $this->home . "/drivers/$driver.php";

        if (!file_exists($file)) {
            rcube::raise_error(array(
                'code' => 600,
                'type' => 'php',
                'file' => __FILE__, 'line' => __LINE__,
                'message' => "hms_forwarding plugin: Unable to open driver file ($file)"
            ), true, false);
            return HMS_ERROR;
        }

        include_once $file;

        if (!class_exists($class, false) || !method_exists($class, 'save') || !method_exists($class, 'load')) {
            rcube::raise_error(array(
                'code' => 600,
                'type' => 'php',
                'file' => __FILE__, 'line' => __LINE__,
                'message' => "hms_forwarding plugin: Broken driver $driver"
            ), true, false);
            return $this->gettext('internalerror');
        }

        $this->driver = new $class;
    }
}
