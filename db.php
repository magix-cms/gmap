<?php

/**
 * Class plugins_gmap_db
 */
class plugins_gmap_db
{
	/**
	 * @param $config
	 * @param bool $params
	 * @return mixed|null
	 */
    public function fetchData($config, $params = array())
	{
        $sql = '';

        if(is_array($config)) {
            if($config['context'] === 'all') {
            	switch ($config['type']) {
					case 'pages':
						$sql = 'SELECT h.*,c.*
                    			FROM mc_gmap AS h
                    			JOIN mc_gmap_content AS c USING(id_gmap)
                    			JOIN mc_lang AS lang ON(c.id_lang = lang.id_lang)';
						break;
					case 'address':
						$sql = 'SELECT a.*,c.*
                    			FROM mc_gmap_address AS a
                    			JOIN mc_gmap_address_content AS c USING(id_address)
                    			JOIN mc_lang AS lang ON(c.id_lang = lang.id_lang)
                    			WHERE c.id_lang = :default_lang';
						break;
					case 'addressContent':
						$sql = 'SELECT a.*,c.*
                    			FROM mc_gmap_address AS a
                    			JOIN mc_gmap_address_content AS c USING(id_address)
                    			JOIN mc_lang AS lang ON(c.id_lang = lang.id_lang)
                    			WHERE c.id_address = :id';
						break;
					case 'addresses':
						$sql = "SELECT a.*,c.*
                    			FROM mc_gmap_address AS a
                    			JOIN mc_gmap_address_content AS c USING(id_address)
                    			JOIN mc_lang AS l USING(id_lang) 
								WHERE iso_lang = :lang
                    			AND c.published_address = 1";
						break;
					case 'config':
						$sql = "SELECT * FROM mc_gmap_config";
						break;
				}

                return $sql ? component_routing_db::layer()->fetchAll($sql,$params) : null;
            }
            elseif($config['context'] === 'one') {
				switch ($config['type']) {
					case 'root':
						$sql = 'SELECT * FROM mc_gmap ORDER BY id_gmap DESC LIMIT 0,1';
						break;
					case 'content':
						$sql = 'SELECT * FROM mc_gmap_content WHERE id_gmap = :id AND id_lang = :id_lang';
						break;
					case 'page':
						$sql = 'SELECT *
								FROM mc_gmap as g
								JOIN mc_gmap_content as gc USING(id_gmap)
								JOIN mc_lang as l USING(id_lang)
								WHERE iso_lang = :lang
								LIMIT 0,1';
						break;
					case 'markerColor':
						$sql = "SELECT config_value as markerColor FROM mc_gmap_config WHERE config_id = 'markerColor'";
						break;
					case 'addressContent':
						$sql = 'SELECT * FROM mc_gmap_address_content WHERE id_address = :id AND id_lang = :id_lang';
						break;
					case 'lastAddress':
						$sql = 'SELECT * FROM mc_gmap_address ORDER BY id_address DESC LIMIT 0,1';
						break;
				}

                return $sql ? component_routing_db::layer()->fetch($sql,$params) : null;
            }
        }
    }

    /**
     * @param $config
     * @param array $params
     */
    public function insert($config, $params = array())
    {
        if (is_array($config)) {
			$sql = '';

			switch ($config['type']) {
				case 'root':
					$sql = 'INSERT INTO mc_gmap(date_register) VALUES (NOW())';
					break;
				case 'content':
					$sql = 'INSERT INTO mc_gmap_content(id_gmap, id_lang, name_gmap, content_gmap, published_gmap) 
				  			VALUES (:id, :id_lang, :name_gmap, :content_gmap, :published_gmap)';
					break;
				case 'address':
					$sql = 'INSERT INTO mc_gmap_address(order_address, date_register) 
				  			SELECT COUNT(id_address), NOW() FROM mc_gmap_address';
					break;
				case 'addressContent':
					$sql = 'INSERT INTO mc_gmap_address_content(id_address, id_lang, company_address, content_address, address_address, postcode_address, country_address, city_address, phone_address, mobile_address, fax_address, email_address, lat_address, lng_address, link_address, blank_address, last_update, published_address)
							VALUES (:id_address, :id_lang, :company_address, :content_address, :address_address, :postcode_address, :country_address, :city_address, :phone_address, :mobile_address, :fax_address, :email_address, :lat_address, :lng_address, :link_address, :blank_address, NOW(), :published_address)';
					break;
			}

			if ($sql !== '') component_routing_db::layer()->insert($sql,$params);
        }
    }
    /**
     * @param $config
     * @param array $params
     */
    public function update($config, $params = array())
    {
        if (is_array($config)) {
			$sql = '';

			switch ($config['type']) {
				case 'content':
					$sql = 'UPDATE mc_gmap_content 
							SET 
								name_gmap = :name_gmap,
							 	content_gmap = :content_gmap,
							  	published_gmap = :published_gmap
                			WHERE id_gmap = :id 
                			AND id_lang = :id_lang';
					break;
				case 'addressContent':
					$sql = 'UPDATE mc_gmap_address_content
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
					$sql = "UPDATE `mc_gmap_config`
							SET config_value = CASE config_id
								WHEN 'api_key' THEN :api_key
								WHEN 'markerColor' THEN :markerColor
							END
							WHERE config_id IN ('api_key','markerColor')";
					break;
				case 'img':
					$sql = 'UPDATE mc_gmap_address
							SET 
								img_address = :img
							WHERE id_address = :id';
					break;
			}

			if ($sql !== '') component_routing_db::layer()->update($sql,$params);
        }
    }

	/**
	 * Delete a record or more
	 * @param array $config
	 * @param array $params
	 */
	protected function delete($config, $params = array()) {
		if(is_array($config)){
			$sql = '';

			switch ($config['type']) {
				case 'address':
					$sql = 'DELETE FROM mc_gmap_address
							WHERE id_address = :id';
					break;
			}

			if ($sql !== '') component_routing_db::layer()->delete($sql,$params);
		}
	}
}