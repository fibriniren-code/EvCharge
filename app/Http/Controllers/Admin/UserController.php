<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $role = $request->query('role', 'all');
        $search = trim((string) $request->query('search', ''));

        $users = User::query()
            ->when(in_array($role, ['driver', 'operator', 'admin'], true), fn ($q) => $q->where('role', $role))
            ->when($search !== '', fn ($q) => $q->where(fn ($w) => $w
                ->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")))
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        return view('admin.users.index', [
            'users' => $users,
            'role' => $role,
            'search' => $search,
            'counts' => [
                'all' => User::count(),
                'driver' => User::where('role', 'driver')->count(),
                'operator' => User::where('role', 'operator')->count(),
                'admin' => User::where('role', 'admin')->count(),
            ],
        ]);
    }

    /** Admin membuat akun operator atau admin (pendaftaran publik hanya untuk driver). */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
            'phone_number' => ['nullable', 'string', 'max:20'],
            'role' => ['required', 'in:operator,admin'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        // Kolom password memakai cast "hashed", jadi diisi teks biasa
        User::create($data + ['status' => 'active']);

        return redirect()->route('admin.users.index', ['role' => $data['role']])
            ->with('success', 'Akun ' . $data['role'] . ' atas nama ' . $data['name'] . ' berhasil dibuat.');
    }

    public function toggle(Request $request, User $user)
    {
        if ($user->id === $request->user()->id) {
            return back()->withErrors(['error' => 'Anda tidak bisa menonaktifkan akun Anda sendiri.']);
        }

        $deactivating = $user->status === 'active';

        if ($deactivating && $user->role === 'admin'
            && User::where('role', 'admin')->where('status', 'active')->count() <= 1) {
            return back()->withErrors(['error' => 'Tidak bisa menonaktifkan admin aktif terakhir.']);
        }

        $user->update(['status' => $deactivating ? 'inactive' : 'active']);

        return back()->with('success', $user->name . ($deactivating ? ' dinonaktifkan.' : ' diaktifkan kembali.'));
    }
}