<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBackupServerTables extends Migration
{
    public function up()
    {
        Schema::create('backup_server_destinations', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->string('status')->default('active');

            $table->string('name');
            $table->string('disk_name');

            $table->integer('keep_all_backups_for_days')->nullable();
            $table->integer('keep_daily_backups_for_days')->nullable();
            $table->integer('keep_weekly_backups_for_weeks')->nullable();
            $table->integer('keep_monthly_backups_for_months')->nullable();
            $table->integer('keep_yearly_backups_for_years')->nullable();
            $table->integer('delete_oldest_backups_when_using_more_megabytes_than')->nullable();

            $table->integer('healthy_maximum_backup_age_in_days_per_source')->nullable();
            $table->integer('healthy_maximum_storage_in_mb_per_source')->nullable();
            $table->integer('healthy_maximum_storage_in_mb')->nullable();
            $table->integer('healthy_maximum_inode_usage_percentage')->nullable();

            $table->timestamps();
        });

        Schema::create('backup_server_sources', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->string('status')->default('active');
            $table->boolean('healthy')->default(false);

            $table->string('name');
            $table->string('host');
            $table->string('ssh_user');
            $table->integer('ssh_port')->default(22);
            $table->string('ssh_private_key_file', 512)->nullable();

            $table->integer('backup_hour')->default(0);

            $table->json('pre_backup_commands')->nullable();
            $table->json('post_backup_commands')->nullable();

            $table->json('includes')->nullable();
            $table->json('excludes')->nullable();

            $table->unsignedBigInteger('destination_id')->nullable();

            $table->string('cleanup_strategy_class')->nullable();

            $table->integer('keep_all_backups_for_days')->nullable();
            $table->integer('keep_daily_backups_for_days')->nullable();
            $table->integer('keep_weekly_backups_for_weeks')->nullable();
            $table->integer('keep_monthly_backups_for_months')->nullable();
            $table->integer('keep_yearly_backups_for_years')->nullable();
            $table->integer('delete_oldest_backups_when_using_more_megabytes_than')->nullable();

            $table->integer('healthy_maximum_backup_age_in_days')->nullable();
            $table->integer('healthy_maximum_storage_in_mb')->nullable();

            $table->timestamps();

            $table
                ->foreign('destination_id')
                ->references('id')
                ->on('backup_server_destinations')
                ->onDelete('set null');
        });

        Schema::create('backup_server_backups', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('status');
            $table->unsignedBigInteger('source_id');
            $table->unsignedBigInteger('destination_id');
            $table->string('disk_name');
            $table->string('path')->nullable();
            $table->unsignedBigInteger('size_in_kb')->nullable();
            $table->unsignedBigInteger('real_size_in_kb')->nullable();

            $table->timestamps();
            $table->timestamp('completed_at')->nullable();

            $table->text('rsync_summary')->nullable();
            $table->bigInteger('rsync_time_in_seconds')->nullable();
            $table->string('rsync_current_transfer_speed')->nullable();
            $table->string('rsync_average_transfer_speed_in_MB_per_second')->nullable();

            $table->foreign('source_id')->references('id')->on('backup_server_sources')->onDelete('cascade');
            $table->foreign('destination_id')->references('id')->on('backup_server_destinations')->onDelete('cascade');
        });

        Schema::create('backup_server_backup_log', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('source_id')->nullable();
            $table->unsignedBigInteger('backup_id')->nullable();
            $table->unsignedBigInteger('destination_id')->nullable();
            $table->string('task');
            $table->string('level');
            $table->longText('message');
            $table->timestamps();
        });
    }
}
