<?php
require_once('db.php');
require_once('marker.php');

class plugins_gmap_public extends plugins_gmap_db
{
    protected $template, $data, $getlang;

	/**
	 * paramètre pour la requête JSON
	 */
	public $json_multi_data,$marker,$dotless;

	/**
	 * @access public
	 * Constructor
	 */
	public function __construct()
	{
	    $this->template = new frontend_model_template();
		$this->data = new frontend_model_data($this);
		$this->getlang = $this->template->currentLanguage();
		$formClean = new form_inputEscape();

		if(http_request::isGet('marker')){
			$this->marker = $formClean->simpleClean($_GET['marker']);
		}

		$this->dotless = http_request::isGet('dotless') ? true : false;
	}

	/**
	 * Assign data to the defined variable or return the data
	 * @param string $type
	 * @param string|int|null $id
	 * @param string $context
	 * @param boolean $assign
	 * @return mixed
	 */
	private function getItems($type, $id = null, $context = null, $assign = true) {
		return $this->data->getItems($type, $id, $context, $assign);
	}

    /**
	 * Load map data
     * @access private
     */
	private function setJsConfig() {
		$addresses = $this->getItems('addresses',array('lang' => $this->getlang),'all');
		$config = parent::fetchData(array('context' => 'all','type' => 'config'));

        $configId = array();
        $configValue = array();
        foreach($config as $key){
            $configId[] = $key['config_id'];
            $configValue[] = $key['config_value'];
        }
        $setConfig = array_combine($configId,$configValue);

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
			$setConfig['markers'] = '['.implode(',',$map).']';
		}
		else {
			$setConfig['markers'] = '[]';
		}

		$config = [];
		foreach ($setConfig as $k => $v) {
			if($k != 'markers')
				$v = json_encode($v);
			$config[]= $k.':'.$v;
		}
		$detect = new Mobile_Detect;
		$OS = false;
		if( $detect->isiOS() ){
			$OS = 'IOS';
		}elseif( $detect->isAndroidOS() ){
			$OS = 'Android';
		}
		$config[] = '"OS":"'.$OS.'"';
		$config[] = '"lang":"'.$this->getlang.'"';
		$this->template->assign('config_gmap','{'.implode(',',$config).'}');
	}

    /**
	 * Load map data
     * @access private
     */
	private function setConfig() {
		$config = parent::fetchData(array('context' => 'all','type' => 'config'));

        $configId = array();
        $configValue = array();
        foreach($config as $key){
            $configId[] = $key['config_id'];
            $configValue[] = $key['config_value'];
        }
        $setConfig = array_combine($configId,$configValue);

		$config = [];
		foreach ($setConfig as $k => $v) {
			$config[$k] = $v;
		}
		$this->template->assign('config',$config);
	}

	/**
	 * @access public
	 * Execute le plugin dans la partie public
	 */
	public function run() {
		$this->template->configLoad();

		if(isset($this->marker)) {
			$img = '';

			if($this->marker === 'main') {
				$markerPath = component_core_system::basePath().'/plugins/gmap/markers/'.$this->marker.($this->dotless?'-dotless':'').'.svg';

				if(!file_exists($markerPath)) {
					$config = parent::fetchData(array('context' => 'one','type' => 'config'));;
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
		} else {
			$this->getItems('page',array('lang' => $this->getlang),'one');
			$this->setConfig();
			$this->setJsConfig();
			$this->template->display('gmap/index.tpl');
		}
    }
}