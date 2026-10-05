<?php

namespace Core;

class Model
{
    protected \PDO $db;

    public function __construct()
    {
        $this->db = pdo();
    }
}
