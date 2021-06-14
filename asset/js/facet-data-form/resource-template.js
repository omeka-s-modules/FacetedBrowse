FacetedBrowse.registerFacetAddEditHandler('resource_template', function() {
    $('#resource-template-template-ids').chosen({
        include_group_label_in_selected: true
    });
});
FacetedBrowse.registerFacetSetHandler('resource_template', function() {
    return {
        template_ids: $('#resource-template-template-ids').val()
    };
});
