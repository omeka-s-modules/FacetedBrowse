// Handle facet add/edit.
$(document).on('faceted-browse:facet-add-edit', '#facet-add-button, .facet-edit', function(e, type) {
    if ('resource_class' !== type) {
        return;
    }
    $('#resource-class-class-ids').chosen({
        include_group_label_in_selected: true
    });
});
// Handle facet set.
$(document).on('faceted-browse:facet-set', '#facet-set-button', function(e, type) {
    if ('resource_class' !== type) {
        return;
    }
    $(this).data('facet-data', {
        class_ids: $('#resource-class-class-ids').val()
    });
});
