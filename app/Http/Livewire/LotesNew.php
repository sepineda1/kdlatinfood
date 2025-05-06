<?php

namespace App\Http\Livewire;

use App\Models\Presentacion;
use Livewire\Component;

//Modelos
use App\Models\Product;
use App\Models\User;
use App\Models\Inspectors;
use App\Models\Insumo;
use App\Models\Sabores;
use App\Models\Category;
use App\Models\Lotes;

//Extras
use Carbon\Carbon;
use Illuminate\Support\Facades\Barcode;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;

//api
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use PhpParser\Node\Stmt\Foreach_;
use Symfony\Component\HttpFoundation\Response;
use App\Services\QuickBooksService;

class LotesNew extends Component
{
    //Componente
    public $componentName;
    public $selected_id;
    public $Sabor;
    public $LoteInsumo;
    public $search;

    //Variables para Formulario
    public $Fecha_Vencimiento;
    public $User;

    public $totalLibras;

    //SubFormulario
    public $subform;

    public $stockReal = '';

    public $stockActual = 0;
    //Extras

    public function mount()
    {
        $this->subform = [
            [
                'id' => uniqid(),
                'BAR' => 'Elegir',
                'CANT' => '',
                'PYR' => '',
                'MAX' => '',
                'stock_items' => '',
                'libra_consumo' => '',
                'total_libras' => ''
            ]
        ];

        $this->User = Auth()->user()->name;
        $this->pageTitle = 'Listado';
        $this->componentName = 'Lotes';
        $this->LoteInsumo = 'Elegir';
        $this->Sabor = 'Elegir';
    }
    public function render()
    {
        //  $sabores = Sabores::orderBy('nombre')->get();
        $sabores = Sabores::orderBy('nombre')
            ->when($this->search, function ($query) {
                $query->where('nombre', 'like', '%' . $this->search . '%');
            })
            ->whereNotNull('libra_consumo')
            ->get();

        $lotesAsociados = [];

        foreach ($sabores as $sabor) {
            $lotes = Lotes::where('sabor_id', $sabor->id)->get();
            $lotesAsociados[$sabor->id] = $lotes->groupBy('CodigoBarras');
        }

        $insumo = Insumo::where('idSabor', $this->Sabor)->get();
        $product = Product::where('sabor_id', $this->Sabor)->get();
        $array_product = [];
        foreach ($product as $item) {
            //if($item->estado == "CRUDO"){
            $pre = Presentacion::where('products_id', $item->id)->where('visible','si')->where('TieneKey', 'SI')->get();
            foreach ($pre as $item2) {
                array_push($array_product, $item2);
            }
            //}

        }


        $array_product = collect($array_product);
        return view('livewire.LotesNew.lotes-new', [
            'product' => $product,
            'sabor' => $sabores,
            'lotesAsociados' => $lotesAsociados,
            'insumo' => $insumo,
            'subform' => $this->subform,
            'presentacion' => $array_product
        ])
            ->extends('layouts.theme.app')
            ->section('content');
    }

    public function haveKey(int $idLote, string $barcode, int $idPro)
    {
        try {
            $presentacion = Presentacion::where('barcode', $barcode)
                ->where('products_id', $idPro)
                ->first();
            if ($presentacion != null) {
                if ($presentacion->TieneKey == 'SI') {
                    // Generar la URL usando las propiedades del producto
                    $url = url('detail/pdf/lote/' . $idPro . '/' . $barcode . '/' . $idLote);
                    $this->emit('abrir-qr', $url);
                    return;
                }
                $this->emit('global-msg', 'Este producto no tiene Key');
            } else {
                $this->emit('global-msg', 'La presentación no existe');
            }
        } catch (\Throwable $th) {
            $this->emit('global-msg', 'Ocurrio un error');
        }
    }

    public function Store()
    {
        DB::beginTransaction();
        try {
            //User en Sesion
            $user = Auth()->user()->name;


            $rules = [
                'LoteInsumo' => 'required|not_in:Elegir',
                'Sabor' => 'required|not_in:Elegir',
            ];
            foreach ($this->subform as $index => $item) {
                $rules['subform.' . $index . '.BAR'] = 'required|not_in:Elegir';
                $rules['subform.' . $index . '.CANT'] = 'required';
            }

            $this->validate($rules);

            if ($this->stockReal < 0) {
                $this->emit('sale-error', "Has superado el limite de libras que puede asignar ese lote.");
                return;
            }


            foreach ($this->subform as $item) {

                if (isset($item['MAX']) && $item['CANT'] > $item['MAX']) {
                    $this->emit('sale-error', "Has superado el máximo de cajas permitidas en el ítem #" . ($index + 1));
                    return;
                }
                $lotes =
                    $lote = Lotes::create([
                        'User' => $user,
                        'sabor_id' => $this->Sabor,
                        'Fecha_Vencimiento' => $this->Fecha_Vencimiento = Carbon::now()->addMonths(6),
                        'CodigoBarras' => $this->extractIdAndBarcode($this->LoteInsumo)[1],
                        'SKU' => $item['BAR'],
                        'Cantidad_Articulos' => $item['CANT'],
                    ]);


                $product = Presentacion::findOrFail($lote->SKU);
                $productName = $product->product->name . " " . $product->size->size . " " . $product->product->estado;
                //$itemsCajas=$product->tam1*$item['CANT']; //voy por aqui

                $itemsCajas = $product->stock_items * $item['CANT']; // 50 * 3 = 150 Empanadas de Queso Mediana

                $product = Presentacion::with('size')->findOrFail($lote->SKU);
                $this->updateProductStock($lote->SKU, $lote->Cantidad_Articulos, $productName);
                $consumo = $product->consumoPorSabor($this->Sabor);
                $libra_consumo = $consumo->getConsumoEnLibras();  //0.025 Libras
                // Actualizar el stock del sabor
                $sabor = Sabores::findOrFail($this->Sabor);
                /*$PYR=$sabor->stock*$sabor->libra_consumo; // 134 productos  
                $impactoPYR=$itemsCajas*$sabor->libra_consumo; // 2 * 0.67 = 1.34
                $NuevoPYR=$PYR-$impactoPYR; // 134 - 1.34 = 132,66
                if ($sabor->libra_consumo == 0){
                    throw new \Exception("El consumo en libras es cero. Por favor, verifica");
                }
                $NuevoStock=$NuevoPYR/$sabor->libra_consumo;*/ // 132,66 / 0.67 = 198

                //$PYR=$sabor->stock*$libra_consumo; // Los productos totales que se pueden hacer con las libras = 7636Libras *  0.025libras = 

                $impactoPYR = $itemsCajas * $libra_consumo; // Libras Totales que se van a restar en hacer 150 empanadas =  150 * 0.025 = 3.75Libras
                $NuevoPYR = $sabor->stock - $impactoPYR; //Libras restantes 
                if ($libra_consumo == 0) {
                    throw new \Exception("El consumo en libras es cero. Por favor, verifica");
                }


                //$NuevoStock=$NuevoPYR/$libra_consumo;

                // dd($sabor->stock);
                $sabor->stock = $NuevoPYR; //  198
                //  dd($totsl." ,stock sabor: ".$sabor->stock." nuevo stock: ".$NuevoStock);
                $sabor->save();
                // Actualizar la cantidad de artículos en el modelo Insumo
                ///$insumo = Insumo::where('CodigoBarras', $this->LoteInsumo)->first();
                $insumo = Insumo::where('id', $this->extractIdAndBarcode($this->LoteInsumo)[0])->first();
                //$this->extractIdAndBarcode($this->LoteInsumo)[1],
                //$insumo->Cantidad_Articulos = $NuevoStock;
                $insumo->Cantidad_Articulos = $NuevoPYR;
                $insumo->save();
                $this->emit('producto-creado');
            }



            //inspectors
            $inspector = Inspectors::create([
                'user' => $user,
                'action' => 'Creo lote de productos, Codigo de barras: ' . $this->extractIdAndBarcode($this->LoteInsumo)[1],
                'seccion' => 'Lotes | Products'
            ]);
            DB::commit();

            $this->emit('lote-added', 'Lote Agregado');
            $this->emit('global-msg', 'Lote de productos CREADO');
            $this->resetUI();
        } catch (\Exception $e) {
            DB::rollBack();
            $this->emit('producto-creado');
            $this->emit('global-msg', 'Error al crear el lote: ' . $e->getMessage());
            $this->emit('sale-error', "Error : " . $e->getMessage());
            throw $e;
        }
    }

    public function updateProductStock($productId, $addedStock, $productName)
    {
        $product = Presentacion::findOrFail($productId); //addedStock son cajas 
        $currentStock = $product->stock_box; //obtengo stock de cajas del producto 30 cajas
        //$newStock = $currentStock + ($addedStock*$product->stock_items); // multiplico el estock añadido * cantidad de producto que tiene la caja CANTIDAD DE EMPANADA DE MANERA UNITARIA
        $newStock = $currentStock + $addedStock;
        //$product->update(['stock_box' => $newStock]);
        $product->stock_box = $newStock;
        $product->save();
        //$QUICK = new QuickBooksService();
        //$QUICK->update_product($product);
        //update_product
        //$product->update(['stock_items' => $newStock]);
        $this->emit('global-msg', "SE ACTUALIZÓ EL STOCK DE: $productName");
    }

    public function resetUI()
    {
        $this->subform = [
            [
                'id' => uniqid(),
                'BAR' => 'Elegir',
                'CANT' => '',
                'PYR' => '',
                'MAX' => '',
                'stock_items' => '',
                'libra_consumo' => '',
                'total_libras' => ''
            ]
        ];
        $this->LoteInsumo = 'Elegir';
        $this->Sabor = 'Elegir';
        $this->stockReal = '';
        $this->stockActual = 0;
    }

    //SubFormulario
    public function __construct()
    {
        $this->subform = [
            [
                'id' => uniqid(),
                'BAR' => 'Elegir',
                'CANT' => '',
                'PYR' => '',
                'MAX' => '',
                'stock_items' => '',
                'libra_consumo' => '',
                'total_libras' => ''
            ]
        ];
    }

    public function addItem()
    {
        $this->subform[] = [
            'id' => uniqid(),
            'BAR' => 'Elegir',
            'CANT' => '',
            'PYR' => '',
            'MAX' => '',
            'stock_items' => '',
            'libra_consumo' => '',
            'total_libras' => ''
        ];

        $this->emit('tableRendered');
        $this->emit('producto-creado');
        $this->recalculateTotalLibras();
        $this->updateMaxValues();
    }

    public function removeItem($index)
    {
        unset($this->subform[$index]);
        $this->subform = array_values($this->subform);
        $this->emit('tableRendered'); // Emitir el evento para actualizar la tabla del subformulario
        $this->emit('producto-creado');
        $this->recalculateTotalLibras();
        $this->updateMaxValues();
    }

    public function renderTable()
    {
        $this->emit('tableRendered'); // Emitir un evento para que Livewire renderice la tabla del subformulario
    }


    //Fin Subformulario

    //funciones del blade
    protected $listeners = [

        'Cambio' => 'updateEstado'
    ];

    public function extractIdAndBarcode($inputString)
    {
        // Verificar que la cadena no esté vacía
        if (!empty($inputString)) {
            // Separar la cadena utilizando el guion '-'
            return explode('-', $inputString);
        }

        return null; // En caso de que la cadena esté vacía o no sea válida
    }

    public function updateEstado(Presentacion $id, $cantidadPrecocido, $id_lote)
    {
        if (empty($cantidadPrecocido)) {
            $this->emit('global-msg', 'Introduzca una cantidad');
            return;
        }
        $sku = $id->barcode;
        $stockCrudo = $id->stock;

        // Calcular el barcode del producto precocido sumando 1 al barcode actual
        $barcodePrecocido = substr($sku, 0, -1) . (intval(substr($sku, -1)) + 1);

        // Buscar el producto precocido por su barcode
        $nextProduct = Presentacion::where('barcode', $barcodePrecocido)->first();

        if ($nextProduct) {
            // Actualizar el stock del producto precocido sumando la cantidadPrecocido
            //$itemsPasados=$nextProduct->tam1*$cantidadPrecocido;
            $itemsPasados = $cantidadPrecocido;

            $nextProduct->stock_box += $itemsPasados;
            $nextProduct->save();

            // Actualizar el stock del producto actual restando la cantidadPrecocido
            $stockRestante = $cantidadPrecocido;
            // dd("stock restante: ".$stockRestante);
            $id->stock_box -= $stockRestante;

            $id->save();

            // Buscar el lote por su ID
            $lote = Lotes::find($id_lote);

            if ($lote) {
                if ($cantidadPrecocido <= $lote->Cantidad_Articulos) {
                    // Restar la cantidadPrecocido de Cantidad_Articulos del lote
                    $lote->Cantidad_Articulos -= $cantidadPrecocido;
                    $lote->save();
                    $this->emit('global-msg', 'Paso de Crudo a Precocido');
                } else {
                    // La cantidadPrecocido es mayor que Cantidad_Articulos
                    $this->emit('global-msg', 'Excedió la cantidad disponible en el lote');
                }
            } else {
                $this->emit('global-msg', 'No se encontró el lote');
            }
        } else {
            $this->emit('global-msg', 'No se encontró el producto precocido');
        }
    }

    public function updatedSubform($value, $name)
    {
        
        [$index, $field] = explode('.', $name);

        if ($field === 'BAR' && isset($this->subform[$index]['BAR']) && $this->Sabor != 'Elegir' || $field === 'CANT') {

            if ($this->Sabor != 'Elegir') {
                $this->totalLibras = 0;
            }
            $presentacionId = $this->subform[$index]['BAR'];
            $lote = Insumo::where('id', $this->extractIdAndBarcode($this->LoteInsumo)[0])->first();

            $presentacion = Presentacion::find($presentacionId);
            if ($presentacion) {
                $consumo = $presentacion->consumoPorSabor($this->Sabor);
                if ($consumo && $consumo->libra_consumo > 0 && $lote) {
                    $calc = ($consumo->getConsumoEnLibras() > 0) ? floor($lote->Cantidad_Articulos / $consumo->getConsumoEnLibras()) : 0;
                    $this->subform[$index]['PYR'] = $calc;
                    $this->subform[$index]['MAX'] = ($presentacion->stock_items > 0) ? floor($calc / $presentacion->stock_items) : 0;
                    $this->subform[$index]['stock_items'] = $presentacion->stock_items;
                    $this->subform[$index]['libra_consumo'] = $consumo->getConsumoEnLibras();
                    //$this->subform[$index]['total_libras'] = ($this->subform[$index]['CANT'] != "") ? ($presentacion->stock_items * $this->subform[$index]['CANT']) * $consumo->getConsumoEnLibras() : 0;
                    if (is_numeric($this->subform[$index]['CANT'])) {
                        $cantidad = floatval($this->subform[$index]['CANT']);
                        $this->subform[$index]['total_libras'] = ($presentacion->stock_items * $cantidad) * $consumo->getConsumoEnLibras();
                    } else {
                        if ($this->subform[$index]['CANT'] != "") {
                            $this->subform[$index]['total_libras'] = 0;
                            $this->emit('sale-error', "La cantidad ingresada en el ítem #" . ($index + 1) . " no es válida. Solo se permiten números decimales.");
                        }
                    }

                    $this->recalculateTotalLibras();
                    $this->updateMaxValues();
                    $this->emit('hide-loader');
                } else {
                    $this->subform[$index]['PYR'] = '0';
                    $this->subform[$index]['MAX'] = '0';
                    $this->subform[$index]['stock_items'] = '0';
                    $this->subform[$index]['libra_consumo'] = '0';
                    $this->subform[$index]['total_libras'] = '0';
                    $this->emit('hide-loader');
                }
            }
        }
    }

    public function recalculateTotalLibras()
    {
        $this->totalLibras = 0;
        foreach ($this->subform as $item) {
            if (isset($item['total_libras']) && is_numeric($item['total_libras'])) {
                $this->totalLibras += $item['total_libras'];
            }
        }
        if ($this->stockActual > 0) {
            $this->stockReal = round($this->stockActual - $this->totalLibras, 2);
        }
    }

    public function updateMaxValues()
    {
        $librasRestantes = $this->stockActual;

        foreach ($this->subform as $index => $item) {
            if (!empty($item['BAR']) && $item['BAR'] !== 'Elegir') {
                $presentacion = Presentacion::find($item['BAR']);

                if ($presentacion && $this->Sabor != 'Elegir') {
                    $consumo = $presentacion->consumoPorSabor($this->Sabor);

                    if ($consumo && $consumo->libra_consumo > 0) {
                        $libraConsumo = $consumo->getConsumoEnLibras();
                        $unidadesXCaja = $presentacion->stock_items;

                        $productosMaximos = floor($librasRestantes / $libraConsumo);
                        $cajasMaximas = ($unidadesXCaja > 0)
                            ? floor($productosMaximos / $unidadesXCaja)
                            : 0;

                        $this->subform[$index]['PYR'] = $productosMaximos;
                        $this->subform[$index]['MAX'] = $cajasMaximas;

                        // Si ya tiene cantidad ingresada, calcula su impacto en libras y réstalo
                        if (!empty($item['CANT']) && is_numeric($item['CANT'])) {
                            $totalLibrasItem = $unidadesXCaja * $item['CANT'] * $libraConsumo;
                            $this->subform[$index]['total_libras'] = round($totalLibrasItem, 2);
                            $librasRestantes -= $totalLibrasItem;
                        }
                    } else {
                        $this->subform[$index]['PYR'] = 0;
                        $this->subform[$index]['MAX'] = 0;
                    }
                }
            }
        }

        $this->stockReal = round($librasRestantes, 2);
    }


    public function clearSubform()
    {
        $this->subform = [
            [
                'id' => uniqid(),
                'BAR' => 'Elegir',
                'CANT' => '',
                'PYR' => '',
                'MAX' => '',
                'stock_items' => '',
                'libra_consumo' => '',
                'total_libras' => ''
            ]
        ];
        $this->LoteInsumo = 'Elegir';
        $this->totalLibras = 0;
        $this->stockReal = '';
        $this->stockActual = 0;
        $this->emit('tableRendered');
    }

    public function updatedSabor()
    {
        //$this->emit('show-loader');

        $this->clearSubform();
        $this->emit('hide-loader');
    }





    /*public function updatedLoteInsumo()
{
    $this->stockActual = 0;
    $this->stockReal = 0;

    $lote = Insumo::where('id', $this->extractIdAndBarcode($this->LoteInsumo)[0])->first();

    if ($lote) {
        $this->stockActual = $lote->Cantidad_Articulos;

        // Recalcular stockReal usando el total de libras ya calculado
        $this->recalculateTotalLibras();

        // Validar si el stock real alcanza
        if ($this->stockReal < 0) {
            $this->emit('sale-error', 'Este lote no tiene suficiente stock para cubrir el consumo estimado.');
        }
    } else {
        $this->emit('sale-error', 'No se pudo encontrar el lote seleccionado.');
    }
}*/

    public function updatedLoteInsumo()
    {
        $this->stockActual = 0;
        $this->stockReal = 0;

        $lote = Insumo::where('id', $this->extractIdAndBarcode($this->LoteInsumo)[0])->first();

        if ($lote) {
            $this->stockActual = $lote->Cantidad_Articulos;

            // Recorrer todas las filas del subform para recalcular
            foreach ($this->subform as $index => $item) {
                if (!empty($item['BAR']) && $item['BAR'] !== 'Elegir' && $this->Sabor !== 'Elegir') {
                    $presentacion = Presentacion::find($item['BAR']);
                    if ($presentacion) {
                        $consumo = $presentacion->consumoPorSabor($this->Sabor);
                        if ($consumo && $consumo->libra_consumo > 0) {
                            $libraConsumo = $consumo->getConsumoEnLibras();
                            $calc = ($libraConsumo > 0) ? floor($lote->Cantidad_Articulos / $libraConsumo) : 0;

                            $this->subform[$index]['PYR'] = $calc;
                            $this->subform[$index]['MAX'] = ($presentacion->stock_items > 0)
                                ? floor($calc / $presentacion->stock_items)
                                : 0;
                            $this->subform[$index]['stock_items'] = $presentacion->stock_items;
                            $this->subform[$index]['libra_consumo'] = $libraConsumo;
                            $this->subform[$index]['total_libras'] = (!empty($item['CANT']))
                                ? ($presentacion->stock_items * $item['CANT']) * $libraConsumo
                                : 0;
                        } else {
                            $this->subform[$index]['PYR'] = '0';
                            $this->subform[$index]['MAX'] = '0';
                            $this->subform[$index]['stock_items'] = '0';
                            $this->subform[$index]['libra_consumo'] = '0';
                            $this->subform[$index]['total_libras'] = '0';
                        }
                    }
                }
            }

            // Luego recalcula el total de libras y stock restante
            $this->recalculateTotalLibras();
            $this->emit('hide-loader');

            if ($this->stockReal < 0) {
                $this->emit('sale-error', 'Este lote no tiene suficiente stock para cubrir el consumo estimado.');
                $this->emit('hide-loader');
            }

            $this->emit('tableRendered');
        } else {
            $this->emit('sale-error', 'No se pudo encontrar el lote seleccionado.');
            $this->emit('hide-loader');
        }
    }



    //api
    public function CreateApi(Request $request)
    {
        try {
            $rules = [
                'LoteInsumo' => 'required|not_in:Elegir',
                'Sabor' => 'required|not_in:Elegir',
            ];
            foreach ($request->input('subform') as $index => $item) {
                $rules['subform.' . $index . '.BAR'] = 'required|not_in:Elegir';
                $rules['subform.' . $index . '.CANT'] = 'required';
            }

            $this->validate($request, $rules);

            $user = Auth()->user()->name;

            foreach ($request->input('subform') as $item) {
                // Guardar cada fila como un registro independiente
                $lote = Lotes::create([
                    'User' => $user,
                    'sabor_id' => $request->input('Sabor'),
                    'Fecha_Vencimiento' => $this->Fecha_Vencimiento = Carbon::now()->addMonths(6),
                    'CodigoBarras' => $request->input('LoteInsumo'),
                    'SKU' => $item['BAR'],
                    'Cantidad_Articulos' => $item['CANT'],
                ]);

                // Obtener el nombre del producto
                $product = Presentacion::findOrFail($lote->SKU);
                $productName = $product->product->name . " " . $product->size->size . " " . $product->product->estado;

                // Actualizar el stock del producto
                $this->updateProductStock($lote->SKU, $lote->Cantidad_Articulos, $productName);

                // Actualizar el stock del sabor
                $sabor = Sabores::findOrFail($request->input('Sabor'));
                $sabor->stock -= $lote->Cantidad_Articulos;
                $sabor->save();
            }

            // Actualizar la cantidad de artículos en el modelo Insumo
            $insumo = Insumo::where('CodigoBarras', $request->input('LoteInsumo'))->first();
            $insumo->Cantidad_Articulos -= $lote->Cantidad_Articulos;
            $insumo->save();

            //inspectors
            $inspector = Inspectors::create([
                'user' => $user,
                'action' => 'Creo lote de productos, Codigo de barras: ' . $request->input('LoteInsumo'),
                'seccion' => 'Lotes | Products'
            ]);

            return response()->json([
                'message' => 'Lote added successfully',
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Model not found',
            ], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function FindApi($barcode)
    {
        try {
            $lotes = Lotes::where('CodigoBarras', $barcode)->get();

            if ($lotes->isEmpty()) {
                return response()->json([
                    'message' => 'No lots found for the given barcode',
                ], Response::HTTP_NOT_FOUND);
            }

            $sabores = Sabores::orderBy('nombre')
                ->when($this->search, function ($query) {
                    $query->where('nombre', $this->search);
                })
                ->get();

            $lotesAsociados = [];

            foreach ($sabores as $sabor) {
                $lotesAsociados[$sabor->id] = $lotes->where('sabor_id', $sabor->id)->groupBy('CodigoBarras');
            }

            $insumo = Insumo::where('idSabor', $this->Sabor)->get();
            $product = $lotes->map(function ($lote) {
                //return $lote->producto;
                return $lote->presentacion;
            })->unique();

            return response()->json([

                'Lotes Econtrados' => $lotesAsociados,

            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function ShowAll()
    {
        try {
            $sabores = Sabores::orderBy('nombre')
                ->when($this->search, function ($query) {
                    $query->where('nombre', $this->search);
                })
                ->get();

            $lotesAsociados = [];

            foreach ($sabores as $sabor) {
                $lotes = Lotes::where('sabor_id', $sabor->id)->get();

                $lotesAsociados[$sabor->id] = $lotes->groupBy('CodigoBarras');
            }

            $insumo = Insumo::where('idSabor', $this->Sabor)->get();
            $product = Product::where('sabor_id', $this->Sabor)->get();

            return response()->json([

                'lotesAsociados' => $lotesAsociados,
                'insumo' => $insumo,
                'product' => $product,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
