<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property Carbon $created_at Дата и время создания записи в БД
 * @property Carbon $updated_at Дата и время последнего изменения записи в БД
 * @property Carbon $deleted_at Дата и время мягкого удаления записи в БД
 * @property string $full_name
 * @property string $short_name
 * @property string $address
 * @property InstitutionType $type
 */
class Institution extends Model
{
    use HasFactory;

    public function type()
    {
        return $this->belongsTo(InstitutionType::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }
}
