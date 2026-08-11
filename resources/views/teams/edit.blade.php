@extends('layouts.main')

@section('content')
    <div class="mb-8">
        <h2 class="text-3xl font-display font-bold text-dark-navy">Edit Anggota Tim</h2>
        <p class="text-steel-gray mt-1">Ubah data pengguna aplikasi</p>
    </div>

    <div class="max-w-xl">
        <form action="{{ route('teams.update', $team->id) }}" method="POST" class="skeuo-card p-6 md:p-8 space-y-6">
            @csrf
            @method('PUT')
            
            <div>
                <label for="name" class="block text-sm font-medium text-dark-navy mb-2">Nama Lengkap</label>
                <input type="text" id="name" name="name" value="{{ old('name', $team->name) }}" required class="skeuo-input py-2">
            </div>
            
            <div>
                <label for="email" class="block text-sm font-medium text-dark-navy mb-2">Email</label>
                <input type="email" id="email" name="email" value="{{ old('email', $team->email) }}" required class="skeuo-input py-2">
            </div>
            
            <div>
                <label for="role" class="block text-sm font-medium text-dark-navy mb-2">Role</label>
                <select id="role" name="role" required class="skeuo-select py-2">
                    <option value="pic" {{ $team->role == 'pic' ? 'selected' : '' }}>PIC / Staf</option>
                    <option value="pm" {{ $team->role == 'pm' ? 'selected' : '' }}>Project Manager</option>
                    <option value="admin" {{ $team->role == 'admin' ? 'selected' : '' }}>Administrator</option>
                    <option value="stakeholder" {{ $team->role == 'stakeholder' ? 'selected' : '' }}>Stakeholder</option>
                </select>
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-dark-navy mb-2">Password Baru (Biarkan kosong jika tidak ingin mengubah)</label>
                <input type="password" id="password" name="password" class="skeuo-input py-2">
            </div>
            
            <div class="flex justify-end space-x-4 pt-4 border-t border-steel-gray">
                <a href="{{ route('teams.index') }}" class="skeuo-btn-secondary">
                    Batal
                </a>
                <button type="submit" class="skeuo-btn">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
@endsection
