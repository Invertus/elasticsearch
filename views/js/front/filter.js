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

    var $xhr;
    var $sendingRequest = false;
    var $scrollToList = false;
    var $pageLoad = true;
    var $bradLoader = $('#bradFilterLoader');

    var $centerColumn = $('#center_column');
    var $originalProductList = $centerColumn.find('.product_list');
    var $originalTopPagination = $centerColumn.find('.top-pagination-content');
    var $originalBottomPagination = $centerColumn.find('.bottom-pagination-content');
    var $originalHeadingCounter = $centerColumn.find('.heading-counter');

    // Dont scroll on page load
    performFiltering($scrollToList);
    addEventListeners();
    listenSortingAndProductPerPageChange();

    /**
     * Perform filtering
     */
    function performFiltering($scrollToList)
    {
        $scrollToList = (typeof $scrollToList === 'undefined') ? true : $scrollToList;

        var $selectedFilters = getSelectedFilters();

        if ($sendingRequest) {
            $xhr.abort();
        }

        $sendingRequest = true;

        $xhr = $.ajax($globalBradFilterUrl, {
            data: $selectedFilters,
            beforeSend: function() {
                $originalProductList.css('opacity', '0.6');
                $originalProductList.prepend($bradLoader.clone().show());
            },
            complete: function() {
                $originalProductList.css('opacity', '1');
                $originalProductList.find('#bradFilterLoader').remove();
            },
            success: function ($response) {
                if ($pageLoad) {
                    $originalProductList.empty();
                    $originalTopPagination.empty();
                    $originalTopPagination.empty();
                    $originalBottomPagination.empty();

                    $pageLoad = false;
                }


                handleFilteringResponse($response);

                if ($scrollToList) {
                    scrollToProductList();
                }
            }
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

        appendQueryStringToUrl($response.query_string);

        $("#bradFilterContainer").replaceWith($response.filters_block_template);
        updateUniform();

        var $topPaginationStyles = $originalTopPagination.attr('class');

        // Remove previously inserted html
        $('#bradSelectedFilters').remove();

        $originalProductList.html('<div id="bradProductList">' + $response.products_list_template + '</div>');
        $originalTopPagination.html('<div class="' + $topPaginationStyles + '" id="bradTopPagination">' + $response.top_pagination_template + '</div>');
        $originalBottomPagination.html('<div id="bradBottomPagination">' + $response.bottom_pagination_template + '</div>');
        $originalTopPagination.before($response.selected_filters_template);
        $originalHeadingCounter.html($response.category_count_template);

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
        $selectedFilters['orderway']    = $bradFilterForm.find('input[name="orderway"]').val();
        $selectedFilters['orderby']     = $bradFilterForm.find('input[name="orderby"]').val();
        $selectedFilters['p']           = $bradFilterForm.find('input[name="p"]').val();
        $selectedFilters['n']           = $bradFilterForm.find('input[name="n"]').val();

        return $selectedFilters;
    }

    /**
     * Append query string to url
     */
    function appendQueryStringToUrl($queryString)
    {
        window.history.pushState([], '', $globalBaseUrl);

        if (!$queryString) {
            return;
        }

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

            var $url = $event.currentTarget.href;
            var $data = $url.match(new RegExp("p=(.*?)($|\&)", "i"));

            var $page = 1;

            if ($data) {
                $page = $data[1];
            }

            $('#bradFilterForm').find('input[name="p"]').val($page);
            performFiltering();
        });
    }

    /**
     * Add custom event listener to sorting
     */
    function listenSortingAndProductPerPageChange()
    {
        var $sortingForm = $('#productsSortForm');

        $sortingForm.on('change', function ($event) {
            $event.preventDefault();
            $event.stopPropagation();

            var $selectedValue = $(this).find('select').val();
            $selectedValue = $selectedValue.split(':');

            var $bradFilterForm = $('#bradFilterForm');
            $bradFilterForm.find('input[name="orderby"]').val($selectedValue[0]);
            $bradFilterForm.find('input[name="orderway"]').val($selectedValue[1]);

            $bradFilterForm.find('input[name="p"]').val(1);
            performFiltering();

            return false;
        });

        var $productsNumberForm = $('#nb_item');
        $productsNumberForm.on('change', function ($event) {
            $event.preventDefault();
            $event.stopPropagation();

            var $selectedValue = $(this).val();

            var $bradFilterForm = $('#bradFilterForm');
            $bradFilterForm.find('input[name="n"]').val($selectedValue);

            $bradFilterForm.find('input[name="p"]').val(1);
            performFiltering();

            return false;
        });
    }

    /**
     * Listen when user clicks on selected filter removal link
     */
    function listenSelectedFilterRemove()
    {
        var $centerColumn = $('#center_column');

        $centerColumn.find('.brad-selected-filter').on('click', function($e) {
            $e.preventDefault();

            var $filter = $(this).data('filter');
            var $filterValue = $(this).data('value');

            var $filterBlockInput = $('input[name="' + $filter + '"][value="' + $filterValue + '"]');
            $filterBlockInput.attr('checked', false);

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
        listenSelectedFilterRemove();
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

