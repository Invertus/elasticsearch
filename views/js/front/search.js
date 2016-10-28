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
    $bradSearchBox.on('focusout', '#bradSearchQuery', function () {
        setTimeout(clearInstantSearchResults, 200);
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
        var $decodedResponse = JSON.parse($response);

        // Handle instant search results
        if (typeof $decodedResponse.instant_results !== 'undefined' ||
            false !==  $decodedResponse.instant_results
        ) {
            clearInstantSearchResults();

            var $instantSearchResultsDiv = $('#bradInstantSearchResults');
            $instantSearchResultsDiv.html($decodedResponse.instant_results);
        }

        // Handle dynamic search results
        if (typeof $decodedResponse.dynamic_results !== 'undefined' ||
            false !==  $decodedResponse.dynamic_results
        ) {
            clearDynamicSearchResults(false);

            var $centerColumnDiv = $('#center_column');
            $centerColumnDiv.hide();

            var $bradDynamicSearchResults =
                $('<div id="bradDynamicSearchResults" class="center_column col-xs-12 col-sm-9"></div>');
            $bradDynamicSearchResults.html($decodedResponse.dynamic_results);

            $centerColumnDiv.after($bradDynamicSearchResults);
        }

        $sendingRequest = false;
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
    function clearDynamicSearchResults($displayDefaultProducts)
    {
        var $bradDynamicSearchResults = $('#bradDynamicSearchResults');
        $bradDynamicSearchResults.remove();

        if (typeof $displayDefaultProducts === 'undefined') {
            $displayDefaultProducts = true;
        }

        if ($displayDefaultProducts) {
            var $centerColumnDiv = $('#center_column');
            $centerColumnDiv.show();
        }
    }
});
