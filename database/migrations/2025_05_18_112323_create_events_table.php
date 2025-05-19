<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
class CreateEventsTable extends Migration
{
    public function up()
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('venue');
            $table->dateTime('date');
            $table->decimal('price', 10, 2)->default(0.00);
            $table->unsignedInteger('max_attendees');
            $table->enum('status', ['draft', 'published', 'cancelled'])->default('draft');
            $table->timestamps();
            $table->softDeletes();
            $table->index('organization_id');
            $table->index('date');
        });
    }
    public function down()
    {
        Schema::dropIfExists('events');
    }
}