<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;
    protected $primaryKey = 'client_id';
    protected $keyType = 'string';

    protected $fillable = ['client_id', 'client_name', 'phone', 'address', 'email', 'client_type', 'created_by', 'updated_by'];

    /**
     * Override toArray method to sanitize all fields.
     */
    public function toArray()
    {
        $attributes = parent::toArray();

        return $this->sanitizeArray($attributes);
    }

    /**
     * Override toJson method to sanitize all fields when converted to JSON.
     */
    public function toJson($options = 0)
    {
        $attributes = parent::toArray();

        $sanitized = $this->sanitizeArray($attributes);

        return json_encode($sanitized, $options);
    }

    /**
     * Helper method to sanitize all attributes in an array.
     */
    protected function sanitizeArray(array $attributes)
    {
        foreach ($attributes as $key => $value) {
            if (is_string($value)) {
                $attributes[$key] = htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
            }
        }
        return $attributes;
    }

}
