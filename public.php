<?php
require_once('db.php');
require_once('marker.php');
/**
 * @category plugin
 * @package gmap
 * @copyright MAGIX CMS Copyright (c) 2011 Gerits Aurelien, http://www.magix-dev.be, http://www.magix-cms.com
 * @license Dual licensed under the MIT or GPL Version 3 licenses.
 * @version 1.0
 * @create 20-12-2021
 * @author Aurélien Gérits <aurelien@magix-cms.com>
 * @name plugins_gmap_public
 */
class plugins_gmap_public extends plugins_gmap_db {
	/**
	 * @var frontend_model_template $template
	 * @var frontend_model_data $data
	 */
    protected frontend_model_template $template;
    protected frontend_model_data $data;

	/**
	 * @var bool $dotless
	 */
	public bool $dotless;

	/**
	 * @var string $lang
	 * @var string $marker
	 */
	public string
		$lang,
		$marker;

	/**
	 * @var array $conf
	 */
	public array $conf;

	/**
	 *
	 */
	public function __construct() {
	    $this->template = new frontend_model_template();
		$this->data = new frontend_model_data($this);
		$this->lang = $this->template->lang;
		if(http_request::isGet('marker')) $this->marker = form_inputEscape::simpleClean($_GET['marker']);
		$this->dotless = http_request::isGet('dotless');
		$config = $this->getItems('config');
		if(!empty($config)) {
			$configId = [];
			$configValue = [];
			foreach($config as $key){
				$configId[] = $key['config_id'];
				$configValue[] = $key['config_value'];
			}
			$config = array_combine($configId,$configValue);
		}
		$this->conf = $config;
	}

	/**
	 * Assign data to the defined variable or return the data
	 * @param string $type
	 * @param string|array|null $id
	 * @param string|null $context
	 * @param bool|string $assign
	 * @return mixed
	 */
	private function getItems(string $type, $id = null, ?string $context = null, $assign = false) {
		return $this->data->getItems($type, $id, $context, $assign);
	}

	/**
	 * Load map data
	 * @return string
	 */
	private function setJsConfig(): string {
		$addresses = $this->getAddresses();
		//$config = $this->conf;
		$config = ['api_key' => $this->conf['api_key']];
		if($addresses != null) {
			$map = [];
			foreach ($addresses as $addr){
				$mark = '{';
				foreach ($addr as $k => $v) {
					$mark .= '"'.str_replace('_address','',$k).'":'.json_encode($v).',';
				}
				$mark = substr($mark, 0, -1).'}';
				$map[] = $mark;
			}
			$config['markers'] = '['.implode(',',$map).']';
		}
		else {
			$config['markers'] = '[]';
		}

		$configString = [];
		foreach ($config as $k => $v) {
			if($k != 'markers')
				$v = json_encode($v);
			$configString[]= $k.':'.$v;
		}
		$detect = new Mobile_Detect;
		$OS = false;
		if( $detect->isiOS() ){
			$OS = 'IOS';
		}
		elseif( $detect->isAndroidOS() ){
			$OS = 'Android';
		}
		$configString[] = '"OS":"'.$OS.'"';
		$configString[] = '"lang":"'.$this->lang.'"';
		return '{'.implode(',',$configString).'}';
	}

	/**
	 * Load map data
	 * @return array
	 */
	private function setConfig(): array {
		$config = [];
		if(!empty($this->conf)) {
			foreach ($this->conf as $k => $v) {
				$config[$k] = $v;
			}
		}
		return $config;
	}

	/**
	 * Execute le plugin dans la partie public
	 */
	public function run() {
		$this->template->configLoad();

		if(isset($this->marker)) {
			$img = '';

			if($this->marker === 'main') {
				$markerPath = component_core_system::basePath().'/plugins/gmap/markers/'.$this->marker.($this->dotless?'-dotless':'').'.svg';

				if(!file_exists($markerPath)) {
					$config = parent::fetchData(array('context' => 'one','type' => 'config'));
					$marker = new plugins_gmap_marker($config['markerColor'],$this->template);
					$marker->createMarker();
				} else {
					$img = file_get_contents($markerPath);
				}
			}
			else {
				$img = file_get_contents(component_core_system::basePath().'/plugins/gmap/markers/grey'.($this->dotless?'-dotless':'').'.svg');
			}

			if($img !== '') {
				header('Content-type: image/svg+xml');
				print $img;
			}
		}
		else {
			$this->getItems('page',['lang' => $this->lang],'one',true);
            $this->template->breadcrumb->addItem($this->template->getConfigVars('gmap'));
			$this->template->assign('addresses',$this->getAddresses());
			$this->template->assign('config',$this->setConfig());
			$this->template->assign('config_gmap',$this->setJsConfig());
			$this->template->display('gmap/index.tpl');
		}
    }

	/**
	 * @return array
	 */
	public function outrun(): array {
		return [
			'page' => $this->getItems('page',['lang' => $this->lang],'one'),
			'config' => $this->setConfig(),
			'config_gmap' => $this->setJsConfig()
		];
	}

	/**
	 * @return array
	 */
	public function getAddresses(): array {
		return $this->getItems('addresses',['lang' => $this->lang],'all')?: [];
	}
}