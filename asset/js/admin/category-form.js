/**
 * Available events:
 *
 * - faceted-browse:facet-add-edit : Triggered after the user clicks facet
 *       add/edit. Attach handlers to #facet-add-button or .facet-edit to
 *       prepare the facet form, if needed.
 * - faceted-browse:facet-set : Trigered after the user clicks facet set. Attach
 *       handlers to #facet-set-button to validate facet data and set the valid
 *       facet data object to the set button using data('facet-data', {...}).
 *       Alert the user if the data is invalid.
 */
$(document).ready(function() {

const facets = $('#facets');
const facetSidebar = $('#facet-sidebar');
const facetTypeSelect = $('#facet-type-select');
const facetAddButton = $('#facet-add-button');
const facetFormContainer = $('#facet-form-container');
let facetSelected = null;

// Enable facet sorting.
new Sortable(facets[0], {draggable: '.facet', handle: '.sortable-handle'});

// Handle facet type select.
facetTypeSelect.on('change', function(e) {
    facetAddButton.prop('disabled', ('' === $(this).val()) ? true : false);
});
// Handle facet add button.
facetAddButton.on('click', function(e) {
    facetSelected = undefined;
    const thisButton = $(this);
    const type = facetTypeSelect.val();
    $.post(facets.data('facetFormUrl'), {
        facet_type: type
    }, function(html) {
        facetFormContainer.html(html);
        Omeka.openSidebar(facetSidebar);
        thisButton.trigger('faceted-browse:facet-add-edit', [type]);
    });
});
// Handle facet edit button.
facets.on('click', '.facet-edit', function(e) {
    e.preventDefault();
    facetSelected = $(this).closest('.facet');
    const thisButton = $(this);
    const type = facetSelected.find('.facet-type').val();
    const name = facetSelected.find('.facet-name').val();
    const data = facetSelected.find('.facet-data').val();
    $.post(facets.data('facetFormUrl'), {
        facet_type: type,
        facet_name: name,
        facet_data: data
    }, function(html) {
        facetFormContainer.html(html);
        Omeka.openSidebar(facetSidebar);
        thisButton.trigger('faceted-browse:facet-add-edit', [type]);
    });
});
facets.on('click', '.facet-remove', function(e) {
    e.preventDefault();
    const thisButton = $(this);
    const facet = thisButton.closest('.facet');
    facet.find(':input').prop('disabled', true);
    facet.addClass('delete');
    facet.find('.facet-restore').show();
    thisButton.hide();
});
facets.on('click', '.facet-restore', function(e) {
    e.preventDefault();
    const thisButton = $(this);
    const facet = thisButton.closest('.facet');
    facet.find(':input').prop('disabled', false);
    facet.removeClass('delete');
    facet.find('.facet-remove').show();
    thisButton.hide();
});
// Handle facet set button.
facetFormContainer.on('click', '#facet-set-button', function(e) {
    const thisButton = $(this);
    const type = $('#facet-type-input').val();
    const name = $.trim($('#facet-name-input').val());
    if (!name) {
        $('#facet-name-input')[0].setCustomValidity(Omeka.jsTranslate('A facet must have a name'));
        $('#facet-name-input')[0].reportValidity();
        return;
    }
    thisButton.trigger('faceted-browse:facet-set', [type]);
    const data = thisButton.data('facetData');
    if (!data) {
        // The data is invalid. The handler should have alerted the user. Do
        // nothing and let the user make corrections.
        return;
    }
    if (facetSelected) {
        // Handle an edit.
        facetSelected.find('.facet-name-display').text(name);
        facetSelected.find('.facet-name').val(name);
        facetSelected.find('.facet-data').val(JSON.stringify(data));
        facetSelected = undefined;
        Omeka.closeSidebar(facetSidebar);
        facetFormContainer.empty();
    } else {
        // Handle an add.
        $.post(facets.data('facetRowUrl'), {
            facet_type: $('#facet-type-input').val(),
            facet_name: $('#facet-name-input').val(),
            index: $('.facet').length
        }, function(html) {
            const facet = $($.parseHTML(html));
            facet.find('.facet-data').val(JSON.stringify(data));
            facets.append(facet);
            Omeka.closeSidebar(facetSidebar);
            facetFormContainer.empty();
        });
    }
});

});
