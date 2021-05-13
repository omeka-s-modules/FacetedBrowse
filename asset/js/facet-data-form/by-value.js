FacetedBrowse.registerFacetAddEditHandler('by_value', function() {
    $('#by-value-property-id').chosen({
        allow_single_deselect: true,
    });
    $('#by-value-query-type').chosen({
        disable_search: true,
    });
    $('#by-value-select-type').chosen({
        disable_search: true,
    });
    if (['ex', 'nex'].includes($('#by-value-query-type').val())) {
        $('#by-value-property-id').closest('.field').hide();
    }
});
FacetedBrowse.registerFacetSetHandler('by_value', function() {
    return {
        property_id: $('#by-value-property-id').val(),
        query_type: $('#by-value-query-type').val(),
        select_type: $('#by-value-select-type').val(),
        values: $('#by-value-values').val()
    };
});

$(document).ready(function() {

// Handle behavior during selecting a property.
$(document).on('change', '#by-value-property-id', function(e) {
    $('#show-all').prop('checked', false);
    $('#show-all-table-container').empty();
});
// Handle behavior during selecting a query type.
$(document).on('change', '#by-value-query-type', function(e) {
    const thisSelect = $(this);
    const propertySelect = $('#by-value-property-id');
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

});
