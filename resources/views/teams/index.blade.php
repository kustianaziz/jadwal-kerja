@extends('layouts.main')

@section('content')
    <div class="mb-8">
        <h2 class="text-3xl font-display font-bold text-dark-navy">Tim Saya</h2>
        <p class="text-steel-gray mt-1">Daftar anggota tim dan perannya</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Team Grid -->
        <div class="lg:col-span-2 grid grid-cols-1 sm:grid-cols-2 gap-6">
            @forelse($users ?? [] as $user)
                <div class="skeuo-card p-6 flex flex-col items-center text-center">
                    <!-- Avatar Frame -->
                    <div class="w-24 h-24 mb-4 rounded-full border-4 border-steel-gray bg-ice-blue flex items-center justify-center shadow-[0_4px_6px_rgba(0,0,0,0.1),inset_0_2px_4px_rgba(0,0,0,0.1)] relative">
                        <span class="text-3xl font-display font-bold text-dark-navy">{{ substr($user->name, 0, 1) }}</span>
                        
                        <!-- Role Badge on Avatar -->
                        <div class="absolute -bottom-2 -right-2 bg-tech-blue border-2 border-white rounded-full w-8 h-8 flex items-center justify-center text-ice-blue shadow-sm">
                            @if(($user->role ?? '') == 'pm')
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                            @elseif(($user->role ?? '') == 'admin')
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                            @else
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                            @endif
                        </div>
                    </div>

                    <h3 class="text-lg font-bold text-dark-navy mb-1">{{ $user->name }}</h3>
                    <p class="text-sm text-steel-gray mb-4">{{ $user->email }}</p>

                    <!-- Role Badge -->
                    @php
                        $roleClass = 'bg-steel-gray/20 text-dark-navy border-steel-gray';
                        if(($user->role ?? '') == 'pm') $roleClass = 'bg-tech-blue/10 text-tech-blue border-tech-blue';
                        if(($user->role ?? '') == 'admin') $roleClass = 'bg-gauge-orange/10 text-gauge-orange border-gauge-orange';
                    @endphp
                    <span class="inline-block px-3 py-1 text-xs font-bold uppercase tracking-wider border rounded {{ $roleClass }} mb-4">
                        {{ $user->role ?? 'Anggota' }}
                    </span>

                    <div class="flex space-x-2 mt-auto pt-4 border-t border-steel-gray w-full justify-center">
                        <a href="{{ route('teams.edit', $user->id) }}" class="skeuo-btn-secondary py-1 px-3 text-xs">Edit</a>
                        <form action="{{ route('teams.destroy', $user->id) }}" method="POST" onsubmit="event.preventDefault(); showConfirmModal('Apakah Anda yakin ingin menghapus pengguna ini?', this);">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="py-1 px-3 text-xs bg-gauge-red text-white border border-[#8a2f20] shadow-[inset_0_1px_0_rgba(255,255,255,0.2),_0_2px_4px_rgba(0,0,0,0.2)] rounded-sm font-display uppercase tracking-wider active:shadow-[inset_0_2px_4px_rgba(0,0,0,0.3)] active:translate-y-px transition-all">Hapus</button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="col-span-full p-12 text-center border-2 border-dashed border-steel-gray rounded-md bg-white">
                    <p class="text-steel-gray">Belum ada anggota tim.</p>
                </div>
            @endforelse
        </div>

        <!-- Form Panel -->
        <div class="lg:col-span-1">
            <form action="{{ route('teams.store') }}" method="POST" class="skeuo-card p-6 sticky top-8">
                @csrf
                <h4 class="text-lg font-display font-bold text-dark-navy mb-4 border-b border-steel-gray pb-2">Tambah Anggota Tim</h4>
                
                @if ($errors->any())
                    <div class="mb-4 skeuo-card bg-gauge-red/10 border-gauge-red p-3">
                        <ul class="list-disc list-inside text-sm text-gauge-red">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                
                <div class="space-y-4">
                    <div>
                        <label for="name" class="block text-sm font-medium text-dark-navy mb-1">Nama Lengkap</label>
                        <input type="text" id="name" name="name" required class="skeuo-input text-sm">
                    </div>
                    
                    <div>
                        <label for="email" class="block text-sm font-medium text-dark-navy mb-1">Email</label>
                        <input type="email" id="email" name="email" required class="skeuo-input text-sm">
                    </div>
                    
                    <div>
                        <label for="role" class="block text-sm font-medium text-dark-navy mb-1">Role</label>
                        <select id="role" name="role" required class="skeuo-select py-1.5 text-sm">
                            <option value="pic">PIC / Staf</option>
                            <option value="pm">Project Manager</option>
                            <option value="admin">Administrator</option>
                            <option value="stakeholder">Stakeholder</option>
                        </select>
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-dark-navy mb-1">Password</label>
                        <input type="password" id="password" name="password" required class="skeuo-input text-sm">
                    </div>
                    
                    <button type="submit" class="skeuo-btn w-full text-sm mt-4">
                        Simpan Anggota
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
