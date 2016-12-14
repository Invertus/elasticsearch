$(document).ready(function() {

    var $sendingRequest = false;

    performFiltering();
    addEventListeners();

    /**
     * Perform filtering
     */
    function performFiltering()
    {
        var $selectedFilters = getSelectedFilters();

        if ($sendingRequest) {
            if (typeof $xhr == 'object') {
                $xhr.abort();
            }
        }

        $sendingRequest = true;

        var $xhr = $.ajax($globalBradFilterUrl, {
            data: $selectedFilters,
            success: handleFilteringResponse
        });
    }

    /**
     * Handle filtering response
     *
     * @param $response
     */
    function handleFilteringResponse($response)
    {
        $sendingRequest = false;

        $response = JSON.parse($response);
        console.log($response);
        appendQueryStringToUrl($response.query_string);

        $("#bradFilterContainer").replaceWith($response.filters_template);
        updateUniform();

        var $centerColumn = $('#center_column');
        var $originalProductList = $centerColumn.find('.product_list');
        var $originalTopPagination = $centerColumn.find('.top-pagination-content');
        var $originalBottomPagination = $centerColumn.find('.bottom-pagination-content');

        var $topPaginationStyles = $originalTopPagination.attr('class');
        var $bottomPaginationStyles = $originalBottomPagination.attr('class');

        $('#bradProductList').remove();
        $('#bradTopPagination').remove();
        $('#bradBottomPagination').remove();

        if ($response.reset_original_layout) {
            $originalProductList.show();
            $originalTopPagination.show();
            $originalTopPagination.show();
            return;
        }

        $originalProductList.hide();
        $originalTopPagination.hide();
        $originalBottomPagination.hide();

        $originalProductList.after('<div id="bradProductList">' + $response.products_list + '</div>');
        $originalTopPagination.after('<div class="' + $topPaginationStyles + '" id="bradTopPagination">' + $response.top_pagination + '</div>');
        $originalBottomPagination.after('<div class="' + $bottomPaginationStyles + '" id="bradBottomPagination">' + $response.bottom_pagination + '</div>');

        scrollToProductList();
        addEventListeners();
    }
    
    /**
     * Handle input area values
     */
    function handleInputArea()
    {
        $('.brad-input-area').each(function ($index, $element) {

            var $inputName = $($element).data('input-name');

            $($element).on('focusout', '.brad-min-range, .brad-max-range', function() {
                var $defaultMinValue = $($element).find('.brad-min-range').data('default-min-value');
                var $defaultMaxValue = $($element).find('.brad-max-range').data('default-max-value');

                var $minValue = $($element).find('.brad-min-range').val();
                var $maxValue = $($element).find('.brad-max-range').val();

                var $input = $('input[name="' + $inputName +'"]');

                $input.val($minValue + ':' + $maxValue);

                if (($minValue != $defaultMinValue ||
                    $maxValue != $defaultMaxValue) &&
                    ($minValue.length != 0 &&
                    $maxValue.length != 0)
                ) {
                    $input.attr('checked', 'checked');
                } else {
                    $input.removeAttr('checked');
                }

                performFiltering();
            });
        });
    }

    /**
     * Handle slider input
     */
    function handleSlider()
    {
        $('.brad-slider').each(function($index, $element) {

            var $selectedMinValue = $($element).data('selected-min-value');
            var $selectedMaxValue = $($element).data('selected-max-value');
            var $defaultMinValue = $($element).data('min-value');
            var $defaultMaxValue = $($element).data('max-value');
            var $inputName = $($element).data('input-name');

            var $rangeMinValue = (typeof $selectedMinValue != 'undefined') ? $selectedMinValue : $defaultMinValue;
            var $rangeMaxValue = (typeof $selectedMaxValue != 'undefined') ? $selectedMaxValue : $defaultMaxValue;

            $($element).slider({
                range: true,
                min: $defaultMinValue,
                max: $defaultMaxValue,
                values: [$rangeMinValue, $rangeMaxValue],
                slide: function($event, $ui) {
                    var $selectedMinValue = $ui.values[0];
                    var $selectedMaxValue = $ui.values[1];

                    var $input = $('input[name="' + $inputName + '"]');

                    $input.val($ui.values[0] + ":" + $ui.values[1]);

                    if ($selectedMinValue != $defaultMinValue || $selectedMaxValue != $defaultMaxValue) {
                        $input.attr('checked', 'checked');
                    } else {
                        $input.removeAttr('checked');
                    }
                },
                stop: function() {
                    performFiltering();
                }
            });

            var $value = $($element).slider("values", 0) + ":" + $($element).slider("values", 1);
            if (typeof $selectedMaxValue != 'undefined' && typeof $selectedMinValue != 'undefined') {
                $value = $selectedMinValue + ':' + $selectedMaxValue;
            }

            $('input[name="' + $inputName + '"]').val($value);
        });
    }

    /**
     * Update uniform after inserting new content
     */
    function updateUniform()
    {
        if (typeof isMobile != 'undefined' && !isMobile && typeof $.fn.uniform !== 'undefined'){
            $('#bradFilterContainer').find('input[type="checkbox"]').uniform();
        }
    }

    /**
     * Get all selected filters values
     */
    function getSelectedFilters()
    {
        var $selectedFilters = {};

        $('.brad-checkbox-filter-input:checked, .brad-slider-filter-input[checked], .brad-input-filter-input[checked]').each(function($index, $element) {
            var $filterName = $($element).attr('name');
            var $filterValue = $($element).val();

            if (typeof $selectedFilters[$filterName] == 'undefined') {
                $selectedFilters[$filterName] = $filterValue;
            } else {
                $selectedFilters[$filterName] += '-' + $filterValue;
            }
        });

        var $bradFilterForm = $('#bradFilterForm');

        $selectedFilters['id_category'] = $globalIdCategory;
        $selectedFilters['orderway'] = $bradFilterForm.find('input[name="orderway"]').val();
        $selectedFilters['orderby'] = $bradFilterForm.find('input[name="orderby"]').val();
        $selectedFilters['p'] = $bradFilterForm.find('input[name="p"]').val();
        $selectedFilters['n'] = $bradFilterForm.find('input[name="n"]').val();

        return $selectedFilters;
    }

    /**
     * Append query string to url
     */
    function appendQueryStringToUrl($queryString)
    {
        if ($globalBaseUrl.indexOf('?') > -1) {
            window.history.pushState([], '', $globalBaseUrl + '&' + $queryString);
        } else {
            window.history.pushState([], '', $globalBaseUrl + '?' + $queryString);
        }
    }

    /**
     * Added event listeners to brad pagination buttons
     */
    function listenPaginationClick()
    {
        var $bradTopPagination = $('#center_column');

        $bradTopPagination.find('.top-pagination-content a, .bottom-pagination-content a').unbind();
        $bradTopPagination.find('.top-pagination-content a, .bottom-pagination-content a').on('click', function($event) {
            $event.preventDefault();
            var $page = $(this).find('span').text();
            //var $url = $event.currentTarget.href;
            //var $page = $url.match(new RegExp('p' + "=(.*?)($|\&)", "i"))[1];

            $('#bradFilterForm').find('input[name="p"]').val($page);
            performFiltering();
        });
    }

    /**
     * Add event listeners to filter form and inputs
     */
    function addEventListeners()
    {
        $('.brad-checkbox-filter-input').on('change', function() {

            $('#bradFilterForm').find('input[name="p"]').val(1);

            performFiltering();
        });
        handleSlider();
        handleInputArea();
        listenPaginationClick();
    }

    /**
     * Scroll to center column
     */
    function scrollToProductList()
    {
        $('body').animate({
            scrollTop: $('#productsSortForm').offset().top
        }, 100);
    }
});

