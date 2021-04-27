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
     * Set a query by facet ID and trigger a state change.
     *
     * All facet types should:
     *
     * - Detect a user interaction;
     * - Calculate the query needed to reflect the current state of the facet;
     * - Set the query using this function;
     * - All via a script added in FacetTypeInterface::prepareFacet().
     *
     * This will detect the state change, iterate every facet, build a new,
     * consolidated query, and update the item browse section.
     *
     * Set triggerStateChange to false if for any reason the state change should
     * not be triggered.
     *
     * @param int facetId The facet ID
     * @param string facetQuery The facet query
     * @param bool triggerStateChange Trigger a state change?
     */
    setFacetQuery: (facetId, facetQuery, triggerStateChange = true) => {
        FacetedBrowse.facetQueries[facetId] = facetQuery;
        if (triggerStateChange) {
            FacetedBrowse.facetStateChangeHandler();
        }
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
