<?php 

class RciamStatsViewerStatsTypeEnum
{
    const Quantitative = 'QN';
    const Qualitative  = 'QL';
    const type         = array(
        'QN' => 'Quantitative',
        'QL' => 'Qualitative'
    );
}

class RciamStatsViewerDBDriverTypeEnum
{
    const Mysql     = 'MY';
    const Postgres  = 'PG';
    const type      = array(
        'MY' => 'Mysql',
        'PG' => 'Postgres'
    );
}