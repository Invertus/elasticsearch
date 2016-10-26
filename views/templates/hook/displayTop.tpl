<div id="bradSearchBlockTop" class="col-sm-4 clearfix">
    <form id="bradSearchBox" method="GET" action="{$bradSearchUrl|escape:'htmlall':'UTF-8'}">
        <input class="search_query form-control ac_input"
               type="text"
               id="bradSearchQuery"
               name="brad_search_query"
               placeholder="{l s='Search' mod='brad'}"
               value=""
               autocomplete="off"
        >
        <button type="submit" name="submit_search" class="btn btn-default button-search">
            <span>{l s='Search' mod='brad'}</span>
        </button>
        <div id="bradInstantSearchResults"></div>
    </form>
</div>
