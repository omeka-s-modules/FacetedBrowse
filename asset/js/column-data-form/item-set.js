FacetedBrowse.registerColumnAddEditHandler('item_set', function() {});
FacetedBrowse.registerColumnSetHandler('item_set', function() {
    return {
        'max_item_sets': $('#item-set-max-item-sets').val()
    };
});
