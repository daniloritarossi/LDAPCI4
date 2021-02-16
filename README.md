# AUTH LDAPCI4
This project based on package of Greg Wojtak LDAP library.  
Use this library with Codeigniter 4.1+ PHP7.4+

User Guide:
1)put the file in folder project app/ThirdParty/<br>
2)set autoload class, go to app/Config/Autoload.php search the public variable $classmap and add this <br>
public $classmap = [<br>
		'Auth_Ldap' => APPPATH .'third_party/Auth_Ldap.php'<br>
		];<br>
3)Create new controller like this: <br> 

<?php <br> 
namespace app\Controllers;<br> 

use CodeIgniter\Controller;<br> 
use App\ThirdParty\Auth_Ldap;<br> 

class Login extends Controller {<br> 	
	public function __construct() {<br>		
	}<br>	
	public function mylogin() {<br>		
		$ldapCkeckLogin = new Auth_Ldap ();<br>
		$result = $ldapCkeckLogin->login ( $username, $password, FALSE );</br>
	}</br>
