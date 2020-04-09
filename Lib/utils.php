<?php

class RciamStatsViewerUtils {

    private $configData;

    public function __construct($configData) {
       
        $this->configData = array();
        $this->configData = $configData;
       //var_dump($this->configData["RciamStatsViewer"]["statisticsTableName"]);
    }
    
    public function getLoginCountPerDay($conn,$days)
    {
        
        assert($conn != NULL);
       //var_dump($this);
        $dbDriver = 'pgsql';
        $table_name = $this->configData["RciamStatsViewer"]["statisticsTableName"];
        
        if($days == 0) {    // 0 = all time
            if ($dbDriver == 'pgsql') {
                $sql = "SELECT year, month, day, SUM(count) AS count FROM $table_name WHERE service != '' GROUP BY year, month, day ORDER BY year DESC,month DESC,day DESC";
            } else {
                $sql = "SELECT year, month, day, SUM(count) AS count FROM $table_name WHERE service != '' GROUP BY year DESC,month DESC,day DESC";
            }
           // $query = $conn->prepare($sql);
      
        } else {
            if ($dbDriver == 'pgsql') {
                $sql = "SELECT year, month, day, SUM(count) AS count FROM $table_name WHERE service != '' AND CAST(CONCAT(year,'-',LPAD(CAST(month AS varchar),2,'0'),'-',LPAD(CAST(day AS varchar),2,'0')) AS date) > current_date - INTERVAL '1 days' * :days GROUP BY year, month, day ORDER BY year DESC,month DESC,day DESC";
            } else {
                $sql = "SELECT year, month, day, SUM(count) AS count FROM $table_name WHERE service != '' AND CONCAT(year,'-',LPAD(month,2,'00'),'-',LPAD(day,2,'00')) BETWEEN CURDATE() - INTERVAL :days DAY AND CURDATE() GROUP BY year DESC,month DESC,day DESC";
            }
            $queryParams = array(
                'days' => array($days, PDO::PARAM_INT),
            );
           // $query = $conn->prepare($sql);
        }
        $result = $conn->query($sql);
        
       return $result;
    }

    public function getLoginCountPerIdp($conn, $days)
    {
        assert($conn != NULL);

        $dbDriver = 'pgsql';
        $tableName =  $this->configData["RciamStatsViewer"]["statisticsTableName"];
        $identityProvidersMapTableName =  $this->configData["RciamStatsViewer"]["identityProvidersMapTableName"];

        if($days == 0) {    // 0 = all time
            if ($dbDriver == 'pgsql') {
                $sql = "SELECT sourceidp, COALESCE(name,sourceIdp) AS idpname, SUM(count) AS count FROM $tableName LEFT OUTER JOIN $identityProvidersMapTableName ON sourceidp = entityId GROUP BY sourceidp, name HAVING sourceidp != '' ORDER BY count DESC";
            } else {
                $sql = "SELECT sourceidp, IFNULL(name,sourceIdp) AS idpname, SUM(count) AS count FROM $tableName LEFT OUTER JOIN $identityProvidersMapTableName ON sourceidp = entityId GROUP BY sourceidp HAVING sourceidp != '' ORDER BY count DESC";
            }
            //$stmt = $conn->read($query);
        } else {
            if ($dbDriver == 'pgsql') {
                $sql = "SELECT year, month, day, sourceidp, COALESCE(name,sourceIdp) AS idpname, SUM(count) AS count FROM $tableName LEFT OUTER JOIN $identityProvidersMapTableName ON sourceidp = entityId WHERE CAST(CONCAT(year,'-',LPAD(CAST(month AS varchar),2,'0'),'-',LPAD(CAST(day AS varchar),2,'0')) AS date) > current_date - INTERVAL '1 days' * :days GROUP BY sourceidp, name, year, month, day HAVING sourceidp != '' ORDER BY count DESC";
            } else {
                $sql = "SELECT year, month, day, sourceidp, IFNULL(name,sourceIdp) AS idpname, SUM(count) AS count FROM $tableName LEFT OUTER JOIN $identityProvidersMapTableName ON sourceidp = entityId WHERE CONCAT(year,'-',LPAD(month,2,'00'),'-',LPAD(day,2,'00')) BETWEEN CURDATE() - INTERVAL :days DAY AND CURDATE() GROUP BY sourceidp HAVING sourceidp != '' ORDER BY count DESC";
            }
            $queryParams = array(
                'days' => array($days, PDO::PARAM_INT),
            );
            //$stmt = $conn->read($query, $queryParams);
        }
        $result = $conn->query($sql);
       
        return $result;
    }
    public function getLoginCountPerSp($conn,$days)
    {    
        assert($conn != NULL);
        $table_name =  $this->configData["RciamStatsViewer"]["statisticsTableName"];
        $serviceProvidersMapTableName =  $this->configData["RciamStatsViewer"]["serviceProvidersMapTableName"];
        $dbDriver = 'pgsql';
        if($days == 0) {    // 0 = all time
            if ($dbDriver == 'pgsql') {
                $sql = "SELECT service, COALESCE(name,service) AS spname, SUM(count) AS count FROM $table_name LEFT OUTER JOIN $serviceProvidersMapTableName ON service = identifier GROUP BY service, name HAVING service != ''  ORDER BY count DESC";
            } else {
                $sql = "SELECT service, IFNULL(name,service) AS spname, SUM(count) AS count FROM $table_name LEFT OUTER JOIN " . $serviceProvidersMapTableName . " ON service = identifier GROUP BY service HAVING service != ''  ORDER BY count DESC";
            }
            //$stmt = $conn->read($query);
        } else {
            if ($dbDriver == 'pgsql') {
                $sql = "SELECT year, month, day, service, COALESCE(name,service) AS spname, SUM(count) AS count FROM $table_name LEFT OUTER JOIN $serviceProvidersMapTableName ON service = identifier WHERE CAST(CONCAT(year,'-',LPAD(CAST(month AS varchar),2,'0'),'-',LPAD(CAST(day AS varchar),2,'0')) AS date) > current_date - INTERVAL '1 days' * :days GROUP BY service, name, year, month, day HAVING service != ''  ORDER BY count DESC";
            } else {
                $sql = "SELECT year, month, day, service, IFNULL(name,service) AS spname, SUM(count) AS count FROM $table_name LEFT OUTER JOIN $serviceProvidersMapTableName ON service = identifier WHERE CONCAT(year,'-',LPAD(month,2,'00'),'-',LPAD(day,2,'00')) BETWEEN CURDATE() - INTERVAL :days DAY AND CURDATE() GROUP BY service HAVING service != ''  ORDER BY count DESC";
            }
            $queryParams = array(
                'days' => array($days, PDO::PARAM_INT),
            );
           // $stmt = $conn->read($query, $queryParams);
        }

        $result = $conn->query($sql);

        return $result;
       // while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            //echo "['<a href=spDetail.php?identifier=" .$row["service"] . "> " . str_replace("'", "\'", $row["spName"]) . "</a>', " . $row["count"] . "],";
         //   echo "['" . str_replace("'", "\'", $row["spname"]) . "', '". $row["service"] . "', " .  $row["count"] . "],";
        //}
    }
}