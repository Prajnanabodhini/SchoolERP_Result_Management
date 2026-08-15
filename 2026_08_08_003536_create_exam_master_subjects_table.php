Schema::create('exam_master_subjects', function (Blueprint $table) {
    $table->id();

    $table->unsignedBigInteger('exam_master_id');
    $table->unsignedBigInteger('standard_id');
    $table->unsignedBigInteger('subject_id');

    $table->decimal('max_marks', 8, 2)->default(0);
    $table->decimal('passing_marks', 8, 2)->default(0);

    $table->integer('display_order')->default(1);

    $table->timestamps();
});