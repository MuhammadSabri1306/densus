<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Input_custom
{
    private $CI;

    private $fields = [];
    private $fieldTypes = ['string', 'bool', 'int', 'double', 'float'];

    private $hash_algo = PASSWORD_DEFAULT;
    private $body = [];

    public function __construct()
    {
        $this->CI =& get_instance();
    }

    private function get_input($method, $key)
    {
        if($method == 'get') {
            return $this->CI->input->get($key);
        }

        if($method == 'post') {
            return $this->CI->post($key);
        }

        if($method == 'put') {
            $val = $this->CI->put($key);
            return $val;
        }

        return null;
    }

    public function set_fields($fields)
    {
        foreach($fields as $name => $options) {
            $opt = [
                'required' => in_array('required', $options),
                'nullable' => in_array('nullable', $options),
                'hash' => in_array('hash', $options)
            ];

            $fieldType = array_intersect($options, $this->fieldTypes);
            $opt['type'] = count($fieldType) > 0 ? $fieldType[0] : null;
            
            $this->fields[$name] = $opt;
        }
    }

    public function get_body($method, $resetBody = true)
    {
        if($resetBody) $this->body = [];
        
        foreach($this->fields as $key => $attr) {

            if(!$resetBody && isset($this->body[$key])) {
                $val = $this->body[$key];
            } else {
                $val = $this->get_input($method, $key);
            }

            switch($attr['type']) {
                case 'string': $val = $this->to_string($val); break;
                case 'bool': $val = $this->to_bool($val); break;
                case 'int': $val = $this->to_numeric('int', $val); break;
                case 'double': $val = $this->to_numeric('double', $val); break;
                case 'float': $val = $this->to_numeric('float', $val); break;
            }

            $is_empty = empty($val) && $val !== 0;
            if($attr['required'] && $is_empty) {
                return [
                    'valid' => false,
                    'msg' => $this->getErrMessage($key, 'required'),
                    'body' => []
                ];
            }

            if(!is_null($val) || $attr['nullable']) {
                if($attr['hash']) $val = password_hash($val, $this->hash_algo);
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

    public function getErrMessage($key, $type = 'required')
    {
        if($type == 'required') {
            return "Field '$key' tidak dapat dikosongkan.";
        }
        return '';
    }

    private function to_string($val)
    {
        if(is_string($val) && strlen($val) > 0) return $val;
        if(!is_null($val)) return strval($val);
        return null;
    }

    private function to_bool($val)
    {
        if($val === false || (int) $val === 0) return 0;
        if($val === true || (int) $val === 1) return 1;
        return null;
    }

    private function to_numeric($dataType, $val)
    {
        if(is_string($val) && strlen($val) < 1) return null;
        if(is_null($val)) return null;
        if($dataType == 'int') return (int) $val;
        if($dataType == 'double') return (double) $val;
        if($dataType == 'float') return (float) $val;
        return $val;
    }

    private function reset_fields()
    {
        $this->fields = [];
    }

}