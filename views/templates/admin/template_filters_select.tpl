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
