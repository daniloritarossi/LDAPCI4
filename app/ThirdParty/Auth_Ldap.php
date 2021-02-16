<?php
namespace app\ThirdParty;

/**
  This file is part of AUTH LDAPCI4.

 AUTH LDAPCI4 is free software: you can redistribute it and/or modify
 it under the terms of the GNU Lesser General Public License as published by
 the Free Software Foundation, either version 3 of the License, or
 (at your option) any later version.

 Auth_Ldap is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 GNU General Public License for more details.
 
 You should have received a copy of the GNU General Public License
 along with Auth_Ldap. If not, see <http://www.gnu.org/licenses/>. 
*/


/**
  AUTH LDAPCI4 Simple LDAP Authentication controller for Codeigniter 4.1.
 
  @package AUTH LDAPCI4
  @author Danilo Ritarossi danilo.ritarossi@gmail.com (based on package by Greg Wojtak <greg.wojtak@gmail.com>)
  @copyright Copyright © 2010,2011 by Danilo Ritarossi danilo.ritarossi@gmail.com
  @package AUTH LDAPCI4
  @license GNU Lesser General Public License
*/ 
class Auth_Ldap {
	function __construct() {
		log_message ( 'debug', 'Auth_Ldap initialization commencing...' );
		
		// Load the session library
		$this->session = \Config\Services::session ();
		if (getenv ( 'CI_ENVIRONMENT' ) === "development") {
			// Load the configuration
			$config = new \Config\Development\Auth_ldap ();
		} elseif (getenv ( 'CI_ENVIRONMENT' ) === "production") {
			// Load the configuration
			$config = new \Config\Production\Auth_ldap ();
		}
		
		// Load the language file
		// $this->ci->lang->load('auth_ldap');
		
		$this->_init ( $config );
	}
	
	/**
	 *
	 * @access private
	 * @return void
	 */
	private function _init($config) {
		
		// Verify that the LDAP extension has been loaded/built-in
		// No sense continuing if we can't
		if (! function_exists ( 'ldap_connect' )) {
			show_error ( 'LDAP functionality not present.  Either load the module ldap php module or use a php with ldap support compiled in.' );
			log_message ( 'error', 'LDAP functionality not present in php.' );
		}
		
		$this->ldap_uris = $config->config ['ldap_uris'];
		$this->use_tls = $config->config ['use_tls'];
		$this->basedn = $config->config ['basedn'];
		// $this->account_ou = $config->config['account_ou'];
		$this->login_attribute = strtolower ( $config->config ['login_attribute'] );
		$this->use_ad = $config->config ['use_ad'];
		$this->ad_domain = $config->config ['ad_domain'];
		$this->proxy_user = $config->config ['proxy_user'];
		$this->proxy_pass = $config->config ['proxy_pass'];
		$this->roles = $config->config ['roles'];
		$this->auditlog = $config->config ['auditlog'];
		$this->member_attribute = $config->config ['member_attribute'];
		$this->user_object_class = $config->config ['user_object_class'];
	}
	
	/**
	 * Private function to get the IP address of the request
	 *
	 * @return string
	 */
	private function _getIpAddress() {
		// Checking IP From Shared Internet
		if (! empty ( $_SERVER ['HTTP_CLIENT_IP'] )) {
			$ip = $_SERVER ['HTTP_CLIENT_IP'];
			// To Check IP is Pass From Proxy
		} elseif (! empty ( $_SERVER ['HTTP_X_FORWARDED_FOR'] )) {
			$ip = $_SERVER ['HTTP_X_FORWARDED_FOR'];
		} else if (! empty ( $_SERVER ['REMOTE_ADDR'] )) {
			$ip = $_SERVER ['REMOTE_ADDR'];
		} else {
			$ip = "";
		}
		return $ip;
	}
	
	/**
	 *
	 * @access public
	 * @param string $username
	 * @param string $password
	 * @return bool
	 */
	function login($username, $password, $roleCtrl) {
		/*
		 * For now just pass this along to _authenticate. We could do
		 * something else here before hand in the future.
		 */
		 $user_info = $this->_authenticate ( $username, $password );
		 
		 if (empty ( $user_info ['role'] ) && $roleCtrl == TRUE) {
		 	log_message ( 'info', $username . " has no role to play." );
		 	show_error ( $username . ' succssfully authenticated, but is not allowed because the username was not found in an allowed access group.' );
		 }
		 if ($user_info !== FALSE) {
		 	// Record the login
		 	$this->_audit ( "Successful login: " . $user_info ['cn'] . "(" . $username . ") from " . $_SERVER ['REMOTE_ADDR'] );
		 	
		 	// Set the session data
		 	$customdata = array (
		 			'username' => $username,
		 			'cn' => $user_info ['cn'],
		 			'role' => $user_info ['role'],
		 			'logged_in' => TRUE
		 	);
		 	
		 	$this->session->set ( $customdata );
		 	
		 	return TRUE;
		 } else {
		 	return FALSE;
		 }
	}
	
	/**
	 *
	 * @access public
	 * @return bool
	 */
	function is_authenticated() {
		if ($this->ci->session->userdata ( 'logged_in' )) {
			return TRUE;
		} else {
			return FALSE;
		}
	}
	
	/**
	 *
	 * @access public
	 */
	function logout() {
		// Just set logged_in to FALSE and then destroy everything for good measure
		$this->ci->session->set_userdata ( array (
				'logged_in' => FALSE
		) );
		$this->ci->session->sess_destroy ();
	}
	
	/**
	 *
	 * @access private
	 * @param string $msg
	 * @return bool
	 */
	private function _audit($msg) {
		$date = date ( 'Y/m/d H:i:s' );
		if (! file_put_contents ( $this->auditlog, $date . ": " . $msg . "\n", FILE_APPEND )) {
			log_message ( 'info', 'Error opening audit log ' . $this->auditlog );
			return FALSE;
		}
		return TRUE;
	}
	
	/**
	 *
	 * @access private
	 * @param string $username
	 * @param string $password
	 * @return array
	 */
	private function _authenticate($username, $password) {
		foreach ( $this->ldap_uris as $uri ) {
			$this->ldapconn = ldap_connect ( $uri );
			if ($this->ldapconn) {
				$this->connected_uri = $uri;
				break;
			} else {
				log_message ( 'info', 'Error connecting to ' . $uri );
			}
		}
		// At this point, $this->ldapconn should be set. If not... DOOM!
		if (! $this->ldapconn) {
			log_message ( 'error', "Couldn't connect to any LDAP servers.  Bailing..." );
			show_error ( 'Error connecting to your LDAP server(s).  Please check the connection and try again.' );
		}
		
		if ($this->use_tls) {
			log_message ( 'info', 'Attempting to start TLS' );
			if (substr ( $this->connected_uri, 0, 5 ) === 'ldaps') {
				log_message ( 'error', 'TLS is incompatible with ldaps.  Either use ldap:// uri or use_tls = false' );
				show_error ( 'TLS incompatible with ldaps.  Use ldap:// uri or use_tls = false' );
			}
			ldap_start_tls ( $this->ldapconn );
		}
		
		// We've connected, now we can attempt the login...
		
		// These to ldap_set_options are needed for binding to AD properly
		// They should also work with any modern LDAP service.
		ldap_set_option ( $this->ldapconn, LDAP_OPT_REFERRALS, 0 );
		ldap_set_option ( $this->ldapconn, LDAP_OPT_PROTOCOL_VERSION, 3 );
		
		// Find the DN of the user we are binding as
		// If proxy_user and proxy_pass are set, use those, else bind anonymously
		if ($this->proxy_user) {
			$bind = ldap_bind ( $this->ldapconn, $this->proxy_user, $this->proxy_pass );
		} else {
			$bind = ldap_bind ( $this->ldapconn );
		}
		
		if (! $bind) {
			log_message ( 'error', 'Unable to perform anonymous/proxy bind' );
			show_error ( 'Unable to bind for user id lookup' );
		}
		
		log_message ( 'debug', 'Successfully bound to directory.  Performing dn lookup for ' . $username );
		// $filter = '('.$this->login_attribute.'='.$username.')';
		$filter = '(&(objectClass=' . $this->user_object_class . ')(' . $this->login_attribute . '=' . $username . '))';
		$search = ldap_search ( $this->ldapconn, $this->basedn, $filter, array (
				'dn',
				$this->login_attribute,
				'cn',
				'mail',
				'enable',
				$this->member_attribute,
				'siglafunzionale',
				'sn'
		) );
		// $entries = ldap_get_entries($this->ldapconn, $search);
		
		$entries = ldap_get_entries ( $this->ldapconn, $search );
		
		if ($entries ['count'] != 0 && array_key_exists ( "dn", $entries [0] )) {
			$binddn = $entries [0] ['dn'];
			// Now actually try to bind as the user
			try {
				$bind = ldap_bind ( $this->ldapconn, $binddn, $password );
			} catch ( \Exception $e ) {
				echo 'Message: ' . $e->getMessage ();
				return FALSE;
			}
			
			if (! $bind) {
				$this->_audit ( "Failed login attempt: " . $username . " from " . $_SERVER ['REMOTE_ADDR'] );
				return FALSE;
			}
			
			$cn = $entries [0] ['cn'] [0];
			$dn = stripslashes ( $entries [0] ['dn'] );
			$id = $entries [0] [$this->login_attribute] [0];
			
			$get_role_arg = $id;
			return array (
					'cn' => $cn,
					'dn' => $dn,
					'id' => $id,
					'role' => $this->_get_role ( $get_role_arg )
			);
		} else {
			$this->_audit ( "Failed login attempt: " . $username . " from " . $_SERVER ['REMOTE_ADDR'] );
			return FALSE;
		}
	}
	
	/**
	 *
	 * @access private
	 * @param string $str
	 * @param bool $for_dn
	 * @return string
	 */
	private function ldap_escape($str, $for_dn = false) {
		/**
		 * This function courtesy of douglass_davis at earthlink dot net
		 * Posted in comments at
		 * http://php.net/manual/en/function.ldap-search.php on 2009/04/08
		 */
		// see:
		// RFC2254
		// http://msdn.microsoft.com/en-us/library/ms675768(VS.85).aspx
		// http://www-03.ibm.com/systems/i/software/ldap/underdn.html
		if ($for_dn)
			$metaChars = array (
					',',
					'=',
					'+',
					'<',
					'>',
					';',
					'\\',
					'"',
					'#'
			);
			else
				$metaChars = array (
						'*',
						'(',
						')',
						'\\',
						chr ( 0 )
				);
				
				$quotedMetaChars = array ();
				foreach ( $metaChars as $key => $value )
					$quotedMetaChars [$key] = '\\' . str_pad ( dechex ( ord ( $value ) ), 2, '0' );
					$str = str_replace ( $metaChars, $quotedMetaChars, $str ); // replace them
					return ($str);
	}
	
	/**
	 *
	 * @access private
	 * @param string $username
	 * @return int
	 */
	private function _get_role($username) {
		$filter = '(' . $this->member_attribute . '=' . $username . ')';
		$search = ldap_search ( $this->ldapconn, $this->basedn, $filter, array (
				'cn'
		) );
		if (! $search) {
			log_message ( 'error', "Error searching for group:" . ldap_error ( $this->ldapconn ) );
			show_error ( 'Couldn\'t find groups: ' . ldap_error ( $this->ldapconn ) );
		}
		$results = ldap_get_entries ( $this->ldapconn, $search );
		if ($results ['count'] != 0) {
			for($i = 0; $i < $results ['count']; $i ++) {
				$role = array_search ( $results [$i] ['cn'] [0], $this->roles );
				if ($role !== FALSE) {
					return $role;
				}
			}
		}
		return false;
	}
}


