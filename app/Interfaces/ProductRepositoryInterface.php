<?php

namespace App\Interfaces;

interface ProductRepositoryInterface
{
    public function getAllByTenant(int $tenantId, ?string $search = null, ?int $categoryId = null, ?int $perPage = null);

    public function findById(int $id, array $with = []);

    public function create(array $data);

    public function update(int $id, array $data);

    public function delete(int $id);

    public function getNextCode(int $tenantId): string;
}
