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

{if $instant_search_results && !empty($instant_search_results)}
    <ul class="brad-autocomplete-block">

        {foreach $instant_search_results as $product}
            <li class="brad-autocomplete-item" role="presentation">
                <a class="brad-autocomplete-item-border" href="{$product.link|escape:'htmlall':'UTF-8'}" tabindex="-1">

                    <img src="{$link->getImageLink($product.link_rewrite, $product.id_image, $image_type)|escape:'htmlall':'UTF-8'}"
                         class="brad-autocomplete-item-image"
                    >

                    <div class="brad-autocomplete-info-block">
                        <span class="brad-autocomplete-item-name">
                            {$product.name|escape:'htmlall':'UTF-8'}
                        </span>
                        <br>
                        <span class="brad-autocomplete-item-category">
                            {$product.category_name|escape:'htmlall':'UTF-8'}
                        </span>
                    </div>

                </a>
            </li>
        {/foreach}

        <li class="brad-autocomplete-item" role="presentation">
            <a class="brad-autocomplete-item-border" href="{$more_search_results_url|escape:'htmlall':'UTF-8'}">
                {l s='See more results' mod='brad'}
            </a>
        </li>
    </ul>
{else}
    <ul class="brad-autocomplete-block">
        <li class="brad-autocomplete-item" role="presentation">
            <a class="brad-autocomplete-item-border" href="#">
                <span class="brad-autocomplete-item-name">{l s='No results were found' mod='brad'}</span>
            </a>
        </li>
    </ul>
{/if}
