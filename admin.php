<?php
require_once ('db.php');
require_once('marker.php');
/**
 * Class plugins_gmap_admin
 * Fichier pour l'administration d'un plugin
 */
class plugins_gmap_admin extends plugins_gmap_db
{
	protected $controller,
		$data,
		$template,
		$message,
		$plugins,
		$xml,
		$sitemap,
		$modelLanguage,
		$collectionLanguage,
		$upload,
		$imagesComponent,
		$header,
		$module,
		$mods;

	/**
	 * Global
	 * @var bool
	 */
	public $edit, $action, $tab, $id;

	/**
	 * Configuration
	 * @var array
	 */
	public $cfg;

	/**
	 * Page title and content
	 * @var array
	 */
	public $content;

	/**
	 * Address information
	 * @var array
	 */
	public $address, $img;

	/**
	 * plugins_gmap_admin constructor.
	 */
	public function __construct(){
		$this->template = new backend_model_template();
		$this->plugins = new backend_controller_plugins();
		$this->message = new component_core_message($this->template);
		$this->xml = new xml_sitemap();
		$this->sitemap = new backend_model_sitemap($this->template);
		$this->modelLanguage = new backend_model_language($this->template);
		$this->collectionLanguage = new component_collections_language();
		$this->upload = new component_files_upload();
		$this->imagesComponent = new component_files_images($this->template);
		$this->data = new backend_model_data($this);
		$this->header = new http_header();

		$formClean = new form_inputEscape();

		// --- Get
		if(http_request::isGet('controller')) $this->controller = $formClean->simpleClean($_GET['controller']);
		if (http_request::isGet('edit')) $this->edit = $formClean->numeric($_GET['edit']);
		if (http_request::isRequest('action')) $this->action = $formClean->simpleClean($_REQUEST['action']);
		if (http_request::isGet('tabs')) $this->tab = $formClean->simpleClean($_GET['tabs']);
		// --- Post
		// - Config
		if (http_request::isPost('cfg')) $this->cfg = $formClean->arrayClean($_POST['cfg']);
		// - Content
		if (http_request::isPost('content')) {
			$array = $_POST['content'];
			foreach($array as $key => $arr) {
				foreach($arr as $k => $v) {
					$array[$key][$k] = ($k == 'content_gmap') ? $formClean->cleanQuote($v) : $formClean->simpleClean($v);
				}
			}
			$this->content = $array;
		}
		// - Addresses
		if (http_request::isPost('address')) {
			/*$array = $_POST['address'];
			foreach($array as $key => $arr) {
				foreach($arr as $k => $v) {
					$array[$key][$k] = $formClean->simpleClean($v);
				}
			}
			$this->address = $array;*/
			$this->address = $formClean->arrayClean($_POST['address']);
		}
		// --- Add or Edit
		if (http_request::isPost('id')) $this->id = $formClean->simpleClean($_POST['id']);
		// --- Image Upload
		if(isset($_FILES['img']["name"])) $this->img = http_url::clean($_FILES['img']["name"]);
		if (http_request::isGet('plugin')) $this->plugin = $formClean->simpleClean($_GET['plugin']);
	}

	/**
	 * Method to override the name of the plugin in the admin menu
	 * @return string
	 */
	public function getExtensionName()
	{
		return $this->template->getConfigVars('gmap_plugin');
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
	 * @param $data
	 * @return array
	 */
	private function setItemContentData($data)
	{
		$arr = array();
		foreach ($data as $page) {
			if (!array_key_exists($page['id_gmap'], $arr)) {
				$arr[$page['id_gmap']] = array();
				$arr[$page['id_gmap']]['id_gmap'] = $page['id_gmap'];
			}
			$arr[$page['id_gmap']]['content'][$page['id_lang']] = array(
				'id_lang'          => $page['id_lang'],
				'name_gmap'        => $page['name_gmap'],
				'content_gmap'     => $page['content_gmap'],
				'published_gmap'   => $page['published_gmap']
			);
		}
		return $arr;
	}

	/**
	 * @param $data
	 * @return array
	 */
	private function setItemAddressData($data)
	{
		$arr = array();
		foreach ($data as $page) {

			if (!array_key_exists($page['id_address'], $arr)) {
				$arr[$page['id_address']] = array();
				$arr[$page['id_address']]['id_address'] = $page['id_address'];
				$arr[$page['id_address']]['img_address'] = $page['img_address'];
			}
			$arr[$page['id_address']]['content'][$page['id_lang']] = array(
				'id_lang'           => $page['id_lang'],
				'company_address'   => $page['company_address'],
				'content_address'   => $page['content_address'],
				'address_address'   => $page['address_address'],
				'postcode_address'  => $page['postcode_address'],
				'country_address'   => $page['country_address'],
				'city_address'      => $page['city_address'],
				'phone_address'     => $page['phone_address'],
				'mobile_address'    => $page['mobile_address'],
				'fax_address'       => $page['fax_address'],
				'email_address'     => $page['email_address'],
				'vat_address'       => $page['vat_address'],
				'lat_address'       => $page['lat_address'],
				'lng_address'       => $page['lng_address'],
				'link_address'      => $page['link_address'],
				'blank_address'     => $page['blank_address'],
				'img_address'       => $page['img_address'],
				'published_address' => $page['published_address']
			);
		}
		return $arr;
	}

	/**
	 * set Data from database
	 * @access private
	 */
	private function getBuildItems($data)
	{
		switch($data['type']){
			case 'content':
				$collection = $this->getItems('pages',null,'all',false);
				return $this->setItemContentData($collection);
				break;
			case 'address':
				$collection = $this->getItems('addressContent',$this->edit,'all',false);
				return $this->setItemAddressData($collection);
				break;
		}
	}

	/**
	 * @param $config
	 */
	public function setSitemap($config){
		$dateFormat = new date_dateformat();
		//print 'lang sitemap plugins: '.$config['id_lang'];
		$url = '/' . $config['iso_lang']. '/'.$config['name'].'/';
		$this->xml->writeNode(
			array(
				'type'      =>  'child',
				'loc'       =>  $this->sitemap->url(array('domain' => $config['domain'], 'url' => $url)),
				'image'     =>  false,
				'lastmod'   =>  $dateFormat->dateDefine(),
				'changefreq'=>  'always',
				'priority'  =>  '0.7'
			)
		);
	}

	/**
	 * @access private
	 * Charge les données de configuration pour l'édition
	 */
	private function setConfigData()
	{
		$config = parent::fetchData(array('context' => 'all','type' => 'config'));
		$configId = array();
		$configValue = array();
		foreach ($config as $key) {
			$configId[] = $key['config_id'];
			$configValue[] = $key['config_value'];
		}
		$setConfig = array_combine($configId, $configValue);
		$this->template->assign('getConfigData', $setConfig);
	}

	/**
	 * Create and insert the address image
	 * @param $img
	 * @param $name
	 * @param bool $debug
	 * @return null|string
	 * @throws Exception
	 */
	private function insert_image($img, $name, $id, $debug = false){
		if(isset($this->$img)) {
			$resultUpload = $this->upload->setImageUpload(
				'img',
				array(
					'name'            => filter_rsa::randMicroUI(),
					'edit'            => $name,
					'prefix'          => array('s_','m_','l_'),
					'module_img'      => 'plugins',
					'attribute_img'   => 'gmap',
					'original_remove' => false
				),
				array(
					'upload_root_dir' => 'upload/gmap', //string
					'upload_dir'      => $id //string ou array
				),
				$debug
			);

			$this->upd(array(
				'type' => 'img',
				'data' => array(
					'id_address' => $id,
					'img_address' => $resultUpload['file']
				)
			));

			return $resultUpload;
		}
	}

	/**
	 * @param $name
	 * @param $id
	 * @return null|string
	 */
	private function address_image($name, $id){
		if(isset($this->img) && !empty($id)) {
			return $this->insert_image(
				'img',
				$name,
				$id,
				false
			);
		}
	}

	/**
	 * Insert data
	 * @param array $config
	 */
	private function add($config)
	{
		switch ($config['type']) {
			case 'address':
				parent::insert(
					array('type' => $config['type'])
				);
				break;
			case 'addressContent':
			case 'content':
				parent::insert(
					array('type' => $config['type']),
					$config['data']
				);
				break;
		}
	}

	/**
	 * Update data
	 * @param array $config
	 */
	private function upd($config)
	{
		switch ($config['type']) {
			case 'address':
			case 'addressContent':
			case 'img':
			case 'content':
				parent::update(
					array('type' => $config['type']),
					$config['data']
				);
				break;
			case 'config':
				parent::update(
					array('type' => $config['type']),
					$config['data']
				);
				$this->message->json_post_response(true,'update');
				break;
		}
	}

	/**
	 * Delete a record
	 * @param $config
	 */
	private function del($config)
	{
		switch ($config['type']) {
			case 'address':
				parent::delete(
					array('type' => $config['type']),
					$config['data']
				);
				$this->message->json_post_response(true,'delete',array('id' => $this->id));
				break;
		}
	}

	/**
	 *
	 */
	private function loadModules() {
		$this->module = $this->module instanceof backend_controller_module ? $this->module : new backend_controller_module();
		if(empty($this->mods)) $this->mods = $this->module->load_module('gmap');
	}

	private function getModuleTabs() {
		$newsItems = [];
		foreach ($this->mods as $name => $mod) {
			$item['name'] = $name;
			if (method_exists($mod, 'getExtensionName')) {
				$this->template->addConfigFile(
					array(component_core_system::basePath() . 'plugins' . DIRECTORY_SEPARATOR . $name . DIRECTORY_SEPARATOR . 'i18n' . DIRECTORY_SEPARATOR),
					array($name . '_admin_')
				);
				//$this->template->configLoad();
				$item['title'] = $mod->getExtensionName();
			} else {
				$item['title'] = $name;
			}
			$newsItems[] = $item;
		}
		$this->template->assign('setTabsPlugins', $newsItems);
	}

	/**
	 *
	 */
	public function run(){
		$this->loadModules();
		if(isset($this->plugin)) {
			$this->modelLanguage->getLanguage();
			$defaultLanguage = $this->collectionLanguage->fetchData(array('context' => 'one', 'type' => 'default'));
			$this->getItems('address',array('default_lang' => $defaultLanguage['id_lang']),'all');
			$this->getModuleTabs();
			// Initialise l'API menu des plugins core
			$this->modelLanguage->getLanguage();
			// Execute un plugin core
			$class = 'plugins_' . $this->plugin . '_core';
			if(file_exists(component_core_system::basePath().'plugins'.DIRECTORY_SEPARATOR.$this->plugin.DIRECTORY_SEPARATOR.'core.php') && class_exists($class) && method_exists($class, 'run')) {
				$executeClass =  new $class;
				if($executeClass instanceof $class){
					$executeClass->run();
				}
			}
		}
		else {
			if(isset($this->tab)) {
				if ($this->tab === 'content') {
					if (isset($this->action)) {
						switch ($this->action) {
							case 'edit':
								if(isset($this->content) && !empty($this->content)) {
									$root = parent::fetchData(array('context' => 'one', 'type' => 'root'));
									if (!$root) {
										parent::insert(array('type' => 'root'));
										$root = parent::fetchData(array('context' => 'one', 'type' => 'root'));
									}
									$id = $root['id_gmap'];

									foreach ($this->content as $lang => $content) {
										if(empty($content['id'])) $content['id'] = $id;
										$rootLang = $this->getItems('content',array('id' => $id,'id_lang' => $lang),'one',false);

										$content['id_lang'] = $lang;
										$content['published_gmap'] = (!isset($content['published_gmap']) ? 0 : 1);

										$config = array(
											'type' => 'content',
											'data' => $content
										);

										($rootLang) ? $this->upd($config) : $this->add($config);
									}
									$this->message->json_post_response(true,'update');
								}
								break;
						}
					}
				}
				elseif ($this->tab === 'config') {
					if (isset($this->action)) {
						switch ($this->action) {
							case 'edit':
								if (isset($this->cfg) && !empty($this->cfg)) {
									if(isset($this->cfg['markerColor']) && !empty($this->cfg['markerColor'])) {
										$marker = new plugins_gmap_marker($this->cfg['markerColor'], $this->template);
										$marker->createMarker();
									}

									$this->upd(array(
										'type' => 'config',
										'data' => $this->cfg
									));
								}
								break;
						}
					}
				}
				elseif ($this->tab === 'address') {
					switch ($this->action) {
						case 'add':
						case 'edit':
							if(isset($this->address) && !empty($this->address)) {
								$notify = 'update';
								$img = null;

								if(isset($this->slide['id']) && !empty($this->address['id'])) {
									$img = parent::fetchData(array('context' => 'one', 'type' => 'img'),$this->address['id']);
									$img = $img['img_slide'];
								}

								if (!isset($this->address['id'])) {
									$this->add(array(
										'type' => 'address'
									));

									$lastAddress = $this->getItems('lastAddress', null,'one',false);
									$this->address['id'] = $lastAddress['id_address'];
									$notify = 'add_redirect';
								}

								if(isset($this->img)) {
									$img = $this->address_image($img, $this->address['id']);

									$this->upd(array(
										'type' => 'img',
										'data' => array(
											'id' => $this->address['id'],
											'img' => $img['file']
										)
									));
								}

								foreach ($this->address['content'] as $lang => $address) {
									$address['id_lang'] = $lang;
									$address['blank_address'] = (!isset($address['blank_address']) ? 0 : 1);
									$address['published_address'] = (!isset($address['published_address']) ? 0 : 1);
									$addrLang = $this->getItems('addressContent',array('id' => $this->address['id'],'id_lang' => $lang),'one',false);

									if($addrLang) {
										$address['id'] = $addrLang['id_content'];
									}
									else {
										$address['id_address'] = $this->address['id'];
									}

									$config = array(
										'type' => 'addressContent',
										'data' => $address
									);

									$addrLang ? $this->upd($config) : $this->add($config);
								}
								$this->message->json_post_response(true,$notify);
							}
							else {
								$this->modelLanguage->getLanguage();
								$country = new component_collections_country();
								$this->template->assign('countries',$country->getCountries());
								$this->setConfigData();

								if(isset($this->edit)) {
									$setEditData = $this->getBuildItems(array('type'=>'address'));
									$this->template->assign('address', $setEditData[$this->edit]);
								}

								$this->template->assign('edit',($this->action === 'edit' ? true : false));
								$this->template->display('edit.tpl');
							}
							break;
						case 'delete':
							if(isset($this->id) && !empty($this->id)) {
								$this->del(
									array(
										'type' => 'address',
										'data' => array(
											'id' => $this->id
										)
									)
								);
							}
							break;
						case 'order':
							if (isset($this->address)) {
								$this->update_order();
							}
							break;
					}
				}
			}
			else {
				$this->modelLanguage->getLanguage();
				$defaultLanguage = $this->collectionLanguage->fetchData(array('context'=>'one','type'=>'default'));
				$this->setConfigData();

				$last = parent::fetchData(array('context' => 'one', 'type' => 'root'));
				$pages = $this->getBuildItems(array('type' => 'content'));
				$this->template->assign('pages', (isset($pages) && isset($last['id_gmap']) ? $pages[$last['id_gmap']] : []));

				$this->getItems('address',array('default_lang' => $defaultLanguage['id_lang']),'all');
				$assign = array(
					'id_address',
					'company_address' => array('title' => 'name'),
					'address_address' => array('title' => 'name'),
					'postcode_address' => array('title' => 'name'),
					'country_address' => array('title' => 'name'),
					'content_address' => array('class' => 'fixed-td-lg', 'type' => 'bin', 'input' => null),
					'date_register'
				);
				$this->data->getScheme(array('mc_gmap_address', 'mc_gmap_address_content'), array('id_address', 'company_address', 'address_address','postcode_address','country_address','content_address', 'date_register'), $assign);

				$this->getModuleTabs();
				$this->template->display('index.tpl');
			}
		}
	}
}