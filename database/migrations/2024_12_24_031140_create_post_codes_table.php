<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection(config('postcode.database'))->create('post_codes', function (Blueprint $table) {
            $table->id()
                ->comment('#ID');

            $table->string('country_code', 2)
                ->comment('Country Code Alpha-2');

            $table->string('postcode', 16)
                ->comment('Post Code');

            $table->unique(['country_code', 'postcode']);

            $table->string('postcode_formatted', 16)
                ->index()
                ->comment('Formatted Post Code');

            $table->string('state_id', 3)
                ->index()
                ->comment('State Code');

            $table->string('city_id', 3)
                ->index()
                ->comment('City Code');

            $table->string('state')
                ->comment('State Name');

            $table->string('city')
                ->comment('City Name');

            $table->string('address')
                ->comment('Address');

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
        Schema::connection(config('postcode.database'))->dropIfExists('post_codes');
    }
};
