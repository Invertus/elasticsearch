<div class="block">
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
                                        {if isset($criteria.color)}
                                            <span style="
                                                width: 15px;
                                                height: 15px;
                                                background-color: {$criteria.color|escape:'htmlall':'UTF-8'};
                                                display: inline-block;
                                            ">
                                            </span>
                                        {/if}
                                        <input value="{$criteria[$criteria_value_key]|escape:'htmlall':'UTF-8'}" type="checkbox">
                                        {$criteria[$criteria_name_key]|escape:'htmlall':'UTF-8'}
                                    </label>
                                </div>
                            {/foreach}
                        {elseif BradFilter::FILTER_STYLE_INPUT == $filter.filter_style}


                        {elseif BradFilter::FILTER_STYLE_SLIDER == $filter.filter_style}

                        {/if}
                    </div>
                    <hr>
                </div>
                {/foreach}
            </div>
        </form>
    </div>
</div>
