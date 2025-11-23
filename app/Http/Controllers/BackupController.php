<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\BackupService;
use App\Models\AuditLog;

class BackupController extends Controller
{
    protected BackupService $backupService;

    public function __construct(BackupService $backupService)
    {
        $this->backupService = $backupService;
    }

    public function index()
    {
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Only administrators can access backups.');
        }

        $backups = $this->backupService->getBackupsList();

        return view('settings.backup', compact('backups'));
    }

    public function create()
    {
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Only administrators can create backups.');
        }

        try {
            $path = $this->backupService->exportDatabase();
            
            AuditLog::log('created', 'backup', basename($path));

            return back()->with('success', 'Backup created successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Backup failed: ' . $e->getMessage());
        }
    }

    public function download(string $filename)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Only administrators can download backups.');
        }

        try {
            AuditLog::log('downloaded', 'backup', $filename);
            
            return $this->backupService->downloadBackup($filename);
        } catch (\Exception $e) {
            return back()->with('error', 'Download failed: ' . $e->getMessage());
        }
    }

    public function restore(Request $request)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Only administrators can restore backups.');
        }

        $request->validate([
            'backup_file' => 'required|file|mimes:sql',
        ]);

        try {
            $file = $request->file('backup_file');
            $path = $file->storeAs('temp', $file->getClientOriginalName());
            $fullPath = storage_path('app/' . $path);

            $this->backupService->restoreDatabase($fullPath);

            // Clean up temp file
            unlink($fullPath);

            AuditLog::log('restored', 'backup', $file->getClientOriginalName());

            return back()->with('success', 'Database restored successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Restore failed: ' . $e->getMessage());
        }
    }

    public function destroy(string $filename)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Only administrators can delete backups.');
        }

        try {
            $this->backupService->deleteBackup($filename);
            
            AuditLog::log('deleted', 'backup', $filename);

            return back()->with('success', 'Backup deleted successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Delete failed: ' . $e->getMessage());
        }
    }
}

