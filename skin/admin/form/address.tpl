<div class="row">
    <form id="edit_address" action="{$smarty.server.SCRIPT_NAME}?controller={$smarty.get.controller}&amp;tabs=address&amp;action={if !$edit}add{else}edit{/if}" method="post" class="validate_form{if !$edit} add_form collapse in{else} edit_form{/if} col-ph-12 col-sm-8 col-md-6">
        <div id="drop-zone"{if !isset($slide.img_slide) || empty($slide.img_slide)} class="no-img"{/if}>
            <div id="drop-buttons" class="form-group">
                <label id="clickHere" class="btn btn-default">
                    ou cliquez ici.. <span class="fa fa-upload"></span>
                    <input type="hidden" name="MAX_FILE_SIZE" value="4048576" />
                    <input type="file" id="img" name="img" />
                    <input type="hidden" id="id_product" name="id" value="{$slide.id_slide}">
                </label>
            </div>
            <div class="preview-img">
                {if isset($slide.img_slide) && !empty($slide.img_slide)}
                    <img id="preview" src="/upload/slideshow/{$slide.id_slide}/{$slide.img_slide}" alt="Slide" class="preview img-responsive" />
                {else}
                    <img id="preview" src="#" alt="Déposez votre images ici..." class="no-img img-responsive" />
                {/if}
            </div>
        </div>
        {include file="language/brick/dropdown-lang.tpl"}
        <div class="row">
            <div class="col-ph-12">
                <div class="tab-content">
                    {foreach $langs as $id => $iso}
                        <div role="tabpanel" class="tab-pane{if $iso@first} active{/if}" id="lang-{$id}">
                            <fieldset>
                                <legend>Adresse</legend>
                                <div class="row">
                                    <div class="col-xs-12 col-sm-8 col-md-9 col-lg-10">
                                        <div class="form-group">
                                            <label for="company_address_{$id}">{#company_address#|ucfirst} :</label>
                                            <input type="text" class="form-control" id="company_address_{$id}" name="address[content][{$id}][company_address]" value="{$address.content[{$id}].company_address}" size="50" />
                                        </div>
                                        <div class="form-group">
                                            <label for="address_address_{$id}">{#address_address#|ucfirst} :</label>
                                            <input type="text" class="form-control address" id="address_address_{$id}" name="address[content][{$id}][address_address]" value="{$address.content[{$id}].address_address}" size="50" />
                                        </div>
                                        <div class="row">
                                            <div class="col-xs-12 col-sm-6">
                                                <div class="form-group">
                                                    <label for="postcode_address_{$id}">{#postcode_address#|ucfirst} :</label>
                                                    <input type="text" class="form-control postcode" id="postcode_address_{$id}" name="address[content][{$id}][postcode_address]" value="{$address.content[{$id}].postcode_address}" size="50" />
                                                </div>
                                            </div>
                                            <div class="col-xs-12 col-sm-6">
                                                <div class="form-group">
                                                    <label for="city_address_{$id}">{#city_address#|ucfirst} :</label>
                                                    <input type="text" class="form-control city" id="city_address_{$id}" name="address[content][{$id}][city_address]" value="{$address.content[{$id}].city_address}" size="50" />
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label for="country_address_{$id}">{#country#}&nbsp;:</label>
                                            <select id="country_address_{$id}" class="form-control country" name="address[content][{$id}][country_address]">
                                                <option value="">{#select_country#}</option>
                                                {foreach $countries as $key => $val}
                                                    <option value="{$val}" data-iso="{$key}" {if {$address.content[{$id}].country_address} === $val} selected{/if}>{$val|ucfirst}</option>
                                                {/foreach}
                                            </select>
                                        </div>
                                        <div class="row">
                                            <div class="col-xs-12 col-sm-6">
                                                <div class="form-group">
                                                    <label for="lat_address_{$id}">{#lat_address#|ucfirst} :</label>
                                                    <input type="text" class="form-control lat" id="lat_address_{$id}" name="address[content][{$id}][lat_address]" value="{$address.content[{$id}].lat_address}" size="50" />
                                                </div>
                                            </div>
                                            <div class="col-xs-12 col-sm-6">
                                                <div class="form-group">
                                                    <label for="lng_address_{$id}">{#lng_address#|ucfirst} :</label>
                                                    <input type="text" class="form-control lng" id="lng_address_{$id}" name="address[content][{$id}][lng_address]" value="{$address.content[{$id}].lng_address}" size="50" />
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-xs-12 col-sm-4 col-md-3 col-lg-2">
                                        <div class="form-group">
                                            <label for="published_address_{$id}">Statut</label>
                                            <input id="published_address_{$id}" data-toggle="toggle" type="checkbox" name="address[content][{$id}][published_address]" data-on="Publiée" data-off="Brouillon" data-onstyle="success" data-offstyle="danger"{if (!isset($address) && $iso@first) || $address.content[{$id}].published_address} checked{/if}>
                                        </div>
                                    </div>
                                </div>
                                <div class="map-col"></div>
                            </fieldset>
                            <fieldset>
                                <legend>Informations Complémentaires</legend>
                                <div class="row">
                                    <div class="col-xs-12 col-md-4">
                                        <div class="form-group">
                                            <label for="phone_address_{$id}">{#phone_address#}</label>
                                            <input id="phone_address_{$id}" type="text" class="form-control" name="address[content][{$id}][phone_address]" placeholder="{#ph_phone_address#}" {if isset($address)} value="{$address.content[{$id}].phone_address}"{/if} />
                                        </div>
                                        <div class="form-group">
                                            <label for="mobile_address_{$id}">{#mobile_address#}</label>
                                            <input id="mobile_address_{$id}" type="text" class="form-control" name="address[content][{$id}][mobile_address]" placeholder="{#ph_mobile_address#}" {if isset($address)} value="{$address.content[{$id}].mobile_address}"{/if} />
                                        </div>
                                        <div class="form-group">
                                            <label for="fax_address_{$id}">{#fax_address#}</label>
                                            <input id="fax_address_{$id}" type="text" class="form-control" name="address[content][{$id}][fax_address]" placeholder="{#ph_fax_address#}" {if isset($address)} value="{$address.content[{$id}].fax_address}"{/if} />
                                        </div>
                                        <div class="form-group">
                                            <label for="email_address_{$id}">{#email_address#}</label>
                                            <input id="email_address_{$id}" type="text" class="form-control" name="address[content][{$id}][email_address]" placeholder="{#ph_email_address#}" {if isset($address)} value="{$address.content[{$id}].email_address}"{/if} />
                                        </div>
                                    </div>
                                    <div class="col-xs-12 col-sm-8">
                                        <div class="form-group">
                                            <label for="about_address_{$id}">{#content#|ucfirst} :</label>
                                            <textarea cols="30" rows="13" name="address[content][{$id}][about_address]" id="about_address_{$id}" class="form-control">{call name=cleantextarea field=$address.content[{$id}].about_address}</textarea>
                                        </div>
                                    </div>
                                </div>
                            </fieldset>
                            <fieldset>
                                <legend>Options</legend>
                                <div class="row">
                                    <div class="col-xs-12 col-sm-12">
                                        <div class="form-group">
                                            <label for="link_address_{$id}">{#link_address#|ucfirst} :</label>
                                            <input type="text" class="form-control" id="link_address_{$id}" name="address[content][{$id}][link_address]" value="{$address.content[{$id}].link_address}" size="50" />
                                        </div>
                                    </div>
                                </div>
                            </fieldset>
                        </div>
                    {/foreach}
                </div>
            </div>
        </div>
        <fieldset>
            <legend>Enregistrer</legend>
            {if $edit}
            <input type="hidden" name="address[id]" value="{$address.id_address}" />
            {/if}
            <button class="btn btn-main-theme" type="submit" name="action" value="edit">{#save#|ucfirst}</button>
        </fieldset>
    </form>
</div>