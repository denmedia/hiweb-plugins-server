/**
 * Created by hiweb on 13.06.2016.
 */

jQuery(document).ready(function ($) {
    $('#hw_plugins_server_options').on('click', '#hw_plugins_server_status_toggle', function (e) {
        e.preventDefault();
        $.ajax({
            url: ajaxurl + '?action=hw_plugins_server_status_toggle',
            success: function (data) {
                $('#hw_plugins_server_options').html(data);
            },
            error: function (data) {
                console.error(data);
            }
        });
    });

    $('#hw_plugins_server_options').on('click', '#hw_plugins_server_remote_url_update', function (e) {
        e.preventDefault();
        $('#hw_plugins_server_remote_url_update').html('...save...');
        $.ajax({
            url: ajaxurl + '?action=hw_plugins_server_remote_url_update',
            type: 'post',
            data: {url: $('[name="hw_plugins_server_remote_url"]').val()},
            dataType: 'json',
            success: function (data) {
                if (typeof data != 'object' || !data.hasOwnProperty('result')) {
                    console.warn(data);
                    $('#hw_plugins_server_remote_url_update').html('ERROR').addClass('button-disabled').attr('disabled','disabled');
                } else {
                    $('#hw_plugins_server_remote_url_update').html('DONE! REPEAT UPDATE').removeClass('button-primary');
                }

            },
            error: function (data) {
                console.error(data);
            }
        });
    });

    $('#hw_plugins_server_options').on('click', '#hw_plugins_server_kickback_status_toggle', function (e) {
        e.preventDefault();
        $('#hw_plugins_kickback_status_toggle').html('...save...');
        $.ajax({
            url: ajaxurl + '?action=hw_plugins_server_kickback_status_toggle',
            success: function (data) {
                $('#hw_plugins_server_options').html(data);
            },
            error: function (data) {
                console.error(data);
            }
        });
    });
});