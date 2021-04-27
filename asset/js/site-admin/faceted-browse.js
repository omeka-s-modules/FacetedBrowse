const FacetedBrowse = {

    facetAddEdit: {},
    facetSet: {},
    facetQueries: {},

    /**
     * Register a callback that handles facet add/edit.
     *
     * @param string facetType The facet type
     * @param function callback The callback that handles facet add/edit
     */
    registerFacetAddEdit: (facetType, callback) => {
        FacetedBrowse.facetAddEdit[facetType] = callback;
    },
    /**
     * Register a callback that handles facet set.
     *
     * @param string facetType The facet type
     * @param function callback The callback that handles facet set
     */
    registerFacetSet: (facetType, callback) => {
        FacetedBrowse.facetSet[facetType] = callback;
    },
    /**
     * Call a facet add/edit handler.
     *
     * @param string facetType The facet type
     */
    facetAddEdit: facetType => {
        if (facetType in FacetedBrowse.facetAddEdit) {
            FacetedBrowse.facetAddEdit[facetType]();
        }
    },
    /**
     * Call a facet set handler.
     *
     * @param string facetType The facet type
     * @return object The facet data
     */
    facetSet: facetType => {
        if (facetType in FacetedBrowse.facetSet) {
            return FacetedBrowse.facetSet[facetType]();
        }
    },
    /**
     * Set a query by facet ID and trigger a state change.
     *
     * @param int facetId The facet ID
     * @param string facetQuery The facet query
     * @param bool triggerStateChange Trigger a state change?
     */
    setFacetQuery: (facetId, facetQuery, triggerStateChange = true) => {
        FacetedBrowse.facetQueries[facetId] = facetQuery;
        if (triggerStateChange) {
            // Consolidate the queries and fetch the browse content.
            const browseUrl = $('#container').data('urlBrowse');
            const categoryQuery = $('#category').data('categoryQuery');
            const queries = [];
            for (const facetId in FacetedBrowse.facetQueries) {
                queries.push(FacetedBrowse.facetQueries[facetId]);
            }
            $.get(`${browseUrl}?${categoryQuery}&${queries.join('&')}`, {}, function(html) {
                $('#section-content').html(html);
            });
        }
    },
};
