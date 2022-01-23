<?php 

    class Login
    {
        public static function logado(){
            return isset($_SESSION['login']) ? true : false;
        }

        public static function loggout(){
            include_once 'config.php';

            logout($revokeURL, array(
                'token' => session('access_token'),
                'token_type_hint' => 'access_token',
                'client_id' => OAUTH2_CLIENT_ID,
                'client_secret' => OAUTH2_CLIENT_SECRET,
              ));
            unset($_SESSION['access_token']);
            unset($_SESSION['cargo']);
            unset($_SESSION['img']);
            unset($_SESSION['login']);
            unset($_SESSION['user']);
            unset($_SESSION['email']);
            unset($_SESSION['id']);
            unset($_SESSION['redirect']);
            header('Location: ' . $_SERVER['PHP_SELF']);
            die();
        }
       
    }
?>