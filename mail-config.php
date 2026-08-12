<?php
/**
 * SMTP / delivery configuration for the site's contact & enrolment forms.
 *
 * IMPORTANT: This file holds a real mailbox password once filled in.
 * It is listed in .gitignore so it should NEVER be committed to GitHub.
 * On the live server, edit this file directly via Hostinger's File
 * Manager (or SFTP) — do not push it to the repository.
 */

return [
    // SMTP connection (Hostinger mailbox that will SEND the emails)
    'smtp_host'     => 'smtp.hostinger.com',
    'smtp_port'     => 587,
    'smtp_secure'   => 'tls',
    'smtp_username' => 'admissions@sevenministriesint.com',
    'smtp_password' => 'REPLACE_WITH_MAILBOX_PASSWORD', // <-- fill this in on the server only

    // Where form submissions should be delivered
    'deliver_to' => [
        'contact'         => 'admissions@sevenministriesint.com',
        'general'         => 'admissions@sevenministriesint.com',
        'after-school'    => 'admissions@sevenministriesint.com',
        'post-secondary'  => 'admissions@sevenministriesint.com',
    ],
];
