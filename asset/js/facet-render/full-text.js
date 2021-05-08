$(document).ready(function() {

const container = $('#container');

container.on('input', '.full-text', function(e) {
    const thisFullText = $(this);
    const facet = thisFullText.closest('.facet');
    FacetedBrowse.setFacetState(facet.data('facetId'), thisFullText.val(), `fulltext_search=${encodeURIComponent(thisFullText.val())}`);
    FacetedBrowse.triggerFacetStateChange();
});

});
