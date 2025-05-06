<?php

namespace App\Http\Livewire;

use Carbon\Carbon;
use Livewire\Component;
use App\Http\Livewire\Scaner;
use App\Models\Category;
use App\Models\Product;
use App\Models\Sabores;
use App\Models\Presentacion;
use App\Models\Discounts;
use App\Models\Lotes;
use App\Models\Carrito;
use App\Models\Favoritos;
use Illuminate\Validation\Validator;
use Illuminate\Support\Facades\Http;
use App\Models\Inspectors;
use Automattic\WooCommerce\Client;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Illuminate\Support\Facades\View;
use App\Traits\CartTrait;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
//api
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use Exception;
use App\Models\Sizes;
use App\Http\Controllers\ProductoWooController;

//QUICKBOOKS
use QuickBooksOnline\API\Facades\Item as ItemQB;
use QuickBooksOnline\API\DataService\DataService;
use QuickBooksOnline\API\Data\IPPCustomer;
use QuickBooksOnline\API\Exception\ServiceException;
use QuickBooksOnline\API\Facades\Customer as CustomerQB;
use Illuminate\Support\Facades\File;
use App\Models\quickbook_credentials;
use App\Services\QuickBooksService;
use Illuminate\Support\Facades\DB;


class PresentacionesController extends Component
{
    use CartTrait;
    public $name;
    public $searchTerm = '';
    public $barcode;
    public $size_id;
    public $descripcion;
    public $saborID;
    public $cost = 0;
    public $estado;
    public $price;
    public $stock;
    public $alerts;
    public $categoryid;
    public $search;
    public $image;
    public $selected_id;
    public $pageTitle;
    public $componentName;
    public $tam2;
    public $tam1;
    private $pagination = 5;
    private $pagination2 = 5;
    public $filteredProducts;
    public $sizes_id;
    public $products_id;
    public $stock_items;
    public $precio;
    public $stock_box = 0;
    public $is_readonly = false;
    public $presentacion;
    protected $WOOCONTROLLER;
    protected $quickBooksService;

    public function __construct()
    {
        $this->WOOCONTROLLER = new ProductoWooController();
    }

    public function boot(QuickBooksService $quickBooksService)
    {
        $this->quickBooksService = $quickBooksService;
    }

    public function mount()
    {
        $this->search = '';
        $this->filteredProducts = [];
        $this->pageTitle = 'Listado';
        $this->componentName = 'Presentaciones';
        $this->categoryid = 'Elegir';
        $this->estado = 'Elegir';
        $this->saborID = 'Elegir';
        $this->size_id = 'Elegir';
        $this->products_id = 'Elegir';
        $this->sizes_id = 'Elegir';
    }


    public function updatedCost($value)
    {
        //$this->price = $this->stock_box * $value;

        if (empty($this->stock_items) || !is_numeric($this->stock_items)) {
            $this->stock_items = 0; // Valor predeterminado
        }

        if (empty($value) || !is_numeric($value)) {
            $this->cost = 0; // Valor predeterminado
        }

        $this->price = (float) $this->stock_items * (float) ($value ?: 0);
        $this->price = number_format(round($this->price, 2), 2, '.', '');
    }
    public function updatedStockItems($value)
    {
        //$this->price = $this->cost * $value;

        if (empty($this->cost) || !is_numeric($this->cost)) {
            $this->cost = 0; // Valor predeterminado
        }
        if (empty($value) || !is_numeric($value)) {
            $this->stock_items = 0; // Valor predeterminado
        }

        $this->price = (float) $this->cost * (float) ($value ?: 0);
        $this->price = number_format(round($this->price, 2), 2, '.', '');
        //number_format(round($pre->costo,2), 2, '.', '')
    }

    public function getCollectionData($presentacion)
    {
        return collect($presentacion)->map(function ($pre) {

            $lot = Lotes::where('SKU', $pre->id)
                ->whereDate('Fecha_Vencimiento', '>=', now()->toDateString()) // Solo considera fechas futuras o actuales
                ->orderBy('Fecha_Vencimiento', 'asc')->count();

            return (object) [
                'id' => $pre->id,
                'created_at' => $pre->created_at,
                'updated_at' => $pre->updated_at,
                'products_id' => $pre->products_id,
                'sizes_id' => $pre->sizes_id,
                'barcode' => $pre->barcode,
                'stock_box' => $pre->stock_box,
                'alerts' => $pre->alerts,
                'stock_items' => $pre->stock_items,
                'name' => $pre->product->name,
                'size' => $pre->size->size,
                'price' => $pre->price,
                'TieneKey' => $pre->TieneKey,
                'estado' => $pre->product->estado,
                'KeyProduct' => $pre->KeyProduct,
                'costo' => $pre->costo,
                'visible' => $pre->visible,
                'tam1' => $pre->tam1,
                'tam2' => $pre->tam2,
                'libra_consumo_1' => $pre->libra_consumo_1,
                'libra_consumo_2' => $pre->libra_consumo_2,
                'QB_id' => $pre->QB_id,
                'lot' => $lot,
                'EstaEnWoocomerce' => $pre->EstaEnWoocomerce,
            ];
        })->toArray();
    }

    public function render()
    {
      
        $presentacion = Presentacion::orderBy('id', 'asc')->get();
        if ($this->searchTerm == "") {
            $this->presentacion = $this->getCollectionData($presentacion);
        }

        $prod = Product::orderBy('id', 'asc')->get();
        $sizes = Sizes::orderBy('id', 'asc')->get();
       
        //$this->quickBooksService->create_invoice(760);
        // dd($sizes);
        return view('livewire.presentaciones.presentaciones-controller', [
            'prod' => $prod,
            'presentacion' => $presentacion,
            'sizes' => $sizes
        ])->extends('layouts.theme.app')
            ->section('content');
    }

    public function haveLot($id)
    {
        return Lotes::where('SKU', $id)
            ->where('Fecha_Vencimiento', '>=', now()) // Solo considera fechas futuras o actuales
            ->orderBy('Fecha_Vencimiento', 'asc')->first();
    }

    public function Edit($id)
    {
        $record = Presentacion::find($id);
        //$this->nombre = $record->nombre;
        $this->barcode = $record->barcode;
        $this->cost = $record->costo;
        $this->stock_box = $record->stock_box;
        $this->price = number_format(round($record->price, 2), 2, '.', '');
        ;
        $this->stock_items = $record->stock_items;
        $this->alerts = $record->alerts;
        $this->products_id = $record->products_id;
        $this->sizes_id = $record->sizes_id;
        $this->selected_id = $record->id;
        $this->is_readonly = true;
        $this->emit('show-modal', 'show modal!');
        $this->emit('producto-creado');
    }
    public function GenerateKey($id)
    {
        // Obtener el producto
        $product = Presentacion::find($id);

        // Generar la clave aleatoria de 90 caracteres
        $key = Str::random(90);

        // Asignar la clave al campo KeyProduct
        $product->KeyProduct = $key;

        // Establecer el campo TieneKey como 'SI'
        $product->TieneKey = 'SI';

        // Guardar los cambios en la base de datos
        $product->save();

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


    public function Store()
    {
        try {

            $rules = [
                'products_id' => 'required|exists:products,id',
                'sizes_id' => 'required|exists:sizes,id',
                'barcode' => 'required|string|max:255',
                'stock_box' => 'required|integer|min:0',
                'alerts' => 'required|integer|min:0',
                'stock_items' => 'required|integer|min:0'
            ];
    
            // Validar las reglas
            $this->validate($rules);
    
            // Comprobar si ya existe una presentación con el mismo producto y tamaño
            $existingPresentacion = Presentacion::where('products_id', $this->products_id)
                ->where('sizes_id', $this->sizes_id)
                ->first();
    
            if ($existingPresentacion) {
                $this->emit('producto-creado');
                $this->emit('creando');
                // Si ya existe, emitir un mensaje de error y no proceder con la creación
                $this->emit('global-msg', 'Esta presentación ya está creada para este producto y tamaño.');
    
                return;
            }
            // Crear la presentación si no existe
            $pre = Presentacion::create([
                'products_id' => $this->products_id,
                'sizes_id' => $this->sizes_id,
                'barcode' => $this->barcode,
                'stock_box' => $this->stock_box,
                'alerts' => $this->alerts,
                'stock_items' => $this->stock_items,
                'price' => $this->price,
                'costo' => $this->cost,
                'visible' => 'no',
            ]);

            //$this->createOrUpdateProductInQuickBooksAndUpdate($pre->id);
            $this->quickBooksService->create_product($pre); //Quickbooks
            //$this->AddProductRunningInBackground($pre->id);

            // Emitir eventos y resetear UI
            $this->emit('producto-creado');
            $this->emit('creando');
            $this->resetUI();
            //$this->WOOCONTROLLER->CrearProWoo($pre->id);
            //ProductoWooController();
            $this->emit('global-msg', 'Presentación Creada.');
            $this->emit('sabor-added', 'Categoría Registrada');
        } catch (Exception $e) {
            // En caso de error, emitir un mensaje con el error
            $this->emit('sale-error',  $e->getMessage());
            throw $e;
        }
    }

    public function ScanCode($code)
    {

        try {
            $this->ScanearCode($code);        
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400); 
        }
    }
    protected $listeners = [
        'deleteRow' => 'Destroy',
        'deletePre' => 'DestroyPresentacion',
        'crearWC' => 'CrearProWoo',
        'wcProductCreated' => 'wcProductCreated',
        'Quickboks' => 'loadQuickbooks'

    ];

    public function DestroyPresentacion(Presentacion $product)
    {
        $user = Auth()->user()->name;
        $delete = $this->quickBooksService->deleteProduct($product->id);
        $delete = true;
        if($delete){
            $product->delete();
            $this->resetUI();
            $this->emit('product-deleted', 'Producto Eliminado');
            $this->emit('global-msg', 'Producto Eliminado');
            $inspector = Inspectors::create([
                'user' => $user,
                'action' => 'Elimnino un producto ',
                'seccion' => 'Products'
            ]);
        }else{
            $this->emit('global-msg', "Error: No se pudo eliminar.");
        }
    }


    public function getOrCreateCategory($nombre, $parent_id = 0)
    {
        $woocommerceUrl = 'https://kdlatinfood.com/wp-json/wc/v3/';
        $consumerKey = 'ck_8e38a879e7f6ce0d56e34c525de194a60c2e2ce5';
        $consumerSecret = 'cs_723eab16e53f3607fd38984b00f763310cc4f473';

        // 🔹 Buscar la categoría en WooCommerce
        $response = Http::withBasicAuth($consumerKey, $consumerSecret)
            ->get("{$woocommerceUrl}products/categories", [
                'search' => $nombre
            ]);

        if ($response->successful() && !empty($response->json())) {
            return $response->json()[0]['id']; // Retorna el ID si existe
        }

        // 🔹 Crear la categoría si no existe
        $createCategoryResponse = Http::withBasicAuth($consumerKey, $consumerSecret)
            ->post("{$woocommerceUrl}products/categories", [
                'name' => $nombre,
                'parent' => $parent_id
            ]);

        if ($createCategoryResponse->successful()) {
            return $createCategoryResponse->json()['id']; // Retorna el ID de la nueva categoría
        }

        throw new Exception("Error al crear la categoría $nombre en WooCommerce");
    }


    //Crear Productos en el Woocomerce
    //Habilitada por Sebastyan Pineda
    public function CrearProWoo($id)
    {
        // Obtener datos del producto
        $pre = Presentacion::find($id);

        $sku = $pre->barcode;
        $nombre = $pre->product->name . " " . $pre->size->size . " " . $pre->product->estado;
        $precio = $pre->price;
        $descripcion = $pre->product->descripcion;
        $stock = $pre->stock_box;
        //$desc = $pre->descripcion;
        $desc = $descripcion;
        $categoria_nombre = $pre->product->category->name;
        $categoria_id = $pre->product->category_id;
        $size = $pre->size->size;

        $url = 'https://kdlatinfood.com/wp-json/wc/v3/products';
        $consumerKey = 'ck_8e38a879e7f6ce0d56e34c525de194a60c2e2ce5';
        $consumerSecret = 'cs_723eab16e53f3607fd38984b00f763310cc4f473';

        $skuCheckResponse = Http::withBasicAuth($consumerKey, $consumerSecret)
        ->get($url, ['sku' => $sku]);

        if ($skuCheckResponse->successful() && !empty($skuCheckResponse->json())) {
            return true;
        }


       

        $imageUrl = 'https://kdlatinfood.com/intranet/public/storage/products/' . $pre->product->image;


        try {

            // 🔹 Crear Categoría Principal (Ej: "Empanadas")
            $categoria_padre_id = $this->getOrCreateCategory($categoria_nombre);

            // 🔹 Crear Subcategoría (Ej: "Empanada de Queso Cruda")
            $subcategoria_id = $this->getOrCreateCategory($pre->product->name, $categoria_padre_id);

            // Crear el array con los datos del producto
            $data = [
                'name' => $nombre,
                'sku' => $sku,
                'regular_price' => $precio,
                'stock_quantity' => $stock,
                'description' => $desc,
                'short_description' => $desc,
                'categories' => [
                    [
                        'id' => $subcategoria_id,
                    ],
                ],
                'default_attributes' => [

                    [
                        'name' => 'Size',
                        'option' => $size
                    ]
                ],
                'images' => [
                    [
                        'src' => $imageUrl,
                        'name' => $nombre,
                        'alt' => $nombre
                    ],
                ],
            ];
            //dd($data);
            // Realizar la solicitud HTTP con los datos del producto, incluida la imagen
            $response = Http::withBasicAuth(
                'ck_8e38a879e7f6ce0d56e34c525de194a60c2e2ce5',
                'cs_723eab16e53f3607fd38984b00f763310cc4f473'
            )->post($url, $data);
     
            // Verificar si la solicitud fue exitosa
            if ($response->successful()) {
                $this->resetUI();
                $this->emit('producto-creado');
                $this->emit('product-deleted', 'Producto Eliminado');
                $pre->EstaEnWoocomerce = 'si';
                $pre->save();

                // Mostrar mensaje de éxito
                $this->emit('global-msg', "SE CREO CORRECTAMENTE");
            } else {
                // Si la solicitud no fue exitosa, lanzar una excepción
                throw new Exception('Error al crear el producto en WooCommerce');
            }
        } catch (Exception $e) {
            // Manejar la excepción capturada
            // Aquí puedes registrar el error, enviar notificaciones, etc.
            $errorMessage = $e->getMessage();
            $this->emit('global-msg', "Error: $errorMessage");
            dd($e);
        }


        $this->emit('producto-creado');

        // RELOAD 
        $user = Auth()->user()->name;
        $inspector = Inspectors::create([
            'user' => $user,
            'action' => 'Creo un producto en woocomerce ',
            'seccion' => 'Products'
        ]);
    }


    public function Update()
    {
        try{
            DB::beginTransaction();
            $rules = [
                'products_id' => 'required|exists:products,id',
                'sizes_id' => 'required|exists:sizes,id',
                'barcode' => 'required|string|max:255',
                'stock_box' => 'required|integer|min:0',
                'alerts' => 'required|integer|min:0',
                'stock_items' => 'required|integer|min:0'
            ];
            $this->validate($rules);

            $pre = Presentacion::find($this->selected_id);
            $product = Product::where('barcode', $pre->barcode)->first();
            $ModificandoBarcode = false;
            if($product){
                if($this->barcode != $pre->barcode){
                   $ModificandoBarcode = true;
                }

            }

            $calculatedPrice = $this->cost * $this->stock_items;
            $data = [
                'products_id' => $this->products_id,
                'sizes_id' => $this->sizes_id,
                'stock_box' => $this->stock_box,
                'alerts' => $this->alerts,
                'stock_items' => $this->stock_items,
                'price' => $calculatedPrice,
                'costo' => $this->cost,
            ];
            if(!$ModificandoBarcode){
                $data['barcode'] = $this->barcode;
            }
            $pre->update($data);

            if ($product) {
                $product->price = $this->cost;
                $product->cost = $this->cost;
                $product->tam1 = $this->stock_items;
                $product->save();
            }

            //$this->WOOCONTROLLER->CrearProWoo($pre->id);
            if($pre->QB_id){
                $this->quickBooksService->update_product($pre); //Quickbooks
            }else{
                $this->quickBooksService->create_product($pre); //Quickbooks
            }
            if($ModificandoBarcode){
                $this->emit('producto-creado');
                $this->emit('global-msg',  'Actualizado Correctamente,El SKU no se puede modificar,  si deseas modificarlo para está presentacion, debes actualizar el SKU en el producto.');
                $this->resetUI();
                
            }else{
                $this->emit('producto-creado');
                $this->resetUI();
                $this->emit('global-msg', 'Presentación Actualizado!');
                $this->emit('hide-modal', 'show modal!');
            }
            DB::commit();
        }catch (Exception $e) {
            DB::rollBack();
            $this->emit('producto-creado');
            $this->emit('global-msg',  $e->getMessage());
            throw $e;
        }

    }


    public function resetUI()
    {
        $this->nombre = '';
        $this->description = '';
        $this->selected_id = 0;
        $this->products_id = 'Elegir';
        $this->sizes_id = 'Elegir';
        $this->barcode = '';
        $this->stock_box = 0;
        $this->alerts = '';
        $this->stock_items = '';
        $this->cost = 0;
        $this->is_readonly = false;
    }
    /**
     * Obtener las presentaciones de un producto dado su ID.
     *
     * @param  int  $productId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPresentacionesByProduct($productId)
    {
        try {
            // Obtener las presentaciones relacionadas con el producto, incluyendo el producto
            $presentaciones = Presentacion::with('product')
                ->where('products_id', $productId)
                ->where('visible', 'si')
                ->get();

            // Verificar si hay presentaciones
            if ($presentaciones->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se encontraron presentaciones para este producto.'
                ], 404);
            }

            // Obtener el producto asociado (asumiendo que todas las presentaciones tienen el mismo producto)
            $producto = $presentaciones->first()->product;

            // Retornar las presentaciones junto con el producto
            return response()->json([
                'success' => true,
                'data' => [
                    'producto' => $producto,
                    'presentaciones' => $presentaciones
                ]
            ], 200);

        } catch (\Exception $e) {
            // Manejar errores y retornar respuesta en caso de excepción
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las presentaciones: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getPresentacionesByProductForUser($productId, $customerID)
    {
        try {
            // Obtener las presentaciones relacionadas con el producto, incluyendo el producto
            $presentaciones = Presentacion::with('product')
                ->where('products_id', $productId)
                ->where('visible', 'si')
                ->get()
                ->map(function ($presentacion) use ($customerID) {

                    $discount = Discounts::where('presentacion_id', $presentacion->id)->where('customer_id', $customerID)->first();
                    $have_discount = !is_null($discount);
                    $presentacion->have_discount = $have_discount; // Agrega el nuevo campo
                    if ($presentacion->have_discount) {
                        $presentacion->discount = $discount;
                    }
                    return $presentacion;
                });

            // Verificar si hay presentaciones
            if ($presentaciones->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se encontraron presentaciones para este producto.'
                ], 404);
            }

            // Obtener el producto asociado (asumiendo que todas las presentaciones tienen el mismo producto)
            $producto = $presentaciones->first()->product;

            // Retornar las presentaciones junto con el producto
            return response()->json([
                'success' => true,
                'data' => [
                    'producto' => $producto,
                    'presentaciones' => $presentaciones
                ]
            ], 200);

        } catch (\Exception $e) {
            // Manejar errores y retornar respuesta en caso de excepción
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las presentaciones: ' . $e->getMessage()
            ], 500);
        }
    }

    public function visible($id)
    {   try{
            DB::beginTransaction();
            // Obtener el producto
            $product = Presentacion::find($id);
            // Asignar la clave al campo KeyProduct
            $product->visible = 'no';
            $product->save();
            $this->visibleProduct($product->products_id,$id);
            // Imprimir la clave generada para verificar
            $this->emit('global-msg', "El producto ha sido ocultado");
            //$this->showPresentations($product->products_id);
            $this->emit('producto-creado');
            $user = Auth()->user()->name;
            $inspector = Inspectors::create([
                'user' => $user,
                'action' => 'oculto un producto ',
                'seccion' => 'Products'
            ]);
            DB::commit();
        }catch(Exception $e){
            DB::rollBack();
            $this->emit('producto-creado');
            $this->emit('global-msg', "No se puedo cambiar visibilidad");
        }
       
    }
    public function novisible($id)
    {
        try {
            DB::beginTransaction();
            // Obtener el producto
            $product = Presentacion::find($id);
            // Asignar la clave al campo KeyProduct
            $product->visible = 'si';
            $product->save();
            $this->noVisibleProduct($product->products_id,$id);
            //$this->showPresentations($product->products_id);
            // Imprimir la clave generada para verificar
            $this->emit('global-msg', "El producto ha sido PUBLICADO");
            $this->emit('producto-creado');
            $user = Auth()->user()->name;
            $inspector = Inspectors::create([
                'user' => $user,
                'action' => 'publico un producto ',
                'seccion' => 'Products'
            ]);
            DB::commit();
        }
        catch(Exception $e){
            DB::rollBack();
            $this->emit('producto-creado');
            $this->emit('global-msg', "No se puedo cambiar visibilidad");
        }
    }

    public function visibleProduct($idPro, $idPres)
    {
        try {
            DB::beginTransaction();
            //todas las presentaciones diferentes a ellas.
            $allPresentations = Presentacion::where('products_id', $idPro)->where('id', '!=', $idPres)->get();
            $modificateProduct = 0;
            if ($allPresentations->isEmpty()) {
                Product::where('id', $idPro)->update(['visible' => "no"]); //Oculta el producto (Solo una)
            } else {
                foreach ($allPresentations as $pre) { //Recorrre todas las presentaciones diferentes a la seleccionada
                    if ($pre->visible == 'no') { //Si encuentre una que NO es visible Aumenta el Modificate Produc, Cantidad de presentaciones ocultas
                        $modificateProduct++;
                    }
                }

                if ($modificateProduct === $allPresentations->count()) {
                    Product::where('id', $idPro)->update(['visible' => "no"]);
                }
            }
            DB::commit();
        } catch (\Exception $e) {
            $this->emit('producto-creado');
            $this->emit('global-msg', "Error : ". $e->getMessage());
            DB::rollBack();
            throw $e;
        }
    }
    // para actualizar el producto la visivilidad a si
    public function noVisibleProduct($idPro, $idPres)
    {
        try {
            DB::beginTransaction();

            $allPresentations = Presentacion::where('products_id', $idPro)->where('id', '!=', $idPres)->get();
            $modificateProduct = 0;
            if ($allPresentations->isEmpty()) {
                Product::where('id', $idPro)->update(['visible' => "si"]);
            } else {
                foreach ($allPresentations as $pre) {
                    if ($pre->visible != 'si') {
                        $modificateProduct++;
                    }
                }

                if ($modificateProduct === $allPresentations->count()) {
                    Product::where('id', $idPro)->update(['visible' => "si"]);
                }
            }
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function loadQuickbooks($presentacion_id){
        $product = Presentacion::find($presentacion_id);
        $this->quickBooksService->create_product($product);
        
        $user = Auth()->user()->name;
        $inspector = Inspectors::create([
            'user' => $user,
            'action' => 'publico un producto en el Quickbooks',
            'seccion' => 'Products'
        ]);
        $this->emit('producto-creado');
        $this->emit('global-msg', "El producto ha sido PUBLICADO en Quickbooks");
    }

    /*public function createOrUpdateProductInQuickBooksAndUpdate($productId){
        $this->quickBooksService->createOrUpdateProduct($productId);
    }*/

    /*public function createOrUpdateProductInQuickBooksAndUpdate($productId)
    {
        // Llamar a la función para crear o actualizar el producto en QuickBooks
        $this->createOrUpdateProductInQuickBooks($productId);

        // Luego, llamar de nuevo para asegurarse de que se actualice después de la creación
        $this->createOrUpdateProductInQuickBooks($productId);
        $this->emit('producto-creado');
    }
    public function createOrUpdateProductInQuickBooks($productId)
    {

        // Inicializar las credenciales
        $config = config('quickbooks');
        $qb_credentials = $this->update_access_token(); //renovar token y lo trae.
         //Configura el servicio de autenticacion
        //Establece el modo autenticacion como oauth2
        $dataService = DataService::Configure([
            'auth_mode' => 'oauth2',
            'ClientID' => $config['client_id'],
            'ClientSecret' => $config['client_secret'],
            'RedirectURI' => $config['redirect_uri'],
            'accessTokenKey' => $qb_credentials['access_token'],
            'refreshTokenKey' => $qb_credentials['refresh_token'],
            'QBORealmID' => $config['realm_id'],
            'baseUrl' => $config['base_url'],
        ]);

        // Buscar el producto en tu base de datos local - DEBE CREAR ES LA PRESENTACION
        $product = Presentacion::find($productId);
        //$product = Product::find($productId);
        $name_product = $product->product->name . " " .$product->size->size. " ".$product->product->estado; 

        // Consultar QuickBooks con el nombre del producto
        $query = "SELECT * FROM Item WHERE Name = '" . $name_product . "'";
        $qb_product = $dataService->Query($query);
        // dd($qb_product);

        if (isset($qb_product) && !empty($qb_product) && count($qb_product) > 0) {
            // dd($qb_product);
            // Si el producto existe en QuickBooks, actualizarlo
            $qb_product = $qb_product[0];
            $product->QB_id = $qb_product->Id;
            $product->save();
            $qb_product->UnitPrice = $product->price; // Actualizar el precio del producto
            $qb_product->Description = $product->product->descripcion; // Actualizar la descripción del producto
            $qb_product->Sku = $product->barcode; // Actualizar el SKU del producto
            $qb_product->Active = $product->product->estado === 'activo' ? true : false;
            // Actualizar el estado del producto
            $qb_product->SalesTaxIncluded = true; // Indicar si el impuesto está incluido en el precio
            $qb_product->QtyOnHand = $product->stock_box; // Actualizar el stock del producto
            try {
                $result = $dataService->Update($qb_product);
                $this->emit('global-msg', 'Product updated in QuickBooks');

            } catch (ServiceException $th) {
                //  dd($th);
                $this->emit('global-msg', 'Error updating product in QuickBooks');
            }
        } else {
            // Si el producto no existe en QuickBooks, crearlo
            $qb_product = ItemQB::create([
                "TrackQtyOnHand" => true,
                "Name" => $name_product,
                "QtyOnHand" => $product->stock_box,
                "IncomeAccountRef" => [
                    "name" => $name_product,
                    "value" => "79"
                ],
                "AssetAccountRef" => [
                    "name" => "Inventory Asset",
                    "value" => "81"
                ],
                "InvStartDate" => $product->created_at,
                "Type" => "Inventory",
                "ExpenseAccountRef" => [
                    "name" => "Cost of Goods Sold",
                    "value" => "80"
                ]
            ]);

            // $product->QB_id = $qb_product->Id;
            // $product->save();
            //

            try {
                $result = $dataService->Add($qb_product);
                //dd($qb_product);
                $this->emit('global-msg', 'Product created in QuickBooks');

            } catch (ServiceException $th) {
                $this->emit('global-msg', 'Error creating product in QuickBooks');
                //  dd($th);

            }
        }

    }
    public function update_access_token()
    {
        //Carga de configuracion y credenciales desde la base de datos
        $config = config('quickbooks');
        $quickbook_credentials = quickbook_credentials::where('status', 1)->first();
        // Verifica si existen las credenciales activas
        if ($quickbook_credentials->count() > 0) {
            $access_token = $quickbook_credentials->access_token;
            $refresh_access_token = $quickbook_credentials->refresh_access_token;
        } else {
            $access_token = $config['access_token'];
            $refresh_access_token = $config['refresh_access_token'];
        }

        //Configura el servicio de autenticacion
        //Establece el modo autenticacion como oauth2
        $dataService = DataService::Configure([
            'auth_mode' => 'oauth2',
            'ClientID' => $config['client_id'],
            'ClientSecret' => $config['client_secret'],
            'RedirectURI' => $config['redirect_uri'],
            'accessTokenKey' => $access_token,
            'refreshTokenKey' => $refresh_access_token,
            'QBORealmID' => $config['realm_id'],
            'baseUrl' => $config['base_url'],
            'token_refresh_interval_before_expiry' => $config['base_url'],
        ]);

        //Obtiene los nuevos token con el refresh token
        $OAuth2LoginHelper = $dataService->getOAuth2LoginHelper(); //Solicita un nuevo access_token 
        $accessTokenObj = $OAuth2LoginHelper->refreshAccessTokenWithRefreshToken($config['refresh_token']); //Envia la solicitud para que le den un nuevo token
        $accessTokenValue = $accessTokenObj->getAccessToken();  //obtiene el nuevo token
        $refreshTokenValue = $accessTokenObj->getRefreshToken(); // obtiene el nuevo access token

        //Actualiza la base de datos con los nuevos tokens
        $dataArr['client_id'] = $config['client_id'];
        $dataArr['client_secret'] = $config['client_secret'];
        $dataArr['realm_id'] = $config['realm_id'];
        $dataArr['redirect_uri'] = $config['redirect_uri'];
        $dataArr['base_url'] = $config['base_url'];
        $dataArr['status'] = 1;
        $dataArr['access_token'] = $accessTokenValue;
        $dataArr['refresh_token'] = $refreshTokenValue;

        $quickbook_credentials->where('id', 1)->update($dataArr);
        //emite un mensaje con los nuevos tokens (Access Token y Refresh Token)
        $this->emit('global-msg', 'Token Actualizado');
        return ['access_token' => $accessTokenValue, 'refresh_token' => $refreshTokenValue];
        //access token caduca en 1 hora... entonces el refresh token es el token de renovacion que utilizo para obtener el nuevo acceso_token
        //Segun puede durar hasta 100dias. Cuando se renueva el access_token tambien se renueva el refresh_token
    }*/

}
