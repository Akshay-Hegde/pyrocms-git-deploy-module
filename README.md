# PyroCMS Git Deploy Plugin

## Description

The git deploy plugin is a simple way to deploy a website using git.
It relies on Bitbucket / Github POST commit Hooks.

## Usage

To start with you need to create a clone of your projects git repository.

If you are using a private repository you need to configure your server to use SSH keys for the user that your webserver runs as.

Next setup the configuration items in config/deploy.php.

Then setup the POST commit hook on Bitbucket / Github , to do this just add the URL http://YOUR-DOMAIN.com/deploy , if you specified a Hash key in config you need to append this to the URL e.g : http://YOUR-DOMAIN.com/deploy/MY_HASH_KEY.

The execute method of this module is based on a script written by Brandon Summers, for more information see his article: http://brandonsummers.name/blog/2012/02/10/using-bitbucket-for-automated-deployments/.