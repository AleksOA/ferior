<?php
load_muplugin_textdomain( 'multi_network', '/multi_network/languages/' );

// Function  for registration page
// ===================================
require WPMU_PLUGIN_DIR . '/multi_network/signup/plugin.php';
// ===================================

require WPMU_PLUGIN_DIR . '/multi_network/login/plugin.php';

require WPMU_PLUGIN_DIR . '/multi_network/login/custom-login.php';


require WPMU_PLUGIN_DIR . '/multi_network/signup/custom-activate.php';

require WPMU_PLUGIN_DIR . '/multi_network/signup/custom-signup.php';