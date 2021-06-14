FacetedBrowse.registerFacetApplyStateHandler('resource_template', function(facet, facetState) {
    const thisFacet = $(facet);
    facetState.forEach(function(templateId) {
        thisFacet.find(`input.resource-template[data-template-id="${templateId}"]`)
            .prop('checked', true)
            .addClass('selected');
    });
});

$(document).ready(function() {

const container = $('#container');

container.on('click', '.resource-template', function(e) {
    const thisTemplate = $(this);
    const facet = thisTemplate.closest('.facet');
    const queries = [];
    const state = [];
    facet.find('.resource-template').not(thisTemplate).removeClass('selected');
    thisTemplate.prop('checked', !thisTemplate.hasClass('selected'));
    thisTemplate.toggleClass('selected');
    facet.find('.resource-template.selected').each(function() {
        const id = $(this).data('templateId');
        queries.push(`resource_template_id=${id}`);
        state.push(id);
    });
    FacetedBrowse.setFacetState(facet.data('facetId'), state, queries.join('&'));
    FacetedBrowse.triggerStateChange();
});

});
