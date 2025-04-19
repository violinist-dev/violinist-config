<?php

namespace Violinist\Config;

class ExtendsChainItem
{
    private $name;
    private $key;
    private $value;

    public function __construct(string $extends_name, $key, $value)
    {
        $this->name = $extends_name;
        $this->key = $key;
        $this->value = $value;
    }

    public function getName() : string
    {
        return $this->name;
    }
    public function getKey()
    {
        return $this->key;
    }
}
