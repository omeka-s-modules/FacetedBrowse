FacetedBrowse.registerFacetApplyStateHandler('by_value', function(facet, facetState) {
    const thisFacet = $(facet);
    facetState.forEach(function(value) {
        thisFacet.find(`input.by-value[data-value="${value}"]`)
            .prop('checked', true)
            .addClass('selected');
    });
});

$(document).ready(function() {

const container = $('#container');

container.on('click', '.by-value', function(e) {
    const thisValue = $(this);
    const facet = thisValue.closest('.facet');
    const facets = container.find('.facet[data-facet-type="by_value"]');
    let index = 0;
    if ('single' === facet.data('facetData').select_type) {
        facet.find('.by-value').not(thisValue).removeClass('selected');
        thisValue.prop('checked', !thisValue.hasClass('selected'));
    }
    thisValue.toggleClass('selected');
    facets.each(function() {
        const thisFacet= $(this);
        const facetData = thisFacet.data('facetData');
        const queries = [];
        const state = [];
        thisFacet.find('.by-value.selected').each(function() {
            const property = $(this).data('propertyId');
            const type = facetData.query_type;
            const text = $(this).data('value');
            if (['ex', 'nex'].includes(type)) {
                queries.push(`property[${index}][joiner]=and&property[${index}][property]=${property}&property[${index}][type]=${type}`);
            } else {
                queries.push(`property[${index}][joiner]=and&property[${index}][property]=${property}&property[${index}][type]=${type}&property[${index}][text]=${encodeURIComponent(text)}`);
            }
            state.push(text);
            index++;
        });
        FacetedBrowse.setFacetState(thisFacet.data('facetId'), state, queries.join('&'));
    });
    FacetedBrowse.triggerStateChange();
});

});
