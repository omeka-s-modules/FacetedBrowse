// Handle facet set button.
$(document).on('faceted_browse:parse_facet_data', '#facet-set-button', function(e, type) {
    if ('property_literal' !== type) {
        return;
    }
    const propertyId = $('#property-literal-property-id');
    const queryType = $('#property-literal-query-type');
    if (!propertyId.val()) {
        propertyId[0].setCustomValidity(Omeka.jsTranslate('A facet must have a property'));
        propertyId[0].reportValidity();
    } else if (!queryType.val()) {
        queryType[0].setCustomValidity(Omeka.jsTranslate('A facet must have a query type'));
        queryType[0].reportValidity();
    } else {
        $(this).data('facet-data', {
            property_id: propertyId.val(),
            query_type: queryType.val(),
            values: $('#property-literal-values').val()
        });
    }
});
// Handle show all values checkbox.
$(document).on('click', '#property-literal-show-all-values', function(e) {
    const allValues = $('#property-literal-all-values');
    if (this.checked) {
        $.get(allValues.data('values-url'), {
            property_id: $('#property-literal-property-id').val(),
            query: $('#category-query').val()
        }, function(data) {
            if (data.length) {
                data.forEach(value => {
                    allValues.append(`<li>${value}</li>`);
                });
            } else {
                allValues.append(`<li>${Omeka.jsTranslate('The selected property has no values')}</li>`);
            }
        });
    } else {
        allValues.empty();
    }
});
// Handle property ID select.
$(document).on('change', '#property-literal-property-id', function(e) {
    $('#property-literal-show-all-values').prop('checked', false);
    $('#property-literal-all-values').empty();
});
