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

    var $searchFeatureAndAttributeInput = $('#id_key_search');
    // it's either id_attribute_group or id_feature depending on filter type
    var $idKeyHiddenInput = $('#id_key');

    var $filterTypeInput = $('#filter_type');
    var $filterStyle = $('#filter_style');

    $filterTypeInput.on('change', adjustFormLayout);
    $filterStyle.on('change', handleAvailableFilterStyles);

    adjustFormLayout();

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
     * Adjust filter form layout
     */
    function adjustFormLayout()
    {
        var $customRangesInput = $('#brad_custom_ranges');
        var $criteriaSuffix = $('#criteria_suffix');
        var $criteriaOrderBy = $('#criteria_order_by');
        var $criteriaOrderWay = $('#criteria_order_way');

        switch (parseInt($filterTypeInput.val())) {
            case FILTER_TYPE_WEIGHT:
            case FILTER_TYPE_PRICE:
                $searchFeatureAndAttributeInput.closest('.form-group').hide();
                if (parseInt($filterStyle.val()) == FILTER_STYLE_LIST_OF_VALUES) {
                    $customRangesInput.closest('.form-group').show();
                    $criteriaSuffix.closest('.form-group').show();
                    $criteriaOrderBy.closest('.form-group').hide();
                    $criteriaOrderWay.closest('.form-group').hide();
                } else if (
                    parseInt($filterStyle.val()) == FILTER_STYLE_SLIDER ||
                    parseInt($filterStyle.val()) == FILTER_STYLE_INPUT
                ) {
                    $criteriaOrderBy.closest('.form-group').hide();
                    $criteriaOrderWay.closest('.form-group').hide();
                    $customRangesInput.closest('.form-group').hide();
                } else {
                    $customRangesInput.closest('.form-group').hide();
                    $criteriaSuffix.closest('.form-group').hide();
                    $criteriaOrderBy.closest('.form-group').show();
                    $criteriaOrderWay.closest('.form-group').show();
                }
                break;
            case FILTER_TYPE_ATTRIBUTE_GROUP:
            case FILTER_TYPE_FEATURE:
                $searchFeatureAndAttributeInput.closest('.form-group').show();
                if (parseInt($filterStyle.val()) == FILTER_STYLE_LIST_OF_VALUES) {
                    $customRangesInput.closest('.form-group').show();
                    $criteriaSuffix.closest('.form-group').show();
                    $criteriaOrderBy.closest('.form-group').hide();
                    $criteriaOrderWay.closest('.form-group').hide();
                } else if (
                    parseInt($filterStyle.val()) == FILTER_STYLE_SLIDER ||
                    parseInt($filterStyle.val()) == FILTER_STYLE_INPUT
                ) {
                    $criteriaOrderBy.closest('.form-group').hide();
                    $criteriaOrderWay.closest('.form-group').hide();
                    $customRangesInput.closest('.form-group').hide();
                } else {
                    $customRangesInput.closest('.form-group').hide();
                    $criteriaSuffix.closest('.form-group').hide();
                    $criteriaOrderBy.closest('.form-group').show();
                    $criteriaOrderWay.closest('.form-group').show();
                }
                break;
            case FILTER_TYPE_MANUFACTURER:
            case FILTER_TYPE_QUANTITY:
            case FILTER_TYPE_CATEGORY:
                $searchFeatureAndAttributeInput.closest('.form-group').hide();
                $criteriaOrderBy.closest('.form-group').show();
                $criteriaOrderWay.closest('.form-group').show();
                $customRangesInput.closest('.form-group').hide();
                $criteriaSuffix.closest('.form-group').hide();
                break;
        }
    }

    function handleAvailableFilterStyles()
    {
        $.ajax($globalBradFilterControllerUrl, {

        });
    }
});
