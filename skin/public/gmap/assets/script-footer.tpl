{block name="scripts"}
    <script type="text/javascript">
        let configMap = {$config_gmap};
    </script>
    {$jquery = false}
    {$js_files = [
        'group' => [],
        'normal' => [],
        'async' => [
            "/plugins/gmap/js/{if $setting.mode === 'dev'}src/{/if}gmap{if $setting.mode !== 'dev'}.min{/if}.js"
        ]
    ]}
{/block}