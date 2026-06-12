<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;

class UserModel
{
    protected $table = 'users';

    // Get user by email
    public function getByEmail($email)
    {
        return DB::table($this->table)
            ->where('email', $email)
            ->where('is_deleted', 0)
            ->first();
    }

    // Get all users
    public function getAll($role = "")
    {
        $query = DB::table($this->table);
        if (!empty($role)) {
            $query->where('role', $role);
        }
        $query =  $query->where('is_deleted', 0)
            ->get();
        return $query;
    }

    // Insert user
    public function insertUser($data)
    {
        return DB::table($this->table)->insert($data);
    }

    // Update user
    public function updateUser($id, $data)
    {
        return DB::table($this->table)
            ->where('id', $id)
            ->update($data);
    }

    // Soft delete
    public function deleteUser($id)
    {
        return DB::table($this->table)
            ->where('id', $id)
            ->update(['is_deleted' => 1]);
    }

    public function getUsersDataTable($start, $length, $search)
    {
        $query = DB::table($this->table)
            ->leftJoin('roles', 'users.role', '=', 'roles.id')
            ->select('users.id', 'users.name', 'users.email', 'users.role', 'roles.name as role_name')
            ->where('users.is_deleted', 0);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('users.name', 'like', "%$search%")
                    ->orWhere('users.email', 'like', "%$search%");
            });
        }

        return $query->offset($start)
            ->limit($length)
            ->get();
    }

    public function countAll()
    {
        return DB::table($this->table)
            ->where('is_deleted', 0)
            ->count();
    }

    public function countFiltered($search)
    {
        $query = DB::table($this->table)
            ->where('is_deleted', 0);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                    ->orWhere('email', 'like', "%$search%");
            });
        }

        return $query->count();
    }
}
