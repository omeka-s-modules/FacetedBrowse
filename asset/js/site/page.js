$(document).ready(function() {

const sectionSidebar = $('#section-sidebar');
const sectionContent = $('#section-content');

$.get(sectionSidebar.data('categoriesUrl'), {}, function(html) {
    sectionSidebar.html(html);
});
$.get(sectionContent.data('browseUrl'), {}, function(html) {
    sectionContent.html(html);
});
sectionSidebar.on('click', '.category', function(e) {
    e.preventDefault();
});
sectionContent.on('click', '.resource-link', function(e) {
    e.preventDefault();
});

});
