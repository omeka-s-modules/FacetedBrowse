$(document).ready(function() {

const container = $('#container');
const sectionSidebar = $('#section-sidebar');
const sectionContent = $('#section-content');

const urlCategories = container.data('urlCategories');
const urlFacets = container.data('urlFacets');
const urlBrowse = container.data('urlBrowse');

const failBrowse = function(data) {
    sectionContent.html(`${Omeka.jsTranslate('Error fetching browse markup.')} ${data.status} (${data.statusText})`);
};
const failFacet = function(data) {
    sectionContent.html(`${Omeka.jsTranslate('Error fetching facet markup.')} ${data.status} (${data.statusText})`);
};
const failCategory = function(data) {
    sectionContent.html(`${Omeka.jsTranslate('Error fetching category markup.')} ${data.status} (${data.statusText})`);
};

if (container.data('categoryId')) {
    // There is one category. Skip categories list and show facets list.
    $.get(urlFacets, {
        category_id: container.data('categoryId')
    }).done(function(html) {
        sectionSidebar.html(html);
        $('#categories-return').hide();
        $.get(`${urlBrowse}?${container.data('categoryQuery')}`).done(function(html) {
            sectionContent.html(html);
        }).fail(failBrowse);
    }).fail(failFacet);
} else {
    // There is more than one category. Show category list.
    $.get(urlCategories).done(function(html) {
        sectionSidebar.html(html);
        $.get(urlBrowse).done(function(html) {
            sectionContent.html(html);
        }).fail(failBrowse);
    }).fail(failCategory);
}
// Set the facet state change handler.
FacetedBrowse.setFacetStateChangeHandler(function() {
    const queries = [];
    for (const facetId in FacetedBrowse.facetQueries) {
        queries.push(FacetedBrowse.facetQueries[facetId]);
    }
    $.get(`${urlBrowse}?${$('#facets').data('categoryQuery')}&${queries.join('&')}`).done(function(html) {
        sectionContent.html(html)
    }).fail(failBrowse);
});
// Handle category click.
container.on('click', '.category', function(e) {
    e.preventDefault();
    const thisCategory = $(this);
    // Reset category queries so queries from other categories aren't applied.
    FacetedBrowse.facetQueries = {};
    $.get(urlFacets, {category_id: thisCategory.data('categoryId')}).done(function(html) {
        sectionSidebar.html(html);
        $.get(`${urlBrowse}?${thisCategory.data('categoryQuery')}`).done(function(html) {
            sectionContent.html(html);
        }).fail(failBrowse);
    }).fail(failFacet);
});
// Handle a categories return click.
container.on('click', '#categories-return', function(e) {
    e.preventDefault();
    $.get(urlCategories).done(function(html) {
        sectionSidebar.html(html);
        $.get(urlBrowse).done(function(html) {
            sectionContent.html(html);
        }).fail(failBrowse);
    }).fail(failCategory);
});
// Handle item click.
container.on('click', '.resource-link', function(e) {
    e.preventDefault();
});
// Handle pagination next button.
container.on('click', '.next', function(e) {
    e.preventDefault();
    const thisButton = $(this);
    if (!thisButton.hasClass('inactive')) {
        $.get($(this).prop('href'), {}, function(html) {
            sectionContent.html(html);
        });
    }
});
// Handle pagination previous button.
container.on('click', '.previous', function(e) {
    e.preventDefault();
    const thisButton = $(this);
    if (!thisButton.hasClass('inactive')) {
        $.get(thisButton.prop('href'), {}, function(html) {
            sectionContent.html(html);
        });
    }
});
// Handle pagination form and sort form.
container.on('submit', '.pagination form, form.sorting', function(e) {
    e.preventDefault();
    $.get(`${urlBrowse}?${$(this).serialize()}`, {}, function(html) {
        sectionContent.html(html);
    });
});
// Handle permalink.
container.on('focus', '.permalink', function(e) {
    this.select();
});

});
