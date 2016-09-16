/**
 * Created by hiweb on 13.06.2016.
 */

jQuery(document).ready(function ($) {
    $(document).on('click', '[data-click]', function (e) {
        e.preventDefault();
        var plugin = $(this).closest('[data-plugin]').attr('data-plugin');
        var action = $(this).attr('data-click');
        $.ajax({
            url: ajaxurl + '?action=hw_plugins_server_host_action',
            type: 'post',
            data: {plugin: plugin, do: action},
            dataType: 'json',
            success: function (data) {
                if (data.hasOwnProperty('html')) {
                    $('.wrap')[0].outerHTML = data.html;
                }
            },
            error: function (data) {
                console.error(data);
            }
        });
    });
});