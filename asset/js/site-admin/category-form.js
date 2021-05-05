$(document).ready(function() {

const facets = $('#facets');
const facetSidebar = $('#facet-sidebar');
const facetTypeSelect = $('#facet-type-select');
const facetAddButton = $('#facet-add-button');
const facetFormContainer = $('#facet-form-container');
let facetSelected = null;

/**
 * Update facet type select.
 *
 * This ensures that there are no more facets of a type set to this category
 * than is allowed. It does this by disabling facet types that are equal to or
 * exceed the maximum that is set by the facet type.
 */
const updateFacetTypeSelect = function() {
    facetTypeSelect.find('option').each(function() {
        const thisOption = $(this);
        const facetType = thisOption.val();
        const maxFacets = thisOption.data('maxFacets');
        if (maxFacets) {
            const numFacets = $('.facet').find(`input.facet-type[value="${facetType}"]`).length;
            if (numFacets >= maxFacets) {
                thisOption.prop('disabled', true);
            }
        }
        facetTypeSelect.val('');
    });
};

updateFacetTypeSelect();

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
        FacetedBrowse.handleFacetAddEdit(type);
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
        FacetedBrowse.handleFacetAddEdit(type);
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
        alert(Omeka.jsTranslate('A facet must have a name'));
        return;
    }
    const data = FacetedBrowse.handleFacetSet(type);
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
        updateFacetTypeSelect();
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
            updateFacetTypeSelect();
        });
    }
});
// Handle show all checkbox.
$(document).on('click', '#show-all', function(e) {
    const thisCheckbox = $(this);
    const tableContainer = $('#show-all-table-container');
    if (this.checked) {
        const query = {};
        const queryParams = thisCheckbox.data('queryParams');
        if (queryParams) {
            // Set additional query parameters if set.
            $.each(queryParams, function(key, value) {
                query[key] = $(value).val();
            });
        }
        // Always include the category query.
        query.category_query = $('#category-query').val();
        $.get(thisCheckbox.data('url'), query, function(html) {
            tableContainer.html(html);
        });
    } else {
        tableContainer.empty();
    }
});

});
