<?php

    $totalAktif = 1;
    $totalHealthy = 1;
    $healthScoreAverage = 100;
    
    $groups = \App\Models\ProjectGroup::with(['projects.phases.tasks.pics', 'projects.phases.pic', 'projects.pm', 'projects.pics'])->get();
    $ungroupedProjects = \App\Models\Project::with(['phases.tasks.pics', 'phases.pic', 'pm', 'pics'])->whereNull('group_id')->where('status', '!=', 'dibatalkan')->get();
    $allPics = \App\Models\Pic::all();

    $html = view('welcome', compact('groups', 'ungroupedProjects', 'totalAktif', 'totalHealthy', 'healthScoreAverage', 'allPics'))->render();
    echo $html;
