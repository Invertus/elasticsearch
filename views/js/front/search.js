$(document).ready(function() {

    var $sendingRequest = false;
    var $bradSearchBox = $('#bradSearchBox');

    /**
     * Add event listener to search input and bind callback
     */
    $bradSearchBox.on('input propertychange paste', '#bradSearchQuery', performSearch);

    /**
     * Clear instant search results when search input loses focus
     */
    $bradSearchBox.on('focusout', '#bradSearchQuery', function () {
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
            $sendingRequest = false;
            return;
        }

        if ($sendingRequest) {
            return;
        }

        $sendingRequest = true;

        $.ajax($globalBradSearchUrl, {
            'data': {
                'brad_search_query': $searchQuery
            },
            'dataType': 'html',
            'success': function ($response) {
                
                $sendingRequest = false;
            }
        });
    }

    /**
     * Clear instant search results
     */
    function clearInstantSearchResults()
    {
        $('#bradInstantSearchResults').html('');
    }
});
