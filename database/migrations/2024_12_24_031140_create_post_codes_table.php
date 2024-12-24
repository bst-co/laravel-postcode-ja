<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('post_codes', function (Blueprint $table) {
            $table->id()
                ->comment('#ID');

            $table->string('country_code', 3);

            $table->string('zip_code', 16);

            $table->unique(['country_code', 'zip_code']);

            $table->string('state_id', 3)
                ->index();

            $table->string('city_id', 3)
                ->index();

            $table->string('state');

            $table->string('city');

            $table->string('address');

            $table->datetime('created_at')
                ->nullable();

            $table->datetime('updated_at')
                ->nullable();

            $table->datetime('deleted_at')
                ->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('post_codes');
    }
};
