/**
 * Created by Ricardo on 20/11/2018.
 */
$(document).ready(function () {

    $('tbody .expandable').on('click', function(e) {

        let element = $(this);
        let wrapper = element.siblings('.details').find('.details-wrapper');

        wrapper.slideToggle({
            duration: 350,
            complete: () => {
                let visible = wrapper.is(':visible');
                wrapper
                    .closest('tr')
                    .find('.expandable i')
                    .removeClass('fa-chevron-' + (visible ? 'right' : 'down'))
                    .addClass('fa-chevron-' + (visible ? 'down' : 'right'));
            }
        });
    });
});
