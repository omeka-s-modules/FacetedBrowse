$(document).ready(function() {

const container = $('#container');
const sectionSidebar = $('#section-sidebar');
const sectionContent = $('#section-content');
const browseStatus = $('#browse-status');
let focusOnLoad = false;

const urlCategories = container.data('urlCategories');
const urlFacets = container.data('urlFacets');
const urlBrowse = container.data('urlBrowse');

const modalToggleButton = $("#section-sidebar-modal-toggle");
const modalCloseButton = $('#section-sidebar-modal-close');

// Callbacks that handle  request errors.
const makeFail = function(msg) {
    return function(data) {
        focusOnLoad = false;
        sectionContent.html(`${Omeka.jsTranslate(msg)} ${data.status} (${data.statusText})`).attr('aria-busy', 'false');
    };
};
const failBrowse = makeFail('Error fetching browse markup.');
const failFacet = makeFail('Error fetching facet markup.');
const failCategory = makeFail('Error fetching category markup.');

const setBrowseStatus = function() {
    const rowCount = sectionContent.find('.row-count').first().text();
    browseStatus.text(rowCount || Omeka.jsTranslate('No results'));
};

// Set breakpoint for using facet modal window.
const mediaQuery = window.matchMedia('(max-width: 39.9988em)');

// Reset modal attributes for desktop widths.
const handleTabletChange = function(e) {
    if (e.matches) {
        const dialogWrapper = $('<dialog id="section-sidebar-dialog" aria-label="Mobile dialog" aria-labelledby="section-sidebar-dialog section-sidebar"></dialog>');
        sectionSidebar.wrap(dialogWrapper);
    } else if (document.getElementById('section-sidebar-dialog')) {
        closeModal();
        sectionSidebar.unwrap();
    }
};

// Implements modal behavior for facet sidebar on mobile widths.
const enableModal = function() {
    container.on('click', '#section-sidebar-modal-toggle', function() {
        const activeDialog = document.getElementById('section-sidebar-dialog');
        modalToggleButton.attr('aria-expanded', 'true');
        activeDialog.showModal();
        sectionSidebar.find('button').first().focus();
        activeDialog.addEventListener('close', function() {
            modalToggleButton.attr('aria-expanded', 'false');
            // Results may still be loading if the user closes the modal quickly.
            if (sectionContent.attr('aria-busy') === 'true') {
                focusOnLoad = true;
            } else {
                sectionContent.focus();
            }
        });
    });

    container.on('click', '#section-sidebar .close-button', function() {
        closeModal();
    });
};

const closeModal = function() {
    const activeModal = document.getElementById('section-sidebar-dialog');
    if (activeModal) {
        activeModal.close();
    }
    modalToggleButton.attr('aria-expanded', 'false');
}

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
        $('.categories-container').find('a,input,button,select').first().focus();
        $.get(urlBrowse).done(function(html) {
            sectionContent.html(html);
            setBrowseStatus();
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

    // Add facets, sorting, and pagination queries.
    queries.push(facetsQuery);
    if (null !== sortBy) queries.push(`sort_by=${sortBy}`);
    if (null !== sortOrder) queries.push(`sort_order=${sortOrder}`);
    if (null !== page) queries.push(`page=${page}`);
    queries.push(`faceted_browse_category_id=${facets.data('categoryId')}`);
    sectionContent.text(Omeka.jsTranslate('Loading results…')).addClass('loading').attr('aria-busy', 'true');
    $.get(`${urlBrowse}?${queries.join('&')}`).done(function(html) {
        sectionContent.html(html).removeClass('loading');
        setBrowseStatus();
        sectionContent.attr('aria-busy', 'false');
        setPermalinkFragment();
        if (focusOnLoad) {
            sectionContent.focus();
            focusOnLoad = false;
        }
    }).fail(failBrowse);
});

// Then, set up the page for first load.
if (FacetedBrowse.getState('categoryId')) {
    // This page has a previously saved category state.
    focusOnLoad = true;
    $.get(urlFacets, {category_id: FacetedBrowse.getState('categoryId')}).done(function(html) {
        sectionSidebar.html(html);
        applyPreviousState();
    }).fail(failFacet);
} else if (container.data('categoryId')) {
    // There is one category. Skip categories list and show facets list.
    focusOnLoad = true;
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
    FacetedBrowse.resetState(thisCategory.data('categoryId'));
    $.get(urlFacets, {category_id: thisCategory.data('categoryId')}).done(function(html) {
        sectionSidebar.html(html);
        sectionSidebar.find('.select-list').each(function() {
            // Must update the select lists so they are truncated.
            FacetedBrowse.updateSelectList($(this));
        });
        $('.facets-container').find('a,input,button,select').first().focus();
        const queries = [];
        queries.push(`faceted_browse_category_id=${thisCategory.data('categoryId')}`);
        $.get(`${urlBrowse}?${queries.join('&')}`).done(function(html) {
            sectionContent.html(html);
            setBrowseStatus();
            setPermalinkFragment();
        }).fail(failBrowse);
    }).fail(failFacet);
});

// Handle a categories return click.
container.on('click', '#categories-return', function(e) {
    FacetedBrowse.resetState();
    renderCategories();
});

// Handle pagination next button.
container.on('click', '.next', function(e) {
    e.preventDefault();
    const thisButton = $(this);
    if (!thisButton.hasClass('inactive')) {
        const page = parseInt(thisButton.closest('.pagination').find('input[name="page"]').val()) + 1;
        FacetedBrowse.setPaginationState(page);
        $.get(thisButton.prop('href'), function(html) {
            sectionContent.html(html);
            setBrowseStatus();
        });
    }
});

// Handle pagination previous button.
container.on('click', '.previous', function(e) {
    e.preventDefault();
    const thisButton = $(this);
    if (!thisButton.hasClass('inactive')) {
        const page = parseInt(thisButton.closest('.pagination').find('input[name="page"]').val()) - 1;
        FacetedBrowse.setPaginationState(page);
        $.get(thisButton.prop('href'), function(html) {
            sectionContent.html(html);
            setBrowseStatus();
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
        setBrowseStatus();
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
        setBrowseStatus();
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

enableModal();
mediaQuery.addListener(handleTabletChange);
handleTabletChange(mediaQuery);

});
