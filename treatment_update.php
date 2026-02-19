<?php
$table  = "treatments";
$pk     = "treatment_id";
$fields = [
    'treatment_date',
    'symptoms',
    'diagnosis',
    'treatment',
    'medication',
    'doctor_name',
    'next_appointment'
];

require __DIR__ . "/_core/update_template.php";
