$(document).ready(function() {

const container = $('#container');

container.on('input', '.full-text', function(e) {
    console.log($(this).val());
    const thisFullText = $(this);
    const facet = thisFullText.closest('.facet');
    FacetedBrowse.setFacetQuery(
        facet.data('facetId'),
        `fulltext_search=${encodeURIComponent(thisFullText.val())}`
    );
});

});
