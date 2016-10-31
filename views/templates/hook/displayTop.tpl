<div id="bradSearchBlockTop" class="col-sm-4 clearfix">
    <form id="bradSearchBox" method="GET" action="{$brad_search_url|escape:'htmlall':'UTF-8'}">
        <input class="search_query form-control ac_input"
               type="text"
               id="bradSearchQuery"
               name="search_query"
               placeholder="{l s='Search' mod='brad'}"
               value="{$search_query|escape:'htmlall':'UTF-8'}"
               autocomplete="off"
        >
        {if not $is_friendly_url_enabled}
            <input type="hidden" name="fc" value="module">
            <input type="hidden" name="module" value="brad">
            <input type="hidden" name="controller" value="search">
        {/if}
        <button type="submit" class="btn btn-default button-search">
            <span>{l s='Search' mod='brad'}</span>
        </button>
        <div id="bradInstantSearchResults"></div>
    </form>
</div>
