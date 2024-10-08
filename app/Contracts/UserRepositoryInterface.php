<?php

namespace App\Contracts;

interface UserRepositoryInterface
{
    public function all();

    public function find(array $filters = []);

    public function create(array $data);

    public function update(array $data, int $id);

    public function delete(int $id);
}
