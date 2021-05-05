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
    if ('ex' === $('#by-value-query-type').val()) {
        $('#by-value-property-id').closest('.field').hide();
    }
});
FacetedBrowse.registerFacetSetHandler('by_value', function() {
    const propertyId = $('#by-value-property-id');
    const queryType = $('#by-value-query-type');
    const selectType = $('#by-value-select-type');
    return {
        property_id: propertyId.val(),
        query_type: queryType.val(),
        select_type: selectType.val(),
        values: $('#by-value-values').val()
    };
});

$(document).ready(function() {

// Clear show all during certain interactions.
$(document).on('change', '#by-value-property-id, #by-value-query-type', function(e) {
    $('#show-all').prop('checked', false);
    $('#show-all-table-container').empty();
    if ('ex' === $('#by-value-query-type').val()) {
        $('#by-value-property-id').closest('.field').hide();
    } else {
        $('#by-value-property-id').closest('.field').show();
    }
});

});
