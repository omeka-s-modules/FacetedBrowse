FacetedBrowse.registerFacetAddEditHandler('by_class', function() {
    $('#by-class-class-ids').chosen({
        include_group_label_in_selected: true
    });
});
FacetedBrowse.registerFacetSetHandler('by_class', function() {
    return {
        class_ids: $('#by-class-class-ids').val()
    };
});
