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

    // var pjax = new Pjax({
    //     elements: "a", // default is "a[href], form[action]"
    //     selectors: ["#squidcart-content"],
    //     switches: {
    //         "#squidcart-content": Pjax.switches.sideBySide
    //     },
    //     switchesOptions: {
    //         "#squidcart-content": {
    //             classNames: {
    //                 remove: "animated",
    //                 add: "animated",
    //                 backward: "fadeIn",
    //                 forward: "fadeOut"
    //             },
    //             callbacks: {
    //                 removeElement: function () {
    //                     initSortable()
    //                 }
    //             }
    //         }
    //     }
    // });

    $('[data-action]').on('click', function (e) {
        e.preventDefault();
        let target = $(this);
        let action = target.data('action');
        let id = target.data('id');
        let href = target.attr('href');
        let url = `${href}/action:squidcart.${action}/id:${id}`;
        console.log(url);
        $.ajax({
            url: url,
        }).done(function() {

        }).fail(function() {
            alert( "error" );
        });
    });
});



