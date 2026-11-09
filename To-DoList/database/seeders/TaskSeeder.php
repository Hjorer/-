<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Task;

class TaskSeeder extends Seeder
{
    public function run(): void
    {
        Task::create([
            'title' => 'Изучить основы Laravel',
            'description' => 'Разобраться с роутингом, контроллерами и миграциями',
            'due_date' => now()->addDays(3),
            'status' => 'в процессе'
        ]);

        Task::create([
            'title' => 'Развернуть проект на GitHub',
            'description' => 'Создать публичный репозиторий и залить код To-Do приложения',
            'due_date' => now()->addDays(5),
            'status' => 'не выполнена'
        ]);

        Task::create([
            'title' => 'Сверстать интерфейс на Bootstrap',
            'description' => 'Сделать адаптивную шапку, карточки задач и модальное окно',
            'due_date' => now()->subDays(1),
            'status' => 'выполнена'
        ]);
    }
}
