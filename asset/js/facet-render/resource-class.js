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

const handleUserInteraction = function(thisClass) {
    const facet = thisClass.closest('.facet');
    const facetData = facet.data('facetData');
    const queries = [];
    const state = [];
    switch (facetData.select_type) {
        case 'single_list':
            facet.find('.resource-class').not(thisClass).removeClass('selected');
            thisClass.prop('checked', !thisClass.hasClass('selected'));
        case 'multiple_list':
            thisClass.toggleClass('selected');
            break;
    }
    if ('single_select' === facetData.select_type) {
        const id = thisClass.val();
        queries.push(`resource_class_id[]=${id}`);
        state.push(id);
    } else {
        facet.find('.resource-class.selected').each(function() {
            const id = $(this).data('classId');
            queries.push(`resource_class_id[]=${id}`);
            state.push(id);
        });
    }
    FacetedBrowse.setFacetState(facet.data('facetId'), state, queries.join('&'));
    FacetedBrowse.triggerStateChange();
};

container.on('change', 'select.resource-class', function(e) {
    handleUserInteraction($(this));
});

container.on('click', 'input.resource-class', function(e) {
    handleUserInteraction($(this));
});

});
