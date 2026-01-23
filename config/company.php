<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Company Holidays
    |--------------------------------------------------------------------------
    |
    | List of fixed holidays in the Netherlands (YYYY-MM-DD format).
    | These are excluded from working hours calculations.
    |
    */

    'holidays' => [
        // 2024 Dutch holidays (example - update yearly)
        '2024-01-01', // Nieuwjaarsdag
        '2024-04-01', // Eerste Paasdag
        '2024-04-02', // Tweede Paasdag
        '2024-04-27', // Koningsdag
        '2024-05-05', // Bevrijdingsdag (5 mei)
        '2024-05-09', // Hemelvaartsdag
        '2024-05-20', // Eerste Pinksterdag
        '2024-05-21', // Tweede Pinksterdag
        '2024-12-25', // Eerste Kerstdag
        '2024-12-26', // Tweede Kerstdag

        // 2025 Dutch holidays (example - update yearly)
        '2025-01-01', // Nieuwjaarsdag
        '2025-04-20', // Eerste Paasdag
        '2025-04-21', // Tweede Paasdag
        '2025-04-27', // Koningsdag
        '2025-05-05', // Bevrijdingsdag (5 mei)
        '2025-05-29', // Hemelvaartsdag
        '2025-06-09', // Eerste Pinksterdag
        '2025-06-10', // Tweede Pinksterdag
        '2025-12-25', // Eerste Kerstdag
        '2025-12-26', // Tweede Kerstdag
    ],

    /*
    |--------------------------------------------------------------------------
    | Leave Settings
    |--------------------------------------------------------------------------
    |
    | Default settings for leave calculations.
    |
    */

    'leave' => [
        'default_days_per_year' => 25, // Full-time annual leave days
        'hours_per_day' => 8, // Standard working hours per day
        'max_carry_over_days' => 5, // Maximum days to carry over to next year
        'carry_over_expiry_month' => 6, // Month when carry-over expires (June = 6)
        'carry_over_expiry_day' => 30, // Day when carry-over expires
    ],
];
