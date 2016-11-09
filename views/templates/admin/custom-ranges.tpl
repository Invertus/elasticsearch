<div id="brad_custom_ranges" class="col-lg-4">
    <div class="panel">
        <div class="panel-heading">
            <div class="row">
                <div class="col-lg-6 text-center">{l s='Min value' mod='brad'}</div>
                <div class="col-lg-6 text-center">{l s='Max value' mod='brad'}</div>
            </div>
        </div>

        <ul class="brad-custom-ranges-list list-group">
            {if isset($values)}
                {foreach from=$values item=value}
                    <li class="row list-group-item">
                        <div class="col-lg-6 brad-custom-range-min text-center">
                            <input type="text"
                                   name="brad_min_range_{$value.id_elasticsearch_criterion_value|intval}"
                                   data-id="{$value.id_elasticsearch_criterion_value|intval}"
                                   value="{$value.value_min|escape:'htmlall':'UTF-8'}"
                            >
                        </div>
                        <div class="col-lg-6 brad-custom-range-max text-center">
                            <input type="text"
                                   name="brad_max_range_{$value.id_elasticsearch_criterion_value|intval}"
                                   data-id="{$value.id_elasticsearch_criterion_value|intval}"
                                   value="{$value.value_max|escape:'htmlall':'UTF-8'}"
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