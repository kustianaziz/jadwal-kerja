@extends('layouts.main')

@section('content')
    <div class="mb-4 flex justify-between items-start">
        <a href="{{ url()->previous() }}" class="text-cyan-glow hover:text-tech-blue font-medium flex items-center transition-colors">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Batal
        </a>
    </div>

    <div class="mb-8">
        <h2 class="text-3xl font-display font-bold text-dark-navy">Tambah Fase Baru</h2>
        <p class="text-steel-gray mt-1">Proyek: <span class="font-bold text-dark-navy">{{ $project->nama_proyek ?? 'Nama Proyek' }}</span></p>
    </div>

    <div class="max-w-2xl">
        <form action="{{ route('phases.store', $project->id) }}" method="POST" class="skeuo-card p-6 md:p-8 space-y-6">
            @csrf
            <input type="hidden" name="project_id" value="{{ isset($project) ? $project->id : '' }}">
            
            <!-- Phase Name & PIC -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="nama_fase" class="block text-sm font-medium text-dark-navy mb-2">Nama Fase</label>
                    <input type="text" id="nama_fase" name="nama_fase" required class="skeuo-input text-lg py-3">
                </div>
                <div>
                    <label for="pic_id" class="block text-sm font-medium text-dark-navy mb-2">Penanggung Jawab (PIC)</label>
                    <select id="pic_id" name="pic_id" class="skeuo-select py-3">
                        <option value="">-- Pilih PIC (Opsional) --</option>
                        @foreach($pics ?? [] as $pic)
                            <option value="{{ $pic->id }}">{{ $pic->nama }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Bobot -->
            <div>
                <div class="flex justify-between items-end mb-2">
                    <label for="bobot_pct" class="block text-sm font-medium text-dark-navy">Bobot Fase (%)</label>
                    <span class="text-xs font-bold text-gauge-amber bg-gauge-amber/10 px-2 py-1 rounded border border-gauge-amber/20">
                        Sisa Bobot Tersedia: {{ $sisaBobot ?? 100 }}%
                    </span>
                </div>
                <div class="flex items-center">
                    <input type="number" id="bobot_pct" name="bobot_pct" min="0.01" max="{{ $sisaBobot ?? 100 }}" step="0.01" required class="skeuo-input w-24 text-center font-bold text-lg">
                    <span class="ml-3 text-steel-gray">% dari total proyek</span>
                </div>
            </div>

            <!-- Dates -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="tanggal_mulai" class="block text-sm font-medium text-dark-navy mb-2">Tanggal Mulai</label>
                    <input type="date" id="tanggal_mulai" name="tanggal_mulai" class="skeuo-input">
                </div>
                <div>
                    <label for="tanggal_target" class="block text-sm font-medium text-dark-navy mb-2">Tanggal Target Selesai</label>
                    <input type="date" id="tanggal_target" name="tanggal_target" required class="skeuo-input">
                </div>
            </div>

            <!-- Description -->
            <div>
                <label for="deskripsi" class="block text-sm font-medium text-dark-navy mb-2">Deskripsi (Opsional)</label>
                <textarea id="deskripsi" name="deskripsi" rows="3" class="skeuo-input"></textarea>
            </div>

            <!-- Actions -->
            <div class="pt-4 mt-6 border-t border-steel-gray flex justify-end">
                <button type="submit" class="skeuo-btn px-8">
                    Simpan Fase
                </button>
            </div>
        </form>
    </div>
@endsection
