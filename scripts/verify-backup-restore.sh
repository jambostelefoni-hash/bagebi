#!/usr/bin/env bash
set -euo pipefail

APP_DIR=/var/www/bagebi
BACKUP_ROOT=/var/backups/bagebi
SOURCE_DATABASE=bagebi
VERIFY_DATABASE=bagebi_restore_verify
STAMP=$(date +%Y%m%d-%H%M%S)
DATABASE_BACKUP="$BACKUP_ROOT/verification-$STAMP.sql.gz"
STORAGE_BACKUP="$BACKUP_ROOT/verification-$STAMP-private-storage.tar.gz"

cleanup() {
    mysql -e "DROP DATABASE IF EXISTS \`$VERIFY_DATABASE\`;"
}
trap cleanup EXIT

mkdir -p "$BACKUP_ROOT"
mysqldump --single-transaction --routines --events "$SOURCE_DATABASE" | gzip > "$DATABASE_BACKUP"
tar -C "$APP_DIR" -czf "$STORAGE_BACKUP" storage/app
gzip -t "$DATABASE_BACKUP"

mysql -e "DROP DATABASE IF EXISTS \`$VERIFY_DATABASE\`; CREATE DATABASE \`$VERIFY_DATABASE\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
gunzip -c "$DATABASE_BACKUP" | mysql "$VERIFY_DATABASE"

source_tables=$(mysql -N -e "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='$SOURCE_DATABASE';")
restored_tables=$(mysql -N -e "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='$VERIFY_DATABASE';")
source_children=$(mysql -N -e "SELECT COUNT(*) FROM \`$SOURCE_DATABASE\`.kindergarteners;")
restored_children=$(mysql -N -e "SELECT COUNT(*) FROM \`$VERIFY_DATABASE\`.kindergarteners;")

if [[ "$source_tables" != "$restored_tables" || "$source_children" != "$restored_children" ]]; then
    echo "Backup verification failed: tables=$source_tables/$restored_tables children=$source_children/$restored_children" >&2
    exit 1
fi

echo "Backup verified: tables=$source_tables children=$source_children"
echo "Database backup: $DATABASE_BACKUP"
echo "Private storage backup: $STORAGE_BACKUP"
