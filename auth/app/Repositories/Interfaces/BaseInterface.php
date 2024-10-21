<?php

namespace App\Repositories\Interfaces;

interface BaseInterface
{
    public function all();

    public function create(array $data);

    public function update(string $id, array $data);

    public function destroy(string $id);

    public function getById(string $id);
}
