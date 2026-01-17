<?php
function smarty_function_gmap_addresses($params, $smarty){
	$modelTemplate = $smarty->tpl_vars['modelTemplate']->value instanceof frontend_model_template ? $smarty->tpl_vars['modelTemplate']->value : new frontend_model_template();
	$gmap = new plugins_gmap_public($modelTemplate);
	$smarty->assign('addresses',$gmap->getAddresses());
}