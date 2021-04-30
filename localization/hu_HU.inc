<?php

$labels = array();

$labels['forwarding'] = 'Átirányítás';
$labels['editforwarding'] = 'Átirányítás szerkeztése';

$labels['enabled'] = 'Aktív';
$labels['address'] = 'Cím';
$labels['keeporiginalmessage'] = 'Az eredeti üzenet megtartása';

$messages = array();
$messages['novalidemailaddress'] = 'Nincs érvényes e-mail cím.';
$messages['successfullyupdated'] = 'Az átirányítás sikeresen frissült.';
$messages['internalerror'] = 'Hiba a kiválasztott művelettel.';
//$messages['connecterror'] = $messages['internalerror'] . ' Connection error.';
$messages['loaderror'] = 'Nem sikerült betölteni az átirányítást.';
$messages['loadconnecterror'] = $messages['loadinternalerror'] . ' Connection error.';
$messages['updateerror'] = 'Nem sikerült frissíteni a külső fiókot.';
$messages['updateconnecterror'] = $messages['updateerror'] . ' Connection error.';
