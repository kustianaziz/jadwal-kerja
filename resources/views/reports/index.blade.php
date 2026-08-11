@extends('layouts.main')

@section('content')
    <div class="mb-8 flex justify-between items-end">
        <div>
            <h2 class="text-3xl font-display font-bold text-dark-navy">Laporan Progress Terperinci</h2>
            <p class="text-steel-gray mt-1">Laporan menyeluruh dari level Grup, Proyek, Fase, hingga Task</p>
        </div>
        <button onclick="window.print()" class="skeuo-btn py-2 px-4 flex items-center">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
            Cetak Laporan
        </button>
    </div>

    <div class="space-y-8">
        @foreach($groups as $group)
            @php
                $groupProgress = 0;
                if($group->projects->count() > 0) {
                    $groupProgress = $group->projects->sum(function($p) {
                        return ($p->progress_pct / 100) * ($p->bobot_pct ?? 100);
                    });
                }
            @endphp
            <!-- GROUP LEVEL -->
            <div class="skeuo-card p-6 border-l-4 border-l-tech-blue bg-ice-blue/10">
                <div class="flex flex-col md:flex-row justify-between md:items-center border-b border-tech-blue pb-3 mb-4">
                    <div>
                        <span class="text-xs font-bold text-tech-blue uppercase tracking-wider">Grup Proyek</span>
                        <h3 class="text-2xl font-display font-bold text-dark-navy">{{ $group->nama_grup }}</h3>
                    </div>
                    <div class="mt-2 md:mt-0 md:text-right">
                        <div class="text-sm font-bold text-dark-navy tabular-nums mb-1">Progress: {{ number_format($groupProgress, 1) }}%</div>
                        <div class="text-xs text-steel-gray">Target: {{ $group->target_selesai ? \Carbon\Carbon::parse($group->target_selesai)->format('d M Y') : '-' }}</div>
                    </div>
                </div>

                <!-- PROJECTS IN GROUP -->
                <div class="pl-4 md:pl-8 space-y-6">
                    @forelse($group->projects as $project)
                        <div class="border border-steel-gray/30 rounded-lg bg-white overflow-hidden shadow-sm">
                            <!-- Project Header -->
                            <div class="bg-ice-blue/30 p-4 border-b border-steel-gray/30 flex flex-col md:flex-row justify-between gap-4">
                                <div>
                                    <span class="text-[10px] font-bold text-steel-gray uppercase tracking-wider">Proyek</span>
                                    <h4 class="text-lg font-display font-bold text-dark-navy">
                                        <a href="{{ route('projects.show', $project->id) }}" class="hover:text-cyan-glow transition-colors">{{ $project->nama_proyek }}</a>
                                    </h4>
                                    <div class="text-xs text-steel-gray mt-1 flex gap-3">
                                        <span>PM: <strong class="text-dark-navy">{{ $project->pm->name ?? '-' }}</strong></span>
                                        <span>Mulai: <strong>{{ $project->tanggal_mulai ? \Carbon\Carbon::parse($project->tanggal_mulai)->format('d M Y') : '-' }}</strong></span>
                                        <span>Target: <strong>{{ $project->target_selesai ? \Carbon\Carbon::parse($project->target_selesai)->format('d M Y') : '-' }}</strong></span>
                                    </div>
                                </div>
                                <div class="md:text-right">
                                    <div class="text-xl font-display font-bold text-dark-navy tabular-nums">{{ number_format($project->progress_pct, 1) }}%</div>
                                    <div class="text-[10px] text-steel-gray">Bobot: {{ $project->bobot_pct ?? 100 }}%</div>
                                    @php
                                        $statusClass = 'stamp-healthy';
                                        if($project->health_status == 'attention') $statusClass = 'stamp-attention';
                                        if($project->health_status == 'at_risk') $statusClass = 'stamp-at-risk';
                                        if($project->health_status == 'critical') $statusClass = 'stamp-critical';
                                    @endphp
                                    <span class="{{ $statusClass }} text-[10px] py-1 px-2 mt-1 inline-block">
                                        {{ strtoupper(str_replace('_', ' ', $project->health_status)) }}
                                    </span>
                                </div>
                            </div>

                            <!-- PHASES IN PROJECT -->
                            <div class="p-4 space-y-4">
                                @forelse($project->phases as $phase)
                                    <div class="border-l-2 border-cyan-glow pl-4">
                                        <!-- Phase Header -->
                                        <div class="flex flex-col md:flex-row justify-between mb-2 pb-2 border-b border-dashed border-steel-gray/30">
                                            <div>
                                                <span class="text-[10px] font-bold text-cyan-glow uppercase tracking-wider">Fase</span>
                                                <h5 class="text-md font-bold text-dark-navy">{{ $phase->nama_fase }}</h5>
                                                <div class="text-xs text-steel-gray flex gap-3 mt-1">
                                                    <span>PIC: <strong class="text-dark-navy">{{ $phase->pic->nama ?? '-' }}</strong></span>
                                                    <span>Mulai: <strong>{{ $phase->tanggal_mulai ? \Carbon\Carbon::parse($phase->tanggal_mulai)->format('d M Y') : '-' }}</strong></span>
                                                    <span>Target: <strong>{{ $phase->tanggal_target ? \Carbon\Carbon::parse($phase->tanggal_target)->format('d M Y') : '-' }}</strong></span>
                                                </div>
                                            </div>
                                            <div class="md:text-right mt-1 md:mt-0">
                                                <span class="text-sm font-bold text-dark-navy tabular-nums">{{ number_format($phase->progress ?? 0, 1) }}%</span>
                                                <div class="text-[10px] text-steel-gray">Bobot: {{ $phase->bobot_pct }}%</div>
                                            </div>
                                        </div>

                                        <!-- TASKS IN PHASE -->
                                        <div class="pl-2 space-y-2 mt-2">
                                            @forelse($phase->tasks as $task)
                                                <div class="flex flex-col sm:flex-row sm:items-center justify-between bg-ice-blue/10 p-2 rounded border border-steel-gray/20 hover:bg-ice-blue/30 transition-colors">
                                                    <div class="flex-1">
                                                        <span class="text-[10px] font-bold text-steel-gray uppercase tracking-wider mr-2">Task</span>
                                                        <span class="text-sm font-medium text-dark-navy">{{ $task->nama_task }}</span>
                                                        <div class="text-[10px] text-steel-gray flex gap-3 mt-1">
                                                            <span>PIC: <strong class="text-dark-navy">{{ $task->pics->first()->nama ?? '-' }}</strong></span>
                                                            <span>Mulai: <strong>{{ $task->tanggal_mulai ? \Carbon\Carbon::parse($task->tanggal_mulai)->format('d M y') : '-' }}</strong></span>
                                                            <span>Deadline: <strong>{{ $task->deadline ? \Carbon\Carbon::parse($task->deadline)->format('d M y') : '-' }}</strong></span>
                                                        </div>
                                                    </div>
                                                    <div class="sm:text-right mt-1 sm:mt-0 flex flex-row sm:flex-col items-center sm:items-end justify-between">
                                                        <span class="text-sm font-bold text-dark-navy tabular-nums">{{ number_format($task->progress_pct ?? 0, 1) }}%</span>
                                                        <span class="text-[10px] text-steel-gray ml-2 sm:ml-0">Bobot: {{ $task->bobot_pct }}%</span>
                                                    </div>
                                                </div>
                                                @if($task->journals && $task->journals->count() > 0)
                                                    <div class="ml-4 mt-1 mb-3 space-y-1">
                                                        @foreach($task->journals->where('tipe', '!=', 'system') as $journal)
                                                            <div class="bg-white p-2 rounded border-l-2 border-gauge-amber text-xs shadow-sm">
                                                                <div class="flex justify-between items-start">
                                                                    <strong class="text-dark-navy">{{ $journal->judul }}</strong>
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
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                @endif
                                            @empty
                                                <div class="text-xs text-steel-gray italic p-1">Belum ada task di fase ini.</div>
                                            @endforelse
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-sm text-steel-gray italic">Belum ada fase di proyek ini.</div>
                                @endforelse
                            </div>
                        </div>
                    @empty
                        <div class="text-sm text-steel-gray italic p-2">Belum ada proyek di grup ini.</div>
                    @endforelse
                </div>
            </div>
        @endforeach

        <!-- UNGROUPED PROJECTS -->
        @if($ungroupedProjects->count() > 0)
            <div class="skeuo-card p-6 border-l-4 border-l-steel-gray bg-ice-blue/10 mt-8">
                <div class="border-b border-steel-gray pb-3 mb-4">
                    <span class="text-xs font-bold text-steel-gray uppercase tracking-wider">Proyek Tanpa Grup</span>
                </div>
                
                <div class="pl-0 md:pl-4 space-y-6">
                    @foreach($ungroupedProjects as $project)
                        <div class="border border-steel-gray/30 rounded-lg bg-white overflow-hidden shadow-sm">
                            <!-- Project Header -->
                            <div class="bg-ice-blue/30 p-4 border-b border-steel-gray/30 flex flex-col md:flex-row justify-between gap-4">
                                <div>
                                    <span class="text-[10px] font-bold text-steel-gray uppercase tracking-wider">Proyek</span>
                                    <h4 class="text-lg font-display font-bold text-dark-navy">
                                        <a href="{{ route('projects.show', $project->id) }}" class="hover:text-cyan-glow transition-colors">{{ $project->nama_proyek }}</a>
                                    </h4>
                                    <div class="text-xs text-steel-gray mt-1 flex gap-3">
                                        <span>PM: <strong class="text-dark-navy">{{ $project->pm->name ?? '-' }}</strong></span>
                                        <span>Mulai: <strong>{{ $project->tanggal_mulai ? \Carbon\Carbon::parse($project->tanggal_mulai)->format('d M Y') : '-' }}</strong></span>
                                        <span>Target: <strong>{{ $project->target_selesai ? \Carbon\Carbon::parse($project->target_selesai)->format('d M Y') : '-' }}</strong></span>
                                    </div>
                                </div>
                                <div class="md:text-right">
                                    <div class="text-xl font-display font-bold text-dark-navy tabular-nums">{{ number_format($project->progress_pct, 1) }}%</div>
                                    <div class="text-[10px] text-steel-gray">Bobot: {{ $project->bobot_pct ?? 100 }}%</div>
                                    @php
                                        $statusClass = 'stamp-healthy';
                                        if($project->health_status == 'attention') $statusClass = 'stamp-attention';
                                        if($project->health_status == 'at_risk') $statusClass = 'stamp-at-risk';
                                        if($project->health_status == 'critical') $statusClass = 'stamp-critical';
                                    @endphp
                                    <span class="{{ $statusClass }} text-[10px] py-1 px-2 mt-1 inline-block">
                                        {{ strtoupper(str_replace('_', ' ', $project->health_status)) }}
                                    </span>
                                </div>
                            </div>

                            <!-- PHASES IN PROJECT -->
                            <div class="p-4 space-y-4">
                                @forelse($project->phases as $phase)
                                    <div class="border-l-2 border-cyan-glow pl-4">
                                        <!-- Phase Header -->
                                        <div class="flex flex-col md:flex-row justify-between mb-2 pb-2 border-b border-dashed border-steel-gray/30">
                                            <div>
                                                <span class="text-[10px] font-bold text-cyan-glow uppercase tracking-wider">Fase</span>
                                                <h5 class="text-md font-bold text-dark-navy">{{ $phase->nama_fase }}</h5>
                                                <div class="text-xs text-steel-gray flex gap-3 mt-1">
                                                    <span>PIC: <strong class="text-dark-navy">{{ $phase->pic->nama ?? '-' }}</strong></span>
                                                    <span>Mulai: <strong>{{ $phase->tanggal_mulai ? \Carbon\Carbon::parse($phase->tanggal_mulai)->format('d M Y') : '-' }}</strong></span>
                                                    <span>Target: <strong>{{ $phase->tanggal_target ? \Carbon\Carbon::parse($phase->tanggal_target)->format('d M Y') : '-' }}</strong></span>
                                                </div>
                                            </div>
                                            <div class="md:text-right mt-1 md:mt-0">
                                                <span class="text-sm font-bold text-dark-navy tabular-nums">{{ number_format($phase->progress ?? 0, 1) }}%</span>
                                                <div class="text-[10px] text-steel-gray">Bobot: {{ $phase->bobot_pct }}%</div>
                                            </div>
                                        </div>

                                        <!-- TASKS IN PHASE -->
                                        <div class="pl-2 space-y-2 mt-2">
                                            @forelse($phase->tasks as $task)
                                                <div class="flex flex-col sm:flex-row sm:items-center justify-between bg-ice-blue/10 p-2 rounded border border-steel-gray/20 hover:bg-ice-blue/30 transition-colors">
                                                    <div class="flex-1">
                                                        <span class="text-[10px] font-bold text-steel-gray uppercase tracking-wider mr-2">Task</span>
                                                        <span class="text-sm font-medium text-dark-navy">{{ $task->nama_task }}</span>
                                                        <div class="text-[10px] text-steel-gray flex gap-3 mt-1">
                                                            <span>PIC: <strong class="text-dark-navy">{{ $task->pics->first()->nama ?? '-' }}</strong></span>
                                                            <span>Mulai: <strong>{{ $task->tanggal_mulai ? \Carbon\Carbon::parse($task->tanggal_mulai)->format('d M y') : '-' }}</strong></span>
                                                            <span>Deadline: <strong>{{ $task->deadline ? \Carbon\Carbon::parse($task->deadline)->format('d M y') : '-' }}</strong></span>
                                                        </div>
                                                    </div>
                                                    <div class="sm:text-right mt-1 sm:mt-0 flex flex-row sm:flex-col items-center sm:items-end justify-between">
                                                        <span class="text-sm font-bold text-dark-navy tabular-nums">{{ number_format($task->progress_pct ?? 0, 1) }}%</span>
                                                        <span class="text-[10px] text-steel-gray ml-2 sm:ml-0">Bobot: {{ $task->bobot_pct }}%</span>
                                                    </div>
                                                </div>
                                                @if($task->journals && $task->journals->count() > 0)
                                                    <div class="ml-4 mt-1 mb-3 space-y-1">
                                                        @foreach($task->journals->where('tipe', '!=', 'system') as $journal)
                                                            <div class="bg-white p-2 rounded border-l-2 border-gauge-amber text-xs shadow-sm">
                                                                <div class="flex justify-between items-start">
                                                                    <strong class="text-dark-navy">{{ $journal->judul }}</strong>
                                                                    <span class="text-[9px] text-steel-gray">{{ $journal->tanggal ? \Carbon\Carbon::parse($journal->tanggal)->format('d M Y') : $journal->created_at->format('d M H:i') }}</span>
                                                                </div>
                                                                <div class="text-dark-navy/80 mt-1 prose prose-sm prose-p:my-0 prose-ul:my-0 leading-tight">
                                                                    {!! $journal->detail !!}
                                                                </div>
                                                                <div class="text-[9px] text-steel-gray mt-1 text-right">
                                                                    Oleh: {{ $journal->author->name ?? 'Sistem' }}
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                @endif
                                            @empty
                                                <div class="text-xs text-steel-gray italic p-1">Belum ada task di fase ini.</div>
                                            @endforelse
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-sm text-steel-gray italic">Belum ada fase di proyek ini.</div>
                                @endforelse
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
        
        @if($groups->count() == 0 && $ungroupedProjects->count() == 0)
            <div class="skeuo-card p-12 text-center">
                <p class="text-lg text-steel-gray">Belum ada data proyek dan grup yang bisa dilaporkan.</p>
            </div>
        @endif
    </div>
@endsection
