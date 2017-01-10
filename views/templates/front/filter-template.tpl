{*
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
*}

<div class="block" id="bradFilterContainer">
    <p class="title_block">
        {l s='Filter' mod='brad'}
    </p>

    <div id="bradFilterBlock" class="block_content">
        <form action="#" id="bradFilterForm">
            <div class="filter_block">
                {foreach $filters as $filter}

                    {assign var='is_checkbox_input' value=BradFilter::FILTER_STYLE_CHECKBOX == $filter.filterStyle || BradFilter::FILTER_STYLE_LIST_OF_VALUES == $filter.filterStyle}

                    {* If aggregations & hide 0 filters is on and filter style is checkbox then hide filters with 0 products *}
                    {if $aggregations_on && $hide_zero_filters && $is_checkbox_input}
                        {if !isset($aggregations[$filter.inputName].total_count) || $aggregations[$filter.inputName].total_count == 0}
                            {continue}
                        {/if}
                    {/if}
                    <div>
                        <div class="filter_block_heading">
                            <strong>{$filter.name|escape:'htmlall':'UTF-8'}</strong>
                        </div>

                        <div {if isset($filter.customHeight) && $filter.customHeight}style="max-height: {$filter.customHeight|intval}px; overflow-y: scroll;" {/if}>
                            {assign 'criteria_name_key' $filter.criteriaNameKey}
                            {assign 'criteria_value_key' $filter.criteriaValueKey}

                            {* ===================================== *}
                            {* Display checkboxes *}
                            {* ===================================== *}
                            {if $is_checkbox_input }
                                {foreach $filter.criterias as $criteria}
                                    <div class="checkbox">
                                        <label>
                                            {if isset($criteria.color) && !empty($criteria.color)}
                                                <span class="brad-cirteria-color" style="background-color: {$criteria.color|escape:'htmlall':'UTF-8'};"></span>
                                            {/if}
                                            <input name="{$filter.inputName|escape:'htmlall':'UTF-8'}"
                                                   value="{$criteria[$criteria_value_key]|escape:'htmlall':'UTF-8'}"
                                                   type="checkbox"
                                                   class="brad-checkbox-filter-input"
                                                   {if isset($criteria.checked)}checked="checked"{/if}
                                            >
                                            {$criteria[$criteria_name_key]|escape:'htmlall':'UTF-8'}
                                            {if isset($aggregations[$filter.inputName])}
                                                {if isset($aggregations[$filter.inputName][$criteria[$criteria_value_key]])}
                                                    ({$aggregations[$filter.inputName][$criteria[$criteria_value_key]]})
                                                {else}
                                                    (0)
                                                {/if}
                                            {/if}
                                        </label>
                                    </div>
                                {/foreach}

                            {* ===================================== *}
                            {* Display input fields *}
                            {* ===================================== *}
                            {elseif BradFilter::FILTER_STYLE_INPUT == $filter.filterStyle}

                                {assign var='default_prices' value=':'|explode:$filter.criterias.0.$criteria_value_key}
                                {assign var='default_min_value' value=$default_prices.0}
                                {assign var='default_max_value' value=$default_prices.1}

                                <div class="row brad-input-area"
                                     data-input-name="{$filter.inputName|escape:'htmlall':'UTF-8'}">
                                    <div class="col-md-4">
                                        <label for="">{l s='From:' mod='brad'}</label>
                                        <input type="text"
                                               class="form-control brad-min-range"
                                               value="{if isset($filter.criterias.0.selected_min_value)}{$filter.criterias.0.selected_min_value|escape:'htmlall':'UTF-8'}{else}{$default_min_value|escape:'htmlall':'UTF-8'}{/if}"
                                               data-default-min-value="{$default_min_value|escape:'htmlall':'UTF-8'}"
                                        >
                                    </div>
                                    <div class="col-md-4" >
                                        <label for="">{l s='To:' mod='brad'}</label>
                                        <input type="text"
                                               class="form-control brad-max-range"
                                               value="{if isset($filter.criterias.0.selected_max_value)}{$filter.criterias.0.selected_max_value|escape:'htmlall':'UTF-8'}{else}{$default_max_value|escape:'htmlall':'UTF-8'}{/if}"
                                               data-default-max-value="{$default_max_value|escape:'htmlall':'UTF-8'}"
                                        >
                                    </div>
                                    {assign var='is_selected_value' value=(isset($filter.criterias.0.selected_min_value) || isset($filter.criterias.0.selected_max_value))}
                                    <input class="brad-input-filter-input" type="hidden" name="{$filter.inputName|escape:'htmlall':'UTF-8'}" {if $is_selected_value}checked="checked" value="{$filter.criterias.0.selected_min_value|escape:'htmlall':'UTF-8'}:{$filter.criterias.0.selected_max_value|escape:'htmlall':'UTF-8'}" {/if}>
                                </div>

                            {* ===================================== *}
                            {* Display slider *}
                            {* ===================================== *}
                            {elseif BradFilter::FILTER_STYLE_SLIDER == $filter.filterStyle}

                                {assign var='default_prices' value=':'|explode:$filter.criterias.0.$criteria_value_key}
                                {assign var='default_min_value' value=$default_prices.0}
                                {assign var='default_max_value' value=$default_prices.1}

                                <div>
                                    <label>{l s='Range:' mod="brad"} <span class="brad-selected-range"></span></label>
                                    <input class="brad-slider-input brad-slider-filter-input"
                                           title="{l s='range' mod='brad'}"
                                           type="text"
                                           readonly
                                           name="{$filter.inputName|escape:'htmlall':'UTF-8'}"
                                    >
                                    <div data-min-value="{$default_min_value|escape:'htmlall':'UTF-8'}"
                                         data-max-value="{$default_max_value|escape:'htmlall':'UTF-8'}"
                                         data-input-name="{$filter.inputName|escape:'htmlall':'UTF-8'}"
                                         {if isset($filter.criterias.0.selected_min_value)}data-selected-min-value="{$filter.criterias.0.selected_min_value|escape:'htmlall':'UTF-8'}"{/if}
                                         {if isset($filter.criterias.0.selected_max_value)}data-selected-max-value="{$filter.criterias.0.selected_max_value|escape:'htmlall':'UTF-8'}"{/if}
                                         class="brad-slider"
                                    >
                                    </div>
                                </div>

                            {/if}
                        </div>
                        <hr>
                    </div>
                {/foreach}
            </div>

            <input type="hidden" name="orderby" value="{$orderby|escape:'htmlall':'UTF-8'}">
            <input type="hidden" name="orderway" value="{$orderway|escape:'htmlall':'UTF-8'}">
            <input type="hidden" name="p" value="{$p|escape:'htmlall':'UTF-8'}">
            <input type="hidden" name="n" value="{$n|escape:'htmlall':'UTF-8'}">
        </form>
    </div>
</div>
