<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Artisan;

class ToolsController extends Controller
{
    public function backup()
    {
        $exit = Artisan::call('backup:dokumen');
        $output = trim(Artisan::output());
        $msg = $exit === 0 ? $output : 'Backup selesai.';
        return back()->with('backup_result', '✓ ' . $msg);
    }

    public function seedPartai()
    {
        Artisan::call('db:seed', ['--class' => 'PartaiSeeder', '--force' => true]);
        $output = trim(Artisan::output());
        return back()->with('seed_result', '✓ ' . $output);
    }
}