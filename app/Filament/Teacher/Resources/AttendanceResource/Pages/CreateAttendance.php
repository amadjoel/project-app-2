<?php

namespace App\Filament\Teacher\Resources\AttendanceResource\Pages;

use App\Filament\Teacher\Resources\AttendanceResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateAttendance extends CreateRecord
{
    protected static string $resource = AttendanceResource::class;
}
