<?php
function smarty_function_gmap_data($params, $smarty){
    $modelTemplate = $smarty->tpl_vars['modelTemplate']->value instanceof frontend_model_template ? $smarty->tpl_vars['modelTemplate']->value : new frontend_model_template();
    $modelTemplate->addConfigFile([component_core_system::basePath().'/plugins/gmap/i18n/'], ['public_local_']);
    $modelTemplate->configLoad();
    $gmap = new plugins_gmap_public($modelTemplate);
    $run = $gmap->outrun();
    $smarty->assign('map_page',$run['page']);
    $smarty->assign('config_gmap',$run['config_gmap']['config_gmap']);
    $smarty->assign('addresses',$run['config_gmap']['addresses']);
    $smarty->assign('config',$run['config']);
}