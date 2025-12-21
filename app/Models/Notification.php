<?php
    namespace App\Models;

    use Illuminate\Database\Eloquent\Factories\HasFactory;
    use Illuminate\Database\Eloquent\Model;
    use App\Models\User;

    class Notification extends Model
    {
        use HasFactory;

        // Bisa diisi semua kolom penting
        protected $fillable = [
            'user_id',
            'proposal_id', // ✅ opsional (kalau kolomnya kamu tambahin)
            'title',
            'type',     // info, success, warning, dsb
            'message',  // optional, detail notifikasi
            'is_read'
        ];

        // ✅ biar is_read pasti kebaca boolean di JSON (true/false)
        protected $casts = [
            'is_read' => 'boolean',
            'proposal_id' => 'integer',
        ];

        /**
         * Relasi ke user
         */
        public function user()
        {
            return $this->belongsTo(User::class);
        }

        /**
         * Scope untuk notifikasi yang belum dibaca
         */
        public function scopeUnread($query)
        {
            return $query->where('is_read', false);
        }

        /**
         * Tandai notifikasi sebagai sudah dibaca
         */
        public function markAsRead()
        {
            $this->is_read = true;
            $this->save();
        }
    }
