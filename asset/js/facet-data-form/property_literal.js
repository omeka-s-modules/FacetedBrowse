// Handle facet add/edit.
$(document).on('faceted-browse:facet-add-edit', '#facet-add-button, .facet-edit', function(e, type) {
    if ('property_literal' !== type) {
        return;
    }
    $('#property-literal-property-id').chosen({
        allow_single_deselect: true,
    });
    $('#property-literal-query-type').chosen({
        disable_search: true,
    });
});
// Handle facet set.
$(document).on('faceted-browse:facet-set', '#facet-set-button', function(e, type) {
    if ('property_literal' !== type) {
        return;
    }
    const propertyId = $('#property-literal-property-id');
    const queryType = $('#property-literal-query-type');
    if (!propertyId.val()) {
        alert(Omeka.jsTranslate('A facet must have a property.'));
    } else if (!queryType.val()) {
        alert(Omeka.jsTranslate('A facet must have a query type.'));
    } else {
        $(this).data('facet-data', {
            property_id: propertyId.val(),
            query_type: queryType.val(),
            values: $('#property-literal-values').val()
        });
    }
});
// Handle show all values.
$(document).on('click', '#property-literal-show-all-values', function(e) {
    const allValues = $('#property-literal-all-values');
    if (this.checked) {
        $.get(allValues.data('values-url'), {
            property_id: $('#property-literal-property-id').val(),
            query: $('#category-query').val()
        }, function(data) {
            if (data.length) {
                data.forEach(value => {
                    allValues.append(`<tr><td style="width: 90%; padding: 0; border-bottom: 1px solid #dfdfdf;">${value.value}</td><td style="width: 10%; padding: 0; border-bottom: 1px solid #dfdfdf;">${value.value_count}</td></tr>`);
                });
            } else {
                allValues.append(`<tr><td>${Omeka.jsTranslate('The selected property has no values.')}</td></tr>`);
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
