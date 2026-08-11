@extends('layouts.main')

@section('content')
    <div class="mb-8">
        <h2 class="text-3xl font-display font-bold text-dark-navy">Grup Proyek</h2>
        <p class="text-steel-gray mt-1">Kelola master data grup/divisi proyek</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- List Panel -->
        <div class="md:col-span-2 space-y-6">
            <div class="skeuo-card overflow-hidden">
                <div class="bg-tech-blue p-4 border-b border-steel-gray relative">
                    <h3 class="text-lg font-display font-bold text-ice-blue z-10">Daftar Grup</h3>
                </div>
                <div class="p-0">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-ice-blue/50 border-b border-steel-gray">
                                <th class="p-4 text-xs font-bold text-dark-navy uppercase tracking-wider">Nama Grup</th>
                                <th class="p-4 text-xs font-bold text-dark-navy uppercase tracking-wider">Deskripsi</th>
                                <th class="p-4 text-xs font-bold text-dark-navy uppercase tracking-wider text-center">Jml Proyek</th>
                                <th class="p-4 text-xs font-bold text-dark-navy uppercase tracking-wider text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-steel-gray">
                            @forelse($groups as $group)
                                <tr class="hover:bg-ice-blue/30 transition-colors">
                                    <td class="p-4 font-medium text-dark-navy">{{ $group->nama_grup }}</td>
                                    <td class="p-4 text-sm text-dark-navy">{{ Str::limit($group->deskripsi, 50) }}</td>
                                    <td class="p-4 text-center">
                                        <span class="inline-block px-2 py-1 bg-ice-blue border border-steel-gray rounded-full text-xs font-bold shadow-inner">
                                            {{ $group->projects_count }}
                                        </span>
                                    </td>
                                    <td class="p-4 text-right">
                                        <a href="{{ route('groups.edit', $group->id) }}" class="skeuo-btn-secondary py-1 px-2 text-xs inline-block">Edit</a>
                                        <form action="{{ route('groups.destroy', $group->id) }}" method="POST" class="inline-block" onsubmit="event.preventDefault(); showConfirmModal('Apakah Anda yakin ingin menghapus grup ini?', this);">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="py-1 px-2 text-xs bg-gauge-red text-white border border-[#8a2f20] shadow-[inset_0_1px_0_rgba(255,255,255,0.2),_0_2px_4px_rgba(0,0,0,0.2)] rounded-sm font-display uppercase tracking-wider active:shadow-[inset_0_2px_4px_rgba(0,0,0,0.3)] active:translate-y-px transition-all">Hapus</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="p-8 text-center text-steel-gray">Belum ada data grup.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Form Panel -->
        <div class="md:col-span-1">
            <form action="{{ route('groups.store') }}" method="POST" class="skeuo-card p-6 sticky top-8">
                @csrf
                <h4 class="text-lg font-display font-bold text-dark-navy mb-4 border-b border-steel-gray pb-2">Tambah Grup Baru</h4>
                
                <div class="space-y-4">
                    <div>
                        <label for="nama_grup" class="block text-sm font-medium text-dark-navy mb-1">Nama Grup</label>
                        <input type="text" id="nama_grup" name="nama_grup" required class="skeuo-input text-sm">
                    </div>
                    
                    <div>
                        <label for="deskripsi" class="block text-sm font-medium text-dark-navy mb-1">Deskripsi</label>
                        <textarea id="deskripsi" name="deskripsi" rows="3" class="skeuo-input text-sm"></textarea>
                    </div>
                    
                    <button type="submit" class="skeuo-btn w-full text-sm">
                        Simpan Grup
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
