<?php 

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
                  'action'     => 'edit'))
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
}