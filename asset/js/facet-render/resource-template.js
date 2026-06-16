FacetedBrowse.registerFacetApplyStateHandler('resource_template', function(facet, facetState) {
    const thisFacet = $(facet);
    const facetData = thisFacet.data('facetData');
    facetState = facetState ?? [];
    facetState.forEach(function(templateId) {
        if ('single_select' === facetData.select_type) {
            thisFacet.find(`select.resource-template option[value="${templateId}"]`)
                .prop('selected', true);
        } else {
            thisFacet.find(`input.resource-template[data-template-id="${templateId}"]`)
                .prop('checked', true)
                .addClass('selected');
        }
    });
    if ('single_list' === facetData.select_type) {
        const anyInput = thisFacet.find('input.resource-template[data-template-id=""]');
        const hasActiveFilter = thisFacet.find('input.resource-template.selected').length > 0;
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

const handleUserInteraction = function(thisTemplate) {
    const facet = thisTemplate.closest('.facet');
    const facetData = facet.data('facetData');
    const queries = [];
    const state = [];
    switch (facetData.select_type) {
        case 'single_list':
            facet.find('.resource-template').not(thisTemplate).not('[data-template-id=""]').removeClass('selected');
            // falls through
        case 'multiple_list':
            thisTemplate.toggleClass('selected');
            break;
    }
    if ('single_list' === facetData.select_type) {
        facet.find('input.resource-template[data-template-id=""]').addClass('selected');
    }
    if ('single_select' === facetData.select_type) {
        const id = thisTemplate.val();
        queries.push(`resource_template_id[]=${id}`);
        state.push(id);
    } else {
        facet.find('.resource-template.selected').each(function() {
            const id = $(this).data('templateId');
            if (id) {
                queries.push(`resource_template_id[]=${id}`);
                state.push(id);
            }
        });
    }
    FacetedBrowse.setFacetState(facet.data('facetId'), state, queries.join('&'));
    FacetedBrowse.triggerStateChange();
};


container.on('change', 'select.resource-template', function(e) {
    handleUserInteraction($(this));
});

container.on('change', 'input.resource-template[type="radio"]', function(e) {
    const thisValue = $(this);
    handleUserInteraction(thisValue);
    FacetedBrowse.updateSelectList(thisValue.closest('.select-list'));
});

container.on('click', 'input.resource-template[type="checkbox"]', function(e) {
    const thisValue = $(this);
    const selectList = thisValue.closest('.select-list');
    handleUserInteraction(thisValue);
    FacetedBrowse.updateSelectList(selectList);
    if (!thisValue.is(':visible')) {
        selectList.find('input:visible').first().focus();
    }
});


});
