<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TemplateQuestion extends Model
{
    protected $fillable = ['test_template_id', 'question_id', 'order', 'config'];

    protected $casts = ['config' => 'array'];

    public function template()
    {
        return $this->belongsTo(TestTemplate::class, 'test_template_id');
    }

    public function question()
    {
        return $this->belongsTo(Question::class);
    }
}
