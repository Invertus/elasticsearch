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

<div id="brad_custom_ranges" class="col-lg-4">
    <div class="panel">
        <div class="panel-heading">
            <div class="row">
                <div class="col-lg-6 text-center">{l s='Min value' mod='brad'}</div>
                <div class="col-lg-6 text-center">{l s='Max value' mod='brad'}</div>
            </div>
        </div>

        <ul class="brad-custom-ranges-list list-group">
            {if isset($custom_ranges) && !empty($custom_ranges)}
                {foreach from=$custom_ranges item=value}
                    <li class="row list-group-item">
                        <div class="col-lg-6 brad-custom-range-min text-center">
                            <input type="text"
                                   name="brad_min_range_{$value.id|intval}"
                                   data-id="{$value.id|intval}"
                                   value="{$value.min_value|escape:'htmlall':'UTF-8'}"
                            >
                        </div>
                        <div class="col-lg-6 brad-custom-range-max text-center">
                            <input type="text"
                                   name="brad_max_range_{$value.id|intval}"
                                   data-id="{$value.id|intval}"
                                   value="{$value.max_value|escape:'htmlall':'UTF-8'}"
                            >
                        </div>
                    </li>
                {/foreach}
            {else}
                <li class="row list-group-item">
                    <div class="col-lg-6 brad-custom-range-min text-center">
                        <input type="text" name="brad_min_range_1" data-id="1"/>
                    </div>
                    <div class="col-lg-6 brad-custom-range-max text-center">
                        <input type="text" name="brad_max_range_1" data-id="1"/>
                    </div>
                </li>
            {/if}
        </ul>

        <div class="row">
            <div class="col-lg-12 text-center">
                <a href="javascript:void(0);" class="btn btn-default brad-add-range-row" style="margin-top: 10px;">
                    <i class="icon-plus"></i>
                    {l s='Add row' mod='brad'}
                </a>
            </div>
        </div>
    </div>
</div>