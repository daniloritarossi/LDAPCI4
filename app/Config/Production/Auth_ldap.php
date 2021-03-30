<?php
namespace Config\Development;

/*
 * This file is part of Auth_Ldap.
 *
 * Auth_Ldap is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Auth_Ldap is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Auth_Ldap. If not, see <http://www.gnu.org/licenses/>.
 *
 */

/**
 *
 * @author Danilo Ritarossi <danilo.ritarossi@gmail.com> (based on package Greg Wojtak <greg.wojtak@gmail.com>)
 * @copyright Copyright © 2021 by Danilo Ritarossi <danilo.ritarossi@gmail.com>
 * @package Auth_Ldap
 * @subpackage configuration
 * @license GNU Lesser General Public License
 */

/**
 * Array Index - Usage
 * hosts - Array of ldap servers to try to authenticate against
 * ports - The remote port on the ldap server to connect to
 * basedn - The base dn of your ldap data store
 * login_attribute - LDAP attribute used to check usernames against
 * proxy_user - Distinguised name of a proxy user if your LDAP server does not allow anonymous binds
 * proxy pass - Password to use with above
 * roles - An array of role names to use within your app. The values are arbitrary.
 * The keys themselves represent the
 * "security level," ie
 * if( $security_level >= 3 ) {
 * // Is a power user
 * echo display_info_for_power_users_or_admins();
 * }
 * member_attribute - Attribute to search to determine allowance after successful authentication
 * auditlog - Location to log auditable events. Needs to be writeable
 * by the web server
 */
class Auth_ldap extends \CodeIgniter\Config\BaseConfig {
	public $config = array (
			'ldap_uris' => array (
					'ldap://192.168.0.0:389'
			),
			
			'proxy_user' => 'uid=name_uid,ou=name_ou,ou=PTA,dc=dc_plus_name,dc=dc_plus_name_one,dc=dc_plus_name_two',
			'proxy_pass' => 'password_ldap',
			'roles' => array (
					1 => 'User',
					3 => 'Power User',
					5 => 'Administrator'
			),
			
			'use_tls' => false,
			'use_ssl' => false,
			'use_ad' => true,
			'ad_domain' => 'mycompany.com',
			'login_attribute' => 'uid',
			
			'member_attribute' => 'uid',
			'auditlog' => APPPATH . '../writable/logs/audit.log', // Some place to log attempted logins (separate from message log)
			
			'basedn' => 'ou=ou_elm,ou=ou_elm2,ou=elm3,dc=applicazioni,dc=dc_uno,dc=dc_due',
			'user_search_base' => "", // Leave empty to use 'search_base']
			'group_search_base' => "", // Leave empty to use 'search_base']
			'user_object_class' => 'inetOrgPerson',
			'group_object_class' => 'posixGroup',
			'user_search_filter' => '', // Additional search filters to use for user lookups
			'group_search_filter' => '', // Additional search filters to use for group lookups
			'schema_type' => 'rfc123456', // Use rfc2307, rfc2307bis, or ad
			'memberOf_attribute' => "memberof"
	);
}