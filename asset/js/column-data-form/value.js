FacetedBrowse.registerColumnAddEditHandler('value', function() {
    $('#value-property-terms').chosen({
        include_group_label_in_selected: true
    });
});
FacetedBrowse.registerColumnSetHandler('value', function() {
    const propertyTerm = $('#value-property-terms');
    if (!propertyTerm.val()) {
        alert(Omeka.jsTranslate('A column must have a property.'));
        return;
    }
    return {
        'property_term': propertyTerm.val()
    };
});
