@extends('layouts.main')

@section('content')
    <div class="mb-8 flex flex-col md:flex-row md:justify-between md:items-center gap-4">
        <div>
            <h2 class="text-3xl font-display font-bold text-dark-navy">Overview Proyek</h2>
            <p class="text-steel-gray mt-1">Ringkasan status proyek aktif</p>
        </div>
        <div class="flex flex-col sm:flex-row gap-3">
            <a href="{{ route('projects.global_gantt') }}" class="skeuo-btn-secondary flex items-center justify-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                Roadmap Proyek
            </a>
            <a href="{{ route('projects.create') }}" class="skeuo-btn flex items-center justify-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Proyek Baru
            </a>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="skeuo-card p-6 flex items-center">
            <div class="w-16 h-16 rounded-full bg-ice-blue border-2 border-steel-gray flex items-center justify-center shadow-inner mr-6">
                <svg class="w-8 h-8 text-tech-blue" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
            </div>
            <div>
                <p class="text-sm font-medium text-steel-gray uppercase tracking-wider">Total Aktif</p>
                <p class="text-4xl font-display font-bold text-dark-navy tabular-nums" style="text-shadow: inset 0 2px 4px rgba(0,0,0,0.1);">{{ $totalAktif ?? 0 }}</p>
            </div>
        </div>

        <div class="skeuo-card p-6 col-span-1 md:col-span-2">
            <div class="flex justify-between items-center mb-4">
                <p class="text-sm font-medium text-steel-gray uppercase tracking-wider">Overall Health Score</p>
                <span class="stamp-healthy">Healthy</span>
            </div>
            
            <div class="flex items-center">
                <div class="text-5xl font-display font-bold text-dark-navy mr-6 tabular-nums">{{ number_format($healthScoreAverage, 0) }}<span class="text-2xl text-steel-gray">%</span></div>
                
                <div class="flex-1">
                    <div class="gauge-container h-8">
                        <div class="gauge-bar bg-gauge-green" style="width: {{ $healthScoreAverage }}%"></div>
                        <div class="gauge-ticks"></div>
                    </div>
                    <div class="flex justify-between mt-2 text-xs text-steel-gray font-medium">
                        <span>0</span>
                        <span>25</span>
                        <span>50</span>
                        <span>75</span>
                        <span>100</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Projects Table Card -->
    <div class="skeuo-card overflow-visible" x-data="{
        search: '',
        expandedGroups: JSON.parse(localStorage.getItem('jadwal_groups')) || [],
        expandedProjects: JSON.parse(localStorage.getItem('jadwal_projects')) || [],
        expandedPhases: JSON.parse(localStorage.getItem('jadwal_phases')) || [],
        
        init() {
            this.$watch('expandedGroups', val => localStorage.setItem('jadwal_groups', JSON.stringify(val)));
            this.$watch('expandedProjects', val => localStorage.setItem('jadwal_projects', JSON.stringify(val)));
            this.$watch('expandedPhases', val => localStorage.setItem('jadwal_phases', JSON.stringify(val)));

            @if(session('expanded_group'))
                if (!this.expandedGroups.includes('{{ session('expanded_group') }}')) {
                    this.expandedGroups.push('{{ session('expanded_group') }}');
                }
            @endif
            @if(session('expanded_project'))
                if (!this.expandedProjects.includes('{{ session('expanded_project') }}')) {
                    this.expandedProjects.push('{{ session('expanded_project') }}');
                }
            @endif
            @if(session('expanded_phase'))
                if (!this.expandedPhases.includes('{{ session('expanded_phase') }}')) {
                    this.expandedPhases.push('{{ session('expanded_phase') }}');
                }
            @endif
        },
        showAddPhase: null, 
        showAddTask: null,
        showCopyPhase: null,
        showAddJournal: null,
        showMoveJournal: null,
        showCopyJournal: null,
        showEditJournal: null,
        showDeleteJournal: null,
        selectedJournals: [],
        showBulkMove: false,
        showBulkCopy: false,
        moveTask(taskId, targetPhaseId) {
            if(!taskId || !targetPhaseId) return;
            let url = '{{ route('tasks.move', ['task' => '__ID__']) }}'.replace('__ID__', taskId);
            fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ target_phase_id: targetPhaseId })
            }).then(res => {
                if(res.ok) window.location.reload();
                else alert('Gagal memindahkan task');
            });
        }
    }">
        <!-- Card Tab Header -->
        <div class="bg-tech-blue p-4 border-b border-steel-gray flex flex-col md:flex-row justify-between md:items-center relative gap-3">
            <div class="absolute -top-3 left-4 bg-ice-blue border border-steel-gray px-4 py-1 rounded-t-md shadow-[0_-2px_4px_rgba(0,0,0,0.05)] z-0 hidden md:block">
                <span class="text-xs font-bold text-dark-navy uppercase tracking-wider">Daftar Proyek</span>
            </div>
            <h3 class="text-lg font-display font-bold text-ice-blue z-10 mt-1">Status Proyek Saat Ini</h3>
            
            <div class="z-10 relative flex items-center w-full md:w-auto">
                <input type="text" x-model="search" placeholder="Cari..." class="skeuo-input py-1 px-3 text-sm w-full md:w-48">
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-ice-blue/50 border-b border-steel-gray">
                        <th class="p-3 text-xs font-bold text-dark-navy uppercase tracking-wider w-8"></th>
                        <th class="p-3 text-xs font-bold text-dark-navy uppercase tracking-wider">Nama / WBS</th>
                        <th class="p-3 text-xs font-bold text-dark-navy uppercase tracking-wider whitespace-nowrap">PIC</th>
                        <th class="p-3 text-xs font-bold text-dark-navy uppercase tracking-wider whitespace-nowrap">Start</th>
                        <th class="p-3 text-xs font-bold text-dark-navy uppercase tracking-wider whitespace-nowrap">Finish</th>
                        <th class="p-3 text-xs font-bold text-dark-navy uppercase tracking-wider w-48">Progress</th>
                        <th class="p-3 text-xs font-bold text-dark-navy uppercase tracking-wider text-right whitespace-nowrap">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-steel-gray">
                    @foreach($groups ?? [] as $group)
                        <!-- Group Row -->
                        <tr class="bg-ice-blue/50 font-bold border-b-2 border-steel-gray cursor-pointer" @click="expandedGroups.includes('{{ $group->id }}') ? expandedGroups = expandedGroups.filter(id => id != '{{ $group->id }}') : expandedGroups.push('{{ $group->id }}')">
                            <td class="p-3 text-center w-12">
                                <button class="text-steel-gray hover:text-tech-blue focus:outline-none bg-white p-1 rounded border border-steel-gray shadow-inner">
                                    <svg x-show="!expandedGroups.includes('{{ $group->id }}')" class="w-4 h-4 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                                    <svg x-show="expandedGroups.includes('{{ $group->id }}')" class="w-4 h-4 transition-transform rotate-90" x-cloak fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                                </button>
                            </td>
                            <td colspan="4" class="p-3 text-dark-navy text-sm font-bold tracking-wide">
                                <div class="flex items-center">
                                    <svg class="w-5 h-5 mr-3 text-tech-blue" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                                    GRUP PROYEK: <span class="ml-2 font-display text-base">{{ mb_strtoupper($group->nama_grup) }}</span>
                                </div>
                            </td>
                            <td colspan="2" class="p-3">
                                @php
                                    $groupProgress = $group->projects->avg('progress_pct') ?? 0;
                                @endphp
                                <div class="flex items-center">
                                    <span class="text-xs font-bold w-12 text-right mr-2">{{ number_format($groupProgress, 1) }}%</span>
                                    <div class="flex-1 bg-steel-gray/20 h-2 rounded-full overflow-hidden shadow-inner border border-steel-gray/30">
                                        <div class="bg-tech-blue h-full rounded-full transition-all duration-500" style="width: {{ $groupProgress }}%; background: linear-gradient(90deg, #28A8EA 0%, #38BDF8 100%);"></div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @foreach($group->projects ?? [] as $project)
                            @include('partials.project-row', ['project' => $project, 'groupId' => $group->id])
                        @endforeach
                    @endforeach

                    @if(isset($ungroupedProjects) && $ungroupedProjects->count() > 0)
                        <!-- Ungrouped Row -->
                        <tr class="bg-ice-blue/50 font-bold border-b-2 border-steel-gray cursor-pointer" @click="expandedGroups.includes('ungrouped') ? expandedGroups = expandedGroups.filter(id => id != 'ungrouped') : expandedGroups.push('ungrouped')">
                            <td class="p-3 text-center w-12">
                                <button class="text-steel-gray hover:text-tech-blue focus:outline-none bg-white p-1 rounded border border-steel-gray shadow-inner">
                                    <svg x-show="!expandedGroups.includes('ungrouped')" class="w-4 h-4 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                                    <svg x-show="expandedGroups.includes('ungrouped')" class="w-4 h-4 transition-transform rotate-90" x-cloak fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                                </button>
                            </td>
                            <td colspan="4" class="p-3 text-dark-navy text-sm font-bold tracking-wide">
                                <div class="flex items-center">
                                    <svg class="w-5 h-5 mr-3 text-steel-gray" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path></svg>
                                    PROYEK TANPA GRUP
                                </div>
                            </td>
                            <td colspan="2" class="p-3">
                                @php
                                    $ungroupedProgress = $ungroupedProjects->avg('progress_pct') ?? 0;
                                @endphp
                                <div class="flex items-center">
                                    <span class="text-xs font-bold w-12 text-right mr-2 text-steel-gray">{{ number_format($ungroupedProgress, 1) }}%</span>
                                    <div class="flex-1 bg-steel-gray/20 h-2 rounded-full overflow-hidden shadow-inner border border-steel-gray/30">
                                        <div class="bg-steel-gray h-full rounded-full transition-all duration-500" style="width: {{ $ungroupedProgress }}%;"></div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @foreach($ungroupedProjects as $project)
                            @include('partials.project-row', ['project' => $project, 'groupId' => 'ungrouped'])
                        @endforeach
                    @endif
                    
                    @if((!isset($groups) || $groups->count() == 0) && (!isset($ungroupedProjects) || $ungroupedProjects->count() == 0))
                        <tr>
                            <td colspan="7" class="p-8 text-center text-steel-gray font-medium">Belum ada proyek yang berjalan.</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>

        <!-- Bulk Move Modal -->
        <div x-show="showBulkMove" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-dark-navy/60 backdrop-blur-sm px-4">
            <div @click.away="showBulkMove = false" class="bg-paper-cream w-full max-w-lg rounded-lg shadow-2xl border border-steel-gray/30 overflow-hidden flex flex-col">
                <div class="bg-leather-brown p-4 border-b border-steel-gray flex justify-between items-center text-paper-cream">
                    <h3 class="font-display font-bold text-lg">Pindahkan Banyak Jurnal</h3>
                    <button @click="showBulkMove = false" class="hover:text-cyan-glow transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
                <div class="p-6">
                    <div class="mb-4 text-xs text-gauge-orange bg-gauge-amber/10 p-3 rounded border border-gauge-amber/30">
                        <strong class="block mb-1">Perhatian!</strong>
                        Memindahkan jurnal beserta lampirannya <strong>TIDAK</strong> akan me-reset persentase Progress pada task asal. Harap ubah progress secara manual (via Edit Task) jika diperlukan.
                    </div>
                    <form action="{{ route('journals.bulkMove') }}" method="POST" class="space-y-4 text-sm">
                        @csrf
                        <!-- Hidden inputs for all selected journals -->
                        <template x-for="journalId in selectedJournals" :key="journalId">
                            <input type="hidden" name="journal_ids[]" :value="journalId">
                        </template>
                        
                        <div>
                            <label class="block text-xs font-bold text-steel-gray uppercase tracking-wider mb-1">Pilih Task Tujuan</label>
                            <select name="target_task_id" required class="skeuo-select w-full py-2 px-3">
                                <option value="">Pilih Task...</option>
                                @foreach($projects ?? [] as $p)
                                    @foreach($p->phases ?? [] as $ph)
                                        <optgroup label="{{ $p->nama_proyek }} - {{ $ph->nama_fase }}">
                                            @foreach($ph->tasks ?? [] as $t)
                                                <option value="{{ $t->id }}">{{ $t->nama_task }}</option>
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
                            <button type="button" @click="showBulkMove = false" class="skeuo-btn-secondary py-2 px-4 uppercase tracking-wider font-bold text-[10px]">Batal</button>
                            <button type="submit" class="skeuo-btn py-2 px-6 uppercase tracking-wider font-bold text-[10px]">Pindahkan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Bulk Copy Modal -->
        <div x-show="showBulkCopy" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-dark-navy/60 backdrop-blur-sm px-4">
            <div @click.away="showBulkCopy = false" class="bg-paper-cream w-full max-w-lg rounded-lg shadow-2xl border border-steel-gray/30 overflow-hidden flex flex-col">
                <div class="bg-leather-brown p-4 border-b border-steel-gray flex justify-between items-center text-paper-cream">
                    <h3 class="font-display font-bold text-lg">Salin Banyak Jurnal</h3>
                    <button @click="showBulkCopy = false" class="hover:text-cyan-glow transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
                <div class="p-6">
                    <div class="mb-4 text-xs text-tech-blue bg-ice-blue p-3 rounded border border-tech-blue/30">
                        <strong class="block mb-1">Informasi:</strong>
                        Menyalin jurnal akan membuat duplikat dari jurnal-jurnal ini beserta semua lampirannya ke Task tujuan.
                    </div>
                    <form action="{{ route('journals.bulkCopy') }}" method="POST" class="space-y-4 text-sm">
                        @csrf
                        <!-- Hidden inputs for all selected journals -->
                        <template x-for="journalId in selectedJournals" :key="'copy_'+journalId">
                            <input type="hidden" name="journal_ids[]" :value="journalId">
                        </template>
                        
                        <div>
                            <label class="block text-xs font-bold text-steel-gray uppercase tracking-wider mb-1">Pilih Task Tujuan</label>
                            <select name="target_task_id" required class="skeuo-select w-full py-2 px-3">
                                <option value="">Pilih Task...</option>
                                @foreach($projects ?? [] as $p)
                                    @foreach($p->phases ?? [] as $ph)
                                        <optgroup label="{{ $p->nama_proyek }} - {{ $ph->nama_fase }}">
                                            @foreach($ph->tasks ?? [] as $t)
                                                <option value="{{ $t->id }}">{{ $t->nama_task }}</option>
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
                            <button type="button" @click="showBulkCopy = false" class="skeuo-btn-secondary py-2 px-4 uppercase tracking-wider font-bold text-[10px]">Batal</button>
                            <button type="submit" class="skeuo-btn py-2 px-6 uppercase tracking-wider font-bold text-[10px]">Salin Jurnal</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    </div>
@endsection
