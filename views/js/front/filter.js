$(document).ready(function() {

    var $sendingRequest = false;

    handleInputArea();
    handleSlider();

    $('.brad-checkbox-filter-input').on('change', performFiltering);

    /**
     * Perform filtering
     */
    function performFiltering()
    {
        var $selectedFilters = getSelectedFilters();
        $selectedFilters['id_category'] = $globalIdCategory;

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

        var $queryString = $response.query_string;

        if ($globalBaseUrl.indexOf('?') > -1) {
            window.history.pushState([], '', $globalBaseUrl + '&' + $queryString);
        } else {
            window.history.pushState([], '', $globalBaseUrl + '?' + $queryString);
        }

        if (typeof $response.filters_template != 'undefined') {
            $("#bradFilterContainer").replaceWith($response.filters_template);
            $('.brad-checkbox-filter-input').on('change', performFiltering);
            handleSlider();
            handleInputArea();
            updateUniform();
        }

        var $centerColumn = $('#center_column');
        var $centerColumnClasses = $centerColumn.attr('class');
        $('#bradResults').remove();

        if (typeof $response.products_list != 'undefined' && $response.products_list) {
            console.log($response.products_list);
            $centerColumn.hide();
            $centerColumn.after('<div id="bradResults" class="' + $centerColumnClasses + '">' + $response.products_list + '</div>');
        } else {
            $centerColumn.show();
        }
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

        return $selectedFilters;
    }

    function debug(msg) {
        console.log(msg);
    }
});

