$(document).ready(function() {

    handleInputArea();
    handleSlider();

    $('.brad-checkbox-filter-input').on('change', performFiltering);

    /**
     * Perform filtering
     */
    function performFiltering() {
        debug('Filtering started');

        var $selectedFilters = getSelectedFilters();
        debug('Finished searching filters');
        debug($selectedFilters);


    }

    /**
     * get all selected filters values
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

            var $defaultMinValue = $($element).data('min-value');
            var $defaultMaxValue = $($element).data('max-value');
            var $inputName = $($element).data('input-name');

            $($element).slider({
                range: true,
                min: $defaultMinValue,
                max: $defaultMaxValue,
                values: [$defaultMinValue, $defaultMaxValue],
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

            $('input[name="' + $inputName + '"]').val(
                $($element).slider("values", 0) + ":" + $($element).slider("values", 1)
            );
        });
    }

    function debug(msg) {
        console.log(msg);
    }
});
