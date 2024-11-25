const FacetedBrowse = {

    facetAddEditHandlers: {},
    facetSetHandlers: {},
    columnAddEditHandlers: {},
    columnSetHandlers: {},
    facetApplyStateHandlers: {},
    stateChangeHandler: () => {},
    state: {},

    /**
     * Register a callback that handles facet add/edit.
     *
     * "Facet add/edit" happens when a user adds or edits a facet. Use this
     * handler to prepare the facet form for use. The handler will receive no
     * arguments. If needed, facet types should register a handler in a script
     * added in prepareDataForm().
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
     * return nothing. The handler will receive no arguments. All facet types
     * should register a handler in a script added in prepareDataForm().
     *
     * @param string facetType The facet type
     * @param function handler The callback that handles facet set
     */
    registerFacetSetHandler: (facetType, handler) => {
        FacetedBrowse.facetSetHandlers[facetType] = handler;
    },
    /**
     * Register a callback that handles column add/edit.
     *
     * "Column add/edit" happens when a user adds or edits a column. Use this
     * handler to prepare the column form for use. The handler will receive no
     * arguments. If needed, column types should register a handler in a script
     * added in prepareDataForm().
     *
     * @param string columnType The Column type
     * @param function handler The callback that handles column add/edit
     */
    registerColumnAddEditHandler: (columnType, handler) => {
        FacetedBrowse.columnAddEditHandlers[columnType] = handler;
    },
    /**
     * Register a callback that handles column set.
     *
     * "Column set" happens when a user finishes configuring the column and sets
     * it. Use this handler to validate the column form and, if it validates,
     * return the column data object. If it does not validate, alert the user and
     * return nothing. The handler will receive no arguments. All column types
     * should register a handler in a script added in prepareDataForm().
     *
     * @param string columnType The column type
     * @param function handler The callback that handles column set
     */
    registerColumnSetHandler: (columnType, handler) => {
        FacetedBrowse.columnSetHandlers[columnType] = handler;
    },
    /**
     * Register a callback that handles facet apply state.
     *
     * "Facet apply state" happens when the user returns to a page that has
     * been interacted with. Use this handler to apply a previously saved state
     * to a facet. The handler will receive a facet container element as the
     * first argument and the facet's state as the second argument. All facet
     * types should register a handler in a script added in prepareFacet().
     *
     * @param string facetType The facet type
     * @param function handler The callback that handles facet apply state
     */
    registerFacetApplyStateHandler: (facetType, handler) => {
        FacetedBrowse.facetApplyStateHandlers[facetType] = handler;
    },
    /**
     * Set the facet state.
     *
     * Via a script added in FacetTypeInterface::prepareFacet(), all facet types
     * should detect a user interaction, calculate the data needed to preserve
     * the current state of the facet, calculate the query needed to fetch the
     * items, then set them using this function.
     *
     * @param int facetId The facet ID
     * @param string facetQuery The facet query
     */
    setFacetState: (facetId, facetState, facetQuery) => {
        FacetedBrowse.state.facetQueries[facetId] = facetQuery;
        FacetedBrowse.state.facetStates[facetId] = facetState;
        // Must reset the pagination state after every user interaction because
        // the total results have likely changed.
        FacetedBrowse.state.page = null;
        FacetedBrowse.replaceHistoryState();
    },
    /**
     * Set the sorting state.
     *
     * @param string sortBy
     * @param string sortOrder
     */
    setSortingState: (sortBy, sortOrder) => {
        FacetedBrowse.state.sortBy = sortBy;
        FacetedBrowse.state.sortOrder = sortOrder;
        FacetedBrowse.replaceHistoryState();
    },
    /**
     * Set the pagination state.
     *
     * @param int page
     */
    setPaginationState: page => {
        FacetedBrowse.state.page = page;
        FacetedBrowse.replaceHistoryState();
    },
    /**
     * Trigger a state change.
     *
     * Via a script added in FacetTypeInterface::prepareFacet(), all facet types
     * should call this function once all relevant states have been set.
     */
    triggerStateChange: () => {
        const queries = [];
        for (const facetId in FacetedBrowse.state.facetQueries) {
            queries.push(FacetedBrowse.state.facetQueries[facetId]);
        }
        FacetedBrowse.stateChangeHandler(
            queries.join('&'),
            FacetedBrowse.state.sortBy,
            FacetedBrowse.state.sortOrder,
            FacetedBrowse.state.page,
        );
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
     * Call a column add/edit handler.
     *
     * @param string columnType The column type
     */
    handleColumnAddEdit: columnType => {
        if (columnType in FacetedBrowse.columnAddEditHandlers) {
            FacetedBrowse.columnAddEditHandlers[columnType]();
        }
    },
    /**
     * Call a column set handler.
     *
     * @param string columnType The column type
     * @return object The column data
     */
    handleColumnSet: columnType => {
        if (columnType in FacetedBrowse.columnSetHandlers) {
            return FacetedBrowse.columnSetHandlers[columnType]();
        }
    },
    /**
     * Call a facet apply state handler.
     *
     * @param string facetType The facet type
     * @param int facetId The unique facet ID
     * @param object facet The facet element
     */
    handleFacetApplyState: (facetType, facetId, facet) => {
        if (!(facetType in FacetedBrowse.facetApplyStateHandlers)) {
            return;
        }
        const facetApplyStateHandler = FacetedBrowse.facetApplyStateHandlers[facetType];
        const facetState = FacetedBrowse.state.facetStates[facetId];
        facetApplyStateHandler(facet, facetState);
    },
    /**
     * Set the callback that handles a state change.
     *
     * @param function handler The callback that handles state change
     */
    setStateChangeHandler: handler => {
        FacetedBrowse.stateChangeHandler = handler;
    },
    /**
     * Initialize the state.
     */
    initState: () => {
        try {
            // The client may pass the state via the URI fragment. If so, set it
            // as the history state. This will remove the fragment from the URL
            // for tidiness.
            const fragmentState = JSON.parse(decodeURIComponent(window.location.hash.substr(1)));
            history.replaceState(fragmentState, null, window.location.pathname);
        } catch (error) {
            // There was likely a "SyntaxError: Unexpected end of JSON input"
            // error. Do nothing.
        }

        // Check for valid history state.
        if ('object' === typeof history.state
            && null !== history.state
            && history.state.hasOwnProperty('categoryId')
            && history.state.hasOwnProperty('sortBy')
            && history.state.hasOwnProperty('sortOrder')
            && history.state.hasOwnProperty('page')
            && history.state.hasOwnProperty('facetStates')
            && history.state.hasOwnProperty('facetQueries')
        ) {
            FacetedBrowse.state = history.state;
        } else {
            // The state is not set or is malformed. Reset it.
            FacetedBrowse.resetState();
        }
    },
    /**
     * Reset the state.
     *
     * @param ?int categoryId The current category ID
     */
    resetState: (categoryId = null) => {
        FacetedBrowse.state.categoryId = categoryId;
        FacetedBrowse.state.sortBy = null;
        FacetedBrowse.state.sortOrder = null;
        FacetedBrowse.state.page = null;
        FacetedBrowse.state.facetStates = {};
        FacetedBrowse.state.facetQueries = {};
        FacetedBrowse.replaceHistoryState();
    },
    /**
     * Replace the current history entry of this page.
     */
    replaceHistoryState: () => {
        history.replaceState(FacetedBrowse.state, null);
    },
    /**
     * Get a specific state by name.
     *
     * @param string stateName
     */
    getState: stateName => {
        return history.state.hasOwnProperty(stateName) ? history.state[stateName] : undefined;
    },

    updateSelectList: selectList => {
        const facet = selectList.closest('.facet');
        const truncateListItems = selectList.data('truncateListItems');
        // First, sort the selected list items and prepend them to the list.
        const listItemsSelected = selectList.find('input.selected')
            .closest('.select-list-item')
            .show()
            .sort(function(a, b) {
                // Subtracting seems to be cross-browser compatible.
                return $(a).data('index') - $(b).data('index');
            });
        listItemsSelected.prependTo(selectList);
        // Then, sort the unselected list items and append them to the list.
        const listItemsUnselected = selectList.find('input:not(.selected)')
            .closest('.select-list-item')
            .show()
            .sort(function(a, b) {
                // Subtracting seems to be cross-browser compatible.
                return $(a).data('index') - $(b).data('index');
            });
        listItemsUnselected.appendTo(selectList);
        const listItems = selectList.find('.select-list-item');
        if (!truncateListItems || truncateListItems >= listItems.length) {
            // No need to show expand when list does not surpass configured limit.
            return;
        }
        if (selectList.hasClass('expanded')) {
            // No need to hide items when list is expanded.
            facet.find('.select-list-expand').hide();
            facet.find('.select-list-collapse').show();
            return;
        }
        if (truncateListItems < listItemsSelected.length) {
            // Show all selected items even if they surpass the configured limit.
            listItemsUnselected.hide();
        } else {
            // Truncate to the configured limit.
            listItems.slice(truncateListItems).hide();
        }
        const hiddenCount = listItems.filter(':hidden').length;
        facet.find('.select-list-hidden-count').text(`(${hiddenCount})`);
        facet.find('.select-list-expand').show();
        facet.find('.select-list-collapse').hide();
    },
};
