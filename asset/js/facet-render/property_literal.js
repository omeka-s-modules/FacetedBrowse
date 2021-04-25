$(document).ready(function() {

const container = $('#container');

container.on('click', '.property-literal', function(e) {
    const thisValue = $(this);
    const facet = thisValue.closest('.facet');
    const facets = container.find('.facet[data-facet-type="property_literal"]');
    const queries = [];
    let index = 0;
    if ('single' === facet.data('facetData').select_type) {
        facet.find('.property-literal').not(thisValue).removeClass('selected');
        thisValue.prop('checked', !thisValue.hasClass('selected'));
    }
    thisValue.toggleClass('selected');
    facets.each(function() {
        const thisFacet= $(this);
        const facetData = thisFacet.data('facetData');
        thisFacet.find('.property-literal.selected').each(function() {
            const text = $(this).data('value');
            queries.push(`property[${index}][joiner]=and&property[${index}][property]=${facetData.property_id}&property[${index}][type]=${facetData.query_type}&property[${index}][text]=${encodeURIComponent(text)}`);
            index++;
        });
    });
    facets.data('query', '');
    facet.data('query', queries.join('&'));
    container.trigger('faceted-browse:query-state-change');
});

});
