<?php
App::uses('ClassRegistry', 'Utility');
App::uses('RciamStatsViewer.RciamStatsViewer', 'Model');

class AppSchema extends CakeSchema {

  public $connection = 'default';

  public function before($event = array())
  {
    // No Database cache clear will be needed afterwards
    $db = ConnectionManager::getDataSource($this->connection);
    $db->cacheSources = false;

    if (isset($event['drop'])) {
      switch ($event['drop']) {
        case 'rciam_stats_viewers':
          $RciamStatsViewer = ClassRegistry::init('RciamStatsViewer.RciamStatsViewer');
          $RciamStatsViewer->useDbConfig = $this->connection;
          $backup_file = __DIR__ . '/rciam_stats_viewers_' . date('y_m_d') . '.csv';
          if(!file_exists($backup_file)) {
            touch($backup_file);
            chmod($backup_file, 0766);
          }
          try {
            $RciamStatsViewer->query("COPY cm_rciam_stats_viewers TO '" . $backup_file . "' DELIMITER ',' CSV HEADER");
          } catch (Exception $e){
            // Ignore the Exception
          }
          break;
      }
    }

    return true;
  }

  public function after($event = array())
  {
    if (isset($event['create'])) {
      switch ($event['create']) {
        case 'rciam_stats_viewers':
          $RciamStatsViewer = ClassRegistry::init('RciamStatsViewer.RciamStatsViewer');
          $RciamStatsViewer->useDbConfig = $this->connection;
          // Add the constraints or any other initializations
          $RciamStatsViewer->query("ALTER TABLE ONLY public.cm_rciam_stats_viewers ADD CONSTRAINT cm_rciam_stats_viewers_co_id_fkey FOREIGN KEY (co_id) REFERENCES public.cm_cos(id)");
          $RciamStatsViewer->query("ALTER TABLE ONLY public.cm_rciam_stats_viewers ADD CONSTRAINT cm_rciam_stats_viewers_rciam_stats_viewer_id_fkey FOREIGN KEY (rciam_stats_viewer_id) REFERENCES public.cm_rciam_stats_viewers(id)");
          $RciamStatsViewer->query("ALTER TABLE ONLY public.cm_rciam_stats_viewers ADD CONSTRAINT cm_rciam_stats_viewers_privileged_co_group_id_fkey FOREIGN KEY (privileged_co_group_id) REFERENCES public.cm_co_groups(id)");
          break;
      }
    }
  }

  public $rciam_stats_viewers = array(
    'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'length' => 11, 'key' => 'primary'),
    'co_id' => array('type' => 'integer', 'null' => true, 'default' => null),
    'rciam_stats_viewer_id' => array('type' => 'integer', 'null' => true, 'default' => null),
    'privileged_co_group_id' => array('type' => 'integer', 'null' => true, 'default' => null),
    'type' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 2),
    'hostname' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 128),
    'port' => array('type' => 'integer', 'null' => true, 'default' => null),
    'username' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 128),
    'password' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 256),
    'databas' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 128),
    'persistent' => array('type' => 'boolean', 'null' => false, 'default' => null),
    'encoding' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 128),
    'statisticsTableName' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 128),
    'identityProvidersMapTableName' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 128),
    'serviceProvidersMapTableName' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 128),
    'actor_identifier' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 256),
    'created' => array('type' => 'datetime', 'null' => true, 'default' => null),
    'modified' => array('type' => 'datetime', 'null' => true, 'default' => null),
    'deleted' => array('type' => 'boolean', 'null' => false, 'default' => false),
    'revision' => array('type' => 'integer', 'null' => false, 'default' => null),
    'indexes' => array(
      'PRIMARY' => array('unique' => true, 'column' => 'id')
    ),
    'tableParameters' => array()
  );

}