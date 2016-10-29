$(document).ready(function() {

    var $sendingRequest = false;
    var $bradSearchBox = $('#bradSearchBox');

    /**
     * Add event listener to search input and bind callback
     */
    $bradSearchBox.on('input propertychange paste focusin', '#bradSearchQuery', performSearch);

    /**
     * Clear instant search results when search input loses focus
     */
    $bradSearchBox.on('focusout', '#bradSearchQuery', function() {
        setTimeout(clearInstantSearchResults, 100);
    });

    /**
     * Perform search & handle response
     */
    function performSearch()
    {
        var $bradSearchInput = $(this);
        var $searchQuery = $bradSearchInput.val();

        if ($searchQuery.length < $globalBradMinWordLength) {
            clearInstantSearchResults();
            clearDynamicSearchResults();

            $sendingRequest = false;
            return;
        }

        if ($sendingRequest) {
            return;
        }

        $sendingRequest = true;

        $.ajax($globalBradSearchUrl, {
            'data': {
                'query': $searchQuery,
                'token': static_token
            },
            'dataType': 'html',
            'success': handleSearchResponse
        });
    }

    /**
     * Handle search response
     *
     * @param $response JSON string
     */
    function handleSearchResponse($response)
    {
        $sendingRequest = false;

        var $decodedResponse = JSON.parse($response);

        // Handle instant search results
        if (false !==  $decodedResponse.instant_results) {
            clearInstantSearchResults();

            var $instantSearchResultsDiv = $('#bradInstantSearchResults');
            $instantSearchResultsDiv.html($decodedResponse.instant_results);
        }

        // Handle dynamic search results
        if (false !==  $decodedResponse.dynamic_results) {

            clearDynamicSearchResults(false);

            var $centerColumnDiv = $('#center_column');
            $centerColumnDiv.hide();

            // Copy center column classes
            var $centerColumnClasses = $centerColumnDiv.attr('class');

            var $bradDynamicSearchResults = $('<div></div>');
            $bradDynamicSearchResults.attr('id', 'bradDynamicSearchResults');
            $bradDynamicSearchResults.addClass($centerColumnClasses);
            $bradDynamicSearchResults.html($decodedResponse.dynamic_results);

            $centerColumnDiv.after($bradDynamicSearchResults);
        }
    }

    /**
     * Clear instant search results
     */
    function clearInstantSearchResults()
    {
        var $instantSearchResultsDiv = $('#bradInstantSearchResults');
        $instantSearchResultsDiv.html('');
    }

    /**
     * Clear dynamic serach results
     *
     * @params $displayDefaultProducts
     */
    function clearDynamicSearchResults($displayCenterBlock)
    {
        var $bradDynamicSearchResults = $('#bradDynamicSearchResults');
        $bradDynamicSearchResults.remove();

        if (typeof $displayCenterBlock === 'undefined') {
            $displayCenterBlock = true;
        }

        if ($displayCenterBlock) {
            var $centerColumnDiv = $('#center_column');
            $centerColumnDiv.show();
        }
    }
});
