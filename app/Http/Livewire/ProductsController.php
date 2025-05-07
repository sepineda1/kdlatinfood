<?php

namespace App\Http\Livewire;

use App\Models\Discounts;
use App\Models\Presentacion;
use App\Models\SaleDetail;
use Illuminate\Support\Facades\Log;
use App\Http\Livewire\Scaner;
use App\Models\Category;
use App\Models\Product;
use App\Models\Sabores;
use App\Models\Lotes;
use App\Models\Carrito;
use App\Models\Favoritos;
use Illuminate\Validation\Validator;
use Illuminate\Support\Facades\Http;
use Livewire\Component;
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
//QUICKBOOKS
use QuickBooksOnline\API\Facades\Item as ItemQB;
use QuickBooksOnline\API\DataService\DataService;
use QuickBooksOnline\API\Data\IPPCustomer;
use QuickBooksOnline\API\Exception\ServiceException;
use QuickBooksOnline\API\Facades\Customer as CustomerQB;
use Illuminate\Support\Facades\File;
use App\Models\quickbook_credentials;
use App\Http\Controllers\ProductoWooController;
use App\Services\QuickBooksService;
use Illuminate\Support\Facades\DB;


class ProductsController extends Component
{
    use WithPagination;
    use WithFileUploads;
    use CartTrait;

    public function ScanCode($code)
    {
        try {
            $this->ScanearCode($code);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
        //this->emit('global-msg', "Se agrego el producto al carrito ");
    }
    public $presentations = [];
    public $name, $barcode, $size_id, $descripcion, $saborID, $cost, $estado, $price, $stock, $alerts, $categoryid, $search, $image, $selected_id, $pageTitle, $componentName, $tam2, $tam1;
    private $pagination = 5;
    private $pagination2 = 5;
    public $filteredProducts;
    public $stock_items;
    public $precio;
    public $sizes_id;
    public $products_id;
    public $stock_box;

    public $category_id;
    public $type;

    public $select_options_products = false;
    public $select_options_list_products = [];

    public $presentacion_selected = 0;

    public $open_modal_create = false;

    protected $WOOCONTROLLER;

    protected $quickBooksService;

    public $presentacion_id_consumo;
    public $show_consumo_modal = false;

    public function boot(QuickBooksService $quickBooksService)
    {
        $this->quickBooksService = $quickBooksService;
    }

    public function paginationView()
    {
        return 'vendor.livewire.bootstrap';
    }

    public function __construct()
    {
        $this->WOOCONTROLLER = new ProductoWooController();
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

        $this->price = round((float) $this->stock_items * (float) ($value ?: 0), 2);
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

        $this->price = round((float) $this->cost * (float) ($value ?: 0), 2);
    }

    public function updatedTam1($value)
    {
        //$this->price = $this->cost * $value;

        if (empty($this->cost) || !is_numeric($this->cost)) {
            $this->cost = 0; // Valor predeterminado
        }
        if (empty($value) || !is_numeric($value)) {
            $this->tam1 = 0; // Valor predeterminado
        }

        $this->price = round((float) $this->cost * (float) ($value ?: 0), 2);
    }

    public function calcularPrecio()
    {
        $cost = is_numeric($this->cost) ? (float) $this->cost : 0;
        $tam1 = is_numeric($this->tam1) ? (float) $this->tam1 : 0;

        $this->price = round($cost * $tam1, 2);
    }

    public function getCollectionData($presentations)
    {
        return collect($presentations)->map(function ($pre) {

            $lot = Lotes::where('SKU', $pre->id)
                ->where('Fecha_Vencimiento', '>=', now()) // Solo considera fechas futuras o actuales
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
                'image' => $pre->product->image ? $pre->product->image : "",
                'sabor' => $pre->product->sabor->nombre,
                'size' => $pre->size->size,
                'price' => $pre->price,
                'TieneKey' => $pre->TieneKey,
                'KeyProduct' => $pre->KeyProduct,
                'costo' => $pre->costo,
                'visible' => $pre->visible,
                'tam1' => $pre->tam1,
                'tam2' => $pre->tam2,
                'libra_consumo_1' => $pre->libra_consumo_1,
                'libra_consumo_2' => $pre->libra_consumo_2,
                'QB_id' => $pre->QB_id,
                'lot' => $lot,
                'EstaEnWoocomerce' => $pre->product->EstaEnWoocomerce,
                'estado' => $pre->product->estado,
            ];
        })->toArray();
    }
    
    public function showPresentations($productId)
    {
        $this->selected_id = $productId;

        $product = Product::find($productId);
        if (!$product) {
            $this->emit('global-msg', 'Producto no encontrado');
            $this->emit('producto-creado');
            return;
        }
        $presentations = Presentacion::where('products_id', $productId)->get();
        $this->presentations = $this->getCollectionData($presentations);

        $this->emit('presentaciones-show');
        $this->emit('producto-creado');
    }
    public function EditPresentacion($id)
    {
        $this->presentacion_selected = $id;
        $this->open_modal_create = false;
        $record = Presentacion::where('id', $id)->first();
        //$presentations = Presentacion::where('products_id', $record->products_id)->get();
        //$this->presentations = $this->getCollectionData($presentations);
        //$this->presentations = 
        $this->products_id = $record->products_id;
        $this->sizes_id = $record->sizes_id;
        $this->barcode = $record->barcode;
        $this->stock_box = $record->stock_box;
        $this->cost = $record->costo;
        $this->alerts = $record->alerts;
        $this->stock_items = $record->stock_items;
        $this->price = $record->price;
        //$this->selected_id = $record->id;
        $this->emit('producto-creado');
        $this->render();
        $this->emit('presentaciones-crear-show', 'show modal!');

    }
    public function GenerateKeyPresentacion($id)
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


    public function StorePresentacion()
    {
        try {
            $rules = [
                'products_id' => 'required',
                'sizes_id' => 'required',
                'barcode' => 'nullable',
                'stock_box' => 'required',
                'alerts' => 'nullable',
                'stock_items' => 'required',
                'cost' => 'required'
            ];
            $messages = [
                'products_id.required' => 'Por favor debes Elegir el Producto.',
                'sizes_id.min' => 'Por favor debes Elegir el Tamaño.',
                'barcode.required' => 'Debes Completar el Campo SKU.',
                'stock_box.min' => 'Debes poner el numero de cajas que tiene actualmente.',
                'alerts.required' => 'El numero de Cajas Minimas no puedo ser cero',
                'stock_items.unique' => 'El numero de Productos que tiene una caja no puede ser cero.',
                'cost.min' => 'Debes completar el costo.',
            ];

            $this->validate($rules, $messages);


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

            try {
                $calculatedPrice = $this->cost * $this->stock_items;
                // Crear la presentación si no existe
                $pre = Presentacion::create([
                    'products_id' => $this->products_id,
                    'sizes_id' => $this->sizes_id,
                    'barcode' => $this->barcode,
                    'stock_box' => $this->stock_box,
                    'alerts' => $this->alerts,
                    'stock_items' => $this->stock_items,
                    'price' => $calculatedPrice,
                    'costo' => $this->cost,
                    'visible' => 'no',
                ]);

                $this->quickBooksService->create_product($pre);


                // Emitir eventos y resetear UI
                $this->emit('producto-creado');
                $this->emit('creando');
                $this->resetUI();
                //$this->WOOCONTROLLER->CrearProWoo($pre->id);
                $this->emit('global-msg', 'Presentacion Creado');
                $this->emit('presentaciones-crear-hide', 'show modal!');
                $this->emit('sabor-added', 'Categoría Registrada');
                $this->showPresentations($this->products_id);
            } catch (Exception $e) {
                // En caso de error, emitir un mensaje con el error
                $this->emit('global-msg', 'Error: ' . $e->getMessage());
            }

        } catch (Exception $e) {

            $errorString = '';
            if ($e instanceof ValidationException) {
                $errors = $e->errors();

                // Convertir los errores a una cadena                
                foreach ($errors as $fieldErrors) {
                    foreach ($fieldErrors as $error) {
                        $errorString .= $error . ' ';
                    }
                }

                // Eliminar el último espacio extra
                $errorString = rtrim($errorString);
            } else {
                $errorString = $e->getMessage();
            }
            $this->emit('producto-creado');
            $this->emit('global-msg', 'Error: ' . $errorString);
            throw $e;
        }



    }

    public function ScanCodePresentacion($code)
    {

        try {
            $this->ScanearCode($code);

        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }


    public function UpdatePresentacion()
    {
        try {
            DB::beginTransaction();
            $rules = [
                'products_id' => 'required|exists:products,id',
                'sizes_id' => 'required|exists:sizes,id',
                'barcode' => 'required|string|max:255',
                'stock_box' => 'required|integer|min:0',
                'alerts' => 'required|integer|min:0',
                'stock_items' => 'required|integer|min:0',
                'cost' => 'required'
            ];

            // Validar las reglas
            $this->validate($rules);

            $pre = Presentacion::find($this->presentacion_selected);
            $product = Product::where('barcode', $pre->barcode)->first();
            $ModificandoBarcode = false;
            if($product){
                if($this->barcode != $pre->barcode){
                   $ModificandoBarcode = true;
                }

            }


           
            $pre = Presentacion::find($this->presentacion_selected);
            if(!$pre){
                $this->emit('producto-creado');
                $this->emit('global-msg', 'Error: no se puede actualizar.');
                return;
            }

            $calculatedPrice = $this->stock_items * $this->cost;
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

            /*$pre->update([
                'products_id' => $this->products_id,
                'sizes_id' => $this->sizes_id,
                'barcode' => $this->barcode,
                'stock_box' => $this->stock_box,
                'alerts' => $this->alerts,
                'stock_items' => $this->stock_items,
                'price' => $calculatedPrice,
                'costo' => $this->cost,
            ]);*/

            $product = Product::where('barcode', $pre->barcode)->first();
            if ($product) {
                $calculatedPrice = $this->cost * $this->stock_items;
                $product->price = $this->cost;
                $product->cost = $this->cost;
                $product->tam1 = $this->stock_items;
                //$product->price_public = $calculatedPrice;
                $product->save();

            }

            $this->showPresentations($this->products_id);
            if($ModificandoBarcode){
                $this->emit('producto-creado');
                $this->emit('global-msg',  'Actualizado Correctamente,El SKU no se puede modificar,  si deseas modificarlo para está presentacion, debes actualizar el SKU en el producto.');
                //$this->resetUI();
                
            }else{
                $this->emit('producto-creado');
      
                $this->emit('global-msg', 'Presentación Actualizado!');
                //$this->emit('hide-modal', 'show modal!');
            }
            
            DB::commit();
            $this->quickBooksService->update_product($pre);

            /*$this->emit('producto-creado');
            //$this->resetUI();
            $this->showPresentations($this->products_id);
            
            //$this->WOOCONTROLLER->CrearProWoo($pre->id);
            $this->emit('global-msg', 'Presentacion Actualizada');
            $this->emit('presentaciones-crear-hide', 'show modal!');
            $this->emit('sabor-updated', 'Categoría Actualizada');*/

        } catch (Exception $e) {

            /*$this->emit('global-msg', 'Hubo un error: ' . $e->getMessage());
            $this->emit('producto-creado');
            throw $e;*/

            DB::rollBack();
            $this->emit('producto-creado');
            $this->emit('global-msg',  $e->getMessage());
            throw $e;
        }

        /*

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
                $product->price =  $calculatedPrice;
                $product->cost = $this->cost;
                $product->tam1 = $this->stock_items;
                $product->save();
            }

            //$this->WOOCONTROLLER->CrearProWoo($pre->id);
            if($pre->QB_id){
                //$this->quickBooksService->update_product($pre); //Quickbooks
            }else{
                //$this->quickBooksService->create_product($pre); //Quickbooks
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
        */

    }


    public function resetUIPresentacion()
    {
        //Aqui tengo que cerrar modal.
        $this->emit('presentaciones-crear-hide', 'show modal!');
        $this->nombre = '';
        $this->description = '';
        $this->products_id = 'Elegir';
        $this->sizes_id = 'Elegir';
        $this->barcode = '';
        $this->stock_box = '';
        $this->alerts = '';
        $this->stock_items = '';
    }

    public function mount($category_id = null, $type = null)
    {
        $this->search = '';
        $this->filteredProducts = [];
        $this->pageTitle = 'Listado';
        $this->componentName = 'Productos';
        $this->categoryid = 'Elegir';
        $this->estado = 'Elegir';
        $this->saborID = 'Elegir';
        $this->size_id = 'Elegir';
        $this->products_id = 'Elegir';
        $this->size_id = 'Elegir';

        $this->category_id = $category_id;
        if ($type != null) {
            $this->type = $type == "raw" ? "CRUDO" : "PRECOCIDO";
        }

    }

    public function openPresentacionCrear($productID)
    {
        $this->selected_id = $productID;
        $this->open_modal_create = true;
        $this->emit('modal-hide');
        //dd('hola');
        $this->emit('presentaciones-crear-show');

    }


    public function openPrecocidosModal()
    {
        $this->emit('openPrecocidosModal');
    }

    public function openCrudosModal()
    {
        $this->emit('openCrudosModal');
    }

    public function redirectToProductDetail($productId)
    {
        return redirect()->to(route('product.detail', $productId));
    }

    public function abrirModalConsumo($id)
    {
        $this->presentacion_id_consumo = $id;
        $this->show_consumo_modal = true;
        $this->emit('abrir-modal-consumo');
    }

    public function updatedSearch()
    {
        if (empty($this->search)) {
            $this->filteredProducts = [];
            return;
        }
        $query = Product::join('categories as c', 'c.id', 'products.category_id')
            ->select('products.*', 'c.name as category')
            ->orderBy('products.name', 'asc');

        if (strlen($this->search) > 0) {
            $query->where(function ($q) {
                $q->where('products.name', 'like', '%' . $this->search . '%')
                    ->orWhere('products.barcode', 'like', '%' . $this->search . '%')
                    ->orWhere('c.name', 'like', '%' . $this->search . '%');
            });
        }

        $this->filteredProducts = $query->get();
    }

    public function render()
    {

        $currentRoute = request()->path();
        if ($currentRoute == 'products') {
            $categories = Category::orderBy('name', 'asc')->get();
            return view('livewire.products.list', [
                'categories2' => $categories,
            ])
                ->extends('layouts.theme.app')
                ->section('content');
        } else {
            if ($this->selected_id) {
                $presentations = Presentacion::where('products_id', $this->selected_id)->get();
                $this->presentations = $this->getCollectionData($presentations);
            }
            $name_category = Category::where('id', $this->category_id)->first();
            $products = Product::where('estado', $this->type)->
                where('category_id', $this->category_id)->get();
            $categories = Category::orderBy('name', 'asc')->get();

            //$productsToCompare = Presentacion::whereColumn('alerts', '>=', 'stock_items')->get();
            $products0 = Presentacion::all();
            $productsOutOfStock = $products0->filter(function ($product) {
                return $product->alerts > $product->stock_items;
            });
            //$productsOutOfStock = Presentacion::whereRaw('alerts > stock_items')->get();
            $sizes = Sizes::orderBy('id', 'asc')->get();
            View::share('productsOutOfStock', $productsOutOfStock);
            View::share('categories', $categories);

            $sabores = Sabores::orderBy('nombre', 'asc')
                ->whereRaw("nombre NOT LIKE '%ELIMINAR%'")
                ->get();
            $prod = Product::orderBy('id', 'asc')->get();

            $data = [];
            if ($this->type == "CRUDO") {
                $data = [
                    'data' => $products,
                    'categories' => $categories,
                    'sabores' => $sabores,
                    'sizes' => $sizes,
                    'prod' => $prod,
                    'name_category' => $name_category->name
                ];
            }
            if ($this->type == "PRECOCIDO") {
                $data = [
                    'data2' => $products,
                    'categories2' => $categories,
                    'sabores' => $sabores,
                    'sizes' => $sizes,
                    'prod' => $prod,
                    'name_category' => $name_category->name
                ];
            }

            return view('livewire.products.component', $data)
                ->extends('layouts.theme.app')
                ->section('content');
        }

    }



    public function Store()
    {
        try{
            $rules = [
                'name' => "required|min:3",
                'cost' => 'required',
                'price' => 'required',
                'tam1' => 'required',
                'categoryid' => 'required|not_in:Elegir',
                'barcode' => 'required|min:6',
                'saborID'=> 'required|not_in:Elegir',
            ];
    
            $messages = [
                'name.required' => 'Nombre del producto requerido',
                'name.min' => 'El nombre del producto debe tener al menos 3 caracteres',
                'cost.required' => 'El costo es requerido',
                'price.required' => 'El precio es requerido',
                'tam1.required' => 'El stock es requerido',
                'categoryid.not_in' => 'Elige un nombre de categoría diferente de Elegir',
                'barcode.required' => 'El SKU es Obligatorio',
                'barcode.min' => 'El SKU debe tener al menos 6 caracteres',
                'saborID.not_in' => 'Elige el nombre de un sabor diferente de Elegir',
            ];
    
            $this->validate($rules, $messages);
    
            $count = Product::where('barcode', $this->barcode)->count();
            // Validación adicional para los campos select
            if ($this->categoryid === 'Elegir' || $this->estado === 'Elegir' || $this->saborID === 'Elegir') {
                $this->emit('global-msg', 'Por favor, selecciona opciones válidas para los campos select.');
                $this->emit('producto-creado');
                return;
            }
    
            if ($count > 0) {
                $this->emit('global-msg', 'El Sku ya existe.');
                $this->emit('producto-creado');
                return;
            }
    
            $this->stock = 0;
            $this->alerts = 0;
            if (
                empty($this->name) || empty($this->cost) || empty($this->price) || empty($this->estado) ||
                empty($this->descripcion) || empty($this->categoryid) ||
                empty($this->saborID) || empty($this->tam1) || empty($this->size_id)
            ) {
    
                $this->emit('global-msg', 'Falta por completar algunos campos!');
                $this->emit('producto-creado');
                return;
            }
    
    
            /*try {
                    
                $userid = Auth()->user()->id;
                $categoryName = $this->categoryid;
                $categoryId = $this->getCategoryIdFromWooCommerce($categoryName);
    
            } catch (Exception $e) {
                $this->emit('global-msg', 'Error');
                $this->emit('producto-creado');
                return;
            }*/
            $userid = Auth()->user()->id;
    
            $barcodeNumber = rand(pow(10, 9), pow(10, 10) - 1);
            $product = Product::create([
                'name' => $this->name,
                'cost' => $this->cost,
                'price' => $this->cost,
                'estado' => $this->estado,
                'barcode' => $this->barcode,
                'stock' => $this->stock,
                'descripcion' => $this->descripcion,
                'alerts' => $this->alerts,
                'category_id' => $this->categoryid,
                'user_id' => $userid,
                'sabor_id' => $this->saborID,
                'tam1' => $this->tam1,
                'tam2' => $this->tam2,
                'size_id' => $this->size_id,
                'EstaEnWoocomerce' => 'no',
                'TieneKey' => 'NO',
                'visible' => 'no',
                'QB_id' => 0
            ]);
            $this->emit('creando');
            // Obtener el ID del producto creado
            $productId = $product->id;
    
    
            //  $this->createProductInWooCommerce($product, $categoryId);
    
            if ($this->image) {
                $customFileName = uniqid() . '_.' . $this->image->extension();
                $this->image->storeAs('public/products', $customFileName);
                $product->image = $customFileName;
                $product->save();
            }
            $user = Auth()->user()->name;
            $inspector = Inspectors::create([
                'user' => $user,
                'action' => 'Creo el producto: ' . $this->name,
                'seccion' => 'Products'
            ]);
            $this->resetUI();
            $this->emit('global-msg', 'Producto Agregado');
            $this->emit('product-added', 'Producto Registrado', $barcodeNumber);

        }catch(Exception $e){
            $errorString = '';
            if ($e instanceof ValidationException) {
                $errors = $e->errors();

                // Convertir los errores a una cadena                
                foreach ($errors as $fieldErrors) {
                    foreach ($fieldErrors as $error) {
                        $errorString .= $error . ' ';
                    }
                }

                // Eliminar el último espacio extra
                $errorString = rtrim($errorString);
            } else {
                $errorString = $e->getMessage();
            }
            $this->emit('producto-creado');
            $this->emit('global-msg', 'Error: ' . $errorString);
            throw $e;
        }

       
    }
    private function getCategoryIdFromWooCommerce($categoryName)
    {
        // Configurar la URL y los datos para la solicitud a la API de WooCommerce para obtener las categorías
        $url = 'https://kdlatinfood.com/wp-json/wc/v3/products';

        $response = Http::withBasicAuth('ck_8e38a879e7f6ce0d56e34c525de194a60c2e2ce5', 'cs_723eab16e53f3607fd38984b00f763310cc4f473')->get($url);

        if ($response->successful()) {
            $categories = $response->json();
            foreach ($categories as $category) {
                if ($category['name'] == $categoryName) {
                    return $category['id'];
                }
            }
        }

        // Si no se encuentra la categoría en WooCommerce, puedes manejarlo según tus necesidades
        // Por ejemplo, lanzar una excepción o asignar un valor predeterminado
        return 0;
    }

    private function createProductInWooCommerce($product, $categoryId)
    {
        // Configurar la URL y los datos para la solicitud a la API de WooCommerce para crear el producto
        $url = 'https://kdlatinfood.com/wp-json/wc/v3/products';

        $response = Http::withBasicAuth('ck_8e38a879e7f6ce0d56e34c525de194a60c2e2ce5', 'cs_723eab16e53f3607fd38984b00f763310cc4f473')->post($url, [
            'name' => $product->name,
            'sku' => $product->barcode,
            'regular_price' => $product->price,
            'stock_quantity' => $product->stock,
            'category_ids' => [$categoryId],
            // Otros campos del producto...
        ]);

        if ($response->successful()) {
            // El producto se creó correctamente en WooCommerce
            // Puedes realizar alguna acción adicional si lo deseas
            // Por ejemplo, registrar una entrada en los archivos de registro
            Log::info("Producto creado en WooCommerce con SKU: {$product->barcode}");
        } else {
            // Error al crear el producto en WooCommerce
            // Puedes manejar el error según tus necesidades
            Log::error("Error al crear el producto en WooCommerce con SKU: {$product->barcode}");
        }

        $user = Auth()->user()->name;
        $inspector = Inspectors::create([
            'user' => $user,
            'action' => 'Creo un producto en woocomerce ',
            'seccion' => 'Products'
        ]);
        $this->emit('global-msg', 'Producto Registrado en WooCommerce');
    }

    public function CrearProWoo($id)
    {
        // Obtener datos del producto
        $pro = Product::find($id);

        $sku = $pro->barcode;
        $nombre = $pro->product->name;
        $precio = $pro->price_public;
        $descripcion = $pro->descripcion;
        $stock = $pro->stock;
        $desc = $pro->descripcion;
        $categoria_nombre = $pro->category->name;
        $categoria_id = $pro->category_id;
        $size = $pro->size->size;


        $url = 'https://kdlatinfood.com/wp-json/wc/v3/products';

        $imageUrl = 'https://kdlatinfood.com/intranet/public/storage/products/' . $pro->image;


        try {
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
                        'id' => $categoria_id,
                        'name' => $categoria_nombre,
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
                $pro->EstaEnWoocomerce = 'si';
                $pro->save();

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

    /*public function CrearProWoo($id)
    {
        // Obtener datos del producto
        $prod = Product::find($id);

        $sku = $prod->barcode;
        $nombre = $prod->name;
        $precio = $prod->price_public;
        $descripcion = $prod->descripcion;
        $stock = $prod->stock;
        $desc = $prod->descripcion;
        $categoria_nombre = $prod->category->name;
        $categoria_id = $prod->category_id;
        $size = $prod->size->size;


        $url = 'https://kdlatinfood.com/wp-json/wc/v3/products';

        $imageUrl = 'https://kdlatinfood.com/intranet/public/storage/products/' . $prod->image;


        try {
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
                        'id' => $categoria_id,
                        'name' => $categoria_nombre,
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
                $prod->EstaEnWoocomerce = 'si';
                $prod->save();

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
    */

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

            $presentations = Presentacion::where('products_id',$id)->get();
            foreach ($presentations as $pre) {
                $pre->visible = 'no';
                $pre->save();
            }
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
        }

        public function novisible($id)
        {
            // Obtener el producto
            $product = Product::find($id);
            // Asignar la clave al campo KeyProduct
            $product->visible = 'si';

            $product->save();


            $presentations = Presentacion::where('products_id',$id)->get();
            foreach ($presentations as $pre) {
                $pre->visible = 'si';
                $pre->save();
            }

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
        } 

    public function visiblep($id)
    {
        try {
            DB::beginTransaction();
            // Obtener el producto
            $product = Presentacion::find($id);
            // Asignar la clave al campo KeyProduct
            $product->visible = 'no';
            $product->save();
            $this->visibleProduct($product->products_id, $id);

            /*$numPre = Presentacion::where('products_id', $product->products_id)->where('id', '!=', $id)->count();
            if($numPre == 0){
                $products = Product::find($product->products_id);
                $products->visible = 'no';
                $products->save();
            }*/

            // Imprimir la clave generada para verificar
            $this->emit('global-msg', "El producto ha sido ocultado");
            $this->showPresentations($product->products_id);
            $this->emit('producto-creado');
            $user = Auth()->user()->name;
            $inspector = Inspectors::create([
                'user' => $user,
                'action' => 'oculto un producto ',
                'seccion' => 'Products'
            ]);
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $this->emit('producto-creado');
            $this->emit('global-msg', "Nose puedo cambiar visibilidad");
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // para actualizar el producto la visivilidad a no
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
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
    public function novisiblep($id)
    {
        try {
            DB::beginTransaction();
            // Obtener el producto
            $product = Presentacion::find($id);
            //$this->noVisibleProduct($product->products_id, $id);
            // Asignar la clave al campo KeyProduct
            $product->visible = 'si';
            $product->save();
            
            $this->noVisibleProduct($product->products_id, $id);
            /*$numPre = Presentacion::where('products_id', $product->products_id)->where('id', '!=', $id)->count();
            if($numPre == 0){
                $products = Product::find($product->products_id);
                $products->visible = 'si';
                $products->save();
            }*/

            $this->showPresentations($product->products_id);
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
        } catch (Exception $e) {
            DB::rollBack();
            $this->emit('producto-creado');
            $this->emit('global-msg', "Nose puedo cambiar visibilidad");
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    /*public function Edit(Product $product)
    {
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
        $this->size_id = $product->size_id;
        $this->image = null;
        //$this->emit('producto-creado');
        $this->emit('modal-show', 'Show modal');
    }*/

    public function getProductForId($productId = false)
    {
        $data_raw_complete = [];
        $filePath = storage_path('app/public/relationship/products.json');
        if ($productId != false) {


            if (file_exists($filePath)) {
                $jsonContent = file_get_contents($filePath);
                $data_raw = json_decode($jsonContent, true);
                //dd($data);
                foreach ($data_raw as $item_raw) {
                    if (in_array($productId, $item_raw['products'])) {
                        if ($this->type == "CRUDO") {
                            if (!empty($item_raw['raw'])) {
                                $array_raw = $item_raw['raw'];
                                foreach ($array_raw as $items) {
                                    array_push($data_raw_complete, Product::where('id', $items)->first());
                                }

                            }
                        }
                        if ($this->type == "PRECOCIDO") {
                            if (!empty($item_raw['precooked'])) {
                                $array_raw = $item_raw['precooked'];
                                foreach ($array_raw as $items) {
                                    array_push($data_raw_complete, Product::where('id', $items)->first());
                                }
                            }
                        }
                    }

                }
            }
        } else {
            if (file_exists($filePath)) {
                $jsonContent = file_get_contents($filePath);
                $data_raw = json_decode($jsonContent, true);
                //dd($data);
                foreach ($data_raw as $item_raw) {
                    if ($this->type == "CRUDO") {
                        if (!empty($item_raw['raw'])) {
                            $array_raw = $item_raw['raw'];
                            $products_raw = Product::where('id', $array_raw[0])->
                                where('category_id', $this->category_id)->first();
                            if ($products_raw) {
                                $data_raw_new = $products_raw;
                                $data_raw_new->name = $item_raw['name'];
                                array_push($data_raw_complete, $products_raw);
                            }


                        }
                    } else {
                        if (!empty($item_raw['precooked'])) {
                            $array_raw = $item_raw['precooked'];
                            $products_raw = Product::where('id', $array_raw[0])->
                                where('category_id', $this->category_id)->first();
                            if ($products_raw) {
                                $data_raw_new = $products_raw;
                                $data_raw_new->name = $item_raw['name'];
                                array_push($data_raw_complete, $products_raw);
                            }


                        }
                    }

                }
            }
        }

        return $data_raw_complete;
    }

    public function EditProduct($id)
    {
        $this->presentations = [];
        $product = Product::find($id);
        $this->selected_id = $product->id;
        $this->name = $product->name;
        $this->barcode = $product->barcode;
        $this->cost = $product->cost;
        $this->estado = $product->estado;
        $this->descripcion = $product->descripcion;
        $this->price = round($product->cost * $product->tam1, 2);
        $this->stock = $product->stock;
        $this->alerts = $product->alerts;
        $this->categoryid = $product->category_id;
        $this->saborID = $product->sabor_id;
        $this->tam1 = $product->tam1;
        $this->tam2 = $product->tam2;
        $this->size_id = $product->size_id;
        $this->image = null;
        //$this->select_options_products = true;
        $this->emit('producto-creado');
        $this->emit('modal-show', 'Show modal');
    }



    public function Update()
    {
        //$this->select_options_list_products = $this->getProductForId($this->selected_id);
        $this->presentations = [];
        $rules = [
            'name' => "required|min:3",
            'cost' => 'required',
            'price' => 'required',
            'categoryid' => 'required|not_in:Elegir',
            'saborID' => 'required|not_in:Elegir',
        ];

        $messages = [
            'name.required' => 'Nombre del producto requerido',
            'name.min' => 'El nombre del producto debe tener al menos 3 caracteres',
            'cost.required' => 'El costo es requerido',
            'price.required' => 'El precio es requerido',
            'stock.required' => 'El stock es requerido',
            'alerts.required' => 'Ingresa el valor mínimo en existencias',
            'categoryid.not_in' => 'Elige un nombre de categoría diferente de Elegir',
            'saborID.not_in' => 'Elige el nombre de un sabor diferente de Elegir',
        ];

        $this->validate($rules, $messages);

        if ($this->categoryid === 'Elegir' || $this->estado === 'Elegir' || $this->saborID === 'Elegir') {
            $this->emit('global-msg', 'Por favor, selecciona opciones válidas para los campos select.');
            $this->emit('producto-creado');
            return;
        }

        $this->stock = 0;
        $this->alerts = 0;
        if (
            empty($this->name) || empty($this->cost) || empty($this->price) || empty($this->estado) ||
            empty($this->descripcion) || empty($this->categoryid) ||
            empty($this->saborID) || empty($this->tam1) || empty($this->size_id)
        ) {

            $this->emit('global-msg', 'Falta por completar algunos campos!');
            $this->emit('producto-creado');
            return;
        }


        $product = Product::find($this->selected_id);

        //   $product = Product::find($this->selected_id);

        // Verificar si se ha cambiado el campo "estado" a "PRE-COCIDO"

        // Actualizar el producto existente
        $product->update([
            'name' => $this->name,
            'cost' => $this->cost,
            'price' => $this->cost,
            'barcode' => $this->barcode,
            'stock' => 0,
            'estado' => $this->estado,
            'descripcion' => $this->descripcion,
            'alerts' => 0,
            'category_id' => $this->categoryid,
            'tam1' => $this->tam1,
            'tam2' => $this->tam2,
            'sabor_id' => $this->saborID,
            'size_id' => $this->size_id
        ]);

        /*$pre = Presentacion::where('products_id', $this->selected_id)->get();
        foreach ($pre as $item) {
            $this->WOOCONTROLLER->CrearProWoo($item->id);
        }*/

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
        //$this->updateWooCommerceStock($this->barcode, $this->stock);

        $user = Auth()->user()->name;
        $inspector = Inspectors::create([
            'user' => $user,
            'action' => 'Actualizo : ' . $this->name,
            'seccion' => 'Products'
        ]);

        //$this->resetUI();
        $this->emit('product-updated', 'Producto Actualizado');
        $this->emit('global-msg', 'Producto Actualizado');
        $this->emit('producto-creado');
    }

    private function updateWooCommerceStock($barcode, $stock)
    {
        // Configurar la URL y los datos para la solicitud a la API de WooCommerce
        $url = 'https://kdlatinfood.com/wp-json/wc/v3/products';
        $productId = null;

        // Buscar el producto en WooCommerce por SKU ($barcode)
        $response = Http::withBasicAuth('ck_8e38a879e7f6ce0d56e34c525de194a60c2e2ce5', 'cs_723eab16e53f3607fd38984b00f763310cc4f473')
            ->get($url, ['sku' => $barcode]);

        if ($response->successful()) {
            $products = $response->json();
            if (!empty($products)) {
                // Obtener el ID del producto en WooCommerce
                $productId = $products[0]['id'];
            }
        }

        if ($productId) {
            // Actualizar el stock del producto en WooCommerce
            $response = Http::withBasicAuth('ck_8e38a879e7f6ce0d56e34c525de194a60c2e2ce5', 'cs_723eab16e53f3607fd38984b00f763310cc4f473')
                ->put("$url/$productId", ['stock_quantity' => $stock]);

            if ($response->successful()) {
                // El stock se actualizó correctamente en WooCommerce
                // Puedes realizar alguna acción adicional si lo deseas
                // Por ejemplo, registrar una entrada en los archivos de registro

            } else {
                // Error al actualizar el stock en WooCommerce
                // Puedes manejar el error según tus necesidades

            }
        } else {
            // No se encontró el producto en WooCommerce por SKU
            // Puedes manejar esta situación según tus necesidades
            dd('No se Econtro el producto');
        }
    }
    public function resetUI()
    {
        $this->emit('producto-creado');
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
        $this->sizes_id = 'Elegir';
        $this->tam1 = 0;
        $this->tam2 = 0;
        $this->presentacion_selected = 0;
        $this->resetValidation();
    }

    protected $listeners = [
        'deleteRow' => 'Destroy',
        'deletePre' => 'DestroyPresentacion',
        'crearWC' => 'CrearProWoo',
        'wcProductCreated' => 'wcProductCreated',
        'loaderQuickbooksAll' => 'loaderQuickbooksAll',
        'QuickboksPresentacion' => 'loaderQuickbooksPresentacion',
        'modal-show1' => 'ModalAddProduct',
    ];
    public function wcProductCreated($data)
    {
        if ($data['success']) {
            $this->emit('swal:alert', [
                'type' => 'success',
                'title' => '¡Producto creado!',
                'text' => 'El producto se ha creado correctamente.',
            ]);

            // Cierra el modal o realiza otra acción aquí
            $this->closeModal();
        } else {
            $this->emit('swal:alert', [
                'type' => 'error',
                'title' => 'Error al crear producto',
                'text' => $data['message'],
            ]);
        }
    }


    public function recargarPagina()
    {
        $this->emit('global-msg', "SE CREO CORRECTAMENTE");
        $this->dispatchBrowserEvent('recargar-pagina');
    }


    public function Destroy(Product $product)
    {
        try {
            $user = Auth()->user()->name;

            $Presentaciones = Presentacion::where('products_id', $product->id)->get();
            foreach ($Presentaciones as $pre) {
                $isDetails = SaleDetail::where('presentaciones_id', $pre->id)->count();
                if ($isDetails > 0) {
                    $this->emit('global-msg', 'No se puede eliminar este producto porque tiene ventas asociadad.');
                    return;
                }
            }

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
        } catch (Exception $e) {
            $this->emit('global-msg', 'No se pudo eliminar : ' . $e->getMessage());
        }
    }

    public function DestroyPresentacion(Presentacion $product)
    {
        try {
            $user = Auth()->user()->name;
            $existDetails = SaleDetail::where('presentaciones_id', $product->id)->count();
            if ($existDetails === 0) {
                $delete = $this->quickBooksService->deleteProduct($product->id);//Quickbooks
                $delete = true;
                if ($delete) {
                    $product->delete(); 
                    $this->resetUI();
                    $this->emit('product-deleted', 'Presentacion Eliminado');
                    $this->emit('global-msg', 'Presentacion Eliminado');
                    $inspector = Inspectors::create([
                        'user' => $user,
                        'action' => 'Elimnino una Presentacion ',
                        'seccion' => 'Presentacion'
                    ]);
                } else {
                    $this->emit('global-msg', "Error: No se pudo eliminar. ");
                }
            } else {
                $this->emit('global-msg', "Error: No se pudo eliminar porque la presentacion ya tiene ventas realizadas.");
            }
        } catch (Exception $e) {
            $this->emit('global-msg', 'No se pudo eliminar : ' . $e->getMessage());
        }
    }


    /*API */
    public function showAll()
    {
        try {
            $products = Product::with('category')
                ->where('visible', 'si')
                ->where('TieneKey', 'si')
                ->get()
                ->sortBy(function ($product) {
                    return $product->category->name;
                })
                ->values()
                ->all();

            $formattedProducts = [];
            foreach ($products as $product) {
                $formattedProducts[] = [
                    'id' => $product->id,
                    'name' => $product->name,
                    'EstaEnWoocomerce' => $product->EstaEnWoocomerce,
                    'barcode' => $product->barcode,
                    'sabor_id' => $product->sabor->nombre,
                    'cost' => floatval($product->cost),
                    'price' => floatval($product->price),
                    'stock' => $product->stock,
                    'alerts' => $product->alerts,
                    'image' => asset('storage/products/' . $product->image),
                    'category_id' => $product->category->name,
                    'descripcion' => $product->descripcion,
                    'estado' => $product->estado,
                    'TieneKey' => $product->TieneKey,
                    'KeyProduct' => $product->KeyProduct,
                    'user_id' => $product->user_id,
                    'tam1' => $product->tam1,
                    'tam2' => $product->tam2,
                ];
            }

            return response()->json($formattedProducts);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'An error occurred',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    public function findId($productId)
    {
        try {
            // Buscar el producto por ID
            $product = Product::with('category')
                ->where('id', $productId)
                ->where('visible', 'si')
                ->where('TieneKey', 'si')
                ->first();

            if (!$product) {
                return response()->json([
                    'message' => 'Product not found',
                ], Response::HTTP_NOT_FOUND);
            }

            // Obtener otros productos del mismo sabor que cumplen las condiciones
            $relatedProducts = Product::with('category', 'sabor')
                ->where('sabor_id', $product->sabor_id)
                ->where('visible', 'si')
                ->where('TieneKey', 'si')
                ->where('id', '!=', $productId) // Excluir el producto actual
                ->get();

            // Formatear el producto encontrado
            $formattedProduct = [
                'id' => $product->id,
                'name' => $product->name,
                'EstaEnWoocomerce' => $product->EstaEnWoocomerce,
                'barcode' => $product->barcode,
                'sabor_id' => $product->sabor->nombre,
                'cost' => floatval($product->cost),
                'price' => floatval($product->price),
                'stock' => $product->stock,
                'alerts' => $product->alerts,
                'image' => asset('storage/products/' . $product->image),
                'category_id' => $product->category->name,
                'descripcion' => $product->descripcion,
                'estado' => $product->estado,
                'TieneKey' => $product->TieneKey,
                'KeyProduct' => $product->KeyProduct,
                'user_id' => $product->user_id,
                'tam1' => $product->tam1,
                'tam2' => $product->tam2,
            ];

            // Formatear los productos relacionados
            $formattedRelatedProducts = [];
            foreach ($relatedProducts as $relatedProduct) {
                $formattedRelatedProducts[] = [
                    'id' => $relatedProduct->id,
                    'name' => $relatedProduct->name,
                    'EstaEnWoocomerce' => $relatedProduct->EstaEnWoocomerce,
                    'barcode' => $relatedProduct->barcode,
                    'sabor_id' => $relatedProduct->sabor->nombre,
                    'cost' => floatval($relatedProduct->cost),
                    'price' => floatval($relatedProduct->price),
                    'stock' => $relatedProduct->stock,
                    'alerts' => $relatedProduct->alerts,
                    'image' => asset('storage/products/' . $relatedProduct->image),
                    'category_id' => $relatedProduct->category->name,
                    'descripcion' => $relatedProduct->descripcion,
                    'estado' => $relatedProduct->estado,
                    'TieneKey' => $relatedProduct->TieneKey,
                    'KeyProduct' => $relatedProduct->KeyProduct,
                    'user_id' => $relatedProduct->user_id,
                    'tam1' => $relatedProduct->tam1,
                    'tam2' => $relatedProduct->tam2,
                ];
            }

            return response()->json([
                'product' => $formattedProduct,
                // 'related_products' => $formattedRelatedProducts,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'An error occurred',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function ProductsPreCocidos()
    {
        try {
            $products = Product::with('category')
                ->where('estado', 'PRECOCIDO')
                ->where('visible', 'si')
                ->where('TieneKey', 'si')
                ->get()
                ->sortBy(function ($product) {
                    return $product->category->name;
                })
                ->values()
                ->all();

            $formattedProducts = [];
            foreach ($products as $product) {
                $formattedProducts[] = [
                    'id' => $product->id,
                    'name' => $product->name,
                    'EstaEnWoocomerce' => $product->EstaEnWoocomerce,
                    'barcode' => $product->barcode,
                    'sabor_id' => $product->sabor->nombre,
                    'cost' => floatval($product->cost),
                    'price' => floatval($product->price),
                    'stock' => $product->stock,
                    'alerts' => $product->alerts,
                    'image' => asset('storage/products/' . $product->image),
                    'category_id' => $product->category->name,
                    'descripcion' => $product->descripcion,
                    'estado' => $product->estado,
                    'TieneKey' => $product->TieneKey,
                    'KeyProduct' => $product->KeyProduct,
                    'user_id' => $product->user_id,
                    'tam1' => $product->tam1,
                    'tam2' => $product->tam2,
                ];
            }

            return response()->json($formattedProducts);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'An error occurred',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    public function ProductsCrudos()
    {
        try {
            $products = Product::with('category')
                ->where('estado', 'CRUDO')
                ->where('visible', 'si')
                ->where('TieneKey', 'si')
                ->get()
                ->sortBy(function ($product) {
                    return $product->category->name;
                })
                ->values()
                ->all();

            $formattedProducts = [];
            foreach ($products as $product) {
                $formattedProducts[] = [
                    'id' => $product->id,
                    'name' => $product->name,
                    'EstaEnWoocomerce' => $product->EstaEnWoocomerce,
                    'barcode' => $product->barcode,
                    'sabor_id' => $product->sabor->nombre,
                    'cost' => floatval($product->cost),
                    'price' => floatval($product->price),
                    'stock' => $product->stock,
                    'alerts' => $product->alerts,
                    'image' => asset('storage/products/' . $product->image),
                    'category_id' => $product->category->name,
                    'descripcion' => $product->descripcion,
                    'estado' => $product->estado,
                    'TieneKey' => $product->TieneKey,
                    'KeyProduct' => $product->KeyProduct,
                    'user_id' => $product->user_id,
                    'tam1' => $product->tam1,
                    'tam2' => $product->tam2,
                    'visible' => $product->visible
                ];
            }

            return response()->json($formattedProducts);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'An error occurred',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function showAllKEY()
    {
        try {
            // Obtener presentaciones con productos que tienen key, junto con tamaño y categoría
            $presentaciones = Presentacion::with(['product.category', 'product.sabor', 'product', 'size'])
                ->where('TieneKey', "SI")
                ->get()
                ->sortBy(function ($p) {
                    return $p->product->category->name;
                })
                ->values();

            // Formatear resultados
            $formatted = $presentaciones->map(function ($p) {
                return [
                    'presentacion_id' => $p->id,
                    'barcode' => $p->barcode,
                    'stock_box' => $p->stock_box,
                    'stock_items' => $p->stock_items,
                    'price' => floatval($p->price),
                    'size' => $p->size->size ?? null,
                    'producto' => [
                        'id' => $p->product->id,
                        'name' => $p->product->name,
                        'EstaEnWoocomerce' => $p->product->EstaEnWoocomerce,
                        'barcode' => $p->product->barcode,
                        'sabor_id' => $p->product->sabor->nombre ?? null,
                        'cost' => floatval($p->product->cost),
                        'price' => floatval($p->product->price),
                        'stock' => $p->product->stock,
                        'image' => $p->product->image,
                        'tam' => $p->product->tam1,
                        'estado' => $p->product->estado,
                    ]
                ];
            });

            return response()->json($formatted);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /*public function showAllKEY()
    {
        try {
            // Obtener las presentaciones junto con el producto y el tamaño
            $presentaciones = Presentacion::with(['product', 'size'])
                ->whereHas('product', function ($query) {
                    $query->where('TieneKey', 'si');
                })
                ->get()
                ->sortBy(function ($presentacion) {
                    return $presentacion->product->category->name; // Ordenar por categoría del producto
                })
                ->values()
                ->all();
  
            $formattedPresentaciones = [];
            foreach ($presentaciones as $presentacion) {
                //$discount = Discounts::where();
                $formattedPresentaciones[] = [
                    'presentacion_id' => $presentacion->id,
                    'barcode' => $presentacion->barcode,
                    'stock_box' => $presentacion->stock_box,
                    'stock_items' => $presentacion->stock_items,
                    'price' => floatval($presentacion->price),
                    'size' => $presentacion->size->size, // Tamaño de la presentación
                    'producto' => [
                        'id' => $presentacion->product->id,
                        'name' => $presentacion->product->name,
                        'EstaEnWoocomerce' => $presentacion->product->EstaEnWoocomerce,
                        'barcode' => $presentacion->product->barcode,
                        'sabor_id' => $presentacion->product->sabor->nombre,
                        'cost' => floatval($presentacion->product->cost),
                        'price' => floatval($presentacion->product->price),
                        'stock' => $presentacion->product->stock,
                        'image' => $presentacion->product->image,
                        'tam' => $presentacion->product->tam1,
                        'estado' => $presentacion->product->estado,
                    ]
                ];
            }

            return response()->json($formattedPresentaciones);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'An error occurred',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }*/


    public function showAllKEY_($CostumerID)
    {
        try {
            // Obtener las presentaciones junto con el producto y el tamaño
            $presentaciones = Presentacion::with(['product', 'size'])
                ->whereHas('product', function ($query) {
                    $query->where('TieneKey', 'si');
                })
                ->get()
                ->sortBy(function ($presentacion) {
                    return $presentacion->product->category->name; // Ordenar por categoría del producto
                })
                ->values()
                ->all();

            $formattedPresentaciones = [];
            foreach ($presentaciones as $presentacion) {
                $discount = Discounts::where('customer_id', $CostumerID)->where('presentacion_id', $presentacion->id)->first();
                $valDiscount = $discount ? $discount->discount : null;
                //$discount = Discounts::where();
                $formattedPresentaciones[] = [
                    'presentacion_id' => $presentacion->id,
                    'barcode' => $presentacion->barcode,
                    'stock_box' => $presentacion->stock_box,
                    'stock_items' => $presentacion->stock_items,
                    'discount' => $valDiscount,
                    'price' => floatval($presentacion->price),
                    'size' => $presentacion->size->size, // Tamaño de la presentación
                    'producto' => [
                        'id' => $presentacion->product->id,
                        'name' => $presentacion->product->name,
                        'EstaEnWoocomerce' => $presentacion->product->EstaEnWoocomerce,
                        'barcode' => $presentacion->product->barcode,
                        'sabor_id' => $presentacion->product->sabor->nombre,
                        'cost' => floatval($presentacion->product->cost),
                        'price' => floatval($presentacion->product->price),
                        'stock' => $presentacion->product->stock,
                        'image' => $presentacion->product->image,
                        'tam' => $presentacion->product->tam1,
                        'estado' => $presentacion->product->estado,
                    ]
                ];
            }

            return response()->json($formattedPresentaciones);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'An error occurred',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }



    public function createApi(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required',
                'cost' => 'required',
                'price' => 'required',
                'stock' => 'required',
                'category_id' => 'required',
            ]);

            $product = Product::create($request->all());

            return response()->json([
                'message' => 'Product created successfully',
                'data' => $product,
            ], Response::HTTP_CREATED);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'An error occurred',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function updateApi(Request $request, $id)
    {
        try {
            $product = Product::findOrFail($id);

            $request->validate([
                'name' => 'required',
                'cost' => 'required',
                'price' => 'required',
                'stock' => 'required',
                'category_id' => 'required',
            ]);

            $product->update($request->all());

            return response()->json([
                'message' => 'Product updated successfully',
                'data' => $product,
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Product not found',
            ], Response::HTTP_NOT_FOUND);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'An error occurred',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    public function findProductsByCategory($categoryId)
    {
        try {
            $products = Product::whereHas('category', function ($query) use ($categoryId) {
                $query->where('id', $categoryId);
            })->get();

            $responseData = [];

            foreach ($products as $product) {
                $responseData[] = [
                    'id' => $product->id,
                    'name' => $product->name,
                    'cost' => $product->cost,
                    'price' => $product->price,
                    'stock' => $product->stock,
                    'image' => asset('storage/products/' . $product->image),
                    'category' => [
                        'id' => $product->category->id,
                        'name' => $product->category->name,
                    ],
                ];
            }

            return response()->json([
                'data' => $responseData,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'An error occurred',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function findApi($id)
    {
        try {
            $product = Product::with('category')->findOrFail($id);

            return response()->json([
                'data' => [
                    'id' => $product->id,
                    'name' => $product->name,
                    'cost' => $product->cost,
                    'price' => $product->price,
                    'stock' => $product->stock,
                    'category' => [
                        'id' => $product->category->id,
                        'name' => $product->category->name,
                    ],
                ],
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Product not found',
            ], Response::HTTP_NOT_FOUND);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'An error occurred',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    public function Addfavorite($id_producto, $id_cliente)
    {
        $favorito = new Favoritos([
            'id_producto' => $id_producto,
            'id_cliente' => $id_cliente,
        ]);
        $favorito->save();

        return response()->json(['message' => 'Producto añadido a favoritos'], 201);
    }

    public function Deletefavorite($id_producto, $id_cliente)
    {
        try {
            $favorito = Favoritos::where('id_producto', $id_producto)
                ->where('id_cliente', $id_cliente)
                ->first();

            if ($favorito) {
                $favorito->delete();
                return response()->json(['message' => 'Producto eliminado de favoritos'], 200);
            } else {
                return response()->json(['message' => 'Producto no encontrado en favoritos'], 404);
            }
        } catch (Exception $e) {
            return response()->json(['message' => 'Error al eliminar producto de favoritos'], 500);
        }
    }
    public function checkFavorite($id_producto, $id_cliente)
    {
        try {
            $favorito = Favoritos::where('id_producto', $id_producto)
                ->where('id_cliente', $id_cliente)
                ->exists();

            return response()->json(['is_favorite' => $favorito], 200);
        } catch (QueryException $e) {
            return response()->json(['message' => 'Error al verificar si el producto es favorito'], 500);
        }
    }

    public function GetAllfavorite($id_cliente)
    {
        try {
            $favoritos = Favoritos::where('id_cliente', $id_cliente)
                ->with('producto')
                ->get();

            if ($favoritos->isEmpty()) {
                return response()->json(['message' => 'El cliente no tiene productos favoritos'], 404);
            }

            $productosFavoritos = $favoritos->pluck('producto');

            return response()->json($productosFavoritos, 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'El cliente no existe'], 404);
        } catch (QueryException $e) {
            return response()->json(['message' => 'Error al obtener productos favoritos'], 500);
        }
    }

    public function AddCart($id_producto, $id_cliente, $items)
    {
        try {
            $carrito = Carrito::where('id_producto', $id_producto)
                ->where('id_cliente', $id_cliente)
                ->first();

            if ($carrito) {
                $carrito->items += $items;
                $carrito->save();
            } else {
                $carrito = new Carrito([
                    'id_producto' => $id_producto,
                    'id_cliente' => $id_cliente,
                    'items' => $items,
                ]);
                $carrito->save();
            }

            return response()->json(['message' => 'Producto(s) agregado(s) al carrito'], 201);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Producto o cliente no encontrado'], 404);
        } catch (Exception $e) {
            return response()->json(['message' => 'Error al agregar producto al carrito'], 500);
        }
    }
    public function DeleteItemCart($id_producto, $id_cliente)
    {
        try {
            $carrito = Carrito::where('id_producto', $id_producto)
                ->where('id_cliente', $id_cliente)
                ->first();

            if ($carrito) {
                $carrito->delete();
                return response()->json(['message' => 'Producto eliminado del carrito'], 200);
            } else {
                return response()->json(['message' => 'Producto no encontrado en el carrito'], 404);
            }
        } catch (Exception $e) {
            return response()->json(['message' => 'Error al eliminar producto del carrito'], 500);
        }
    }
    public function UpdateItemCart($id_producto, $id_cliente, $items)
    {
        try {
            $carrito = Carrito::where('id_producto', $id_producto)
                ->where('id_cliente', $id_cliente)
                ->first();

            if ($carrito) {
                $carrito->items += $items; // Sumamos la cantidad actual con la nueva cantidad
                $carrito->save();
                return response()->json(['message' => 'Cantidad de items actualizada'], 200);
            } else {
                return response()->json(['message' => 'Producto no encontrado en el carrito'], 404);
            }
        } catch (Exception $e) {
            return response()->json(['message' => 'Error al actualizar cantidad de items'], 500);
        }
    }

    public function GetAllCart($id_cliente)
    {
        try {
            $carrito = Carrito::where('id_cliente', $id_cliente)
                ->with('producto')
                ->get();

            if ($carrito->isEmpty()) {
                return response()->json(['message' => 'El cliente no tiene productos en el carrito'], 404);
            }

            $totalProductos = $carrito->sum('items'); // Calculamos la cantidad total de productos en el carrito

            $response = [
                'message' => 'Carrito del cliente obtenido exitosamente',
                'totalProductos' => $totalProductos,
                'carrito' => $carrito,
            ];

            return response()->json($response, 200);
        } catch (Exception $e) {
            return response()->json(['message' => 'Error al obtener productos del carrito'], 500);
        }
    }
    public function DecrementItemCart($id_producto, $id_cliente, $items)
    {
        try {
            $carrito = Carrito::where('id_producto', $id_producto)
                ->where('id_cliente', $id_cliente)
                ->first();

            if ($carrito) {
                $nuevaCantidad = $carrito->items - $items;

                if ($nuevaCantidad < 0) {
                    return response()->json(['message' => 'La cantidad no puede ser negativa'], 400);
                }

                if ($nuevaCantidad == 0) {
                    // Si la nueva cantidad es 0, eliminamos el producto del carrito
                    $carrito->delete();
                } else {
                    // Si la nueva cantidad no es 0, actualizamos la cantidad de items en el carrito
                    $carrito->items = $nuevaCantidad;
                    $carrito->save();
                }

                return response()->json(['message' => 'Cantidad de items actualizada'], 200);
            } else {
                return response()->json(['message' => 'Producto no encontrado en el carrito'], 404);
            }
        } catch (Exception $e) {
            return response()->json(['message' => 'Error al actualizar cantidad de items'], 500);
        }
    }

    public function loaderQuickbooksAll($product_id)
    {

        $presentaciones = Presentacion::where('products_id', $product_id)->get();
        foreach ($presentaciones as $item) {
            if ($item->QB_id != "") {
                $this->quickBooksService->update_product($item);
            } else {
                $this->quickBooksService->create_product($item);
            }

        }

        $this->emit('producto-creado');
        $this->emit('global-msg', "Los productos han sido actualizados en el Quickbooks");
    }

    public function loaderQuickbooksPresentacion($presentacion_id)
    {
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


    public function ModalAddProduct(){

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
        $this->dispatchBrowserEvent('show-modal1');
        $this->emit('producto-creado');
    }
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
        $name_product = $product->product->name . " " .$product->size->size. " ".$product->product->estado; 
        //$product = Product::find($productId);

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
