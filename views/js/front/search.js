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

        if (false !== $decodedResponse.instant_results) {

            clearInstantSearchResults();

            var $instantSearchResultsDiv = $('#bradInstantSearchResults');
            $instantSearchResultsDiv.html($decodedResponse.instant_results);
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
});
