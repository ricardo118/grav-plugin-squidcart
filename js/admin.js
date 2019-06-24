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

    initSortable();

    function initSortable () {
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
    }

    window.onbeforeunload = function() {
        $('#modal-unsaved').addClass('shake');
        if (changes.length) {
            // return "Are you sure you want to navigate away?";
        }
    };

    $('[data-modal-unsaved=save]').on('click', function (e) {
        console.log('save');
        $('#modal-unsaved').addClass('slideOutDown');
        setTimeout(function(){
            $('.modal-unsaved-wrapper').hide();
            $('#modal-unsaved').removeClass('slideOutDown').removeClass('shake');
        }, 800);
    });

    function changesDetected(change) {
        if (changes.indexOf(change) === -1) changes.push(change);
        if (changes.length) {
            $('.modal-unsaved-wrapper').show();
            console.log(changes);
        }
    }

    function onSaveChanges() {

    }

    $('[data-action]').on('click', function (e) {
        e.preventDefault();
        const target = $(this);
        const href = target.attr('href');
        const data = target.data();
        const action = target.data('action');
        const icon = target.find('.fa');
        let url = `${href}/action:squidcart.${action}/`;
        delete data.action;

        icon.addClass('fa-spinner');

        $.each(data, function (key, val) {
            const param = `${key}:${val}/`;
            url = url + param;
        });
        console.log(url);
        $.ajax({
            url: url,
        }).done(function(data) {
            showActionModal(data);
            updateHTML(action, target);
        }).fail(function(data) {
            showActionModal(data);
        }).complete(function () {
            icon.removeClass('fa-spinner');
        });
    });

    $('[data-modal-dismiss]').on('click', function (e) {
        const wrapper = $(this).closest('.modal-action-wrapper');
        const modal = wrapper.find('.modal');

        modal.addClass('slideOutDown');
        setTimeout(function(){
            wrapper.addClass('hide');
            modal.removeClass('slideOutDown');
        }, 700);
    });

    function showActionModal(data) {
        const wrapper = $('.modal-action-wrapper');
        const modal = wrapper.find('.modal');
        const modal_body = modal.find('[data-modal-message]');

        wrapper.addClass(data.status).removeClass('hide');
        modal_body.text(data.message);

        if (data.status === 'success') {
            setTimeout(function(){
                modal.addClass('slideOutDown');
                setTimeout(function(){
                    wrapper.addClass('hide');
                    modal.removeClass('slideOutDown');
                }, 700);
            }, 4000);
        }
    }

    function updateHTML(data_action, target) {
        const action = data_action.split('.')[0];

        switch (action) {
            case 'delete':
                target.closest('[data-action-delete]').remove();
                break;
            case 'edit':
                console.log('updated');
                break;
            default:
                console.log(action);
                break;
        }
    }
});



