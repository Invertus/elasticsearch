<div id="bradSearchBlockTop" class="col-sm-4 clearfix">
    <form id="bradSearchBox" method="GET" action="{$bradSearchUrl|escape:'htmlall':'UTF-8'}">
        <input class="search_query form-control ac_input"
               type="text"
               id="bradSearchQuery"
               name="query"
               placeholder="{l s='Search' mod='brad'}"
               value=""
               autocomplete="off"
        >
        <button type="submit" class="btn btn-default button-search">
            <span>{l s='Search' mod='brad'}</span>
        </button>
        <div id="bradInstantSearchResults"></div>
    </form>
</div>
