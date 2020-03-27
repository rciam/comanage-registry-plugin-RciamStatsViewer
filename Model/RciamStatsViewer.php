<?php 

class RciamStatsViewer extends AppModel
{
    public $cmPluginType= 'other';

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