<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Input_handler
{
    private $CI;

    private $fields = [];
    private $required = [];
    private $is_boolean = [];
    private $is_hashstring = [];

    private $hash_algo = PASSWORD_DEFAULT;
    private $body = [];

    public function __construct()
    {
        $this->CI =& get_instance();
    }

    public function set_fields(...$fields)
    {
        // foreach($fields as $item) {
        //     array_push($this->fields, $item);
        // }
        $this->fields = $fields;
    }
    
    public function set_required(...$fields)
    {
        // foreach($fields as $item) {
        //     array_push($this->required, $item);
        // }
        $this->required = $fields;
    }

    public function set_boolean_fields(...$fields)
    {
        // foreach($fields as $item) {
        //     array_push($this->is_boolean, $item);
        // }
        $this->is_boolean = $fields;
    }

    public function set_hashstring_fields(...$fields)
    {
        // foreach($fields as $item) {
        //     array_push($this->is_hashstring, $item);
        // }
        $this->is_hashstring = $fields;
    }

    private function get_input($type, $key)
    {
        if($type == 'post') {
            return $this->CI->post($key);
        }

        if($type == 'put') {
            return $this->CI->put($key);
        }

        return null;
    }

    public function get_body($type)
    {
        foreach($this->fields as $key) {
            $val = $this->get_input($type, $key);
            if(in_array($key, $this->is_boolean)) {
                $val = boolval($val);
            } elseif(is_numeric($val)) {
                $val = count(explode('.', $val)) > 1 ? (double) $val : (int) $val;
            }

            // if($key == 'is_ldap') {
            //     dd($val);
            // }

            $is_empty = empty($val) && $val !== 0;
            if(in_array($key, $this->required) && $is_empty) {
                return [
                    'valid' => false,
                    'msg' => "The field '$key' is required.",
                    'body' => []
                ];
            }
            
            if(!$is_empty) {
                $val = in_array($key, $this->is_hashstring) ? password_hash($val, $this->hash_algo) : $val;
                $this->body[$key] = $val;
            }
        }

        $body = $this->body;
        $this->reset_fields();

        return [
            'valid' => true,
            'msg' => null,
            'body' => $body
        ];
    }

    private function reset_fields()
    {
        $this->fields = [];
        $this->required = [];
        $this->is_boolean = [];
        $this->is_hashstring = [];
    }
}