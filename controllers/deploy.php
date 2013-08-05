<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Deploy Controller Class
 *
 * @subpackage  Deploy
 * @category    Controller
 * @author      Dan Sullivan
 */
class Deploy extends Public_Controller
{

    private $deploy_branch;
    private $deploy_complete;
    private $deploy_directory;
    private $deploy_hash_key;
    private $deploy_log_array;
    private $deploy_log_table;
    private $deploy_migration_path;
    private $deploy_migration_table;
    private $deploy_migration_version;
    private $deploy_remote;

    /**
     * Construct
     *
     * @access  public
     * @return  void
     */
    public function __construct()
    {
        parent::__construct();

        $this->load->config('deploy/deploy');

        $this->deploy_branch = $this->config->item('deploy:branch');
        $this->deploy_complete = false;
        $this->deploy_directory = realpath(FCPATH).DIRECTORY_SEPARATOR;
        $this->deploy_hash_key = $this->config->item('deploy:hash_key');
        $this->log_table = $this->db->dbprefix('deploy_log');
        $this->deploy_migration_path = $this->config->item('deploy:migration_path');
        $this->deploy_migration_table = $this->db->dbprefix('deploy_migration');
        $this->migration_version = $this->config->item('deploy:migration_version');
        $this->deploy_remote = $this->config->item('deploy:remote');

        $this->log('Attempting deployment...');
    }

    /**
     * Deploy Site
     *
     * @access  public
     * @param string optional $hash
     */
    public function index($hash = null)
    {
        $this->secure($hash);

        $this->deploy_complete = $this->execute();

        $this->deploy_complete = $this->run_migration();

        $this->cleanup();

        Events::trigger('deploy_completed', $this->deploy_complete);

        $this->email();

        $this->admin();
    }

    /**
    * Writes a message to the log table.
    *
    * @access private
    * @param  string  $message  The message to write
    */
    private function log($message)
    {
        $date = date('Y-m-d H:i:s');
        $this->deploy_log_array[] = $date.' - '.$message;
        if ($this->config->item('deploy:log') && $this->db->table_exists($this->log_table)) {
            $data = array(
                'msg' => $message,
                'date'=> $date
            );
            $this->db->insert($this->log_table, $data);
            $this->db->query('DELETE FROM '.$this->log_table.' WHERE date < DATE_SUB(NOW(), INTERVAL 30 DAY)');
        }
    }

    /**
     * Migrate Site - Entry point from Admin
     *
     * @access  public
     * @param string optional $hash
     */
    public function migrate($hash = null)
    {
        $this->secure($hash);
        $return = $this->run_migration();
        $this->admin();
        return $return;
    }

    /**
     * Run the site migration
     *
     * @access  private
     */
    private function run_migration()
    {
        $return = true;
        if ($this->deploy_migration_version && $this->deploy_migration_path && $this->config->item('deploy:migration') && $this->db->table_exists($this->deploy_migration_table)) {
            $params = array(
                'migration_enabled' => true,
                'migration_version' => $this->deploy_migration_version,
                'migration_table' => $this->deploy_migration_table,
                'migration_path' => $this->deploy_migration_path
            );
            $this->deploy_migration->__construct($params);

            if (!($this->deploy_migration->current()))
            {
                $message = $this->deploy_migration->error_string();
                $return = false;
            } else {
                $message = 'Database migrations run successfully';
                $return = true;
            }
            $this->log($message);
        }
        return $return;
    }

    /**
     * Triggered from Admin?
     *
     * @access  private
     */
    private function admin()
    {
        if ($this->input->post('admin')) {
            if ($this->deploy_complete) {
                $message = 'Deployment run successfully';
            } else {
                $message = 'Deployment failed';
            }
            $this->session->set_flashdata('success', $message);
            redirect('admin/deploy');
        }
    }

    /**
     * Clean up asset files and session table
     *
     * @access  private
     */
    private function cleanup()
    {
        if ($this->config->item('deploy:asset_clean')) {
            Asset::clear_cache($this->config->item('deploy:asset_clean_age'));
            $this->log('Asset cache cleaned');
        }
        if ($this->config->item('deploy:session_clean')) {
            $session_table = SITE_REF.'_'.str_replace('default_', '', config_item('sess_table_name'));
            if ($this->db->table_exists($session_table))
            {
                $this->db
                    ->where('last_activity <', (strtotime($this->config->item('deploy:session_clean_age'))))
                    ->delete($session_table);
                $this->log('Session table cleaned');
            }
        }
    }

    /**
     * Check request is POST and the hash is set if required
     *
     * @access  private
     * @param string $hash
     */
    private function secure($hash = null)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { show_404(); }
        if ($this->deploy_hash_key && $this->deploy_hash_key != '') {
            if ($hash != $this->deploy_hash_key) {
                show_404();
            }
        }
    }

    /**
     * Check if we need to send an email
     *
     * @access  private
     */
    private function email()
    {
        if ($this->deploy_complete == true && $this->config->item('deploy:email_on_success')) {
            $this->send_email('Deployment successful');
        } elseif ($this->deploy_complete == false && $this->config->item('deploy:email_on_failure')) {
            $this->send_email('Deployment failed');
        }
    }

    /**
     * Send the email
     *
     * @access  private
     * @param string $subject
     */
    private function send_email($subject)
    {
        $message;
        $email_address = $this->config->item('deploy:email_address');
        $site_name = Settings::get('site_name');

        if($email_address) {
            $this->load->library('email');

            $this->email->from($email_address, $site_name);
            $this->email->to($email_address);

            $this->email->subject($site_name.' - '.$subject);
            if ($this->deploy_log_array) {
                foreach ($this->deploy_log_array as $line) {
                    $message .= $line.'<br>';
                }
            }
            $this->email->message('Here is the output from the latest deployment attempt: <br>'.$message);

            if($this->email->send()) {
                $this->log('Email sent successfully');
            } else {
                $this->log('Email could not be sent');
            }
        } else {
            $this->log('Email address not set');
        }
    }

    /**
     * Run the git pull process
     *
     * @access  private
     */
    private function execute()
    {
        try
        {
            // Make sure we're in the right directory
            exec('cd '.$this->deploy_directory, $output);
            $this->log('Changing working directory... '.implode(' ', $output));

            // Discard any changes to tracked files since our last deploy
            exec('git reset --hard HEAD', $output);
            $this->log('Reseting repository... '.implode(' ', $output));

            // Update the local repository
            exec('git pull '.$this->deploy_remote.' '.$this->deploy_branch, $output);
            $this->log('Pulling in changes... '.implode(' ', $output));

            // Secure the .git directory
            exec('chmod -R og-rx .git');
            $this->log('Securing .git directory... ');

            $this->log('Deployment successful.');
            return true;
        }
        catch (Exception $e)
        {
            $this->log($e, 'ERROR');
            return false;
        }
    }

}

/* end of file deploy.php */