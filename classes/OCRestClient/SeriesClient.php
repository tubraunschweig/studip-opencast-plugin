<?php

	require_once 'app/controllers/studip_controller.php';
	require_once 'lib/log_events.inc.php';
    
    require_once "OCRestClient.php";
    require_once dirname(__FILE__). '/../../models/OCModel.php';
    require_once dirname(__FILE__). '/../../models/OCSeriesModel.php';

    class SeriesClient extends OCRestClient
    {
        static $me;
        public $serviceName = 'Series';
        function __construct() {
            try {
                if ($config = parent::getConfig('series')) {
                    parent::__construct($config['service_url'],
                                        $config['service_user'],
                                        $config['service_password']);
                } else {
                    throw new Exception (_("Die Konfiguration wurde nicht korrekt angegeben"));
                }
            } catch(Exception $e) {

            }
        }

        /**
         *  getAllSeries() - retrieves all series from connected Opencast-Matterhorn Core
         *
         *  @return array response all series
         */
        function getAllSeries() {

            $cache = StudipCacheFactory::getCache();
            $cache_key = 'oc_allseries';
            $all_series = $cache->read($cache_key);

            $count = 100;

            if($all_series === false) {
                $service_url = "/series.json?count=".$count;

                if($series = $this->getJSON($service_url)){
                    $catalog = $series->catalogs;
                    $offset = intval(ceil($series->totalCount / $count));
                    for($i = 1; $i < $offset; $i++) {
                        $additional_series = $this->getSeriesOffset($count, $i);
                        if($additional_series){
                            $catalog = array_merge($catalog,$additional_series);
                        }
                    }
                    $cache->write($cache_key, serialize($catalog), 7200);
                    return $catalog;
                } else return false;
            } else return unserialize($all_series);
        }

        /**
         *  getAllSeries() - retrieves all series for a given offset from connected Opencast-Matterhorn Core
         *
         *  @param int count maximal number of series that should be returned
         *  @param int startpage offset
         *
         *  @return array response all series for given offset
         */
         function getSeriesOffset($count, $startpage) {
            $service_url = "/series.json?count=".$count."&startPage=".$startpage;

            if($series = $this->getJSON($service_url)){
                return $series->catalogs;
            } else return false;
        }
        
        // todo
        function getOneSeries($seriesID)
        {
                return $this->getJSON('/'.$seriesID. '.json');
        }

        /**
         *  getSeries() - retrieves seriesmetadata for a given series identifier from conntected Opencast-Matterhorn Core
         *
         *  @param string series_id Identifier for a Series
         *
         *	@return array response of a series
         */
        function getSeries($series_id) {

            $service_url = "/".$series_id.".json";
            if($series = $this->getJSON($service_url)){
                return $series;
            } else return false;
        }

        /**
         *  getSeriesDublinCore() - retrieves DC Representation for a given series identifier from conntected Opencast-Matterhorn Core
         *
         *  @param string series_id Identifier for a Series
         *
         *  @return string DC representation of a series
         */
        function getSeriesDublinCore($series_id) {

            $service_url = "/".$series_id."/dublincore";
            if($seriesDC = $this->getXML($service_url)){
                // dublincore representation is returned in XML
                //$seriesDC = simplexml_load_string($seriesDC);
                return $seriesDC;

            } else return false;
        }


        /**
         * createSeriesForSeminar - creates an new Series for a given course in OC Matterhorn
         * @param string $course_id  - course identifier
         * @return bool success or not
         */
        function createSeriesForSeminar($course_id) {
                
            //*** Patch "Doppelte Serien in OC verhindern" Beginn   ***//
            
            // Alle Serien aus der Opencast lesen
            $DBSeries = OCSeriesModel::getAllSeries();
			// Namen der Veranstaltung aus der Stud.IP-Datenbank lesen 
			$stmt = DBManager::get()->prepare("SELECT Name FROM `seminare` WHERE Seminar_id = ?");
            $res = $stmt->execute(array($course_id));
            $output = $stmt->fetch(PDO::FETCH_ASSOC);
            $name = utf8_encode($output['Name']);
     
            // Pr�fen, ob der Name der Veranstaltung in Opencast vorhanden ist
            $series_exists_boolean = false;
            foreach ($DBSeries as $key=>$value) {
				$series_exists_boolean = (($DBSeries[$key]['title']!="" && $name!="" ? strpos($DBSeries[$key]['title'], $name)===0 : false) || $series_exists_boolean);
			}
			unset($value);  
         
            /* $conf = OCRestClient::getConfig('apisecurity');
			$sc = new OCRestClient ( $conf['service_host'], $conf['service_user'], $conf['service_password']);
            $service_url = "/api/series/?filter=title:".rawurlencode($name);
            echo $service_url; echo "<br>";
			$json_array = $sc->getJSON($service_url);
			$series_exists_boolean = (empty($json_array) == false ? true : false);
			//echo $series_exists_boolean;
			//echo '<pre>'.print_r($json_array,1).'</pre>'; */
			
			$series_exists_status = "status_unknown";
            
            if (!$series_exists_boolean) {
					            
	            $dublinCore = utf8_encode(OCSeriesModel::createSeriesDC($course_id));
	            
	            
	            $ACLData = array(
	                'ROLE_ADMIN' => array(
	                    'read' => 'true',
                        'write' => 'true',
                        'analyze' => 'true'),
                    'ROLE_GROUP_GUI_USER' => array(
                        'read' => 'true',
                        'write' => 'true'),
                    'ROLE_ANONYMOUS' => array(
                        'read' => 'true'
                    )
                );
	                        
	            $ACL = OCSeriesModel::createSeriesACL($ACLData); 
	            $post = array('series' => $dublinCore,
	                        'acl' => $ACL);
	
	            $res = $this->getXML('/', $post, false, true);
	    
	            $string = str_replace('dcterms:', '', $res[0]);
	            $xml = simplexml_load_string($string);
	            $json = json_decode(json_encode($xml), true);
	
	            if ($res[1] == 201){
	
	                $new_series = json_decode($res[0]);
	                $series_id = $json['identifier'];
	                OCSeriesModel::setSeriesforCourse($course_id, $series_id, 'visible', 1, time());
	                
	                self::updateAccescontrolForSeminar($series_id, $ACL);
					
					return "new_series_created";
	            } else {
	                return "no_connection_to_series_service";
	            }     
            } else {
 				return "series_exists";
			}
			          
        }
        // Das Ergebnis ist "new_series_created", "series_exists" oder "no_connection_to_series_service"
        
        /**
         * updateAccescontrolForSeminar - updates the ACL for a given series in OC Matterhorn
         * @param string $series_id  - series identifier
         * @param array  $acl_data   -utf8_encoded ACL
         * @return bool success or not
         */
        
        function updateAccescontrolForSeminar($series_id, $acl_data) {
            
            $post =  array('acl' => $acl_data);
            $res = $this->getXML('/'.$series_id.'/accesscontrol', $post, false, true);

            if ($res[1] == 204){
                return true;
            } else {
                return false;
            }
        }
        

        /**
         *  removeSeries() - removes a series for a given identifier from the Opencast-Matterhorn Core
         *
         *  @param string series_id Identifier for a Series
         *
         *  @return success either true or false
         */
        function removeSeries($series_id) {

            $service_url = "/".$series_id;
            curl_setopt($this->ochandler,CURLOPT_URL,$this->matterhorn_base_url.$service_url);
            curl_setopt($this->ochandler, CURLOPT_CUSTOMREQUEST, "DELETE");
            //TODO über REST Classe laufen lassen, getXML, getJSON...
            $response = curl_exec($this->ochandler);
            $httpCode = curl_getinfo($this->ochandler, CURLINFO_HTTP_CODE);
            if($httpCode == 204){
                return true;
            } else return false;
        }


        // static functions...
        static function storeAllSeries($series_id) {
            $stmt = DBManager::get()->prepare("SELECT * FROM `oc_series` WHERE series_id = ?");
            $stmt->execute(array($series_id));
            if(!$stmt->fetch()) {
                $stmt = DBManager::get()->prepare("REPLACE INTO
                    oc_series (series_id)
                    VALUES (?)");
                return $stmt->execute(array($series_id));
            }
            else return false;
        }
    }
?>
