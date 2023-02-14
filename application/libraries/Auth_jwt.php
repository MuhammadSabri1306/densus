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
    public $role; // admin / viewer / teknisi
    public $level; // nasional / divre / witel
    public $location; // nasional / divre_name / witel_name
    public $locationId; // null / divre_code / witel_code

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
        if(!$this->is_role($rawToken, $allowedRole)) {

            return REST_ERR_AUTH_CODE;

        } elseif($this->is_expired($rawToken)) {

            return REST_ERR_EXP_CODE;
            
        } else {

            $payload = $this->decrypt_token(str_replace('Bearer ', '', $rawToken));
            if($payload) {
                $this->id = $payload->id;
                $this->name = $payload->name;
                $this->role = $payload->role;
                $this->level = $payload->level;
                $this->location = $payload->location;
                $this->locationId = $payload->locationId;
            }
            
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