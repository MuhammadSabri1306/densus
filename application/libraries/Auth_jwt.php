<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class Auth_jwt
{
    private $key = 'juarayya_densus_jwt_KEY@08/02/2023/11:20_inikunciutkJWTnya_ci3_vue3';
    private $alg = 'HS256';

    public $userId;
    public $name;
    public $isOrganik;
    public $area;
    public $role;
    
    public function create_token($params)
    {
        $payload = $this->get_payload();
        if(!$payload) {
            return null;
        }

        $token = JWT::encode($payload, $this->key, $this->alg);
        return $token;
    }

    public function isRole($token, $allowedRole)
    {
        $payload = $this->decrypt_token($token);
        if(!$payload->role) {
            return false;
        }
        
        return in_array($payload->role, $allowedRole);
    }

    public function decrypt_token($token)
    {
        $key = new Key($this->key, $this->alg);
        $decoded = JWT::decode($token, $key);
        return $decoded;
    }

    private function get_payload()
    {
        if(!$this->userId) return null;
        if(!$this->name) return null;
        if(!$this->isOrganik) return null;
        if(!$this->area) return null;
        if(!$this->role) return null;

        return [
            'userId' => $this->userId,
            'name' => $this->name,
            'isOrganik' => $this->isOrganik,
            'area' => $this->area,
            'role' => $this->role
        ];
    }
}