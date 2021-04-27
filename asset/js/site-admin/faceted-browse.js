const FacetedBrowse = {

    facetAddEdit: {},
    facetSet: {},

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
     * @return object data
     */
    facetSet: facetType => {
        if (facetType in FacetedBrowse.facetSet) {
            return FacetedBrowse.facetSet[facetType]();
        }
    }
};
