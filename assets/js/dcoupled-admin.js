/**
 * Dcoupled Admin scripts
 *
 * @author Louis Thai <louis.thai@finaldream.de>
 * @since 28.11.2017
 */

jQuery(document).ready(function($) {

    var ajaxMessage = function(message, error) {
        var marked = $('.wrap .wp-header-end');
        var type = (error) ? 'error' : 'updated';
        var msg = $('<div class="' + type + '"><p><strong>' + message + '</strong></p></div>');

        marked.after(msg);

        setTimeout(function() {
            msg.fadeOut().remove();
        }, 5000);
    };

    var callback = function(res) {
        ajaxMessage(res.data || 'Error occurred', !res.success);

        $('.dcoupled-clear-cache').removeAttr('disabled');
        $('.dcoupled-clear-cache').next('.spinner').removeClass('is-active');
    };

    $('.dcoupled-clear-cache').on('click', function(e) {
        e.preventDefault();

        $(this).attr('disabled', true);
        $(this).next('.spinner').addClass('is-active');

        $.ajax({
            type: 'POST',
            url: ajaxurl,
            data: {
                action: $(this).data('action'),
                post_id: $(this).data('post-id'),
            },
            success: callback,
            error: callback
        });
    });
});
