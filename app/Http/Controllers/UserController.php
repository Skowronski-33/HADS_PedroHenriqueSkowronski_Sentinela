<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with('roles')->orderBy('name')->paginate(15);

        return view('usuarios.index', compact('users'));
    }

    public function create()
    {
        $roles = Role::orderBy('name')->pluck('name');

        return view('usuarios.create', compact('roles'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'perfil' => ['required', 'string', Rule::exists('roles', 'name')],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'ativo' => true,
        ]);

        $user->assignRole($data['perfil']);

        return redirect()->route('usuarios.index')->with('success', 'Usuário cadastrado com sucesso.');
    }

    public function edit(User $user)
    {
        $roles = Role::orderBy('name')->pluck('name');
        $perfilAtual = $user->roles->first()?->name;

        return view('usuarios.edit', compact('user', 'roles', 'perfilAtual'));
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'perfil' => ['required', 'string', Rule::exists('roles', 'name')],
        ]);

        $user->name = $data['name'];
        $user->email = $data['email'];

        if (! empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }

        $user->save();
        $user->syncRoles([$data['perfil']]);

        return redirect()->route('usuarios.index')->with('success', 'Usuário atualizado com sucesso.');
    }

    public function toggleAtivo(Request $request, User $user)
    {
        if ($user->id === $request->user()->id) {
            return redirect()->route('usuarios.index')->with('error', 'Você não pode inativar o próprio usuário.');
        }

        $user->ativo = ! $user->ativo;
        $user->save();

        $mensagem = $user->ativo ? 'Usuário reativado com sucesso.' : 'Usuário inativado com sucesso.';

        return redirect()->route('usuarios.index')->with('success', $mensagem);
    }
}
