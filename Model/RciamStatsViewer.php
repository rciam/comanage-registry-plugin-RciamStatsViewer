<?php

App::import('Model', 'ConnectionManager');
App::uses('Security', 'Utility');
App::uses('Hash', 'Utility');

class RciamStatsViewer extends AppModel
{
    // Required by COmanage Plugins
    public $cmPluginType = 'other';

    // Default display field for cake generated views
    public $displayField = 'name';

    // Add behaviors
    public $actsAs = array('Containable',
                           'Changelog' => array('priority' => 5));

    /**
     * Expose menu items.
     *
     * @ since COmanage Registry v2.0.0
     * @ return Array with menu location type as key and array of labels, controllers, actions as values.
     */

    public function cmPluginMenus()
    {
        $this->log(__METHOD__ . '::@', LOG_DEBUG);
        return array(
            'coconfig' => array(_txt('ct.rciam_stats_viewers.1') =>
            array(
                'controller' => 'rciam_stats_viewers',
                'action'     => 'edit'
            )),
            'copeople' => array(_txt('ct.rciam_stats_viewer_services.pl') =>
            array(
                'controller' => "rciam_stats_viewer_services",
                'action' => 'index'
            ))
        );
    }

    /**
     * @param Integer $co_id
     * @return array|null
     */

    public function getConfiguration($co_id)
    {

        // Get all the config data. Even the EOFs that i have now deleted
        $args = array();
        $args['conditions']['RciamStatsViewer.co_id'] = $co_id;

        $data = $this->find('first', $args);
        // There is no configuration available for the plugin. Abort
        if (empty($data)) {
            return null;
        }

        Configure::write('Security.useOpenSsl', true);
        $data["RciamStatsViewer"]["password"] = Security::decrypt(base64_decode($data["RciamStatsViewer"]["password"]), Configure::read('Security.salt'));
        return $data;
    }


  /**
   * Actions to take before a save operation is executed.
   *
   * @since  COmanage Registry v3.1.0
   */

    public function beforeSave($options = array())
    {
        if (isset($this->data['RciamStatsViewer']['password'])) {
            $key = Configure::read('Security.salt');
            Configure::write('Security.useOpenSsl', true);
            $password = base64_encode(Security::encrypt($this->data['RciamStatsViewer']['password'], $key));
            $this->data['RciamStatsViewer']['password'] = $password;
        }
    }

    // Validation rules for table elements
    public $validate = array(
        'co_id' => array(
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
          'numeric' => array(
            'rule' => 'naturalNumber',
            'message' => 'Please provide the number of DB port',
            'required' => false,
            'allowEmpty' => true,
            'last' => 'true',
          ),
          'valid_range' => array(
            'rule' => array('range', 1024, 65535),
            'message' => 'Port must be between 1024-65535',
            'required' => false,
            'allowEmpty' => true,
            'last' => 'true',
          ),
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
    );

    /**
     * Establish a connection (via Cake's ConnectionManager) to the specified SQL server.
     * @param $coId
     * @param array $dbconfig
     * @return DataSource|null
     * @throws InvalidArgumentException   Plugins Configuration is not valid
     * @throws MissingConnectionException The database connection failed
     */
    public function connect($coId, $dbconfig=array())
    {
        // Get our connection information
        $args = array();
        $args['conditions']['RciamStatsViewer.co_id'] = $coId;
        $args['contain'] = false;

        $rciamstatsviewer = $this->find('first', $args);

        if (empty($rciamstatsviewer)
            && empty($dbconfig)) {
            throw new InvalidArgumentException(_txt('er.notfound', array(_txt('ct.rciam_stats_viewers.1'), $coId)));
        }

        Configure::write('Security.useOpenSsl', true);
        if(empty($dbconfig)) {
          $dbconfig = array(
            'datasource' => 'Database/' . RciamStatsViewerDBDriverTypeEnum::type[$rciamstatsviewer['RciamStatsViewer']['type']],
            'persistent' => $rciamstatsviewer['RciamStatsViewer']['persistent'],
            'host'       => $rciamstatsviewer['RciamStatsViewer']['hostname'],
            'login'      => $rciamstatsviewer['RciamStatsViewer']['username'],
            'password'   => Security::decrypt(base64_decode($rciamstatsviewer['RciamStatsViewer']['password']), Configure::read('Security.salt')),
            'database'   => $rciamstatsviewer['RciamStatsViewer']['databas'],
            'encoding'   => $rciamstatsviewer['RciamStatsViewer']['encoding'],
            'port'       => $rciamstatsviewer['RciamStatsViewer']['port'],
          );
        }

        // Port Value
        if (empty($dbconfig['port'])) {
            if ($dbconfig['datasource'] === 'Database/Mysql') {
              $dbconfig['port'] = RciamStatsViewerDBPortsEnum::Mysql;
            } else if ($dbconfig['datasource'] === 'Database/Postgres') {
              $dbconfig['port'] = RciamStatsViewerDBPortsEnum::Postgres;
            }
        }

        // Database connection per CO
        $datasource = ConnectionManager::create('connection_' . $coId, $dbconfig);

        return $datasource;
    }
}
