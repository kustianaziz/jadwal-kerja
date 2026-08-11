@extends('layouts.main')

@section('content')
    <div class="mb-8">
        <h2 class="text-3xl font-display font-bold text-dark-navy">Edit Grup Proyek</h2>
        <p class="text-steel-gray mt-1">Ubah data grup/divisi proyek</p>
    </div>

    <div class="max-w-xl">
        <form action="{{ route('groups.update', $group->id) }}" method="POST" class="skeuo-card p-6 md:p-8 space-y-6">
            @csrf
            @method('PUT')
            
            <div>
                <label for="nama_grup" class="block text-sm font-medium text-dark-navy mb-2">Nama Grup</label>
                <input type="text" id="nama_grup" name="nama_grup" value="{{ old('nama_grup', $group->nama_grup) }}" required class="skeuo-input py-2">
            </div>
            
            <div>
                <label for="deskripsi" class="block text-sm font-medium text-dark-navy mb-2">Deskripsi</label>
                <textarea id="deskripsi" name="deskripsi" rows="4" class="skeuo-input py-2">{{ old('deskripsi', $group->deskripsi) }}</textarea>
            </div>
            
            <div class="flex justify-end space-x-4 pt-4 border-t border-steel-gray">
                <a href="{{ route('groups.index') }}" class="skeuo-btn-secondary">
                    Batal
                </a>
                <button type="submit" class="skeuo-btn">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
@endsection
