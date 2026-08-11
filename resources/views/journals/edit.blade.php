@extends('layouts.main')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="mb-6">
        <a href="{{ url()->previous() }}" class="text-steel-gray hover:text-tech-blue font-bold text-sm tracking-wider uppercase mb-2 inline-block">&larr; Kembali</a>
        <h2 class="text-3xl font-display font-bold text-dark-navy">Edit Jurnal</h2>
        <p class="text-steel-gray mt-1">Mengubah detail jurnal secara manual.</p>
    </div>

    <form action="{{ route('journals.update', $journal->id) }}" method="POST" enctype="multipart/form-data" class="skeuo-card p-8 space-y-6">
        @csrf
        @method('PUT')
        <!-- Default redirect to dashboard if the user came from there, otherwise it goes to project -->
        <input type="hidden" name="redirect_to" value="{{ str_contains(url()->previous(), 'dashboard') ? 'dashboard' : '' }}">
        
        <div>
            <label class="block text-xs font-bold text-steel-gray uppercase tracking-wider mb-2">Judul Jurnal</label>
            <input type="text" name="judul" value="{{ old('judul', $journal->judul) }}" required class="skeuo-input w-full py-2 px-4 font-medium text-dark-navy">
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-xs font-bold text-steel-gray uppercase tracking-wider mb-2">Tanggal</label>
                <input type="date" name="tanggal" value="{{ old('tanggal', $journal->tanggal ? \Carbon\Carbon::parse($journal->tanggal)->format('Y-m-d') : $journal->created_at->format('Y-m-d')) }}" required class="skeuo-input w-full py-2 px-4 text-dark-navy">
            </div>
            
            <div>
                <label class="block text-xs font-bold text-steel-gray uppercase tracking-wider mb-2">Tipe Jurnal</label>
                <select name="tipe" class="skeuo-select w-full py-2 px-4 text-dark-navy" required>
                    <option value="update" {{ old('tipe', $journal->tipe) == 'update' ? 'selected' : '' }}>Update Progress</option>
                    <option value="issue" {{ old('tipe', $journal->tipe) == 'issue' ? 'selected' : '' }}>Issue/Kendala</option>
                    <option value="pencapaian" {{ old('tipe', $journal->tipe) == 'pencapaian' ? 'selected' : '' }}>Pencapaian</option>
                    @if($journal->tipe == 'system')
                        <option value="system" selected>System Log</option>
                    @endif
                </select>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-xs font-bold text-steel-gray uppercase tracking-wider mb-2">Status Task</label>
                <select name="status" class="skeuo-select w-full py-2 px-4 text-dark-navy" required>
                    <option value="in_progress" {{ ($journal->task && $journal->task->status == 'in_progress') ? 'selected' : '' }}>In Progress</option>
                    <option value="review" {{ ($journal->task && $journal->task->status == 'review') ? 'selected' : '' }}>Review</option>
                    <option value="blocked" {{ ($journal->task && $journal->task->status == 'blocked') ? 'selected' : '' }}>Blocked</option>
                    <option value="selesai" {{ ($journal->task && $journal->task->status == 'selesai') ? 'selected' : '' }}>Selesai</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-bold text-steel-gray uppercase tracking-wider mb-2">Progress (%)</label>
                <div class="relative">
                    <input type="number" name="progress_pct" value="{{ old('progress_pct', $journal->task->progress_pct ?? 0) }}" step="0.01" min="0" max="100" required class="skeuo-input w-full py-2 px-4 pr-10 text-dark-navy">
                    <span class="absolute right-4 top-2 text-steel-gray pointer-events-none">%</span>
                </div>
            </div>
        </div>

        <div>
            <label class="block text-xs font-bold text-steel-gray uppercase tracking-wider mb-2">Detail Jurnal</label>
            <textarea name="detail" rows="6" class="skeuo-input w-full py-3 px-4 text-dark-navy leading-relaxed">{!! old('detail', strip_tags($journal->detail)) !!}</textarea>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div x-data="{ links: {{ empty($journal->tautan) ? '[{url: \'\'}]' : collect($journal->tautan)->map(function($url){ return ['url' => $url]; })->toJson() }} }" class="space-y-2">
                <label class="block text-xs font-bold text-steel-gray uppercase tracking-wider mb-1">Tautan / Link</label>
                <template x-for="(link, index) in links" :key="index">
                    <div class="flex items-center mb-2">
                        <input type="url" name="tautan[]" x-model="link.url" placeholder="https://..." class="skeuo-input w-full py-2 px-3 text-sm">
                        <button type="button" @click="links.splice(index, 1)" x-show="links.length > 1" class="ml-2 text-gauge-red hover:text-red-700">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>
                </template>
                <button type="button" @click="links.push({url: ''})" class="text-sm font-bold text-tech-blue hover:underline">+ Tambah Tautan</button>
            </div>
            
            <div>
                <label class="block text-xs font-bold text-steel-gray uppercase tracking-wider mb-1">Lampiran File Tambahan</label>
                <input type="file" name="lampiran[]" multiple class="skeuo-input w-full py-1.5 px-3 text-sm bg-white" accept="image/*,.pdf,.doc,.docx,.xls,.xlsx">
                <p class="text-xs text-steel-gray mt-2">Tekan CTRL/CMD saat memilih file untuk melampirkan lebih dari satu file. Lampiran baru akan ditambahkan tanpa menghapus lampiran lama.</p>
            </div>
        </div>

        <div class="pt-4 border-t border-steel-gray/30 flex justify-end gap-4">
            <a href="{{ url()->previous() }}" class="skeuo-btn-secondary py-2 px-6 font-bold uppercase tracking-wider">Batal</a>
            <button type="submit" class="skeuo-btn py-2 px-8 font-bold uppercase tracking-wider">Simpan Perubahan</button>
        </div>
    </form>
</div>
@endsection
