FacetedBrowse.registerFacetAddEditHandler('item_set', function() {
    const selectTypeSelect = $('#item-set-select-type');
    const truncateItemSetsInput = $('#item-set-truncate-item-sets');
    const itemSetSelect = $('#item-set-item-set-ids');
    selectTypeSelect.chosen({
        disable_search: true,
    });
    itemSetSelect.chosen({
        include_group_label_in_selected: true
    });
    switch (selectTypeSelect.val()) {
        case 'single_select':
            truncateItemSetsInput.closest('.field').hide();
            break;
        case 'single_list':
        case 'multiple_list':
            break;
    }
});
FacetedBrowse.registerFacetSetHandler('item_set', function() {
    return {
        select_type: $('#item-set-select-type').val(),
        truncate_item_sets: $('#item-set-truncate-item-sets').val(),
        item_set_ids: $('#item-set-item-set-ids').val()
    };
});

$(document).ready(function() {

// Handle behavior during selecting a select type.
$(document).on('change', '#item-set-select-type', function (e) {
    const thisSelect = $(this);
    const truncateItemSetsInput = $('#item-set-truncate-item-sets');
    switch (thisSelect.val()) {
         case 'single_select':
            truncateItemSetsInput.closest('.field').hide();
            break;
        case 'single_list':
        case 'multiple_list':
            truncateItemSetsInput.closest('.field').show();
            break;
    }
});

});
