@extends('layouts.main')

@section('content')
    <div class="mb-8">
        <h2 class="text-3xl font-display font-bold text-dark-navy">Proyek Baru</h2>
        <p class="text-steel-gray mt-1">Buat inisiatif proyek baru</p>
    </div>

    <div class="max-w-3xl">
        <form action="{{ route('projects.store') }}" method="POST">
            @csrf
            
            <div class="skeuo-card p-6 md:p-8 space-y-6">
                <!-- Group & Priority -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="group_id" class="block text-sm font-medium text-dark-navy mb-2">Grup / Divisi</label>
                        <select id="group_id" name="group_id" class="skeuo-select">
                            <option value="">Pilih Grup</option>
                            @foreach($groups ?? [] as $group)
                                <option value="{{ $group->id }}">{{ $group->nama_grup }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="prioritas" class="block text-sm font-medium text-dark-navy mb-2">Prioritas</label>
                        <select id="prioritas" name="prioritas" class="skeuo-select">
                            <option value="medium">Normal</option>
                            <option value="high">Tinggi</option>
                            <option value="low">Rendah</option>
                            <option value="urgent">Kritis</option>
                        </select>
                    </div>
                </div>

                <!-- Project Name -->
                <div>
                    <label for="nama_proyek" class="block text-sm font-medium text-dark-navy mb-2">Nama Proyek</label>
                    <input type="text" id="nama_proyek" name="nama_proyek" required class="skeuo-input text-lg py-3">
                </div>

                <!-- PM & Date -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="pm_user_id" class="block text-sm font-medium text-dark-navy mb-2">Project Manager (PM)</label>
                        <select id="pm_user_id" name="pm_user_id" required class="skeuo-select">
                            <option value="">Pilih PM</option>
                            @foreach($pms ?? [] as $pm)
                                <option value="{{ $pm->id }}">{{ $pm->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-dark-navy mb-2">Anggota Tim (PIC)</label>
                        <div class="skeuo-input p-3 max-h-32 overflow-y-auto space-y-2">
                            @foreach($pics ?? [] as $pic)
                                <label class="flex items-center space-x-3 cursor-pointer p-1 hover:bg-ice-blue rounded transition-colors">
                                    <input type="checkbox" name="pics[]" value="{{ $pic->id }}" class="form-checkbox h-4 w-4 text-tech-blue border-steel-gray rounded focus:ring-tech-blue focus:ring-offset-ice-blue">
                                    <span class="text-sm font-medium text-dark-navy">{{ $pic->nama }}</span>
                                </label>
                            @endforeach
                        </div>
                        <p class="text-xs text-steel-gray mt-1">Pilih satu atau lebih anggota tim.</p>
                    </div>
                    <div>
                        <label for="tanggal_mulai" class="block text-sm font-medium text-dark-navy mb-2">Tanggal Mulai</label>
                        <input type="date" id="tanggal_mulai" name="tanggal_mulai" class="skeuo-input py-2">
                    </div>
                    <div>
                        <label for="target_selesai" class="block text-sm font-medium text-dark-navy mb-2">Target Selesai</label>
                        <input type="date" id="target_selesai" name="target_selesai" class="skeuo-input py-2">
                    </div>
                </div>

                <!-- Bobot Proyek -->
                <div>
                    <label for="bobot_pct" class="block text-sm font-medium text-dark-navy mb-2">Bobot Proyek (%)</label>
                    <div class="flex items-center">
                        <input type="number" id="bobot_pct" name="bobot_pct" min="0.01" max="100" step="0.01" value="100" required class="skeuo-input w-24 text-center font-bold text-lg">
                        <span class="ml-3 text-sm text-steel-gray">% dari total target Grup Proyek</span>
                    </div>
                    <p class="text-xs text-steel-gray mt-1">Isi 100 jika proyek ini berdiri sendiri (tidak tergabung di grup) atau merupakan satu-satunya proyek di grup tersebut.</p>
                </div>
                </div>

                <!-- Description -->
                <div>
                    <label for="deskripsi-editor" class="block text-sm font-medium text-dark-navy mb-2">Deskripsi & Tujuan</label>
                    <textarea id="deskripsi-editor" name="deskripsi" rows="5" class="skeuo-input"></textarea>
                </div>
            </div>

            <!-- Actions -->
            <div class="mt-6 flex justify-end space-x-4">
                <a href="{{ url('/') }}" class="skeuo-btn-secondary">
                    Batal
                </a>
                <button type="submit" class="skeuo-btn-success">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path></svg>
                    Simpan Proyek
                </button>
            </div>
        </form>
    </div>
@endsection
