@extends('layouts.main')

@section('content')
    <div class="mb-4 flex justify-between items-start">
        <a href="{{ route('projects.show', $phase->project_id) }}" class="text-cyan-glow hover:text-tech-blue font-medium flex items-center transition-colors">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Kembali ke Proyek
        </a>
    </div>

    <div class="mb-8">
        <h2 class="text-3xl font-display font-bold text-dark-navy">Edit Fase</h2>
        <p class="text-steel-gray mt-1">Ubah fase untuk proyek: {{ $phase->project->nama_proyek }}</p>
    </div>

    <div class="max-w-2xl">
        <form action="{{ route('phases.update', $phase->id) }}" method="POST" class="skeuo-card p-6 md:p-8 space-y-6">
            @csrf
            @method('PUT')
            
            <!-- Phase Name & PIC -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="nama_fase" class="block text-sm font-medium text-dark-navy mb-2">Nama Fase</label>
                    <input type="text" id="nama_fase" name="nama_fase" value="{{ old('nama_fase', $phase->nama_fase) }}" required class="skeuo-input text-lg py-3">
                </div>
                <div>
                    <label for="pic_id" class="block text-sm font-medium text-dark-navy mb-2">Penanggung Jawab (PIC)</label>
                    <select id="pic_id" name="pic_id" class="skeuo-select py-3">
                        <option value="">-- Pilih PIC (Opsional) --</option>
                        @foreach($pics ?? [] as $pic)
                            <option value="{{ $pic->id }}" {{ $phase->pic_id == $pic->id ? 'selected' : '' }}>{{ $pic->nama }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="bobot_pct" class="block text-sm font-medium text-dark-navy mb-2">Bobot Fase (%)</label>
                    <div class="relative">
                        <input type="number" id="bobot_pct" name="bobot_pct" value="{{ old('bobot_pct', $phase->bobot_pct) }}" min="0" max="100" step="0.01" required class="skeuo-input py-2 pr-8">
                        <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-steel-gray">
                            %
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="tanggal_mulai" class="block text-sm font-medium text-dark-navy mb-2">Tanggal Mulai</label>
                    <input type="date" id="tanggal_mulai" name="tanggal_mulai" value="{{ old('tanggal_mulai', $phase->tanggal_mulai ? \Carbon\Carbon::parse($phase->tanggal_mulai)->format('Y-m-d') : '') }}" class="skeuo-input py-2">
                </div>
                <div>
                    <label for="tanggal_target" class="block text-sm font-medium text-dark-navy mb-2">Tanggal Target Selesai</label>
                    <input type="date" id="tanggal_target" name="tanggal_target" value="{{ old('tanggal_target', $phase->tanggal_target ? \Carbon\Carbon::parse($phase->tanggal_target)->format('Y-m-d') : '') }}" class="skeuo-input py-2">
                </div>
            </div>

            <div>
                <label for="deskripsi" class="block text-sm font-medium text-dark-navy mb-2">Deskripsi</label>
                <textarea id="deskripsi" name="deskripsi" rows="3" class="skeuo-input py-2">{{ old('deskripsi', $phase->deskripsi) }}</textarea>
            </div>
            
            <div class="flex justify-end space-x-4 pt-4 border-t border-steel-gray">
                <a href="{{ route('projects.show', $phase->project_id) }}" class="skeuo-btn-secondary">
                    Batal
                </a>
                <button type="submit" class="skeuo-btn">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
@endsection
