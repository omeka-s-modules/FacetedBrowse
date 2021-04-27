$(document).ready(function() {

const container = $('#container');

container.on('click', '.resource-class', function(e) {
    const thisClass = $(this);
    const facet = thisClass.closest('.facet');
    const queries = [];
    facet.find('.resource-class').not(thisClass).removeClass('selected');
    thisClass.prop('checked', !thisClass.hasClass('selected'));
    thisClass.toggleClass('selected');
    facet.find('.resource-class.selected').each(function() {
        const id = $(this).data('classId');
        queries.push(`resource_class_id=${id}`);
    });
    FacetedBrowse.setFacetQuery(facet.data('facetId'), queries.join('&'));
});

});
