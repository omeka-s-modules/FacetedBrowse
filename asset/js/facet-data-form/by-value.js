$(document).ready(function() {

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
// Handle show all values.
$(document).on('click', '#by-value-show-all-values', function(e) {
    const allValues = $('#by-value-all-values');
    if (this.checked) {
        $.get(allValues.data('valuesUrl'), {
            property_id: $('#by-value-property-id').val(),
            query_type: $('#by-value-query-type').val(),
            category_query: $('#category-query').val()
        }, function(data) {
            if (data.length) {
                data.forEach(value => {
                    allValues.append(`<tr><td style="width: 90%; padding: 0; border-bottom: 1px solid #dfdfdf;">${value.value}</td><td style="width: 10%; padding: 0; border-bottom: 1px solid #dfdfdf;">${value.value_count}</td></tr>`);
                });
            } else {
                allValues.append(`<tr><td>${Omeka.jsTranslate('There are no available values.')}</td></tr>`);
            }
        });
    } else {
        allValues.empty();
    }
});
// Clear all values during certain interactions.
$(document).on('change', '#by-value-property-id, #by-value-query-type', function(e) {
    $('#by-value-show-all-values').prop('checked', false);
    $('#by-value-all-values').empty();
    if ('ex' === $('#by-value-query-type').val()) {
        $('#by-value-property-id').closest('.field').hide();
    } else {
        $('#by-value-property-id').closest('.field').show();
    }
});

});
