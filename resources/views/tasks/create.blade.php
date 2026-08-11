@extends('layouts.main')

@section('content')
    <div class="mb-4 flex justify-between items-start">
        <a href="{{ route('projects.show', $phase->project_id ?? 1) }}" class="text-cyan-glow hover:text-tech-blue font-medium flex items-center transition-colors">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Batal
        </a>
    </div>

    <div class="mb-8">
        <h2 class="text-3xl font-display font-bold text-dark-navy">Tambah Task Baru</h2>
        <p class="text-steel-gray mt-1">Fase: <span class="font-bold text-dark-navy">{{ $phase->nama_fase ?? 'Nama Fase' }}</span></p>
    </div>

    <div class="max-w-3xl">
        <form action="{{ route('tasks.store', $phase->id) }}" method="POST" enctype="multipart/form-data" class="skeuo-card p-6 md:p-8 space-y-6">
            @csrf
            <input type="hidden" name="phase_id" value="{{ isset($phase) ? $phase->id : '' }}">
            
            @if ($errors->any())
                <div class="mb-4 skeuo-card bg-gauge-red/10 border-gauge-red p-3">
                    <ul class="list-disc list-inside text-sm text-gauge-red">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Task Name -->
                <div class="md:col-span-2">
                    <label for="nama_task" class="block text-sm font-medium text-dark-navy mb-2">Nama Task</label>
                    <input type="text" id="nama_task" name="nama_task" required class="skeuo-input text-lg py-2">
                </div>

                <!-- Bobot -->
                <div>
                    <div class="flex justify-between items-end mb-2">
                        <label for="bobot_pct" class="block text-sm font-medium text-dark-navy">Bobot Task (%)</label>
                    </div>
                    <div class="flex items-center">
                        <input type="number" id="bobot_pct" name="bobot_pct" min="0.01" max="{{ $sisaBobot ?? 100 }}" step="0.01" required class="skeuo-input w-24 text-center font-bold">
                        <span class="ml-3 text-sm text-steel-gray">Sisa tersedia: <strong class="text-gauge-amber">{{ $sisaBobot ?? 100 }}%</strong></span>
                    </div>
                    
                    <!-- Small indicator bar -->
                    <div class="mt-2 flex h-2 w-full bg-ice-blue rounded-full border border-steel-gray overflow-hidden shadow-inner">
                        <div class="bg-cyan-glow h-full" style="width: {{ 100 - ($sisaBobot ?? 100) }}%"></div>
                        <div class="bg-gauge-amber/50 h-full w-4"></div>
                    </div>
                </div>

                <!-- Prioritas -->
                <div>
                    <label for="prioritas" class="block text-sm font-medium text-dark-navy mb-2">Prioritas</label>
                    <select id="prioritas" name="prioritas" class="skeuo-select">
                        <option value="medium">Normal</option>
                        <option value="high">Tinggi</option>
                        <option value="low">Rendah</option>
                    </select>
                </div>

                <!-- Dates -->
                <div>
                    <label for="tanggal_mulai" class="block text-sm font-medium text-dark-navy mb-2">Tanggal Mulai</label>
                    <input type="date" id="tanggal_mulai" name="tanggal_mulai" class="skeuo-input">
                </div>
                <div>
                    <label for="deadline" class="block text-sm font-medium text-dark-navy mb-2">Deadline</label>
                    <input type="date" id="deadline" name="deadline" required class="skeuo-input">
                </div>

                <!-- Assignees -->
                <div>
                    <label for="pic_utama" class="block text-sm font-medium text-dark-navy mb-2">PIC Utama</label>
                    <select id="pic_utama" name="pic_utama" required class="skeuo-select">
                        <option value="">Pilih PIC</option>
                        @foreach($pics ?? [] as $pic)
                            <option value="{{ $pic->id }}">{{ $pic->nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-dark-navy mb-2">Kontributor (Opsional)</label>
                    <div class="skeuo-input max-h-32 overflow-y-auto space-y-2 py-2">
                        @foreach($pics ?? [] as $pic)
                            <label class="flex items-center px-2 py-1 hover:bg-ice-blue/50 rounded cursor-pointer transition-colors">
                                <input type="checkbox" name="kontributor[]" value="{{ $pic->id }}" class="text-cyan-glow border-steel-gray rounded shadow-inner focus:ring-cyan-glow bg-ice-blue mr-3">
                                <span class="text-sm text-dark-navy">{{ $pic->nama }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <!-- Description & Files -->
                <div class="md:col-span-2">
                    <label for="deskripsi" class="block text-sm font-medium text-dark-navy mb-2">Detail Instruksi</label>
                    <textarea id="deskripsi" name="deskripsi" rows="4" class="skeuo-input"></textarea>
                </div>
                
                <div class="md:col-span-2">
                    <label for="lampiran" class="block text-sm font-medium text-dark-navy mb-2">Lampiran (Opsional)</label>
                    <div class="skeuo-input p-1 flex items-center bg-ice-blue/30">
                        <input type="file" id="lampiran" name="lampiran[]" multiple class="w-full text-sm text-steel-gray file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-medium file:bg-tech-blue file:text-ice-blue hover:file:bg-[#5C3D29] file:transition-colors file:shadow-skeuo-btn cursor-pointer">
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="pt-6 mt-6 border-t border-steel-gray flex justify-end">
                <button type="submit" class="skeuo-btn px-8 text-lg">
                    Simpan Task
                </button>
            </div>
        </form>
    </div>
@endsection
