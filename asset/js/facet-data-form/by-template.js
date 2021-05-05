FacetedBrowse.registerFacetAddEditHandler('by_template', function() {
    $('#by-template-template-ids').chosen({
        include_group_label_in_selected: true
    });
});
FacetedBrowse.registerFacetSetHandler('by_template', function() {
    return {
        template_ids: $('#by-template-template-ids').val()
    };
});
