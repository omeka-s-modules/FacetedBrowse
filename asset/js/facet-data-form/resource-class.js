FacetedBrowse.registerFacetAddEditHandler('resource_class', function() {
    $('#resource-class-class-ids').chosen({
        include_group_label_in_selected: true
    });
});
FacetedBrowse.registerFacetSetHandler('resource_class', function() {
    return {
        select_type: $('#resource-class-select-type').val(),
        class_ids: $('#resource-class-class-ids').val()
    };
});
