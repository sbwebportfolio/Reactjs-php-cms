<?php
/** Setup/install notes for the Phase 2 authentication schema. */
?>
-- Run cms_database.sql first, then this idempotent hardening migration.
-- Login throttling fields are included in the Phase 1 admins schema; these ALTERs
-- are provided only as a reference for older installations and are intentionally
-- not automatic because MySQL versions differ in IF NOT EXISTS support.
CREATE TABLE IF NOT EXISTS login_attempts (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  email_hash CHAR(64) NOT NULL,
  ip_hash CHAR(64) NOT NULL,
  attempted_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  was_successful TINYINT(1) NOT NULL DEFAULT 0,
  INDEX idx_login_attempts_email_time (email_hash, attempted_at),
  INDEX idx_login_attempts_ip_time (ip_hash, attempted_at)
) ENGINE=InnoDB;
