FacetedBrowse.registerFacetAddEditHandler('value', function() {
    $('#value-property-id').chosen({
        allow_single_deselect: true,
    });
    $('#value-query-type').chosen({
        disable_search: true,
    });
    $('#value-select-type').chosen({
        disable_search: true,
    });
    if (['ex', 'nex'].includes($('#value-query-type').val())) {
        $('#value-property-id').closest('.field').hide();
    }
    if ('text_input' === $('#value-select-type').val()) {
        $('#value-query-type').find('option')
            .filter('[value="res"],[value="nres"],[value="ex"],[value="nex"]')
            .prop('disabled', true)
            .trigger('chosen:updated');
        $('#value-values').closest('.field').hide();
        $('#show-all-container').hide();
    }
});
FacetedBrowse.registerFacetSetHandler('value', function() {
    return {
        property_id: $('#value-property-id').val(),
        query_type: $('#value-query-type').val(),
        select_type: $('#value-select-type').val(),
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
    if ('text_input' === thisSelect.val()) {
        // Default to eq when selected query type is invalid for text_input.
        if (['res', 'nres', 'ex', 'nex'].includes(queryTypeSelect.val())) {
            queryTypeSelect.val('eq');
        }
        // Disable query types that are invalid for text_input.
        queryTypeSelect.find('option')
            .filter('[value="res"],[value="nres"],[value="ex"],[value="nex"]')
            .prop('disabled', true);
        // Hide areas unneeded for text_input.
        $('#value-values').closest('.field').hide();
        $('#show-all-container').hide();
    } else {
        queryTypeSelect.find('option').prop('disabled', false);
        $('#value-values').closest('.field').show();
        $('#show-all-container').show();
    }
    queryTypeSelect.trigger('chosen:updated');
});

});
