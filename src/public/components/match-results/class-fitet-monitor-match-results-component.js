jQuery(function ($) {
    $('.fm-show-details-link').on('click', function (event) {
        $(event.target).closest('table').toggleClass("fm-show-details")
    });
});
