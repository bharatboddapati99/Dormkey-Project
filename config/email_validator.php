<?php

/**
 * Validates whether an email address format is valid AND has real mail servers (MX records).
 */
function isValidRealEmail($email) {
    // 1. Basic PHP Syntax Validation
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return [
            'valid' => false,
            'message' => 'Please enter a valid email address structure (e.g. name@domain.com).'
        ];
    }

    // Extract domain part
    $domain = strtolower(substr(strrchr($email, "@"), 1));

    // 2. Block Common Disposable / Temporary Email Domains
    $blockedDomains = [
        'yopmail.com', 'mailinator.com', '10minutemail.com', 'tempmail.com',
        'guerrillamail.com', 'trashmail.com', 'sharklasers.com', 'dispostable.com'
    ];

    if (in_array($domain, $blockedDomains)) {
        return [
            'valid' => false,
            'message' => 'Disposable or temporary email addresses are not allowed.'
        ];
    }

    // 3. Check Real-World DNS MX Records (Does the domain actually receive email?)
    // Note: getmxrr checks if the domain has active mail servers registered on the internet.
    if (!checkdnsrr($domain, "MX") && !checkdnsrr($domain, "A")) {
        return [
            'valid' => false,
            'message' => "The email domain '@{$domain}' does not exist or cannot receive emails."
        ];
    }

    return ['valid' => true, 'message' => 'Valid email address.'];
}
?>