FacetedBrowse.registerFacetApplyStateHandler('value', function(facet, facetState) {
    const thisFacet = $(facet);
    const facetData = thisFacet.data('facetData');
    facetState.forEach(function(value) {
        if ('single_select' === facetData.select_type) {
            thisFacet.find(`select.value option[data-value="${value}"]`)
                .prop('selected', true);
        } else {
            thisFacet.find(`input.value[data-value="${value}"]`)
                .prop('checked', true)
                .addClass('selected');
        }
    });
});

$(document).ready(function() {

const container = $('#container');

const getQuery = function(index, property, type, text) {
    if (['ex', 'nex'].includes(type)) {
        return `property[${index}][joiner]=and&property[${index}][property]=${property}&property[${index}][type]=${type}`;
    }
    return `property[${index}][joiner]=and&property[${index}][property]=${property}&property[${index}][type]=${type}&property[${index}][text]=${encodeURIComponent(text)}`;
};

const handleUserInteraction = function(thisValue) {
    const facet = thisValue.closest('.facet');
    const facets = container.find('.facet[data-facet-type="value"]');
    let index = 0;
    switch (facet.data('facetData').select_type) {
        case 'single_list':
            facet.find('.value').not(thisValue).removeClass('selected');
            thisValue.prop('checked', !thisValue.hasClass('selected'));
        case 'multiple_list':
            thisValue.toggleClass('selected');
            break;
    }
    facets.each(function() {
        const thisFacet= $(this);
        const facetData = thisFacet.data('facetData');
        const queries = [];
        const state = [];
        if ('single_select' === facetData.select_type) {
            const select = thisFacet.find('.value option:selected');
            const property = select.data('propertyId');
            const type = facetData.query_type;
            const text = select.data('value');
            if (text) {
                queries.push(getQuery(index, property, type, text));
                state.push(text);
                index++;
            }
        } else {
            thisFacet.find('.value.selected').each(function() {
                const property = $(this).data('propertyId');
                const type = facetData.query_type;
                const text = $(this).data('value');
                queries.push(getQuery(index, property, type, text));
                state.push(text);
                index++;
            });
        }
        FacetedBrowse.setFacetState(thisFacet.data('facetId'), state, queries.join('&'));
    });
    FacetedBrowse.triggerStateChange();
};

container.on('change', 'select.value', function(e) {
    handleUserInteraction($(this));
});

container.on('click', 'input.value', function(e) {
    handleUserInteraction($(this));
});

});
