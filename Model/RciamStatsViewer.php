<?php 

App::import('Model', 'ConnectionManager');

class RciamStatsViewer extends AppModel
{
    // Required by COmanage Plugins
    public $cmPluginType= 'other';

    // Association rules from this model to other models
//    public $belongsTo = array("Server");

     // Default display field for cake generated views
    public $displayField = 'name';

    /**
     * Expose menu items.
     *
     * @ since COmanage Registry v2.0.0
     * @ return Array with menu location type as key and array of labels, controllers, actions as values.
     */

    public function cmPluginMenus() {
        $this->log(__METHOD__ . '::@', LOG_DEBUG);
        return array(
        'coconfig' => array(_txt('ct.rciam_stats_viewers.1') =>
            array('controller' => 'rciam_stats_viewers',
                  'action'     => 'edit')),
        'copeople' => array(_txt('ct.rciam_stats_viewer_services.pl') =>
            array('controller' => "rciam_stats_viewer_services",
                  'action' => 'index'))          
        );
    }

    /**
     * @param Integer $co_id
     * @return array|null
     */

    public function getConfiguration($co_id) {
        // Get all the config data. Even the EOFs that i have now deleted
        $args = array();
        $args['conditions']['RciamStatsViewer.co_id'] = $co_id;
        
        $data = $this->find('first', $args);
        // There is no configuration available for the plugin. Abort
        if(empty($data)) {
            return null;
        }
        
        return $data;
    }

    public $validate = array(
        'co_id'=> array(
            'rule' => 'numeric',
            'required' => true,
            'message' => 'A CO ID must be provided',
        ),
        'type' => array(
            'rule' => array(
              'inList',
              array(
                RciamStatsViewerDBDriverTypeEnum::Mysql,
                RciamStatsViewerDBDriverTypeEnum::Postgres 
              )
            ),
            'required' => true
        ),
        'hostname' => array(
            'rule' => 'notBlank',
            'required' => true,
            'allowEmpty' => false
        ),
        'username' => array(
            'rule' => 'notBlank',
            'required' => false,
            'allowEmpty' => true
        ),
        'password' => array(
            'rule' => 'notBlank',
            'required' => false,
            'allowEmpty' => true
        ),
        // 'database' is a MySQL reserved keyword
        'databas' => array(
            'rule' => 'notBlank',
            'required' => false,
            'allowEmpty' => true
        ),
        'stats_type'=>array(
            'rule' => array(
                'inList',
                array(
                    "QN",
                    "QL"
                ),
            ),
            'required' => true,
            'message' => 'A valid type must be selected'
        )
    );

  /**
   * Establish a connection (via Cake's ConnectionManager) to the specified SQL server.
   *
   * @since  COmanage Registry v3.2.0
   * @param  Integer $serverId Server ID (NOT RciamStatsViewerId)
   * @param  String  $name     Connection name, used for subsequent access via Models
   * @return Boolean true on success
   * @throws Exception
   */
  
  public function connect($coId) {
    // Get our connection information
    $args = array();
    $args['conditions']['RciamStatsViewer.co_id'] = $coId;
    $args['contain'] = false;
    
    $rciamstatsviewer = $this->find('first', $args);
   
    if(empty($rciamstatsviewer)) {
      throw new InvalidArgumentException(_txt('er.notfound', array(_txt('ct.rciam_stats_viewers.1'), $coId)));
    }
  

    $dbmap = array(
      RciamStatsViewerDBDriverTypeEnum::Mysql     => 'Mysql',
      RciamStatsViewerDBDriverTypeEnum::Postgres  => 'Postgres'
    );
   

    $dbconfig = array(
      'datasource' => 'Database/' . $dbmap[ $rciamstatsviewer['RciamStatsViewer']['type'] ],
      'persistent' => false,
      'host' => $rciamstatsviewer['RciamStatsViewer']['hostname'],
      'login' => $rciamstatsviewer['RciamStatsViewer']['username'],
      'password' => $rciamstatsviewer['RciamStatsViewer']['password'],
      'database' => $rciamstatsviewer['RciamStatsViewer']['databas'],
//    'prefix' => '',
//    'encoding' => 'utf8',
    );
    
    $datasource = ConnectionManager::create('connection_'.$coId, $dbconfig);
    //var_dump($datasource);
    //$conn = ConnectionManager::get(); #Remote D
    //var_dump($conn);
    return $datasource;
  }
}