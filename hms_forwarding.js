/**
 * hms_forwarding plugin script
 *
 * @licstart  The following is the entire license notice for the
 * JavaScript code in this file.
 *
 * Copyright (c) 2017, Andreas Tunberg <andreas@tunberg.com>
 *
 * The JavaScript code in this page is free software: you can redistribute it
 * and/or modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation, either version 3 of
 * the License, or (at your option) any later version.
 *
 * @licend  The above is the entire license notice
 * for the JavaScript code in this file.
 */
 
window.rcmail && rcmail.addEventListener('init', function(evt) {

    rcmail.register_command('plugin.forwarding-save', function() {
        var email = rcmail.gui_objects.forwardingform._address.value;
        if (email.length > 0 && !rcube_check_email(email, true)) {
            rcmail.display_message(rcmail.get_label('hms_forwarding.novalidemailaddress'), 'error');
            return;
        }
        rcmail.set_busy(true, 'loading');
        rcmail.gui_objects.forwardingform.submit();
    },true);
});