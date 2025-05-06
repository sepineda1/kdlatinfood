<?php
namespace App\Traits;

use App\Models\Presentacion;
use App\Models\Product;
use Darryldecode\Cart\Facades\CartFacade as Cart;


trait CartTrait
{

        public function ScanearCode($barcode, $cant = 1)
        {
                try {
                        $product = Presentacion::where('barcode', $barcode)->first();
                        if ($product->visible === 'no') {
                                throw new \Exception("No se puede realizar una venta si el producto no esta disponible");
                        }
                        if ($product == null || empty($product)) {
                                //$this->emit('scan-notfound', 'El producto no está registrado*');                        
                                throw new \Exception("El producto no está registrado*");
                        } else {
                                if ($this->InCart($product->id)) {
                                        $this->IncreaseQuantity($product);
                                }
                                if ($product->stock_items < 1) {
                                        //$this->emit('no-stock', 'Stock insuficiente');                                
                                        //$this->emit('sale-error', 'Stock insuficiente');
                                        throw new \Exception("Stock insuficiente");
                                }
                                Cart::add($product->id, $product->product->name, $product->price, $cant, $product->product->imagen, $product->size->size);
                                $this->total = Cart::getTotal();
                                $this->itemsQuantity = Cart::getTotalQuantity();

                                $this->emit('producto-creado');
                                $this->emit('global-msg', "Se agrego el producto al carrito ");

                        }
                } catch (\Exception $e) {
                        $this->emit('sale-error', $e->getMessage());
                }

        }


        public function InCart($productId)
        {
                $exist = Cart::get($productId);
                if ($exist)
                        return true;
                else
                        return false;
        }


        public function IncreaseQuantity($product, $cant = 1)
        {
                try {
                        $title = '';


                        $exist = Cart::get($product->id);
                        if ($exist)
                                $title = 'Cantidad actualizada*';
                        else
                                $title = "Producto agregado*";

                        if ($exist) {
                                if ($product->stock_items < ($cant + $exist->quantity)) {
                                        $this->emit('no-stock', "Stock insuficiente*");
                                        return;
                                }
                        }


                        //        Cart::add($product->id, $product->name, $product->price, $cant, $product->image);
                        Cart::add($product->id, $product->product->name, $product->price, $cant, $product->product->imagen, $product->size->size);

                        $this->total = Cart::getTotal();
                        $this->itemsQuantity = Cart::getTotalQuantity();

                        $this->emit('scan-ok', $title);
                } catch (\Exception $th) {
                        //throw $th;
                        $this->emit('scan-ok', $th->getMessage());
                }

        }


        public function updateQuantity($product, $cant = 1)
        {
                $title = '';
                //$product = Product::find($productId);
                $exist = Cart::get($product->id);
                if ($exist)
                        $title = 'Cantidad actualizada*';
                else
                        $title = "Producto agregado {$product->id}*";


                if ($exist) {
                        if ($product->stock_items < $cant) {
                                $this->emit('no-stock', 'Stock insuficiente *');
                                return;
                        }
                }


                $this->removeItem($product->id);

                if ($cant > 0) {
                        Cart::add($product->id, $product->product->name, $product->price, $cant, $product->product->imagen);
                        //Cart::add($product->id, $product->name, $product->price, $cant, $product->image);                        

                        $this->total = Cart::getTotal();
                        $this->itemsQuantity = Cart::getTotalQuantity();

                        $this->emit('scan-ok', $title);

                }


        }

        public function removeItem($productId)
        {
                Cart::remove($productId);
                $this->totalDescuento = 0;
                $this->total = Cart::getTotal();
                $this->itemsQuantity = Cart::getTotalQuantity();
                $this->render();
                $this->emit('scan-ok', 'Producto eliminado*');

        }

        public function decreaseQuantity($productId)
        {
                try {
                        $item = Cart::get($productId);
                        Cart::remove($productId);

                        // si el producto no tiene imagen, mostramos una default
                        $img = (count($item->attributes) > 0 ? $item->attributes[0] : Product::find($productId)->imagen);

                        $newQty = ($item->quantity) - 1;

                        if ($newQty > 0)
                                Cart::add($item->id, $item->name, $item->price, $newQty, $img);


                        $this->total = Cart::getTotal();
                        $this->itemsQuantity = Cart::getTotalQuantity();
                        $this->totalDescuento = 0;
                        $this->render();
                        $this->emit('scan-ok', 'Cantidad actualizada*');
                } catch (\Exception $th) {
                        //throw $th;
                        $this->emit('scan-ok', $th->getMessage());
                }


        }

        public function trashCart()
        {
                Cart::clear();
                $this->efectivo = 0;
                $this->change = 0;
                $this->total = Cart::getTotal();
                $this->itemsQuantity = Cart::getTotalQuantity();

                $this->emit('scan-ok', 'Carrito vacío*');

        }

        public function updatePrice($productId, $newPrice)
        {
                $item = Cart::get($productId);

                if (!$item) {
                        $this->emit('sale-error', 'El producto no está en el carrito');
                        return;
                }

                // Eliminamos el ítem del carrito
                Cart::remove($productId);

                // Volvemos a agregar el producto con el nuevo precio, manteniendo la cantidad y los atributos
                Cart::add($productId, $item->name, $newPrice, $item->quantity, $item->attributes);

                // Actualizamos totales
                $this->total = Cart::getTotal();
                $this->itemsQuantity = Cart::getTotalQuantity();

                //$this->emit('scan-ok', 'Precio actualizado correctamente');
        }


}