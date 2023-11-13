FacetedBrowse.registerFacetAddEditHandler('resource_template', function() {
    const selectTypeSelect = $('#resource-template-select-type');
    const truncateResourceTemplatesInput = $('#resource-template-truncate-resource-templates');
    const resourceTemplateSelect = $('#resource-template-template-ids');
    selectTypeSelect.chosen({
        disable_search: true,
    });
    resourceTemplateSelect.chosen({
        include_group_label_in_selected: true
    });
    switch (selectTypeSelect.val()) {
        case 'single_select':
            truncateResourceTemplatesInput.closest('.field').hide();
            break;
        case 'single_list':
        case 'multiple_list':
            break;
    }
});
FacetedBrowse.registerFacetSetHandler('resource_template', function() {
    return {
        select_type: $('#resource-template-select-type').val(),
        truncate_resource_templates: $('#resource-template-truncate-resource-templates').val(),
        template_ids: $('#resource-template-template-ids').val()
    };
});

$(document).ready(function() {

// Handle behavior during selecting a select type.
$(document).on('change', '#resource-template-select-type', function (e) {
    const thisSelect = $(this);
    const truncateResourceTemplatesInput = $('#resource-template-truncate-resource-templates');
    switch (thisSelect.val()) {
        case 'single_select':
            truncateResourceTemplatesInput.closest('.field').hide();
            break;
        case 'single_list':
        case 'multiple_list':
            truncateResourceTemplatesInput.closest('.field').show();
            break;
    }
});

});
