<?php
require_once __DIR__ . '/BaseDaov1.php';

/**
 * VERSION 3 – a second DAO on top of BaseDaov1.
 * Two lines and we can already read clubs.
 */
class ClubDaov3 extends BaseDaov1
{
    public function __construct()
    {
        parent::__construct('clubs');
    }
}
