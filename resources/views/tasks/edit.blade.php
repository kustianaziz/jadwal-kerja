@extends('layouts.main')

@section('content')
    <div class="mb-4 flex justify-between items-start">
        <a href="{{ route('projects.show', $task->phase->project_id ?? 1) }}" class="text-cyan-glow hover:text-tech-blue font-medium flex items-center transition-colors">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Kembali
        </a>
    </div>

    <div class="mb-8">
        <h2 class="text-3xl font-display font-bold text-dark-navy">Update Task</h2>
        <p class="text-steel-gray mt-1">Fase: <span class="font-medium">{{ $task->phase->nama_fase ?? 'Nama Fase' }}</span></p>
    </div>

    <div class="max-w-2xl">
        <div class="skeuo-card p-6 md:p-8 mb-6">
            <h3 class="text-xl font-display font-bold text-dark-navy mb-2">{{ $task->nama_task ?? 'Nama Task' }}</h3>
            <p class="text-sm text-dark-navy mb-4">{{ $task->deskripsi ?? 'Tidak ada deskripsi.' }}</p>
            
            <div class="flex items-center text-xs text-steel-gray border-t border-steel-gray pt-4">
                <span class="mr-4">Deadline: <strong>{{ isset($task->deadline) ? \Carbon\Carbon::parse($task->deadline)->format('d M Y') : '-' }}</strong></span>
                <span>PIC: <strong>{{ $task->pics->first()->nama ?? '-' }}</strong></span>
            </div>
        </div>

        <form action="{{ route('tasks.update', isset($task) ? $task->id : '') }}" method="POST" class="skeuo-card p-6 md:p-8 space-y-6">
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
            
            <!-- Progress Slider/Input -->
            <div class="bg-ice-blue/50 p-6 rounded-md border border-steel-gray shadow-inner">
                <label for="progress" class="block text-center text-sm font-medium text-dark-navy mb-4 uppercase tracking-wider">Update Progress Saat Ini</label>
                
                <div class="flex flex-col items-center">
                    <div class="flex items-center justify-center mb-4 relative">
                        <input type="number" id="progress" name="progress_pct" min="0" max="100" value="{{ $task->progress_pct ?? 0 }}" required class="skeuo-input w-32 text-center text-4xl font-display font-bold py-4 tabular-nums">
                        <span class="absolute right-4 text-2xl font-bold text-steel-gray pointer-events-none">%</span>
                    </div>
                    
                    <input type="range" min="0" max="100" value="{{ $task->progress_pct ?? 0 }}" class="w-full h-2 bg-steel-gray rounded-lg appearance-none cursor-pointer accent-cyan-glow" oninput="document.getElementById('progress').value = this.value">
                    
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
                    <option value="belum_mulai" {{ ($task->status ?? '') == 'belum_mulai' ? 'selected' : '' }}>Belum Mulai</option>
                    <option value="in_progress" {{ ($task->status ?? '') == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                    <option value="review" {{ ($task->status ?? '') == 'review' ? 'selected' : '' }}>Review</option>
                    <option value="blocked" {{ ($task->status ?? '') == 'blocked' ? 'selected' : '' }}>Blocked / Kendala</option>
                    <option value="selesai" {{ ($task->status ?? '') == 'selesai' ? 'selected' : '' }}>Selesai (100%)</option>
                </select>
            </div>

            <!-- Catatan -->
            <div>
                <label for="catatan" class="block text-sm font-medium text-dark-navy mb-2">Catatan Update (Opsional)</label>
                <textarea id="catatan" name="catatan" rows="3" placeholder="Tuliskan kendala atau progress singkat..." class="skeuo-input"></textarea>
            </div>

            <!-- Actions -->
            <div class="pt-6 mt-6 border-t border-steel-gray flex justify-end">
                <button type="submit" class="skeuo-btn-success px-8 text-lg w-full md:w-auto">
                    Simpan Progress
                </button>
            </div>
        </form>
    </div>
@endsection
