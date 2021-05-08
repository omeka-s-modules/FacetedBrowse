$(document).ready(function() {

const container = $('#container');

container.on('click', '.by-item-set', function(e) {
    const thisItemSet = $(this);
    const facet = thisItemSet.closest('.facet');
    const queries = [];
    facet.find('.by-item-set').not(thisItemSet).removeClass('selected');
    thisItemSet.prop('checked', !thisItemSet.hasClass('selected'));
    thisItemSet.toggleClass('selected');
    facet.find('.by-item-set.selected').each(function() {
        const id = $(this).data('itemSetId');
        queries.push(`item_set_id=${id}`);
    });
    FacetedBrowse.setFacetState(facet.data('facetId'), queries.join('&'));
    FacetedBrowse.triggerFacetStateChange();
});

});
