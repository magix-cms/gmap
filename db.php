<?php
/**
 * @category plugin
 * @package gmap
 * @copyright MAGIX CMS Copyright (c) 2011 Gerits Aurelien, http://www.magix-dev.be, http://www.magix-cms.com
 * @license Dual licensed under the MIT or GPL Version 3 licenses.
 * @version 1.0
 * @create 20-12-2021
 * @author Aurélien Gérits <aurelien@magix-cms.com>
 * @name plugins_gmap_db
 */
class plugins_gmap_db {
	/**
	 * @var debug_logger $logger
	 */
	protected debug_logger $logger;

	/**
	 * @param array $config
	 * @param array $params
	 * @return array|bool
	 */
	public function fetchData(array $config, array $params = []) {
		if($config['context'] === 'all') {
			switch ($config['type']) {
				case 'pages':
					$query = 'SELECT h.*,c.*
							FROM mc_gmap AS h
							JOIN mc_gmap_content AS c USING(id_gmap)
							JOIN mc_lang AS lang ON(c.id_lang = lang.id_lang)';
					break;
				case 'address':
					$query = 'SELECT a.*,c.*
							FROM mc_gmap_address AS a
							JOIN mc_gmap_address_content AS c USING(id_address)
							JOIN mc_lang AS lang ON(c.id_lang = lang.id_lang)
							WHERE c.id_lang = :default_lang';
					break;
				case 'addressContent':
					$query = 'SELECT a.*,c.*
							FROM mc_gmap_address AS a
							JOIN mc_gmap_address_content AS c USING(id_address)
							JOIN mc_lang AS lang ON(c.id_lang = lang.id_lang)
							WHERE c.id_address = :id';
					break;
				case 'addresses':
					$query = "SELECT a.*,c.*
							FROM mc_gmap_address AS a
							JOIN mc_gmap_address_content AS c USING(id_address)
							JOIN mc_lang AS l USING(id_lang) 
							WHERE iso_lang = :lang
							AND c.published_address = 1";
					break;
				case 'config':
					$query = "SELECT * FROM mc_gmap_config";
					break;
				default:
					return false;
			}

			try {
				return component_routing_db::layer()->fetchAll($query, $params);
			}
			catch (Exception $e) {
				if(!isset($this->logger)) $this->logger = new debug_logger(MP_LOG_DIR);
				$this->logger->log('statement','db',$e->getMessage(),$this->logger::LOG_MONTH);
			}
		}
		elseif($config['context'] === 'one') {
			switch ($config['type']) {
				case 'root':
					$query = 'SELECT * FROM mc_gmap ORDER BY id_gmap DESC LIMIT 0,1';
					break;
				case 'content':
					$query = 'SELECT * FROM mc_gmap_content WHERE id_gmap = :id AND id_lang = :id_lang';
					break;
				case 'page':
					$query = 'SELECT *
							FROM mc_gmap as g
							JOIN mc_gmap_content as gc USING(id_gmap)
							JOIN mc_lang as l USING(id_lang)
							WHERE iso_lang = :lang
							LIMIT 0,1';
					break;
				case 'markerColor':
					$query = "SELECT config_value as markerColor FROM mc_gmap_config WHERE config_id = 'markerColor'";
					break;
				case 'addressContent':
					$query = 'SELECT * FROM mc_gmap_address_content WHERE id_address = :id AND id_lang = :id_lang';
					break;
				case 'lastAddress':
					$query = 'SELECT * FROM mc_gmap_address ORDER BY id_address DESC LIMIT 0,1';
					break;
				default:
					return false;
			}

			try {
				return component_routing_db::layer()->fetch($query, $params);
			}
			catch (Exception $e) {
				if(!isset($this->logger)) $this->logger = new debug_logger(MP_LOG_DIR);
				$this->logger->log('statement','db',$e->getMessage(),$this->logger::LOG_MONTH);
			}
		}
		return false;
    }

	/**
	 * @param string $type
	 * @param array $params
	 * @return bool|mixed
	 */
	public function insert(string $type, array $params = []) {
		switch ($type) {
			case 'root':
				$query = 'INSERT INTO mc_gmap(date_register) VALUES (NOW())';
				break;
			case 'content':
				$query = 'INSERT INTO mc_gmap_content(id_gmap, id_lang, name_gmap, content_gmap, published_gmap) 
						VALUES (:id, :id_lang, :name_gmap, :content_gmap, :published_gmap)';
				break;
			case 'address':
                $queries = [
                    ['request' => 'INSERT INTO mc_gmap_address(order_address, date_register) SELECT COUNT(id_address), NOW() FROM mc_gmap_address', 'params' => []],
                    ['request' => 'SELECT @address_id := LAST_INSERT_ID() as id_address', 'params' => [], 'fetch' => true]
                ];

                try {
                    $results = component_routing_db::layer()->transaction($queries);
                    return $results[1];
                }
                catch (Exception $e) {
                    if(!isset($this->logger)) $this->logger = new debug_logger(MP_LOG_DIR);
                    $this->logger->log('statement','db',$e->getMessage(),$this->logger::LOG_MONTH);
                    return false;
                }
			case 'addressContent':
				$query = 'INSERT INTO mc_gmap_address_content(id_address, id_lang, company_address, content_address, address_address, postcode_address, country_address, city_address, phone_address, mobile_address, fax_address, email_address, vat_address, lat_address, lng_address, link_address, blank_address, last_update, published_address)
						VALUES (:id_address, :id_lang, :company_address, :content_address, :address_address, :postcode_address, :country_address, :city_address, :phone_address, :mobile_address, :fax_address, :email_address, :vat_address, :lat_address, :lng_address, :link_address, :blank_address, NOW(), :published_address)';
                //print $query;
                //var_dump($params);
				break;
			default:
				return false;
		}

		try {
			component_routing_db::layer()->insert($query,$params);
			return true;
		}
		catch (Exception $e) {
			if(!isset($this->logger)) $this->logger = new debug_logger(MP_LOG_DIR);
			$this->logger->log('statement','db',$e->getMessage(),$this->logger::LOG_MONTH);
			return false;
		}
    }

	/**
	 * @param string $type
	 * @param array $params
	 * @return bool
	 */
	public function update(string $type, array $params = []): bool {
		switch ($type) {
			case 'content':
				$query = 'UPDATE mc_gmap_content 
						SET 
							name_gmap = :name_gmap,
							content_gmap = :content_gmap,
							published_gmap = :published_gmap
						WHERE id_gmap = :id 
						AND id_lang = :id_lang';
				break;
			case 'addressContent':
				$query = 'UPDATE mc_gmap_address_content
						SET 
							company_address = :company_address,
							content_address = :content_address,
							address_address = :address_address,
							postcode_address = :postcode_address,
							country_address = :country_address,
							city_address = :city_address,
							phone_address = :phone_address, 
							mobile_address = :mobile_address, 
							fax_address = :fax_address, 
							email_address = :email_address, 
							vat_address = :vat_address, 
							lat_address = :lat_address, 
							lng_address = :lng_address, 
							link_address = :link_address,
							blank_address = :blank_address,
							last_update = NOW(), 
							published_address = :published_address
						WHERE id_content = :id 
						AND id_lang = :id_lang';
				break;
			case 'config':
				$query = "UPDATE `mc_gmap_config`
						SET config_value = CASE config_id
							WHEN 'api_key' THEN :api_key
							WHEN 'markerColor' THEN :markerColor
						END
						WHERE config_id IN ('api_key','markerColor')";
				break;
			case 'img':
				$query = 'UPDATE mc_gmap_address
						SET 
							img_address = :img
						WHERE id_address = :id';
				break;
			default:
				return false;
		}

		try {
			component_routing_db::layer()->update($query,$params);
			return true;
		}
		catch (Exception $e) {
			if(!isset($this->logger)) $this->logger = new debug_logger(MP_LOG_DIR);
			$this->logger->log('statement','db',$e->getMessage(),$this->logger::LOG_MONTH);
			return false;
		}
    }

	/**
	 * @param string $type
	 * @param array $params
	 * @return bool
	 */
	protected function delete(string $type, array $params = []): bool {
		switch ($type) {
			case 'address':
				$query = 'DELETE FROM mc_gmap_address
						WHERE id_address = :id';
				break;
			default:
				return false;
		}

		try {
			component_routing_db::layer()->delete($query,$params);
			return true;
		}
		catch (Exception $e) {
			if(!isset($this->logger)) $this->logger = new debug_logger(MP_LOG_DIR);
			$this->logger->log('statement','db',$e->getMessage(),$this->logger::LOG_MONTH);
			return false;
		}
	}
}