<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Task;
use App\Http\Requests\StoreTaskRequest;
use Illuminate\Support\Facades\Log;

class TaskController extends Controller
{
    public function index(Request $request)
{
    $query = Task::query();
    if ($request->has('status') && $request->get('status') !== 'all') {
        $status = $request->get('status');
        
        if ($status === 'completed') {
            $query->where('status', 'выполнена');
        } elseif ($status === 'pending') {
            $query->whereIn('status', ['не выполнена', 'в процессе']);
        }
    }
    if ($request->has('sort')) {
        switch ($request->get('sort')) {
            case 'alphabet':
                $query->orderBy('title', 'asc');
                break;
            case 'date_asc':
                $query->orderBy('created_at', 'asc');
                break;
            case 'date_desc':
            default:
                $query->orderBy('created_at', 'desc');
                break;
        }
    } else {
        $query->orderBy('created_at', 'desc');
    }

    $tasks = $query->get();

    return view('tasks.index', compact('tasks'));
}

    public function store(StoreTaskRequest $request)
    {
        $task = Task::create($request->validated());
        Log::info("Задача успешно создана: ID {$task->id}, Заголовок: '{$task->title}'");
        return redirect()->route('tasks.index')->with('success', 'Задача успешно добавлена!');
    }
    public function update(UpdateTaskRequest $request, Task $task)
{
    $oldTitle = $task->title;
    $task->update($request->validated());
    Log::info("Задача успешно обновлена: ID {$task->id}. Старое название: '{$oldTitle}', Новое название: '{$task->title}', Статус: '{$task->status}'");

    return redirect()->route('tasks.index')->with('success', 'Задача успешно обновлена!');
}
    public function destroy(Task $task)
{
    $taskTitle = $task->title;
    $task->delete();
    Log::info("Задача успешно удалена: ID {$task->id}, Название: '{$taskTitle}'");

    return redirect()->route('tasks.index')->with('success', 'Задача успешно удалена!');
}
public function updateStatus(Request $request, Task $task)
{
    $validated = $request->validate([
        'status' => 'required|in:выполнена,в процессе,не выполнена',
    ]);

    $oldStatus = $task->status;
    $task->update(['status' => $validated['status']]);
    Log::info("Статус задачи изменен: ID {$task->id}, Название: '{$task->title}'. Старый статус: '{$oldStatus}', Новый статус: '{$task->status}'");

    return redirect()->back()->with('success', 'Статус задачи успешно изменен!');
}
}
