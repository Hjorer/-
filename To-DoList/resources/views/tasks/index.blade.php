<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>To-Do List</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
</head>
<body class="bg-light">

<div class="container py-5">
    <h1 class="text-center mb-5 text-primary">Отслеживание задач (To-Do List)</h1>
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <div class="card shadow-sm mb-5">
        <div class="card-header bg-primary text-white">
            <h5 class="card-title mb-0">Новая задача</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('tasks.store') }}" method="POST">
                @csrf
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="title" class="form-label">Название задачи *</label>
                        <input type="text" name="title" id="title" class="form-control" value="{{ old('title') }}" placeholder="Например: Сдать лабораторную">
                    </div>
                    <div class="col-md-6">
                        <label for="due_date" class="form-label">Срок выполнения *</label>
                        <input type="date" name="due_date" id="due_date" class="form-control" value="{{ old('due_date') }}">
                    </div>
                    <div class="col-12">
                        <label for="description" class="form-label">Описание задачи</label>
                        <textarea name="description" id="description" class="form-control" rows="3" placeholder="Дополнительные детали..."></textarea>
                    </div>
                    <div class="col-12 text-end">
                        <button type="submit" class="btn btn-success px-4">Добавить задачу</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <h2 class="mb-4 text-secondary">Ваши задачи</h2>
    <div class="row g-3 mb-4 bg-white p-3 rounded shadow-sm align-items-center">
        <div class="col-md-6 d-flex align-items-center flex-wrap">
            <strong class="text-muted me-3">Фильтр по статусу:</strong>
            <div class="btn-group btn-group-sm" role="group">
                <a href="{{ route('tasks.index', array_merge(request()->query(), ['status' => 'all'])) }}" 
                   class="btn {{ request('status') === 'all' || !request('status') ? 'btn-dark' : 'btn-outline-dark' }}">
                    Все
                </a>
                <a href="{{ route('tasks.index', array_merge(request()->query(), ['status' => 'completed'])) }}" 
                   class="btn {{ request('status') === 'completed' ? 'btn-success' : 'btn-outline-success' }}">
                    Выполненные
                </a>
                <a href="{{ route('tasks.index', array_merge(request()->query(), ['status' => 'pending'])) }}" 
                   class="btn {{ request('status') === 'pending' ? 'btn-danger' : 'btn-outline-danger' }}">
                    Невыполненные
                </a>
            </div>
        </div>
        <div class="col-md-6 d-flex align-items-center justify-content-md-end flex-wrap">
            <strong class="text-muted me-3">Сортировка:</strong>
            <div class="btn-group btn-group-sm" role="group">
                <a href="{{ route('tasks.index', array_merge(request()->query(), ['sort' => 'date_desc'])) }}" 
                   class="btn {{ request('sort') === 'date_desc' || !request('sort') ? 'btn-primary' : 'btn-outline-primary' }}">
                    Сначала новые
                </a>
                <a href="{{ route('tasks.index', array_merge(request()->query(), ['sort' => 'date_asc'])) }}" 
                   class="btn {{ request('sort') === 'date_asc' ? 'btn-primary' : 'btn-outline-primary' }}">
                    Сначала старые
                </a>
                <a href="{{ route('tasks.index', array_merge(request()->query(), ['sort' => 'alphabet'])) }}" 
                   class="btn {{ request('sort') === 'alphabet' ? 'btn-primary' : 'btn-outline-primary' }}">
                    По алфавиту
                </a>
            </div>
        </div>
    </div>

    @if($tasks->isEmpty())
        <div class="alert alert-info text-center shadow-sm">
            У вас пока нет созданных задач. Добавьте первую задачу выше!
        </div>
    @else
        <div class="row g-3">
            @foreach($tasks as $task)
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 shadow-sm border-start border-4 
                        @if($task->status === 'выполнена') border-success 
                        @elseif($task->status === 'в процессе') border-warning 
                        @else border-danger @endif">
                        
                        <div class="card-body d-flex flex-column">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h5 class="card-title text-truncate mb-0" style="max-width: 70%;" title="{{ $task->title }}">
                                    {{ $task->title }}
                                </h5>
                            <form action="{{ route('tasks.updateStatus', $task) }}" method="POST" style="max-width: 150px;">
                                @csrf
                                @method('PATCH')
                                <select name="status" class="form-select form-select-sm fw-bold
                                    @if($task->status === 'выполнена') bg-success text-white
                                    @elseif($task->status === 'в процессе') bg-warning text-dark
                                    @else bg-danger text-white @endif" 
                                    onchange="this.form.submit()">
                                    <option value="не выполнена" {{ $task->status === 'не выполнена' ? 'selected' : '' }}>Не выполнена</option>
                                    <option value="в процессе" {{ $task->status === 'в процессе' ? 'selected' : '' }}>В процессе</option>
                                    <option value="выполнена" {{ $task->status === 'выполнена' ? 'selected' : '' }}>Выполнена</option>
                                </select>
                            </form>
                            </div>
                            <p class="card-text text-muted flex-grow-1">
                                {{ $task->description ?? 'Описание отсутствует' }}
                            </p>
                            <div class="mt-3 pt-2 border-top d-flex justify-content-between align-items-center text-muted small">
                                <span>
                                    <strong>Срок:</strong> {{ \Carbon\Carbon::parse($task->due_date)->format('d.m.Y') }}
                                </span>
                                <div class="btn-group btn-group-sm">
                                    
                                    <button type="button" 
                                            class="btn btn-outline-secondary edit-task-btn"
                                            data-bs-toggle="modal" 
                                            data-bs-target="#editTaskModal"
                                            data-id="{{ $task->id }}"
                                            data-title="{{ $task->title }}"
                                            data-description="{{ $task->description }}"
                                            data-due-date="{{ $task->due_date }}"
                                            data-status="{{ $task->status }}">
                                        📝
                                    </button>
                                    <form action="{{ route('tasks.destroy', $task) }}" method="POST" class="d-inline" onsubmit="return confirm('Вы уверены, что хотите удалить эту задачу?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger">
                                            ❌
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif


</div>
<div class="modal fade" id="editTaskModal" tabindex="-1" aria-labelledby="editTaskModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-secondary text-white">
                <h5 class="modal-title" id="editTaskModalLabel">Редактирование задачи</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Закрыть"></button>
            </div>
            <form id="editTaskForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_title" class="form-label">Название задачи *</label>
                        <input type="text" name="title" id="edit_title" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_due_date" class="form-label">Срок выполнения *</label>
                        <input type="date" name="due_date" id="edit_due_date" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_status" class="form-label">Статус задачи</label>
                        <select name="status" id="edit_status" class="form-select">
                            <option value="не выполнена">не выполнена</option>
                            <option value="в процессе">в процессе</option>
                            <option value="выполнена">выполнена</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="edit_description" class="form-label">Описание задачи</label>
                        <textarea name="description" id="edit_description" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                    <button type="submit" class="btn btn-primary">Сохранить изменения</button>
                </div>
            </form>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js" integrity="sha384-IQsoLXl5PILFhosVNubq5LC7Qb9DXgDA9i+tQ8Zj3iwWAwPtgFTxbJ8NT4GN1R8p" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.min.js" integrity="sha384-cVKIPhGWiC2Al4u+LWgxfKTRIcfu0JTxR+EQDz/bgldoEyl4H0zUF0QKbrJ0EcQF" crossorigin="anonymous"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const editButtons = document.querySelectorAll('.edit-task-btn');
        const editForm = document.getElementById('editTaskForm');
        
        editButtons.forEach(button => {
            button.addEventListener('click', function () {
                const id = button.getAttribute('data-id');
                const title = button.getAttribute('data-title');
                const description = button.getAttribute('data-description');
                const dueDate = button.getAttribute('data-due-date');
                const status = button.getAttribute('data-status');
                editForm.setAttribute('action', `/tasks/${id}`);
                document.getElementById('edit_title').value = title;
                document.getElementById('edit_due_date').value = dueDate;
                document.getElementById('edit_description').value = description;
                document.getElementById('edit_status').value = status;
            });
        });
    });
</script>


</body>
</html>
