$(document).ready(function() {

const container = $('#container');

container.on('click', '.property-literal', function(e) {
    e.preventDefault();
    const thisValue = $(this);
    const facet = thisValue.closest('.facet');
    const facets = container.find('.facet[data-facet-type="property_literal"]');
    const queries = [];
    let index = 0;
    if ('multiple' === facet.data('facetData').select_type) {
        thisValue.toggleClass('selected');
    } else {
        // Default select type is "single"
        const notThisValue = facet.find('.property-literal').not(thisValue);
        if (thisValue.hasClass('selected')) {
            thisValue.removeClass('selected');
            notThisValue.removeClass('disabled');
        } else {
            thisValue.addClass('selected');
            notThisValue.addClass('disabled');
        }
    }
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
