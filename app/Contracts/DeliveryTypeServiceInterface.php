<?php
namespace App\Contracts;

use Illuminate\Database\Eloquent\Collection;
use App\Models\DeliveryType;
use App\Models\CatalogoDeliveryType;
interface DeliveryTypeServiceInterface
{
    public function find(int $id): DeliveryType;
    public function add(int $saleId, int $deliveryTypeId, string $date): DeliveryType;
    public function update(int $id, array $data) : ?DeliveryType;
    public function remove(int $id): bool;
    public function getBySaleId(int $saleId): Collection;
    public function getByDeliveryTypeId(int $deliveryTypeId): Collection;
    public function getByDateRange($from, $to): Collection;
    public function countBySaleId(int $saleId, ?int $deliveryTypeId = null): int;
    public function listCatalog(): Collection;
    public function getCatalogById(int $id) : CatalogoDeliveryType;
}
