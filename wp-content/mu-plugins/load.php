<?php
load_muplugin_textdomain( 'multi_network', '/multi_network/languages/' );

// Function  for registration page
// ===================================
require WPMU_PLUGIN_DIR . '/multi_network/signup/plugin.php';
// ===================================

require WPMU_PLUGIN_DIR . '/multi_network/signup/wp-signup.php';

require WPMU_PLUGIN_DIR . '/multi_network/signup/wp-activate.php';