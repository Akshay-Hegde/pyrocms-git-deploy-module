<?php defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Deploy Details File
 *
 * @package     PyroCMS
 * @subpackage  Deploy
 * @author      Dan Sullivan
 */
class Module_Deploy extends Module {

    public $version = '1.0';

    // --------------------------------------------------------------------------

    public function info()
    {
        return array(
            'name' => array(
                'en' => 'Deploy'
            ),
            'description' => array(
                'en' => 'Git Deploy module'
            ),
            'frontend' => true,
            'backend' => true,
            'menu' => 'data',
            'author' => 'Dan Sullivan',
            'roles' => array(
                'deploy', 'migrate', 'clear_log'
            ),
        );
    }

    // --------------------------------------------------------------------------

    public function install()
    {
        $migrations = "
            CREATE TABLE ".$this->db->dbprefix('deploy_migration')." (
              `version` int(4) NOT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
        ";

        $insert_migration = "
            INSERT INTO ".$this->db->dbprefix('deploy_migration')." VALUES('1')
        ";

        $log = "
            CREATE TABLE ".$this->db->dbprefix('deploy_log')." (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `msg` varchar(255) NOT NULL COLLATE utf8_unicode_ci,
              `date` datetime NOT NULL,
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
        ";

        if ($this->db->query($migrations) and $this->db->query($insert_migration) and $this->db->query($log))
        {
            return true;
        }
    }

    // --------------------------------------------------------------------------

    public function uninstall()
    {
        $this->dbforge->drop_table('deploy_migration');
        $this->dbforge->drop_table('deploy_log');

        return true;
    }

    // --------------------------------------------------------------------------

    public function upgrade($old_version)
    {

        return true;
    }

    // --------------------------------------------------------------------------

    public function help()
    {
        return "No documentation has been added for this module.<br/>Contact the module developer for assistance.";
    }

}