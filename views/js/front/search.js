/**
 * Copyright (c) 2016-2017 Invertus, JSC
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"),
 * to deal in the Software without restriction,
 * including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense,
 * and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so,
 * subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED,
 * INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.
 * IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT,
 * TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

$(document).ready(function() {

    /**
     * Center column div id
     * @type {string}
     */
    var CENTER_COLUMN_ID = '#center_column';

    var $bradSearchBox = $('#bradSearchBox');

    /**
     * Add event listener to search input and bind callback
     */
    $bradSearchBox.on('input propertychange paste focusin', '#bradSearchQuery', performSearch);

    /**
     * Clear instant search results when search input loses focus
     */
    $(document).on('click', clearInstantSearchResults);

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
            return;
        }

        $.ajax($globalBradSearchUrl, {
            'data': {
                'search_query': $searchQuery,
                'n': $globalBradInstantSearchResultsCount,
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
        if (false !==  $decodedResponse.instant_results) {
            clearInstantSearchResults();

            var $instantSearchResultsDiv = $('#bradInstantSearchResults');
            $instantSearchResultsDiv.html($decodedResponse.instant_results);
        }

        // Handle dynamic search results
        if (false !==  $decodedResponse.dynamic_results) {

            // Clear dynamic search results but dont show original center column
            clearDynamicSearchResults(false);

            var $centerColumnDiv = $(CENTER_COLUMN_ID);
            $centerColumnDiv.hide();

            // Copy center column classes
            var $centerColumnClasses = $centerColumnDiv.attr('class');

            // Create container for dynamic search results
            var $bradDynamicSearchResults = $('<div></div>');
            $bradDynamicSearchResults.attr('id', 'bradDynamicSearchResults');
            $bradDynamicSearchResults.addClass($centerColumnClasses);
            $bradDynamicSearchResults.html($decodedResponse.dynamic_results);

            // Add dynamic search results after hidden center column
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
            var $centerColumnDiv = $(CENTER_COLUMN_ID);
            $centerColumnDiv.show();
        }
    }
});
