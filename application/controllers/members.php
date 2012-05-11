<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Members extends CI_Controller {
	public $user_dirs = array();
	public $themes = array();
	public $selected;
	function __construct(){
		parent::__construct();
		$this->load->helper('directory');
		$this->load->model('EzAuth_Model', 'ezauth');
		$this->ezauth->program = $this->config->item('ezauth_program');
		$this->ezauth->auto_login();
		$this->ezauth->protected_pages = array(
		    //'edit'       	 =>    	'admin',
		    'upload'             =>     'user',
		    'doUpload'           =>     'user',
		    'changepw'		 =>	'user',
		    'project'            =>     'user',
		    'content'            =>     'user',
		    'show_files'         =>     'user',
		    'player'             =>     'user',
		    'privatexml'         =>     'user'

		);
		$this->user_dirs[]= "files";
		$this->user_dirs[]= "images";
		$this->user_dirs[]= "sketches";
		$this->user_dirs[]= "music";
		$this->themes[1]= "superhero";
		$this->themes[2]= "united";
		$this->themes[3]= "cyborg";
		$this->themes[4]= "cerulean";
		$this->themes[5]= "simplex";
		$this->selected = 0;//0=default
		
	}
        public function _remap($method) {
	   $auth = $this->ezauth->authorize($method, true);
	   $segments = array_slice($this->uri->segment_array(),2);
	   if ($auth['authorize'] == true){
	       if(method_exists($this,$method)) call_user_func_array(array(&$this, $method), $segments);
	       else redirect("/members");
	   } else redirect('/members');
        }
	public function index(){
		$data = array();
		if($this->selected>0) $data['theme'] = $this->themes[$this->selected];
		$this->load->view('include/header',$data);
      		$this->load->view('frontpage');
      		$this->load->view('include/footer');
	}
	public function content(){
	   $data = array();
	   if($this->selected>0) $data['theme'] = $this->themes[$this->selected];
	   $this->load->view('include/header',$data);
	   $this->load->view('content');
	   $this->load->view('include/footer');
	     
	}
	public function vanilla_auth(){
	     require_once APPPATH.'/third_party/functions.jsconnect.php';
		$clientID = $this->config->item('sso_clientID');
		$secret = $this->config->item('sso_secret');

		if (empty($this->input->get)) {
			echo "show login form";
		} else {
			$get = array(
				'client_id' => $this->input->get('client_id'),
				'timestamp' => $this->input->get('timestamp'),
				'signature' => $this->input->get('signature')
			);
			
		        $user = array();
			$user['uniqueid'] = $this->ezauth->user->id;
			$user['name'] = $this->ezauth->user->username;
			$user['email'] = $this->ezauth->user->email;
			$user['photourl'] = $this->ezauth->user->photo;
		}

		$secure = true; 
		$this->WriteJsConnect($user, $get, $clientID, $secret, $secure);
	}
	public function project(){
           $data = array('markitup'=>TRUE);
           if($this->selected>0) $data['theme'] = $this->themes[$this->selected];
	   $this->load->view('include/header',$data);
	   $this->load->view('editor');
	   $this->load->view('include/footer',$data);
	}
     public function upload($dir=NULL){
        $da_ta = array();
	if($this->selected>0) $da_ta['theme'] = $this->themes[$this->selected];
	$this->load->view('include/header',$da_ta);
	if(is_null($dir)) $this->load->view('upload_tabs');
	else {
		$data = array('dir'=>$dir);
		$this->load->view('upload',$data);
	}
	$this->load->view('include/footer');
     }
     
     public function countdown($time=NULL){
	$data = array();
        if($this->selected>0) $data['theme'] = $this->themes[$this->selected];
	$data['countdown'] = array();
	if(!is_null($time)) $data['countdown']['time']= $time;
	$this->load->view('include/header',$data);
	$this->load->view('countdown');
	$this->load->view('include/footer');
     }
     
     public function products($time=NULL){
	$data = array();
	$data['productslider'] = TRUE;
        if($this->selected>0) $data['theme'] = $this->themes[$this->selected];
	$this->load->view('include/header',$data);
	$this->load->view('product_gallery');
	$this->load->view('include/footer');
     }
     
     public function _check_dir($dir){
	if(!is_dir($dir))
		return (mkdir($dir, 0777))?TRUE:FALSE;
	else
		return TRUE;
     }
     
      public function player(){
	$data = array('player'=>TRUE);
        if($this->selected>0) $data['theme'] = $this->themes[$this->selected];
	$this->load->view('include/header',$data);
	$this->load->view("player");
	$this->load->view('include/footer');
     }
    public function privatexml(){
	$this->load->view("privateXML");
     }
     
     public function doUpload($dir=NULL,$file=NULL){
        error_reporting(E_ALL | E_STRICT);
	if(is_null($dir) || !in_array($dir,$this->user_dirs)) $dir = "files";
	$dir = urlencode($dir);
	$user = urlencode($this->ezauth->user->username);
	$scriptURL = "/members/doUpload/".$dir."/";
	$userDIR = get_include_path()."assets/users/".$user;
	$uploadDIR = get_include_path()."assets/users/".$user."/".$dir."/";
	$uploadURL = "/assets/users/".$user."/".$dir."/";
	
	$this->uploads->options['script_url'] = $scriptURL;
	$this->uploads->options['upload_dir'] = $uploadDIR;
	$this->uploads->options['upload_url'] = $uploadURL;
	if($this->_check_dir($uploadDIR)){
		
		header('Pragma: no-cache');
		header('Cache-Control: no-store, no-cache, must-revalidate');
		header('Content-Disposition: inline; filename="files.json"');
		header('X-Content-Type-Options: nosniff');
		header('Access-Control-Allow-Origin: *');
		header('Access-Control-Allow-Methods: OPTIONS, HEAD, GET, POST, PUT, DELETE');
		header('Access-Control-Allow-Headers: X-File-Name, X-File-Type, X-File-Size');
		
		switch ($_SERVER['REQUEST_METHOD']) {
		    case 'OPTIONS':
			break;
		    case 'HEAD':
		    case 'GET':
			$this->uploads->get();
			break;
		    case 'POST':
			if (isset($_REQUEST['_method']) && $_REQUEST['_method'] === 'DELETE') {
			    $this->uploads->delete();
			} else {
			    $this->uploads->post();
			}
			break;
		    case 'DELETE':
			if(!is_null($file)){
			    $this->uploads->delete(rawurldecode($file));
			}
			break;
		    default:
			header('HTTP/1.1 405 Method Not Allowed');
		}
	}

	

     }


        function _user_files(){
		
		$uploadDIR = get_include_path()."assets/users/".$this->ezauth->user->username."/";
		return directory_map($uploadDIR);
		
	}
	function profile($user=FALSE){
                $data = array();
                if($this->selected>0) $data['theme'] = $this->themes[$this->selected];
		$this->load->view('include/header');
		$this->load->view('user_profile',array('user'=>$user));
		$this->load->view('include/footer');
	}
	
	public function login($data = array()) {
	        $rules['username'] = "required";
	        $this->validation->set_rules($rules);
	        $fields['username'] = "Username";
	        $this->validation->set_fields($fields);
	        if ($this->validation->run()) {
	            $login_ok = $this->ezauth->login();   
	            if ($login_ok['authorize'] == true) {
			$this->ezauth->remember_user();		
			redirect('/members/');    		
		    } else {
			$data['error_string'] = $login_ok['error'];
		    }

	        }
	}
	
	public function register() {
		$data = array();
		$rules = array(
			'username'=>'trim|required|min_length[5]|max_length[30]',
			'email'=>'trim|required|valid_email',
			'password'=>'required|matches[password2]',  'password2'=>'required',
			'first_name'=>'trim', 'last_name'=>'trim'
		);
		$fields = array(
			'username'   => 'Username',
			'email'	     => 'E-mail address',
			'password'   =>	'Password',
			'password2'  =>	'Password Confirmation',
			'first_name' =>	'First Name',
			'last_name'  =>	'Last Name'
		);
		$this->validation->set_rules($rules);
		$this->validation->set_fields($fields);
		if ($this->validation->run()) {
			$inp = array(
				'ez_users' => array(
					'username'   =>	$this->input->post('username'),	
					'first_name' =>	$this->input->post('first_name'),	
					'last_name'  =>	$this->input->post('last_name'),	
					'email'	     =>	$this->input->post('email')			
				),
				'ez_access_keys' => array(			
					'tarantism'	 =>	'user',
				),
				'password'  =>	$this->input->post('password')
			);
			
			$verify_yesno = ($this->input->post('verify')) ? true : false;
			$user_reg = $this->ezauth->register($inp, $verify_yesno);	
			if ($user_reg['reg_ok'] == 'yes' && $verify_yesno == true) {
				$v_code = $user_reg['code'];

				$message = '<p>To gain access, you must verify your e-mail
				address by clicking the link below or copying it and pasting it into your browser.</p><p>
				{unwrap}
				<a href="http://www.adipa.mobi/members/verify/'.$v_code.'" 
				title="Verify your e-mail address">'.$this->config->.'/members/verify/'.$v_code.'
				{/unwrap}</a></p>';
				
				$this->_send_mail($inp['ez_users']['email'], 'Verify your e-mail address!', $message);
			}
			if ($user_reg['reg_ok'] == 'yes') {
				$user = $inp['ezusers'];
				$un = $user['username'];
				
				
				$folder = get_include_path()."assets/users/".$un."/";
				if(mkdir($folder, 0777)){
					if(mkdir($folder."main/", 0777)){
						redirect('/members/reg_ok');
					}
				}
			} else {
				$data['disp_error'] = 'Error:<br />' . $user_reg['error'];
			}
		}
		$this->load->view('login/register_view', $data);
	}
	
	public function reg_ok() {
		$this->load->view('login/reg_ok_view');
	}
	
	public function _send_mail($to, $subject, $message, $from = NULL) {
		$config['mailtype'] = 'html';
		$config['protocol'] = 'sendmail';
		$this->email->initialize($config);
		if(is_null($from)){
			$this->email->from($this->responseEmail);
		} else {
			$this->email->from($from);
		}
		$this->email->to($to);
		$this->email->subject($subject);
		$this->email->message($message);	
		$this->email->send();
	}
	
	public function logout() {
		$this->ezauth->logout();
		redirect('/members');
	}
	
	public function verify() {
		if ($this->ezauth->verify_email($this->uri->segment(3)) == true) {
			$this->load->view('login/verify_ok');
		} else {
			redirect('/members');
		}
	}
	
	public function forgotpw1() {
		$data = array();
		$fields = array( 'username' => 'trim', 'email' => 'trim' );
		$rules = array( 'username' => 'User name', 'email' => 'E-mail address' );
		$this->validation->set_rules($rules);
		$this->validation->set_fields($fields);
		if ($this->validation->run()) {
			$user = $this->ezauth->get_userid($this->input->post('username'), $this->input->post('email'));
			$usr = $this->ezauth->get_reset_code($user['user_id']);
			$message = auto_link('here is your reset code: http://www.adipa.mobi/members/forgotpw2/'.$usr['reset_code']);
			$this->_send_mail($usr['email'], 'Reset Code', $message);
			$data['disp_message'] = 'A reset code was sent to your e-mail address. Check your e-mail!';
		}
		$this->load->view('login/forgotpw1', $data);
	}
	
	
	
	public function forgotpw2() {
		$reset_code = $this->uri->segment(3);
		if (empty($reset_code)) return false;
		$usr = $this->ezauth->reset_password($reset_code);
		$message = 'Username: '.$usr['username']. '. Here is your temporary password: '.$usr['temp_pw'];
		$this->_send_mail($usr['email'], 'Temporary Password', $message);
		$data['disp_message'] = 'Your temporary password was e-mailed to you. Check your e-mail!';
		$this->load->view('login/forgotpw2', $data);
	}
	
	public function changepw() {
		$data = array();
		$un = $this->ezauth->user->username;
		if ($un == 'admin' || $un == 'client') {
			$data['disp_error'] = 'You can\'t be logged in as "admin" or "client" when trying to change an account password.';
			$this->load->view('login/forgotpw2',$data);
			return;
		}
		$rules = array('old_password'=>'required','new_password'=>'required|matches[new_password2]','new_password2'=>'required');
		$fields = array('old_password'=>'Old Password','new_password'=>'New Password','new_password2'=>'Confirm New Password');
		$this->validation->set_fields($fields);
		$this->validation->set_rules($rules);
		if ($this->validation->run()) {
			$result = $this->ezauth->change_pw($this->ezauth->user->id, $this->input->post('old_password'), $this->input->post('new_password'));
			if ($result) $data['disp_message'] = 'Password changed!'; else $data['disp_message'] = 'Password not changed.';
		}		
		$this->load->view('login/changepw_view', $data);
	}

public function WriteJsConnect($User, $Request, $ClientID, $Secret, $Secure = TRUE) {
	   $User = array_change_key_case($User);
	   define('JS_TIMEOUT', 24 * 60);
	   // Error checking.
	   if ($Secure) {
	      // Check the client.
	      if (!isset($Request['client_id']))
		 $Error = array('error' => 'invalid_request', 'message' => 'The client_id parameter is missing.');
	      elseif ($Request['client_id'] != $ClientID)
		 $Error = array('error' => 'invalid_client', 'message' => "Unknown client {$Request['client_id']}.");
	      elseif (!isset($Request['timestamp']) && !isset($Request['signature'])) {
		 if (is_array($User) && count($User) > 0) {
		    // This isn't really an error, but we are just going to return public information when no signature is sent.
		    $Error = array('name' => $User['name'], 'photourl' => @$User['photourl']);
		 } else {
		    $Error = array('name' => '', 'photourl' => '');
		 }
	      } elseif (!isset($Request['timestamp']) || !is_numeric($Request['timestamp']))
		 $Error = array('error' => 'invalid_request', 'message' => 'The timestamp parameter is missing or invalid.');
	      elseif (!isset($Request['signature']))
		 $Error = array('error' => 'invalid_request', 'message' => 'Missing  signature parameter.');
	      elseif (($Diff = abs($Request['timestamp'] - $this->JsTimestamp())) > JS_TIMEOUT)
		 $Error = array('error' => 'invalid_request', 'message' => 'The timestamp is invalid.');
	      else {
		 // Make sure the timestamp hasn't timed out.
		 $Signature = md5($Request['timestamp'].$Secret);
		 if ($Signature != $Request['signature'])
		    $Error = array('error' => 'access_denied', 'message' => 'Signature invalid.');
	      }
	   }
	   
	   if (isset($Error)) $Result = $Error;
	   elseif (is_array($User) && count($User) > 0)  $Result = ($Secure === NULL) ? $User : $this->SignJsConnect($User, $ClientID, $Secret, TRUE);
	   else $Result = array('name' => '', 'photourl' => '');
	   
	   $Json = json_encode($Result);
	   
	   echo (isset($Request['callback'])) ? "{$Request['callback']}($Json)" : $Json;
	}
	
	public function SignJsConnect($Data, $ClientID, $Secret, $ReturnData = FALSE) {
	   $Data = array_change_key_case($Data);
	   ksort($Data);
	
	   foreach ($Data as $Key => $Value) {
	      if ($Value === NULL)
		 $Data[$Key] = '';
	   }
	   
	   $String = http_build_query($Data);
	//   echo "$String\n";
	   $Signature = md5($String.$Secret);
	   
	   if ($ReturnData) {
	      $Data['client_id'] = $ClientID;
	      $Data['signature'] = $Signature;
	//      $Data['string'] = $String;
	      return $Data;
	   } else {
	      return $Signature;
	   }
	}
	
	public function JsTimestamp() {
	   return time();
	}

	
}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */
