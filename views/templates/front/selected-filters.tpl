<div id="bradSelectedFilters">

    <strong>{l s='Selected filters' mod='brad'}:</strong> <br>

    <ul >
        {foreach $formatted_selected_filters as $key => $filter}
        <li>
            <ul class="list-inline">
                <li>
                    <strong>{$filter.name|escape:'htmlall':'UTF-8'}:</strong>
                </li>
                {foreach $filter.values as $value}
                    <li>
                        <a href="#" data-filter="{$value.filter|escape:'htmlall':'UTF-8'}" data-value="{$value.filter_value|escape:'htmlall':'UTF-8'}" class="brad-selected-filter">
                            <i class="icon-times" aria-hidden="true"></i>
                            {$value.display_value|escape:'htmlall':'UTF-8'}
                        </a>
                    </li>
                {/foreach}
            </ul>
        </li>
        {/foreach}
    </ul>
</div>
