<?php

namespace App\Filament\Teacher\Resources\UserResource\Pages;

use App\Filament\Teacher\Resources\UserResource;
use Filament\Resources\Pages\Page;

class TeacherDashboard extends Page
{
    protected static string $resource = UserResource::class;

    protected static string $view = 'filament.teacher.resources.user-resource.pages.teacher-dashboard';
}
