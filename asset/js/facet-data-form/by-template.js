FacetedBrowse.registerFacetAddEditHandler('by_template', function() {
    $('#by-template-template-ids').chosen({
        include_group_label_in_selected: true
    });
});
FacetedBrowse.registerFacetSetHandler('by_template', function() {
    return {
        template_ids: $('#by-template-template-ids').val()
    };
});

$(document).ready(function() {

// Handle show all values.
$(document).on('click', '#by-template-show-all-templates', function(e) {
    const allTemplates = $('#by-template-all-templates');
    if (this.checked) {
        $.get(allTemplates.data('templatesUrl'), {
            query: $('#category-query').val()
        }, function(data) {
            if (data.length) {
                data.forEach(resourceTemplate => {
                    allTemplates.append(`<tr><td style="width: 90%; padding: 0; border-bottom: 1px solid #dfdfdf;">${resourceTemplate.label}</td><td style="width: 10%; padding: 0; border-bottom: 1px solid #dfdfdf;">${resourceTemplate.item_count}</td></tr>`);
                });
            } else {
                allTemplates.append(`<tr><td>${Omeka.jsTranslate('There are no available templates.')}</td></tr>`);
            }
        });
    } else {
        allTemplates.empty();
    }
});

});
