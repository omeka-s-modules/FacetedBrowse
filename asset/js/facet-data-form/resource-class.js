FacetedBrowse.registerFacetAddEditHandler('resource_class', function() {
    const selectTypeSelect = $('#resource-class-select-type');
    const truncateResourceClassesInput = $('#resource-class-truncate-resource-classes');
    const resourceClassSelect = $('#resource-class-class-ids');
    selectTypeSelect.chosen({
        disable_search: true,
    });
    resourceClassSelect.chosen({
        include_group_label_in_selected: true
    });
    switch (selectTypeSelect.val()) {
        case 'single_select':
            truncateResourceClassesInput.closest('.field').hide();
            break;
        case 'single_list':
        case 'multiple_list':
            break;
    }
});
FacetedBrowse.registerFacetSetHandler('resource_class', function() {
    return {
        select_type: $('#resource-class-select-type').val(),
        truncate_resource_classes: $('#resource-class-truncate-resource-classes').val(),
        class_ids: $('#resource-class-class-ids').val()
    };
});

$(document).ready(function() {

// Handle behavior during selecting a select type.
$(document).on('change', '#resource-class-select-type', function (e) {
    const thisSelect = $(this);
    const truncateResourceClassesInput = $('#resource-class-truncate-resource-classes');
    switch (thisSelect.val()) {
        case 'single_select':
            truncateResourceClassesInput.closest('.field').hide();
            break;
        case 'single_list':
        case 'multiple_list':
            truncateResourceClassesInput.closest('.field').show();
            break;
    }
});

});
