$(document).ready(function() {

const categories = $('#categories');

// Enable category sorting.
new Sortable(categories[0], {draggable: '.category', handle: '.sortable-handle'});

$('.category').each(function() {
    const categoryId = $(this).find('.category-id').val();
    $(`.category-add[data-category-id="${categoryId}"]`).hide();
});

categories.on('click', '.category-remove', function(e) {
    e.preventDefault();
    const thisButton = $(this);
    const category = thisButton.closest('.category');
    category.find(':input').prop('disabled', true);
    category.addClass('delete');
    category.find('.category-restore').show();
    thisButton.hide();
});
categories.on('click', '.category-restore', function(e) {
    e.preventDefault();
    const thisButton = $(this);
    const category = thisButton.closest('.category');
    category.find(':input').prop('disabled', false);
    category.removeClass('delete');
    category.find('.category-remove').show();
    thisButton.hide();
});
$('.category-add').on('click', function(e) {
    const thisCategory = $(this);
    $.post(categories.data('categoryRowUrl'), {
        category_id: thisCategory.data('categoryId'),
        index: $('.category').length
    }, function(html) {
        thisCategory.hide();
        categories.append($($.parseHTML(html)));
    });
});

});
