<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class Brute_force_protector
{
    private $CI;
    public static $cookieName = 'ds_client_id';
    private static $tableName = 'user_login_failed';
    private static $maxTry = 3;
    private static $availableAfter = 60 * 60 * 24; // 1 day
    private $attemptData = null;

    public function __construct()
    {
        $this->CI =& get_instance();
    }

    public function getConfigMaxTry()
    {
        return self::$maxTry;
    }

    public function get_client_ip()
    {
        if(isset($_SERVER['REMOTE_ADDR'])) return $_SERVER['REMOTE_ADDR'];
        if(getenv('REMOTE_ADDR')) return getenv('REMOTE_ADDR');
        return 'UNKNOWN';
    }

    public function get_user_agent()
    {
        if(isset($_SERVER['HTTP_USER_AGENT'])) return $_SERVER['HTTP_USER_AGENT'];
        if(getenv('HTTP_USER_AGENT')) return getenv('HTTP_USER_AGENT');
        return 'UNKNOWN';
    }

    public function set_client_cookie_id()
    {
        $clientId = bin2hex(random_bytes(16));
        setcookie(self::$cookieName, $clientId, time() + (365 * 24 * 60 * 60), "/"); // 1 year expiration
        return $this;
    }

    public function get_client_cookie_id()
    {
        if(!isset($_COOKIE[self::$cookieName])) return null;
        return $_COOKIE[self::$cookieName];
    }

    public function is_allowed(string $username)
    {
        $clientId = $this->get_client_cookie_id();
        $clientIp = $this->get_client_ip();
        $userAgent = $this->get_user_agent();

        $this->CI->db->select('*')
            ->from(self::$tableName)
            ->where('client_ip', $clientIp)
            ->where('user_agent', $userAgent);
        if($clientId) {
            $this->CI->db->where('client_id', $clientId);
        } else {
            $this->CI->db->where('username', $username);
        }
        $this->CI->db->order_by('last_try_at', 'DESC')->limit(1);
        $this->attemptData = $this->CI->db->get()->row_array();

        if(!$this->attemptData) return true;
        if(!$this->attemptData['blocked_until']) return true;

        $isStillBlocked = time() <= strtotime($this->attemptData['blocked_until']);
        if(!$isStillBlocked) {
            $this->CI->db->where('id', $this->attemptData['id']);
            $this->CI->db->delete(self::$tableName);
        }
        return $isStillBlocked;
    }

    public function commit_failed_login(string $username)
    {
        $clientId = $this->get_client_cookie_id();
        $clientIp = $this->get_client_ip();
        $userAgent = $this->get_user_agent();

        if(!$this->attemptData) {

            $this->CI->db->select('*')
                ->from(self::$tableName)
                ->where('client_ip', $clientIp)
                ->where('user_agent', $userAgent);
            if($clientId) {
                $this->CI->db->where('client_id', $clientId);
            } else {
                $this->CI->db->where('username', $username);
            }
            $this->CI->db->order_by('last_try_at', 'DESC')->limit(1);
            $this->attemptData = $this->CI->db->get()->row_array();

        }

        if(!$this->attemptData) {

            $clientId = $this->set_client_cookie_id()->get_client_cookie_id();
            $this->attemptData = [
                'client_ip' => $clientIp,
                'user_agent' => $userAgent,
                'client_id' => $clientId,
                'username' => $username,
                'attempts_count' => 1,
                'last_try_at' => date('Y-m-d H:i:s'),
            ];
            $this->CI->db->insert(self::$tableName, $this->attemptData);
            $this->attemptData['id'] = $this->CI->db->insert_id();
            return true;

        } else {
            $this->attemptData['attempts_count'] = intval($this->attemptData['attempts_count']) + 1;
        }

        if($this->attemptData['attempts_count'] < self::$maxTry) {
            $this->CI->db
                ->where('id', $this->attemptData['id'])
                ->update($this->tableName, [ 'attempts_count' => $this->attemptData['attempts_count'] ]);
            return true;
        }

        $this->attemptData['blocked_until'] = date('Y-m-d H:i:s', time() + self::$availableAfter);
        $this->CI->db
            ->where('id', $this->attemptData['id'])
            ->update(self::$tableName, [
                'attempts_count' => $this->attemptData['attempts_count'],
                'blocked_until' => $this->attemptData['blocked_until'],
            ]);
        return false;
    }
}