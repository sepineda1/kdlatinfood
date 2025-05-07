<?php

use App\Http\Controllers\ErroresImpresoraController;
use App\Http\Livewire\PresentacionesController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Livewire\ProductsController;
use App\Http\Controllers\ProductImageController;
use App\Http\Livewire\CategoriesController;
use App\Http\Livewire\ClientesController;
use App\Http\Livewire\SaboresController;
use App\Http\Livewire\InsumosController;
use App\Http\Livewire\LotesNew;
use App\Http\Livewire\PosController;
use App\Http\Livewire\DespachosController;
use App\Http\Livewire\EnviosController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Livewire\UsersController;
use App\Http\Controllers\CarritoController;
use App\Http\Livewire\DiscountController;
use App\Http\Controllers\DeliveryTypeController;
use App\Http\Controllers\MantenimientoSistemaController;
use App\Http\Controllers\CatalogoPaymentTypeController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

/*API REST FOR APP */

Route::put('update-token', [UsersController::class, 'updateNotificationToken']);
Route::post('login-user', [UsersController::class, 'LoginUserAdmin']);
Route::post('/update-access-token', [UsersController::class, 'update_access_token']);
Route::post('login-client', [UsersController::class, 'LoginUserClient']);
//Route::post('/create-client', [UsersController::class, 'createCustomer']);
//Route::post('/create-user', [UsersController::class, 'createUser']);

Route::post('save-error', [ErroresImpresoraController::class, 'SaveError']);


/*Despachos */
Route::post('verify-qrcode', [EnviosController::class, 'verifyQRCode']);
Route::post('verify-qrcode1', [EnviosController::class, 'verifyQRCode1']);
Route::post('updateActualSales', [EnviosController::class, 'updateSalesStatusAPI']);
Route::post('/add-product-to-sale', [DespachosController::class, 'addProductToSale']);
Route::post('/update-sale', [DespachosController::class, 'updateSaleAPI']);
Route::delete('/sales/borrar/{saleDetailId}/{userID}', [DespachosController::class, 'removeProductFromSale']);
//API PARA ELIMINAR EN CASCADA LA VENTA
Route::delete('/sales/delete/{saleId}/{userID}', [DespachosController::class, 'removeSale']);
//Route::delete('/sales/delete/{saleId}/', [DespachosController::class, 'removeSale']);

Route::get('/despachos', [DespachosController::class, 'getAllSales']);
Route::get('/despachos-pending', [DespachosController::class, 'getAllSalesPending']);
Route::get('/sales/{id}', [DespachosController::class, 'getSaleDetails']);
Route::put('/sales/cargar/{id}', [DespachosController::class, 'cargarSale']);
//Route::put('/sales/cargar2/{id}', [DespachosController::class, 'cargarSale2']);

Route::put('/sales/FIN/{id}', [EnviosController::class, 'updateFinApi']);
Route::get('/sales/detalle/pendiente/{id}', [DespachosController::class, 'getSaleDetailsPendiente']); 
Route::put('/decrementProductSale/{quantity}/{saleDetailId}/{userID}', [DespachosController::class, 'decrementQuantityToSale']);

/* PRODUCTOS*/
Route::get('products', [ProductsController::class, 'ShowAll']);
Route::get('products/detail/{productId}', [ProductsController::class, 'findId']);
Route::get('showAllKEY/', [ProductsController::class, 'showAllKEY']);
Route::get('showAllKEY/{CostumerID}', [ProductsController::class, 'showAllKEY_']);
Route::get('products/create', [ProductsController::class, 'CreateApi']);
Route::get('products/update/{id}', [ProductsController::class, 'UpdateApi']);
Route::get('products/find/{id}', [ProductsController::class, 'FindApi']);
Route::get('products/findprod/{id}', [ProductsController::class, 'findProductsByCategory']);
Route::get('products/crudos', [ProductsController::class, 'ProductsCrudos']);
Route::get('products/precocidos', [ProductsController::class, 'ProductsPreCocidos']);
Route::get('presentaciones/product/{productId}', [PresentacionesController::class, 'getPresentacionesByProduct']);
Route::get('presentaciones/product/{productId}/{customerID}', [PresentacionesController::class, 'getPresentacionesByProductForUser']);

/* CARRITO */
Route::prefix('carrito')->group(function () {
    // Ruta para agregar un producto al carrito
    Route::post('agregar', [CarritoController::class, 'addToCart']);

    // Ruta para obtener el carrito por ID del cliente
    Route::get('{id_cliente}', [CarritoController::class, 'getCartByCustomer']);

    // Ruta para eliminar un producto específico del carrito
    Route::post('decrement', [CarritoController::class, 'decrementProductFromCart']);
    Route::get('total/{id_cliente}', [CarritoController::class, 'getTotalCartByCustomer']);

    Route::post('pagar', [CarritoController::class, 'payCart']);
    Route::post('pagar2', [CarritoController::class, 'payCart2']);
    Route::post('pagar3', [CarritoController::class, 'payCart3']);

    // Ruta para vaciar el carrito del cliente (eliminar todos los productos)
    Route::delete('vaciar/{id_cliente}', [CarritoController::class, 'clearCart']);

    Route::delete('eliminarPresentacion/{id_carrito}/{id_presentacion}', [CarritoController::class, 'eliminarPresentacion']);
});
/*ACTUALIZAR MAPA */
//Endpoint para Iniciar RUTA
//Endpoint para Actualizar Posicion en la ruta

/*DISCOUNTS-> DESCUENTO */
Route::get('discounts/{customer_id}', [DiscountController::class, 'getDiscountCustomer_API']);
Route::get('isDiscountAvailable/{customer_id}/{presentacion_id}', [DiscountController::class, 'isDiscountAvailable_API']);

/*CATEGORIAS */
Route::get('categories', [CategoriesController::class, 'ShowAll']);
Route::get('categories/create', [CategoriesController::class, 'CreateApi']);
Route::get('categories/update/{id}', [CategoriesController::class, 'UpdateApi']);
Route::get('categories/find/{id}', [CategoriesController::class, 'FindApi']);

/*LOTES */
Route::get('lotes', [LotesNew::class, 'ShowAll']);
Route::get('lotes/create', [LotesNew::class, 'CreateApi']);
Route::get('lotes/update/{id}', [LotesNew::class, 'UpdateApi']);
Route::get('lotes/find/{barcode}', [LotesNew::class, 'FindApi']);

/*SABORES */
Route::get('sabores', [SaboresController::class, 'ShowAll']);
Route::get('sabores/create', [SaboresController::class, 'CreateApi']);
//Route::get('lotes/update/{id}', [SaboresController::class, 'UpdateApi']);
Route::get('sabores/find/{barcode}', [SaboresController::class, 'FindApi']);

/*INSUMOS */
Route::get('insumos', [InsumosController::class, 'ShowAll']);
Route::get('insumos/create', [InsumosController::class, 'CreateApi']);
//Route::get('lotes/update/{id}', [InsumosController::class, 'UpdateApi']);
Route::get('insumos/find/{barcode}', [InsumosController::class, 'FindApi']);

/*CLIENTES */

Route::post('clientes/create', [ClientesController::class, 'createApi']);
Route::put('clientes/update/{id}', [ClientesController::class, 'editApi']);
Route::get('clientes/find/{id}', [ClientesController::class, 'getByIdApi']);
Route::get('clientes/findUser/{id}', [ClientesController::class, 'FindUser']);
Route::put('clientes/edit-address/{id}', [ClientesController::class, 'editApiaAdress']);

//Route::post('login', [LoginController::class, 'loginApi']);
Route::post('logout', [LoginController::class, 'logoutApi']);

//EndPoint tipos de Entregas, y lista de entregas
Route::get('catalogo/deliverytypes', [DeliveryTypeController::class,'listCatalog']);
Route::post('store/deliverytypes', [DeliveryTypeController::class,'storeDeliveryTypeAPI']);
Route::get('catalogo/paymentype', [CatalogoPaymentTypeController::class,'getAll']);
Route::get('catalogo/paymentype/{id}', [CatalogoPaymentTypeController::class,'getById']);


/*compra*/
Route::post('PosAPI/payWithCredit', [PosController::class, 'payWithCreditApi']);

Route::get('storage/images', function () {
    $imageExtensions = ['jpg', 'jpeg', 'png', 'gif'];
    $imageDirectories = ['categories', 'denominations', 'products', 'users'];
    $images = [];

    foreach ($imageDirectories as $directory) {
        $path = storage_path('app/public/' . $directory);
        if (File::exists($path)) {
            $files = array_diff(scandir($path), ['.', '..']);
            $imagesInDirectory = array_filter($files, function ($file) use ($imageExtensions) {
                $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                return in_array($extension, $imageExtensions);
            });
            $images = array_merge($images, $imagesInDirectory);
        }
    }

    return response()->json($images);
});
Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});


//FAVORITOS
Route::prefix('favoritos')->group(function () {

    Route::post('/add/{id_producto}/{id_cliente}', [ProductsController::class, 'Addfavorite']);
    Route::delete('/delete/{id_producto}/{id_cliente}', [ProductsController::class, 'Deletefavorite']);
    Route::get('/all/{id_cliente}', [ProductsController::class, 'GetAllfavorite']);
});


Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});


Route::post('comando', [PosController::class, 'comando']);

Route::get('/products/images', [ProductImageController::class, 'index']);


//EndPoint
Route::post('/webhook/intuit', function (Request $request) {
    // Guarda el webhook en un log para depuración
    Log::info('Webhook recibido:', $request->all());

    // Retornar una respuesta HTTP 200 OK
    return response()->json(['status' => 'Webhook recibido'], 200);
});
Route::get('/mantenimientoSistema/{costumerID}', [MantenimientoSistemaController::class, 'existeMantenimiento']);

