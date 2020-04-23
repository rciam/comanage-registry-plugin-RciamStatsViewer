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

class RciamStatsViewerDBPortsEnum
{
  const Mysql     = '3306';
  const Postgres  = '5432';
  const port      = array(
    'MY' => '3306',
    'PG' => '5432'
  );
}

class RciamStatsViewerDBEncodingTypeEnum
{
    const utf_8      = 'utf8';
    const iso_8859_7 = 'iso_8859_7'; // Latin/ Greek
    const latin1     = 'latin1'; // Western European
    const latin2     = 'latin2'; // Central European
    const latin3     = 'latin3'; // South European
    const latin4     = 'latin4'; // North European

    const type       = array(
        'utf8'       => 'utf8',
        'iso_8859_7' => 'iso_8859_7',
        'latin1'     => 'latin1',
        'latin2'     => 'latin2',
        'latin3'     => 'latin3',
        'latin4'     => 'latin4'
    );
}
