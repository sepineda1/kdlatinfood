<?php

namespace App\Http\Livewire\Component;

use Livewire\Component;
use App\Models\Sale;
use App\Models\SaleDetail;
use App\Models\Presentacion;
use App\Models\Customer;
use App\Models\Discounts;
use Illuminate\Support\Facades\DB;
use App\Models\Inspectors;
use App\Traits\SaleTrait;
use Exception;

class EditSaleComponent extends Component
{
    use SaleTrait;
    public $saleId;
    public $saleData;
    public $detailsEdit = [];
    public $quantities = [];
    public $addProduct = false;
    public $showModalSaldo = false;
    public $newProducts = [
        'sku' => '',
        'items' => 0,
        'price' => 0,
        'discount' => 0,
    ];
    public $TotalCash = 0;

    public $selectedProducts = [];

    protected $listeners = [
        'removeProduct' => 'removeProduct',
        'decrementarCantidad' => 'minusProductToSaleButton',
        'aumentarCantidad' => 'addProductToSaleButton',
        'openEditModal' => 'loadSale',
        'confirm-cash-order-automatic' => 'confirmCashOrder',
        'confirm-decrement-after-delete' => 'confirmDecrementAfterDelete',
    ];

    public function loadSale($id)
    {
        $this->saleId = $id;
        $this->saleData = Sale::with('customer')->findOrFail($id);
        $this->detailsEdit = SaleDetail::where('sale_id', $id)->get();
        $this->quantities = $this->detailsEdit->pluck('quantity')->toArray();
        $this->emit('producto-creado');
        $this->dispatchBrowserEvent('showEditModal');
    }

    public function toggleAddProduct()
    {
        $this->addProduct = !$this->addProduct;
        $this->emitSelf('keepModalOpen');
    }


    public function getProductIdBySku($sku)
    {
        // Busca el producto por SKU en la base de datos
        $product = Presentacion::where('barcode', $sku)->first();

        // Si se encuentra el producto, devuelve su ID; de lo contrario, devuelve null
        if ($product) {
            return $product->id;
        } else {
            return null;
        }
    }

    public function addProductRow($isCash = false)
    {
        // Valida que tengas una venta seleccionada
        if (!$this->saleId) {
            return;
        }
        if (!$this->newProducts['sku']) {
            $this->emit('sale-error', 'Selecciona una Presentación.');
            return;
        }

        if ($this->newProducts['items'] < 1) {
            $this->emit('sale-error', 'La cantidad debe ser mayor a cero.');
            return;
        }

        $presentacion_id = $this->getProductIdBySku($this->newProducts['sku']);
        $pre = Presentacion::find($presentacion_id);
        if ($pre->visible === "no") {
            $this->emit('sale-error', 'La presentacion NO está disponible.');
            return;
        }

        $Sale = Sale::findOrFail($this->saleId);
        if ($isCash) {
            if ($Sale->cash != 0) {
                $discount = Discounts::where('presentacion_id', $pre->id)->where('customer_id', $Sale->CustomerID)->first();
                $discount = $discount ? $discount->discount : 0;
                $price = $pre->price - ($discount * $pre->price) / 100;
                $this->TotalCash = $price * $this->newProducts['items'];
                $this->emit('producto-creado');
                $this->dispatchBrowserEvent('showModalSaldo');
                return;
            }
        }

        $response = $this->addProductoToSaleCore($this->saleId, $this->newProducts['sku'], $this->newProducts['items'], false);
        if ($response) {
            $this->detailsEdit = SaleDetail::where('sale_id', $this->saleId)->get();
            foreach ($this->detailsEdit as $key => $detail) {
                $this->selectedProducts[$key] = $detail->product->product->products_id;
                $this->quantities[$key] = $detail->quantity;
            }
            $this->saleData = Sale::find($this->saleId);
            $this->details = SaleDetail::where('sale_id', $this->saleId)->get();
            $this->emit('global-msg', "Producto agregado a la venta");
            $this->emit('producto-creado');
            $this->dispatchBrowserEvent('hideModalSaldo');
            $this->newProducts = [
                'sku' => '',
                'name' => '',
                'items' => 0,
                'price' => 0,
                'discount' => '0.00',
                'total' => ''
            ];
        }
    }



    public function updatedNewProductsSku($value)
    {
        try {
            $this->saleData = Sale::find($this->saleId);
            $presentacion = Presentacion::where('barcode', $value)->first();
            $discount = Discounts::where('presentacion_id', $presentacion->id)->where('customer_id', $this->saleData->CustomerID)->first();


            if ($presentacion) {
                $this->newProducts['price'] = $presentacion->price;
                $this->newProducts['discount'] = isset($discount) ? $discount->discount : 0;
                //$this->newProducts['name'] = $presentacion->product->name ?? '';
            } else {
                $this->newProducts['price'] = 0;
                $this->newProducts['discount'] = 0;
                //$this->newProducts['name'] = '';
            }

        } catch (Exception $e) {
            //throw $th;
            $this->emit('sale-error', $e->getMessage());
        }


    }

    public function removeNewProduct()
    {
        // Restablecer los valores de $newProducts
        $this->newProducts = [
            'sku' => '',
            'name' => '',
            'items' => 0,
        ];

        // Ocultar el formulario de adición
        $this->addProduct = false;
    }

    // Botón '+' en cada fila
    public function addProductToSaleButton($key, $quantity = 1)
    {
        $detailId = $this->detailsEdit[$key]['id'];
        $detail = SaleDetail::findOrFail($detailId);
        $pre = Presentacion::findOrFail($detail->presentaciones_id);
        $sale = Sale::findOrFail($detail->sale_id);

        if ($sale->cash > 0) {
            $disc = Discounts::where('presentacion_id', $pre->id)
                ->where('customer_id', $sale->CustomerID)
                ->first();
            $discount = $disc ? $disc->discount : 0;
            $price = $pre->price - ($discount * $pre->price) / 100;
            $total = $price * $quantity;

            $this->emit('producto-creado');
            $text = "Para agregar este producto, Se recibe en EFECTIVO la suma de $" . number_format($total, 2);
            $this->emit('confirm-cash-order-modal', [
                'saleId' => $sale->id,
                'barcode' => $pre->barcode,
                'quantity' => $quantity,
                'text' => $text,
                'setting' => 'add',
                'saleDetailID' => $detailId,
                'hasCash' => true,
            ]);
            return;
        }
        $response = $this->addProductoToSaleCore($sale->id, $pre->barcode, $quantity, false);
        if ($response) {
            $this->detailsEdit = SaleDetail::where('sale_id', $sale->id)->get();
            foreach ($this->detailsEdit as $key => $detail) {
                $this->selectedProducts[$key] = $detail->product->product->products_id;
                $this->quantities[$key] = $detail->quantity;
            }
            $this->saleData = Sale::find($sale->id);
            $this->details = SaleDetail::where('sale_id', $sale->id)->get();
            $this->emit('global-msg', "Producto agregado a la venta");
            $this->emit('producto-creado');
            $this->dispatchBrowserEvent('hideModalSaldo');
            $this->newProducts = [
                'sku' => '',
                'name' => '',
                'items' => 0,
                'price' => 0,
                'discount' => '0.00',
                'total' => ''
            ];


        }
    }

    // Botón '-' en cada fila
    public function minusProductToSaleButton($key, $quantity = 1)
    {
        $detailId = $this->detailsEdit[$key]['id'];
        $detail = SaleDetail::findOrFail($detailId);
        $sale = Sale::findOrFail($detail->sale_id);
        $pre = Presentacion::findOrFail($detail->presentaciones_id);

        if ($sale->cash > 0) {
            $textAdd = '';
            if (($detail->quantity - $quantity) < 1 && $sale->items === $quantity) {
                $textAdd = ' Si restas esta cantidad, automaticamente se eliminará la orden.';
            }
            $price = $detail->price - ($detail->discount * $detail->price) / 100;
            $total = $price * $quantity;

            $this->emit('producto-creado');
            $text = "Para quitar este producto, Se hace la devolución en EFECTIVO la suma de $" .
                number_format($total, 2) . $textAdd;
            $this->emit('confirm-cash-order-modal', [
                'saleId' => $sale->id,
                'barcode' => $pre->barcode,
                'quantity' => $quantity,
                'text' => $text,
                'setting' => 'minus',
                'saleDetailID' => $detailId,
                'hasCash' => true,
            ]);
            return;
        }

        if (($detail->quantity - $quantity) < 1 && $sale->items === $quantity) {
            $this->emit('producto-creado');
            $this->emit('confirm-delete-order-modal', [
                'quantity' => $quantity,
                'saleDetailID' => $detailId,
                'hasCash' => false,
            ]);
            return;
        }
        $response = $this->decrementQuantityCore($quantity, $detailId, $sale->cash > 0, false);
        if ($response) {
            $this->detailsEdit = SaleDetail::where('sale_id', $sale->id)->get();
            foreach ($this->detailsEdit as $key => $detail) {
                $this->selectedProducts[$key] = $detail->product->product->products_id;
                $this->quantities[$key] = $detail->quantity;
            }
            $this->saleData = Sale::find($sale->id);
            $this->details = SaleDetail::where('sale_id', $sale->id)->get();
            $this->emit('producto-creado');
            $this->emit('global-msg', "Producto Eliminado.");
        }
    }

    public function confirmDecrementAfterDelete($data)
    {
        $Sale = SaleDetail::find($data['saleDetailID']);
        $SaleID = $Sale->id;
        $response = $this->decrementQuantityCore(
            $data['quantity'],
            $data['saleDetailID'],
            $data['hasCash'],
            false
        );
        if ($response) {
            $this->detailsEdit = SaleDetail::where('sale_id',  $SaleID)->get();
            foreach ($this->detailsEdit as $key => $detail) {
                $this->selectedProducts[$key] = $detail->product->product->products_id;
                $this->quantities[$key] = $detail->quantity;
            }
            $this->saleData = Sale::find($SaleID);
            $this->details = SaleDetail::where('sale_id',  $SaleID)->get();
            $this->emit('producto-creado');
            $this->emit('global-msg', "Producto Eliminado.");

        }
    }

    public function confirmCashOrder($data)
    {
        if ($data['setting'] === 'add') {
            $response = $this->addProductoToSaleCore(
                $data['saleId'],
                $data['barcode'],
                $data['quantity'],
                false
            );
            if ($response) {
                $this->detailsEdit = SaleDetail::where('sale_id', $data['saleId'])->get();
                foreach ($this->detailsEdit as $key => $detail) {
                    $this->selectedProducts[$key] = $detail->product->product->products_id;
                    $this->quantities[$key] = $detail->quantity;
                }
                $this->saleData = Sale::find($data['saleId']);
                $this->details = SaleDetail::where('sale_id', $data['saleId'])->get();
                $this->emit('global-msg', "Producto agregado a la venta");
                $this->emit('producto-creado');
                $this->dispatchBrowserEvent('hideModalSaldo');
                $this->newProducts = [
                    'sku' => '',
                    'name' => '',
                    'items' => 0,
                    'price' => 0,
                    'discount' => '0.00',
                    'total' => ''
                ];
            }
        } else {
            $response = $this->decrementQuantityCore(
                $data['quantity'],
                $data['saleDetailID'],
                $data['hasCash'],
                false
            );
            if ($response) {
                $this->detailsEdit = SaleDetail::where('sale_id', $data['saleId'])->get();
                foreach ($this->detailsEdit as $key => $detail) {
                    $this->selectedProducts[$key] = $detail->product->product->products_id;
                    $this->quantities[$key] = $detail->quantity;
                }
                $this->saleData = Sale::find($data['saleId']);
                $this->details = SaleDetail::where('sale_id', $data['saleId'])->get();
                $this->emit('producto-creado');
                $this->emit('global-msg', "Producto Eliminado.");
            }
        }
    }

    public function responses($content, $code, $ResponseJSON, $nameEmit = '', $params = [])
    {
        if ($ResponseJSON) {
            return response()->json(array_merge(['message' => $content], $params), $code);
        } else {
            $this->emit($nameEmit, $content);
            return;
        }
    }
    public function removeProduct($key)
    {
        if (!$this->saleId) {
            return;
        }
        $saleDetailId = $this->detailsEdit[$key]['id'];
        $saleDetail = SaleDetail::where('id', $saleDetailId)->first();
        $this->minusProductToSaleButton($key, $saleDetail->quantity);
    }

 
    public function render()
    {
        $prod = Presentacion::where('visible','si')->get();
        return view('livewire.component.edit-sale-component', ['prod' => $prod]);
    }

}