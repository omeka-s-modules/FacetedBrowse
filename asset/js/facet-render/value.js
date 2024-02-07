FacetedBrowse.registerFacetApplyStateHandler('value', function(facet, facetState) {
    const thisFacet = $(facet);
    const facetData = thisFacet.data('facetData');
    facetState = facetState ?? [];
    facetState.forEach(function(value) {
        if ('text_input' === facetData.select_type) {
            thisFacet.find('input.value').val(value);
        } else if ('single_select' === facetData.select_type) {
            thisFacet.find(`select.value option[data-value="${value}"]`)
                .prop('selected', true);
        } else {
            thisFacet.find(`input.value[data-value="${value}"]`)
                .prop('checked', true)
                .addClass('selected');
        }
    });
    if (['single_list', 'multiple_list'].includes(facetData.select_type)) {
        FacetedBrowse.updateSelectList(thisFacet.find('.select-list'));
    }
});

$(document).ready(function() {

const container = $('#container');
const FB = container.data('FacetedBrowse');
let timerId;

const getQuery = function(index, property, type, text) {
    if (['ex', 'nex'].includes(type)) {
        return `property[${index}][joiner]=and&property[${index}][property]=${property}&property[${index}][type]=${type}`;
    }
    return `property[${index}][joiner]=and&property[${index}][property]=${property}&property[${index}][type]=${type}&property[${index}][text]=${encodeURIComponent(text)}`;
};

const handleUserInteraction = function(thisValue) {
    const facet = thisValue.closest('.facet');
    const facets = container.find('.facet[data-facet-type="value"]');
    // We set a high initial index to avoid collisions with category queries
    // that include "Search by value" queries.
    let index = 100;
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
        if ('text_input' === facetData.select_type) {
            const input = thisFacet.find('.value');
            const property = input.data('propertyId');
            const type = facetData.query_type;
            const text = input.val();
            if (text) {
                queries.push(getQuery(index, property, type, text));
                state.push(text);
                index++;
            }
        } else if ('single_select' === facetData.select_type) {
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
        FB.setFacetState(thisFacet.data('facetId'), state, queries.join('&'));
    });
    FB.triggerStateChange();
};

// Handle single_select interaction.
container.on('change', 'select.value', function(e) {
    handleUserInteraction($(this));
});

// Handle single_list interaction.
container.on('click', 'input.value[type="radio"]', function(e) {
    const thisValue = $(this);
    handleUserInteraction(thisValue);
    FacetedBrowse.updateSelectList(thisValue.closest('.select-list'));
});

// Handle multiple_list interaction.
container.on('click', 'input.value[type="checkbox"]', function(e) {
    const thisValue = $(this);
    handleUserInteraction(thisValue);
    FacetedBrowse.updateSelectList(thisValue.closest('.select-list'));
});

// Handle text_input interaction.
container.on('keyup', 'input.value[type="text"]', function(e) {
    const thisValue = $(this);
    clearTimeout(timerId);
    timerId = setTimeout(function() {
        handleUserInteraction(thisValue);
    }, 350);
});

});
