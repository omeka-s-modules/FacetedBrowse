$(document).ready(function() {

const container = $('#container');
const sectionSidebar = $('#section-sidebar');
const sectionContent = $('#section-content');

const urlCategories = container.data('urlCategories');
const urlFacets = container.data('urlFacets');
const urlBrowse = container.data('urlBrowse');

$.get(urlCategories, {}, function(html) {
    sectionSidebar.html(html);
});
$.get(urlBrowse, {}, function(html) {
    sectionContent.html(html);
});
// Set the facet state change handler.
FacetedBrowse.setFacetStateChangeHandler(function() {
    const queries = [];
    for (const facetId in FacetedBrowse.facetQueries) {
        queries.push(FacetedBrowse.facetQueries[facetId]);
    }
    $.get(`${urlBrowse}?${$('#category').data('categoryQuery')}&${queries.join('&')}`, {}, function(html) {
        $('#section-content').html(html);
    });
});
// Handle category click.
container.on('click', '.category', function(e) {
    e.preventDefault();
    const thisCategory = $(this);
    $.get(urlFacets, {
        category_id: thisCategory.data('categoryId')
    }, function(html) {
        sectionSidebar.html(html);
        $.get(`${urlBrowse}?${thisCategory.data('categoryQuery')}`, {}, function(html) {
            sectionContent.html(html);
        });
    });
});
// Handle a categories return click.
container.on('click', '#categories-return', function(e) {
    e.preventDefault();
    $.get(urlCategories, {}, function(html) {
        sectionSidebar.html(html);
    });
    $.get(urlBrowse, {}, function(html) {
        sectionContent.html(html);
    });
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
