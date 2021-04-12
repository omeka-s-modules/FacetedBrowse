$( document ).ready(function() {

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
    $.post(facets.data('facetFormUrl'), {
        facet_type: facetTypeSelect.val()
    }, function(html) {
        facetFormContainer.html(html);
        Omeka.openSidebar(facetSidebar);
    });
});
// Handle facet edit button.
facets.on('click', '.facet-edit', function(e) {
    e.preventDefault();
    facetSelected = $(this).closest('.facet');
    $.post(facets.data('facetFormUrl'), {
        facet_type: facetSelected.find('.facet-type').val(),
        facet_name: facetSelected.find('.facet-name').val(),
        facet_data: facetSelected.find('.facet-data').val()
    }, function(html) {
        facetFormContainer.html(html);
        Omeka.openSidebar(facetSidebar);
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
    // Handlers triggered by this event should validate the facet data against
    // the type, and, if valid, set the facet data object to #facet-set-button
    // using data('facet-data', {...}). If the data is invalid, it should alert
    // the user to make corrections.
    thisButton.trigger('faceted_browse:parse_facet_data', [type]);
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
