$(document).ready(function() {

const facets = $('#facets');
const facetSidebar = $('#facet-sidebar');
const facetTypeSelect = $('#facet-type-select');
const facetAddButton = $('#facet-add-button');
const facetFormContainer = $('#facet-form-container');
let facetSelected = null;

const columns = $('#columns');
const columnSidebar = $('#column-sidebar');
const columnTypeSelect = $('#column-type-select');
const columnAddButton = $('#column-add-button');
const columnFormContainer = $('#column-form-container');
let columnSelected = null;

/**
 * Reset facet type select.
 *
 * This ensures that there are no more facets of a type set to this category
 * than is allowed. It does this by disabling facet types that are equal to or
 * exceed the maximum that is set by the facet type.
 */
const resetFacetTypeSelect = function() {
    facetTypeSelect.val('');
    facetAddButton.prop('disabled', true);
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
    });
};

/**
 * Reset column type select.
 *
 * This ensures that there are no more columns of a type set to this category
 * than is allowed. It does this by disabling columns types that are equal to or
 * exceed the maximum that is set by the column type.
 */
const resetColumnTypeSelect = function() {
    columnTypeSelect.val('');
    columnAddButton.prop('disabled', true);
    columnTypeSelect.find('option').each(function() {
        const thisOption = $(this);
        const columnType = thisOption.val();
        const maxColumns = thisOption.data('maxColumns');
        if (maxColumns) {
            const numColumns = $('.column').find(`input.column-type[value="${columnType}"]`).length;
            if (numColumns >= maxColumns) {
                thisOption.prop('disabled', true);
            }
        }
    });
}

/**
 * Close all other sidebars when one becomes active.
 */
const closeOtherSidebars = function(button, sidebar) {
    $(document).on('click', button, function() {
        var openSidebar = $('.sidebar.active').not(sidebar);
        Omeka.closeSidebar(openSidebar);
        openSidebar.removeClass('active');
    });
}

/**
 * Scroll to an element in the sidebar.
 */
const sidebarScrollTo = function(scrollTo) {
    const container = $('.confirm-main');
    container.animate({
        scrollTop: scrollTo.offset().top - container.offset().top + container.scrollTop()
    });
};

closeOtherSidebars('.delete.button', '#delete');
closeOtherSidebars('.query-form-edit', '#query-sidebar-edit');
closeOtherSidebars('.facet-edit', '#facet-sidebar');
closeOtherSidebars('.column-edit', '#column-sidebar');
closeOtherSidebars('#facet-add-button', '#facet-sidebar');
closeOtherSidebars('#column-add-button', '#column-sidebar');

resetFacetTypeSelect();
resetColumnTypeSelect();

// Enable facet and column sorting.
new Sortable(facets[0], {draggable: '.facet', handle: '.sortable-handle'});
new Sortable(columns[0], {draggable: '.column', handle: '.sortable-handle'});

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
        resetColumnTypeSelect();
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
        resetColumnTypeSelect();
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
    Omeka.closeSidebar(facetSidebar);
    if (facetSelected) {
        // Handle an edit.
        facetSelected.find('.facet-name-display').text(name);
        facetSelected.find('.facet-name').val(name);
        facetSelected.find('.facet-data').val(JSON.stringify(data));
        facetSelected = undefined;
        resetFacetTypeSelect();
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
            resetFacetTypeSelect();
        });
    }
});

// Handle column type select.
columnTypeSelect.on('change', function(e) {
    columnAddButton.prop('disabled', ('' === $(this).val()) ? true : false);
});
// Handle column add button.
columnAddButton.on('click', function(e) {
    columnSelected = undefined;
    const thisButton = $(this);
    const type = columnTypeSelect.val();
    $.post(columns.data('columnFormUrl'), {
        column_type: type
    }, function(html) {
        columnFormContainer.html(html);
        Omeka.openSidebar(columnSidebar);
        resetFacetTypeSelect();
        FacetedBrowse.handleColumnAddEdit(type);
    });
});
// Handle column edit button.
columns.on('click', '.column-edit', function(e) {
    e.preventDefault();
    columnSelected = $(this).closest('.column');
    const thisButton = $(this);
    const type = columnSelected.find('.column-type').val();
    const name = columnSelected.find('.column-name').val();
    const data = columnSelected.find('.column-data').val();
    $.post(columns.data('columnFormUrl'), {
        column_type: type,
        column_name: name,
        column_data: data
    }, function(html) {
        columnFormContainer.html(html);
        Omeka.openSidebar(columnSidebar);
        resetFacetTypeSelect();
        FacetedBrowse.handleColumnAddEdit(type);
    });
});
columns.on('click', '.column-remove', function(e) {
    e.preventDefault();
    const thisButton = $(this);
    const column = thisButton.closest('.column');
    column.find(':input').prop('disabled', true);
    column.addClass('delete');
    column.find('.column-restore').show();
    thisButton.hide();
});
columns.on('click', '.column-restore', function(e) {
    e.preventDefault();
    const thisButton = $(this);
    const column = thisButton.closest('.column');
    column.find(':input').prop('disabled', false);
    column.removeClass('delete');
    column.find('.column-remove').show();
    thisButton.hide();
});
// Handle column set button.
columnFormContainer.on('click', '#column-set-button', function(e) {
    const thisButton = $(this);
    const type = $('#column-type-input').val();
    const name = $.trim($('#column-name-input').val());
    if (!name) {
        alert(Omeka.jsTranslate('A column must have a name'));
        return;
    }
    const data = FacetedBrowse.handleColumnSet(type);
    if (!data) {
        // The data is invalid. The handler should have alerted the user. Do
        // nothing and let the user make corrections.
        return;
    }
    Omeka.closeSidebar(columnSidebar);
    if (columnSelected) {
        // Handle an edit.
        columnSelected.find('.column-name-display').text(name);
        columnSelected.find('.column-name').val(name);
        columnSelected.find('.column-data').val(JSON.stringify(data));
        columnSelected = undefined;
        resetColumnTypeSelect();
    } else {
        // Handle an add.
        $.post(columns.data('columnRowUrl'), {
            column_type: $('#column-type-input').val(),
            column_name: $('#column-name-input').val(),
            index: $('.column').length
        }, function(html) {
            const column = $($.parseHTML(html));
            column.find('.column-data').val(JSON.stringify(data));
            columns.append(column);
            resetColumnTypeSelect();
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
            sidebarScrollTo($('#show-all-container'));
        }).fail(function() {
            tableContainer.html('<p class="error">' + Omeka.jsTranslate('Cannot show all. The result set is likely too large.') + '<p>');
        });
    } else {
        tableContainer.empty();
    }
});

// Handle add all button.
$(document).on('click', '#add-all', function(e) {
    const rows = $('#show-all-table').data('rows');
    const populateMultiSelect = function(multiSelect, rows) {
        $.each(rows, function(index, row) {
            multiSelect.find(`option[value="${row.id}"]`).prop('selected', true);
        });
        multiSelect.trigger('chosen:updated');
    };
    // Add all according to facet type.
    switch ($('#facet-type-input').val()) {
        case 'value':
            const labels = [];
            $.each(rows, (index, row) => {
                labels.push(row.label);
            });
            $('#value-values').val(labels.join("\n"));
            sidebarScrollTo($('#value-values').closest('.field'));
            break;
        case 'resource_class':
            populateMultiSelect($('#resource-class-class-ids'), rows);
            sidebarScrollTo($('#resource-class-class-ids').closest('.field'));
            break;
        case 'resource_template':
            populateMultiSelect($('#resource-template-template-ids'), rows);
            sidebarScrollTo($('#resource-template-template-ids').closest('.field'));
            break;
        case 'item_set':
            populateMultiSelect($('#item-set-item-set-ids'), rows);
            sidebarScrollTo($('#item-set-item-set-ids').closest('.field'));
            break;
    }
});

});
