$(document).ready(function() {

const categories = $('#categories');

if (categories.length) {
    // Enable category sorting.
    new Sortable(categories[0], {draggable: '.category', handle: '.sortable-handle'});
}

});
