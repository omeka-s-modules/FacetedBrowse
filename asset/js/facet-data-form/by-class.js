$(document).ready(function() {

FacetedBrowse.registerFacetAddEditHandler('by_class', function() {
    $('#by-class-class-ids').chosen({
        include_group_label_in_selected: true
    });
});
FacetedBrowse.registerFacetSetHandler('by_class', function() {
    return {
        class_ids: $('#by-class-class-ids').val()
    };
});
// Handle show all values.
$(document).on('click', '#by-class-show-all-classes', function(e) {
    const allClasses = $('#by-class-all-classes');
    if (this.checked) {
        $.get(allClasses.data('classesUrl'), {
            query: $('#category-query').val()
        }, function(data) {
            if (data.length) {
                data.forEach(resourceClass => {
                    allClasses.append(`<tr><td style="width: 90%; padding: 0; border-bottom: 1px solid #dfdfdf;">${resourceClass.label}</td><td style="width: 10%; padding: 0; border-bottom: 1px solid #dfdfdf;">${resourceClass.item_count}</td></tr>`);
                });
            } else {
                allClasses.append(`<tr><td>${Omeka.jsTranslate('There are no available classes.')}</td></tr>`);
            }
        });
    } else {
        allClasses.empty();
    }
});

});
