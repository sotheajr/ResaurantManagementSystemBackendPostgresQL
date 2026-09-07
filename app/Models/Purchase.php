<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    use HasFactory;

    protected $table = 'purchases';
    protected $primaryKey = 'purchase_id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'supplier_id',
        'purchase_date',
        'total',
        'invoice_attachment',
    ];

    protected $appends = ['invoice_attachment_url'];

    protected $casts = [
        'purchase_date' => 'date',
        'total' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Full public URL for the stored invoice attachment.
     */
    public function getInvoiceAttachmentUrlAttribute()
    {
        if (!$this->invoice_attachment) {
            return null;
        }

        // If the file is already an absolute URL (e.g., Cloudinary), return it directly
        if (str_starts_with($this->invoice_attachment, 'http://') || str_starts_with($this->invoice_attachment, 'https://')) {
            return $this->invoice_attachment;
        }

        // Otherwise, it's a local storage path
        return asset('storage/' . $this->invoice_attachment);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function purchaseItems()
    {
        return $this->hasMany(PurchaseItem::class, 'purchase_id');
    }
}
