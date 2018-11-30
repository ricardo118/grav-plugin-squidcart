/**
 * Created by Ricardo on 20/11/2018.
 */
$(document).ready(function () {
    let changes = [];

    $('tbody .expandable').on('click', function(e) {

        let element = $(this);
        let wrapper = element.siblings('.details').find('.details-wrapper');

        wrapper.slideToggle({
            duration: 250,
            complete: () => {
                let visible = wrapper.is(':visible');
                wrapper
                    .closest('tr')
                    .find('.expandable i')
                    .removeClass(visible ? '' : 'spin')
                    .addClass(visible ? 'spin' : '');
            }
        });
    });

    const sortable = document.getElementById('sortable');
    if (sortable) {
        Sortable.create(sortable, {
            group: sortable,
            animation: 100,
            onSort: function (evt) {
                changesDetected('updateProduct');
            },
        });
    }

    function changesDetected(change) {
        if (changes.indexOf(change) === -1) changes.push(change);
        if (changes.length) {
            $('#modal-unsaved').show();
            console.log(changes);
        }
    }

    function onSaveChanges() {
        product_images

    }
});
