$(document).on('faceted_browse:parse_facet_data', '#facet-set-button', function(e, type) {
    if ('property_literal' !== type) {
        return;
    }
    const propertyIdInput = $('#property-literal-property-id');
    const queryTypeSelect = $('#property-literal-query-type');
    if (!propertyIdInput.val()) {
        propertyIdInput[0].setCustomValidity(Omeka.jsTranslate('A facet must have a property'));
        propertyIdInput[0].reportValidity();
    } else if (!queryTypeSelect.val()) {
        queryTypeSelect[0].setCustomValidity(Omeka.jsTranslate('A facet must have a query type'));
        queryTypeSelect[0].reportValidity();
    } else {
        $(this).data('facet-data', {
            property_id: propertyIdInput.val(),
            query_type: queryTypeSelect.val(),
            values: $('#property-literal-values').val()
        });
    }
});

$(document).on('change', '#property-literal-property-id', function(e) {
    let allValues = $('#property-literal-all-values');
    if (!allValues.length) {
        allValues = $('<ul id="property-literal-all-values"></ul>');
        $('#facet-set-button').after(allValues);
    }
    $.get(facetedBrowsePropertyLiteralValuesUrl, {
        property_id: $(this).val(),
        query: $('#category-query').val()
    }, function(data) {
        allValues.empty();
        data.forEach(value => {
            allValues.append(`<li>${value}</li>`);
        });
    });
});
