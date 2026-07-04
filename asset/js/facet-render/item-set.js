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
    if ('single_list' === facetData.select_type) {
        const anyInput = thisFacet.find('input.item-set[data-item-set-id=""]');
        const hasActiveFilter = thisFacet.find('input.item-set.selected').length > 0;
        anyInput.addClass('selected');
        if (!hasActiveFilter) {
            anyInput.prop('checked', true);
        }
    }
    if (['single_list', 'multiple_list'].includes(facetData.select_type)) {
        FacetedBrowse.updateSelectList(thisFacet.find('.select-list'));
    }
});

$(document).ready(function() {

const container = $('#container');

const handleUserInteraction = function(thisItemSet) {
    const facet = thisItemSet.closest('.facet');
    const facetData = facet.data('facetData');
    const queries = [];
    const state = [];
    switch (facetData.select_type) {
        case 'single_list':
            facet.find('.item-set').not(thisItemSet).not('[data-item-set-id=""]').removeClass('selected');
            // falls through
        case 'multiple_list':
            thisItemSet.toggleClass('selected');
            break;
    }
    if ('single_list' === facetData.select_type) {
        facet.find('input.item-set[data-item-set-id=""]').addClass('selected');
    }
    if ('single_select' === facetData.select_type) {
        const id = thisItemSet.val();
        queries.push(`item_set_id[]=${id}`);
        state.push(id);
    } else {
        facet.find('.item-set.selected').each(function() {
            const id = $(this).data('itemSetId');
            if (id) {
                queries.push(`item_set_id[]=${id}`);
                state.push(id);
            }
        });
    }
    FacetedBrowse.setFacetState(facet.data('facetId'), state, queries.join('&'));
    FacetedBrowse.triggerStateChange();
};

container.on('change', 'select.item-set', function(e) {
    handleUserInteraction($(this));
});

container.on('change', 'input.item-set[type="radio"]', function(e) {
    const thisValue = $(this);
    handleUserInteraction(thisValue);
    FacetedBrowse.updateSelectList(thisValue.closest('.select-list'));
});

container.on('click', 'input.item-set[type="checkbox"]', function(e) {
    const thisValue = $(this);
    const selectList = thisValue.closest('.select-list');
    handleUserInteraction(thisValue);
    FacetedBrowse.updateSelectList(selectList);
    if (!thisValue.is(':visible')) {
        selectList.find('input:visible').first().focus();
    }
});

});
