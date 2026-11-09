<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Order extends Model
{
    protected $fillable = ['user_id', 'status', 'total_price', 'comment'];

    // Связь: Заказ -> Пользователь
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Связь: Заказ -> Блюда (Многие ко многим)
    public function dishes(): BelongsToMany
    {
        return $this->belongsToMany(Dish::class, 'order_dish')
                    ->withPivot('quantity', 'price_at_sale') // Указываем доп. поля из пивот-таблицы
                    ->withTimestamps();
    }
}
