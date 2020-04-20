<?php

return [
    'exception_message' => 'Exception message: :message',
    'exception_trace' => 'Exception trace: :trace',
    'exception_message_title' => 'Exception message',
    'exception_trace_title' => 'Exception trace',

    'backup_failed_subject' => 'Failed backup of :source_name',
    'backup_failed_body' => 'Important: An error occurred while backing up :source_name',

    'backup_completed_subject' => 'Successful new backup of :source_name',
    'backup_completed_subject_title' => 'Successful new backup!',
    'backup_completed_body' => 'Great news, a new backup of :source_name was successfully created on :destination_name.',

    'cleanup_source_successful_subject' => 'Clean up of :source_name backups successful',
    'cleanup_source_successful_subject_title' => 'Clean up of backups successful!',
    'cleanup_source_successful_body' => 'The clean up of the :source_name backups.',

    'cleanup_destination_successful_subject' => 'Clean up of backups on :destination_name successful',
    'cleanup_destination_successful_subject_title' => 'Clean up of backups successful!',
    'cleanup_destination_successful_body' => 'The clean up of the backups on :destination_name was successful.',

    'cleanup_destination_failed_subject' => 'Cleaning up the backups of :destination_name failed.',
    'cleanup_destination_failed_subject_title' => 'Clean up of backups failed!',
    'cleanup_destination_failed_body' => 'An error occurred while cleaning up the backups of :destination_name',

    'healthy_source_found_subject' => 'The backups for :source_name are healthy',
    'healthy_source_found_subject_title' => 'The backups for :source_name are healthy',
    'healthy_source_found_body' => 'The backups for :source_name are considered healthy.',
    'unhealthy_source_found_subject' => 'Important: The backups for :source_name are unhealthy',
    'unhealthy_source_found_subject_title' => 'Important: The backups for :source_name are unhealthy. :problem',
    'unhealthy_source_found_body' => 'The backups for :source_name are unhealthy.',

    'healthy_destination_found_subject' => 'The backup destination :destination_name is healthy',
    'healthy_destination_found_subject_title' => 'The backup destination :destination_name is healthy',
    'healthy_destination_found_body' => 'The backup destination :destination_name is considered healthy.',
    'unhealthy_destination_found_subject' => 'Important: The backup destination :destination_name is unhealthy',
    'unhealthy_destination_found_subject_title' => 'Important: The backup destination :destination_name is unhealthy.',
    'unhealthy_destination_found_body' => 'The backup destination :destination_name is unhealthy.',
];
