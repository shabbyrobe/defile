<?php
namespace Defile;

class Buffer extends Util\Object
{
    public $len;
    public $data;

    public function __construct($data='')
    {
        $this->data = $data;
        $this->len = $data ? strlen($data) : 0;
    }
}
