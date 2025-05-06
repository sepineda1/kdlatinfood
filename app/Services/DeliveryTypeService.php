<?php

namespace App\Services;

use App\Models\DeliveryType;
use App\Models\CatalogoDeliveryType;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Collection;
use App\Contracts\DeliveryTypeServiceInterface;


class DeliveryTypeService implements DeliveryTypeServiceInterface
{

    public function find(int $id): DeliveryType
    {
        return DeliveryType::findOrFail($id);
    }
    public function add(int $saleId, int $deliveryTypeId, $date): DeliveryType
    {
        return DeliveryType::create([
            'sale_id'           => $saleId,
            'deliverytype_id'   => $deliveryTypeId,
            'date'              => $date,
        ]);
    }

    public function update(int $id, array $data): DeliveryType
    {
        $deliveryType = DeliveryType::find($id);
        if (!$deliveryType) {
            return null;
        }

        if (isset($data['date'])) {
            $data['date'] = $data['date'] instanceof \DateTime
                ? $data['date']
                : Carbon::parse($data['date']);
        }

        $deliveryType->update($data);

        return $deliveryType;
    }


    public function remove(int $id): bool
    {
        $deliveryType = DeliveryType::find($id);
        if (!$deliveryType) {
            return false;
        }
        return $deliveryType->delete();
    }


    public function getBySaleId(int $saleId): Collection
    {
        return DeliveryType::where('sale_id', $saleId)->get();
    }


    public function getByDeliveryTypeId(int $deliveryTypeId): Collection
    {
        return DeliveryType::where('deliverytype_id', $deliveryTypeId)->get();
    }

    /**
     * Obtener entregas en un rango de fechas.
     *
     * @param  \DateTime|string  $from
     * @param  \DateTime|string  $to
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getByDateRange($from, $to): Collection
    {
        $fromDate = $from instanceof \DateTime ? $from : Carbon::parse($from);
        $toDate = $to instanceof \DateTime ? $to : Carbon::parse($to);

        return DeliveryType::whereBetween('date', [$fromDate, $toDate])
            ->orderBy('date')
            ->get();
    }

    public function countBySaleId(int $saleId, ?int $deliveryTypeId = null): int
    {
        $query = DeliveryType::where('sale_id', $saleId);
        if ($deliveryTypeId) {
            $query->where('deliverytype_id', $deliveryTypeId);
        }
        return $query->count();
    }
    public function listCatalog(): Collection
    {
        return CatalogoDeliveryType::all();
    }


    public function getCatalogById(int $id): CatalogoDeliveryType
    {
        return CatalogoDeliveryType::findOrFail($id);
    }
}
