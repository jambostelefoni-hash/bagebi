#!/usr/bin/env bash
set -euo pipefail

APP_DIR=/var/www/bagebi
BACKUP_ROOT=/var/backups/bagebi/daily
STAMP=$(date +%F)
TARGET="$BACKUP_ROOT/$STAMP"

mkdir -p "$TARGET"
mysqldump --single-transaction --routines --events bagebi | gzip > "$TARGET/database.sql.gz"
tar -C "$APP_DIR" -czf "$TARGET/private-storage.tar.gz" storage/app
find "$BACKUP_ROOT" -mindepth 1 -maxdepth 1 -type d -mtime +14 -exec rm -rf {} +
