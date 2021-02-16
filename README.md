# AUTH LDAPCI4
This project based on package of Greg Wojtak LDAP library.  
Use this library with Codeigniter 4.1+ PHP7.4+

User Guide:
1)put the file in folder project app/ThirdParty/
2)set autoload class, go to app/Config/Autoload.php search the public variable $classmap and add this 
public $classmap = [
			'Auth_Ldap' => APPPATH .'third_party/Auth_Ldap.php',		
	];
