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

class RciamStatsViewerDBEncodingTypeEnum
{
    const utf_8     = 'utf-8';
    const type      = array(
        'utf-8' => 'utf-8',
    );
}