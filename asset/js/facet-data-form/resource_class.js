// Handle facet add/edit.
$(document).on('faceted-browse:facet-add-edit', '#facet-add-button, .facet-edit', function(e, type) {
    if ('resource_class' !== type) {
        return;
    }
});
// Handle facet set.
$(document).on('faceted-browse:facet-set', '#facet-set-button', function(e, type) {
    if ('resource_class' !== type) {
        return;
    }
    $(this).data('facet-data', {});
});
