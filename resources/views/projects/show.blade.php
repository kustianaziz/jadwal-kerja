@extends('layouts.main')

@section('content')
    <div class="mb-4 flex justify-between items-start">
        <a href="{{ url('/') }}" class="text-cyan-glow hover:text-tech-blue font-medium flex items-center transition-colors">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Kembali
        </a>
        <div class="flex space-x-2">
            <a href="{{ route('projects.gantt', $project->id) }}" class="skeuo-btn py-1 px-3 text-sm flex items-center">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                Gantt Chart
            </a>
            <a href="{{ route('projects.edit', $project->id) }}" class="skeuo-btn-secondary py-1 px-3 text-sm">
                Edit Proyek
            </a>
            <form action="{{ route('projects.destroy', $project->id) }}" method="POST" onsubmit="event.preventDefault(); showConfirmModal('Yakin ingin menghapus seluruh data proyek ini beserta semua fase dan tugasnya?', this);">
                @csrf
                @method('DELETE')
                <button type="submit" class="py-1 px-3 text-sm bg-gauge-red text-white border border-[#8a2f20] shadow-[inset_0_1px_0_rgba(255,255,255,0.2),_0_2px_4px_rgba(0,0,0,0.2)] rounded-sm font-display uppercase tracking-wider active:shadow-[inset_0_2px_4px_rgba(0,0,0,0.3)] active:translate-y-px transition-all">
                    Hapus
                </button>
            </form>
        </div>
    </div>

    <!-- Project Header & Progress -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        <!-- Header Card -->
        <div class="lg:col-span-2 skeuo-card p-6 md:p-8 relative">
            <div class="absolute top-0 right-0 p-6">
                @php
                    $statusClass = 'stamp-healthy';
                    $statusText = 'On Track';
                    if(isset($project->status)) {
                        if($project->status == 'At Risk') {
                            $statusClass = 'stamp-at-risk';
                            $statusText = 'At Risk';
                        } elseif($project->status == 'Critical') {
                            $statusClass = 'stamp-critical';
                            $statusText = 'Critical';
                        } elseif($project->status == 'Attention') {
                            $statusClass = 'stamp-attention';
                            $statusText = 'Attention';
                        }
                    }
                @endphp
                <span class="{{ $statusClass }}">{{ $statusText }}</span>
            </div>

            <h1 class="text-3xl font-display font-bold text-dark-navy pr-32 mb-2">{{ $project->nama_proyek ?? 'Nama Proyek' }}</h1>
            <p class="text-steel-gray mb-6">Target Selesai: {{ isset($project->target_selesai) ? \Carbon\Carbon::parse($project->target_selesai)->format('d M Y') : '-' }}</p>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 pt-6 border-t border-steel-gray">
                <div>
                    <p class="text-xs font-medium text-steel-gray uppercase tracking-wider mb-1">Project Manager</p>
                    <div class="flex items-center">
                        <div class="w-6 h-6 rounded-full border border-steel-gray bg-white flex items-center justify-center text-xs mr-2 shadow-inner">
                            {{ substr($project->pm->name ?? 'P', 0, 1) }}
                        </div>
                        <span class="text-sm font-medium text-dark-navy">{{ $project->pm->name ?? '-' }}</span>
                    </div>
                </div>
                <div>
                    <p class="text-xs font-medium text-steel-gray uppercase tracking-wider mb-1">Grup/Divisi</p>
                    <p class="text-sm font-medium text-dark-navy">{{ $project->group->nama_grup ?? '-' }}</p>
                </div>
                <div class="col-span-2">
                    <p class="text-xs font-medium text-steel-gray uppercase tracking-wider mb-1">PIC (Tim Proyek)</p>
                    <div class="flex flex-wrap gap-2 mt-1">
                        @forelse($project->pics ?? [] as $pic)
                            <div class="flex items-center bg-ice-blue border border-steel-gray rounded-full px-2 py-0.5">
                                <div class="w-4 h-4 rounded-full bg-cyan-glow flex items-center justify-center text-[8px] mr-1 text-white font-bold">
                                    {{ substr($pic->nama, 0, 1) }}
                                </div>
                                <span class="text-xs font-medium text-dark-navy">{{ $pic->nama }}</span>
                            </div>
                        @empty
                            <span class="text-xs text-steel-gray italic">-</span>
                        @endforelse
                    </div>
                </div>
                <div>
                    <p class="text-xs font-medium text-steel-gray uppercase tracking-wider mb-1">Prioritas</p>
                    <p class="text-sm font-medium text-dark-navy">{{ $project->prioritas ?? 'Normal' }}</p>
                </div>
                <div>
                    <p class="text-xs font-medium text-steel-gray uppercase tracking-wider mb-1">Fase</p>
                    <p class="text-sm font-medium text-dark-navy tabular-nums">{{ isset($project->phases) ? $project->phases->count() : 0 }} Fase</p>
                </div>
            </div>
        </div>

        <!-- Progress Card -->
        <div class="skeuo-card p-6 md:p-8 flex flex-col justify-center items-center text-center">
            <p class="text-sm font-medium text-steel-gray uppercase tracking-wider mb-4">Overall Progress</p>
            
            <div class="text-6xl font-display font-bold text-dark-navy mb-4 tabular-nums" style="text-shadow: inset 0 2px 4px rgba(0,0,0,0.1);">
                {{ $project->progress ?? 0 }}<span class="text-3xl text-steel-gray">%</span>
            </div>
            
            <div class="w-full mt-auto">
                <div class="gauge-container h-8 mb-2">
                    <div class="gauge-bar bg-cyan-glow" style="width: {{ $project->progress ?? 0 }}%"></div>
                    <div class="gauge-ticks"></div>
                </div>
                <div class="flex justify-between text-xs text-steel-gray font-medium">
                    <span>0</span>
                    <span>25</span>
                    <span>50</span>
                    <span>75</span>
                    <span>100</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Phases Section -->
    <div class="mb-8">
        <h3 class="text-2xl font-display font-bold text-dark-navy mb-6">Fase & Task</h3>
        
        <div class="space-y-6">
            @forelse($project->phases ?? [] as $phase)
                <div class="skeuo-card border-l-4 border-l-cyan-glow overflow-visible">
                    <!-- Phase Header -->
                    <div class="p-4 md:p-6 bg-ice-blue/30 border-b border-steel-gray flex flex-col md:flex-row md:items-center justify-between gap-4">
                        <div class="flex-1">
                            <div class="flex flex-wrap items-center mb-1 gap-2">
                                <h4 class="text-xl font-display font-bold text-dark-navy">{{ $phase->nama_fase }}</h4>
                                @php
                                    $pStatus = 'stamp-healthy';
                                    if(isset($phase->status) && $phase->status == 'Terlambat') $pStatus = 'stamp-at-risk';
                                @endphp
                                <span class="{{ $pStatus }} text-[10px] py-0.5 px-2">{{ $phase->status ?? 'On Track' }}</span>
                                <div class="flex space-x-1 ml-auto md:ml-4">
                                    <a href="{{ route('phases.edit', $phase->id) }}" class="skeuo-btn-secondary py-0.5 px-2 text-[10px]">Edit</a>
                                    <form action="{{ route('phases.destroy', $phase->id) }}" method="POST" onsubmit="event.preventDefault(); showConfirmModal('Yakin hapus fase ini?', this);" class="inline-block">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="py-0.5 px-2 text-[10px] bg-gauge-red text-white border border-[#8a2f20] shadow-[inset_0_1px_0_rgba(255,255,255,0.2),_0_2px_4px_rgba(0,0,0,0.2)] rounded-sm font-display uppercase tracking-wider active:shadow-[inset_0_2px_4px_rgba(0,0,0,0.3)] active:translate-y-px transition-all">Hapus</button>
                                    </form>
                                </div>
                            </div>
                            <p class="text-sm text-steel-gray">Bobot: {{ $phase->bobot_pct }}% | Target: {{ \Carbon\Carbon::parse($phase->tanggal_target)->format('d M') }}</p>
                        </div>
                        
                        <div class="w-full md:w-1/3 flex items-center">
                            <span class="text-lg font-bold text-dark-navy mr-3 tabular-nums w-12 text-right">{{ $phase->progress_pct ?? 0 }}%</span>
                            <div class="flex-1 gauge-container h-6">
                                <div class="gauge-bar bg-tech-blue" style="width: {{ $phase->progress_pct ?? 0 }}%"></div>
                                <div class="gauge-ticks"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Tasks List -->
                    <div class="p-4 md:p-6">
                        <div class="space-y-3 mb-4">
                            @forelse($phase->tasks ?? [] as $task)
                                <div class="block p-4 border border-steel-gray rounded-md bg-white hover:bg-ice-blue/20 transition-colors shadow-sm group relative">
                                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                        <div class="flex-1">
                                            <a href="{{ route('tasks.edit', $task->id) }}" class="flex items-center group/link">
                                                <span class="w-2 h-2 rounded-full {{ ($task->progress_pct ?? 0) == 100 ? 'bg-gauge-green' : 'bg-gauge-amber' }} mr-2"></span>
                                                <h5 class="font-medium text-dark-navy group-hover/link:text-cyan-glow transition-colors">{{ $task->nama_task }}</h5>
                                            </a>
                                            <div class="flex items-center mt-2 text-xs text-steel-gray">
                                                <span class="mr-4">Bobot: {{ $task->bobot_pct }}%</span>
                                                <span class="mr-4">Deadline: {{ \Carbon\Carbon::parse($task->deadline)->format('d M') }}</span>
                                                <div class="flex items-center">
                                                    <div class="w-4 h-4 rounded-full border border-steel-gray bg-ice-blue flex items-center justify-center text-[8px] mr-1">
                                                        {{ substr($task->pics->first()->nama ?? 'U', 0, 1) }}
                                                    </div>
                                                    <span>{{ $task->pics->first()->nama ?? '-' }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="sm:text-right flex items-center gap-4 sm:flex-col sm:gap-2">
                                            <div class="text-sm font-bold text-dark-navy tabular-nums">{{ $task->progress_pct ?? 0 }}%</div>
                                            <div class="flex space-x-1">
                                                <a href="{{ route('tasks.edit', $task->id) }}" class="skeuo-btn-secondary py-0.5 px-2 text-[10px]">Edit</a>
                                                <form action="{{ route('tasks.destroy', $task->id) }}" method="POST" onsubmit="event.preventDefault(); showConfirmModal('Yakin hapus task ini?', this);" class="inline-block">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="py-0.5 px-2 text-[10px] bg-gauge-red text-white border border-[#8a2f20] shadow-[inset_0_1px_0_rgba(255,255,255,0.2),_0_2px_4px_rgba(0,0,0,0.2)] rounded-sm font-display uppercase tracking-wider active:shadow-[inset_0_2px_4px_rgba(0,0,0,0.3)] active:translate-y-px transition-all">Hapus</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <p class="text-sm text-steel-gray italic">Belum ada task di fase ini.</p>
                            @endforelse
                        </div>

                        <a href="{{ route('tasks.create', $phase->id) }}" class="skeuo-btn py-1.5 px-3 text-sm inline-flex">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                            Tambah Task
                        </a>
                    </div>
                </div>
            @empty
                <div class="p-8 text-center border-2 border-dashed border-steel-gray rounded-md bg-white">
                    <p class="text-steel-gray mb-4">Belum ada fase untuk proyek ini.</p>
                </div>
            @endforelse
        </div>

        <div class="mt-6">
            <a href="{{ route('phases.create', $project->id ?? 1) }}" class="block w-full py-4 text-center border-2 border-dashed border-steel-gray rounded-md text-steel-gray hover:text-dark-navy hover:border-dark-navy hover:bg-white transition-all font-medium">
                + Tambah Fase Baru
            </a>
        </div>
    </div>

    <!-- Journal Section -->
    <div class="mb-8">
        <h3 class="text-2xl font-display font-bold text-dark-navy mb-6">Jurnal & Aktivitas</h3>
        
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Timeline -->
            <div class="lg:col-span-2 space-y-6">
                @forelse($project->journals ?? [] as $journal)
                    <div class="skeuo-card p-5 relative pl-12">
                        <!-- Timeline Line (visually via border if we had a container, but using icon absolute positioning) -->
                        <div class="absolute left-4 top-5 w-6 h-6 rounded-full border border-steel-gray bg-ice-blue flex items-center justify-center shadow-inner z-10">
                            @if($journal->tipe == 'Update')
                                <svg class="w-3 h-3 text-tech-blue" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                            @elseif($journal->tipe == 'Issue')
                                <svg class="w-3 h-3 text-gauge-red" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                            @elseif($journal->tipe == 'Pencapaian')
                                <svg class="w-3 h-3 text-cyan-glow" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"></path></svg>
                            @else
                                <svg class="w-3 h-3 text-steel-gray" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                            @endif
                        </div>
                        
                        <div class="flex justify-between items-start mb-2">
                            <h5 class="font-bold text-dark-navy">{{ $journal->judul }}</h5>
                            <span class="text-xs text-steel-gray">{{ \Carbon\Carbon::parse($journal->created_at)->diffForHumans() }}</span>
                        </div>
                        <div class="text-sm text-dark-navy mb-3 prose prose-sm max-w-none">
                            {!! $journal->detail !!}
                        </div>
                        <div class="text-xs text-steel-gray font-medium flex items-center">
                            Ditulis oleh: {{ $journal->author->name ?? '-' }}
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center border-2 border-dashed border-steel-gray rounded-md bg-white">
                        <p class="text-steel-gray">Belum ada jurnal/aktivitas tercatat.</p>
                    </div>
                @endforelse
            </div>

            <!-- Add Journal Form -->
            <div>
                <form action="{{ route('journals.store', $project->id ?? 1) }}" method="POST" class="skeuo-card p-6 sticky top-8">
                    @csrf
                    <input type="hidden" name="project_id" value="{{ isset($project) ? $project->id : '' }}">
                    
                    <h4 class="text-lg font-display font-bold text-dark-navy mb-4 border-b border-steel-gray pb-2">Catat Jurnal Baru</h4>
                    
                    <div class="space-y-4">
                        <div>
                            <label for="task_id" class="block text-xs font-medium text-dark-navy mb-1">Task yang Dikerjakan</label>
                            <select id="task_id" name="task_id" class="skeuo-select py-1.5 text-sm" required>
                                <option value="">Pilih Task...</option>
                                @foreach($project->phases as $phase)
                                    <optgroup label="{{ $phase->nama_fase }}">
                                        @foreach($phase->tasks as $task)
                                            <option value="{{ $task->id }}">{{ $task->nama_task }} ({{ $task->progress_pct ?? 0 }}%)</option>
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label for="status" class="block text-xs font-medium text-dark-navy mb-1">Status Terbaru</label>
                                <select id="status" name="status" class="skeuo-select py-1.5 text-sm" required>
                                    <option value="in_progress">In Progress</option>
                                    <option value="review">Review</option>
                                    <option value="blocked">Blocked / Kendala</option>
                                    <option value="selesai">Selesai (100%)</option>
                                </select>
                            </div>
                            <div>
                                <label for="progress_pct" class="block text-xs font-medium text-dark-navy mb-1">Progress (%)</label>
                                <div class="relative">
                                    <input type="number" id="progress_pct" name="progress_pct" min="0" max="100" step="0.01" required class="skeuo-input py-1.5 pr-6 text-sm">
                                    <span class="absolute right-2 top-2 text-sm text-steel-gray pointer-events-none">%</span>
                                </div>
                            </div>
                        </div>

                        <div>
                            <label for="tipe" class="block text-xs font-medium text-dark-navy mb-1">Tipe Jurnal</label>
                            <select id="tipe" name="tipe" class="skeuo-select py-1.5 text-sm">
                                <option value="update">Update Progress</option>
                                <option value="issue">Issue/Hambatan</option>
                                <option value="pencapaian">Pencapaian (Milestone)</option>
                                <option value="system">Lainnya</option>
                            </select>
                        </div>
                        
                        <div>
                            <label for="judul" class="block text-xs font-medium text-dark-navy mb-1">Judul Catatan / Pekerjaan</label>
                            <input type="text" id="judul" name="judul" required class="skeuo-input py-1.5 text-sm" placeholder="Contoh: Menyelesaikan desain mockup UI">
                        </div>
                        
                        <div>
                            <label for="jurnal-editor" class="block text-xs font-medium text-dark-navy mb-1">Penjelasan / Kendala</label>
                            <textarea id="jurnal-editor" name="detail" rows="4" required class="skeuo-input py-1.5 text-sm" placeholder="Jelaskan secara singkat apa yang diselesaikan atau kendala apa yang terjadi..."></textarea>
                        </div>
                        
                        <button type="submit" class="skeuo-btn w-full text-sm py-2">
                            Simpan Catatan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
