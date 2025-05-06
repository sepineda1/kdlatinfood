<?php
namespace App\Services;
use Exception;
use App\Models\Presentacion;
use App\Models\Inspectors;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Automattic\WooCommerce\Client;
use Automattic\WooCommerce\HttpClient\HttpClientException;

class WoocomerceService
{
    private $woocommerceUrl;
    private $consumerKey;
    private $consumerSecret;
    private $woocommerce;

    public function __construct()
    {
        $config = config('woocomerce');
        $this->woocommerceUrl = $config['URL'];
        $this->consumerKey = $config['CONSUMER_KEY'];
        $this->consumerSecret = $config['CONSUMER_SECRET'];

        $this->woocommerce = new Client(
            'https://kdlatinfood.com',
            $this->consumerKey,
            $this->consumerSecret,
            [
                'wp_api' => true,
                'version' => 'wc/v3',
            ]
        );
    }

    //Cargar los los clientes en cache.
    //
    public function testConnection()
    {
        try {
            $products = Cache::get('products');
            if(!$products){
                $response = $this->woocommerce->get('products');
                Cache::put('products',$response);
                $products =  $response;
            }
            return [
                'status' => 'success',
                'data' => $products
            ];
            
        } catch (HttpClientException $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage(),
                'request' => $e->getRequest(),
                'response' => $e->getResponse()
            ];
        }
    }

    /*private function httpConnect($endpoint, $query = [], $method = 'GET')
    {
        $url = "{$this->woocommerceUrl}/wp-json/wc/v3/{$endpoint}";

        $response = Http::withBasicAuth($this->consumerKey, $this->consumerSecret)
            ->{$method}($url, $query);

        if ($response->successful()) {
            return $response->json();
        } else {
            throw new Exception("Error en la solicitud: {$response->status()} - {$response->body()}");
        }
    }*/

    private function ClientConnect(){
        return new Client(
            'https://kdlatinfood.com', // URL de tu tienda WooCommerce
            $this->consumerKey,  // Tu consumer key de WooCommerce
            $this->consumerSecret, // Tu consumer secret de WooCommerce
            [
                'wp_api' => true,
                'version' => 'wc/v3',
                'timeout' => 30,
            ]
        );
    }

    /**
     * GESTION DE PRODUCTOS WOOCOMERCE
     */

    /*private function getIdCategoryForName($categoryName)
    {
        $response = $this->HttpConect("{$this->woocommerceUrl}products");
        if ($response->successful()) {
            $categories = $response->json();
            foreach ($categories as $category) {
                if ($category['name'] == $categoryName) {
                    return $category['id'];
                }
            }
        }
        return 0;
    }*/

    private function getIdCategoryByName($categoryName)
    {
        try {
            $categories = $this->woocommerce->get('products/categories', ['search' => $categoryName]);
            if (!empty($categories)) {
                return $categories[0]->id;
            }
        } catch (Exception $e) {
            // Manejar la excepción según sea necesario
        }
        return 0;
    }

    /*public function getOrCreateCategory($nombre, $parent_id = 0)
    {
        // Buscar la categoría en WooCommerce
        $url = "{$this->woocommerceUrl}products/categories";
        $query = ['search' => $nombre];
        $response = $this->HttpConect($url, $query);

        if ($response->successful() && !empty($response->json())) {
            return $response->json()[0]['id']; // Retorna el ID si existe
        }

        // Crear la categoría si no existe
        $query = ['name' => $nombre, 'parent' => $parent_id];
        $createCategoryResponse = $this->HttpConect($url, $query);


        if ($createCategoryResponse->successful()) {
            return $createCategoryResponse->json()['id']; // Retorna el ID de la nueva categoría
        }

        return 0; //Error al crear la categoria
    }*/

    public function getOrCreateCategory($name, $parent_id = 0)
    {
        // Buscar la categoría en WooCommerce
        $categoryId = $this->getIdCategoryByName($name);
        if ($categoryId) {
            return $categoryId;
        }

        // Crear la categoría si no existe
        try {
            $data = [
                'name' => $name,
                'parent' => $parent_id,
            ];
            $response = $this->woocommerce->post('products/categories', $data);
            return $response['id'];
        } catch (Exception $e) {
            // Manejar la excepción según sea necesario
        }

        return 0; // Error al crear la categoría
    }

    /*public function create_product($id)
    {
        $pre = Presentacion::find($id);
        $sku = $pre->barcode;
        $nombre = $pre->product->name . " " . $pre->size->size . " " . $pre->product->estado;
        $precio = $pre->price;
        $descripcion = $pre->product->descripcion;
        $stock = $pre->stock_box;
        $categoria_nombre = $pre->product->category->name;
        $size = $pre->size->size;
        $imageUrl = 'https://kdlatinfood.com/intranet/public/storage/products/' . $pre->product->image;

        try {
            // Verificar si el producto ya existe por SKU
            $skuCheckResponse = $this->HttpConect("{$this->woocommerceUrl}products", ['sku' => $sku]);
            if ($skuCheckResponse->successful() && !empty($skuCheckResponse->json())) {
                return true; // El producto ya existe
            }

            // Crear categoría principal (Ej: "Empanadas")
            $categoria_padre_id = $this->getOrCreateCategory($categoria_nombre);

            // Crear subcategoría (Ej: "Empanada de Queso Cruda")
            $subcategoria_id = $this->getOrCreateCategory($pre->product->name, $categoria_padre_id);

            // Crear el array con los datos del producto
            $data = [
                'name' => $nombre,
                'sku' => $sku,
                'regular_price' => $precio,
                'stock_quantity' => $stock,
                'description' => $descripcion,
                'short_description' => $descripcion,
                'categories' => [
                    [
                        'id' => $subcategoria_id,
                    ],
                ],
                'default_attributes' => [
                    [
                        'name' => 'Size',
                        'option' => $size,
                    ],
                ],
                'images' => [
                    [
                        'src' => $imageUrl,
                        'name' => $nombre,
                        'alt' => $nombre,
                    ],
                ],
            ];

            // Crear el producto en WooCommerce
            $response = $this->HttpConect("{$this->woocommerceUrl}products", $data);

            if ($response->successful()) {
                // Actualizar el estado del producto en la base de datos
                $pre->EstaEnWoocomerce = 'si';
                $pre->save();

                // Registrar la acción en el log de inspectores
                $user = auth()->user()->name;
                Inspectors::create([
                    'user' => $user,
                    'action' => 'Creó un producto en WooCommerce',
                    'seccion' => 'Products',
                ]);

                return true; // Producto creado exitosamente
            } else {
                throw new Exception('Error al crear el producto en WooCommerce');
            }
        } catch (Exception $e) {
            // Manejar la excepción
            throw new Exception("Error: " . $e->getMessage());
        }
    }*/

    public function create_product($id)
    {
        $pre = Presentacion::find($id);
        if (!$pre) {
            throw new Exception('Presentación no encontrada.');
        }

        $sku = $pre->barcode;
        $nombre = $pre->product->name . " " . $pre->size->size . " " . $pre->product->estado;
        $precio = $pre->price;
        $descripcion = $pre->product->descripcion;
        $stock = $pre->stock_box;
        $categoria_nombre = $pre->product->category->name;
        $size = $pre->size->size;
        $imageUrl = 'https://kdlatinfood.com/intranet/public/storage/products/' . $pre->product->image;

        try {
            // Verificar si el producto ya existe por SKU
            $products = $this->woocommerce->get('products', ['sku' => $sku]);
            if (!empty($products)) {
                return true; // El producto ya existe
            }

            // Crear categoría principal
            $categoria_padre_id = $this->getOrCreateCategory($categoria_nombre);

            // Crear subcategoría
            $subcategoria_id = $this->getOrCreateCategory($pre->product->name, $categoria_padre_id);

            // Crear el array con los datos del producto
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
                'attributes' => [
                    [
                        'name' => 'Size',
                        'option' => $size,
                    ],
                ],
                'images' => [
                    [
                        'src' => $imageUrl,
                        'name' => $nombre,
                        'alt' => $nombre,
                    ],
                ],
            ];

            // Crear el producto en WooCommerce
            $this->woocommerce->post('products', $data);
            return true; // Producto creado exitosamente
        } catch (Exception $e) {
            // Manejar la excepción
            return false;
        }
    }


    /*public function adjustments_inventory($barcode, $stock)
    {
        // Configurar la URL y los datos para la solicitud a la API de WooCommerce
        $url = 'https://kdlatinfood.com/wp-json/wc/v3/products';
        $productId = null;

        // Buscar el producto en WooCommerce por SKU ($barcode)
        $response = $this->HttpConect("{$this->woocommerceUrl}products", ['sku' => $barcode]);


        if ($response->successful()) {
            $products = $response->json();
            if (!empty($products)) {
                // Obtener el ID del producto en WooCommerce
                $productId = $products[0]['id'];
            }
        }

        if ($productId) {

            // Actualizar el stock del producto en WooCommerce
            $response = $this->HttpConect("{$this->woocommerceUrl}products/{$productId}", ['stock_quantity' => $stock]);

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

    }*/

    public function adjustInventory($barcode, $stock)
    {
        try {
            // Buscar el producto en WooCommerce por SKU
            $products = $this->woocommerce->get('products', ['sku' => $barcode]);
            if (empty($products)) {
                throw new Exception('Producto no encontrado.');
            }

            $productId = $products[0]['id'];

            // Actualizar el stock del producto en WooCommerce
            $data = ['stock_quantity' => $stock];
            $this->woocommerce->put("products/{$productId}", $data);

            // El stock se actualizó correctamente en WooCommerce
            return true;
        } catch (Exception $e) {
            // Manejar la excepción
            throw new Exception("Error al ajustar el inventario: " . $e->getMessage());
        }
    }

    /*GESTION DE CLIENTES*/

    public function create_client($email, $name, $lastName, $password, $img = null)
    {
        try {
            // Verificar si el cliente ya existe
            $existingCustomer = $this->ClientConnect()->get('customers', ['email' => $email]);

            if (!empty($existingCustomer)) {
                throw new Exception('El cliente ya existe en WooCommerce.');
            }

            // Crear el cliente en WooCommerce
            $response = $this->ClientConnect()->post('customers', [
                'email' => $email,
                'first_name' => $name,
                'last_name' => $lastName,
                'password' => $password,
                'username' => $email,
                'avatar_url' => $img,
            ]);

            // Verificar si la solicitud fue exitosa
            if ($response) {
                $createdCustomerId = $response->id;
                return $createdCustomerId; // Retornar el ID del cliente creado
            }

            throw new Exception('Error al crear el cliente en WooCommerce.');
        } catch (Exception $e) {
            // Manejar la excepción
            throw new Exception('Error en WooCommerce: ' . $e->getMessage());
        }
    }

    public function destroy_client($woocommerce_cliente_id)
    {
        $woocommerce = $this->ClientConnect();
        $response = $woocommerce->delete("customers/{$woocommerce_cliente_id}", ['force' => true]);
        return $response;
    }

     //METODO EN SEGUNDO PLANO
    public function create_client_sync(array $client)
    {
        try {
            // Verificar si el cliente ya existe
            $customers = $this->woocommerce->get('customers', ['email' => $client['email']]);
            if (!empty($customers)) {
                throw new Exception('El cliente ya existe en WooCommerce.');
            }

            // Crear el cliente en WooCommerce
            $data = [
                'email' => $client['email'],
                'first_name' => $client['name'],
                'last_name' => $client['last_name'],
                'password' => "123",
                'username' => $client['email'],
                'avatar_url' => "",
            ];
            $response = $this->woocommerce->post('customers', $data);

            return $response['id']; // Retornar el ID del cliente creado
        } catch (Exception $e) {
            // Manejar la excepción
            
            //throw new Exception('Error en WooCommerce: ' . $e->getMessage());
        }
    }

    public function deleteClient($woocommerceClientId)
    {
        try {
            // Eliminar el cliente en WooCommerce
            $this->woocommerce->delete("customers/{$woocommerceClientId}", ['force' => true]);
            return true;
        } catch (Exception $e) { 
            return false;
        }
    
    }


}