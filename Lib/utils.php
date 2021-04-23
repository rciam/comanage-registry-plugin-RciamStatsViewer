<?php
App::uses('CakeLog', 'Log');

class RciamStatsViewerUtils
{
    private $configData;

    public function __construct($configData = array())
    {
        $this->configData = $configData;
    }
        
    /**
     * getStatisticsCountryTableName
     *
     * @return string
     */
    public function getStatisticsCountryTableName() {
        return $this->configData['RciamStatsViewer']["countryStatisticsTableName"];
    }

     /**
     * getStatisticsUserCountry
     *
     * @return string
     */
    public function getStatisticsUserCountryTableName() {
        return $this->configData['RciamStatsViewer']["userCountryStatisticsTableName"];
    }
    
    /**
     * getInformationForProvider
     *
     * @param  mixed $conn
     * @param  mixed $type
     * @param  mixed $data
     * @return object
     */
    public function getInformationForProvider($conn, $type, $data) {
        $queryParams = array();
        if($type == 'idp') {
            $table_name = $this->configData['RciamStatsViewer']['identityProvidersMapTableName'];
            $results = Hash::extract($data, '{n}.{n}.sourceidp');
            $sql = "SELECT entityid, COALESCE(name,entityid) AS idpname FROM $table_name WHERE entityid IN ('". implode("','",$results)."')";
            $providers = $this->execQuery($conn, $sql, $queryParams);
            $providers = Hash::combine($providers, '{n}.{n}.entityid', '{n}.{n}');
            foreach($data as $key => $provider){
                $data[$key][0]['idpname'] =  !empty($providers[$provider[0]['sourceidp']]['idpname']) ? $providers[$provider[0]['sourceidp']]['idpname'] : $provider[0]['sourceidp'];
            }
        }
        elseif($type == 'sp') {
            $table_name = $this->configData['RciamStatsViewer']['serviceProvidersMapTableName'];
            $results = Hash::extract($data, '{n}.{n}.service');
            $sql = "SELECT identifier, COALESCE(name,identifier) AS spname FROM $table_name WHERE identifier IN ('". implode("','",$results)."')";
            $providers = $this->execQuery($conn, $sql, $queryParams);
            $providers = Hash::combine($providers, '{n}.{n}.identifier', '{n}.{n}');
            foreach($data as $key => $provider){
                $data[$key][0]['spname'] = !empty($providers[$provider[0]['service']]['spname']) ? $providers[$provider[0]['service']]['spname'] : $provider[0]['service'];
            }
        }
        return $data;
    }

    /**
     * getTotalLoginCounts
     * 
     * @param $conn
     * @param $days
     * @param null $sp
     * @param null $idp
     * @return int
     */
    public function getTotalLoginCounts($conn, $days, $sp = NULL, $idp = NULL)
    {
        assert($conn !== NULL);
        $queryParams = array();  // Initialize
        $dbDriver = $this->configData['RciamStatsViewer']['type'];
        $table_name = $this->configData['RciamStatsViewer']['statisticsTableName'];

        if($days === 0) {    // 0 = all time
            if($dbDriver === 'PG') {
                if($sp === null && $idp === null) {
                    $sql = "SELECT SUM(count) AS count FROM $table_name WHERE service != ''";
                } else if($sp !== null) {
                    $sql = "SELECT SUM(count) AS count FROM $table_name WHERE service = '" . $sp . "'";
                } else if($idp !== null) {
                    $sql = "SELECT SUM(count) AS count FROM $table_name WHERE sourceidp = '" . $idp . "'";
                }
            } else {
                $sql = "SELECT SUM(count) AS count FROM $table_name WHERE service != ''";
            }
        } else {
            if($dbDriver === 'PG') {
                if($sp === null && $idp === null) {
                    $sql = "SELECT SUM(count) AS count FROM $table_name WHERE service != '' AND CAST(CONCAT(year,'-',LPAD(CAST(month AS varchar),2,'0'),'-',LPAD(CAST(day AS varchar),2,'0')) AS date) > current_date - INTERVAL '1 days' * ?";
                } else if($sp !== null) {
                    $sql = "SELECT SUM(count) AS count FROM $table_name WHERE service = '" . $sp . "' AND CAST(CONCAT(year,'-',LPAD(CAST(month AS varchar),2,'0'),'-',LPAD(CAST(day AS varchar),2,'0')) AS date) > current_date - INTERVAL '1 days' * ?";
                } else if($idp !== null) {
                    $sql = "SELECT SUM(count) AS count FROM $table_name WHERE sourceidp = '" . $idp . "' AND CAST(CONCAT(year,'-',LPAD(CAST(month AS varchar),2,'0'),'-',LPAD(CAST(day AS varchar),2,'0')) AS date) > current_date - INTERVAL '1 days' * ?";
                }
            } else {
                $sql = "SELECT SUM(count) AS count FROM $table_name WHERE service != '' AND CONCAT(year,'-',LPAD(month,2,'00'),'-',LPAD(day,2,'00')) BETWEEN CURDATE() - INTERVAL ? DAY AND CURDATE()";
            }
            $queryParams = array($days);
        }
        $result = $this->execQuery($conn, $sql, $queryParams);
        return !empty($result[0][0]['count']) ? $result[0][0]['count'] : 0;
    }
   
    /**
     * getLoginCountByRanges
     *
     * @param  mixed $conn
     * @param  mixed $dateFrom
     * @param  mixed $dateTo
     * @param  mixed $groupBy
     * @return void
     */
    public function getLoginCountByRanges($conn, $dateFrom = NULL, $dateTo = NULL, $groupBy = NULL)
    {
        assert($conn !== NULL);
        $queryParams = array();  // Initialize
        $dbDriver = $this->configData['RciamStatsViewer']['type'];
        $table_name = $this->configData['RciamStatsViewer']['statisticsTableName'];
        if($dbDriver === 'PG') {
            if(!empty($groupBy) && !empty(RciamStatsViewerDateTruncEnum::type[$groupBy])) {
                $trunc_by = RciamStatsViewerDateTruncEnum::type[$groupBy];
                $sql = "select sum(count) as count, date_trunc('" . $trunc_by . "', CAST(CONCAT(year,'-',LPAD(CAST(month AS varchar),2,'0'),'-',LPAD(CAST(day AS varchar),2,'0')) AS date)) as range_date, date_trunc('" . $trunc_by . "', CAST(CONCAT(year,'-',LPAD(CAST(month AS varchar),2,'0'),'-',LPAD(CAST(day AS varchar),2,'0')) AS date)) as show_date, min(CAST(CONCAT(year,'-',LPAD(CAST(month AS varchar),2,'0'),'-',LPAD(CAST(day AS varchar),2,'0')) AS date)) as min_date from $table_name where service != '' AND CAST(CONCAT(year,'-',LPAD(CAST(month AS varchar),2,'0'),'-',LPAD(CAST(day AS varchar),2,'0')) AS date)  BETWEEN '" . $dateFrom . "' AND '" . $dateTo . "' group by date_trunc('" . $trunc_by . "',CAST(CONCAT(year,'-',LPAD(CAST(month AS varchar),2,'0'),'-',LPAD(CAST(day AS varchar),2,'0')) AS date)) ORDER BY range_date ASC";
            }
            else { // initialize datatable            
                $trunc_by = RciamStatsViewerDateTruncEnum::monthly;
                $sql = "select sum(count) as count, date_trunc('" . $trunc_by . "', CAST(CONCAT(year,'-',LPAD(CAST(month AS varchar),2,'0'),'-',LPAD(CAST(day AS varchar),2,'0')) AS date)) as range_date, date_trunc('" . $trunc_by . "', CAST(CONCAT(year,'-',LPAD(CAST(month AS varchar),2,'0'),'-',LPAD(CAST(day AS varchar),2,'0')) AS date)) as show_date, min(CAST(CONCAT(year,'-',LPAD(CAST(month AS varchar),2,'0'),'-',LPAD(CAST(day AS varchar),2,'0')) AS date)) as min_date from $table_name where service != ''  group by date_trunc('" . $trunc_by . "',CAST(CONCAT(year,'-',LPAD(CAST(month AS varchar),2,'0'),'-',LPAD(CAST(day AS varchar),2,'0')) AS date)) ORDER BY range_date ASC";
            }
        } 
        return $this->execQuery($conn, $sql, $queryParams);
    }

    /**
     * getLoginCountPerIdp
     * 
     * @param $conn
     * @param $days
     * @param null $sp
     * @return mixed
     */
    public function getLoginCountPerIdp($conn, $days, $sp = null, $dateFrom = null, $dateTo = null)
    {
        assert($conn !== NULL);
        $queryParams = array();  // Initialize
        $dbDriver = $this->configData['RciamStatsViewer']['type'];
        $tableName =  $this->configData['RciamStatsViewer']['statisticsTableName'];
        $identityProvidersMapTableName =  $this->configData['RciamStatsViewer']['identityProvidersMapTableName'];
        $subQuery = '';
        if($dateFrom != null && $dateTo != null && $dateTo > $dateFrom){ //ranges in datatable
            $subQuery = " CONCAT(year,'-',LPAD(CAST(month AS varchar),2,'0'),'-',LPAD(CAST(day AS varchar),2,'0')) BETWEEN '". $dateFrom ."' AND '". $dateTo ."'";          
            if($sp === null) {
                $subQuery = " WHERE". $subQuery;
            }
            else {  
                $subQuery = " AND". $subQuery;
            }
        }
        if($days === 0) {    // 0 = all time
            if($dbDriver === 'PG') {
                if($sp === null) {
                    $sql = "SELECT sourceidp, COALESCE(name,sourceIdp) AS idpname, SUM(count) AS count FROM $tableName LEFT OUTER JOIN $identityProvidersMapTableName ON sourceidp = entityId $subQuery GROUP BY sourceidp, name HAVING sourceidp != '' ORDER BY count DESC";
                } else {
                    $sql = "SELECT sourceidp, COALESCE(name,sourceIdp) AS idpname, SUM(count) AS count FROM $tableName LEFT OUTER JOIN $identityProvidersMapTableName ON sourceidp = entityId WHERE service = '" . $sp . "' $subQuery GROUP BY sourceidp, name HAVING sourceidp != '' ORDER BY count DESC";
                }
            } else { // MYSQL
                if($sp === null) {
                    $sql = "SELECT sourceidp, IFNULL(name,sourceIdp) AS idpname, SUM(count) AS count FROM $tableName LEFT OUTER JOIN $identityProvidersMapTableName ON sourceidp = entityId GROUP BY sourceidp, name HAVING sourceidp != '' ORDER BY count DESC";
                } else {
                    $sql = "SELECT sourceidp, IFNULL(name,sourceIdp) AS idpname, SUM(count) AS count FROM $tableName LEFT OUTER JOIN $identityProvidersMapTableName ON sourceidp = entityId WHERE service = '" . $sp . "' GROUP BY sourceidp, name HAVING sourceidp != '' ORDER BY count DESC";
                }
            }
        } else {
            if($dbDriver === 'PG') {
                if ($sp === null) {
                    $sql = "SELECT sourceidp, COALESCE(name,sourceIdp) AS idpname, SUM(count) AS count FROM $tableName LEFT OUTER JOIN $identityProvidersMapTableName ON sourceidp = entityId WHERE CAST(CONCAT(year,'-',LPAD(CAST(month AS varchar),2,'0'),'-',LPAD(CAST(day AS varchar),2,'0')) AS date) > current_date - INTERVAL '1 days' * ? GROUP BY sourceidp, idpname HAVING sourceidp != '' ORDER BY count DESC";
                } else {
                    $sql = "SELECT sourceidp, COALESCE(name,sourceIdp) AS idpname, SUM(count) AS count FROM $tableName LEFT OUTER JOIN $identityProvidersMapTableName ON sourceidp = entityId WHERE service='" . $sp . "' AND CAST(CONCAT(year,'-',LPAD(CAST(month AS varchar),2,'0'),'-',LPAD(CAST(day AS varchar),2,'0')) AS date) > current_date - INTERVAL '1 days' * ? GROUP BY sourceidp, idpname HAVING sourceidp != '' ORDER BY count DESC";
                }
            } else { // MYSQL
                if($sp === null) {
                    $sql = "SELECT sourceidp, IFNULL(name,sourceIdp) AS idpname, SUM(count) AS count FROM $tableName LEFT OUTER JOIN $identityProvidersMapTableName ON sourceidp = entityId WHERE CONCAT(year,'-',LPAD(month,2,'00'),'-',LPAD(day,2,'00')) BETWEEN CURDATE() - INTERVAL ? DAY AND CURDATE() GROUP BY sourceidp, idpname HAVING sourceidp != '' ORDER BY count DESC";
                } else {
                    $sql = "SELECT sourceidp, IFNULL(name,sourceIdp) AS idpname, SUM(count) AS count FROM $tableName LEFT OUTER JOIN $identityProvidersMapTableName ON sourceidp = entityId WHERE service='" . $sp . "' AND CONCAT(year,'-',LPAD(month,2,'00'),'-',LPAD(day,2,'00')) BETWEEN CURDATE() - INTERVAL ? DAY AND CURDATE() GROUP BY sourceidp, idpname HAVING sourceidp != '' ORDER BY count DESC";
                }
            }
            $queryParams = array(
                $days,
            );
        }
        return $this->execQuery($conn, $sql, $queryParams);
    }

    /**
     * getLoginCountPerSp
     * 
     * @param $conn
     * @param $days
     * @param null $idp
     * @return mixed
     */
    public function getLoginCountPerSp($conn, $days, $idp = null, $dateFrom = null, $dateTo = null)
    {
        assert($conn !== NULL);
        $queryParams = array();  // Initialize
        $table_name =  $this->configData['RciamStatsViewer']['statisticsTableName'];
        $serviceProvidersMapTableName =  $this->configData['RciamStatsViewer']['serviceProvidersMapTableName'];
        $dbDriver = $this->configData['RciamStatsViewer']['type'];
        $subQuery = '';
        if($dateFrom != null && $dateTo != null && $dateTo > $dateFrom){ //ranges in datatable
            $subQuery = " CONCAT(year,'-',LPAD(CAST(month AS varchar),2,'0'),'-',LPAD(CAST(day AS varchar),2,'0')) BETWEEN '". $dateFrom ."' AND '". $dateTo ."'";          
            if($idp === null) {
                $subQuery = " WHERE". $subQuery;
            }
            else {   
                $subQuery = " AND". $subQuery;
            }
        }
        if($days === 0) { // 0 = all time
            if($dbDriver === 'PG') {
                if($idp === null) {
                    $sql = "SELECT service, COALESCE(name,service) AS spname, SUM(count) AS count FROM $table_name LEFT OUTER JOIN $serviceProvidersMapTableName ON service = identifier $subQuery GROUP BY service, name HAVING service != ''  ORDER BY count DESC";
                } else {
                    $sql = "SELECT service, COALESCE(name,service) AS spname, SUM(count) AS count FROM $table_name LEFT OUTER JOIN $serviceProvidersMapTableName ON service = identifier WHERE sourceidp = '" . $idp . "' $subQuery GROUP BY service, name HAVING service != ''  ORDER BY count DESC";
                }
            } else { // MYSQL
                if($idp === null) {
                    $sql = "SELECT service, IFNULL(name,service) AS spname, SUM(count) AS count FROM $table_name LEFT OUTER JOIN " . $serviceProvidersMapTableName . " ON service = identifier GROUP BY service HAVING service != ''  ORDER BY count DESC";
                } else {
                    $sql = "SELECT service, IFNULL(name,service) AS spname, SUM(count) AS count FROM $table_name LEFT OUTER JOIN " . $serviceProvidersMapTableName . " ON service = identifier WHERE sourceidp = '" . $idp . "' GROUP BY service HAVING service != ''  ORDER BY count DESC";
                }
            }
        } else {
            if ($dbDriver === 'PG') {
                if ($idp === null) {
                    $sql = "SELECT service, COALESCE(name,service) AS spname, SUM(count) AS count FROM $table_name LEFT OUTER JOIN $serviceProvidersMapTableName ON service = identifier WHERE CAST(CONCAT(year,'-',LPAD(CAST(month AS varchar),2,'0'),'-',LPAD(CAST(day AS varchar),2,'0')) AS date) > current_date - INTERVAL '1 days' * ? GROUP BY service, spname HAVING service != ''  ORDER BY count DESC";
                } else {
                    $sql = "SELECT service, COALESCE(name,service) AS spname, SUM(count) AS count FROM $table_name LEFT OUTER JOIN $serviceProvidersMapTableName ON service = identifier WHERE sourceidp = '" . $idp . "' AND CAST(CONCAT(year,'-',LPAD(CAST(month AS varchar),2,'0'),'-',LPAD(CAST(day AS varchar),2,'0')) AS date) > current_date - INTERVAL '1 days' * ? GROUP BY service, spname HAVING service != ''  ORDER BY count DESC";
                }
            } else { //MYSQL
                if ($idp === null) {
                    $sql = "SELECT service, IFNULL(name,service) AS spname, SUM(count) AS count FROM $table_name LEFT OUTER JOIN $serviceProvidersMapTableName ON service = identifier WHERE CONCAT(year,'-',LPAD(month,2,'00'),'-',LPAD(day,2,'00')) BETWEEN CURDATE() - INTERVAL :days DAY AND CURDATE() GROUP BY service, spname HAVING service != ''  ORDER BY count DESC";
                } else {
                    $sql = "SELECT service, IFNULL(name,service) AS spname, SUM(count) AS count FROM $table_name LEFT OUTER JOIN $serviceProvidersMapTableName ON service = identifier WHERE sourceidp = '" . $idp . "' AND CONCAT(year,'-',LPAD(month,2,'00'),'-',LPAD(day,2,'00')) BETWEEN CURDATE() - INTERVAL :days DAY AND CURDATE() GROUP BY service, spname HAVING service != ''  ORDER BY count DESC";
                }
            }
            $queryParams = array(
                $days
            );
        }
        return $this->execQuery($conn, $sql, $queryParams);
    }

    /**
     * getLoginCountPerDayForProvider
     * 
     * @param $conn
     * @param $days
     * @param $idpIdentifier
     * @param $type
     * @return mixed
     */
    public function getLoginCountPerDayForProvider($conn, $days, $identifier = NULL, $providerType = NULL)
    {
        $dbDriver = $this->configData['RciamStatsViewer']['type'];
        $queryParams = array();  // Initialize
        assert($conn !== NULL);
        $table_name =  $this->configData['RciamStatsViewer']['statisticsTableName'];
        if($providerType == 'idp'){
            $column = 'sourceidp = ?';
        }
        else if($providerType == 'sp'){
            $column = 'service = ?';
        }
        else if ($providerType == null){
            $column = 'service != ?';
            $identifier = '';
        }
        if ($days === 0) {    // 0 = all time
            if ($dbDriver === 'PG') {
                $sql = "SELECT year, month, day, SUM(count) AS count FROM $table_name WHERE $column GROUP BY year, month,day ORDER BY year DESC,month DESC,day DESC";
            } else {
                $sql = "SELECT year, month, day, SUM(count) AS count FROM $table_name WHERE $column GROUP BY year DESC,month DESC,day DESC";
            }
            $queryParams = array(
                $identifier
            );
        } else { // MYSQL
            if ($dbDriver === 'PG') {
                $sql = "SELECT year, month, day, SUM(count) AS count FROM $table_name WHERE $column AND CAST(CONCAT(year,'-',LPAD(CAST(month AS varchar),2,'0'),'-',LPAD(CAST(day AS varchar),2,'0')) AS date) > current_date - INTERVAL '1 days' * ? GROUP BY year, month, day ORDER BY year DESC,month DESC,day DESC";
            } else {
                $sql = "SELECT year, month, day, SUM(count) AS count FROM $table_name WHERE $column AND CONCAT(year,'-',LPAD(month,2,'00'),'-',LPAD(day,2,'00')) BETWEEN CURDATE() - INTERVAL ? DAY AND CURDATE() GROUP BY year DESC,month DESC,day DESC";
            }
            $queryParams = array(
                $identifier,
                $days
            );
        }
        return $this->execQuery($conn, $sql, $queryParams);
    }
    
    /**
     * getAccessCountPerIdpOrSp
     * 
     * @param $conn
     * @param $days
     * @param $spIdentifier
     * @return mixed
     */
    public function getAccessCountPerIdpOrSp($conn, $days, $identifier, $identifier_type)
    {
        $queryParams = array();  // Initialize
        assert($conn !== NULL);
        $table_name =  $this->configData['RciamStatsViewer']['statisticsTableName'];
        $dbDriver = $this->configData['RciamStatsViewer']['type'];
        switch($identifier_type)
        {
            case 'idp':
                $providerMapTableName = $this->configData['RciamStatsViewer']['serviceProvidersMapTableName'];
                $columnName = $dbDriver === 'PG' ? 'COALESCE(name,service) AS spname' : 'IFNULL(name,service) AS spname';
                $joinColumns = 'service = identifier';
                $groupByColumn = 'name';
                $havingClause = "service != '' AND sourceIdp = ?";

            break;
            case 'sp':
                $providerMapTableName = $this->configData['RciamStatsViewer']['identityProvidersMapTableName'];
                $columnName = $dbDriver === 'PG' ? 'COALESCE(name,sourceIdp) AS idpname' : 'IFNULL(name,sourceIdp) AS idpname';
                $joinColumns = 'sourceIdp = entityId';
                $groupByColumn = 'idpname';
                $havingClause = "sourceIdp != '' AND service = ?";
            break;
        }
        if ($days === 0) {    // 0 = all time
            if ($dbDriver === 'PG') {
                $query = "SELECT sourceIdp, service, $columnName, SUM(count) AS count FROM $table_name LEFT OUTER JOIN $providerMapTableName ON $joinColumns GROUP BY sourceIdp, service, $groupByColumn HAVING $havingClause ORDER BY count DESC";
            } else { // MYSQL
                $query = "SELECT sourceIdp, service, $columnName, SUM(count) AS count FROM $table_name LEFT OUTER JOIN $providerMapTableName ON $joinColumns GROUP BY sourceIdp, service HAVING $havingClause ORDER BY count DESC";
            }
            $queryParams = array(
                $identifier,
            );
        } else {
            if ($dbDriver === 'PG') {
                $query = "SELECT year, month, day, sourceIdp, service, $columnName, SUM(count) AS count FROM $table_name LEFT OUTER JOIN $providerMapTableName ON $joinColumns WHERE CAST(CONCAT(year,'-',LPAD(CAST(month AS varchar),2,'0'),'-',LPAD(CAST(day AS varchar),2,'0')) AS date) > current_date - INTERVAL '1 days' * ? GROUP BY sourceIdp, service, $groupByColumn, year, month, day HAVING $havingClause ORDER BY count DESC";
            } else { // MYSQL
                $query = "SELECT year, month, day, sourceIdp, service, $columnName, SUM(count) AS count FROM $table_name LEFT OUTER JOIN $providerMapTableName ON $joinColumns WHERE CONCAT(year,'-',LPAD(month,2,'00'),'-',LPAD(day,2,'00')) BETWEEN CURDATE() - INTERVAL ? DAY AND CURDATE() GROUP BY sourceIdp, service HAVING $havingClause ORDER BY count DESC";
            }
            $queryParams = array(
                $days,
                $identifier
            );
        }
        return $this->execQuery($conn, $query, $queryParams);   
    }

    /**
     * @param $connection
     * @param $sql
     * @param array $queryParams
     * @return mixed
     */
    protected function execQuery($connection, $sql, $queryParams = array())
    {
        try {
            return $connection->fetchAll($sql, $queryParams);
        } catch (PDOException $e) {
            if (Configure::read('debug')) {
                CakeLog::write('error', __METHOD__ . ':: Database Action failed. Error Message::' . '[' . $e->getCode() . ']:' . $e->getMessage());
            }
            throw new RuntimeException($e->getCode());
        }
    }
}
