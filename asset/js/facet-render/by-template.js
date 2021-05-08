$(document).ready(function() {

const container = $('#container');

container.on('click', '.by-template', function(e) {
    const thisTemplate = $(this);
    const facet = thisTemplate.closest('.facet');
    const queries = [];
    const state = [];
    facet.find('.by-template').not(thisTemplate).removeClass('selected');
    thisTemplate.prop('checked', !thisTemplate.hasClass('selected'));
    thisTemplate.toggleClass('selected');
    facet.find('.by-template.selected').each(function() {
        const id = $(this).data('templateId');
        queries.push(`resource_template_id=${id}`);
        state.push(id);
    });
    FacetedBrowse.setFacetState(facet.data('facetId'), state, queries.join('&'));
    FacetedBrowse.triggerFacetStateChange();
});

});
