<?php
class AppSchema extends CakeSchema
{

    public function before($event = array())
    {
      return true;
    }
    
    public function after($event = array())
    {
    }

    public $rciam_stats_viewers = array(
        'id' => array('type' => 'integer', 'autoIncrement' => true, 'null' => false, 'default' => null, 'length' => 10, 'key' => 'primary'),
        'co_id' => array('type' => 'integer', 'null' => true, 'length' => 10),
        'type' => array('type' => 'string','null' => false,  'length' => 2),
        'hostname' => array('type' => 'string','null' => false,  'length' => 128),
        'username' => array('type' => 'string','null' => false,  'length' => 128),
        'password' => array('type' => 'string','null' => false,  'length' => 80),
        'stats_type' => array('type' => 'string','null' => false,  'length' => 2),
        'databas' => array('type' => 'string','null' => false,  'length' => 128),
        'created' => array('type' => 'datetime', 'null' => true),
        'modified' => array('type' => 'datetime', 'null' => true),
        'indexes' => array(
            'PRIMARY' => array('column' => 'id', 'unique' => 1),
        )
    );
}
/**
// Console/cake schema create --file schema.php --path /srv/comanage/comanage-registry-current/local/Plugin/RciamStatsViewer/Config/Schema
ALTER TABLE ONLY public.cm_rciam_stats_viewers ADD CONSTRAINT cm_rciam_stats_viewers_co_id_fkey FOREIGN KEY (co_id) REFERENCES public.cm_cos(id);
GRANT SELECT ON TABLE public.cm_rciam_stats_viewers TO rciam_registry_user_proxy;
*/