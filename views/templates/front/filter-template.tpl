<div class="block" id="bradFilterContainer">
    <p class="title_block">
        {l s='Filter' mod='brad'}
    </p>

    <div id="bradFilterBlock" class="block_content">
        <form action="#" id="bradFilterForm">
            <div class="filter_block">
                {foreach $filters as $filter}
                <div>
                    <div class="filter_block_heading">
                        <strong>{$filter.name|escape:'htmlall':'UTF-8'}</strong>
                    </div>
                    <div>
                        {assign 'criteria_name_key' $filter.criteriaNameKey}
                        {assign 'criteria_value_key' $filter.criteriaValueKey}

                        {if BradFilter::FILTER_STYLE_CHECKBOX == $filter.filterStyle ||
                            BradFilter::FILTER_STYLE_LIST_OF_VALUES == $filter.filterStyle }

                            {foreach $filter.criterias as $criteria}
                                <div class="checkbox">
                                    <label>
                                        {if isset($criteria.color) && !empty($criteria.color)}
                                            <span style="
                                                width: 15px;
                                                height: 15px;
                                                background-color: {$criteria.color|escape:'htmlall':'UTF-8'};
                                                display: inline-block;
                                            "></span>
                                        {/if}
                                        <input name="{$filter.inputName|escape:'htmlall':'UTF-8'}"
                                               value="{$criteria[$criteria_value_key]|escape:'htmlall':'UTF-8'}"
                                               type="checkbox"
                                               class="brad-checkbox-filter-input"
                                               {if isset($criteria.checked)}checked="checked"{/if}
                                        >
                                        {$criteria[$criteria_name_key]|escape:'htmlall':'UTF-8'}
                                    </label>
                                </div>
                            {/foreach}
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
                        {elseif BradFilter::FILTER_STYLE_SLIDER == $filter.filterStyle}

                            {assign var='default_prices' value=':'|explode:$filter.criterias.0.$criteria_value_key}
                            {assign var='default_min_value' value=$default_prices.0}
                            {assign var='default_max_value' value=$default_prices.1}

                            <div>
                                <label>{l s='Range:' mod="brad"} <span class="brad-selected-range"></span></label>
                                <input class="brad-slider-input brad-slider-filter-input"
                                       type="text"
                                       readonly
                                       style="border:0; color:#777; font-weight:bold;"
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
