const FacetedBrowse = {

    facetAddEditHandlers: {},
    facetSetHandlers: {},
    facetStateChangeHandler: () => {},
    facetQueries: {},

    /**
     * Register a callback that handles facet add/edit.
     *
     * "Facet add/edit" happens when a user adds or edits a facet. Use this
     * handler to prepare the facet form for use. If needed, facet types should
     * register a handler in a script added in FacetTypeInterface::prepareDataForm().
     *
     * @param string facetType The facet type
     * @param function handler The callback that handles facet add/edit
     */
    registerFacetAddEditHandler: (facetType, handler) => {
        FacetedBrowse.facetAddEditHandlers[facetType] = handler;
    },
    /**
     * Register a callback that handles facet set.
     *
     * "Facet set" happens when a user finishes configuring the facet and sets
     * it. Use this handler to validate the facet form and, if it validates,
     * return the facet data object. If it does not validate, alert the user and
     * return nothing. All facet types should register a handler in a script
     * added in FacetTypeInterface::prepareDataForm().
     *
     * @param string facetType The facet type
     * @param function handler The callback that handles facet set
     */
    registerFacetSetHandler: (facetType, handler) => {
        FacetedBrowse.facetSetHandlers[facetType] = handler;
    },
    /**
     * Set the facet state.
     *
     * Via a script added in FacetTypeInterface::prepareFacet(), all facet types
     * should detect a user interaction, calculate the query needed to reflect
     * the current state of the facet, then set the query using this function.
     *
     * @param int facetId The facet ID
     * @param string facetQuery The facet query
     */
    setFacetState: (facetId, facetQuery) => {
        FacetedBrowse.facetQueries[facetId] = facetQuery;
    },
    /**
     * Trigger a facet state change.
     *
     * Via a script added in FacetTypeInterface::prepareFacet(), all facet types
     * should call this function once all relevant states have been set.
     */
    triggerFacetStateChange: () => {
        const queries = [];
        for (const facetId in FacetedBrowse.facetQueries) {
            queries.push(FacetedBrowse.facetQueries[facetId]);
        }
        FacetedBrowse.facetStateChangeHandler(queries.join('&'));
    },
    /**
     * Call a facet add/edit handler.
     *
     * @param string facetType The facet type
     */
    handleFacetAddEdit: facetType => {
        if (facetType in FacetedBrowse.facetAddEditHandlers) {
            FacetedBrowse.facetAddEditHandlers[facetType]();
        }
    },
    /**
     * Call a facet set handler.
     *
     * @param string facetType The facet type
     * @return object The facet data
     */
    handleFacetSet: facetType => {
        if (facetType in FacetedBrowse.facetSetHandlers) {
            return FacetedBrowse.facetSetHandlers[facetType]();
        }
    },
    /**
     * Set the callback that handles a facet state change.
     *
     * @param function handler The callback that handles facet state change
     */
    setFacetStateChangeHandler: (handler) => {
        FacetedBrowse.facetStateChangeHandler = handler;
    },
};
