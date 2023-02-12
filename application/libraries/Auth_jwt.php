<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class Auth_jwt
{
    private $CI;
    private $key = 'juarayya_densus_jwt_KEY@08/02/2023/11:20_inikunciutkJWTnya_ci3_vue3';
    private $alg = 'HS256';
    private $expireTime = 24 * 60 * 7;

    public $id;
    public $name;
    public $role;
    public $level;
    public $location;
    public $locationId;

    public function __construct()
    {
        $this->CI =& get_instance();
    }
    
    public function create_token()
    {
        $payload = $this->get_payload();
        if(!$payload) {
            return null;
        }
        
        $payload['expiredAt'] = time() + $this->expireTime;
        $token = JWT::encode($payload, $this->key, $this->alg);
        return $token;
    }

    public function auth(...$allowedRole)
    {
        $rawToken = $this->CI->head('Authorization');
        dd($this->CI->head('Authorization'));
        if(!$this->is_role($rawToken, $allowedRole)) {
            
            return REST_ERR_AUTH_CODE;

        } elseif($this->is_expired($token)) {

            return REST_ERR_EXP_CODE;
            
        } else {
            
            return 200;

        }
    }

    public function is_role($rawToken, $allowedRole)
    {
        $token = $this->validate_token($rawToken);
        if($token === false) return false;

        $payload = $this->decrypt_token($token);
        return !$payload->role ? false : in_array($payload->role, $allowedRole);
    }

    public function is_expired($rawToken)
    {
        $token = $this->validate_token($rawToken);
        if($token === false) return false;

        $payload = $this->decrypt_token($token);
        return $payload->expiredAt < time();
    }

    private function validate_token($rawToken)
    {
        list($type, $data) = explode(" ", $rawToken, 2);
        return (strcasecmp($type, "Bearer") == 0) ? $data : false;
    }

    private function decrypt_token($token)
    {
        $key = new Key($this->key, $this->alg);
        try{
            $decoded = JWT::decode($token, $key);
            return $decoded;
        } catch(Exception $e) {
            return null;
        }
    }

    public function get_payload()
    {
        if(!$this->id) return null;
        if(!$this->name) return null;
        if(!$this->role) return null;
        if(!$this->level) return null;
        if(!$this->location) return null;
        if(!$this->locationId) return null;

        return [
            'id' => $this->id,
            'name' => $this->name,
            'role' => $this->role,
            'level' => $this->level,
            'location' => $this->location,
            'locationId' => $this->locationId,
        ];
    }
}