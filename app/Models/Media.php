<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Storage;
use DB;
use Symfony\Component\HttpKernel\HttpCache\Store;

class Media extends Model
{
    use SoftDeletes;

    const TYPE_IMAGE = 0;
    const TYPE_VIDEO = 1;
    const TYPE_AUDIO = 2;

    const STATUS_TMP = 0;
    const STATUS_CONVERTING = 1;
    const STATUS_ACTIVE = 2;

    protected $fillable = [
        'type', 'status', 'extension', 'info', 'url'
    ];

    protected $visible = [
        'id', 'type', 'created_at', 'url', 'screenshot', 'thumbs'
    ];

    protected $appends = [
        'screenshot'
    ];

    protected $casts = [
        'info' => 'array'
    ];

    public static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (!$model->hash) {
                $exists = true;
                while ($exists) {
                    $model->hash = Str::random();
                    $exists = self::where('hash', $model->hash)->exists();
                }
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    public function getScreenshotAttribute()
    {
        return null;
    }

    public function getThumbsAttribute()
    {
        if ($this->type == self::TYPE_VIDEO) {
            $thumbs = [];
            $files = Storage::files($this->status == self::STATUS_TMP ? $this->tmpPath : $this->pubPath);
            foreach ($files as $f) {
                if (preg_match('/thumb_([0-9]+)\.png/', $f, $matches)) {
                    $thumbs[] = [
                        'id' => $matches[1][0],
                        'url' => Storage::url($f)
                    ];
                }
            }
            return $thumbs;
        }
        return [];
    }

    public function publish()
    {
        if ($this->status == self::STATUS_TMP) {
            $this->status = self::STATUS_ACTIVE;
            $this->save();
        }
    }
}
