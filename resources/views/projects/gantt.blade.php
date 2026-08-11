@extends('layouts.main')

@section('content')
    <div class="mb-4 flex justify-between items-start">
        <a href="{{ route('projects.show', $project->id) }}" class="text-cyan-glow hover:text-tech-blue font-medium flex items-center transition-colors">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Kembali ke Detail
        </a>
    </div>

    <div class="mb-8">
        <h2 class="text-3xl font-display font-bold text-dark-navy">Gantt Chart</h2>
        <p class="text-steel-gray mt-1">{{ $project->nama_proyek }}</p>
    </div>

    <div class="skeuo-card overflow-hidden">
        <div class="overflow-x-auto p-6 bg-ice-blue">
            <div class="inline-block min-w-full">
                <!-- Timeline Header -->
                <div class="flex border-b-2 border-steel-gray pb-2 mb-4" style="min-width: {{ 200 + ($totalDays * 30) }}px;">
                    <div class="w-[200px] flex-shrink-0 font-bold text-dark-navy">Task / Fase</div>
                    <div class="flex relative" style="width: {{ $totalDays * 30 }}px;">
                        @for($i = 0; $i < $totalDays; $i++)
                            @php
                                $currentDate = $minDate->copy()->addDays($i);
                                $isToday = $currentDate->isToday();
                            @endphp
                            <div class="absolute top-0 text-center flex flex-col items-center" style="left: {{ $i * 30 }}px; width: 30px;">
                                <span class="text-[10px] text-steel-gray">{{ $currentDate->format('M') }}</span>
                                <span class="text-xs {{ $isToday ? 'font-bold text-gauge-red bg-gauge-red/10 rounded-full w-6 h-6 flex items-center justify-center' : 'text-dark-navy' }}">{{ $currentDate->format('d') }}</span>
                            </div>
                        @endfor
                    </div>
                </div>

                <!-- Gantt Rows -->
                <div class="space-y-4" style="min-width: {{ 200 + ($totalDays * 30) }}px;">
                    @foreach($project->phases as $phase)
                        <!-- Phase Header Row -->
                        <div class="flex items-center">
                            <div class="w-[200px] flex-shrink-0 font-display font-bold text-dark-navy truncate pr-4 text-sm" title="{{ $phase->nama_fase }}">
                                {{ $phase->nama_fase }}
                            </div>
                            <div class="flex-1 relative h-6 border-l border-steel-gray/30">
                                <!-- Phase Bar (could span the whole min-max of its tasks, for now just a spacer) -->
                            </div>
                        </div>

                        <!-- Task Rows -->
                        @foreach($phase->tasks as $task)
                            @php
                                $hasDates = $task->tanggal_mulai && $task->deadline;
                                $startTask = $hasDates ? \Carbon\Carbon::parse($task->tanggal_mulai) : null;
                                $endTask = $hasDates ? \Carbon\Carbon::parse($task->deadline) : null;
                                
                                $startOffset = $hasDates ? $minDate->diffInDays($startTask) * 30 : 0;
                                $durationDays = $hasDates ? $startTask->diffInDays($endTask) + 1 : 0;
                                $width = $durationDays * 30;
                                
                                $progressWidth = $hasDates ? ($task->progress_pct / 100) * $width : 0;
                                
                                // Status color
                                $barColor = 'bg-steel-gray';
                                if ($task->progress_pct == 100) $barColor = 'bg-gauge-green';
                                elseif ($task->progress_pct > 0) $barColor = 'bg-cyan-glow';
                            @endphp
                            <div class="flex items-center">
                                <div class="w-[200px] flex-shrink-0 pl-6 text-sm text-dark-navy truncate pr-4" title="{{ $task->nama_task }}">
                                    {{ $task->nama_task }}
                                </div>
                                <div class="flex-1 relative h-8 border-l border-steel-gray/30">
                                    <!-- Grid lines background -->
                                    <div class="absolute inset-0 flex">
                                        @for($i = 0; $i < $totalDays; $i++)
                                            <div class="border-r border-steel-gray/20 h-full" style="width: 30px;"></div>
                                        @endfor
                                    </div>
                                    
                                    @if($hasDates)
                                        <!-- Task Bar -->
                                        <div class="absolute top-1 h-6 rounded-sm shadow-md overflow-hidden bg-white border border-steel-gray group" style="left: {{ $startOffset }}px; width: {{ $width }}px;">
                                            <div class="h-full {{ $barColor }} opacity-80" style="width: {{ $progressWidth }}px;"></div>
                                            
                                            <!-- Tooltip on hover -->
                                            <div class="hidden group-hover:block absolute top-full mt-1 left-0 z-10 bg-dark-navy text-white text-xs p-2 rounded shadow-lg whitespace-nowrap">
                                                <p class="font-bold">{{ $task->nama_task }}</p>
                                                <p>{{ $startTask->format('d M') }} - {{ $endTask->format('d M') }}</p>
                                                <p>Progress: {{ $task->progress_pct }}%</p>
                                            </div>
                                        </div>
                                    @else
                                        <div class="absolute top-1 flex items-center h-6 text-xs text-steel-gray italic px-2">
                                            Tanggal belum diset
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    @endforeach
                </div>
            </div>
        </div>
    </div>
@endsection
