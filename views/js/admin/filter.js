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

$(document).ready(function () {

    var FILTER_TYPE_PRICE = 1;
    var FILTER_TYPE_WEIGHT = 2;
    var FILTER_TYPE_FEATURE = 3;
    var FILTER_TYPE_ATTRIBUTE_GROUP = 4;
    var FILTER_TYPE_MANUFACTURER = 5;
    var FILTER_TYPE_QUANTITY = 6;
    var FILTER_TYPE_CATEGORY = 7;

    var FILTER_STYLE_CHECKBOX = 1;
    var FILTER_STYLE_LIST_OF_VALUES = 2;
    var FILTER_STYLE_INPUT = 3;
    var FILTER_STYLE_SLIDER = 4;

    // it's either id_attribute_group or id_feature depending on filter type
    var $idKeyHiddenInput = $('#id_key');
    var $searchFeatureAndAttributeInput = $('#id_key_search');
    var $filterTypeInput = $('#filter_type');
    var $filterStyleInput = $('#filter_style');

    var $filterStyleCheckboxOption = $filterStyleInput.find('option[value="' + FILTER_STYLE_CHECKBOX + '"]');
    var $filterStyleListOfValuesOption = $filterStyleInput.find('option[value="' + FILTER_STYLE_LIST_OF_VALUES + '"]');
    var $filterStyleInputOption = $filterStyleInput.find('option[value="' + FILTER_STYLE_INPUT + '"]');
    var $filterStyleSliderOption = $filterStyleInput.find('option[value="' + FILTER_STYLE_SLIDER + '"]');

    var $customRangesFormGroup = $('#brad_custom_ranges').closest('.form-group');
    var $criteriaSuffixFormGroup = $('#criteria_suffix').closest('.form-group');
    var $criteriaOrderByFormGroup = $('#criteria_order_by').closest('.form-group');
    var $criteriaOrderWayFormGroup = $('#criteria_order_way').closest('.form-group');
    var $featureAndAttributeFormGroup = $searchFeatureAndAttributeInput.closest('.form-group');

    var $selectedFilterTypeValue = $filterTypeInput.find('option:selected').val();
    toggleFilterStyles($selectedFilterTypeValue);

    var $selectedFilterStyleValue = $filterStyleInput.find('option:selected').val();
    toggleFilterForm($selectedFilterStyleValue);

    $filterTypeInput.on('change', function () {
        var $selectedFilterTypeValue = $(this).val();
        toggleFilterStyles($selectedFilterTypeValue);
    });

    $filterStyleInput.on('change', function () {
        var $selectedFilterStyleValue = $(this).val();
        toggleFilterForm($selectedFilterStyleValue);
    });

    $searchFeatureAndAttributeInput
        .autocomplete($globalBradFilterControllerUrl, {
            minChars: 3,
            max: 10,
            width: 300,
            selectFirst: false,
            scroll: false,
            dataType: 'json',
            extraParams: {
                'filter_type': function() { return $(document).find('#filter_type').val(); }
            },
            formatItem: function($data, $i, $max, $value) {
                return $value;
            },
            parse: function ($response) {
                var $result = [];

                if (typeof $response.response == 'undefined') {
                    return $result;
                }

                for (var i = 0; i < $response.response.length; i++) {
                    $result[i] = {
                        data: $response.response[i],
                        value: $response.response[i].name
                    };
                }

                return $result;
            }
        })
        .result(function ($event, $data) {

            $searchFeatureAndAttributeInput.val($data.name);
            $idKeyHiddenInput.val($data.id);

        });

    /**
     * Toggle filter styles select input
     *
     * @param $selectedFilterType
     */
    function toggleFilterStyles($selectedFilterType)
    {
        $selectedFilterType = parseInt($selectedFilterType);

        switch ($selectedFilterType) {
            case FILTER_TYPE_PRICE:
            case FILTER_TYPE_WEIGHT:
            case FILTER_TYPE_ATTRIBUTE_GROUP:
            case FILTER_TYPE_FEATURE:
                $filterStyleCheckboxOption.show();
                $filterStyleListOfValuesOption.show();
                $filterStyleInputOption.show();
                $filterStyleSliderOption.show();
                break;
            case FILTER_TYPE_MANUFACTURER:
            case FILTER_TYPE_QUANTITY:
            case FILTER_TYPE_CATEGORY:
                $filterStyleCheckboxOption.show().attr('selected','selected');
                $filterStyleListOfValuesOption.hide();
                $filterStyleInputOption.hide();
                $filterStyleSliderOption.hide();
                toggleFilterForm(FILTER_STYLE_CHECKBOX);
                break;
        }

        if (-1 != $.inArray($selectedFilterType, [FILTER_TYPE_FEATURE, FILTER_TYPE_ATTRIBUTE_GROUP])) {
            $featureAndAttributeFormGroup.show();
        } else {
            $featureAndAttributeFormGroup.hide();
        }
    }

    /**
     * Toggle filter form layout based on selected filter style
     *
     * @param $selectedFilterStyle
     */
    function toggleFilterForm($selectedFilterStyle)
    {
        $selectedFilterStyle = parseInt($selectedFilterStyle);

        switch ($selectedFilterStyle) {
            case FILTER_STYLE_CHECKBOX:
                $criteriaOrderByFormGroup.show();
                $criteriaOrderWayFormGroup.show();
                $customRangesFormGroup.hide();
                $criteriaSuffixFormGroup.hide();
                break;
            case FILTER_STYLE_LIST_OF_VALUES:
                $criteriaOrderByFormGroup.show();
                $criteriaOrderWayFormGroup.show();
                $customRangesFormGroup.show();
                $criteriaSuffixFormGroup.show();
                break;
            case FILTER_STYLE_INPUT:
            case FILTER_STYLE_SLIDER:
                $criteriaOrderByFormGroup.hide();
                $criteriaOrderWayFormGroup.hide();
                $customRangesFormGroup.hide();
                $criteriaSuffixFormGroup.hide();
                break;
        }
    }
});
