<?php

class RciamStatsViewerUtils {

    public static function getLoginCountPerDay($conn,$days)
    {
        /*
         $remote_db = array( 
        'className' => 'Cake\Database\Connection',
        'driver' => 'Cake\Database\Driver\Postgres,
        'persistent' => false,
        'host' => '83.212.76.100',
        'port' => '5432',
        'username' => 'egi_dev_proxy_admin',
        'password' => '?wCy=sr*3r^H+QkG',
        'database' => 'egi_dev_proxy',
        'encoding' => 'utf8',
        'timezone' => 'UTC',
        'flags' => [],
        'cacheMetadata' => false,
        'log' => false,
        'quoteIdentifiers' => false,
        'url' => env('DATABASE_URL', null),
         );
            $conn=ConnectionManager::create('remote_db',$remote_db);
         */

        assert($conn != NULL);
        
        $dbDriver = 'pgsql';
        $table_name = "statistics";
        //$table_name = $conn->applyPrefix($databaseConnector->getStatisticsTableName());
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
        //$query->execute();
        //$result = $query->fetchAll(); #Here is the result
        //var_dump($result);
        //foreach ($result as $record){
            
           // echo $record[0]["year"];
           // echo "[new Date(".$record["year"].",". ($record["month"] - 1 ). ", ".$record["day"]."), {v:".$record["count"]."}],";
       // }

       // while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        //    echo "[new Date(".$row["year"].",". ($row["month"] - 1 ). ", ".$row["day"]."), {v:".$row["count"]."}],";
       // }

       return $result;
    }
}