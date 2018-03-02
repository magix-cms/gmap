{extends file="layout.tpl"}
{block name="stylesheets"}
    {headlink rel="stylesheet" href="/{baseadmin}/min/?f=plugins/{$smarty.get.controller}/css/admin.min.css" media="screen"}
{/block}
{block name='head:title'}gmap{/block}
{block name='body:id'}gmap{/block}
{block name='article:header'}
    <h1 class="h2"><a href="{$smarty.server.SCRIPT_NAME}?controller={$smarty.get.controller}" title="Afficher la liste des cartes">Gmap</a></h1>
{/block}
{block name='article:content'}
{if {employee_access type="edit" class_name=$cClass} eq 1}
    <div class="panels row">
        <section class="panel col-xs-12 col-md-12">
            {if $debug}
                {$debug}
            {/if}
            <header class="panel-header {*panel-nav*}">
                <h2 class="panel-heading h5">{#edit_address#|ucfirst}</h2>
                {*<ul class="nav nav-tabs" role="tablist">
                    <li role="presentation" class="active"><a href="#general" aria-controls="general" role="tab" data-toggle="tab">{#text#}</a></li>
                    <li role="presentation"><a href="#image" aria-controls="image" role="tab" data-toggle="tab">{#image#}</a></li>
                </ul>*}
            </header>
            <div class="panel-body panel-body-form">
                <div class="mc-message-container">
                    <div class="mc-message"></div>
                </div>
                {include file="form/address.tpl" controller="gmap"}
            </div>
        </section>
    </div>
{/if}
{/block}

{block name="foot" append}
    {include file="section/footer/editor.tpl"}
    <script src="{if isset($smarty.server.HTTPS) eq 'on'}https{else}http{/if}://maps.google.com/maps/api/js?sensor=false&amp;language=fr{if $getConfigData.api_key != '' AND $getConfigData.api_key != NULL}&amp;key={$getConfigData.api_key}{/if}" type="text/javascript"></script>
    {capture name="scriptForm"}{strip}
        /{baseadmin}/min/?f=
        plugins/gmap/js/gmap3-7.2.min.js,
        plugins/gmap/js/admin.min.js
    {/strip}{/capture}
    {script src=$smarty.capture.scriptForm type="javascript"}

    <script type="text/javascript">
        $(function(){
            if (typeof gmap == "undefined")
            {
                console.log("gmap is not defined");
            }else{
                gmap.addAddress();
            }
        });
    </script>
{/block}
