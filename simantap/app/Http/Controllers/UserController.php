<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;
use Yajra\DataTables\Facades\DataTables;

class UserController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            return DataTables::eloquent(User::with('roles')->withTrashed())
                ->addIndexColumn()
                ->addColumn('role_badge', function ($u) {
                    return $u->roles->map(fn ($r) => '<span class="badge bg-primary me-1">' . $r->name . '</span>')->implode('');
                })
                ->addColumn('status_badge', fn ($u) => $u->deleted_at
                    ? '<span class="badge bg-danger">Nonaktif</span>'
                    : ($u->is_active ? '<span class="badge bg-success">Aktif</span>' : '<span class="badge bg-warning">Ditangguhkan</span>'))
                ->addColumn('action', fn ($u) => $this->actionButtons($u))
                ->rawColumns(['role_badge', 'status_badge', 'action'])
                ->make(true);
        }

        return view('users.index');
    }

    public function create(): View
    {
        return view('users.form', ['user' => null, 'roles' => Role::orderBy('name')->get()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'     => 'required|string|max:100',
            'email'    => 'required|email|unique:users,email',
            'nip'      => 'nullable|string|max:30',
            'jabatan'  => 'nullable|string|max:100',
            'telepon'  => 'nullable|string|max:20',
            'password' => 'required|string|min:8|confirmed',
            'role'     => 'required|exists:roles,name',
            'is_active'=> 'boolean',
        ]);

        $user = User::create([
            'name'      => $data['name'],
            'email'     => $data['email'],
            'nip'       => $data['nip'] ?? null,
            'jabatan'   => $data['jabatan'] ?? null,
            'telepon'   => $data['telepon'] ?? null,
            'password'  => Hash::make($data['password']),
            'is_active' => $request->boolean('is_active', true),
        ]);

        $user->assignRole($data['role']);

        return redirect()->route('users.index')->with('success', 'Akun pengguna berhasil dibuat.');
    }

    public function show(User $user): View
    {
        $user->load('roles');
        return view('users.show', compact('user'));
    }

    public function edit(User $user): View
    {
        $user->load('roles');
        return view('users.form', ['user' => $user, 'roles' => Role::orderBy('name')->get()]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'name'     => 'required|string|max:100',
            'email'    => 'required|email|unique:users,email,' . $user->id,
            'nip'      => 'nullable|string|max:30',
            'jabatan'  => 'nullable|string|max:100',
            'telepon'  => 'nullable|string|max:20',
            'password' => 'nullable|string|min:8|confirmed',
            'role'     => 'required|exists:roles,name',
            'is_active'=> 'boolean',
        ]);

        $user->update([
            'name'      => $data['name'],
            'email'     => $data['email'],
            'nip'       => $data['nip'] ?? null,
            'jabatan'   => $data['jabatan'] ?? null,
            'telepon'   => $data['telepon'] ?? null,
            'is_active' => $request->boolean('is_active', true),
        ]);

        if (! empty($data['password'])) {
            $user->update(['password' => Hash::make($data['password'])]);
        }

        $user->syncRoles([$data['role']]);

        return redirect()->route('users.index')->with('success', 'Akun pengguna berhasil diperbarui.');
    }

    public function destroy(User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Tidak dapat menghapus akun sendiri.');
        }
        $user->delete();
        return redirect()->route('users.index')->with('success', 'Akun pengguna berhasil dinonaktifkan.');
    }

    public function restore(int $id): RedirectResponse
    {
        User::withTrashed()->findOrFail($id)->restore();
        return redirect()->route('users.index')->with('success', 'Akun pengguna berhasil dipulihkan.');
    }

    private function actionButtons(User $u): string
    {
        if ($u->deleted_at) {
            return '<form method="POST" action="' . route('users.restore', $u->id) . '" class="d-inline">'
                . csrf_field()
                . '<button type="submit" class="btn btn-sm btn-outline-success"><i class="bi bi-arrow-counterclockwise"></i></button></form>';
        }

        $del = $u->id !== auth()->id()
            ? '<form id="del-u-' . $u->id . '" method="POST" action="' . route('users.destroy', $u) . '" class="d-inline">'
              . csrf_field() . method_field('DELETE')
              . '<button type="button" class="btn btn-sm btn-outline-danger" onclick="konfirmasiHapus(\'del-u-' . $u->id . '\', \'' . addslashes($u->name) . '\')">'
              . '<i class="bi bi-trash"></i></button></form>'
            : '';

        return '<a href="' . route('users.edit', $u) . '" class="btn btn-sm btn-outline-primary me-1"><i class="bi bi-pencil"></i></a>'
            . $del;
    }
}
