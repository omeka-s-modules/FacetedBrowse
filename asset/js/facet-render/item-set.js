FacetedBrowse.registerFacetApplyStateHandler('item_set', function(facet, facetState) {
    const thisFacet = $(facet);
    const facetData = thisFacet.data('facetData');
    facetState = facetState ?? [];
    facetState.forEach(function(itemSetId) {
        if ('single_select' === facetData.select_type) {
            thisFacet.find(`select.item-set option[value="${itemSetId}"]`)
                .prop('selected', true);
        } else {
            thisFacet.find(`input.item-set[data-item-set-id="${itemSetId}"]`)
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

const handleUserInteraction = function(thisItemSet) {
    const facet = thisItemSet.closest('.facet');
    const facetData = facet.data('facetData');
    const queries = [];
    const state = [];
    switch (facetData.select_type) {
        case 'single_list':
            facet.find('.item-set').not(thisItemSet).removeClass('selected');
            thisItemSet.prop('checked', !thisItemSet.hasClass('selected'));
        case 'multiple_list':
            thisItemSet.toggleClass('selected');
            break;
    }
    if ('single_select' === facetData.select_type) {
        const id = thisItemSet.val();
        queries.push(`item_set_id[]=${id}`);
        state.push(id);
    } else {
        facet.find('.item-set.selected').each(function() {
            const id = $(this).data('itemSetId');
            queries.push(`item_set_id[]=${id}`);
            state.push(id);
        });
    }
    FB.setFacetState(facet.data('facetId'), state, queries.join('&'));
    FB.triggerStateChange();
};

container.on('change', 'select.item-set', function(e) {
    handleUserInteraction($(this));
});

container.on('click', 'input.item-set', function(e) {
    const thisValue = $(this);
    handleUserInteraction($(this));
    FacetedBrowse.updateSelectList(thisValue.closest('.select-list'));
});

});
