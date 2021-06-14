FacetedBrowse.registerFacetAddEditHandler('item_set', function() {
    $('#item-set-item-set-ids').chosen({
        include_group_label_in_selected: true
    });
});
FacetedBrowse.registerFacetSetHandler('item_set', function() {
    return {
        item_set_ids: $('#item-set-item-set-ids').val()
    };
});
