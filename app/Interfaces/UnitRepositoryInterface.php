<?php

namespace App\Interfaces;

interface UnitRepositoryInterface
{
    public function getAllByTenant(int $tenantId, ?string $search = null, ?int $perPage = null);

    public function findById(string $id, array $with = []);

    public function create(array $data);

    public function update(string $id, array $data);

    public function delete(string $id);
}
