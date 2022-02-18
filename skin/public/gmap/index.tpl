{extends file="layout.tpl"}
{block name="styleSheet" append}
    <script src="{if isset($smarty.server.HTTPS) eq 'on'}https{else}http{/if}://maps.google.com/maps/api/js?sensor=false&amp;language={$lang}{if $config.api_key != '' AND $config.api_key != NULL}&amp;key={$config.api_key}{/if}"></script>
    {headlink rel="stylesheet" href="/min/?f=plugins/gmap/css/perfect-scrollbar.min.css" concat=$concat media="screen"}
{/block}
{block name="styleSheet"}
    <script src="https://maps.google.com/maps/api/js?language={$lang}{if $config.api_key != '' AND $config.api_key != NULL}&amp;key={$config.api_key}{/if}"></script>
    {$css_files = [
    "/plugins/gmap/css/perfect-scrollbar.min.css",
    "/skin/{$theme}/css/gmap{if $setting.mode.value !== 'dev'}.min{/if}.css",
    "/skin/{$theme}/css/form{if $setting.mode.value !== 'dev'}.min{/if}.css"
    ]}
{/block}
{block name="title"}{seo_rewrite conf=['level'=>'root','type'=>'title','default'=>{#seo_title_gmap#}]}{/block}
{block name="description"}{seo_rewrite conf=['level'=>'root','type'=>'description','default'=>{#seo_desc_gmap#}]}{/block}
{block name='body:id'}gmap{/block}
{block name="main"}
    {*<div class="container">*}
        {include file="gmap/map.tpl"}
    {*</div>*}
{/block}
{block name="scripts"}
    {$jquery = true}
    {$js_files = [
    'group' => [
    'form'
    ],
    'normal' => [
    ],
    'defer' => [
    "/skin/{$theme}/js/{if $setting.mode.value === 'dev'}src/{/if}form{if $setting.mode.value !== 'dev'}.min{/if}.js",
    "/skin/{$theme}/js/vendor/localization/messages_{$lang}.js",
    "/plugins/gmap/js/perfect-scrollbar.min.js",
    "/plugins/gmap/js/gmap3-7.2.min.js",
    "plugins/gmap/js/{if $setting.mode.value === 'dev'}src/{/if}gmap{if $setting.mode.value !== 'dev'}.min{/if}.js"
    ]
    ]}
    {if {$lang} !== "en"}{$js_files['defer'][] = "/libjs/vendor/localization/messages_{$lang}.js"}{/if}
{/block}
{block name="foot"}
    <script type="text/javascript">
        $(function(){
            if (typeof gmap == "undefined"){
                console.log("gmap is not defined");
            }else{
                gmap.run({$config_gmap},{literal}{scrollwheel: false}{/literal});
            }
        });
    </script>
{/block}