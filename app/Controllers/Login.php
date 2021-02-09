<?php
namespace app\Controllers;

use CodeIgniter\Controller;
use App\ThirdParty\Auth_Ldap;

/*
 * This file is part of Auth_Ldap.
 
 Auth_Ldap is free software: you can redistribute it and/or modify
 it under the terms of the GNU Lesser General Public License as published by
 the Free Software Foundation, either version 3 of the License, or
 (at your option) any later version.
 
 Auth_Ldap is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.
 
 You should have received a copy of the GNU General Public License
 along with Auth_Ldap.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

/**
 * Simple LDAP Authentication controller for Codeigniter 4.1.
 * 
 * @package Auth_Ldap
 * @author Danilo Ritarossi danilo.ritarossi@gmail.com (based on package Greg Wojtak <greg.wojtak@gmail.com>)
 * @copyright   Copyright © 2010,2011 by Greg Wojtak <greg.wojtak@gmail.com>
 * @package     Auth_Ldap
 * @subpackage  auth demo
 * @license     GNU Lesser General Public License
 */

class Login extends Controller
{
	/**
	 * MAIN FUNCTION
	 */
	public function index(){		
		$this->login();
	}
	
	/**
	 * Function based on LDAP LIBRARY. 
	 * 
	 * @param $result $errorMsg
	 *  Return true if login successfull false otherwise
	 */
	function login($errorMsg = NULL){
		$ldapCkeckLogin = new Auth_Ldap();
		
		$result = $ldapCkeckLogin->login("username", "password", FALSE);
		// More code here 
		// var_dump($result);
		print_r($result);
		var_dump($result);

	}
	
	public function view($page = 'home'){
		
		// $modelProduct = new \App\Models\Login;
		
	}
}
