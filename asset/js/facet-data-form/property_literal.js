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

$(document).on('click', '#property-literal-show-all-values', function(e) {
    const allValues = $('#property-literal-all-values');
    if (this.checked) {
        $.get(facetedBrowsePropertyLiteralValuesUrl, {
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

$(document).on('change', '#property-literal-property-id', function(e) {
    $('#property-literal-show-all-values').prop('checked', false);
    $('#property-literal-all-values').empty();
});
