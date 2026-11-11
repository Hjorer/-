<?php

namespace App\Http\Controllers;

use App\Models\File;
use App\Models\Folder;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class FileController extends Controller
{
    // Главная страница, папка, поиск или фильтр по тегу
    public function index(Request $request)
    {
        $search = trim($request->input('search', ''));
        $selectedTagId = $request->input('tag_id');
        $currentFolder = null;
        $selectedTag = null;

        if ($search !== '' || $selectedTagId) {
            $folders = collect(); // При поиске или фильтре по тегу папки скрываем

            $query = File::where('user_id', Auth::id())
                ->with(['folder', 'tags'])
                ->latest();

            if ($search !== '') {
                $query->where('name', 'LIKE', '%' . $search . '%');
            }

            if ($selectedTagId) {
                $selectedTag = Tag::where('user_id', Auth::id())->findOrFail($selectedTagId);
                $query->whereHas('tags', function ($q) use ($selectedTagId) {
                    $q->where('tags.id', $selectedTagId);
                });
            }

            $files = $query->get();
        } else {
            if ($request->has('folder_id') && $request->folder_id !== null) {
                $currentFolder = Folder::where('user_id', Auth::id())
                    ->findOrFail($request->folder_id);
            }

            $folders = Folder::where('user_id', Auth::id())
                ->where('parent_id', $currentFolder?->id)
                ->orderBy('name')
                ->get();

            $files = File::where('user_id', Auth::id())
                ->where('folder_id', $currentFolder?->id)
                ->with('tags')
                ->latest()
                ->get();
        }

        // Все теги пользователя для панели фильтрации
        $allTags = Tag::where('user_id', Auth::id())->orderBy('name')->get();
        $allFolders = Folder::where('user_id', Auth::id())->orderBy('name')->get();
        return view('files.index', compact(
            'files',
            'folders',
            'currentFolder',
            'search',
            'allTags',
            'selectedTag',
            'allFolders'
        ));
    }

    // Загрузка файла (с опциональными тегами через запятую)
    public function upload(Request $request)
    {
        $request->validate([
            'file' => [
                'required',
                'file',
                'max:5120',
                'mimes:jpg,jpeg,png,gif,pdf,doc,docx,txt',
            ],
            'folder_id' => ['nullable', 'exists:folders,id'],
            'tags' => ['nullable', 'string', 'max:255'],
        ], [
            'file.required' => 'Выберите файл для загрузки.',
            'file.max' => 'Размер файла не должен превышать 5 МБ.',
            'file.mimes' => 'Недопустимый формат файла. Разрешены: jpg, jpeg, png, gif, pdf, doc, docx, txt.',
        ]);

        if ($request->folder_id) {
            $folder = Folder::findOrFail($request->folder_id);
            if ($folder->user_id !== Auth::id()) {
                abort(403);
            }
        }

        $uploadedFile = $request->file('file');
        $path = $uploadedFile->store('uploads/' . Auth::id(), 'public');

        $file = File::create([
            'user_id' => Auth::id(),
            'folder_id' => $request->folder_id,
            'name' => $uploadedFile->getClientOriginalName(),
            'path' => $path,
            'mime_type' => $uploadedFile->getMimeType(),
            'size' => $uploadedFile->getSize(),
        ]);

        // Привязываем теги, если они были указаны
        if ($request->filled('tags')) {
            $tagNames = array_filter(array_map('trim', explode(',', $request->tags)));
            $tagIds = [];

            foreach ($tagNames as $tagName) {
                $tag = Tag::firstOrCreate([
                    'user_id' => Auth::id(),
                    'name' => mb_strtolower($tagName),
                ]);
                $tagIds[] = $tag->id;
            }

            $file->tags()->syncWithoutDetaching($tagIds);
        }

        return back()->with('success', 'Файл успешно загружен!');
    }

    // Добавление тега к существующему файлу
    public function attachTag(Request $request, File $file)
    {
        if ($file->user_id !== Auth::id()) {
            abort(403);
        }

        $request->validate([
            'tag' => ['required', 'string', 'max:50'],
        ]);

        $tag = Tag::firstOrCreate([
            'user_id' => Auth::id(),
            'name' => mb_strtolower(trim($request->tag)),
        ]);

        $file->tags()->syncWithoutDetaching([$tag->id]);

        return back()->with('success', 'Тег добавлен!');
    }

    // Удаление тега у файла
    public function detachTag(File $file, Tag $tag)
    {
        if ($file->user_id !== Auth::id() || $tag->user_id !== Auth::id()) {
            abort(403);
        }

        $file->tags()->detach($tag->id);

        // Удаляем сам тег, если он больше не привязан ни к одному файлу
        if ($tag->files()->count() === 0) {
            $tag->delete();
        }

        return back()->with('success', 'Тег удален!');
    }

    public function download(File $file)
    {
        if ($file->user_id !== Auth::id()) {
            abort(403, 'Доступ запрещен.');
        }

        if (!Storage::disk('public')->exists($file->path)) {
            abort(404, 'Файл не найден на сервере.');
        }

        return Storage::disk('public')->download($file->path, $file->name);
    }

    public function preview(File $file)
    {
        if ($file->user_id !== Auth::id()) {
            abort(403, 'Доступ запрещен.');
        }

        if (!str_starts_with($file->mime_type, 'image/')) {
            abort(400, 'Предпросмотр доступен только для изображений.');
        }

        if (!Storage::disk('public')->exists($file->path)) {
            abort(404, 'Файл не найден.');
        }

        return response()->file(Storage::disk('public')->path($file->path));
    }

    public function update(Request $request, File $file)
    {
        if ($file->user_id !== Auth::id()) {
            abort(403, 'Доступ запрещен.');
        }

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $extension = pathinfo($file->name, PATHINFO_EXTENSION);
        $newName = trim($request->name);

        if ($extension && !str_ends_with(strtolower($newName), '.' . strtolower($extension))) {
            $newName .= '.' . $extension;
        }

        $file->update(['name' => $newName]);

        return back()->with('success', 'Файл успешно переименован!');
    }

    public function destroy(File $file)
    {
        if ($file->user_id !== Auth::id()) {
            abort(403, 'Доступ запрещен.');
        }

        if (Storage::disk('public')->exists($file->path)) {
            Storage::disk('public')->delete($file->path);
        }

        $file->delete();

        return back()->with('success', 'Файл успешно удален!');
    }
    // Перемещение файла в другую папку
public function move(Request $request, File $file)
{
    if ($file->user_id !== Auth::id()) {
        abort(403, 'Доступ запрещен.');
    }

    $request->validate([
        'folder_id' => ['nullable', 'exists:folders,id'],
    ]);

    if ($request->folder_id) {
        $targetFolder = Folder::findOrFail($request->folder_id);
        if ($targetFolder->user_id !== Auth::id()) {
            abort(403, 'Нельзя переместить файл в чужую папку.');
        }
    }

    $file->update([
        'folder_id' => $request->folder_id,
    ]);

    return back()->with('success', 'Файл перемещен!');
}

}
