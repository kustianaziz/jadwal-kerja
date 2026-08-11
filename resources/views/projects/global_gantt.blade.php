@extends('layouts.main')

@section('content')
    <div class="mb-4 flex justify-between items-start">
        <a href="{{ url('/') }}" class="text-cyan-glow hover:text-tech-blue font-medium flex items-center transition-colors">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Kembali ke Dashboard
        </a>
    </div>

    <div class="mb-8">
        <h2 class="text-3xl font-display font-bold text-dark-navy">Roadmap Semua Proyek</h2>
        <p class="text-steel-gray mt-1">Gantt Chart timeline untuk seluruh proyek yang ada</p>
    </div>

    <div class="skeuo-card overflow-hidden">
        <div class="overflow-x-auto p-6 bg-ice-blue">
            <div class="inline-block min-w-full">
                <!-- Timeline Header -->
                <div class="flex border-b-2 border-steel-gray pb-2 mb-4" style="min-width: {{ 200 + ($totalDays * 30) }}px;">
                    <div class="w-[200px] flex-shrink-0 font-bold text-dark-navy">Nama Proyek</div>
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
                <div class="space-y-6" style="min-width: {{ 200 + ($totalDays * 30) }}px;">
                    @forelse($projects as $project)
                        @php
                            $pDates = $projectDates[$project->id];
                            $startProject = $pDates['start'];
                            $endProject = $pDates['end'];
                            
                            $startOffset = $minDate->diffInDays($startProject) * 30;
                            $durationDays = $startProject->diffInDays($endProject) + 1;
                            $width = $durationDays * 30;
                            
                            $progress = $project->progress ?? 0;
                            $progressWidth = ($progress / 100) * $width;
                            
                            // Health color mapping
                            $barColor = 'bg-gauge-green';
                            if ($project->health_status == 'at_risk' || $project->health_status == 'Terlambat') $barColor = 'bg-gauge-orange';
                            elseif ($project->health_status == 'attention') $barColor = 'bg-gauge-amber';
                            elseif ($project->health_status == 'critical') $barColor = 'bg-gauge-red';
                        @endphp
                        
                        <div class="flex items-center">
                            <div class="w-[200px] flex-shrink-0 font-display font-bold text-dark-navy truncate pr-4 text-sm">
                                <a href="{{ route('projects.show', $project->id) }}" class="hover:text-tech-blue transition-colors">
                                    {{ $project->nama_proyek }}
                                </a>
                            </div>
                            <div class="flex-1 relative h-8 border-l border-steel-gray/30">
                                <!-- Grid lines background -->
                                <div class="absolute inset-0 flex">
                                    @for($i = 0; $i < $totalDays; $i++)
                                        <div class="border-r border-steel-gray/20 h-full" style="width: 30px;"></div>
                                    @endfor
                                </div>
                                
                                <!-- Project Bar -->
                                <a href="{{ route('projects.gantt', $project->id) }}" class="absolute top-1 h-6 rounded-sm shadow-md overflow-hidden bg-white border border-steel-gray group block" style="left: {{ $startOffset }}px; width: {{ $width }}px;">
                                    <div class="h-full {{ $barColor }} opacity-80" style="width: {{ $progressWidth }}px;"></div>
                                    
                                    <!-- Tooltip on hover -->
                                    <div class="hidden group-hover:block absolute top-full mt-1 left-0 z-10 bg-dark-navy text-white text-xs p-2 rounded shadow-lg whitespace-nowrap">
                                        <p class="font-bold">{{ $project->nama_proyek }}</p>
                                        <p>{{ $startProject->format('d M Y') }} - {{ $endProject->format('d M Y') }}</p>
                                        <p>Progress: {{ $progress }}%</p>
                                        <p class="text-cyan-glow mt-1">Klik untuk lihat Gantt Chart detail</p>
                                    </div>
                                </a>
                            </div>
                        </div>
                    @empty
                        <div class="p-8 text-center text-steel-gray italic w-full">
                            Belum ada proyek.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endsection
