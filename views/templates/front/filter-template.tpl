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
                    <div {if $filter.custom_height}style="max-height:{$filter.custom_height|escape:'htmlall':'UTF-8'}px;overflow-y: scroll;"{/if}>
                        {if BradFilter::FILTER_STYLE_CHECKBOX == $filter.filter_style ||
                            BradFilter::FILTER_STYLE_LIST_OF_VALUES == $filter.filter_style }
                            {assign 'criteria_name_key' $filter.criteria_name}
                            {assign 'criteria_value_key' $filter.criteria_value}

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
                                        <input name="{$filter.input_name|escape:'htmlall':'UTF-8'}"
                                               value="{$criteria[$criteria_value_key]|escape:'htmlall':'UTF-8'}"
                                               type="checkbox"
                                               class="brad-checkbox-filter-input"
                                               {if isset($criteria.checked)}checked="checked"{/if}
                                        >
                                        {$criteria[$criteria_name_key]|escape:'htmlall':'UTF-8'}
                                    </label>
                                </div>
                            {/foreach}
                        {elseif BradFilter::FILTER_STYLE_INPUT == $filter.filter_style}
                            <div class="row brad-input-area"
                                 data-input-name="{$filter.input_name|escape:'htmlall':'UTF-8'}">
                                <div class="col-md-4">
                                    <label for="">{l s='From:' mod='brad'}</label>
                                    <input type="text"
                                           class="form-control brad-min-range"
                                           value="{if isset($filter.criterias.selected_min_value)}{$filter.criterias.selected_min_value|escape:'htmlall':'UTF-8'}{else}{$filter.criterias.min_value|escape:'htmlall':'UTF-8'}{/if}"
                                           data-default-min-value="{$filter.criterias.min_value|escape:'htmlall':'UTF-8'}"
                                    >
                                </div>
                                <div class="col-md-4" >
                                    <label for="">{l s='To:' mod='brad'}</label>
                                    <input type="text"
                                           class="form-control brad-max-range"
                                           value="{if isset($filter.criterias.selected_max_value)}{$filter.criterias.selected_max_value|escape:'htmlall':'UTF-8'}{else}{$filter.criterias.max_value|escape:'htmlall':'UTF-8'}{/if}"
                                           data-default-max-value="{$filter.criterias.max_value|escape:'htmlall':'UTF-8'}"
                                    >
                                </div>
                                {assign var='is_selected_value' value=(isset($filter.criterias.selected_min_value) || isset($filter.criterias.selected_max_value))}
                                <input class="brad-input-filter-input" type="hidden" name="{$filter.input_name|escape:'htmlall':'UTF-8'}" {if $is_selected_value}checked="checked" value="{$filter.criterias.selected_min_value|escape:'htmlall':'UTF-8'}:{$filter.criterias.selected_max_value|escape:'htmlall':'UTF-8'}" {/if}>
                            </div>
                        {elseif BradFilter::FILTER_STYLE_SLIDER == $filter.filter_style}
                            <div>
                                <label>{l s='Range:' mod="brad"} <span class="brad-selected-range"></span></label>
                                <input class="brad-slider-input brad-slider-filter-input"
                                       type="text"
                                       readonly
                                       style="border:0; color:#777; font-weight:bold;"
                                       name="{$filter.input_name|escape:'htmlall':'UTF-8'}"
                                >
                                <div data-min-value="{$filter.criterias.min_value|escape:'htmlall':'UTF-8'}"
                                     data-max-value="{$filter.criterias.max_value|escape:'htmlall':'UTF-8'}"
                                     data-input-name="{$filter.input_name|escape:'htmlall':'UTF-8'}"
                                     {if isset($filter.criterias.selected_min_value)}data-selected-min-value="{$filter.criterias.selected_min_value|escape:'htmlall':'UTF-8'}"{/if}
                                     {if isset($filter.criterias.selected_max_value)}data-selected-max-value="{$filter.criterias.selected_max_value|escape:'htmlall':'UTF-8'}"{/if}
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
        </form>
    </div>
</div>
