# AUTH LDAPCI4
====================

Simple AUTH LDAPCI4 Authentication controller for Codeigniter 4.1.
---------------

This project based on package of Greg Wojtak LDAP library.
Use this library with Codeigniter 4.1+ PHP7.4+

User Guide: 
1. Copy folders and files under /app to your project

2. About your environment change the configuration for correct connection on app/Config/{environment}/AuthLdap.php

3. set autoload class, go to app/Config/Autoload.php 
	search the public variable ```$classmap``` and add this
```
public $classmap = [
'Auth_Ldap' => APPPATH .'third_party/Auth_Ldap.php'
];
```
4. Create new controller like this, copy and past the example:<br>

```<?php
namespace app\Controllers;
use CodeIgniter\Controller; 
use App\ThirdParty\Auth_Ldap;


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
class Login extends Controller { 	
	public function __construct() {		
		$this->session = \Config\Services::session();
		helper ( [ 'form','url','cookie'] );
	}
	
	//MAIN FUNCTION	 
	public function index() {	
	 // Change this with your code Header/Page/Footer	
		echo view ( 'Header/Header' );
		echo view ( 'Login' );
		echo view ( 'Footer/Footer' );
	}

	// Public function for Logout
	public function logout(){
		$ldapCkeckLogin = new Auth_Ldap ();
		$ldapCkeckLogin->logout ();
		return redirect()->to('/Login'); 
	}
	
	
	// Function based on LDAP LIBRARY.
	// @param $result $errorMsg
	//  	Return true if login successfull false otherwise
	function access($errorMsg = NULL) {
		$result = array();
	
		$ldapCkeckLogin = new Auth_Ldap ();
		$request = \Config\Services::request ();

		$val = $this->validate ( [ 
				'username' => 'required|max_length[30]',
				'psw' => 'required|max_length[30]'
		] );

		if (! $val) {
			echo view ( 'Header/Header' );
			echo view ( 'Login', [ 
					'validation' => $this->validator
			] );
			echo view ( 'Footer/Footer' );
		} else {
			$username = $request->getPost ( 'username' );
			$password = $request->getPost ( 'psw' );

			// Check se esite l'utente abilitato su DB
			$result = $this->userModel->getUsername ( $username );
			
			// Set the session data
			$customdata = array (
					'user_role' => $result[0]->ROLE
			);
			
			$this->session->set ( $customdata );			
			
			if (! empty ( $result )) {
				$result = $ldapCkeckLogin->login ( $username, $password, FALSE );				
				
			} else {
				$result = FALSE;
			}

			if ($result === TRUE) {				
				
				return redirect()->to('/Dashboard/index'); 
				
			} else {
				echo view ( 'Header/Header' );
				echo view ( 'Login', [ 
						'error_validation' => "Username o Password errati, provare di nuovo."
				] );
				echo view ( 'Footer/Footer' );
			}
		}		
	}	
}
```



