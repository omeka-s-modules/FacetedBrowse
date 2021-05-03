$(document).ready(function() {

const categories = $('#categories');
const cateogorySelect = $('#category-select');
const categoryAddButton = $('#category-add-button');

// Disable category selections if they're already assigned to the page.
cateogorySelect.find('option').each(function() {
    const thisOption = $(this);
    if ($(`input.category-id[value="${thisOption.val()}"]`).length) {
        thisOption.prop('disabled', true);
    }
});

// Enable category sorting.
new Sortable(categories[0], {draggable: '.category', handle: '.sortable-handle'});

// Handle cateogry remove button.
categories.on('click', '.category-remove', function(e) {
    e.preventDefault();
    const thisButton = $(this);
    const category = thisButton.closest('.category');
    category.find(':input').prop('disabled', true);
    category.addClass('delete');
    category.find('.category-restore').show();
    thisButton.hide();
});
// Handle category restore button.
categories.on('click', '.category-restore', function(e) {
    e.preventDefault();
    const thisButton = $(this);
    const category = thisButton.closest('.category');
    category.find(':input').prop('disabled', false);
    category.removeClass('delete');
    category.find('.category-remove').show();
    thisButton.hide();
});
// Handle category select.
cateogorySelect.on('change', function(e) {
    categoryAddButton.prop('disabled', ('' === $(this).val()) ? true : false);
});
// Handle category add button.
categoryAddButton.on('click', function(e) {
    const thisButton = $(this);
    $.post(categories.data('categoryRowUrl'), {
        category_id: cateogorySelect.val(),
        index: $('.category').length
    }, function(html) {
        categories.append(html);
        cateogorySelect.find(':selected').prop('disabled', true);
        cateogorySelect.val('');
    });
});

});
