<?php 

App::import('Model', 'ConnectionManager');
App::uses('Security', 'Utility');
App::uses('Hash', 'Utility');

class RciamStatsViewer extends AppModel
{
    // Required by COmanage Plugins
    public $cmPluginType= 'other';

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

        Configure::write('Security.useOpenSsl', true);
        $data["RciamStatsViewer"]["password"] = Security::decrypt(base64_decode($data["RciamStatsViewer"]["password"]),Configure::read('Security.salt'));
        return $data;
    }

    
    public function beforeSave($options = array()){
        if(isset($this->data['RciamStatsViewer']['password'])){
            $key = Configure::read('Security.salt');
            Configure::write('Security.useOpenSsl', true);
            $password = base64_encode(Security::encrypt($this->data['RciamStatsViewer']['password'],$key));
            $this->data['RciamStatsViewer']['password'] = $password;
            var_dump($password);
        }
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
        'port' => array(
            'rule' => 'notBlank',
            'required' => false,
            'allowEmpty' => true
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
        'db_prefix' => array(
            'rule' => 'notBlank',
            'required' => false,
            'allowEmpty' => true
        ),
        'persistent' => array(
            'rule' => 'boolean',
            'required' => true,
            'allowEmpty' => false
        ),
        'encoding' => array(
            'rule' => array(
                'inList',
                array(
                  RciamStatsViewerDBEncodingTypeEnum::utf_8,
                  RciamStatsViewerDBEncodingTypeEnum::iso_8859_7,
                  RciamStatsViewerDBEncodingTypeEnum::latin1,
                  RciamStatsViewerDBEncodingTypeEnum::latin2,
                  RciamStatsViewerDBEncodingTypeEnum::latin3,
                  RciamStatsViewerDBEncodingTypeEnum::latin4
                )
              ),
            'required' => true,
            'allowEmpty' => false
        ),
        'stats_type'=>array(
            'rule' => array(
                'inList', 
                array(
                    RciamStatsViewerStatsTypeEnum::Quantitative,
                    RciamStatsViewerStatsTypeEnum::Qualitative
                )
                 
            ),
            'required' => true,
            'message' => 'A valid type must be selected'
        )
    );

  /**
   * Establish a connection (via Cake's ConnectionManager) to the specified SQL server.
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
   
    Configure::write('Security.useOpenSsl', true);
    $dbconfig = array(
      'datasource' => 'Database/' . $dbmap[ $rciamstatsviewer['RciamStatsViewer']['type'] ],
      'persistent' => $rciamstatsviewer['RciamStatsViewer']['persistent'],
      'host'       => $rciamstatsviewer['RciamStatsViewer']['hostname'],
      'login'      => $rciamstatsviewer['RciamStatsViewer']['username'],
      'password'   => Security::decrypt(base64_decode($rciamstatsviewer['RciamStatsViewer']['password']),Configure::read('Security.salt')),
      'database'   => $rciamstatsviewer['RciamStatsViewer']['databas'],
      'prefix'     => $rciamstatsviewer['RciamStatsViewer']['db_prefix'],
      'encoding'   => $rciamstatsviewer['RciamStatsViewer']['encoding'],
    );

    // Port Value
    if (!isset($rciamstatsviewer['RciamStatsViewer']['port']) || $rciamstatsviewer['RciamStatsViewer']['port']==""){
        if($dbconfig['datasource'] == "Mysql")
            $dbconfig['port'] = "3306";
        else if($dbconfig['datasource'] == "Postgres")
            $dbconfig['port'] = "5432";
    }
    
    $datasource = ConnectionManager::create('connection_'.$coId, $dbconfig);
    
    return $datasource;
  }
}