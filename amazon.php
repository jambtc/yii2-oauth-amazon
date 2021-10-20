<?php
/**
 * Ideated by Sergio Casizzone
 * User: jambtc
 * Date: 20/10/2021
 */

namespace jambtc\oauthamazon;

class amazon extends \yii\base\Widget
{
    protected $client_id;
    protected $client_secret;


    function __construct($client_id, $client_secret){
        $this->client_id = $client_id;
        $this->client_secret = $client_secret;
    }

    public function setAmazonScript(){
        return '<script type="text/javascript">
                    window.onAmazonLoginReady = function() {
                      amazon.Login.setClientId("'.$this->client_id.'");
                    };
                    (function(d) {
                        var b = d.createElement("div");
                        b.id = "amazon-root";
                        d.body.appendChild(b);

                        var a = d.createElement("script");
                        a.type = "text/javascript";
                        a.async = true;
                        a.id = "amazon-login-sdk";
                        a.src = "https://assets.loginwithamazon.com/sdk/na/login1.js";
                        d.getElementById("amazon-root").appendChild(a);
                    })(document);
                </script>';
    }

    public function loginButton($auth_url){
        return '<a href id="LoginWithAmazon">
                    <img border="0" alt="Login with Amazon"
                        src="https://images-na.ssl-images-amazon.com/images/G/01/lwa/btnLWA_gold_156x32.png"
                        width="156" height="32" />
                </a>
                <script type="text/javascript">

                (function() {
                    // your page initialization code here
                    // the DOM will be available here
                    document.getElementById("LoginWithAmazon").onclick = function() {
                        options = {}
                        options.scope = "profile";
                        options.scope_data = {
                           "profile" : {"essential": false}
                        };
                        amazon.Login.authorize(options, "'.$auth_url.'");
                        return false;
                    };
                })();
                </script>
                ';
    }

    public function logoutButton(){
        return '<a id="amazon-logout">Logout</a>

        <script type="text/javascript">
          document.getElementById("amazon-logout").onclick = function() {
             amazon.Login.logout();
          };
        </script>';
    }

    public function checkAmazonAuthorization() {
        // verify that the access token belongs to us
        $c = curl_init('https://api.amazon.com/auth/o2/tokeninfo?access_token=' . urlencode($_REQUEST['access_token']));
        curl_setopt($c, CURLOPT_RETURNTRANSFER, true);

        $r = curl_exec($c);
        curl_close($c);
        $d = json_decode($r);

        if ($d->aud != $this->client_id) {
          // the access token does not belong to us
          header('HTTP/1.1 404 Not Found');
          echo 'Page not found';
          exit;
        }

        // exchange the access token for user profile
        $c = curl_init('https://api.amazon.com/user/profile');
        curl_setopt($c, CURLOPT_HTTPHEADER, array('Authorization: bearer ' . $_REQUEST['access_token']));
        curl_setopt($c, CURLOPT_RETURNTRANSFER, true);

        $r = curl_exec($c);
        curl_close($c);
        $d = json_decode($r);

        return $d;
    }
}
