{extends file="layout.tpl"}
{block name="stylesheets" append}
    {capture name="cssColorpicker"}{strip}
        /{baseadmin}/min/?f=
        {baseadmin}/template/css/bootstrap-colorpicker.min.css
    {/strip}{/capture}
    {headlink rel="stylesheet" href=$smarty.capture.cssColorpicker media="screen"}
{/block}
{block name='head:title'}gmap{/block}
{block name='body:id'}gmap{/block}
{block name='article:header'}
    {if {employee_access type="append" class_name=$cClass} eq 1}
        <div class="pull-right">
            <p class="text-right">
                {#nbr_address#|ucfirst}: {$address|count}<a href="{$smarty.server.SCRIPT_NAME}?controller={$smarty.get.controller}&amp;tabs=address&amp;action=add" title="{#add_address#}" class="btn btn-link">
                    <span class="fa fa-plus"></span> {#add_address#|ucfirst}
                </a>
            </p>
        </div>
    {/if}
    <h1 class="h2">Gmap</h1>
{/block}
{block name='article:content'}
{if {employee_access type="view" class_name=$cClass} eq 1}
    <div class="panels row">
    <section class="panel col-xs-12 col-md-12">
    {if $debug}
        {$debug}
    {/if}
    <header class="panel-header panel-nav">
        <h2 class="panel-heading h5">Gestion de Gmap</h2>
        <ul class="nav nav-tabs" role="tablist">
            <li role="presentation" {if !$smarty.get.plugin && !$smarty.get.tab}class="active"{/if}><a href="{if $smarty.get.plugin}{$smarty.server.SCRIPT_NAME}?controller={$smarty.get.controller}{else}#general{/if}" aria-controls="general" role="tab" {if !$smarty.get.plugin}data-toggle="tab"{/if}>{#text#}</a></li>
            <li role="presentation" {if !$smarty.get.plugin && $smarty.get.tab === 'address'}class="active"{/if}><a href="{if $smarty.get.plugin}{$smarty.server.SCRIPT_NAME}?controller={$smarty.get.controller}&tab=address{else}#address{/if}" aria-controls="address" role="tab" {if !$smarty.get.plugin}data-toggle="tab"{/if}>{#address#|ucfirst}</a></li>
            <li role="presentation" {if !$smarty.get.plugin && $smarty.get.tab === 'config'}class="active"{/if}><a href="{if $smarty.get.plugin}{$smarty.server.SCRIPT_NAME}?controller={$smarty.get.controller}&tab=config{else}#config{/if}" aria-controls="config" role="tab" {if !$smarty.get.plugin}data-toggle="tab"{/if}>Configuration</a></li>
            {foreach $setTabsPlugins as $key => $value}
                <li role="presentation" {if $smarty.get.plugin eq $value.name}class="active"{/if}><a href="{$smarty.server.SCRIPT_NAME}?controller={$smarty.get.controller}&plugin={$value.name}" aria-controls="plugins-{$value.name}" role="tab">{$value.title|ucfirst}</a></li>
            {/foreach}
        </ul>
    </header>
    <div class="panel-body panel-body-form">
        <div class="mc-message-container clearfix">
            <div class="mc-message"></div>
        </div>
        {*<pre>{$pages|print_r}</pre>*}
        <div class="tab-content">
            {if !$smarty.get.plugin}
            <div role="tabpanel" class="tab-pane{if !$smarty.get.plugin && !$smarty.get.tab} active{/if}" id="general">
                {include file="form/content.tpl" controller="gmap"}
            </div>
            <div role="tabpanel" class="tab-pane{if !$smarty.get.plugin && $smarty.get.tab === 'address'} active{/if}" id="address">
                {include file="section/form/table-form-2.tpl" data=$address idcolumn='id_address' activation=false search=false sortable=true controller="gmap" subcontroller="address"}
            </div>
            <div role="tabpanel" class="tab-pane{if !$smarty.get.plugin && $smarty.get.tab === 'config'} active{/if}" id="config">
                {include file="form/config.tpl"}
            </div>
            {/if}
            {foreach $setTabsPlugins as $value}{if $smarty.get.plugin eq $value.name}
            <div role="tabpanel" class="tab-pane active" id="plugins-{$value.name}">
                {if $smarty.get.plugin eq $value.name}{block name="plugin:content"}{/block}{/if}
                </div>{/if}
            {/foreach}
        </div>
    </div>
    </section>
    </div>
    {include file="modal/delete.tpl" data_type='address' title={#modal_delete_title#|ucfirst} info_text=true delete_message={#delete_gmap_message#}}
    {include file="modal/error.tpl"}
    {else}
        {include file="section/brick/viewperms.tpl"}
{/if}
{/block}

{block name="foot" append}
    {include file="section/footer/editor.tpl"}
    {capture name="scriptForm"}{strip}
        /{baseadmin}/min/?f=libjs/vendor/bootstrap-colorpicker.min.js
    {/strip}{/capture}
    {script src=$smarty.capture.scriptForm type="javascript"}

    <script type="text/javascript">
        $(function(){
            $('.csspicker').colorpicker();
        });
    </script>
{/block}
