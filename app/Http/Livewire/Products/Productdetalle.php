<?php

namespace App\Http\Livewire\Products;

use Livewire\Component;
use App\Models\Product;
use App\Models\Presentacion;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use App\Models\Inspectors;
use Illuminate\Support\Str;
use App\Traits\CartTrait;
use Illuminate\Support\Facades\Http;
use App\Models\Sabores;
use App\Models\Category;
use App\Models\Sizes;
use App\Models\Lotes;
class Productdetalle extends Component
{
    use CartTrait;
    public $product;
    public $name, $barcode,
    $descripcion, $saborID,
    $cost, $estado, $price,
    $stock, $alerts
    , $categoryid, $search,
    $image, $selected_id,
    $pageTitle, $componentName,
    $tam2, $tam1
    ,$size_id, $select_options_products = false,$select_options_list_products = [] ;


    public function mount($id)
    {

        $this->product = Product::find($id);
       
    }

    public function render()
    {
        $presentaciones = $this->product->presentaciones;
        $can_delete_pre = [];
        $i = 0;
        foreach($presentaciones as $pre){
            $number_lots = Lotes::where('SKU', $pre->id)->count();
            $can_delete_pre[$i] = $number_lots > 0 ? false : true ;$i++;
        }
        $this->product->can_delete_pre = $can_delete_pre;
        
        $categories = Category::orderBy('name', 'asc')->get();
        $sabores = Sabores::orderBy('nombre', 'asc')->get();
        $sizes = Sizes::orderBy('id', 'asc')->get();
        // Verifica si el producto tiene un KeyProduct definido
        if ($this->product->KeyProduct !== null) {
            // Genera el código QR solo si KeyProduct no es nulo
            $qr = QrCode::size(210)->generate($this->product->KeyProduct);
        } else {
            // En caso de que KeyProduct sea nulo, asigna null a $qr
            $qr = null;
        }

        return view('livewire.products.productdetalle', [
            'sabores' => $sabores,
            'categories' => $categories,
            'qr' => $qr,
            'sizes' => $sizes
        ])->extends('layouts.theme.app')
            ->section('content');
    }


    public function ScanCode($code)
    {
        try {
            $this->ScanearCode($code);        
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400); 
        }
    }
    public function CrearProWoo($id)
    {
        // Obtener datos del producto
        $prod = Product::find($id);
        $sku = $prod->barcode;
        $nombre = $prod->name;
        $precio = $prod->price;
        $descripcion = $prod->descripcion;
        $stock = $prod->stock;

        // Mostrar mensaje de carga
        // Mostrar Swal de carga

        $this->emit('swal-loading', 'Creando producto en WooCommerce. Por favor, espera...');

        // Crear producto en WooCommerce
        // Configurar la URL y los datos para la solicitud a la API de WooCommerce para crear el producto
        $url = 'https://kdlatinfood.com/wp-json/wc/v3/products';

        // Obtener la ruta completa de la imagen del producto
        $imagePath = public_path('storage/products/' . $prod->image);

        // Crear el array con los datos del producto
        $data = [
            'name' => $nombre,
            'sku' => $sku,
            'regular_price' => $precio,
            'stock_quantity' => $stock,
            // Otros campos del producto...
        ];

        // Agregar la imagen al array de datos
        $data['image'] = Http::attach(
            'image',
            file_get_contents($imagePath),
            $prod->image,
            [
                'Content-Type' => 'image/jpeg',
                'Content-Disposition' => 'attachment; filename=' . $prod->image
            ]
        );


        // Realizar la solicitud HTTP con los datos del producto, incluida la imagen
        $response = Http::withBasicAuth('ck_8e38a879e7f6ce0d56e34c525de194a60c2e2ce5', 'cs_723eab16e53f3607fd38984b00f763310cc4f473')
            ->post($url, $data);


        // Actualizar atributo 'EstaEnWoocomerce' a 'si'
        $prod->EstaEnWoocomerce = 'si';
        $prod->save();
        $this->product = $prod;
        // Mostrar mensaje de éxito
        $this->emit('global-msg', "SE CREO CORRECTAMENTE");
        $this->emit('producto-creado');

        // RELOAD 
        $user = Auth()->user()->name;
        $inspector = Inspectors::create([
            'user' => $user,
            'action' => 'Creo un producto en woocomerce ',
            'seccion' => 'Products'
        ]);
    }
    public function GenerateKey($id)
    {
        // Obtener el producto
        $product = Product::find($id);

        // Generar la clave aleatoria de 90 caracteres
        $key = Str::random(90);

        // Asignar la clave al campo KeyProduct
        $product->KeyProduct = $key;

        // Establecer el campo TieneKey como 'SI'
        $product->TieneKey = 'SI';

        // Guardar los cambios en la base de datos
        $product->save();
        $this->product = $product;
        

        // Imprimir la clave generada para verificar
        $this->emit('global-msg', "Key Generada Correctamente");
        $this->emit('producto-creado');
        $user = Auth()->user()->name;
        $inspector = Inspectors::create([
            'user' => $user,
            'action' => 'Creo una key de producto ',
            'seccion' => 'Products'
        ]);
    }
    public function visible($id)
    {
        // Obtener el producto
        $product = Product::find($id);
        // Asignar la clave al campo KeyProduct
        $product->visible = 'no';
        $product->save();
        $this->product = $product;
        // Imprimir la clave generada para verificar
        $this->emit('global-msg', "El producto ha sido ocultado");

        $this->emit('producto-creado');
        $user = Auth()->user()->name;
        $inspector = Inspectors::create([
            'user' => $user,
            'action' => 'oculto un producto ',
            'seccion' => 'Products'
        ]);
    }
    public function novisible($id)
    {
        // Obtener el producto
        $product = Product::find($id);



        // Asignar la clave al campo KeyProduct
        $product->visible = 'si';

        $product->save();
        $this->product = $product;
        // Imprimir la clave generada para verificar
        $this->emit('global-msg', "El producto ha sido PUBLICADO");
        $this->emit('producto-creado');
        $user = Auth()->user()->name;
        $inspector = Inspectors::create([
            'user' => $user,
            'action' => 'publico un producto ',
            'seccion' => 'Products'
        ]);
    }

    public function toggleVisibilityPresentacion($id, $visibility)
{
    // Validar si el producto existe
    $product = Presentacion::find($id);
    if (!$product) {
        $this->emit('global-msg', 'Producto no encontrado');
        return;
    }

    // Establecer el valor de visibilidad según el parámetro
    $product->visible = $visibility;
    $product->save();

    // Determinar el mensaje de visibilidad
    $visibilityMessage = $visibility === 'si' ? 'El producto ha sido PUBLICADO' : 'El producto ha sido ocultado';

    // Asignar el producto a la propiedad y emitir mensaje
    $this->product = $product->product;
    $this->emit('global-msg', $visibilityMessage);
    $this->emit('producto-creado');

    // Registrar la acción en el registro de inspectores
    $user = Auth()->user()->name;
    $action = $visibility === 'si' ? 'publicó un producto' : 'ocultó un producto';

    Inspectors::create([
        'user' => $user,
        'action' => $action,
        'seccion' => 'Products'
    ]);
}
    public function EditProduct(Product $product)
    {
        $this->select_options_list_products = $this->getProductForId($this->selected_id);
        $this->selected_id = $product->id;
        $this->name = $product->name;
        $this->barcode = $product->barcode;
        $this->cost = $product->cost;
        $this->estado = $product->estado;
        $this->descripcion = $product->descripcion;
        $this->price = $product->price;
        $this->stock = $product->stock;
        $this->alerts = $product->alerts;
        $this->categoryid = $product->category_id;
        $this->saborID = $product->sabor_id;
        $this->tam1 = $product->tam1;
        $this->tam2 = $product->tam2;
        $this->image = null;

        $this->emit('modal-show', 'Show modal');
        $this->emit('producto-creado');
    }



    public function UpdateProduct()
    {
     
        $rules = [
            'name' => "required|min:3",
            'cost' => 'required',
            'price' => 'required',
            'stock' => 'required',
            'alerts' => 'required',
            'categoryid' => 'required|not_in:Elegir'
        ];

        $messages = [
            'name.required' => 'Nombre del producto requerido',

            'name.min' => 'El nombre del producto debe tener al menos 3 caracteres',
            'cost.required' => 'El costo es requerido',
            'price.required' => 'El precio es requerido',
            'stock.required' => 'El stock es requerido',
            'alerts.required' => 'Ingresa el valor mínimo en existencias',
            'categoryid.not_in' => 'Elige un nombre de categoría diferente de Elegir',
        ];

        $this->validate($rules, $messages);

        $product = Product::find($this->selected_id);

        //   $product = Product::find($this->selected_id);

        // Verificar si se ha cambiado el campo "estado" a "PRE-COCIDO"

        // Actualizar el producto existente
        $product->update([
            'name' => $this->name,
            'cost' => $this->cost,
            'price' => $this->price,
            'barcode' => $this->barcode,
            'stock' => $this->stock,
            'estado' => $this->estado,
            'descripcion' => $this->descripcion,
            'alerts' => $this->alerts,
            'category_id' => $this->categoryid,
            'tam1' => $this->tam1,
            'tam2' => $this->tam2,
            'sabor_id' => $this->saborID,
        ]);


        if ($this->image) {
            $customFileName = uniqid() . '_.' . $this->image->extension();
            $this->image->storeAs('public/products', $customFileName);
            $imageTemp = $product->image; // imagen temporal
            $product->image = $customFileName;
            $product->save();

            if ($imageTemp != null) {
                if (file_exists('storage/products/' . $imageTemp)) {
                    unlink('storage/products/' . $imageTemp);
                }
            }
        }

        $this->product = $product;
        //$this->updateWooCommerceStock($this->barcode, $this->stock);

        $user = Auth()->user()->name;
        $inspector = Inspectors::create([
            'user' => $user,
            'action' => 'Actualizo : ' . $this->name,
            'seccion' => 'Products'
        ]);
        $this->resetUI();
        $this->emit('product-updated', 'Producto Actualizado');
        $this->emit('global-msg', 'Producto Actualizado');
        $this->emit('producto-creado');
    }
    public function resetUI()
    {
        $this->name = '';
        $this->barcode = '';
        $this->cost = '';
        $this->price = '';
        $this->stock = '';
        $this->alerts = '';
        $this->search = '';
        $this->descripcion = '';
        $this->categoryid = 'Elegir';
        $this->image = null;
        $this->saborID = 'Elegir';
        $this->estado = 'Elegir';
        $this->selected_id = 0;
        $this->resetValidation();
    }

    protected $listeners = [
        'deletePre' => 'DestroyPresentacion',
    ];

    public function DestroyPresentacion(Presentacion $presentacion)
    {
        $product = $presentacion->product;
        $user = Auth()->user()->name;
        $presentacion->delete();
        $this->resetUI();
        $this->product = $product;
        $this->emit('product-deleted', 'Presentacion Eliminado');
        $this->emit('global-msg', 'Presentacion Eliminado');
        $inspector = Inspectors::create([
            'user' => $user,
            'action' => 'Elimninó una Presentacion ',
            'seccion' => 'Presentacion'
        ]);
        
    }
    public function GenerateKeyPresentacion($id)
    {

        // Obtener el producto
        $presentacion = Presentacion::find($id);
        // Generar la clave aleatoria de 90 caracteres
        $key = Str::random(90);

        // Asignar la clave al campo KeyProduct
        $presentacion->KeyProduct = $key;

        // Establecer el campo TieneKey como 'SI'
        $presentacion->TieneKey = 'SI';

        // Guardar los cambios en la base de datos
        $presentacion->save();
        $producto = $presentacion->product;
        $this->product = $producto;
        //$this->showPresentations($id);
        // Imprimir la clave generada para verificar
        $this->emit('global-msg', "Key Generada Correctamente");
        $this->emit('producto-creado');
        $user = Auth()->user()->name;
        $inspector = Inspectors::create([
            'user' => $user,
            'action' => 'Creo una key de producto ',
            'seccion' => 'Products'
        ]);
        
    }
    public function Destroy(Product $product)
    {
        $user = Auth()->user()->name;
        if ($product->estado == 'PRECOCIDO') {
            $originalProduct = Product::where('barcode', $product->barcode - 1)->first();

            if ($originalProduct && $originalProduct->image == $product->image) {
                // La imagen está asociada solo con el producto "PRECOCIDO"
                // No se debe eliminar la imagen
            } else {
                // La imagen está asociada a otro producto o no existe
                // Se puede eliminar la imagen
                if ($product->image != null) {
                    if (file_exists('storage/products/' . $product->image)) {
                        unlink('storage/products/' . $product->image);
                    }
                }
            }
        } else {
            // Producto no es "PRECOCIDO"
            // Se puede eliminar la imagen asociada
            if ($product->image != null) {
                if (file_exists('storage/products/' . $product->image)) {
                    unlink('storage/products/' . $product->image);
                }
            }
        }

        $product->delete();

        $this->resetUI();
        $this->emit('product-deleted', 'Producto Eliminado');
        $this->emit('global-msg', 'Producto Eliminado');
        $inspector = Inspectors::create([
            'user' => $user,
            'action' => 'Elimnino un producto ',
            'seccion' => 'Products'
        ]);
    }
}
