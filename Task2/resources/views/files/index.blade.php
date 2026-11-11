<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Файловый менеджер</title>
    <link href="{{ asset('assets/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/icons/bootstrap-icons.min.css') }}" rel="stylesheet">

</head>

<body class="bg-light">

    <!-- Верхняя навигационная панель -->
    <nav class="navbar navbar-dark bg-dark shadow-sm mb-4">
        <div class="container">
            <a class="navbar-brand fw-bold" href="{{ route('files.index') }}">
                <i class="bi bi-folder2-open me-2"></i>Файловый менеджер
            </a>
            <div class="d-flex align-items-center text-white gap-3">
                <span><i class="bi bi-person-circle me-1"></i>{{ Auth::user()->name }}</span>
                <form method="POST" action="{{ route('logout') }}" class="m-0">
                    @csrf
                    <button type="submit" class="btn btn-outline-light btn-sm">Выйти</button>
                </form>
            </div>
        </div>
    </nav>

    <div class="container pb-5">

        <!-- Уведомления -->
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <!-- Панель поиска и фильтрации по тегам -->
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <form method="GET" action="{{ route('files.index') }}" class="row g-2 align-items-center">
                    <div class="col-md-6">
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-search"></i></span>
                            <input type="text" name="search" value="{{ $search }}" class="form-control"
                                placeholder="Поиск файлов по имени...">
                            <button type="submit" class="btn btn-primary">Найти</button>
                        </div>
                    </div>
                    @if ($search !== '' || $selectedTag)
                        <div class="col-auto">
                            <a href="{{ route('files.index') }}" class="btn btn-outline-secondary">Сбросить фильтры</a>
                        </div>
                    @endif
                </form>

                @if ($allTags->isNotEmpty())
                    <div class="mt-3 pt-2 border-top">
                        <span class="text-muted small me-2"><i class="bi bi-tags"></i> Теги:</span>
                        @foreach ($allTags as $tag)
                            <a href="{{ route('files.index', ['tag_id' => $tag->id]) }}"
                                class="badge rounded-pill text-decoration-none me-1 {{ $selectedTag?->id === $tag->id ? 'bg-primary' : 'bg-secondary' }}">
                                #{{ $tag->name }}
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <!-- Карточки загрузки файлов и создания папок -->
        <div class="row g-4 mb-4">
            <div class="col-md-7">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-white fw-semibold">
                        <i class="bi bi-cloud-upload me-1 text-primary"></i> Загрузить файл
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('files.upload') }}" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="folder_id" value="{{ $currentFolder?->id }}">
                            <div class="mb-2">
                                <input type="file" name="file" class="form-control" required>
                            </div>
                            <div class="input-group">
                                <input type="text" name="tags" class="form-control form-control-sm"
                                    placeholder="Теги через запятую (работа, отчет)">
                                <button type="submit" class="btn btn-success btn-sm px-4">Загрузить</button>
                            </div>
                            <div class="form-text mt-1">Макс. 5 МБ (jpg, jpeg, png, gif, pdf, doc, docx, txt)</div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-5">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-white fw-semibold">
                        <i class="bi bi-folder-plus me-1 text-warning"></i> Новая папка
                    </div>
                    <div class="card-body d-flex flex-column justify-content-center">
                        <form method="POST" action="{{ route('folders.store') }}">
                            @csrf
                            <input type="hidden" name="parent_id" value="{{ $currentFolder?->id }}">
                            <div class="input-group">
                                <input type="text" name="name" class="form-control" placeholder="Название папки..."
                                    required>
                                <button type="submit" class="btn btn-warning">Создать</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Навигация (Хлебные крошки) -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            @if ($search !== '' || $selectedTag)
                <h5 class="mb-0">
                    @if ($selectedTag)
                        Тег: <span class="badge bg-primary">#{{ $selectedTag->name }}</span>
                    @endif
                    @if ($search !== '')
                        Поиск: «{{ $search }}»
                    @endif
                    <span class="text-muted fs-6">(найдено: {{ $files->count() }})</span>
                </h5>
            @else
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 fs-5">
                        <li class="breadcrumb-item">
                            <a href="{{ route('files.index') }}" class="text-decoration-none">
                                <i class="bi bi-house-door"></i> Корень
                            </a>
                        </li>
                        @if ($currentFolder)
                            <li class="breadcrumb-item active" aria-current="page">{{ $currentFolder->name }}</li>
                        @endif
                    </ol>
                </nav>

                @if ($currentFolder)
                    <a href="{{ route('files.index', ['folder_id' => $currentFolder->parent_id]) }}"
                        class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-arrow-left"></i> На уровень вверх
                    </a>
                @endif
            @endif
        </div>
        <!-- Единый список папок и файлов -->
        <div class="card shadow-sm">
            <div class="table-responsive">
                @if ($folders->isEmpty() && $files->isEmpty())
                    <div class="p-5 text-center text-muted">
                        <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                        {{ ($search !== '' || $selectedTag) ? 'По вашему запросу ничего не найдено.' : 'Эта папка пуста.' }}
                    </div>
                @else
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Имя</th>
                                <th>Теги</th>
                                <th>Размер</th>
                                <th>Дата</th>
                                <th class="text-end">Действия</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Список папок -->
                            @foreach ($folders as $folder)
                                <tr>
                                    <td>
                                        <a href="{{ route('files.index', ['folder_id' => $folder->id]) }}"
                                            class="text-decoration-none fw-semibold text-dark">
                                            <i class="bi bi-folder-fill text-warning fs-5 me-2"></i>{{ $folder->name }}
                                        </a>
                                    </td>
                                    <td class="text-muted">—</td>
                                    <td class="text-muted">Папка</td>
                                    <td class="text-muted small">{{ $folder->created_at->format('d.m.Y H:i') }}</td>
                                    <td class="text-end">
                                        <div class="d-inline-flex gap-2 align-items-center">
                                            <form method="POST" action="{{ route('folders.update', $folder) }}"
                                                class="input-group input-group-sm" style="width: 210px;">
                                                @csrf
                                                @method('PUT')
                                                <input type="text" name="name" value="{{ $folder->name }}" class="form-control"
                                                    required>
                                                <button type="submit" class="btn btn-outline-primary" title="Переименовать">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                            </form>

                                            <form method="POST" action="{{ route('folders.destroy', $folder) }}"
                                                onsubmit="return confirm('Удалить папку и всё её содержимое?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger btn-sm" title="Удалить">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach

                            <!-- Список файлов -->
                            @foreach ($files as $file)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            @if (str_starts_with($file->mime_type, 'image/'))
                                                <img src="{{ route('files.preview', $file) }}" alt="{{ $file->name }}"
                                                    class="rounded border" style="width: 40px; height: 40px; object-fit: cover;">
                                            @else
                                                <i class="bi bi-file-earmark-text text-secondary fs-3 px-1"></i>
                                            @endif

                                            <div>
                                                <a href="{{ route('files.download', $file) }}"
                                                    class="text-decoration-none fw-medium" title="Скачать файл">
                                                    {{ $file->name }}
                                                </a>
                                                @if ($search !== '' || $selectedTag)
                                                    <div class="small text-muted">
                                                        <i class="bi bi-folder"></i>
                                                        {{ $file->folder ? $file->folder->name : 'Корень' }}
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Теги файла -->
                                    <td style="max-width: 220px;">
                                        <div class="mb-1">
                                            @foreach ($file->tags as $tag)
                                                <span class="badge bg-info text-dark d-inline-flex align-items-center me-1 mb-1">
                                                    <a href="{{ route('files.index', ['tag_id' => $tag->id]) }}"
                                                        class="text-dark text-decoration-none">
                                                        #{{ $tag->name }}
                                                    </a>
                                                    <form method="POST" action="{{ route('files.tags.detach', [$file, $tag]) }}"
                                                        class="m-0 ms-1">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn-close" style="font-size: 0.55rem;"
                                                            title="Убрать тег"></button>
                                                    </form>
                                                </span>
                                            @endforeach
                                        </div>

                                        <form method="POST" action="{{ route('files.tags.attach', $file) }}"
                                            class="input-group input-group-sm" style="width: 130px;">
                                            @csrf
                                            <input type="text" name="tag" class="form-control" placeholder="+ тег" required>
                                            <button type="submit" class="btn btn-outline-secondary">+</button>
                                        </form>
                                    </td>

                                    <td class="text-muted small">{{ round($file->size / 1024, 2) }} КБ</td>
                                    <td class="text-muted small">{{ $file->created_at->format('d.m.Y H:i') }}</td>

                                    <!-- Действия над файлом -->
                                    <td class="text-end">
                                        <div class="d-inline-flex flex-wrap justify-content-end gap-2 align-items-center">
                                            @if (str_starts_with($file->mime_type, 'image/'))
                                                <button type="button" class="btn btn-outline-info btn-sm"
                                                    onclick="openPreview('{{ route('files.preview', $file) }}', '{{ $file->name }}')"
                                                    title="Предпросмотр">
                                                    <i class="bi bi-eye"></i>
                                                </button>
                                            @endif

                                            <!-- Перемещение -->
                                            <form method="POST" action="{{ route('files.move', $file) }}"
                                                class="input-group input-group-sm" style="width: 185px;">
                                                @csrf
                                                @method('PATCH')
                                                <select name="folder_id" class="form-select">
                                                    <option value="">Корень</option>
                                                    @foreach ($allFolders as $targetFolder)
                                                        <option value="{{ $targetFolder->id }}" {{ $file->folder_id === $targetFolder->id ? 'selected' : '' }}>
                                                            {{ $targetFolder->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <button type="submit" class="btn btn-outline-secondary" title="Переместить">
                                                    <i class="bi bi-folder-symlink"></i>
                                                </button>
                                            </form>

                                            <!-- Переименование -->
                                            <form method="POST" action="{{ route('files.update', $file) }}"
                                                class="input-group input-group-sm" style="width: 185px;">
                                                @csrf
                                                @method('PUT')
                                                <input type="text" name="name"
                                                    value="{{ pathinfo($file->name, PATHINFO_FILENAME) }}" class="form-control"
                                                    required>
                                                <input type="text" name="name"
                                                    value="{{ pathinfo($file->name, PATHINFO_FILENAME) }}" class="form-control"
                                                    required>
                                                <button type="submit" class="btn btn-outline-primary" title="Переименовать">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                            </form>

                                            <!-- Удаление -->
                                            <form method="POST" action="{{ route('files.destroy', $file) }}"
                                                onsubmit="return confirm('Удалить этот файл?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger btn-sm" title="Удалить">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </div>
    </div>

    <!-- Модальное окно для предпросмотра изображений -->
    <div class="modal fade" id="previewModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="previewTitle">Предпросмотр</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
                </div>
                <div class="modal-body text-center bg-dark">
                    <img id="previewImage" src="" alt="" class="img-fluid rounded" style="max-height: 75vh;">
                </div>
            </div>
        </div>
    </div>

    <script src="https://jsdelivr.net"></script>
    <script>
        function openPreview(url, title) {
            document.getElementById('previewImage').src = url;
            document.getElementById('previewTitle').textContent = title;
            const modal = new bootstrap.Modal(document.getElementById('previewModal'));
            modal.show();
        }
    </script>
    <script src="{{ asset('assets/js/bootstrap.bundle.min.js') }}"></script>

</body>

</html>