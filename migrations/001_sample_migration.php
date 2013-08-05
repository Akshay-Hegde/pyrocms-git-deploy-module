<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_sample_migration extends CI_Migration {

    public function up()
    {
        $this->db->insert('settings',
            array('slug'            => 'sample',
                    'title'         => 'SAMPLE MIGRATION INSERT',
                    'description'   => '',
                    'type'          => 'text',
                    'default'       => '0',
                    'value'         => '1',// admins can upload by default on the first site only
                    'options'       => '',
                    'is_required'   => '1',
                    'is_gui'        => '0',
                    'module'        => '')
            );
    }

    public function down()
    {
        $this->db->delete('settings', array('slug' => 'sample'));
    }
}