/**
 * A facet type should:
 *
 * - Detect a user interaction;
 * - Calculate the query needed to reflect the current state of the facet;
 * - Set the query to the parent .facet element using .data('query', query);
 * - Trigger the "faceted-browse:query-state-change" event.
 *
 * This will detect the state change, iterate every facet, build a new,
 * consolidated query, and update the item browse section.
 */
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

// Handle a query state change.
container.on('faceted-browse:query-state-change', function(e) {
    const category = $('#category');
    const queries = [];
    // Iterate every facet, collecting queries.
    $('.facet').each(function() {
        queries.push($(this).data('query'));
    });
    $.get(`${urlBrowse}?${category.data('categoryQuery')}&${queries.join('&')}`, {}, function(html) {
        sectionContent.html(html);
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

});
