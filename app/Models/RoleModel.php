<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;

class RoleModel
{
    protected $table = 'roles';

    public function getAll()
    {
        return DB::table($this->table)->get();
    }

    public function getById($id)
    {
        return DB::table($this->table)->where('id', $id)->first();
    }
}
