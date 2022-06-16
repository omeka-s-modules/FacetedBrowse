const updateSelectList = function(selectList) {
    const facet = selectList.closest('.facet');
    const truncateValues = selectList.data('truncateValues');
    // First, sort the selected list items and prepend them to the list.
    const listItemsSelected = selectList.find('.value.selected')
        .closest('.value-select-list-item')
        .show()
        .sort(function(a, b) {
            // Subtracting seems to be cross-browser compatible.
            return $(a).data('index') - $(b).data('index');
        });
    listItemsSelected.prependTo(selectList);
    // Then, sort the unselected list items and append them to the list.
    const listItemsUnselected = selectList.find('.value:not(.selected)')
        .closest('.value-select-list-item')
        .show()
        .sort(function(a, b) {
            // Subtracting seems to be cross-browser compatible.
            return $(a).data('index') - $(b).data('index');
        });
    listItemsUnselected.appendTo(selectList);
    const listItems = selectList.find('.value-select-list-item');
    if (!truncateValues || truncateValues >= listItems.length) {
        // No need to show expand when list does not surpass configured limit.
        return;
    }
    if (selectList.hasClass('expanded')) {
        // No need to hide items when list is expanded.
        facet.find('.value-select-list-expand').hide();
        facet.find('.value-select-list-collapse').show();
        return;
    }
    if (truncateValues < listItemsSelected.length) {
        // Show all selected items even if they surpass the configured limit.
        listItemsUnselected.hide();
    } else {
        // Truncate to the configured limit.
        listItems.slice(truncateValues).hide();
    }
    const hiddenCount = listItems.filter(':hidden').length;
    facet.find('.value-select-list-hidden-count').text(`(${hiddenCount})`);
    facet.find('.value-select-list-expand').show();
    facet.find('.value-select-list-collapse').hide();
};

FacetedBrowse.registerFacetApplyStateHandler('value', function(facet, facetState) {
    const thisFacet = $(facet);
    const facetData = thisFacet.data('facetData');
    facetState = facetState ?? [];
    facetState.forEach(function(value) {
        if ('text_input' === facetData.select_type) {
            thisFacet.find('input.value').val(value);
        } else if ('single_select' === facetData.select_type) {
            thisFacet.find(`select.value option[data-value="${value}"]`)
                .prop('selected', true);
        } else {
            thisFacet.find(`input.value[data-value="${value}"]`)
                .prop('checked', true)
                .addClass('selected');
        }
    });
    if (['single_list', 'multiple_list'].includes(facetData.select_type)) {
        updateSelectList(thisFacet.find('.value-select-list'));
    }
});

$(document).ready(function() {

const container = $('#container');
let timerId;

const getQuery = function(index, property, type, text) {
    if (['ex', 'nex'].includes(type)) {
        return `property[${index}][joiner]=and&property[${index}][property]=${property}&property[${index}][type]=${type}`;
    }
    return `property[${index}][joiner]=and&property[${index}][property]=${property}&property[${index}][type]=${type}&property[${index}][text]=${encodeURIComponent(text)}`;
};

const handleUserInteraction = function(thisValue) {
    const facet = thisValue.closest('.facet');
    const facets = container.find('.facet[data-facet-type="value"]');
    let index = 0;
    switch (facet.data('facetData').select_type) {
        case 'single_list':
            facet.find('.value').not(thisValue).removeClass('selected');
            thisValue.prop('checked', !thisValue.hasClass('selected'));
        case 'multiple_list':
            thisValue.toggleClass('selected');
            break;
    }
    facets.each(function() {
        const thisFacet= $(this);
        const facetData = thisFacet.data('facetData');
        const queries = [];
        const state = [];
        if ('text_input' === facetData.select_type) {
            const input = thisFacet.find('.value');
            const property = input.data('propertyId');
            const type = facetData.query_type;
            const text = input.val();
            if (text) {
                queries.push(getQuery(index, property, type, text));
                state.push(text);
                index++;
            }
        } else if ('single_select' === facetData.select_type) {
            const select = thisFacet.find('.value option:selected');
            const property = select.data('propertyId');
            const type = facetData.query_type;
            const text = select.data('value');
            if (text) {
                queries.push(getQuery(index, property, type, text));
                state.push(text);
                index++;
            }
        } else {
            thisFacet.find('.value.selected').each(function() {
                const property = $(this).data('propertyId');
                const type = facetData.query_type;
                const text = $(this).data('value');
                queries.push(getQuery(index, property, type, text));
                state.push(text);
                index++;
            });
        }
        FacetedBrowse.setFacetState(thisFacet.data('facetId'), state, queries.join('&'));
    });
    FacetedBrowse.triggerStateChange();
};

// Handle single_select interaction.
container.on('change', 'select.value', function(e) {
    handleUserInteraction($(this));
});

// Handle single_list interaction.
container.on('click', 'input.value[type="radio"]', function(e) {
    const thisValue = $(this);
    handleUserInteraction(thisValue);
    updateSelectList(thisValue.closest('.value-select-list'));
});

// Handle multiple_list interaction.
container.on('click', 'input.value[type="checkbox"]', function(e) {
    const thisValue = $(this);
    handleUserInteraction(thisValue);
    updateSelectList(thisValue.closest('.value-select-list'));
});

// Handle text_input interaction.
container.on('keyup', 'input.value[type="text"]', function(e) {
    const thisValue = $(this);
    clearTimeout(timerId);
    timerId = setTimeout(function() {
        handleUserInteraction(thisValue);
    }, 350);
});

// Handle expand list button (show more)
container.on('click', '.value-select-list-expand', function(e) {
    e.preventDefault();
    const thisButton = $(this);
    const selectList = thisButton.closest('.facet').find('.value-select-list');
    selectList.addClass('expanded');
    updateSelectList(selectList);
});

// Handle collapse list button (show less)
container.on('click', '.value-select-list-collapse', function(e) {
    e.preventDefault();
    const thisButton = $(this);
    const selectList = thisButton.closest('.facet').find('.value-select-list');
    selectList.removeClass('expanded');
    updateSelectList(selectList);
});

});
