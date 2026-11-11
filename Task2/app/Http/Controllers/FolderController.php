<?php

namespace App\Http\Controllers;

use App\Models\Folder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class FolderController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'parent_id' => ['nullable', 'exists:folders,id'],
        ]);
        if ($request->parent_id) {
            $parent = Folder::findOrFail($request->parent_id);
            if ($parent->user_id !== Auth::id()) {
                abort(403);
            }
        }

        Folder::create([
            'user_id' => Auth::id(),
            'parent_id' => $request->parent_id,
            'name' => $request->name,
        ]);

        return back()->with('success', 'Папка создана!');
    }
    public function update(Request $request, Folder $folder)
    {
        if ($folder->user_id !== Auth::id()) {
            abort(403);
        }

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $folder->update(['name' => $request->name]);

        return back()->with('success', 'Папка переименована!');
    }
    public function destroy(Folder $folder)
    {
        if ($folder->user_id !== Auth::id()) {
            abort(403);
        }

        $this->deletePhysicalFiles($folder);
        $folder->delete();

        return back()->with('success', 'Папка и её содержимое удалены!');
    }    private function deletePhysicalFiles(Folder $folder)
    {
        foreach ($folder->files as $file) {
            if (Storage::disk('public')->exists($file->path)) {
                Storage::disk('public')->delete($file->path);
            }
        }

        foreach ($folder->children as $childFolder) {
            $this->deletePhysicalFiles($childFolder);
        }
    }
}
