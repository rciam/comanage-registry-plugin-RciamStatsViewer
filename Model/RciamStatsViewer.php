<?php 

class RciamStatsViewer extends AppModel
{
    public $cmPluginType= 'other';

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
                    "RciamStatsViewerStatsTypeEnum::PieChart",
                    "RciamStatsViewerStatsTypeEnum::LineChart"
                ),
            ),
            'required' => true,
            'message' => 'A valid type must be selected'
        )
    );
}