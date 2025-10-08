#!/usr/bin/env bash
# Script to backup legacy migrations and rename snapshot migrations into canonical names.
# Run from repository root: ./tools/convert_migrations.sh

set -euo pipefail

ROOT_DIR="$(cd "$(dirname "$0")/.." && pwd)"
MIGRATIONS_DIR="$ROOT_DIR/Dolphin_Backend/database/migrations"
BACKUP_DIR="$MIGRATIONS_DIR/legacy_backup_$(date +%Y%m%d%H%M%S)"

echo "Migrations dir: $MIGRATIONS_DIR"
echo "Creating backup directory: $BACKUP_DIR"
mkdir -p "$BACKUP_DIR"

# Move existing migrations that are NOT the generated snapshot files into backup
shopt -s nullglob
for f in "$MIGRATIONS_DIR"/*.php; do
  base=$(basename "$f")
  # keep files with _snapshot in the name (we will rename them)
  if [[ "$base" == *_snapshot.php ]]; then
    continue
  fi
  # also keep any file that already has today timestamp 2025_10_08 (the generated ones may vary)
  if [[ "$base" =~ ^2025_10_08_12 ]]; then
    # keep generated ones
    continue
  fi
  echo "Backing up $base -> $(basename "$BACKUP_DIR")/"
  git mv "$f" "$BACKUP_DIR/" || mv "$f" "$BACKUP_DIR/"
done

echo "Renaming snapshot migration filenames to canonical names (removing _snapshot)..."
for f in "$MIGRATIONS_DIR"/*_snapshot.php; do
  [ -e "$f" ] || continue
  newf="${f/_snapshot/}"
  echo "Renaming $(basename "$f") -> $(basename "$newf")"
  git mv "$f" "$newf" || mv "$f" "$newf"
done

echo "Done. Review changes, run tests, then run:"
echo "  cd Dolphin_Backend && php artisan migrate --pretend"
