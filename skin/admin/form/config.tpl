<div class="row">
    <form method="post" action="{$smarty.server.SCRIPT_NAME}?controller={$smarty.get.controller}&amp;tabs=config&amp;action=edit" class="validate_form col-ph-12 col-sm-10 col-md-8 col-lg-6">
        <fieldset>
            <legend>Configuration</legend>
            <div class="form-group">
                <label for="api_key">API KEY :</label>
                <input type="text" class="form-control" id="api_key" name="cfg[api_key]" value="{$getConfigData.api_key}" size="50" />
            </div>
            <div class="row">
                <div class="col-xs-12 col-sm-4 col-md-3">
                    <div class="form-group">
                        <label>Marqueur :</label>
                        {*{if is_array($markerCollection) && !empty($markerCollection)}
                        {foreach $markerCollection as $key => $val}
                            <label class="radio-inline">
                            <input type="radio" name="config[marker]" {if {$val|substr:0:-4} eq $getConfigData.marker}checked="checked"{/if} value="{$val|substr:0:-4}" />
                            <img alt="marker {$val}" src="/plugins/gmap/markers/{$val}" />
                            </label>
                        {/foreach}
                        {/if}*}
                        <div class="input-group colorpicker-component csspicker">
                            <input type="text" value="{$getConfigData.markerColor}" class="form-control" name="cfg[markerColor]" />
                            <span class="input-group-addon"><i></i></span>
                        </div>
                    </div>
                </div>
            </div>
        </fieldset>
        <fieldset>
            <legend>Enregistrer</legend>
            <button type="submit" class="btn btn-primary">{#save#|ucfirst}</button>
        </fieldset>
    </form>
</div>