@extends('layouts.main')

@section('content')
    <div class="mb-4 flex justify-between items-start">
        <a href="{{ route('projects.show', $task->phase->project_id ?? 1) }}" class="text-cyan-glow hover:text-tech-blue font-medium flex items-center transition-colors">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Kembali
        </a>
    </div>

    <div class="mb-8">
        <h2 class="text-3xl font-display font-bold text-dark-navy">Edit Task</h2>
        <p class="text-steel-gray mt-1">Fase: <span class="font-medium text-dark-navy font-bold">{{ $task->phase->nama_fase ?? 'Nama Fase' }}</span></p>
    </div>

    <div class="max-w-3xl">
        <form action="{{ route('tasks.update', $task->id) }}" method="POST" enctype="multipart/form-data" class="skeuo-card p-6 md:p-8 space-y-6">
            @csrf
            @method('PUT')
            
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
                    <input type="text" id="nama_task" name="nama_task" value="{{ old('nama_task', $task->nama_task) }}" required class="skeuo-input text-lg py-2">
                </div>

                <!-- Bobot -->
                <div>
                    <div class="flex justify-between items-end mb-2">
                        <label for="bobot_pct" class="block text-sm font-medium text-dark-navy">Bobot Task (%)</label>
                    </div>
                    <div class="flex items-center">
                        <input type="number" id="bobot_pct" name="bobot_pct" value="{{ old('bobot_pct', $task->bobot_pct) }}" min="0.01" max="{{ $sisaBobot + $task->bobot_pct }}" step="0.01" required class="skeuo-input w-24 text-center font-bold">
                        <span class="ml-3 text-sm text-steel-gray">Maksimal tersedia: <strong class="text-gauge-amber">{{ $sisaBobot + $task->bobot_pct }}%</strong></span>
                    </div>
                </div>

                <!-- Prioritas -->
                <div>
                    <label for="prioritas" class="block text-sm font-medium text-dark-navy mb-2">Prioritas</label>
                    <select id="prioritas" name="prioritas" class="skeuo-select">
                        <option value="low" {{ old('prioritas', $task->prioritas) === 'low' ? 'selected' : '' }}>Rendah</option>
                        <option value="medium" {{ old('prioritas', $task->prioritas) === 'medium' ? 'selected' : '' }}>Normal</option>
                        <option value="high" {{ old('prioritas', $task->prioritas) === 'high' ? 'selected' : '' }}>Tinggi</option>
                    </select>
                </div>

                <!-- Dates -->
                <div>
                    <label for="tanggal_mulai" class="block text-sm font-medium text-dark-navy mb-2">Tanggal Mulai</label>
                    <input type="date" id="tanggal_mulai" name="tanggal_mulai" value="{{ old('tanggal_mulai', $task->tanggal_mulai ? \Carbon\Carbon::parse($task->tanggal_mulai)->format('Y-m-d') : '') }}" class="skeuo-input">
                </div>
                <div>
                    <label for="deadline" class="block text-sm font-medium text-dark-navy mb-2">Deadline</label>
                    <input type="date" id="deadline" name="deadline" value="{{ old('deadline', $task->deadline ? \Carbon\Carbon::parse($task->deadline)->format('Y-m-d') : '') }}" required class="skeuo-input">
                </div>

                @php
                    $picUtamaId = $task->pics->where('pivot.peran', 'utama')->first()->id ?? null;
                    $kontributorIds = $task->pics->where('pivot.peran', 'kontributor')->pluck('id')->toArray();
                @endphp

                <!-- Assignees -->
                <div>
                    <label for="pic_utama" class="block text-sm font-medium text-dark-navy mb-2">PIC Utama</label>
                    <select id="pic_utama" name="pic_utama" required class="skeuo-select">
                        <option value="">Pilih PIC</option>
                        @foreach($pics ?? [] as $pic)
                            <option value="{{ $pic->id }}" {{ $picUtamaId === $pic->id ? 'selected' : '' }}>{{ $pic->nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-dark-navy mb-2">Kontributor (Opsional)</label>
                    <div class="skeuo-input max-h-32 overflow-y-auto space-y-2 py-2">
                        @foreach($pics ?? [] as $pic)
                            <label class="flex items-center px-2 py-1 hover:bg-ice-blue/50 rounded cursor-pointer transition-colors">
                                <input type="checkbox" name="kontributor[]" value="{{ $pic->id }}" {{ in_array($pic->id, $kontributorIds) ? 'checked' : '' }} class="text-cyan-glow border-steel-gray rounded shadow-inner focus:ring-cyan-glow bg-ice-blue mr-3">
                                <span class="text-sm text-dark-navy">{{ $pic->nama }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <!-- Description & Files -->
                <div class="md:col-span-2">
                    <label for="deskripsi" class="block text-sm font-medium text-dark-navy mb-2">Detail Instruksi</label>
                    <textarea id="deskripsi" name="deskripsi" rows="4" class="skeuo-input">{{ old('deskripsi', $task->deskripsi) }}</textarea>
                </div>

                <div class="md:col-span-2">
                    <label for="lampiran" class="block text-sm font-medium text-dark-navy mb-2">Tambah Lampiran Baru (Bisa Multi File)</label>
                    <input type="file" id="lampiran" name="lampiran[]" multiple class="skeuo-input py-1.5 text-sm">
                </div>
            </div>

            <!-- Progress Slider/Input -->
            <div class="bg-ice-blue/50 p-6 rounded-md border border-steel-gray shadow-inner">
                <label for="progress" class="block text-center text-sm font-medium text-dark-navy mb-4应用-wider uppercase tracking-wider">Update Progress Saat Ini</label>
                
                <div class="flex flex-col items-center">
                    <div class="flex items-center justify-center mb-4 relative">
                        <input type="number" id="progress" name="progress_pct" min="0" max="100" value="{{ old('progress_pct', $task->progress_pct ?? 0) }}" required class="skeuo-input w-32 text-center text-4xl font-display font-bold py-4 tabular-nums">
                        <span class="absolute right-4 text-2xl font-bold text-steel-gray pointer-events-none">%</span>
                    </div>
                    
                    <input type="range" min="0" max="100" value="{{ old('progress_pct', $task->progress_pct ?? 0) }}" class="w-full h-2 bg-steel-gray rounded-lg appearance-none cursor-pointer accent-cyan-glow" oninput="document.getElementById('progress').value = this.value">
                    
                    <div class="w-full flex justify-between mt-2 text-xs text-steel-gray font-medium">
                        <span>0%</span>
                        <span>50%</span>
                        <span>100%</span>
                    </div>
                </div>
            </div>

            <!-- Status -->
            <div>
                <label for="status" class="block text-sm font-medium text-dark-navy mb-2">Status Task</label>
                <select id="status" name="status" class="skeuo-select">
                    <option value="belum_mulai" {{ old('status', $task->status) == 'belum_mulai' ? 'selected' : '' }}>Belum Mulai</option>
                    <option value="in_progress" {{ old('status', $task->status) == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                    <option value="review" {{ old('status', $task->status) == 'review' ? 'selected' : '' }}>Review</option>
                    <option value="blocked" {{ old('status', $task->status) == 'blocked' ? 'selected' : '' }}>Blocked / Kendala</option>
                    <option value="selesai" {{ old('status', $task->status) == 'selesai' ? 'selected' : '' }}>Selesai (100%)</option>
                </select>
            </div>

            <!-- Actions -->
            <div class="pt-6 mt-6 border-t border-steel-gray flex justify-end space-x-3">
                <a href="{{ route('projects.show', $task->phase->project_id ?? 1) }}" class="skeuo-btn-secondary px-6">Batal</a>
                <button type="submit" class="skeuo-btn px-8">
                    Simpan Perubahan Task
                </button>
            </div>
        </form>
    </div>
@endsection

