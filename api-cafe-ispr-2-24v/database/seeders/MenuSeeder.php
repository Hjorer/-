<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\Dish;

class MenuSeeder extends Seeder
{
    public function run(): void
    {
        $menu = [
            'Пицца' => [
                ['name' => 'Маргарита', 'price' => 450.00, 'desc' => 'Томаты, моцарелла, базилик'],
                ['name' => 'Пепперони', 'price' => 550.00, 'desc' => 'Острые колбаски пепперони, моцарелла'],
                ['name' => 'Четыре сыра', 'price' => 600.00, 'desc' => 'Дорблю, чеддер, пармезан, моцарелла'],
            ],
            'Супы' => [
                ['name' => 'Борщ', 'price' => 320.00, 'desc' => 'Классический борщ с говядиной и сметаной'],
                ['name' => 'Том Ям', 'price' => 480.00, 'desc' => 'Острый тайский суп с креветками'],
                ['name' => 'Грибной крем-суп', 'price' => 290.00, 'desc' => 'Нежный крем-суп из шампиньонов'],
            ],
            'Горячее' => [
                ['name' => 'Стейк из лосося', 'price' => 850.00, 'desc' => 'Нежный лосось на гриле с лимоном'],
                ['name' => 'Бефстроганов', 'price' => 490.00, 'desc' => 'Говядина в сметанном соусе с пюре'],
                ['name' => 'Куриная грудка су-вид', 'price' => 380.00, 'desc' => 'Сочная грудка с овощами'],
            ],
            'Десерты' => [
                ['name' => 'Чизкейк Нью-Йорк', 'price' => 250.00, 'desc' => 'Классический творожный десерт'],
                ['name' => 'Тирамису', 'price' => 280.00, 'desc' => 'Итальянский многослойный десерт'],
                ['name' => 'Шоколадный фондан', 'price' => 300.00, 'desc' => 'Кекс с жидким шоколадом внутри'],
            ],
            'Напитки' => [
                ['name' => 'Капучино', 'price' => 180.00, 'desc' => 'Кофе с пышной молочной пенкой'],
                ['name' => 'Домашний лимонад', 'price' => 200.00, 'desc' => 'Освежающий цитрусовый напиток'],
                ['name' => 'Чай черный/зеленый', 'price' => 150.00, 'desc' => 'Заварной чай в чайнике'],
            ],
        ];
        foreach ($menu as $categoryName => $dishes) {
            $category = Category::create(['name' => $categoryName]);
            foreach ($dishes as $dishData) {
                Dish::create([
                    'category_id' => $category->id,
                    'name' => $dishData['name'],
                    'price' => $dishData['price'],
                    'description' => $dishData['desc'],
                    'is_available' => true,
                ]);
            }
        }
    }
}
