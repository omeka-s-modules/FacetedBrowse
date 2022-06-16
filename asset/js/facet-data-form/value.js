FacetedBrowse.registerFacetAddEditHandler('value', function() {
    const propertyIdSelect = $('#value-property-id');
    const queryTypeSelect = $('#value-query-type');
    const selectTypeSelect = $('#value-select-type');
    const truncateValuesInput = $('#value-truncate-values');
    const valuesTextarea = $('#value-values');
    const showAllContainer = $('#show-all-container');
    propertyIdSelect.chosen({
        allow_single_deselect: true,
    });
    queryTypeSelect.chosen({
        disable_search: true,
    });
    selectTypeSelect.chosen({
        disable_search: true,
    });
    if (['ex', 'nex'].includes(queryTypeSelect.val())) {
        propertyIdSelect.closest('.field').hide();
    }
    switch (selectTypeSelect.val()) {
        case 'text_input':
            queryTypeSelect.find('option')
                .filter('[value="res"],[value="nres"],[value="ex"],[value="nex"]')
                .prop('disabled', true)
                .trigger('chosen:updated');
            truncateValuesInput.closest('.field').hide();
            valuesTextarea.closest('.field').hide();
            showAllContainer.hide();
            break;
        case 'single_select':
            truncateValuesInput.closest('.field').hide();
            break;
        case 'single_list':
        case 'multiple_list':
            break;
    }
});
FacetedBrowse.registerFacetSetHandler('value', function() {
    return {
        property_id: $('#value-property-id').val(),
        query_type: $('#value-query-type').val(),
        select_type: $('#value-select-type').val(),
        truncate_values: $('#value-truncate-values').val(),
        values: $('#value-values').val()
    };
});

$(document).ready(function() {

// Handle behavior during selecting a property.
$(document).on('change', '#value-property-id', function(e) {
    $('#show-all').prop('checked', false);
    $('#show-all-table-container').empty();
});
// Handle behavior during selecting a query type.
$(document).on('change', '#value-query-type', function(e) {
    const thisSelect = $(this);
    const propertySelect = $('#value-property-id');
    $('#show-all').prop('checked', false);
    $('#show-all-table-container').empty();
    if (['ex', 'nex'].includes(thisSelect.val())) {
        propertySelect.closest('.field').hide();
        propertySelect.val('');
        propertySelect.trigger('chosen:updated')
    } else {
        propertySelect.closest('.field').show();
    }
});
// Handle behavior during selecting a select type.
$(document).on('change', '#value-select-type', function (e) {
    const thisSelect = $(this);
    const queryTypeSelect = $('#value-query-type');
    const truncateValuesInput = $('#value-truncate-values');
    const valuesTextarea = $('#value-values');
    const showAllContainer = $('#show-all-container');
    switch (thisSelect.val()) {
        case 'text_input':
            // Default to eq when selected query type is invalid for text_input.
            if (['res', 'nres', 'ex', 'nex'].includes(queryTypeSelect.val())) {
                queryTypeSelect.val('eq');
            }
            // Disable query types that are invalid for text_input.
            queryTypeSelect.find('option')
                .filter('[value="res"],[value="nres"],[value="ex"],[value="nex"]')
                .prop('disabled', true);
            // Hide areas unneeded for text_input.
            truncateValuesInput.closest('.field').hide();
            valuesTextarea.closest('.field').hide();
            showAllContainer.hide();
            break;
        case 'single_select':
            queryTypeSelect.find('option').prop('disabled', false);
            truncateValuesInput.closest('.field').hide();
            valuesTextarea.closest('.field').show();
            showAllContainer.show();
            break;
        case 'single_list':
        case 'multiple_list':
            queryTypeSelect.find('option').prop('disabled', false);
            truncateValuesInput.closest('.field').show();
            valuesTextarea.closest('.field').show();
            showAllContainer.show();
            break;
    }
    queryTypeSelect.trigger('chosen:updated');
});

});
