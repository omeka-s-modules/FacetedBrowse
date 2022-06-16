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
            facet.find('.resource-template').not(thisTemplate).removeClass('selected');
            thisTemplate.prop('checked', !thisTemplate.hasClass('selected'));
        case 'multiple_list':
            thisTemplate.toggleClass('selected');
            break;
    }
    if ('single_select' === facetData.select_type) {
        const id = thisTemplate.val();
        queries.push(`resource_template_id[]=${id}`);
        state.push(id);
    } else {
        facet.find('.resource-template.selected').each(function() {
            const id = $(this).data('templateId');
            queries.push(`resource_template_id[]=${id}`);
            state.push(id);
        });
    }
    FacetedBrowse.setFacetState(facet.data('facetId'), state, queries.join('&'));
    FacetedBrowse.triggerStateChange();
};


container.on('change', 'select.resource-template', function(e) {
    handleUserInteraction($(this));
});

container.on('click', 'input.resource-template', function(e) {
    handleUserInteraction($(this));
});


});
