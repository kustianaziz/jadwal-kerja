<!-- Project Row -->
<tr x-show="{!! isset($groupId) ? "expandedGroups.includes('$groupId')" : 'true' !!}" {!! isset($groupId) ? 'x-cloak' : '' !!} class="hover:bg-ice-blue/30 transition-colors bg-white font-medium border-b-2 border-steel-gray">
    <td class="p-3 text-center">
        <button @click="expandedProjects.includes('{{ $project->id }}') ? expandedProjects = expandedProjects.filter(id => id != '{{ $project->id }}') : expandedProjects.push('{{ $project->id }}')" class="text-steel-gray hover:text-tech-blue focus:outline-none">
            <svg x-show="!expandedProjects.includes('{{ $project->id }}')" class="w-4 h-4 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
            <svg x-show="expandedProjects.includes('{{ $project->id }}')" class="w-4 h-4 transition-transform rotate-90" x-cloak fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
        </button>
    </td>
    <td class="p-3 font-bold text-dark-navy pl-6">
        <a href="{{ route('projects.show', $project->id) }}" class="hover:text-cyan-glow transition-colors">
            {{ $project->nama_proyek }}
        </a>
    </td>
    <td class="p-3">
        <div class="flex items-center">
            <div class="w-5 h-5 rounded-full bg-tech-blue text-ice-blue flex items-center justify-center text-[10px] mr-2">
                {{ substr($project->pm->name ?? 'P', 0, 1) }}
            </div>
            <span class="text-xs">{{ $project->pm->name ?? '-' }} (PM)</span>
        </div>
    </td>
    <td class="p-3 text-xs text-dark-navy whitespace-nowrap">
        {{ $project->phases->min('tanggal_mulai') ? \Carbon\Carbon::parse($project->phases->min('tanggal_mulai'))->format('d M Y') : '-' }}
    </td>
    <td class="p-3 text-xs text-dark-navy whitespace-nowrap">
        {{ $project->phases->max('tanggal_target') ? \Carbon\Carbon::parse($project->phases->max('tanggal_target'))->format('d M Y') : ($project->target_selesai ? \Carbon\Carbon::parse($project->target_selesai)->format('d M Y') : '-') }}
    </td>
    <td class="p-3">
        <div class="flex items-center">
            <span class="text-xs font-bold mr-2 tabular-nums w-8">{{ $project->progress_pct ?? 0 }}%</span>
            <div class="flex-1 gauge-container h-3">
                <div class="gauge-bar bg-cyan-glow" style="width: {{ $project->progress_pct ?? 0 }}%"></div>
            </div>
        </div>
    </td>
    <td class="p-3 text-right whitespace-nowrap">
        <button @click="showAddPhase = showAddPhase === '{{ $project->id }}' ? null : '{{ $project->id }}'; if(!expandedProjects.includes('{{ $project->id }}')) expandedProjects.push('{{ $project->id }}')" class="skeuo-btn-secondary py-1 px-2 text-[10px] bg-white">
            + Fase
        </button>
        <a href="{{ route('projects.show', $project->id) }}" class="skeuo-btn-secondary py-1 px-2 text-[10px]">Detail</a>
    </td>
</tr>

<!-- Quick Add Phase Form (Inline) -->
<tr x-show="showAddPhase === '{{ $project->id }}'" x-transition x-cloak class="bg-ice-blue/10">
    <td></td>
    <td colspan="6" class="p-0">
        <form action="{{ route('phases.store', $project->id) }}" method="POST" class="p-4 border-l-4 border-tech-blue ml-4 bg-white shadow-inner flex flex-col gap-3">
            @csrf
            <input type="text" name="nama_fase" placeholder="Nama Fase Baru..." required class="skeuo-input py-1.5 px-3 text-sm w-full font-bold text-dark-navy">
            
            <div class="flex flex-wrap gap-3 items-center">
                <select name="pic_id" class="skeuo-select py-1 px-2 text-xs w-32" required>
                    <option value="">Pilih PIC...</option>
                    @foreach($allPics ?? [] as $pic)
                        <option value="{{ $pic->id }}">{{ $pic->nama }}</option>
                    @endforeach
                </select>
                <div class="relative">
                    <input type="number" name="bobot_pct" placeholder="Bobot" step="0.01" min="0.01" max="100" required class="skeuo-input py-1 px-2 pr-6 text-xs w-24">
                    <span class="absolute right-2 top-1.5 text-xs text-steel-gray pointer-events-none">%</span>
                </div>
                <div class="flex items-center text-xs text-steel-gray">
                    <span class="mr-1 font-medium uppercase tracking-wider">Start:</span>
                    <input type="date" name="tanggal_mulai" class="skeuo-input py-1 px-2 text-xs w-32" title="Start Date">
                </div>
                <div class="flex items-center text-xs text-steel-gray">
                    <span class="mr-1 font-medium uppercase tracking-wider">Finish:</span>
                    <input type="date" name="tanggal_target" class="skeuo-input py-1 px-2 text-xs w-32" title="End Date">
                </div>
                
                <div class="ml-auto flex gap-2">
                    <button type="submit" class="skeuo-btn py-1 px-4 text-xs font-bold uppercase tracking-wider">Simpan</button>
                    <button type="button" @click="showAddPhase = null" class="skeuo-btn-secondary py-1 px-3 text-xs uppercase tracking-wider">Batal</button>
                </div>
            </div>
        </form>
    </td>
</tr>

<!-- Phases Loop -->
@foreach($project->phases ?? [] as $phase)
    <tr x-show="expandedProjects.includes('{{ $project->id }}') {!! isset($groupId) ? "&& expandedGroups.includes('$groupId')" : '' !!}" x-cloak class="hover:bg-ice-blue/20 transition-colors bg-white/50"
        @dragover.prevent="event.dataTransfer.dropEffect = 'move'; $el.classList.add('bg-cyan-glow/20')"
        @dragleave="$el.classList.remove('bg-cyan-glow/20')"
        @drop="
            $el.classList.remove('bg-cyan-glow/20');
            let taskId = event.dataTransfer.getData('text/plain');
            if(taskId) { moveTask(taskId, {{ $phase->id }}); }
        ">
        <td class="p-3 text-right border-l-4 border-transparent pr-4">
            <button @click="expandedPhases.includes('{{ $phase->id }}') ? expandedPhases = expandedPhases.filter(id => id != '{{ $phase->id }}') : expandedPhases.push('{{ $phase->id }}')" class="text-steel-gray hover:text-tech-blue focus:outline-none">
                <svg x-show="!expandedPhases.includes('{{ $phase->id }}')" class="w-3 h-3 transition-transform inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                <svg x-show="expandedPhases.includes('{{ $phase->id }}')" class="w-3 h-3 transition-transform rotate-90 inline" x-cloak fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
            </button>
        </td>
        <td class="p-3 text-sm font-medium text-dark-navy pl-6">
            <div class="flex items-center">
                <svg class="w-4 h-4 mr-2 text-steel-gray" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path></svg>
                {{ $phase->nama_fase }}
            </div>
        </td>
        <td class="p-3 text-xs text-steel-gray">
            {{ $phase->pic->nama ?? '-' }}
        </td>
        <td class="p-3 text-xs text-dark-navy whitespace-nowrap">{{ $phase->tanggal_mulai ? \Carbon\Carbon::parse($phase->tanggal_mulai)->format('d M Y') : '-' }}</td>
        <td class="p-3 text-xs text-dark-navy whitespace-nowrap">{{ $phase->tanggal_target ? \Carbon\Carbon::parse($phase->tanggal_target)->format('d M Y') : '-' }}</td>
        <td class="p-3">
            <div class="flex items-center">
                <span class="text-xs font-bold mr-2 tabular-nums w-8">{{ $phase->progress_pct ?? 0 }}%</span>
                <div class="flex-1 gauge-container h-2">
                    <div class="gauge-bar bg-tech-blue" style="width: {{ $phase->progress_pct ?? 0 }}%"></div>
                </div>
            </div>
        </td>
        <td class="p-3 text-right whitespace-nowrap">
            <button @click="showCopyPhase = showCopyPhase === '{{ $phase->id }}' ? null : '{{ $phase->id }}'; if(!expandedPhases.includes('{{ $phase->id }}')) expandedPhases.push('{{ $phase->id }}')" class="text-gauge-amber hover:text-tech-blue font-bold text-[10px] uppercase tracking-wider bg-white border border-steel-gray px-2 py-1 rounded shadow-inner mr-1" title="Salin Fase ini">
                Copy
            </button>
            <button @click="showAddTask = showAddTask === '{{ $phase->id }}' ? null : '{{ $phase->id }}'; if(!expandedPhases.includes('{{ $phase->id }}')) expandedPhases.push('{{ $phase->id }}')" class="skeuo-btn-secondary py-1 px-2 text-[10px] bg-white">
                + Task
            </button>
        </td>
    </tr>

    <!-- Copy Phase Form (Inline) -->
    <tr x-show="showCopyPhase === '{{ $phase->id }}'" x-transition x-cloak class="bg-ice-blue/10">
        <td></td>
        <td colspan="6" class="p-0">
            <form action="{{ route('phases.copy', $phase->id) }}" method="POST" class="p-4 border-l-4 border-gauge-amber ml-10 bg-white shadow-inner flex flex-col gap-3">
                @csrf
                <div class="flex items-center text-sm font-bold text-dark-navy">
                    <svg class="w-4 h-4 mr-2 text-gauge-amber" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                    Salin Fase: <span class="ml-2 text-tech-blue">{{ $phase->nama_fase }}</span>
                </div>
                
                <div class="flex flex-wrap gap-3 items-center">
                    <select name="target_project_id" class="skeuo-select py-1 px-2 text-xs w-64" required>
                        <option value="">Pilih Proyek Tujuan...</option>
                        @foreach(\App\Models\Project::all() as $p)
                            <option value="{{ $p->id }}">{{ $p->nama_proyek }}</option>
                        @endforeach
                    </select>
                    
                    <label class="flex items-center text-xs text-dark-navy cursor-pointer hover:bg-ice-blue/30 px-2 py-1 rounded">
                        <input type="checkbox" name="include_tasks" value="1" class="text-cyan-glow border-steel-gray rounded shadow-inner mr-2" checked>
                        Sertakan Semua Task & Detail
                    </label>
                    
                    <div class="ml-auto flex gap-2">
                        <button type="submit" class="skeuo-btn py-1 px-4 text-xs font-bold uppercase tracking-wider">Salin Fase</button>
                        <button type="button" @click="showCopyPhase = null" class="skeuo-btn-secondary py-1 px-3 text-xs uppercase tracking-wider">Batal</button>
                    </div>
                </div>
            </form>
        </td>
    </tr>

    <!-- Quick Add Task Form (Inline) -->
    <tr x-show="showAddTask === '{{ $phase->id }}'" x-transition x-cloak class="bg-ice-blue/10">
        <td></td>
        <td colspan="6" class="p-0">
            <form action="{{ route('tasks.store', $phase->id) }}" method="POST" class="p-4 border-l-4 border-cyan-glow ml-10 bg-white shadow-inner flex flex-col gap-3">
                @csrf
                <input type="text" name="nama_task" placeholder="Nama Task Baru..." required class="skeuo-input py-1.5 px-3 text-sm w-full font-bold text-dark-navy">
                
                <div class="flex flex-wrap gap-3 items-center">
                    <select name="pic_utama" class="skeuo-select py-1 px-2 text-xs w-32" required>
                        <option value="">Pilih PIC...</option>
                        @foreach($allPics ?? [] as $pic)
                            <option value="{{ $pic->id }}">{{ $pic->nama }}</option>
                        @endforeach
                    </select>
                    <select name="prioritas" class="skeuo-select py-1 px-2 text-xs w-24" required>
                        <option value="medium">Normal</option>
                        <option value="high">Tinggi</option>
                        <option value="low">Rendah</option>
                    </select>
                    <div class="relative">
                        <input type="number" name="bobot_pct" placeholder="Bobot" step="0.01" min="0.01" max="100" required class="skeuo-input py-1 px-2 pr-6 text-xs w-24">
                        <span class="absolute right-2 top-1.5 text-xs text-steel-gray pointer-events-none">%</span>
                    </div>
                    <div class="flex items-center text-xs text-steel-gray">
                        <span class="mr-1 font-medium uppercase tracking-wider">Start:</span>
                        <input type="date" name="tanggal_mulai" class="skeuo-input py-1 px-2 text-xs w-32" title="Start Date">
                    </div>
                    <div class="flex items-center text-xs text-steel-gray">
                        <span class="mr-1 font-medium uppercase tracking-wider">Due:</span>
                        <input type="date" name="deadline" class="skeuo-input py-1 px-2 text-xs w-32" title="End Date">
                    </div>
                    
                    <div class="ml-auto flex gap-2">
                        <button type="submit" class="skeuo-btn py-1 px-4 text-xs font-bold uppercase tracking-wider">Simpan</button>
                        <button type="button" @click="showAddTask = null" class="skeuo-btn-secondary py-1 px-3 text-xs uppercase tracking-wider">Batal</button>
                    </div>
                </div>
            </form>
        </td>
    </tr>

    <!-- Tasks Loop -->
    @foreach($phase->tasks ?? [] as $task)
        <tr x-show="expandedPhases.includes('{{ $phase->id }}') {!! isset($groupId) ? "&& expandedGroups.includes('$groupId') && expandedProjects.includes('{$project->id}')" : '' !!}" x-cloak class="hover:bg-ice-blue/10 transition-colors bg-white/20 text-sm"
            draggable="true"
            @dragstart="event.dataTransfer.setData('text/plain', {{ $task->id }}); event.dataTransfer.effectAllowed = 'move'; $el.classList.add('opacity-50')"
            @dragend="$el.classList.remove('opacity-50')">
            <td class="p-3 border-l-4 border-transparent"></td>
            <td class="p-3 text-dark-navy pl-12 flex items-center cursor-grab active:cursor-grabbing">
                <svg class="w-3 h-3 mr-2 text-steel-gray" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                <span class="w-1.5 h-1.5 rounded-full {{ ($task->progress_pct ?? 0) == 100 ? 'bg-gauge-green' : 'bg-gauge-amber' }} mr-2 inline-block"></span>
                {{ $task->nama_task }}
            </td>
            <td class="p-3 text-xs text-steel-gray">
                {{ $task->pics->first()->nama ?? '-' }}
            </td>
            <td class="p-3 text-xs text-dark-navy whitespace-nowrap">{{ $task->tanggal_mulai ? \Carbon\Carbon::parse($task->tanggal_mulai)->format('d M Y') : '-' }}</td>
            <td class="p-3 text-xs text-dark-navy whitespace-nowrap">{{ $task->deadline ? \Carbon\Carbon::parse($task->deadline)->format('d M Y') : '-' }}</td>
            <td class="p-3">
                <div class="flex items-center">
                    <span class="text-xs font-bold mr-2 tabular-nums w-8">{{ $task->progress_pct ?? 0 }}%</span>
                </div>
            </td>
            <td class="p-3 text-right whitespace-nowrap">
                <button @click="showAddJournal = showAddJournal === '{{ $task->id }}' ? null : '{{ $task->id }}'" class="skeuo-btn-secondary py-1 px-2 text-[10px] bg-white mr-1">
                    + Jurnal
                </button>
                <a href="{{ route('tasks.edit', $task->id) }}" class="text-cyan-glow hover:text-tech-blue text-[10px] font-bold uppercase tracking-wider">Edit</a>
            </td>
        </tr>
        
        <!-- Quick Add Journal Form (Inline) -->
        <tr x-show="showAddJournal === '{{ $task->id }}'" x-transition x-cloak class="bg-ice-blue/10">
            <td></td>
            <td colspan="6" class="p-0">
                <form action="{{ route('journals.store', $project->id) }}" method="POST" enctype="multipart/form-data" class="p-4 border-l-4 border-gauge-green ml-16 bg-white shadow-inner flex flex-col gap-3">
                    @csrf
                    <input type="hidden" name="task_id" value="{{ $task->id }}">
                    <input type="hidden" name="redirect_to" value="dashboard">
                    <div class="flex items-center text-sm font-bold text-dark-navy border-b border-steel-gray pb-2 mb-2">
                        <svg class="w-4 h-4 mr-2 text-gauge-green" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                        Catat Jurnal: <span class="ml-2 text-tech-blue">{{ $task->nama_task }}</span>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-3">
                            <input type="text" name="judul" placeholder="Judul Pekerjaan..." required class="skeuo-input py-1.5 px-3 text-sm w-full font-medium">
                            <div class="flex gap-2">
                                <input type="date" name="tanggal" value="{{ date('Y-m-d') }}" required class="skeuo-input py-1.5 px-3 text-sm flex-1 text-steel-gray">
                                <select name="tipe" class="skeuo-select py-1.5 px-2 text-xs flex-1">
                                    <option value="update">Update Progress</option>
                                    <option value="issue">Issue/Kendala</option>
                                    <option value="pencapaian">Pencapaian</option>
                                </select>
                            </div>
                            <textarea name="detail" rows="2" placeholder="Detail pekerjaan atau kendala..." class="skeuo-input py-1.5 px-3 text-sm w-full" required></textarea>
                            
                            <div x-data="{ links: [{url: ''}] }" class="space-y-1">
                                <label class="block text-[10px] font-bold text-steel-gray uppercase tracking-wider mb-1">Tautan / Link</label>
                                <template x-for="(link, index) in links" :key="index">
                                    <div class="flex items-center mb-1">
                                        <input type="url" name="tautan[]" x-model="link.url" placeholder="https://..." class="skeuo-input w-full py-1 px-2 text-xs">
                                        <button type="button" @click="links.splice(index, 1)" x-show="links.length > 1" class="ml-2 text-gauge-red hover:text-red-700">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                        </button>
                                    </div>
                                </template>
                                <button type="button" @click="links.push({url: ''})" class="text-[10px] text-tech-blue hover:underline">+ Tambah Tautan</button>
                            </div>
                        </div>
                        <div class="space-y-3">
                            <div class="flex gap-2">
                                <select name="status" class="skeuo-select py-1.5 px-2 text-xs flex-1" required>
                                    <option value="in_progress" {{ $task->status == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                    <option value="review" {{ $task->status == 'review' ? 'selected' : '' }}>Review</option>
                                    <option value="blocked" {{ $task->status == 'blocked' ? 'selected' : '' }}>Blocked</option>
                                    <option value="selesai" {{ $task->status == 'selesai' ? 'selected' : '' }}>Selesai</option>
                                </select>
                                <div class="relative w-24">
                                    <input type="number" name="progress_pct" placeholder="%" step="0.01" min="0" max="100" value="{{ $task->progress_pct ?? 0 }}" required class="skeuo-input py-1.5 pr-6 text-sm w-full">
                                    <span class="absolute right-2 top-1.5 text-xs text-steel-gray pointer-events-none">%</span>
                                </div>
                            </div>
                            
                            <div>
                                <label class="block text-[10px] font-bold text-steel-gray uppercase tracking-wider mb-1">Lampiran File (Bisa pilih lebih dari 1)</label>
                                <input type="file" name="lampiran[]" multiple class="skeuo-input w-full py-1.5 px-2 text-xs bg-white" accept="image/*,.pdf,.doc,.docx,.xls,.xlsx">
                                <p class="text-[9px] text-steel-gray mt-1 leading-tight">Tekan CTRL/CMD saat memilih file untuk upload banyak file sekaligus.</p>
                            </div>
                            
                            <div class="flex gap-2 mt-4">
                                <button type="submit" class="skeuo-btn py-1.5 px-4 text-xs font-bold uppercase tracking-wider flex-1">Simpan Jurnal</button>
                                <button type="button" @click="showAddJournal = null" class="skeuo-btn-secondary py-1.5 px-3 text-xs uppercase tracking-wider">Batal</button>
                            </div>
                        </div>
                    </div>
                </form>
            </td>
        </tr>
        
        <!-- List Jurnal Inline -->
        @if(isset($task->journals) && $task->journals->count() > 0)
            <tr x-show="expandedPhases.includes('{{ $phase->id }}') {!! isset($groupId) ? "&& expandedGroups.includes('$groupId') && expandedProjects.includes('{$project->id}')" : '' !!}" x-cloak class="bg-ice-blue/5">
                <td></td>
                <td colspan="6" class="p-0">
                    <div class="ml-16 mr-4 my-2 space-y-1">
                        <!-- Alternatif: Tombol Bulk Aksi Inline di dalam baris Task -->
                        <div class="flex items-center p-2 mb-2 bg-ice-blue rounded border border-tech-blue shadow-inner justify-between">
                            <span class="text-xs font-bold text-dark-navy">Pilih jurnal di bawah untuk melakukan aksi masal.</span>
                            <div class="flex gap-2">
                                <button type="button" @click="if(selectedJournals.length > 0) showBulkMove = true; else alert('Pilih minimal 1 jurnal terlebih dahulu!');" class="skeuo-btn py-1 px-3 text-[10px] uppercase tracking-wider font-bold">Pindah Jurnal Terpilih</button>
                                <button type="button" @click="if(selectedJournals.length > 0) showBulkCopy = true; else alert('Pilih minimal 1 jurnal terlebih dahulu!');" class="skeuo-btn-secondary py-1 px-3 text-[10px] uppercase tracking-wider font-bold border border-tech-blue">Salin Jurnal Terpilih</button>
                            </div>
                        </div>

                        @foreach($task->journals->where('tipe', '!=', 'system') as $journal)
                            <div class="bg-white p-2 rounded border-l-2 border-gauge-amber text-xs shadow-sm flex flex-col relative">
                                <div class="flex justify-between items-start">
                                    <div class="flex items-start">
                                        <input type="checkbox" value="{{ $journal->id }}" x-model="selectedJournals" class="mr-2 mt-1 rounded border-steel-gray text-tech-blue focus:ring-tech-blue w-3 h-3">
                                        <div>
                                            <strong class="text-dark-navy">{{ $journal->judul }}</strong>
                                            <div class="mt-1">
                                                <button type="button" @click="showMoveJournal = {{ $journal->id }}" class="text-gauge-amber hover:text-dark-navy uppercase tracking-wider text-[9px] font-bold mr-2">Pindah</button>
                                                <button type="button" @click="showCopyJournal = {{ $journal->id }}" class="text-tech-blue hover:text-dark-navy uppercase tracking-wider text-[9px] font-bold mr-2">Salin</button>
                                                <button type="button" @click="showEditJournal = {{ $journal->id }}" class="text-tech-blue hover:text-cyan-glow uppercase tracking-wider text-[9px] font-bold mr-2">Edit</button>
                                                <button type="button" @click="showDeleteJournal = {{ $journal->id }}" class="text-gauge-red hover:text-dark-navy uppercase tracking-wider text-[9px] font-bold">Hapus</button>
                                            </div>
                                        </div>
                                    </div>
                                    <span class="text-[9px] text-steel-gray">{{ $journal->tanggal ? \Carbon\Carbon::parse($journal->tanggal)->format('d M Y') : $journal->created_at->format('d M H:i') }}</span>
                                </div>
                                <div class="text-dark-navy/80 mt-1 prose prose-sm prose-p:my-0 prose-ul:my-0 leading-tight">
                                    {!! $journal->detail !!}
                                </div>
                                
                                @if(!empty($journal->tautan) && count($journal->tautan) > 0)
                                    <div class="mt-2 flex flex-wrap gap-1">
                                        @foreach($journal->tautan as $link)
                                            <a href="{{ $link }}" target="_blank" class="inline-flex items-center bg-ice-blue border border-tech-blue/30 text-tech-blue px-2 py-0.5 rounded-full text-[10px] hover:bg-tech-blue hover:text-white transition-colors">
                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path></svg>
                                                Tautan
                                            </a>
                                        @endforeach
                                    </div>
                                @endif
                                
                                @if($journal->attachments && $journal->attachments->count() > 0)
                                    <div class="mt-2 flex flex-wrap gap-1">
                                        @foreach($journal->attachments as $file)
                                            <a href="{{ asset('storage/' . $file->path_file) }}" download="{{ $file->nama_file }}" target="_blank" class="inline-flex items-center bg-ice-blue border border-steel-gray/30 text-dark-navy px-2 py-0.5 rounded-full text-[10px] hover:bg-steel-gray hover:text-white transition-colors" title="{{ $file->nama_file }}">
                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path></svg>
                                                {{ Str::limit($file->nama_file, 15) }}
                                            </a>
                                        @endforeach
                                    </div>
                                @endif
                                <div class="text-[9px] text-steel-gray mt-1 text-right">
                                    Oleh: {{ $journal->author->name ?? 'Sistem' }}
                                </div>

                                <!-- Custom Delete Confirmation Modal -->
                                <div x-show="showDeleteJournal === {{ $journal->id }}" x-cloak class="absolute inset-0 bg-white/95 backdrop-blur-sm z-10 flex items-center justify-center rounded p-2">
                                    <div class="text-center">
                                        <p class="font-bold text-dark-navy text-xs mb-2">Hapus jurnal ini?</p>
                                        <div class="flex justify-center gap-2">
                                            <button @click="showDeleteJournal = null" class="skeuo-btn-secondary py-1 px-3 text-[10px]">Batal</button>
                                            <form action="{{ route('journals.destroy', $journal->id) }}" method="POST" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <input type="hidden" name="redirect_to" value="dashboard">
                                                <button type="submit" class="bg-gauge-red text-white py-1 px-3 text-[10px] rounded shadow-inner font-bold border border-dark-navy/20">Ya, Hapus</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                                <!-- Edit Journal Modal -->
                                <div x-show="showEditJournal === {{ $journal->id }}" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-dark-navy/60 backdrop-blur-sm px-4">
                                    <div @click.away="showEditJournal = null" class="bg-paper-cream w-full max-w-2xl rounded-lg shadow-2xl border border-steel-gray/30 overflow-hidden flex flex-col max-h-[90vh]">
                                        <div class="bg-leather-brown p-4 border-b border-steel-gray flex justify-between items-center text-paper-cream">
                                            <h3 class="font-display font-bold text-lg">Edit Jurnal</h3>
                                            <button @click="showEditJournal = null" class="hover:text-cyan-glow transition-colors">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                            </button>
                                        </div>
                                        <div class="p-6 overflow-y-auto">
                                            <form action="{{ route('journals.update', $journal->id) }}" method="POST" enctype="multipart/form-data" class="space-y-4 text-sm">
                                                @csrf
                                                @method('PUT')
                                                <input type="hidden" name="redirect_to" value="dashboard">
                                                
                                                <div>
                                                    <label class="block text-xs font-bold text-steel-gray uppercase tracking-wider mb-1">Judul Pekerjaan</label>
                                                    <input type="text" name="judul" value="{{ $journal->judul }}" required class="skeuo-input w-full py-2 px-3 font-medium">
                                                </div>
                                                
                                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                    <div>
                                                        <label class="block text-xs font-bold text-steel-gray uppercase tracking-wider mb-1">Tanggal</label>
                                                        <input type="date" name="tanggal" value="{{ $journal->tanggal ? \Carbon\Carbon::parse($journal->tanggal)->format('Y-m-d') : $journal->created_at->format('Y-m-d') }}" required class="skeuo-input w-full py-2 px-3">
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-bold text-steel-gray uppercase tracking-wider mb-1">Tipe</label>
                                                        <select name="tipe" class="skeuo-select w-full py-2 px-3" required>
                                                            <option value="update" {{ $journal->tipe == 'update' ? 'selected' : '' }}>Update Progress</option>
                                                            <option value="issue" {{ $journal->tipe == 'issue' ? 'selected' : '' }}>Issue/Kendala</option>
                                                            <option value="pencapaian" {{ $journal->tipe == 'pencapaian' ? 'selected' : '' }}>Pencapaian</option>
                                                            @if($journal->tipe == 'system')
                                                                <option value="system" selected>System Log</option>
                                                            @endif
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                    <div>
                                                        <label class="block text-xs font-bold text-steel-gray uppercase tracking-wider mb-1">Status Task</label>
                                                        <select name="status" class="skeuo-select w-full py-2 px-3" required>
                                                            <option value="in_progress" {{ $task->status == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                                            <option value="review" {{ $task->status == 'review' ? 'selected' : '' }}>Review</option>
                                                            <option value="blocked" {{ $task->status == 'blocked' ? 'selected' : '' }}>Blocked</option>
                                                            <option value="selesai" {{ $task->status == 'selesai' ? 'selected' : '' }}>Selesai</option>
                                                        </select>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-bold text-steel-gray uppercase tracking-wider mb-1">Progress (%)</label>
                                                        <div class="relative">
                                                            <input type="number" name="progress_pct" value="{{ $task->progress_pct ?? 0 }}" step="0.01" min="0" max="100" required class="skeuo-input w-full py-2 px-3 pr-8">
                                                            <span class="absolute right-3 top-2 text-steel-gray pointer-events-none">%</span>
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                <div>
                                                    <label class="block text-xs font-bold text-steel-gray uppercase tracking-wider mb-1">Detail Jurnal</label>
                                                    <textarea name="detail" rows="4" class="skeuo-input w-full py-2 px-3 leading-relaxed" required placeholder="Tuliskan catatan progress, issue, dll...">{{ strip_tags($journal->detail) }}</textarea>
                                                </div>

                                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                    <div x-data="{ links: {{ empty($journal->tautan) ? '[{url: \'\'}]' : collect($journal->tautan)->map(function($url){ return ['url' => $url]; })->toJson() }} }">
                                                        <label class="block text-xs font-bold text-steel-gray uppercase tracking-wider mb-1">Tautan / Link</label>
                                                        <template x-for="(link, index) in links" :key="index">
                                                            <div class="flex items-center mb-2">
                                                                <input type="url" name="tautan[]" x-model="link.url" placeholder="https://..." class="skeuo-input w-full py-1 px-2 text-sm">
                                                                <button type="button" @click="links.splice(index, 1)" x-show="links.length > 1" class="ml-2 text-gauge-red hover:text-red-700">
                                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                                                </button>
                                                            </div>
                                                        </template>
                                                        <button type="button" @click="links.push({url: ''})" class="text-xs text-tech-blue hover:underline">+ Tambah Tautan</button>
                                                    </div>
                                                    
                                                    <div>
                                                        <label class="block text-xs font-bold text-steel-gray uppercase tracking-wider mb-1">Lampiran File (Bisa pilih lebih dari 1)</label>
                                                        <input type="file" name="lampiran[]" multiple class="skeuo-input w-full py-1 px-2 text-sm bg-white" accept="image/*,.pdf,.doc,.docx,.xls,.xlsx">
                                                        <p class="text-[10px] text-steel-gray mt-1">Tekan CTRL/CMD saat memilih file untuk melampirkan lebih dari satu.</p>
                                                    </div>
                                                </div>
                                                
                                                <div class="pt-4 flex justify-end gap-2 border-t border-steel-gray/20">
                                                    <button type="button" @click="showEditJournal = false" class="skeuo-btn-secondary py-2 px-4 uppercase tracking-wider font-bold text-[10px]">Batal</button>
                                                    <button type="submit" class="skeuo-btn py-2 px-6 uppercase tracking-wider font-bold text-[10px]">Simpan Perubahan</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                                <!-- Move Journal Modal -->
                                <div x-show="showMoveJournal === {{ $journal->id }}" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-dark-navy/60 backdrop-blur-sm px-4">
                                    <div @click.away="showMoveJournal = null" class="bg-paper-cream w-full max-w-lg rounded-lg shadow-2xl border border-steel-gray/30 overflow-hidden flex flex-col">
                                        <div class="bg-leather-brown p-4 border-b border-steel-gray flex justify-between items-center text-paper-cream">
                                            <h3 class="font-display font-bold text-lg">Pindahkan Jurnal</h3>
                                            <button @click="showMoveJournal = null" class="hover:text-cyan-glow transition-colors">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                            </button>
                                        </div>
                                        <div class="p-6">
                                            <div class="mb-4 text-xs text-gauge-orange bg-gauge-amber/10 p-3 rounded border border-gauge-amber/30">
                                                <strong class="block mb-1">Perhatian!</strong>
                                                Memindahkan jurnal ini ke task lain <strong>TIDAK</strong> akan mengubah persentase Progress pada task asal. Harap ubah progress secara manual (via Edit Task) jika diperlukan.
                                            </div>
                                            <form action="{{ route('journals.move', $journal->id) }}" method="POST" class="space-y-4 text-sm">
                                                @csrf
                                                <div>
                                                    <label class="block text-xs font-bold text-steel-gray uppercase tracking-wider mb-1">Pilih Task Tujuan</label>
                                                    <select name="target_task_id" required class="skeuo-select w-full py-2 px-3">
                                                        <option value="">Pilih Task...</option>
                                                        @foreach($projects ?? [] as $p)
                                                            @foreach($p->phases ?? [] as $ph)
                                                                <optgroup label="{{ $p->nama_proyek }} - {{ $ph->nama_fase }}">
                                                                    @foreach($ph->tasks ?? [] as $t)
                                                                        <option value="{{ $t->id }}" {{ $t->id == $task->id ? 'disabled' : '' }}>
                                                                            {{ $t->nama_task }} {{ $t->id == $task->id ? '(Saat Ini)' : '' }}
                                                                        </option>
                                                                    @endforeach
                                                                </optgroup>
                                                            @endforeach
                                                        @endforeach
                                                    </select>
                                                </div>
                                                
                                                <div class="mt-4 border border-steel-gray/30 rounded p-4 bg-white/50 shadow-inner">
                                                    <label class="flex items-center space-x-2 cursor-pointer">
                                                        <input type="checkbox" name="sync_progress" value="1" class="rounded border-steel-gray text-tech-blue focus:ring-tech-blue w-4 h-4">
                                                        <span class="text-xs font-bold text-dark-navy">Samakan Progress & Status Task Tujuan dengan asal jurnal?</span>
                                                    </label>
                                                </div>
                                                
                                                <div class="pt-4 flex justify-end gap-2">
                                                    <button type="button" @click="showMoveJournal = null" class="skeuo-btn-secondary py-2 px-4 uppercase tracking-wider font-bold text-[10px]">Batal</button>
                                                    <button type="submit" class="skeuo-btn py-2 px-6 uppercase tracking-wider font-bold text-[10px]">Pindahkan</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                                <!-- Copy Journal Modal -->
                                <div x-show="showCopyJournal === {{ $journal->id }}" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-dark-navy/60 backdrop-blur-sm px-4">
                                    <div @click.away="showCopyJournal = null" class="bg-paper-cream w-full max-w-lg rounded-lg shadow-2xl border border-steel-gray/30 overflow-hidden flex flex-col">
                                        <div class="bg-leather-brown p-4 border-b border-steel-gray flex justify-between items-center text-paper-cream">
                                            <h3 class="font-display font-bold text-lg">Salin Jurnal</h3>
                                            <button @click="showCopyJournal = null" class="hover:text-cyan-glow transition-colors">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                            </button>
                                        </div>
                                        <div class="p-6">
                                            <div class="mb-4 text-xs text-tech-blue bg-ice-blue p-3 rounded border border-tech-blue/30">
                                                <strong class="block mb-1">Informasi:</strong>
                                                Menyalin jurnal akan membuat duplikat dari jurnal ini beserta semua lampirannya ke Task tujuan.
                                            </div>
                                            <form action="{{ route('journals.copy', $journal->id) }}" method="POST" class="space-y-4 text-sm">
                                                @csrf
                                                <div>
                                                    <label class="block text-xs font-bold text-steel-gray uppercase tracking-wider mb-1">Pilih Task Tujuan</label>
                                                    <select name="target_task_id" required class="skeuo-select w-full py-2 px-3">
                                                        <option value="">Pilih Task...</option>
                                                        @foreach($projects ?? [] as $p)
                                                            @foreach($p->phases ?? [] as $ph)
                                                                <optgroup label="{{ $p->nama_proyek }} - {{ $ph->nama_fase }}">
                                                                    @foreach($ph->tasks ?? [] as $t)
                                                                        <option value="{{ $t->id }}">
                                                                            {{ $t->nama_task }} {{ $t->id == $task->id ? '(Saat Ini)' : '' }}
                                                                        </option>
                                                                    @endforeach
                                                                </optgroup>
                                                            @endforeach
                                                        @endforeach
                                                    </select>
                                                </div>
                                                
                                                <div class="mt-4 border border-steel-gray/30 rounded p-4 bg-white/50 shadow-inner">
                                                    <label class="flex items-center space-x-2 cursor-pointer">
                                                        <input type="checkbox" name="sync_progress" value="1" class="rounded border-steel-gray text-tech-blue focus:ring-tech-blue w-4 h-4">
                                                        <span class="text-xs font-bold text-dark-navy">Samakan Progress & Status Task Tujuan dengan asal jurnal?</span>
                                                    </label>
                                                </div>
                                                
                                                <div class="pt-4 flex justify-end gap-2">
                                                    <button type="button" @click="showCopyJournal = null" class="skeuo-btn-secondary py-2 px-4 uppercase tracking-wider font-bold text-[10px]">Batal</button>
                                                    <button type="submit" class="skeuo-btn py-2 px-6 uppercase tracking-wider font-bold text-[10px]">Salin Jurnal</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </td>
            </tr>
        @endif
    @endforeach
@endforeach
