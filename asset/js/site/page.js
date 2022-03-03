$(document).ready(function() {

const container = $('#container');
const sectionSidebar = $('#section-sidebar');
const sectionContent = $('#section-content');

const urlCategories = container.data('urlCategories');
const urlFacets = container.data('urlFacets');
const urlBrowse = container.data('urlBrowse');

// Callbacks that handle  request errors.
const failBrowse = data => sectionContent.html(`${Omeka.jsTranslate('Error fetching browse markup.')} ${data.status} (${data.statusText})`);
const failFacet = data => sectionContent.html(`${Omeka.jsTranslate('Error fetching facet markup.')} ${data.status} (${data.statusText})`);
const failCategory = data => sectionContent.html(`${Omeka.jsTranslate('Error fetching category markup.')} ${data.status} (${data.statusText})`);

// Show that a copy to clipboard was successful.
const showClipboardCopySuccessful = () => {
    // Indicate successful copy here
    $('.permalink .success').addClass('active').show();
    $('.permalink .default').addClass('inactive');
    setTimeout(function() {
        $('.permalink .success').fadeOut(1000, function() {
            $(this).removeClass('active');
            $('.permalink .default').removeClass('inactive');
        });
    }, 1500);
};

/**
 * Apply a previous state to the page.
 */
const applyPreviousState = function() {
    $('.facet').each(function() {
        const thisFacet = $(this);
        FacetedBrowse.handleFacetApplyState(thisFacet.data('facetType'), thisFacet.data('facetId'), this);
    });
    FacetedBrowse.triggerStateChange();
};

/**
 * Set the permalink fragment.
 */
const setPermalinkFragment = function() {
    const fragment = encodeURIComponent(JSON.stringify(FacetedBrowse.state))
    $('.permalink').data('fragment', fragment);
};

/**
 * Render the categories of this page.
 */
const renderCategories = function() {
    $.get(urlCategories).done(function(html) {
        sectionSidebar.html(html);
        $.get(urlBrowse).done(function(html) {
            sectionContent.html(html);
            setPermalinkFragment();
        }).fail(failBrowse);
    }).fail(failCategory);
};

// First, initialize the state.
FacetedBrowse.initState();

// Then, set the state change handler.
FacetedBrowse.setStateChangeHandler(function(facetsQuery, sortBy, sortOrder, page) {
    const facets = $('#facets');
    const queries = [];
    // Add category and facets queries.
    queries.push(facets.data('categoryQuery'));
    queries.push(facetsQuery);
    // Add sorting and pagination queries.
    if (null !== sortBy) queries.push(`sort_by=${sortBy}`);
    if (null !== sortOrder) queries.push(`sort_order=${sortOrder}`);
    if (null !== page) queries.push(`page=${page}`);
    queries.push(`faceted_browse_category_id=${facets.data('categoryId')}`);
    sectionContent.text(Omeka.jsTranslate('Loading results…'));
    $.get(`${urlBrowse}?${queries.join('&')}`).done(function(html) {
        sectionContent.html(html);
        setPermalinkFragment();
    }).fail(failBrowse);
});

// Then, set up the page for first load.
if (FacetedBrowse.getState('categoryId')) {
    // This page has a previously saved category state.
    $.get(urlFacets, {category_id: FacetedBrowse.getState('categoryId')}).done(function(html) {
        sectionSidebar.html(html);
        applyPreviousState();
    }).fail(failFacet);
} else if (container.data('categoryId')) {
    // There is one category. Skip categories list and show facets list.
    $.get(urlFacets, {category_id: container.data('categoryId')}).done(function(html) {
        sectionSidebar.html(html);
        applyPreviousState();
        $('#categories-return').hide();
    }).fail(failFacet);
} else {
    // There is more than one category. Show category list.
    renderCategories();
}

// Handle category click.
container.on('click', '.category', function(e) {
    e.preventDefault();
    const thisCategory = $(this);
    FacetedBrowse.resetState(thisCategory.data('categoryId'), thisCategory.data('categoryQuery'));
    $.get(urlFacets, {category_id: thisCategory.data('categoryId')}).done(function(html) {
        sectionSidebar.html(html);
        const queries = [];
        queries.push(thisCategory.data('categoryQuery'));
        queries.push(`faceted_browse_category_id=${thisCategory.data('categoryId')}`);
        $.get(`${urlBrowse}?${queries.join('&')}`).done(function(html) {
            sectionContent.html(html);
            setPermalinkFragment();
        }).fail(failBrowse);
    }).fail(failFacet);
});

// Handle a categories return click.
container.on('click', '#categories-return', function(e) {
    e.preventDefault();
    FacetedBrowse.resetState();
    renderCategories();
});

// Handle pagination next button.
container.on('click', '.next', function(e) {
    e.preventDefault();
    const thisButton = $(this);
    const form = thisButton.prevAll('form');
    if (!thisButton.hasClass('inactive')) {
        FacetedBrowse.setPaginationState(parseInt(form.find('input[name="page"]').val()) + 1);
        $.get(thisButton.prop('href'), function(html) {
            sectionContent.html(html);
        });
    }
});

// Handle pagination previous button.
container.on('click', '.previous', function(e) {
    e.preventDefault();
    const thisButton = $(this);
    const form = thisButton.prevAll('form');
    if (!thisButton.hasClass('inactive')) {
        FacetedBrowse.setPaginationState(parseInt(form.find('input[name="page"]').val()) - 1);
        $.get(thisButton.prop('href'), function(html) {
            sectionContent.html(html);
        });
    }
});

// Handle pagination form.
container.on('submit', '.pagination form', function(e) {
    e.preventDefault();
    const thisForm = $(this);
    FacetedBrowse.setPaginationState(thisForm.find('input[name="page"]').val());
    $.get(`${urlBrowse}?${$(this).serialize()}`, {}, function(html) {
        sectionContent.html(html);
        setPermalinkFragment();
    });
});

// Handle sort form.
container.on('submit', 'form.sorting', function(e) {
    e.preventDefault();
    const thisForm = $(this);
    FacetedBrowse.setSortingState(
        thisForm.find('select[name="sort_by"]').val(),
        thisForm.find('select[name="sort_order"]').val()
    );
    $.get(`${urlBrowse}?${$(this).serialize()}`, {}, function(html) {
        sectionContent.html(html);
        setPermalinkFragment();
    });
});

// Handle permalink button (copy to clipboard button).
container.on('click', '.permalink', function(e) {
    e.preventDefault();
    const thisButton = $(this);
    const permalink = `${thisButton.data('url')}#${thisButton.data('fragment')}`;

    if (navigator.clipboard && window.isSecureContext) {
        // Use the browser's clipboard API if possible.
        navigator.clipboard.writeText(permalink).then(function() {
            showClipboardCopySuccessful();
        });
    } else {
        // Fall back on the temporary input / execCommand('copy') hack.
        const tempInput = $('<input>');
        $('body').append(tempInput);
        tempInput.val(permalink).select();
        document.execCommand('copy');
        tempInput.remove();
        showClipboardCopySuccessful();
    }
});

});
