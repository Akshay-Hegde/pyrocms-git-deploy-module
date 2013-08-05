<?php defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Deploy Admin Controller Class
 *
 * @package     PyroCMS
 * @subpackage  Deploy
 * @category    Admin Controller
 * @author      Tenreps development team
 */
class Admin extends Admin_Controller {

    function __construct()
    {
        parent::__construct();
        $this->load->config('deploy/deploy');
    }

    // --------------------------------------------------------------------------

    /**
     * Show log
     */
    public function index()
    {

        $data = array();

        $data['logs'] = $this->db->order_by('date DESC')->get('deploy_log')->result();

        $data['hash_key'] = $this->config->item('deploy:hash_key');

        $this->template
            ->append_css( 'module::deploy.css' )
            ->build('admin/index', $data);
    }

    public function clear_log()
    {
        if (group_has_role('deploy', 'clear_log')) {
            if ($this->db->empty_table('deploy_log')) {
                $this->session->set_flashdata('success', 'Log cleared successfully');
            } else {
                $this->session->set_flashdata('error', 'Log could not be cleared');
            }
        } else {
            $this->session->set_flashdata('error', 'You do not have permission to clear logs');
        }
        redirect('admin/deploy');
    }

}