<?php
/**
 * vBulletin-PHP, An easy to use PHP class for providing vBulletin functions
 * Copyright (C) 2012 Nikki <nikki@nikkii.us>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

require_once "modules/vBulletinModule.php";

/**
 * A simple class which contains POST/GET/login functions for a vBulletin forum
 * @author Nikki
 */
class vBForumFunctions {

	/**
	 * Variables, like the base forum URL, whether we're logged in and the security token
	 */
	var $url;
	var $loggedin;
	var $cookiefile;
	var $securitytoken;
    var $session;
	
	/**
	 * Construct a new instance with the URL
	 */
	public function __construct($url, $cookiefile = "jar.txt") {
		$this->url = $url;
		$this->loggedin = file_exists($cookiefile); //TODO some kind of verification of the cookie file...
		$this->cookiefile = $cookiefile;
		$this->loadModules();
	}
	
	/**
	 * Request a page, simple CURL
	 */
	public function request($page, $data=array(), $overridelogin=false, $info=false, $multipart_form=false) {
		$ch = curl_init($this->url.$page);
		curl_setopt($ch, CURLOPT_HEADER, $info);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Expect:  "));
		//I will not deny the forums the right to block this, it can be done easily through settings.
		//You are however free to change this.
        curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (X11; Linux x86_64; rv:19.0) Gecko/20100101 Firefox/19.0");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		//If we are logged in/need to login, use the cookie file
		if($this->loggedin || $overridelogin) {
			curl_setopt($ch, CURLOPT_COOKIEJAR, $this->cookiefile);
			curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookiefile);
		}
		//Check for POST data
		if(empty($data)) {
            echo "No data provided, no request will be made";
            $response = "error";
        } else {
			curl_setopt($ch, CURLOPT_POST, true);
            $fields_data = $data;
            # Passing an array to CURLOPT_POSTFIELDS will encode the data as multipart/form-data, while passing a URL-encoded string will encode the data as application/x-www-form-urlencoded.
            if (!$multipart_form) {
                $fields_data = http_build_query($data);
            }
			curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_data);

            //Execute and get the response
		    $response = curl_exec($ch);
		    if(!$response) {
			    echo "cURL threw an error: ".curl_error($ch)." (url: $page, params: ".implode(",", $data).")\n";
		    }
		    //If we want the information, including the header
		    if($info) {
			    $ret = array();
			    //Get the request info, like the header size
			    $info = curl_getinfo($ch);
			    //Split out the header
			    $ret['header'] = substr($response, 0, $info['header_size']);
			    //Split out the body
			    $ret['response'] = substr($response, $info['header_size']+1);
			    //Close our resource
			    curl_close($ch);
			    return $ret;
		    }
		    //Close our resource
		    curl_close($ch);
		}
		return $response;
	}
	
	/**
	 * Load the modules!
	 */
	public function loadModules($dir="modules") {
		$dh  = opendir($dir);
		while (false !== ($filename = readdir($dh))) {
			if(is_dir($dir . $filename)) continue; //Disabled directory, misc directories...
			if(stristr($filename, "_module.php")) {
				$modname = substr($filename, 0, strrpos($filename, "_"));
				if(!isset($this->$modname)) {
					require_once $dir . "/" . $filename;
					$classname = "Module_$modname";
					$this->$modname = new $classname($this);
				}
			}
		}
		closedir($dh);
	}
	
	/**
	 * Login, simple isn't it?
	 */
	public function login($username, $password) {
		$logindetails = array("do" => "login",
                              "vb_login_username" => $username, 
							  "vb_login_password" => "",
                              "vb_login_password_hint" => "Contrase%C3%B1a",
							  "vb_login_md5password" => md5($password),
							  "vb_login_md5password_utf" => md5($password),
							  "securitytoken" => "guest",
							  "cookieuser" => "1");
								
		$resp = $this->request("login.php", $logindetails, true);
		
		$this->loggedin = (stristr($resp, "Invalid") === false);
		
        if(preg_match("/var SESSIONURL = \"s=(.*?)&\"/", $resp, $t)) {
			$this->session = trim($t[1]);
            echo "Session: " . $this->session . "<br/>\n";
            // We need to make another request to find out security token
            // http://www.elatleta.com/foro/forum.php?s=<session>
            $params = array("s" => $this->session);
            $resp2 = $this->request("forum.php", $params, true);
            if(preg_match("/var SECURITYTOKEN = \"(.*?)\"/", $resp2, $t2)) {
			    $this->securitytoken = trim($t2[1]);
                echo "Security token: " . $this->securitytoken . "<br/>\n";
		    } else {
			    echo "WARNING: No security token\n";
		    }
		} else {
			echo "WARNING: No session found!\n";
		}
		return $this->loggedin;
	}
	
	/**
	 * Get the basic params, securitytoken is used in most functions
	 */
	public function getParams() {
		return array("securitytoken" => $this->securitytoken,
                     "s" => $this->session);
	}
}
?>
