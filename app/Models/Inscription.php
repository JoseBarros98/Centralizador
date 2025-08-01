<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Helpers\NameMatcher;

class Inscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'first_name',
        'paternal_surname',
        'maternal_surname',
        'ci',
        'phone',
        'gender',
        'program_id',
        'payment_plan',
        'payment_method',
        'enrollment_fee',
        'first_installment',
        'total_paid',
        'status',
        'profession',
        'residence',
        'location',
        'inscription_date',
        'notes',
        'created_by',
        'updated_by',
        'certification',

        //Documentación
        'has_identity_card',
        'has_degree_title',
        'has_academic_diploma',
        'has_birth_certificate',
        'has_commitment_letter',
        'commitment_letter_path',
        'document_observations',

        //Accesos
        'was_added_to_the_group',
        'accesses_were_sent',
        'mail_was_sent',

        //Estado académico
        'academic_status',
        'has_freezing_letter',
        'freezing_letter_path',
        'freezing_letter_observations'
    ];

    protected $casts = [
        'inscription_date' => 'date',
        'has_identity_card' => 'boolean',
        'has_degree_title' => 'boolean',
        'has_academic_diploma' => 'boolean',
        'has_birth_certificate' => 'boolean',
        'has_commitment_letter' => 'boolean',
        'was_added_to_the_group' => 'boolean',
        'accesses_were_sent' => 'boolean',
        'mail_was_sent' => 'boolean',
    ];

    public function program()
    {
        return $this->belongsTo(Program::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function receipts()
    {
        return $this->hasMany(Receipt::class);
    }

    public function documents()
    {
        return $this->hasMany(Document::class);
    }

    public function documentFollowups()
    {
        return $this->hasMany(DocumentFollowup::class);
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public static function normalizeName($name)
    {
        return NameMatcher::normalizeName($name);
    }

    public function getFullName()
{
    return trim($this->first_name . ' ' . $this->paternal_surname . ' ' . $this->maternal_surname);
}

    // Generar código único basado en nombre y CI
    public static function generateCode($firstName, $ci)
    {
        $nameInitials = strtoupper(substr($firstName, 0, 2));
        $ciLastFour = substr($ci, -4);
        $randomChars = strtoupper(substr(md5(uniqid()), 0, 3));
        
        return $nameInitials . $ciLastFour . $randomChars;
    }
}
