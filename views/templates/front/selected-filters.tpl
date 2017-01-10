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
