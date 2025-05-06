<?php

namespace App\Http\Livewire;

use App\Models\Sale;
use App\Models\User;
use App\Http\Controllers\MantenimientoSistemaController;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Spatie\Permission\Models\Role;
use Illuminate\Http\Request;
use App\Models\Customer;
//QUICKBOOKS
use QuickBooksOnline\API\Facades\Item as ItemQB;
use QuickBooksOnline\API\DataService\DataService;
use QuickBooksOnline\API\Data\IPPCustomer;
use QuickBooksOnline\API\Exception\ServiceException;
use QuickBooksOnline\API\Facades\Customer as CustomerQB;
use App\Models\quickbook_credentials;
use Illuminate\Validation\ValidationException;

class UsersController extends Component
{

    use WithPagination;
    use WithFileUploads;

    public $name, $phone, $email, $status, $image, $password, $selected_id, $fileLoaded, $profile;
    public $pageTitle, $componentName, $search;
    private $pagination = 10;

    public function paginationView()
    {
        return 'vendor.livewire.bootstrap';
    }

    public function mount()
    {
        $this->pageTitle = 'Listado';
        $this->componentName = 'Usuarios';
        $this->status = 'Elegir';
        $this->search = null;
    }


    public function render()
    {
        if (strlen($this->search) < 0){
            $data = User::where('name', 'like', '%' . $this->search . '%')
                ->select('*')->orderBy('name', 'asc')->paginate($this->pagination);
        }
        else
            $data = User::select('*')->orderBy('name', 'asc')->paginate($this->pagination);


        return view('livewire.users.component', [
            'data' => $data,
            'roles' => Role::orderBy('name', 'asc')->get()
        ])
            ->extends('layouts.theme.app')
            ->section('content');
    }

    public function resetUI()
    {
        $this->emit('producto-creado');
        $this->name = '';
        $this->email = '';
        $this->password = '';
        $this->phone = '';
        $this->image = '';
        $this->search = '';
        $this->status = 'Elegir';
        $this->selected_id = 0;
        $this->resetValidation();
        $this->resetPage();
    }


    public function edit(User $user)
    {
        
        $this->selected_id = $user->id;
        $this->name = $user->name;
        $this->phone = $user->phone;
        $this->profile = ucfirst(strtolower($user->profile));
        $this->status = ucfirst(strtolower($user->status));
        $this->email = $user->email;
        $this->password = '';

        $this->emit('show-modal', 'open!');
    }

    public function closeModal(){
        
    }

    protected $listeners = [
        'deleteRow' => 'destroy',
        'resetUI' => 'resetUI',
        'closeModal' => 'closeModal'
    ];

    


    public function Store()
    {
        try {
            $rules = [
                'name' => 'required|min:3',
                'email' => 'required|unique:users|email',
                'status' => 'required|not_in:Elegir',
                'profile' => 'required|not_in:Elegir',
                'password' => 'required|min:3'
            ];
    
            $messages = [
                'name.required' => 'Ingresa el nombre',
                'name.min' => 'El nombre del usuario debe tener al menos 3 caracteres',
                'email.required' => 'Ingresa el correo ',
                'email.email' => 'Ingresa un correo válido',
                'email.unique' => 'El email ya existe en sistema',
                'status.required' => 'Selecciona el estatus del usuario',
                'status.not_in' => 'Selecciona el estatus',
                'profile.required' => 'Selecciona el perfil/role del usuario',
                'profile.not_in' => 'Selecciona un perfil/role distinto a Elegir',
                'password.required' => 'Ingresa el password',
                'password.min' => 'El password debe tener al menos 3 caracteres'
            ];
    
            $this->validate($rules, $messages);
    
            $user = User::create([
                'name' => $this->name,
                'email' => $this->email,
                'phone' => $this->phone,
                'status' => $this->status,
                'profile' => $this->profile,
                'password' => bcrypt($this->password)
            ]);
    
            $user->syncRoles($this->profile);
    
            if ($this->image) {
                $customFileName = uniqid() . ' _.' . $this->image->extension();
                $this->image->storeAs('public/users', $customFileName);
                $user->image = $customFileName;
                $user->save();
            }
    
            $this->resetUI();
            $this->emit('user-added', 'Usuario Registrado');

        } catch (\Exception $e) {
            $errorString = '';        
            if($e instanceof ValidationException){
                $errors = $e->errors();

                // Convertir los errores a una cadena                
                foreach ($errors as $fieldErrors) {
                    foreach ($fieldErrors as $error) {
                        $errorString .= $error . ' ';
                    }
                }
    
                // Eliminar el último espacio extra
                $errorString = rtrim($errorString);
            }else{
                $errorString = $e->getMessage();
            }
            $this->emit('global-msg', $errorString);
            $this->emit('producto-creado');
        }
        
    }

    public function Update()
    {
        try {
            $rules = [
                'email' => "required|email|unique:users,email,{$this->selected_id}",
                'name' => 'required|min:3',
                'status' => 'required|not_in:Elegir',
                'profile' => 'required|not_in:Elegir'
            ];
    
            $messages = [
                'name.required' => 'Ingresa el nombre',
                'name.min' => 'El nombre del usuario debe tener al menos 3 caracteres',
                'email.required' => 'Ingresa el correo ',
                'email.email' => 'Ingresa un correo válido',
                'email.unique' => 'El email ya existe en sistema',
                'status.required' => 'Selecciona el estatus del usuario',
                'status.not_in' => 'Selecciona el estatus',
                'profile.required' => 'Selecciona el perfil/role del usuario',
                'profile.not_in' => 'Selecciona un perfil/role distinto a Elegir'
            ];
    
            $this->validate($rules, $messages);
    
            $user = User::find($this->selected_id);
            $user->update([
                'name' => $this->name,
                'email' => $this->email,
                'phone' => $this->phone,
                'status' => $this->status,
                'profile' => $this->profile,
                'password' => strlen($this->password) > 0 ? bcrypt($this->password) : $user->password
            ]);
    
            $user->syncRoles($this->profile);
    
    
            if ($this->image) {
                $customFileName = uniqid() . ' _.' . $this->image->extension();
                $this->image->storeAs('public/users', $customFileName);
                $imageTemp = $user->image;
    
                $user->image = $customFileName;
                $user->save();
    
                if ($imageTemp != null) {
                    if (file_exists('storage/users/' . $imageTemp)) {
                        unlink('storage/users/' . $imageTemp);
                    }
                }
            }
    
            $this->resetUI();
            $this->emit('user-updated', 'Usuario Actualizado');
        } catch (\Exception $e) {
            $errorString = '';        
            if($e instanceof ValidationException){
                $errors = $e->errors();

                // Convertir los errores a una cadena                
                foreach ($errors as $fieldErrors) {
                    foreach ($fieldErrors as $error) {
                        $errorString .= $error . ' ';
                    }
                }
    
                // Eliminar el último espacio extra
                $errorString = rtrim($errorString);
            }else{
                $errorString = $e->getMessage();
            }
            $this->emit('global-msg', $errorString);
            $this->emit('producto-creado');
        }
        
    }


    public function destroy(User $user)
    {
        // Verificar si el usuario tiene ventas de manera eficiente
        $hasSales = Sale::where('user_id', $user->id)->exists();

        if ($hasSales) {
            $this->emit('user-withsales', 'No es posible eliminar el usuario porque tiene ventas registradas');
        } else {
            try {
                // Eliminar el usuario si no tiene ventas
                $user->delete();

                $this->resetUI();
                $this->emit('user-deleted', 'Usuario Eliminado');
            } catch (\Exception $th) {
                // Manejar posibles errores durante la eliminación
                $this->emit('user-error', 'Ocurrió un error al intentar eliminar el usuario: ' . $th->getMessage());
            }
        }        
    }
    public function LoginUserAdmin(Request $request)
    {
        try {
            $correo = $request->input('email');
            $pass = $request->input('password');

            // Buscar al Admin por su correo
            $admin = User::where('email', $correo)->first();

            if (!$admin) {
                return response()->json(['success' => false, 'message' => 'No existe el Usuario.'], 404);
            }

            // Verificar la contraseña
            if (password_verify($pass, $admin->password)) {
                // La contraseña es correcta, puedes iniciar sesión

                // Generar un token simple concatenando nombre, correo y teléfono
                $sessionToken = sha1($admin->name . $admin->email . $admin->phone);
                $mantenimiento = MantenimientoSistemaController::existeMantenimiento($admin->id);
                // Retornar la respuesta con el formato deseado
                $responseData = [
                    'success' => true,
                    'message' => 'El admin fue autenticado',
                    'data' => [
                        'id' => $admin->id,
                        'name' => $admin->name,
                        'email' => $admin->email,
                        'phone' => $admin->phone,
                        'session_token' => 'JWT ' . $sessionToken,
                    ],
                    'mantenimiento' => $mantenimiento
                ];

                return response()->json($responseData, 200);
            } else {
                return response()->json(['success' => false, 'message' => 'Usuario O Contraseña Incorrecta.'], 401);
            }
        } catch (\Exception $e) {
            \Log::error($e);
            return response()->json([
                'success' => false,
                'message' => 'Hubo un error en el servidor ' . $e->getMessage(),
            ], 500);
        }
    }
    public function updateNotificationToken(Request $request)
    {
        try {
            // Recoger los datos de la solicitud
            $data = $request->only(['id', 'token']);

            // Buscar al usuario por ID
            $user = Customer::find($data['id']);

            // Verificar si el usuario existe
            if (!$user) {
                return response()->json(['error' => 'Usuario no encontrado'], 404);
            }

            // Actualizar el token de notificación
            $user->notification_token = $data['token'];
            $user->save();
            // Retornar solo la información necesaria del usuario actualizada
            $responseData = [
                'success' => true,
                'message' => 'Token actualizado exitosamente',
                'user' => [
                    'name' => $user->name,
                    'email' => $user->email,

                    'phone' => $user->phone,
                    'token' => $user->notification_token
                ],
            ];

            // Responder con la respuesta exitosa
            return response()->json($responseData, 200);
        } catch (\Exception $e) {
            \Log::error($e);
            return response()->json([
                'error' => 'Hubo un error en el servidor',
                'message' => $e->getMessage(),
                'exception' => get_class($e),
            ], 500);
        }
    }


    public function createUser(Request $request)
    {
        try {
            // Recoger los datos de la solicitud
            $data = $request->only([
                'name',
                'email',
                'password',
                'profile',
                'phone',
            ]);

            // Verificar si el correo ya está registrado
            $existingUser = User::where('email', $data['email'])->first();

            if ($existingUser) {
                return response()->json(['error' => 'El correo ya está registrado'], 400);
            }

            // Verificar si el perfil es válido
            $validProfiles = ['ADMIN', 'EMPLOYEE', 'ACCOUNTANT'];
            if (!in_array($data['profile'], $validProfiles)) {
                return response()->json(['error' => 'Perfil no válido'], 400);
            }

            // Hash de la contraseña
            $data['password'] = bcrypt($data['password']);

            // Crear el nuevo usuario
            $newUser = User::create($data);

            // Retornar solo la información necesaria del usuario
            $responseData = [
                'message' => 'Usuario creado exitosamente',
                'user' => [
                    'name' => $newUser->name,
                    'email' => $newUser->email,
                    'profile' => $newUser->profile,
                    'phone' => $newUser->phone,
                ],
            ];

            return response()->json($responseData, 201);
        } catch (\Exception $e) {
            \Log::error($e);
            return response()->json([
                'error' => 'Hubo un error en el servidor',
                'message' => $e->getMessage(),
                'exception' => get_class($e),
            ], 500);
        }
    }


    public function LoginUserClient(Request $request)
    {
        try {
            $correo = $request->input('email');
            $pass = $request->input('password');

            // Buscar al Cliente por su correo
            $cliente = Customer::where('email', $correo)->first();

            if (!$cliente) {
                return response()->json(['success' => false, 'message' => 'No existe el Usuario.'], 404);
            }

            // Verificar la contraseña
            if (password_verify($pass, $cliente->password)) {
                // La contraseña es correcta, puedes iniciar sesión

                // Generar un token simple concatenando nombre, correo y teléfono
                $sessionToken = sha1($cliente->name . $cliente->email . $cliente->phone);
                $mantenimiento = MantenimientoSistemaController::existeMantenimiento($cliente->id);
                // Retornar la respuesta con el formato deseado
                $responseData = [
                    'success' => true,
                    'message' => 'El usuario fue autenticado',
                    'data' => [
                        'id' => $cliente->id,
                        'name' => $cliente->name,
                        'last_name' => $cliente->last_name,
                        'last_name2' => $cliente->last_name2,
                        'email' => $cliente->email,
                        'address' => $cliente->address,
                        'phone' => $cliente->phone,
                        'session_token' => 'JWT ' . $sessionToken,
                    ],
                    'mantenimiento' => $mantenimiento
                ];

                return response()->json($responseData, 200);
            } else {
                return response()->json(['success' => false, 'message' => 'Usuario o Contraseña Incorrecta'], 401);
            }
        } catch (\Exception $e) {
            \Log::error($e);
            return response()->json([
                'success' => false,
                'message' => 'Hubo un error en el servidor ' . $e->getMessage(),
            ], 500);
        }
    }


    public function createCustomer(Request $request)
    {
        try {
            // Recoger los datos de la solicitud
            $data = $request->only([
                'name',
                'last_name',
                'last_name2',
                'email',
                'phone',
                'address',
                'password',
            ]);

            // Verificar si el correo ya está registrado
            $existingCustomer = Customer::where('email', $data['email'])->first();

            if ($existingCustomer) {
                return response()->json(['error' => 'El correo ya está registrado'], 400);
            }

            // Hash de la contraseña
            $data['password'] = bcrypt($data['password']);

            // Crear el nuevo cliente
            $newCustomer = Customer::create($data);

            $responseData = [
                'message' => 'Cliente creado exitosamente',
                'client' => [
                    'name' => $newCustomer->name,
                    'email' => $newCustomer->email,
                ],
            ];

            return response()->json($responseData, 201);
        } catch (\Exception $e) {
            \Log::error($e);
            return response()->json([
                'error' => 'Hubo un error en el servidor',
                'message' => $e->getMessage(),
                'exception' => get_class($e),
            ], 500);
        }
    }
    public function update_access_token()
    {
        try {
            $config = config('quickbooks');
            $quickbook_credentials = quickbook_credentials::where('status', 1)->first();

            if ($quickbook_credentials->count() > 0) {
                $access_token = $quickbook_credentials->access_token;
                $refresh_access_token = $quickbook_credentials->refresh_access_token;
            } else {
                $access_token = $config['access_token'];
                $refresh_access_token = $config['refresh_access_token'];
            }

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

            $OAuth2LoginHelper = $dataService->getOAuth2LoginHelper();
            $accessTokenObj = $OAuth2LoginHelper->refreshAccessTokenWithRefreshToken($config['refresh_token']);
            $accessTokenValue = $accessTokenObj->getAccessToken();
            $refreshTokenValue = $accessTokenObj->getRefreshToken();

            $dataArr['client_id'] = $config['client_id'];
            $dataArr['client_secret'] = $config['client_secret'];
            $dataArr['realm_id'] = $config['realm_id'];
            $dataArr['redirect_uri'] = $config['redirect_uri'];
            $dataArr['base_url'] = $config['base_url'];
            $dataArr['status'] = 1;
            $dataArr['access_token'] = $accessTokenValue;
            $dataArr['refresh_token'] = $refreshTokenValue;

            $quickbook_credentials->where('id', 1)->update($dataArr);
            return response()->json(['message' => 'Token actualizado correctamente'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error al actualizar el token'], 500);
        }
    }

}
