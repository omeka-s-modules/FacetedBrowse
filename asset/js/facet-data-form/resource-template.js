FacetedBrowse.registerFacetAddEditHandler('resource_template', function() {
    $('#resource-template-template-ids').chosen({
        include_group_label_in_selected: true
    });
});
FacetedBrowse.registerFacetSetHandler('resource_template', function() {
    return {
        select_type: $('#resource-template-select-type').val(),
        template_ids: $('#resource-template-template-ids').val()
    };
});
