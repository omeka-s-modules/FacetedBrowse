$(document).ready(function() {

const container = $('#container');

container.on('click', '.value-literal', function(e) {
    const thisValue = $(this);
    const facet = thisValue.closest('.facet');
    const facets = container.find('.facet[data-facet-type="value_literal"]');
    const queries = [];
    let index = 0;
    if ('single' === facet.data('facetData').select_type) {
        facet.find('.value-literal').not(thisValue).removeClass('selected');
        thisValue.prop('checked', !thisValue.hasClass('selected'));
    }
    thisValue.toggleClass('selected');
    facets.each(function() {
        const thisFacet= $(this);
        const facetData = thisFacet.data('facetData');
        FacetedBrowse.setFacetQuery(thisFacet.data('facetId'), '', false);
        thisFacet.find('.value-literal.selected').each(function() {
            const property = $(this).data('propertyId');
            const type = facetData.query_type;
            const text = $(this).data('value');
            queries.push(`property[${index}][joiner]=and&property[${index}][property]=${property}&property[${index}][type]=${type}&property[${index}][text]=${encodeURIComponent(text)}`);
            index++;
        });
    });
    FacetedBrowse.setFacetQuery(facet.data('facetId'), queries.join('&'));
});

});
