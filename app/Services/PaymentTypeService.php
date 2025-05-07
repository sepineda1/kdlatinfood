<?php
namespace App\Services;

use Carbon\Carbon;
use App\Models\CatalogoPaymentType;
use App\Models\CatalogoDeliveryType;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Collection;
use App\Contracts\PaymentTypeServiceInterface;

class PaymentTypeService implements PaymentTypeServiceInterface
{
    public function getAll(): Collection
    {
        return CatalogoPaymentType::all();
    }

    public function getById(int $id): ?CatalogoPaymentType
    {
        return CatalogoPaymentType::find($id);
    }
}

