FacetedBrowse.registerFacetApplyStateHandler('full_text', function(facet, facetState) {
    const thisFacet = $(facet);
    facetState = facetState ?? '';
    thisFacet.find(`input.full-text`).val(facetState);
});

$(document).ready(function() {

const container = $('#container');
const FB = container.data('FacetedBrowse');
let timerId;

container.on('keyup', '.full-text', function(e) {
    const thisFullText = $(this);
    const facet = thisFullText.closest('.facet');
    const query = thisFullText.val()
        ? `fulltext_search=${encodeURIComponent(thisFullText.val())}`
        : '';
    clearTimeout(timerId);
    timerId = setTimeout(function() {
        FB.setFacetState(facet.data('facetId'), thisFullText.val(), query);
        FB.triggerStateChange();
    }, 350);
});

});
