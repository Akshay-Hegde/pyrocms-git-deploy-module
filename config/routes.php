<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

    // Maintain admin routes
    $route['deploy/admin(:any)?'] = 'admin$1';
    $route['deploy/migrate(:any)?'] = 'deploy/migrate/$1';
    $route['deploy/(:any)?'] = 'deploy/index/$1';