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
