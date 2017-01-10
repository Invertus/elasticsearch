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

<div class="form-group">

    <div class="col-lg-9">

        <section class="filter_panel">
            <section class="filter_list">
                <ul class="list-unstyled sortable">
                    {foreach $selected_filters as $filter}
                        <li class="filter_list_item" draggable="true">
                            <div class="col-lg-2">
                                <label class="switch-light prestashop-switch fixed-width-lg">
                                    <input name="template_filter:{$filter.id_brad_filter|escape:'htmlall':'UTF-8'}-{$filter.filter_type|escape:'htmlall':'UTF-8'}-{$filter.id_key|escape:'htmlall':'UTF-8'}"
                                           id="template_filter:{$filter.id_brad_filter|escape:'htmlall':'UTF-8'}-{$filter.filter_type|escape:'htmlall':'UTF-8'}-{$filter.id_key|escape:'htmlall':'UTF-8'}"
                                           type="checkbox"
                                           checked="checked"
                                    />
                                    <span>
                                        <span>{l s='Yes' mod='brad'}</span>
                                        <span>{l s='No' mod='brad'}</span>
                                    </span>
                                    <a class="slide-button btn"></a>
                                </label>
                            </div>
                            <div class="col-lg-4">
                                <span class="module_name">{$filter.name|escape:'htmlall':'UTF-8'}</span>
                            </div>
                        </li>
                    {/foreach}

                    {foreach $available_filters as $filter}
                        <li class="filter_list_item" draggable="true">
                            <div class="col-lg-2">
                                <label class="switch-light prestashop-switch fixed-width-lg">
                                    <input name="template_filter:{$filter.id_brad_filter|escape:'htmlall':'UTF-8'}-{$filter.filter_type|escape:'htmlall':'UTF-8'}-{$filter.id_key|escape:'htmlall':'UTF-8'}"
                                           id="template_filter:{$filter.id_brad_filter|escape:'htmlall':'UTF-8'}-{$filter.filter_type|escape:'htmlall':'UTF-8'}-{$filter.id_key|escape:'htmlall':'UTF-8'}"
                                           type="checkbox"
                                    />
                                    <span>
                                        <span>{l s='Yes' mod='brad'}</span>
                                        <span>{l s='No' mod='brad'}</span>
                                    </span>
                                    <a class="slide-button btn"></a>
                                </label>
                            </div>
                            <div class="col-lg-4">
                                <span class="module_name">{$filter.name|escape:'htmlall':'UTF-8'}</span>
                            </div>
                        </li>
                    {/foreach}
                </ul>
            </section>
        </section>

    </div>
</div>
