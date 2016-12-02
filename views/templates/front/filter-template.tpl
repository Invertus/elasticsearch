<div class="block">
    <p class="title_block">
        {l s='Filter' mod='brad'}
    </p>

    <div id="bradFilterBlock" class="block_content">
        <form action="#" id="bradFilterForm">
            <div class="filter_block">
                {foreach $filters as $filter}
                <div {if $filter.custom_height}style="min-height:{$filter.custom_height|escape:'htmlall':'UTF-8'}px"{/if}>
                    <div class="filter_block_heading">
                        <strong>{$filter.name|escape:'htmlall':'UTF-8'}</strong>
                    </div>
                    {if BradFilter::FILTER_STYLE_CHECKBOX == $filter.filter_style}

                    {elseif BradFilter::FILTER_STYLE_LIST_OF_VALUES == $filter.filter_style}


                    {elseif BradFilter::FILTER_STYLE_INPUT == $filter.filter_style}


                    {elseif BradFilter::FILTER_STYLE_SLIDER == $filter.filter_style}

                    {/if}
                </div>
                {/foreach}
            </div>
        </form>
    </div>
</div>
