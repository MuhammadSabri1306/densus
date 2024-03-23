<?php

class MY_Exceptions extends CI_Exceptions {}

class FormValidationException extends \Exception
{
    private $data;

    public function __construct(string $message = '', array $data = [])
    {
        parent::__construct($message);
        $this->data = $data;
    }

    public function getData()
    {
        return $this->data;
    }
}