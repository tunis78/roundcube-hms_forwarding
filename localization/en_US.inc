<?php

$labels = array();

$labels['forwarding'] = 'Forwarding';
$labels['editforwarding'] = 'Edit forwarding';

$labels['enabled'] = 'Enabled';
$labels['address'] = 'Address';
$labels['keeporiginalmessage'] = 'Keep original message';

$messages = array();
$messages['novalidemailaddress'] = 'No valid email address.';
$messages['successfullyupdated'] = 'Forwarding has successfully update.';
$messages['internalerror'] = 'Error with selected action.';
//$messages['connecterror'] = $messages['internalerror'] . ' Connection error.';
$messages['loaderror'] = 'Could not load forwarding.';
$messages['loadconnecterror'] = $messages['loadinternalerror'] . ' Connection error.';
$messages['updateerror'] = 'Could not update external account.';
$messages['updateconnecterror'] = $messages['updateerror'] . ' Connection error.';
