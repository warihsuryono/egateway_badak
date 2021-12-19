<?php

namespace App\Models;

use CodeIgniter\Model;

class m_unit extends Model
{
    protected $DBGroup = 'default';
    protected $table = 'units';
    protected $primaryKey = 'id';
    protected $returnType = 'object';
    protected $useTimestamps = false;
    protected $protectFields = false;
}
