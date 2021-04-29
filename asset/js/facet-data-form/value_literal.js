$(document).ready(function() {

FacetedBrowse.registerFacetAddEditHandler('value_literal', function() {
    $('#value-literal-property-id').chosen({
        allow_single_deselect: true,
    });
    $('#value-literal-query-type').chosen({
        disable_search: true,
    });
    $('#value-literal-select-type').chosen({
        disable_search: true,
    });
});
FacetedBrowse.registerFacetSetHandler('value_literal', function() {
    const propertyId = $('#value-literal-property-id');
    const queryType = $('#value-literal-query-type');
    const selectType = $('#value-literal-select-type');
    if (!queryType.val()) {
        alert(Omeka.jsTranslate('A facet must have a query type.'));
    } else if (!selectType.val()) {
        alert(Omeka.jsTranslate('A facet must have a select type.'));
    } else {
        return {
            property_id: propertyId.val(),
            query_type: queryType.val(),
            select_type: selectType.val(),
            values: $('#value-literal-values').val()
        };
    }
});
// Handle show all values.
$(document).on('click', '#value-literal-show-all-values', function(e) {
    const allValues = $('#value-literal-all-values');
    if (this.checked) {
        $.get(allValues.data('valuesUrl'), {
            property_id: $('#value-literal-property-id').val(),
            query_type: $('#value-literal-query-type').val(),
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
$(document).on('change', '#value-literal-property-id, #value-literal-query-type', function(e) {
    $('#value-literal-show-all-values').prop('checked', false);
    $('#value-literal-all-values').empty();
});

});
