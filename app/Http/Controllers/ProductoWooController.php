<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Automattic\WooCommerce\Client;
use App\Models\Product;
use App\Models\Presentacion;
use App\Models\Inspectors;
use Illuminate\Support\Facades\Http;
use Illuminate\Database\QueryException;
use Exception;

class ProductoWooController extends Controller
{

    private $WOOCOMMERCE_URL;
    private $WOOCOMMERCE_CONSUMER_KEY;
    private $WOOCOMMERCE_CONSUMER_SECRET;

    public function __construct()
    {
        $this->WOOCOMMERCE_URL = 'https://kdlatinfood.com/wp-json/wc/v3/';
        $this->WOOCOMMERCE_CONSUMER_KEY = 'ck_8e38a879e7f6ce0d56e34c525de194a60c2e2ce5';
        $this->WOOCOMMERCE_CONSUMER_SECRET = 'cs_723eab16e53f3607fd38984b00f763310cc4f473';
    }

    public function getOrCreateCategory($nombre, $parent_id = 0)
    {
        // 🔹 Buscar la categoría en WooCommerce
        $response = Http::withBasicAuth($this->WOOCOMMERCE_CONSUMER_KEY, $this->WOOCOMMERCE_CONSUMER_SECRET)
            ->get("{$this->WOOCOMMERCE_URL}products/categories", [
                'search' => $nombre
            ]);

        if ($response->successful() && !empty($response->json())) {
            return $response->json()[0]['id']; // Retorna el ID si existe
        }

        // 🔹 Crear la categoría si no existe
        $createCategoryResponse = Http::withBasicAuth($this->WOOCOMMERCE_CONSUMER_KEY, $this->WOOCOMMERCE_CONSUMER_SECRET)
            ->post("{$this->WOOCOMMERCE_URL}products/categories", [
                'name' => $nombre,
                'parent' => $parent_id
            ]);

        if ($createCategoryResponse->successful()) {
            return $createCategoryResponse->json()['id']; // Retorna el ID de la nueva categoría
        }

        throw new Exception("Error al crear la categoría $nombre en WooCommerce");
    }

    public function CrearProWoo($id)
    {
        try {
            // Obtener datos del producto
            $pre = Presentacion::findOrFail($id);

            $sku = $pre->barcode;
            $nombre = $pre->product->name . " " . $pre->size->size . " " . $pre->product->estado;
            $precio = $pre->price;
            $descripcion = $pre->product->descripcion;
            $stock = $pre->stock_box;
            $categoria_nombre = $pre->product->category->name;
            $size = $pre->size->size;

            $url = "{$this->WOOCOMMERCE_URL}products";

            // Verificar si el producto ya existe en WooCommerce
            $skuCheckResponse = Http::withBasicAuth($this->WOOCOMMERCE_CONSUMER_KEY, $this->WOOCOMMERCE_CONSUMER_SECRET)
                ->get($url, ['sku' => $sku]);

                 // 🔹 Obtener o crear categorías
            $categoria_padre_id = $this->getOrCreateCategory($categoria_nombre);
            $subcategoria_id = $this->getOrCreateCategory($pre->product->name, $categoria_padre_id);
            $imageUrl = 'https://kdlatinfood.com/intranet/public/storage/products/' . $pre->product->image;

            if ($skuCheckResponse->successful() && !empty($skuCheckResponse->json())) {

                 // Producto ya existe: Obtener ID de WooCommerce y actualizarlo
                 $productData = [
                    'name' => $nombre,
                    'sku' => $sku,
                    'regular_price' => $precio,
                    'stock_quantity' => $stock,
                    'description' => $descripcion,
                    'short_description' => $descripcion,
                    'categories' => [['id' => $subcategoria_id]],
                    'default_attributes' => [['name' => 'Size', 'option' => $size]],
                    'images' => [['src' => $imageUrl]],
                ];

                 $productId = $skuCheckResponse->json()[0]['id'];
                 $updateResponse = Http::withBasicAuth(
                    $this->WOOCOMMERCE_CONSUMER_KEY,
                    $this->WOOCOMMERCE_CONSUMER_SECRET
                )->put("{$url}/{$productId}", $productData);

                if ($updateResponse->successful()) {
                    return true;
                }else{
                    throw new Exception('Error al actualizar en WooCommerce');
                } // El producto ya existe en WooCommerce
            }

        
            // Datos del producto para WooCommerce
            $data = [
                'name' => $nombre,
                'sku' => $sku,
                'regular_price' => $precio,
                'stock_quantity' => $stock,
                'description' => $descripcion,
                'short_description' => $descripcion,
                'categories' => [
                    ['id' => $subcategoria_id],
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

            // Realizar la solicitud HTTP con los datos del producto
            $response = Http::withBasicAuth(
                $this->WOOCOMMERCE_CONSUMER_KEY,
                $this->WOOCOMMERCE_CONSUMER_SECRET
            )->post($url, $data);

            if ($response->successful()) {
                $pre->EstaEnWoocomerce = 'si';
                $pre->save();

                // Registrar acción del usuario
                Inspectors::create([
                    'user' => auth()->user()->name,
                    'action' => "Creó un producto en WooCommerce: $nombre",
                    'seccion' => 'Products'
                ]);

                return true; // El producto fue creado exitosamente
            } else {
                throw new Exception('Error al crear el producto en WooCommerce');
            }
        } catch (Exception $e) {
            throw new Exception("Error: " . $e->getMessage()); // Lanza la excepción con el mensaje de error
        }
    }





    public function syncProducts(Request $request)
    {
        // Obtener los productos de tu CRM
        $products = Product::all();

        // Configuración de la API de WooCommerce
        $woocommerce = new Client(
            env('WOOCOMMERCE_STORE_URL'),
            env('WOOCOMMERCE_CONSUMER_KEY'),
            env('WOOCOMMERCE_CONSUMER_SECRET'),
            [
                'wp_api' => true,
                'version' => 'wc/v3',
            ]
        );

        // Variables para la barra de progreso
        $totalProducts = count($products);
        $successCount = 0;

        // Iterar sobre los productos y enviarlos a WooCommerce
        foreach ($products as $product) {
            $data = [
                'name' => $product->name,
                'sku' => $product->barcode,
                'regular_price' => $product->price,
                'stock_quantity' => $product->stock,

                // Agrega más campos según tus necesidades
            ];

            try {
                $response = $woocommerce->post('products', $data);

                if ($woocommerce->isSuccessful($response)) {
                    $successCount++;
                }
                dd($response);
            } catch (\Exception $e) {
                // Mostrar información sobre la excepción para depuración
                dd($e);
            }
        }

        return view('product.sync', [
            'totalProducts' => $totalProducts,
            'successCount' => $successCount,
        ]);
    }
}



