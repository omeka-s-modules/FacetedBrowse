FacetedBrowse.registerFacetApplyStateHandler('resource_class', function(facet, facetState) {
    const thisFacet = $(facet);
    facetState.forEach(function(classId) {
        thisFacet.find(`input.resource-class[data-class-id="${classId}"]`)
            .prop('checked', true)
            .addClass('selected');
    });
});

$(document).ready(function() {

const container = $('#container');

container.on('click', '.resource-class', function(e) {
    const thisClass = $(this);
    const facet = thisClass.closest('.facet');
    const queries = [];
    const state = [];
    facet.find('.resource-class').not(thisClass).removeClass('selected');
    thisClass.prop('checked', !thisClass.hasClass('selected'));
    thisClass.toggleClass('selected');
    facet.find('.resource-class.selected').each(function() {
        const id = $(this).data('classId');
        queries.push(`resource_class_id=${id}`);
        state.push(id);
    });
    FacetedBrowse.setFacetState(facet.data('facetId'), state, queries.join('&'));
    FacetedBrowse.triggerStateChange();
});

});
