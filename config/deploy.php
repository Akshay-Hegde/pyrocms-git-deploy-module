<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

    if (ENVIRONMENT == PYRO_DEVELOPMENT) {
        $config['deploy:branch']         = 'develop';  // The name of the branch to pull from
    } elseif (ENVIRONMENT == PYRO_STAGING) {
        $config['deploy:branch']         = 'staging';
    }elseif (ENVIRONMENT == PYRO_PRODUCTION) {
        $config['deploy:branch']         = 'master';
    }

    $config['deploy:remote']             = 'origin';  // The name of the remote to pull from

    $config['deploy:log']                = true;  // Enable logging: true / flase

    $config['deploy:hash_key']           = '';  // Unique hash key that should be appended to URI e.g mydomain.com/deploy/MY-HASH-KEY

    $config['deploy:asset_clean']        = true;  // Clean assets: true / false
    $config['deploy:asset_clean_age']    = 'yesterday';  // Clear assets older than this: strtotime values

    $config['deploy:session_clean']      = true;  // Clean ci_sessions table
    $config['deploy:session_clean_age']  = '-1 week';  // Clear sessions older than this: strtotime values

    $config['deploy:email_on_success']   = true;  // Should we send an email if successful: true / false
    $config['deploy:email_on_failure']   = true;  // Should we send an email if we fail: true / false
    $config['deploy:email_address']      = Settings::get('contact_email');  // Email address to send to

    $config['deploy:migration']          = false;  // Enable migrations: true / false
    $config['deploy:migration_path']     = SHARED_ADDONPATH.'modules/deploy/migrations/'; // The location of the migration files
    $config['deploy:migration_version']  = 1;  // Current migration version