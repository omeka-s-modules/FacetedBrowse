FacetedBrowse.registerFacetAddEditHandler('by_item_set', function() {
    $('#by-item-set-item-set-ids').chosen({
        include_group_label_in_selected: true
    });
});
FacetedBrowse.registerFacetSetHandler('by_item_set', function() {
    return {
        item_set_ids: $('#by-item-set-item-set-ids').val()
    };
});
