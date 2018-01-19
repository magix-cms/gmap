{extends file="layout.tpl"}
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
            <li role="presentation" class="active"><a href="#general" aria-controls="general" role="tab" data-toggle="tab">{#text#}</a></li>
            <li role="presentation"><a href="#address" aria-controls="address" role="tab" data-toggle="tab">{#address#}</a></li>
            <li role="presentation"><a href="#config" aria-controls="config" role="tab" data-toggle="tab">Configuration</a></li>
        </ul>
    </header>
    <div class="panel-body panel-body-form">
        <div class="mc-message-container clearfix">
            <div class="mc-message"></div>
        </div>
        {*<pre>{$pages|print_r}</pre>*}
        <div class="tab-content">
            <div role="tabpanel" class="tab-pane active" id="general">
                {include file="form/content.tpl" controller="gmap"}
            </div>
            <div role="tabpanel" class="tab-pane" id="address">
                {include file="section/form/table-form-2.tpl" data=$address idcolumn='id_address' activation=false search=false sortable=true controller="gmap" subcontroller="address"}
            </div>
            <div role="tabpanel" class="tab-pane" id="config">
                {include file="form/config.tpl"}
            </div>
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
        /{baseadmin}/min/?f=
        {*plugins/gmap/js/gmap3-7.2.min.js,*}
        plugins/gmap/js/admin.min.js
    {/strip}{/capture}
    {script src=$smarty.capture.scriptForm type="javascript"}

    <script type="text/javascript">
        $(function(){
            if (typeof gmap == "undefined")
            {
                console.log("gmap is not defined");
            }else{
                gmap.run();
            }
        });
    </script>
{/block}
