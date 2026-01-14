<?php

namespace App\Livewire\Admin\UserManagement;

use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Masmerise\Toaster\Toaster;

class Index extends Component
{

    use WithPagination, AuthorizesRequests;
    
    #[Title("User Management")]

    public int $paginationIndex = 10;
    public string $currentState = '';

    public $modals = [
        'detail' => false,
        'create' => false,
        'edit' => false,
        'delete' => false
    ];

    protected $rules = [
        'create' => [
            'nim' => 'required|string|regex:/^[0-9]{9}$/',
            'name' => 'required|string',
            'password' => 'required|string',
            'id_class' => 'required|numeric|exists:classes,id',
            'role' => ['required', new Enum(UserRole::class)],
            'status' => ['required', new Enum(UserStatus::class)],
            'can_vote' => 'required|boolean',
        ],
        'delete' => [
            'id' => 'required|numeric'
        ],
        'update' => [
            'nim' => 'nullable|string|regex:/^[0-9]{9}$/',
            'name' => 'nullable|string',
            'password' => 'nullable|string',
            'id_class' => 'nullable|numeric|exists:classes,id',
            'role' => ['nullable', new Enum(UserRole::class)],
            'status' => ['nullable', new Enum(UserStatus::class)],
            'can_vote' => 'nullable|boolean',
        ],
    ];

    public $modalUser;

    protected string $paginationTheme = 'tailwind';

    public function openModal(string $type, $id = null){

        switch($type){
            case 'detail' : 
            case 'edit' : 
            case 'delete' :
                $this->modalUser = User::with('classes')->find($id);
            break;
        }

        $this->currentState = $type;
        $this->modals[$type] = true;

    }

    public function closeModal($type){

        $this->modals[$type] = false;
        $this->currentState = '';
        
        if ($type === 'create') {
           return;
        }

        switch($type){
            case 'detail' : 
            case 'edit' : 
            case 'delete' :
                $this->modalUser = null;
            break;
        }

    }

    public function create(){ 

        try{

            $this->authorize('create');
            
            $result = $this->validate($this->rules[$this->currentState]);
                
            $result['password'] = Hash::make($result['password']);

            $user = User::create($result);

            Toaster::success("User telah berhasil dibuat : {$user->name} 👏🏼👏🏼👏🏼");

            $this->closeModal($this->currentState);
            
        }catch(ValidationException $e){
            Toaster::error("Validasi gagal : {$e->validator->errors()->first()}");
        }catch(QueryException $e){
            Toaster::error("Internal Server Error : {$e->getMessage()}");
        }
        
    }

    public function delete(){

        try{

            $this->authorize('delete', $this->modalUser);

            $this->validate($this->rules[$this->currentState]);
            
            DB::transaction(function(){

                $user = User::whereKey($this->modalUser->id)
                        ->lockForUpdate()
                        ->firstOrFail();
    
                $user->delete();
    
            });

            Toaster::success("User telah berhasil dihapus: {$this->modalUser->name}");
                
            $this->closeModal($this->currentState);

        } catch (ModelNotFoundException $e) {
            Toaster::error("Tak dapat menemukan User: {$e->getMessage()}");
        } catch (QueryException $e) {
            Toaster::error("Internal Server Error: {$e->getMessage()}");
        } catch (ValidationException $e) {
            Toaster::error("Validasi gagal: " . $e->validator->errors()->first());
        } catch (AuthorizationException $e){
             Toaster::error('Tidak memiliki izin untuk melakukan aksi ini');
        }
    }

    public function update() {
        try {

            $this->authorize('update', $this->modalUser);

            $result = $this->validate($this->rules[$this->currentState]);

            DB::transaction( function () use ($result){
                
                if (!empty($result['password'])) {
                    $result['password'] = Hash::make($result['password']);
                } else {
                    unset($result['password']); 
                }

                $user = User::whereKey($this->modalUser->id)
                        ->lockForUpdate()
                        ->firstOrFail();
    
    
                $user->update($result);
    
            });
                
            Toaster::success("User {$this->modalUser->name} berhasil diperbarui");

            $this->closeModal($this->currentState);

        } catch (ModelNotFoundException $e) {
            Toaster::error("Tak dapat menemukan User: {$e->getMessage()}");
        } catch (ValidationException $e) {
            Toaster::error("Validasi gagal: " . $e->validator->errors()->first());
        } catch (QueryException $e) {
            Toaster::error("Internal Server Error: " . $e->getMessage());
        }catch (AuthorizationException $e){
             Toaster::error('Tidak memiliki izin untuk melakukan aksi ini');
        }
    }


    public function updatePaginationIndex() {
        $this->resetPage();        
    }

    public function render(){
        return view('livewire.admin.user-management.index', [
            'users' => User::select('id', 'nim', 'name', 'id_class', 'status')
                       ->with('classes:id,name')
                       ->orderByDesc('created_at')
                       ->paginate($this->paginationIndex)
        ]);
    }
}
