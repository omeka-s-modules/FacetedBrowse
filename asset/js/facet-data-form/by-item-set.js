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

$(document).ready(function() {

$(document).on('click', '#by-item-set-show-all-item-sets', function(e) {
    const allItemSets = $('#by-item-set-all-item-sets');
    if (this.checked) {
        $.get(allItemSets.data('itemSetsUrl'), {
            category_query: $('#category-query').val()
        }, function(data) {
            if (data.length) {
                data.forEach(itemSet => {
                    allItemSets.append(`<tr><td style="width: 90%; padding: 0; border-bottom: 1px solid #dfdfdf;">${itemSet.label}</td><td style="width: 10%; padding: 0; border-bottom: 1px solid #dfdfdf;">${itemSet.item_count}</td></tr>`);
                });
            } else {
                allItemSets.append(`<tr><td>${Omeka.jsTranslate('There are no available item sets.')}</td></tr>`);
            }
        });
    } else {
        allItemSets.empty();
    }
});

});
