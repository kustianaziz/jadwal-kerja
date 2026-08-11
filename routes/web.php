<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectController;
use App\Models\Project;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::get('/dashboard', function () {
    $projects = Project::with(['pm', 'phases.tasks.pics', 'phases.pic', 'pics'])->where('status', '!=', 'dibatalkan')->get();
    
    $totalAktif = $projects->where('status', 'berjalan')->count();
    $totalHealthy = $projects->where('health_status', 'healthy')->count();
    $healthScoreAverage = $projects->avg('health_score') ?? 0;
    
    $groups = \App\Models\ProjectGroup::with(['projects.phases.tasks.pics', 'projects.phases.tasks.journals.author', 'projects.phases.tasks.journals.attachments', 'projects.phases.pic', 'projects.pm', 'projects.pics'])->get();
    $ungroupedProjects = \App\Models\Project::with(['phases.tasks.pics', 'phases.tasks.journals.author', 'phases.tasks.journals.attachments', 'phases.pic', 'pm', 'pics'])->whereNull('group_id')->where('status', '!=', 'dibatalkan')->get();
    
    $allPics = \App\Models\Pic::all();

    return view('welcome', compact('projects', 'groups', 'ungroupedProjects', 'totalAktif', 'totalHealthy', 'healthScoreAverage', 'allPics'));
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/projects/create', [ProjectController::class, 'create'])->name('projects.create');
    Route::post('/projects', [ProjectController::class, 'store'])->name('projects.store');
    Route::get('/projects/gantt', [ProjectController::class, 'globalGantt'])->name('projects.global_gantt');
    Route::get('/projects/{project}', [ProjectController::class, 'show'])->name('projects.show');
    Route::get('/projects/{project}/gantt', [ProjectController::class, 'gantt'])->name('projects.gantt');
    Route::get('/projects/{project}/edit', [ProjectController::class, 'edit'])->name('projects.edit');
    Route::put('/projects/{project}', [ProjectController::class, 'update'])->name('projects.update');
    Route::delete('/projects/{project}', [ProjectController::class, 'destroy'])->name('projects.destroy');
    
    Route::get('/projects/{project}/phases/create', [App\Http\Controllers\PhaseController::class, 'create'])->name('phases.create');
    Route::post('/projects/{project}/phases', [App\Http\Controllers\PhaseController::class, 'store'])->name('phases.store');
    Route::post('/phases/{phase}/copy', [App\Http\Controllers\PhaseController::class, 'copy'])->name('phases.copy');
    Route::get('/phases/{phase}/edit', [App\Http\Controllers\PhaseController::class, 'edit'])->name('phases.edit');
    Route::put('/phases/{phase}', [App\Http\Controllers\PhaseController::class, 'update'])->name('phases.update');
    Route::delete('/phases/{phase}', [App\Http\Controllers\PhaseController::class, 'destroy'])->name('phases.destroy');
    
    Route::get('/phases/{phase}/tasks/create', [App\Http\Controllers\TaskController::class, 'create'])->name('tasks.create');
    Route::post('/phases/{phase}/tasks', [App\Http\Controllers\TaskController::class, 'store'])->name('tasks.store');
    
    Route::get('/tasks/{task}/edit', [App\Http\Controllers\TaskController::class, 'edit'])->name('tasks.edit');
    Route::put('/tasks/{task}', [App\Http\Controllers\TaskController::class, 'update'])->name('tasks.update');
    Route::post('/tasks/{task}/move', [App\Http\Controllers\TaskController::class, 'move'])->name('tasks.move');
    Route::delete('/tasks/{task}', [App\Http\Controllers\TaskController::class, 'destroy'])->name('tasks.destroy');
    
    Route::post('/projects/{project}/journals', [App\Http\Controllers\JournalController::class, 'store'])->name('journals.store');
    Route::get('/journals/{journal}/edit', [App\Http\Controllers\JournalController::class, 'edit'])->name('journals.edit');
    Route::put('/journals/{journal}', [App\Http\Controllers\JournalController::class, 'update'])->name('journals.update');
    Route::delete('/journals/{journal}', [App\Http\Controllers\JournalController::class, 'destroy'])->name('journals.destroy');
    Route::post('/journals/bulk-move', [App\Http\Controllers\JournalController::class, 'bulkMove'])->name('journals.bulkMove');
    Route::post('/journals/bulk-copy', [App\Http\Controllers\JournalController::class, 'bulkCopy'])->name('journals.bulkCopy');
    Route::post('/journals/{journal}/move', [App\Http\Controllers\JournalController::class, 'move'])->name('journals.move');
    Route::post('/journals/{journal}/copy', [App\Http\Controllers\JournalController::class, 'copy'])->name('journals.copy');
    
    Route::get('/reports', function () {
        $groups = App\Models\ProjectGroup::with(['projects.phases.tasks.pics', 'projects.phases.tasks.journals.author', 'projects.phases.tasks.journals.attachments', 'projects.phases.pic', 'projects.pm', 'projects.pics'])->get();
        $ungroupedProjects = App\Models\Project::with(['phases.tasks.pics', 'phases.tasks.journals.author', 'phases.tasks.journals.attachments', 'phases.pic', 'pm', 'pics'])->whereNull('group_id')->get();
        return view('reports.index', compact('groups', 'ungroupedProjects'));
    })->name('reports.index');

    Route::resource('teams', App\Http\Controllers\TeamController::class)->except(['create', 'show']);

    Route::get('/groups', [App\Http\Controllers\ProjectGroupController::class, 'index'])->name('groups.index');
    Route::post('/groups', [App\Http\Controllers\ProjectGroupController::class, 'store'])->name('groups.store');
    Route::get('/groups/{group}/edit', [App\Http\Controllers\ProjectGroupController::class, 'edit'])->name('groups.edit');
    Route::put('/groups/{group}', [App\Http\Controllers\ProjectGroupController::class, 'update'])->name('groups.update');
    Route::delete('/groups/{group}', [App\Http\Controllers\ProjectGroupController::class, 'destroy'])->name('groups.destroy');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
