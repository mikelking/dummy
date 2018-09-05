<?php
/*
Plugin Name: De-Naggerator
Version: 1.0
Description: A simple plugin to disable admin notices for non admin users.
Author: Mikel King
Text Domain: de-nag
License: BSD(3 Clause)
License URI: http://opensource.org/licenses/BSD-3-Clause

    Copyright (C) 2014, Mikel King, rd.com, (mikel.king AT rd DOT com)
    All rights reserved.

    Redistribution and use in source and binary forms, with or without
    modification, are permitted provided that the following conditions are met:
    
        * Redistributions of source code must retain the above copyright notice, this
          list of conditions and the following disclaimer.
        
        * Redistributions in binary form must reproduce the above copyright notice,
          this list of conditions and the following disclaimer in the documentation
          and/or other materials provided with the distribution.
        
        * Neither the name of the {organization} nor the names of its
          contributors may be used to endorse or promote products derived from
          this software without specific prior written permission.
    
    THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
    AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
    IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
    DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE
    FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
    DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
    SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
    CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY,
    OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
    OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
*/

// see: http://codex.wordpress.org/Plugin_API/Hooks_2.0.x

class De_Nag extends WP_Base {
	const VERSION = '1.0';

	public function __construct() {
		add_action('admin_head', array($this, 'hide_core_update_notice_for_users'), 1);
		add_action('admin_head', array($this, 'hide_plugin_update_notice_for_users'), 1);
		add_action('admin_head', array($this, 'hide_theme_update_notice_for_users'), 1);
	}

	public function hide_core_update_notice_for_users() {
		if ( !current_user_can('update_core')) {
			remove_action('admin_notices', 'update_nag', 3);
		}
	}

	public function hide_plugin_update_notice_for_users() {
		!current_user_can('install_plugins')
		and remove_action('admin_notices', 'update_nag', 3);
	}

	public function hide_theme_update_notice_for_users() {
		!current_user_can('install_themes')
		and remove_action('admin_notices', 'update_nag', 3);
	}
}

$ps = De_Nag::get_instance();