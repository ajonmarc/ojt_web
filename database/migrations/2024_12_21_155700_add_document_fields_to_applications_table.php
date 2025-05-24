<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->string('resume_path')->nullable()->after('status');
            $table->string('letter_path')->nullable()->after('resume_path');
            $table->string('preferred_company')->nullable()->after('letter_path');
            $table->date('start_date')->nullable()->after('preferred_company');
            $table->date('end_date')->nullable()->after('start_date');
            $table->integer('completed_hours')->default(0)->after('end_date');
            $table->integer('required_hours')->default(0)->after('completed_hours');
        });
    }

    public function down(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->dropColumn(['resume_path', 'letter_path', 'preferred_company', 'start_date', 'end_date', 'completed_hours', 'required_hours']);
        });
    }
};
