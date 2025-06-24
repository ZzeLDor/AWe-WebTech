<?php
header('Content-Type: application/json');
function validateYear($year) {
    $year = filter_var($year, FILTER_VALIDATE_INT);
    if ($year === false || $year < 2010 || $year > 2025) {
        return false;
    }
    return $year;
}

function validateState($state) {
    $validStates = [
        'AL', 'AZ', 'AR', 'CA', 'CO', 'CT', 'DE', 'FL', 'GA', 'ID',
        'IL', 'IN', 'IA', 'KS', 'KY', 'LA', 'ME', 'MD', 'MA', 'MI',
        'MN', 'MS', 'MO', 'MT', 'NE', 'NV', 'NH', 'NJ', 'NM', 'NY',
        'NC', 'ND', 'OH', 'OK', 'OR', 'PA', 'RI', 'SC', 'SD', 'TN',
        'TX', 'UT', 'VT', 'VA', 'WA', 'WV', 'WI', 'WY'
    ];

    if (empty($state)) {
        return '';
    }

    return in_array(strtoupper($state), $validStates) ? strtoupper($state) : false;
}

function validateSeverity($severity) {
    $severity = filter_var($severity, FILTER_VALIDATE_INT);
    if ($severity === false || $severity < 1 || $severity > 4) {
        return false;
    }
    return $severity;
}

function validateCoordinate($coord) {
    $coord = filter_var($coord, FILTER_VALIDATE_FLOAT);
    if ($coord === false) {
        return false;
    }
    return $coord;
}

function validateDate($date) {
    $d = DateTime::createFromFormat('Y-m-d', $date);
    return $d && $d->format('Y-m-d') === $date ? $date : false;
}

?>