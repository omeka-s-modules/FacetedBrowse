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
    if (!scrollTo.length) {
        // Nothing to scroll to is a no-op, not a TypeError from offset().
        return;
    }
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
    const excludeSortBy = columnSelected.find('.column-exclude-sort-by').val();
    const data = columnSelected.find('.column-data').val();
    $.post(columns.data('columnFormUrl'), {
        column_type: type,
        column_name: name,
        column_exclude_sort_by: excludeSortBy,
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
    const excludeSortBy = $('#column-exclude-sort-by-checkbox').is(':checked') ? '1' : '0';
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
        columnSelected.find('.column-exclude-sort-by').val(excludeSortBy);
        columnSelected.find('.column-data').val(JSON.stringify(data));
        columnSelected = undefined;
        resetColumnTypeSelect();
    } else {
        // Handle an add.
        $.post(columns.data('columnRowUrl'), {
            column_type: $('#column-type-input').val(),
            column_name: $('#column-name-input').val(),
            column_exclude_sort_by: $('#column-exclude-sort-by-checkbox').is(':checked') ? '1' : '0',
            index: $('.column').length
        }, function(html) {
            const column = $($.parseHTML(html));
            column.find('.column-data').val(JSON.stringify(data));
            columns.append(column);
            resetColumnTypeSelect();
        });
    }
});

/**
 * Build the query for a show all request, shared by the checkbox and the sort
 * headers. Sort arguments are omitted on first load, letting the facet type's own
 * default apply.
 */
const showAllQuery = function(sortBy, sortOrder) {
    const query = {};
    const queryParams = $('#show-all').data('queryParams');
    if (queryParams) {
        // Set additional query parameters if set.
        $.each(queryParams, function(key, value) {
            query[key] = $(value).val();
        });
    }
    // The endpoint needs these to ask the right facet type for its values.
    query.category_query = $('#category-query').val();
    query.facet_type = $('#facet-type-input').val();
    if (sortBy) {
        query.sort_by = sortBy;
    }
    if (sortOrder) {
        query.sort_order = sortOrder;
    }
    return query;
};

/**
 * Fetch and render the show all table.
 */
let showAllRequest = null;
const showAllFetch = function(sortBy, sortOrder) {
    const tableContainer = $('#show-all-table-container');
    if (showAllRequest) {
        // A sort click can arrive before the previous response; do not race.
        showAllRequest.abort();
    }
    showAllRequest = $.get($('#show-all').data('url'), showAllQuery(sortBy, sortOrder), function(html) {
        if (!$('#show-all').prop('checked')) {
            // The admin changed a facet setting while this was loading. The data
            // form responded by unchecking the box and clearing the table, but it
            // cannot cancel the request, so ignore whatever comes back.
            return;
        }
        tableContainer.html(html);
        // Only an explicit refusal removes the button; see show-all.phtml.
        if ('none' === $('#show-all').data('addAllMode')) {
            tableContainer.find('#add-all').remove();
        }
        if (!sortBy) {
            // Only the checkbox calls this without a sort. Checking it makes the
            // table appear, so scroll down to show it. A sort click replaces a
            // table already on screen, where scrolling would just jog the sidebar.
            sidebarScrollTo($('#show-all-container'));
        }
    }).fail(function(jqXHR, textStatus) {
        if ('abort' === textStatus) {
            return;
        }
        tableContainer.html('<p class="error">' + Omeka.jsTranslate('Cannot show all. The result set is likely too large.') + '<p>');
    }).always(function() {
        showAllRequest = null;
    });
};

// Handle show all checkbox.
$(document).on('click', '#show-all', function(e) {
    if (this.checked) {
        showAllFetch();
    } else {
        // Abort too, or a response in flight refills what was just cleared.
        if (showAllRequest) {
            showAllRequest.abort();
        }
        $('#show-all-table-container').empty();
    }
});

// Handle a sort header click. The direction comes from aria-sort, so the markup
// is the single source of truth.
$(document).on('click', '.show-all-sort', function(e) {
    const thisButton = $(this);
    const sortBy = thisButton.data('sortBy');
    const ariaSort = thisButton.closest('th').attr('aria-sort');
    // Only an active column has a direction to reverse; a first click sends none,
    // leaving the default to getShowAllSort().
    const sortOrder = 'ascending' === ariaSort ? 'desc'
        : ('descending' === ariaSort ? 'asc' : null);
    showAllFetch(sortBy, sortOrder);
});

/**
 * Handle the add all button.
 *
 * The target field and mode are declared by the facet type's data form, so a facet
 * type needs no JavaScript of its own. Bails when nothing is declared, leaving an
 * older module's handler to own the click.
 */
$(document).on('click', '#add-all', function(e) {
    const showAll = $('#show-all');
    const target = showAll.data('addAllTarget');
    if (!target) {
        return;
    }
    const rows = $('#show-all-table').data('rows');
    const field = $(target);
    switch (showAll.data('addAllMode')) {
        case 'textarea':
            field.val($.map(rows, function(row) {
                // A label may be null or not a string, so normalize before
                // trimming a trailing line break. $.map drops null, so a blank
                // label adds no line. Not a falsy test, which would also drop "0".
                const label = String(row.label ?? '').trim();
                return '' === label ? null : label;
            }).join("\n"));
            break;
        case 'multi-select':
            $.each(rows, function(index, row) {
                field.find(`option[value="${row.id}"]`).prop('selected', true);
            });
            field.trigger('chosen:updated');
            break;
        default:
            return;
    }
    sidebarScrollTo(field.closest('.field'));
});

});
