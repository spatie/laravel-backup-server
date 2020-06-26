<?php

return [
    'exception_title' => 'Exception:',
    'exception_message_title' => 'Exception message',
    'exception_trace_title' => 'Exception trace',

    'backup_failed_subject' => 'Failed backup of :source_name',
    'backup_failed_subject_title' => 'Backup failed!',
    'backup_failed_body' => 'Important: An error occurred while backing up :source_name to :destination_name.',

    'backup_completed_subject' => 'Successful new backup of :source_name',
    'backup_completed_subject_title' => 'Successful new backup!',
    'backup_completed_body' => 'Great news, a new backup of :source_name was successfully created on :destination_name.',

    'cleanup_source_successful_subject' => 'Clean up of :source_name backups successful',
    'cleanup_source_successful_subject_title' => 'All clean!',
    'cleanup_source_successful_body' => 'The backups of :source_name were succesfully cleaned up.',

    'cleanup_source_failed_subject' => 'Clean up of :source_name backups failed',
    'cleanup_source_failed_subject_title' => 'Clean up failed!',
    'cleanup_source_failed_body' => 'An error occurred while cleaning up the backups of :source_name.',

    'cleanup_destination_successful_subject' => 'Clean up of backups on :destination_name succesfull',
    'cleanup_destination_successful_subject_title' => 'All clean!',
    'cleanup_destination_successful_body' => 'The backups on :destination_name were successfully cleaned up.',

    'cleanup_destination_failed_subject' => 'Clean up of :destination_name backups failed',
    'cleanup_destination_failed_subject_title' => 'Clean up failed!',
    'cleanup_destination_failed_body' => 'An error occurred while cleaning up the backups on :destination_name.',

    'healthy_source_found_subject' => 'The backups for :source_name are healthy',
    'healthy_source_found_subject_title' => 'Healthy backups',
    'healthy_source_found_body' => 'A health check ran and the backups for :source_name are considered healthy.',

    'unhealthy_source_found_subject' => 'Important: The backups for :source_name are unhealthy',
    'unhealthy_source_found_subject_title' => 'Unhealthy backups',
    'unhealthy_source_found_body' => 'Important: A health check ran and the backups for :source_name are unhealthy.',

    'healthy_destination_found_subject' => 'The backup destination :destination_name is healthy',
    'healthy_destination_found_subject_title' => 'Healthy backup destination',
    'healthy_destination_found_body' => 'A health check ran and the backup destination :destination_name is considered healthy.',

    'unhealthy_destination_found_subject' => 'Important: The backup destination :destination_name is unhealthy',
    'unhealthy_destination_found_subject_title' => 'Unhealthy backup destination',
    'unhealthy_destination_found_body' => 'A health check ran and the backup destination :destination_name is unhealthy.',

    'server_summary_subject' => 'Backup summary for *:period*',
    'server_summary_subject_title' => 'Your backup summary',
    'server_summary_body' => "Below you'll find some stats for your backup server for :period.",
];
