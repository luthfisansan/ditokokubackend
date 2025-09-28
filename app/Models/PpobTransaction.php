<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PpobTransaction extends Model
{
    use HasFactory;

    protected $table = 'transaction_ppob';

    protected $fillable = [
        'ref_id',
        'customer_no',
        'buyer_sku_code',
        'message',
        'status',
        'rc',
        'buyer_last_saldo',
        'sn',
        'price',
        'tele',
        'wa'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'buyer_last_saldo' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    // Status constants
    const STATUS_PENDING = 'Pending';
    const STATUS_SUCCESS = 'Sukses';
    const STATUS_FAILED = 'Failed';

    // Scope for status filtering
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeSuccess($query)
    {
        return $query->where('status', self::STATUS_SUCCESS);
    }

    public function scopeFailed($query)
    {
        return $query->where('status', self::STATUS_FAILED);
    }

    // Accessor for formatted price
    public function getFormattedPriceAttribute()
    {
        return number_format($this->price, 0, ',', '.');
    }

    // Accessor for status badge class
    public function getStatusBadgeClassAttribute()
    {
        switch ($this->status) {
            case self::STATUS_PENDING:
                return 'badge-soft-warning';
            case self::STATUS_SUCCESS:
                return 'badge-soft-success';
            case self::STATUS_FAILED:
                return 'badge-soft-danger';
            default:
                return 'badge-soft-secondary';
        }
    }
}