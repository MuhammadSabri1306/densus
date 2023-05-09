<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class Auth_jwt
{
    private $CI;
    private $key = 'juarayya_densus_jwt_KEY@08/02/2023/11:20_inikunciutkJWTnya_ci3_vue3';
    private $alg = 'HS256';
    private $expireTime = 60 * 60 * 24 * 2;

    public $cookieName;

    public $id;
    public $username;
    public $name;
    public $role; // admin / viewer / teknisi
    public $level; // nasional / divre / witel
    public $location; // nasional / divre_name / witel_name
    public $locationId; // null / divre_code / witel_code

    public function __construct()
    {
        $this->CI =& get_instance();
    }

    private function verify_token($rawToken)
    {
        list($type, $data) = explode(" ", $rawToken, 2);
        return (strcasecmp($type, "Bearer") == 0) ? $data : false;
    }

    private function validate_token($rawToken, $allowedRole)
    {
        if(!$this->is_role($rawToken, $allowedRole)) {
            return REST_ERR_AUTH_CODE;
        }
        
        if($this->is_expired($rawToken)) {
            return REST_ERR_EXP_CODE;
        }

        $payload = $this->decrypt_token(str_replace('Bearer ', '', $rawToken));
        if($payload) {
            $this->id = $payload->id;
            $this->username = $payload->username;
            $this->name = $payload->name;
            $this->role = $payload->role;
            $this->level = $payload->level;
            $this->location = $payload->location;
            $this->locationId = $payload->locationId;
        }
        
        return 200;
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
        return $this->validate_token($rawToken, $allowedRole);
    }

    public function is_role($rawToken, $allowedRole)
    {
        $token = $this->verify_token($rawToken);
        if($token === false) return false;

        $payload = $this->decrypt_token($token);
        return !$payload->role ? false : in_array($payload->role, $allowedRole);
    }

    public function is_expired($rawToken)
    {
        $token = $this->verify_token($rawToken);
        if($token === false) return false;

        $payload = $this->decrypt_token($token);
        return $payload->expiredAt < time();
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
        if(!$this->username) return null;
        if(!$this->name) return null;
        if(!$this->role) return null;
        if(!$this->level) return null;

        return [
            'id' => $this->id,
            'username' => $this->username,
            'name' => $this->name,
            'role' => $this->role,
            'level' => $this->level,
            'location' => $this->location,
            'locationId' => $this->locationId,
        ];
    }
}